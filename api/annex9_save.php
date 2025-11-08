<?php
/**
 * API Handler: Save Power Supply Status Report (Annex 9)
 * WITH IMAGE UPLOAD SUPPORT - FIXED VERSION WITH COMPLETE AUDIT LOGGING
 */

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// SECURITY LOG: Access attempt
log_audit_action(
    $_SESSION['user_id'],
    'annex9_form_access',
    "Accessed Annex 9 save handler"
);

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_security_event($_SESSION['user_id'], 'invalid_request_method', 'Attempted non-POST request to annex9_save.php');
    set_flash('Invalid request method', 'error');
    header('Location: ../public/annex9.php');
    exit;
}

// CSRF token verification
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event(
        $_SESSION['user_id'],
        'csrf_validation_failed',
        'CSRF token validation failed for Annex 9 submission'
    );
    set_flash('Security token validation failed. Please try again.', 'error');
    header('Location: ../public/annex9.php');
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex9_save', 20, 3600)) {
    log_security_event(
        $user_id,
        'rate_limit_exceeded',
        'Annex 9 submission rate limit exceeded (20 per hour)'
    );
    set_flash('Too many submissions. Please wait before submitting again.', 'error');
    header('Location: ../public/annex9.php');
    exit;
}

// Get user data
$user_data = get_user_location_data($user_id);
$user_barangay = sanitize($_POST['user_barangay'] ?? '');

// Verify barangay access for admins
if (is_admin()) {
    $current_lock = get_admin_locked_barangay($user_id);
    if (!$current_lock) {
        log_security_event(
            $user_id,
            'barangay_lock_missing',
            'Admin attempted Annex 9 submission without barangay lock'
        );
        set_flash('Please select a barangay from the admin dashboard first.', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
    if ($current_lock !== $user_barangay) {
        log_security_event(
            $user_id,
            'barangay_mismatch',
            "Barangay mismatch: Locked=$current_lock, Submitted=$user_barangay"
        );
        set_flash('Invalid barangay selection. Please select a barangay from the admin dashboard.', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
}

// Validate input arrays exist
$required_arrays = ['region', 'province', 'city', 'barangay', 'serviceProvider', 'interruptionDate'];
foreach ($required_arrays as $field) {
    if (!isset($_POST[$field]) || !is_array($_POST[$field]) || empty($_POST[$field])) {
        log_security_event(
            $user_id,
            'invalid_form_data',
            "Missing or invalid field: $field in Annex 9 submission"
        );
        set_flash("Missing or invalid field: $field", 'error');
        header('Location: ../public/annex9.php');
        exit;
    }
}

// Get array data
$regions = $_POST['region'];
$provinces = $_POST['province'];
$cities = $_POST['city'];
$barangays = $_POST['barangay'];
$serviceProviders = $_POST['serviceProvider'];
$interruptionDates = $_POST['interruptionDate'];
$restoredDates = $_POST['restoredDate'] ?? [];
$remarks = $_POST['remarks'] ?? [];

// Get uploaded files
$uploadedFiles = $_FILES['image'] ?? null;

// Validate all arrays have same length
$entryCount = count($regions);
if (
    count($provinces) !== $entryCount ||
    count($cities) !== $entryCount ||
    count($barangays) !== $entryCount ||
    count($serviceProviders) !== $entryCount ||
    count($interruptionDates) !== $entryCount
) {
    log_security_event(
        $user_id,
        'data_manipulation_detected',
        'Array length mismatch in Annex 9 submission - possible data manipulation'
    );
    set_flash('Data mismatch detected. Please try again.', 'error');
    header('Location: ../public/annex9.php');
    exit;
}

// Limit number of entries (prevent abuse)
if ($entryCount > 50) {
    log_security_event(
        $user_id,
        'excessive_entries',
        "Attempted to submit $entryCount entries (limit: 50)"
    );
    set_flash('Maximum 50 entries allowed per submission.', 'error');
    header('Location: ../public/annex9.php');
    exit;
}

// Check for suspiciously low entry count
if ($entryCount === 0) {
    log_security_event(
        $user_id,
        'empty_submission',
        'Attempted to submit form with zero entries'
    );
    set_flash('Please add at least one entry.', 'error');
    header('Location: ../public/annex9.php');
    exit;
}

// IMAGE UPLOAD DIRECTORY - FIXED PATH
$upload_dir = __DIR__ . '/../uploads/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        error_log("Failed to create upload directory: $upload_dir");
        set_flash('Server configuration error. Please contact administrator.', 'error');
        header('Location: ../public/annex9.php');
        exit;
    }
}

// Check if upload directory is writable
if (!is_writable($upload_dir)) {
    error_log("Upload directory not writable: $upload_dir");
    set_flash('Server configuration error. Please contact administrator.', 'error');
    header('Location: ../public/annex9.php');
    exit;
}

// Begin transaction
try {
    $pdo->beginTransaction();
    
    // Prepare insert statement
    $stmt = $pdo->prepare("
        INSERT INTO annex9_power_supply (
            created_by, region, province, city, barangay,
            service_provider, interruption_datetime, restored_datetime, remarks, image_path,
            created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            NOW(), NOW()
        )
    ");
    
    $inserted_count = 0;
    $inserted_ids = [];
    $errors = [];
    $validation_failures = [];
    
    // Process each entry
    for ($i = 0; $i < $entryCount; $i++) {
        // Sanitize and validate required fields
        $region = sanitize($regions[$i]);
        $province = sanitize($provinces[$i]);
        $city = sanitize($cities[$i]);
        $barangay = sanitize($barangays[$i]);
        $serviceProvider = sanitize($serviceProviders[$i]);
        $interruptionDate = sanitize($interruptionDates[$i]);
        
        // Optional fields
        $restoredDate = isset($restoredDates[$i]) && !empty($restoredDates[$i]) ? sanitize($restoredDates[$i]) : null;
        $remark = isset($remarks[$i]) && !empty($remarks[$i]) ? sanitize(substr($remarks[$i], 0, 500)) : null;
        
        // IMAGE UPLOAD PROCESSING - FIXED VERSION
        $imagePath = null;
        if ($uploadedFiles && isset($uploadedFiles['name'][$i]) && !empty($uploadedFiles['name'][$i])) {
            $file_tmp = $uploadedFiles['tmp_name'][$i];
            $file_name = $uploadedFiles['name'][$i];
            $file_size = $uploadedFiles['size'][$i];
            $file_error = $uploadedFiles['error'][$i];
            
            error_log("File upload attempt - Entry $i: $file_name, Size: $file_size, Error: $file_error");
            
            if ($file_error === UPLOAD_ERR_OK) {
                // Validate file size (5MB max)
                if ($file_size > 5 * 1024 * 1024) {
                    $validation_failures[] = "Entry " . ($i + 1) . ": Image file too large (max 5MB)";
                    error_log("File too large: $file_name ($file_size bytes)");
                    // Continue without image
                } else {
                    // Validate file type
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    $file_type = mime_content_type($file_tmp);
                    
                    if (!in_array($file_type, $allowed_types)) {
                        $validation_failures[] = "Entry " . ($i + 1) . ": Invalid image type ($file_type)";
                        error_log("Invalid file type: $file_name ($file_type)");
                        // Continue without image
                    } else {
                        // Generate secure filename
                        $secure_filename = generate_secure_filename($file_name);
                        $destination = $upload_dir . $secure_filename;
                        
                        error_log("Attempting to move file to: $destination");
                        
                        // Move uploaded file
                        if (move_uploaded_file($file_tmp, $destination)) {
                            $imagePath = $secure_filename; // Store only filename, not path
                            error_log("File uploaded successfully: $secure_filename");
                            
                            // AUDIT LOG: File upload success
                            log_audit_action(
                                $user_id, 
                                'file_upload', 
                                "Uploaded image for power supply report: {$secure_filename} for barangay {$barangay}"
                            );
                        } else {
                            $validation_failures[] = "Entry " . ($i + 1) . ": Failed to save image file";
                            error_log("Failed to move uploaded file: $file_name to $destination");
                        }
                    }
                }
            } elseif ($file_error !== UPLOAD_ERR_NO_FILE) {
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
                    UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
                    UPLOAD_ERR_PARTIAL => 'File upload incomplete',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                ];
                $error_msg = $upload_errors[$file_error] ?? "Unknown error ($file_error)";
                $validation_failures[] = "Entry " . ($i + 1) . ": Image upload failed - $error_msg";
                error_log("File upload error $file_error: $error_msg for file: $file_name");
            }
        } else {
            error_log("No file uploaded for entry $i or file upload not set");
        }
        
        // Validation checks
        if (empty($region) || strlen($region) > 100) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid region";
            $validation_failures[] = "Entry " . ($i + 1) . ": region validation failed";
            continue;
        }
        
        if (empty($province) || strlen($province) > 100) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid province";
            $validation_failures[] = "Entry " . ($i + 1) . ": province validation failed";
            continue;
        }
        
        if (empty($city) || strlen($city) > 100) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid city/municipality";
            $validation_failures[] = "Entry " . ($i + 1) . ": city validation failed";
            continue;
        }
        
        if (empty($barangay) || strlen($barangay) > 100) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid barangay";
            $validation_failures[] = "Entry " . ($i + 1) . ": barangay validation failed";
            continue;
        }
        
        // CRITICAL: Verify barangay matches user's barangay
        if ($barangay !== $user_barangay) {
            $errors[] = "Entry " . ($i + 1) . ": Barangay mismatch";
            $validation_failures[] = "Entry " . ($i + 1) . ": barangay mismatch - attempted $barangay, expected $user_barangay";
            log_security_event(
                $user_id,
                'barangay_manipulation_attempt',
                "User attempted to submit data for barangay '$barangay' but locked to '$user_barangay'"
            );
            continue;
        }
        
        if (empty($serviceProvider) || strlen($serviceProvider) > 200) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid service provider";
            $validation_failures[] = "Entry " . ($i + 1) . ": service provider validation failed";
            continue;
        }
        
        // Validate interruption datetime
        $interruption = DateTime::createFromFormat('Y-m-d\TH:i', $interruptionDate);
        if (!$interruption) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid interruption date/time";
            $validation_failures[] = "Entry " . ($i + 1) . ": interruption datetime validation failed";
            continue;
        }
        $interruptionDatetime = $interruption->format('Y-m-d H:i:s');
        
        // Validate restored datetime if provided
        $restoredDatetime = null;
        if ($restoredDate !== null) {
            $restored = DateTime::createFromFormat('Y-m-d\TH:i', $restoredDate);
            if (!$restored) {
                $errors[] = "Entry " . ($i + 1) . ": Invalid restored date/time";
                $validation_failures[] = "Entry " . ($i + 1) . ": restored datetime validation failed";
                continue;
            }
            
            // Check that restored time is after interruption time
            if ($restored < $interruption) {
                $errors[] = "Entry " . ($i + 1) . ": Restored time must be after interruption time";
                $validation_failures[] = "Entry " . ($i + 1) . ": restored time before interruption time";
                continue;
            }
            
            $restoredDatetime = $restored->format('Y-m-d H:i:s');
        }
        
        // Execute insert
        $result = $stmt->execute([
            $user_id, $region, $province, $city, $barangay,
            $serviceProvider, $interruptionDatetime, $restoredDatetime, $remark, $imagePath
        ]);
        
        if ($result) {
            $last_id = $pdo->lastInsertId();
            $inserted_ids[] = $last_id;
            $inserted_count++;
            
            // SUCCESS AUDIT LOG - Individual record
            log_audit_action(
                $user_id,
                'annex9_entry_submitted',
                "Submitted power supply entry #{$last_id} for {$barangay}: {$serviceProvider} - Interruption: {$interruptionDatetime}" . 
                ($imagePath ? " (with image: $imagePath)" : "") . 
                ($restoredDatetime ? " - Restored: {$restoredDatetime}" : "")
            );
            
            // SUCCESS SECURITY LOG - Individual record
            log_security_event(
                $user_id,
                'annex9_submission_success',
                "Successfully submitted Annex9 record #{$last_id} for {$barangay}: {$serviceProvider}" . 
                ($imagePath ? " with image: $imagePath" : "")
            );
            
            error_log("Annex9 - Entry " . ($i + 1) . " inserted with ID: $last_id" . ($imagePath ? " (with image: $imagePath)" : ""));
        } else {
            $errors[] = "Entry " . ($i + 1) . ": Failed to insert";
            error_log("Annex 9 insert failed for entry " . ($i + 1) . " - User: $user_id");
            
            // Delete uploaded image if insert failed
            if ($imagePath && file_exists($upload_dir . $imagePath)) {
                unlink($upload_dir . $imagePath);
                error_log("Deleted orphaned image: $imagePath");
                
                // AUDIT LOG: Orphaned file cleanup
                log_audit_action(
                    $user_id,
                    'file_cleanup',
                    "Cleaned up orphaned image after failed insert: $imagePath"
                );
            }
        }
    }

    // Log validation failures if any
    if (!empty($validation_failures)) {
        log_security_event(
            $user_id,
            'validation_failures',
            'Annex 9 submission had validation failures: ' . implode('; ', $validation_failures)
        );
        
        // AUDIT LOG: Validation warnings
        log_audit_action(
            $user_id,
            'annex9_validation_warnings',
            'Annex 9 submission completed with validation warnings: ' . count($validation_failures) . ' issues'
        );
    }
    
    // Commit transaction if at least one entry was successful
    if ($inserted_count > 0) {
        $pdo->commit();
        
        $id_list = implode(', ', $inserted_ids);
        
        // MAIN AUDIT LOG: Successful batch submission
        log_audit_action(
            $user_id,
            'annex9_batch_submission',
            "Successfully submitted power supply report batch with $inserted_count entries (IDs: $id_list) for barangay: $user_barangay" . 
            (count($errors) > 0 ? " (" . count($errors) . " entries failed)" : "") .
            (!empty($validation_failures) ? " [" . count($validation_failures) . " validation warnings]" : "")
        );
        
        // SUCCESS SECURITY LOG - Batch summary
        log_security_event(
            $user_id,
            'annex9_batch_success',
            "Successfully submitted Annex9 batch with $inserted_count records for {$user_barangay}"
        );
        
        error_log("Annex9 - Total inserted: $inserted_count, IDs: $id_list");
        
        if (count($errors) > 0 || !empty($validation_failures)) {
            $warning_msg = "Report saved with $inserted_count entries.";
            if (count($errors) > 0) {
                $warning_msg .= " " . count($errors) . " entries had errors.";
            }
            if (!empty($validation_failures)) {
                $warning_msg .= " " . count($validation_failures) . " validation warnings.";
            }
            set_flash($warning_msg, 'warning');
        } else {
            set_flash("Power supply report saved successfully! $inserted_count entries recorded.", 'success');
        }
        
        // Redirect to records page after successful submission
        header('Location: ../public/annex9_records.php');
        exit;
    } else {
        $pdo->rollBack();
        
        // AUDIT LOG: Failed submission
        log_audit_action(
            $user_id,
            'annex9_submission_failed',
            "Failed to submit Annex 9 report - all $entryCount entries had validation errors" . 
            (!empty($validation_failures) ? " - Validation issues: " . implode('; ', array_slice($validation_failures, 0, 5)) : "")
        );
        
        // SECURITY LOG: Complete failure
        log_security_event(
            $user_id,
            'annex9_complete_failure',
            "All $entryCount Annex 9 entries failed validation"
        );
        
        set_flash('Failed to save report. Please check your entries and try again.', 'error');
        header('Location: ../public/annex9.php');
        exit;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // AUDIT LOG: Database error
    log_audit_action(
        $user_id,
        'annex9_database_error',
        'Database error during Annex 9 submission: ' . $e->getMessage()
    );
    
    // SECURITY LOG: Database error
    log_security_event(
        $user_id,
        'database_error',
        'Database error in Annex 9 submission: ' . $e->getMessage()
    );
    
    error_log("Annex 9 save error: " . $e->getMessage());
    set_flash('Database error occurred. Please try again later.', 'error');
    header('Location: ../public/annex9.php');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // AUDIT LOG: Unexpected error
    log_audit_action(
        $user_id,
        'annex9_unexpected_error',
        'Unexpected error during Annex 9 submission: ' . $e->getMessage()
    );
    
    // SECURITY LOG: Unexpected error
    log_security_event(
        $user_id,
        'unexpected_error',
        'Unexpected error in Annex 9 submission: ' . $e->getMessage()
    );
    
    error_log("Annex 9 unexpected error: " . $e->getMessage());
    set_flash('An unexpected error occurred. Please try again later.', 'error');
    header('Location: ../public/annex9.php');
    exit;
}

// Final redirect fallback
header('Location: ../public/annex9.php');
exit;
?>
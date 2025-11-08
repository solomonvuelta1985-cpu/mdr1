<?php
/**
 * API Handler: Save Agriculture Damage Report (Annex 5)
 * FULLY SECURED with Audit Logs, Rate Limiting, and Security Logging
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
    'annex5_form_access',
    "Accessed Annex 5 save handler"
);

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_security_event($_SESSION['user_id'], 'invalid_request_method', 'Attempted non-POST request to annex5_save.php');
    set_flash('Invalid request method', 'error');
    header('Location: ../public/annex5.php');
    exit;
}

// CSRF token verification
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event(
        $_SESSION['user_id'],
        'csrf_validation_failed',
        'CSRF token validation failed for Annex 5 submission'
    );
    set_flash('Security token validation failed. Please try again.', 'error');
    header('Location: ../public/annex5.php');
    exit;
}

// Rate limiting check - FIX: Pass actual user_id
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex5_save', 20, 3600)) {
    log_security_event(
        $user_id,
        'rate_limit_exceeded',
        'Annex 5 submission rate limit exceeded (20 per hour)'
    );
    set_flash('Too many submissions. Please wait before submitting again.', 'error');
    header('Location: ../public/annex5.php');
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$user_data = get_user_location_data($user_id);
$user_barangay = sanitize($_POST['user_barangay'] ?? '');

// Verify barangay access for admins
if (is_admin()) {
    $current_lock = get_admin_locked_barangay($user_id);
    if (!$current_lock) {
        log_security_event(
            $user_id,
            'barangay_lock_missing',
            'Admin attempted Annex 5 submission without barangay lock'
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
$required_arrays = ['region', 'province', 'city', 'barangay', 'classification', 'type', 'affectedPeople', 'damageValue'];
foreach ($required_arrays as $field) {
    if (!isset($_POST[$field]) || !is_array($_POST[$field]) || empty($_POST[$field])) {
        log_security_event(
            $user_id,
            'invalid_form_data',
            "Missing or invalid field: $field in Annex 5 submission"
        );
        set_flash("Missing or invalid field: $field", 'error');
        header('Location: ../public/annex5.php');
        exit;
    }
}

// Get array data
$regions = $_POST['region'];
$provinces = $_POST['province'];
$cities = $_POST['city'];
$barangays = $_POST['barangay'];
$classifications = $_POST['classification'];
$types = $_POST['type'];
$affectedPeople = $_POST['affectedPeople'];
$damageValues = $_POST['damageValue'];

// Optional fields
$noRecoveryAreas = $_POST['noRecoveryArea'] ?? [];
$withRecoveryAreas = $_POST['withRecoveryArea'] ?? [];
$totalCropAreas = $_POST['totalCropArea'] ?? [];
$productionLossVolumes = $_POST['productionLossVolume'] ?? [];
$animalHeads = $_POST['animalHeads'] ?? [];
$fisheriesDetails = $_POST['fisheriesDetails'] ?? [];
$totallyDamaged = $_POST['totallyDamaged'] ?? [];
$partiallyDamaged = $_POST['partiallyDamaged'] ?? [];
$totalDamaged = $_POST['totalDamaged'] ?? [];

// Validate all arrays have same length
$entryCount = count($regions);
if (
    count($provinces) !== $entryCount ||
    count($cities) !== $entryCount ||
    count($barangays) !== $entryCount ||
    count($classifications) !== $entryCount ||
    count($types) !== $entryCount ||
    count($affectedPeople) !== $entryCount ||
    count($damageValues) !== $entryCount
) {
    log_security_event(
        $user_id,
        'data_manipulation_detected',
        'Array length mismatch in Annex 5 submission - possible data manipulation'
    );
    set_flash('Data mismatch detected. Please try again.', 'error');
    header('Location: ../public/annex5.php');
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
    header('Location: ../public/annex5.php');
    exit;
}

// Check for suspiciously low entry count (possible bot)
if ($entryCount === 0) {
    log_security_event(
        $user_id,
        'empty_submission',
        'Attempted to submit form with zero entries'
    );
    set_flash('Please add at least one entry.', 'error');
    header('Location: ../public/annex5.php');
    exit;
}

// Valid classifications
$valid_classifications = ['Crops', 'Livestock and Poultry', 'Fisheries', 'Agricultural Infrastructure', 'Machineries and Equipment'];

// Begin transaction
try {
    $pdo->beginTransaction();
    
    // Prepare insert statement - ENSURE id column is AUTO_INCREMENT
    $stmt = $pdo->prepare("
        INSERT INTO annex5_agriculture_damage (
            created_by, region, province, city, barangay,
            classification, type, affected_people, damage_value,
            no_recovery_area, with_recovery_area, total_crop_area,
            production_loss_volume, animal_heads, fisheries_details,
            totally_damaged, partially_damaged, total_damaged,
            created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
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
        $classification = sanitize($classifications[$i]);
        $type = sanitize($types[$i]);
        $affected = filter_var($affectedPeople[$i], FILTER_VALIDATE_INT);
        $damage = filter_var($damageValues[$i], FILTER_VALIDATE_FLOAT);
        
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
        
        // CRITICAL: Verify barangay matches user's barangay (prevent data manipulation)
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
        
        if (!in_array($classification, $valid_classifications)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid classification";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid classification '$classification'";
            continue;
        }
        
        if (empty($type) || strlen($type) > 100) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid type";
            $validation_failures[] = "Entry " . ($i + 1) . ": type validation failed";
            continue;
        }
        
        if ($affected === false || $affected < 0 || $affected > 1000000) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid affected people count";
            $validation_failures[] = "Entry " . ($i + 1) . ": affected people out of range";
            continue;
        }
        
        if ($damage === false || $damage < 0 || $damage > 999999999999.99) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid damage value";
            $validation_failures[] = "Entry " . ($i + 1) . ": damage value out of range";
            continue;
        }
        
        // Optional fields - sanitize and validate
        $noRecovery = isset($noRecoveryAreas[$i]) && $noRecoveryAreas[$i] !== '' ? filter_var($noRecoveryAreas[$i], FILTER_VALIDATE_FLOAT) : 0.00;
        $withRecovery = isset($withRecoveryAreas[$i]) && $withRecoveryAreas[$i] !== '' ? filter_var($withRecoveryAreas[$i], FILTER_VALIDATE_FLOAT) : 0.00;
        $totalCrop = isset($totalCropAreas[$i]) && $totalCropAreas[$i] !== '' ? filter_var($totalCropAreas[$i], FILTER_VALIDATE_FLOAT) : 0.00;
        $prodLossVol = isset($productionLossVolumes[$i]) && $productionLossVolumes[$i] !== '' ? filter_var($productionLossVolumes[$i], FILTER_VALIDATE_FLOAT) : 0.00;
        $heads = isset($animalHeads[$i]) && $animalHeads[$i] !== '' ? filter_var($animalHeads[$i], FILTER_VALIDATE_INT) : 0;
        $fishDetails = isset($fisheriesDetails[$i]) && $fisheriesDetails[$i] !== '' ? sanitize(substr($fisheriesDetails[$i], 0, 500)) : null;
        $totDamaged = isset($totallyDamaged[$i]) && $totallyDamaged[$i] !== '' ? filter_var($totallyDamaged[$i], FILTER_VALIDATE_INT) : 0;
        $partDamaged = isset($partiallyDamaged[$i]) && $partiallyDamaged[$i] !== '' ? filter_var($partiallyDamaged[$i], FILTER_VALIDATE_INT) : 0;
        $totTotal = isset($totalDamaged[$i]) && $totalDamaged[$i] !== '' ? filter_var($totalDamaged[$i], FILTER_VALIDATE_INT) : 0;
        
        // Validate optional numeric fields if present
        if ($noRecovery !== false && ($noRecovery < 0 || $noRecoery > 999999.99)) {
            $noRecovery = 0.00;
        }
        if ($withRecovery !== false && ($withRecovery < 0 || $withRecovery > 999999.99)) {
            $withRecovery = 0.00;
        }
        if ($totalCrop !== false && ($totalCrop < 0 || $totalCrop > 999999.99)) {
            $totalCrop = 0.00;
        }
        if ($prodLossVol !== false && ($prodLossVol < 0 || $prodLossVol > 999999.99)) {
            $prodLossVol = 0.00;
        }
        if ($heads !== false && ($heads < 0 || $heads > 9999999)) {
            $heads = 0;
        }
        if ($totDamaged !== false && ($totDamaged < 0 || $totDamaged > 999999)) {
            $totDamaged = 0;
        }
        if ($partDamaged !== false && ($partDamaged < 0 || $partDamaged > 999999)) {
            $partDamaged = 0;
        }
        if ($totTotal !== false && ($totTotal < 0 || $totTotal > 999999)) {
            $totTotal = 0;
        }
        
        // Execute insert
        $result = $stmt->execute([
            $user_id, $region, $province, $city, $barangay,
            $classification, $type, $affected, $damage,
            $noRecovery, $withRecovery, $totalCrop,
            $prodLossVol, $heads, $fishDetails,
            $totDamaged, $partDamaged, $totTotal
        ]);
        
        if ($result) {
            // Get the last inserted ID
            $last_id = $pdo->lastInsertId();
            $inserted_ids[] = $last_id;
            $inserted_count++;
            
            // SUCCESS SECURITY LOG - Individual record
            log_security_event(
                $user_id,
                'annex5_submission_success',
                "Successfully submitted Annex5 record #{$last_id} for {$barangay}: {$classification} - {$type}"
            );
            
            // Debug log
            error_log("Annex5 - Entry " . ($i + 1) . " inserted with ID: $last_id");
        } else {
            $errors[] = "Entry " . ($i + 1) . ": Failed to insert";
            error_log("Annex 5 insert failed for entry " . ($i + 1) . " - User: $user_id");
        }
    }

    // Log validation failures if any
    if (!empty($validation_failures)) {
        log_security_event(
            $user_id,
            'validation_failures',
            'Annex 5 submission had validation failures: ' . implode('; ', $validation_failures)
        );
    }
    
    // Commit transaction if at least one entry was successful
    if ($inserted_count > 0) {
        $pdo->commit();
        
        // Format inserted IDs for logging
        $id_list = implode(', ', $inserted_ids);
        
        // AUDIT LOG: Successful submission
        log_audit_action(
            $user_id,
            'annex5_submission',
            "Successfully submitted agriculture damage report with $inserted_count entries (IDs: $id_list) for barangay: $user_barangay" . 
            (count($errors) > 0 ? " (" . count($errors) . " entries failed validation)" : "")
        );
        
        // SUCCESS SECURITY LOG - Batch summary
        log_security_event(
            $user_id,
            'annex5_submission_success',
            "Successfully submitted Annex5 records #{$id_list} for {$user_barangay}"
        );
        
        // Debug log
        error_log("Annex5 - Total inserted: $inserted_count, IDs: $id_list");
        
        if (count($errors) > 0) {
            set_flash("Report saved with $inserted_count entries. " . count($errors) . " entries had errors.", 'warning');
        } else {
            set_flash("Agriculture damage report saved successfully! $inserted_count entries recorded.", 'success');
        }
        
        // Redirect to records page after successful submission
        header('Location: ../public/annex5_records.php');
        exit;
    } else {
        $pdo->rollBack();
        
        // AUDIT LOG: Failed submission
        log_audit_action(
            $user_id,
            'annex5_submission_failed',
            "Failed to submit Annex 5 report - all entries had validation errors"
        );
        
        set_flash('Failed to save report. Please check your entries and try again.', 'error');
        header('Location: ../public/annex5.php');
        exit;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // SECURITY LOG: Database error
    log_security_event(
        $user_id,
        'database_error',
        'Database error in Annex 5 submission: ' . $e->getMessage()
    );
    
    error_log("Annex 5 save error: " . $e->getMessage());
    set_flash('Database error occurred. Please try again later.', 'error');
    header('Location: ../public/annex5.php');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // SECURITY LOG: Unexpected error
    log_security_event(
        $user_id,
        'unexpected_error',
        'Unexpected error in Annex 5 submission: ' . $e->getMessage()
    );
    
    error_log("Annex 5 unexpected error: " . $e->getMessage());
    set_flash('An unexpected error occurred. Please try again later.', 'error');
    header('Location: ../public/annex5.php');
    exit;
}

// Final redirect fallback
header('Location: ../public/annex5.php');
exit;
?>
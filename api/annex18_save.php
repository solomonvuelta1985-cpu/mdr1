<?php
/**
 * API Handler: Save Pre-emptive Evacuation (Animals) Report (Annex 18)
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
    'annex18_form_access',
    "Accessed Annex 18 save handler"
);

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_security_event($_SESSION['user_id'], 'invalid_request_method', 'Attempted non-POST request to annex18_save.php');
    set_flash('Invalid request method', 'error');
    header('Location: ../public/annex18.php');
    exit;
}

// CSRF token verification
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event(
        $_SESSION['user_id'],
        'csrf_validation_failed',
        'CSRF token validation failed for Annex 18 submission'
    );
    set_flash('Security token validation failed. Please try again.', 'error');
    header('Location: ../public/annex18.php');
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex18_save', 20, 3600)) {
    log_security_event(
        $user_id,
        'rate_limit_exceeded',
        'Annex 18 submission rate limit exceeded (20 per hour)'
    );
    set_flash('Too many submissions. Please wait before submitting again.', 'error');
    header('Location: ../public/annex18.php');
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
            'Admin attempted Annex 18 submission without barangay lock'
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
$required_arrays = ['region', 'province', 'city', 'barangay', 'classification', 'type', 'quantity', 'evacuationReason'];
foreach ($required_arrays as $field) {
    if (!isset($_POST[$field]) || !is_array($_POST[$field]) || empty($_POST[$field])) {
        log_security_event(
            $user_id,
            'invalid_form_data',
            "Missing or invalid field: $field in Annex 18 submission"
        );
        set_flash("Missing or invalid field: $field", 'error');
        header('Location: ../public/annex18.php');
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
$quantities = $_POST['quantity'];
$evacuationReasons = $_POST['evacuationReason'];

// Optional fields
$shelterLocations = $_POST['shelterLocation'] ?? [];
$assistanceProviders = $_POST['assistanceProvider'] ?? [];
$remarks = $_POST['remarks'] ?? [];

// Validate all arrays have same length
$entryCount = count($regions);
if (
    count($provinces) !== $entryCount ||
    count($cities) !== $entryCount ||
    count($barangays) !== $entryCount ||
    count($classifications) !== $entryCount ||
    count($types) !== $entryCount ||
    count($quantities) !== $entryCount ||
    count($evacuationReasons) !== $entryCount
) {
    log_security_event(
        $user_id,
        'data_manipulation_detected',
        'Array length mismatch in Annex 18 submission - possible data manipulation'
    );
    set_flash('Data mismatch detected. Please try again.', 'error');
    header('Location: ../public/annex18.php');
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
    header('Location: ../public/annex18.php');
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
    header('Location: ../public/annex18.php');
    exit;
}

// Valid classifications and types
$valid_classifications = ['Livestock', 'Poultry'];
$valid_types = [
    'Livestock' => ['Pig', 'Cow', 'Goat', 'Horse'],
    'Poultry' => ['Chicken', 'Duck']
];
$valid_reasons = ['Expected Flooding', 'Volcanic Eruption', 'Typhoon', 'Storm Surge', 'Landslide Risk', 'Other'];
$valid_providers = ['LGU', 'DA', 'NGO', 'Private Sector', 'Community', 'Other'];

// Begin transaction
try {
    $pdo->beginTransaction();
    
    // Prepare insert statement
    $stmt = $pdo->prepare("
        INSERT INTO annex18_animal_evacuation (
            created_by, region, province, city, barangay,
            classification, type, quantity, evacuation_reason,
            shelter_location, assistance_provider, remarks,
            created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
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
        $quantity = filter_var($quantities[$i], FILTER_VALIDATE_INT);
        $evacuation_reason = sanitize($evacuationReasons[$i]);
        
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
        
        if (!in_array($classification, $valid_classifications)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid classification";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid classification '$classification'";
            continue;
        }
        
        if (empty($type) || !in_array($type, $valid_types[$classification] ?? [])) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid type for classification";
            $validation_failures[] = "Entry " . ($i + 1) . ": type '$type' not valid for classification '$classification'";
            continue;
        }
        
        if ($quantity === false || $quantity < 0 || $quantity > 999999) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid quantity";
            $validation_failures[] = "Entry " . ($i + 1) . ": quantity out of range";
            continue;
        }
        
        if (!in_array($evacuation_reason, $valid_reasons)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid evacuation reason";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid evacuation reason '$evacuation_reason'";
            continue;
        }
        
        // Optional fields - sanitize and validate
        $shelter_location = isset($shelterLocations[$i]) && $shelterLocations[$i] !== '' ? 
            sanitize(substr($shelterLocations[$i], 0, 255)) : null;
        $assistance_provider = isset($assistanceProviders[$i]) && $assistanceProviders[$i] !== '' ? 
            sanitize($assistanceProviders[$i]) : null;
        $remark = isset($remarks[$i]) && $remarks[$i] !== '' ? 
            sanitize(substr($remarks[$i], 0, 1000)) : null;
        
        // Validate assistance provider if provided
        if ($assistance_provider && !in_array($assistance_provider, $valid_providers)) {
            $assistance_provider = null;
        }
        
        // Execute insert
        $result = $stmt->execute([
            $user_id, $region, $province, $city, $barangay,
            $classification, $type, $quantity, $evacuation_reason,
            $shelter_location, $assistance_provider, $remark
        ]);
        
        if ($result) {
            // Get the last inserted ID
            $last_id = $pdo->lastInsertId();
            $inserted_ids[] = $last_id;
            $inserted_count++;
            
            // SUCCESS SECURITY LOG - Individual record
            log_security_event(
                $user_id,
                'annex18_submission_success',
                "Successfully submitted Annex18 record #{$last_id} for {$barangay}: {$classification} - {$type}"
            );
            
            // Debug log
            error_log("Annex18 - Entry " . ($i + 1) . " inserted with ID: $last_id");
        } else {
            $errors[] = "Entry " . ($i + 1) . ": Failed to insert";
            error_log("Annex 18 insert failed for entry " . ($i + 1) . " - User: $user_id");
        }
    }

    // Log validation failures if any
    if (!empty($validation_failures)) {
        log_security_event(
            $user_id,
            'validation_failures',
            'Annex 18 submission had validation failures: ' . implode('; ', $validation_failures)
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
            'annex18_submission',
            "Successfully submitted animal evacuation report with $inserted_count entries (IDs: $id_list) for barangay: $user_barangay" . 
            (count($errors) > 0 ? " (" . count($errors) . " entries failed validation)" : "")
        );
        
        // SUCCESS SECURITY LOG - Batch summary
        log_security_event(
            $user_id,
            'annex18_submission_success',
            "Successfully submitted Annex18 records #{$id_list} for {$user_barangay}"
        );
        
        // Debug log
        error_log("Annex18 - Total inserted: $inserted_count, IDs: $id_list");
        
        if (count($errors) > 0) {
            set_flash("Report saved with $inserted_count entries. " . count($errors) . " entries had errors.", 'warning');
        } else {
            set_flash("Animal evacuation report saved successfully! $inserted_count entries recorded.", 'success');
        }
        
        // Redirect to records page after successful submission
        header('Location: ../public/annex18_records.php');
        exit;
    } else {
        $pdo->rollBack();
        
        // AUDIT LOG: Failed submission
        log_audit_action(
            $user_id,
            'annex18_submission_failed',
            "Failed to submit Annex 18 report - all entries had validation errors"
        );
        
        set_flash('Failed to save report. Please check your entries and try again.', 'error');
        header('Location: ../public/annex18.php');
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
        'Database error in Annex 18 submission: ' . $e->getMessage()
    );
    
    error_log("Annex 18 save error: " . $e->getMessage());
    set_flash('Database error occurred. Please try again later.', 'error');
    header('Location: ../public/annex18.php');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // SECURITY LOG: Unexpected error
    log_security_event(
        $user_id,
        'unexpected_error',
        'Unexpected error in Annex 18 submission: ' . $e->getMessage()
    );
    
    error_log("Annex 18 unexpected error: " . $e->getMessage());
    set_flash('An unexpected error occurred. Please try again later.', 'error');
    header('Location: ../public/annex18.php');
    exit;
}

// Final redirect fallback
header('Location: ../public/annex18.php');
exit;
?>
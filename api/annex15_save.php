<?php
/**
 * API Handler: Save Suspension of Classes Report (Annex 15)
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
// Annex Access Control - Only Admin and Sheila can add/edit
require_once __DIR__ . '/../includes/check_annex_access.php';
if (!can_access_annex('15')) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to modify Annex 15 records.']);
    exit;
}


// SECURITY LOG: Access attempt
log_audit_action(
    $_SESSION['user_id'],
    'annex15_form_access',
    "Accessed Annex 15 save handler"
);

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_security_event($_SESSION['user_id'], 'invalid_request_method', 'Attempted non-POST request to annex15_save.php');
    set_flash('Invalid request method', 'error');
    header('Location: ../public/annex15.php');
    exit;
}

// CSRF token verification
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event(
        $_SESSION['user_id'],
        'csrf_validation_failed',
        'CSRF token validation failed for Annex 15 submission'
    );
    set_flash('Security token validation failed. Please try again.', 'error');
    header('Location: ../public/annex15.php');
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex15_save', 20, 3600)) {
    log_security_event(
        $user_id,
        'rate_limit_exceeded',
        'Annex 15 submission rate limit exceeded (20 per hour)'
    );
    set_flash('Too many submissions. Please wait before submitting again.', 'error');
    header('Location: ../public/annex15.php');
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
            'Admin attempted Annex 15 submission without barangay lock'
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
$required_arrays = ['region', 'province', 'city', 'barangay', 'level', 'type', 'suspensionDate', 'suspensionReason', 'issuingAuthority', 'suspensionScope', 'alternativeDelivery'];
foreach ($required_arrays as $field) {
    if (!isset($_POST[$field]) || !is_array($_POST[$field]) || empty($_POST[$field])) {
        log_security_event(
            $user_id,
            'invalid_form_data',
            "Missing or invalid field: $field in Annex 15 submission"
        );
        set_flash("Missing or invalid field: $field", 'error');
        header('Location: ../public/annex15.php');
        exit;
    }
}

// Get array data
$regions = $_POST['region'];
$provinces = $_POST['province'];
$cities = $_POST['city'];
$barangays = $_POST['barangay'];
$levels = $_POST['level'];
$types = $_POST['type'];
$suspensionDates = $_POST['suspensionDate'];
$resumptionDates = $_POST['resumptionDate'] ?? [];
$suspensionReasons = $_POST['suspensionReason'];
$issuingAuthorities = $_POST['issuingAuthority'];
$suspensionScopes = $_POST['suspensionScope'];
$alternativeDeliveries = $_POST['alternativeDelivery'];
$remarks = $_POST['remarks'] ?? [];

// Validate all arrays have same length
$entryCount = count($regions);
if (
    count($provinces) !== $entryCount ||
    count($cities) !== $entryCount ||
    count($barangays) !== $entryCount ||
    count($levels) !== $entryCount ||
    count($types) !== $entryCount ||
    count($suspensionDates) !== $entryCount ||
    count($suspensionReasons) !== $entryCount ||
    count($issuingAuthorities) !== $entryCount ||
    count($suspensionScopes) !== $entryCount ||
    count($alternativeDeliveries) !== $entryCount
) {
    log_security_event(
        $user_id,
        'data_manipulation_detected',
        'Array length mismatch in Annex 15 submission - possible data manipulation'
    );
    set_flash('Data mismatch detected. Please try again.', 'error');
    header('Location: ../public/annex15.php');
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
    header('Location: ../public/annex15.php');
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
    header('Location: ../public/annex15.php');
    exit;
}

// Valid levels, types, etc.
$valid_levels = ['Preschool / Kindergarten', 'Elementary', 'Secondary / High School', 'Senior High School', 'Tertiary / College / University', 'All Levels'];
$valid_types = ['Public', 'Private', 'All'];
$valid_reasons = ['Typhoon Signal', 'Heavy Rainfall / Flooding', 'Earthquake', 'Volcanic Activity', 'Transport Strike', 'Health Emergency', 'Security Concern', 'Other'];
$valid_authorities = ['LGU Executive Order', 'DepEd Advisory', 'CHED Memorandum', 'School Administration', 'Other'];
$valid_scopes = ['Whole Day', 'Half Day (Morning)', 'Half Day (Afternoon)', 'Specific Levels Only', 'All Schools in Area'];
$valid_alternatives = ['None', 'Modular Learning', 'Online Classes', 'Asynchronous Activities', 'Make-up Classes'];

// Begin transaction
try {
    $pdo->beginTransaction();
    
    // Prepare insert statement
    $stmt = $pdo->prepare("
        INSERT INTO annex15_suspension_classes (
            created_by, region, province, city, barangay,
            level, type, suspension_date, resumption_date,
            suspension_reason, issuing_authority, suspension_scope,
            alternative_delivery, remarks, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?,
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
        $level = sanitize($levels[$i]);
        $type = sanitize($types[$i]);
        $suspension_date = sanitize($suspensionDates[$i]);
        $resumption_date = isset($resumptionDates[$i]) && !empty($resumptionDates[$i]) ? sanitize($resumptionDates[$i]) : null;
        $suspension_reason = sanitize($suspensionReasons[$i]);
        $issuing_authority = sanitize($issuingAuthorities[$i]);
        $suspension_scope = sanitize($suspensionScopes[$i]);
        $alternative_delivery = sanitize($alternativeDeliveries[$i]);
        $remark = isset($remarks[$i]) ? sanitize(substr($remarks[$i], 0, 1000)) : null;
        
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
        
        if (!in_array($level, $valid_levels)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid level";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid level '$level'";
            continue;
        }
        
        if (!in_array($type, $valid_types)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid type";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid type '$type'";
            continue;
        }
        
        if (empty($suspension_date)) {
            $errors[] = "Entry " . ($i + 1) . ": Suspension date is required";
            $validation_failures[] = "Entry " . ($i + 1) . ": suspension date missing";
            continue;
        }
        
        if (!in_array($suspension_reason, $valid_reasons)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid suspension reason";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid suspension reason '$suspension_reason'";
            continue;
        }
        
        if (!in_array($issuing_authority, $valid_authorities)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid issuing authority";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid issuing authority '$issuing_authority'";
            continue;
        }
        
        if (!in_array($suspension_scope, $valid_scopes)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid suspension scope";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid suspension scope '$suspension_scope'";
            continue;
        }
        
        if (!in_array($alternative_delivery, $valid_alternatives)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid alternative delivery";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid alternative delivery '$alternative_delivery'";
            continue;
        }
        
        // Execute insert
        $result = $stmt->execute([
            $user_id, $region, $province, $city, $barangay,
            $level, $type, $suspension_date, $resumption_date,
            $suspension_reason, $issuing_authority, $suspension_scope,
            $alternative_delivery, $remark
        ]);
        
        if ($result) {
            // Get the last inserted ID
            $last_id = $pdo->lastInsertId();
            $inserted_ids[] = $last_id;
            $inserted_count++;
            
            // SUCCESS SECURITY LOG - Individual record
            log_security_event(
                $user_id,
                'annex15_submission_success',
                "Successfully submitted Annex15 record #{$last_id} for {$barangay}: {$level} - {$type}"
            );
            
            // Debug log
            error_log("Annex15 - Entry " . ($i + 1) . " inserted with ID: $last_id");
        } else {
            $errors[] = "Entry " . ($i + 1) . ": Failed to insert";
            error_log("Annex 15 insert failed for entry " . ($i + 1) . " - User: $user_id");
        }
    }

    // Log validation failures if any
    if (!empty($validation_failures)) {
        log_security_event(
            $user_id,
            'validation_failures',
            'Annex 15 submission had validation failures: ' . implode('; ', $validation_failures)
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
            'annex15_submission',
            "Successfully submitted suspension of classes report with $inserted_count entries (IDs: $id_list) for barangay: $user_barangay" . 
            (count($errors) > 0 ? " (" . count($errors) . " entries failed validation)" : "")
        );
        
        // SUCCESS SECURITY LOG - Batch summary
        log_security_event(
            $user_id,
            'annex15_submission_success',
            "Successfully submitted Annex15 records #{$id_list} for {$user_barangay}"
        );
        
        // Debug log
        error_log("Annex15 - Total inserted: $inserted_count, IDs: $id_list");
        
        if (count($errors) > 0) {
            set_flash("Report saved with $inserted_count entries. " . count($errors) . " entries had errors.", 'warning');
        } else {
            set_flash("Suspension of classes report saved successfully! $inserted_count entries recorded.", 'success');
        }
        
        // Redirect to records page after successful submission
        header('Location: ../public/annex15_records.php');
        exit;
    } else {
        $pdo->rollBack();
        
        // AUDIT LOG: Failed submission
        log_audit_action(
            $user_id,
            'annex15_submission_failed',
            "Failed to submit Annex 15 report - all entries had validation errors"
        );
        
        set_flash('Failed to save report. Please check your entries and try again.', 'error');
        header('Location: ../public/annex15.php');
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
        'Database error in Annex 15 submission: ' . $e->getMessage()
    );
    
    error_log("Annex 15 save error: " . $e->getMessage());
    set_flash('Database error occurred. Please try again later.', 'error');
    header('Location: ../public/annex15.php');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // SECURITY LOG: Unexpected error
    log_security_event(
        $user_id,
        'unexpected_error',
        'Unexpected error in Annex 15 submission: ' . $e->getMessage()
    );
    
    error_log("Annex 15 unexpected error: " . $e->getMessage());
    set_flash('An unexpected error occurred. Please try again later.', 'error');
    header('Location: ../public/annex15.php');
    exit;
}

// Final redirect fallback
header('Location: ../public/annex15.php');
exit;
?>
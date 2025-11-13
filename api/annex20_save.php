<?php
/**
 * API Handler: Save Assistance Provided to Families Report (Annex 20)
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
if (!can_access_annex('20')) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to modify Annex 20 records.']);
    exit;
}


// SECURITY LOG: Access attempt
log_audit_action(
    $_SESSION['user_id'],
    'annex20_form_access',
    "Accessed Annex 20 save handler"
);

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_security_event($_SESSION['user_id'], 'invalid_request_method', 'Attempted non-POST request to annex20_save.php');
    set_flash('Invalid request method', 'error');
    header('Location: ../public/annex20.php');
    exit;
}

// CSRF token verification
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event(
        $_SESSION['user_id'],
        'csrf_validation_failed',
        'CSRF token validation failed for Annex 20 submission'
    );
    set_flash('Security token validation failed. Please try again.', 'error');
    header('Location: ../public/annex20.php');
    exit;
}

// Rate limiting check
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex20_save', 20, 3600)) {
    log_security_event(
        $user_id,
        'rate_limit_exceeded',
        'Annex 20 submission rate limit exceeded (20 per hour)'
    );
    set_flash('Too many submissions. Please wait before submitting again.', 'error');
    header('Location: ../public/annex20.php');
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
            'Admin attempted Annex 20 submission without barangay lock'
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
$required_arrays = ['region', 'province', 'city', 'cluster', 'type', 'quantity', 'unit', 'costPerUnit', 'amount'];
foreach ($required_arrays as $field) {
    if (!isset($_POST[$field]) || !is_array($_POST[$field]) || empty($_POST[$field])) {
        log_security_event(
            $user_id,
            'invalid_form_data',
            "Missing or invalid field: $field in Annex 20 submission"
        );
        set_flash("Missing or invalid field: $field", 'error');
        header('Location: ../public/annex20.php');
        exit;
    }
}

// Get array data
$regions = $_POST['region'];
$provinces = $_POST['province'];
$cities = $_POST['city'];
$clusters = $_POST['cluster'];
$types = $_POST['type'];
$quantities = $_POST['quantity'];
$units = $_POST['unit'];
$costPerUnits = $_POST['costPerUnit'];
$amounts = $_POST['amount'];

// Optional fields
$remarks = $_POST['remarks'] ?? [];

// Validate all arrays have same length
$entryCount = count($regions);
if (
    count($provinces) !== $entryCount ||
    count($cities) !== $entryCount ||
    count($clusters) !== $entryCount ||
    count($types) !== $entryCount ||
    count($quantities) !== $entryCount ||
    count($units) !== $entryCount ||
    count($costPerUnits) !== $entryCount ||
    count($amounts) !== $entryCount
) {
    log_security_event(
        $user_id,
        'data_manipulation_detected',
        'Array length mismatch in Annex 20 submission - possible data manipulation'
    );
    set_flash('Data mismatch detected. Please try again.', 'error');
    header('Location: ../public/annex20.php');
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
    header('Location: ../public/annex20.php');
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
    header('Location: ../public/annex20.php');
    exit;
}

// Valid clusters
$valid_clusters = [
    'Food', 'WASH', 'Shelter', 'Health', 'Protection',
    'Education', 'Nutrition', 'Camp Management',
    'Emergency Telecommunications', 'Logistics'
];

// Valid assistance types
$valid_types = [
    'Food packs', 'Hygiene kits', 'Water containers',
    'Temporary shelter materials', 'Medicines', 'Blankets',
    'Sleeping mats', 'Cooking utensils', 'Mosquito nets',
    'Water purification tablets', 'Other'
];

// Begin transaction
try {
    $pdo->beginTransaction();

    // Prepare insert statement
    $stmt = $pdo->prepare("
        INSERT INTO annex20_assistance (
            created_by, region, province, city, barangay,
            cluster, type, quantity, unit, cost_per_unit,
            amount, remarks,
            created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
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
        $cluster = sanitize($clusters[$i]);
        $type = sanitize($types[$i]);
        $quantity = filter_var($quantities[$i], FILTER_VALIDATE_INT);
        $unit = sanitize($units[$i]);
        $costPerUnit = filter_var($costPerUnits[$i], FILTER_VALIDATE_FLOAT);
        $amount = filter_var($amounts[$i], FILTER_VALIDATE_FLOAT);

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

        if (!in_array($cluster, $valid_clusters)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid cluster";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid cluster '$cluster'";
            continue;
        }

        if (!in_array($type, $valid_types)) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid type";
            $validation_failures[] = "Entry " . ($i + 1) . ": invalid type '$type'";
            continue;
        }

        if ($quantity === false || $quantity < 0 || $quantity > 1000000) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid quantity";
            $validation_failures[] = "Entry " . ($i + 1) . ": quantity out of range";
            continue;
        }

        if (empty($unit) || strlen($unit) > 50) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid unit";
            $validation_failures[] = "Entry " . ($i + 1) . ": unit validation failed";
            continue;
        }

        if ($costPerUnit === false || $costPerUnit < 0 || $costPerUnit > 999999999.99) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid cost per unit";
            $validation_failures[] = "Entry " . ($i + 1) . ": cost per unit out of range";
            continue;
        }

        if ($amount === false || $amount < 0 || $amount > 999999999999.99) {
            $errors[] = "Entry " . ($i + 1) . ": Invalid amount";
            $validation_failures[] = "Entry " . ($i + 1) . ": amount out of range";
            continue;
        }

        // Validate calculated amount matches
        $calculated_amount = $quantity * $costPerUnit;
        if (abs($calculated_amount - $amount) > 0.02) { // Allow 2 cent tolerance for rounding
            $errors[] = "Entry " . ($i + 1) . ": Amount calculation mismatch";
            $validation_failures[] = "Entry " . ($i + 1) . ": amount mismatch (expected " . number_format($calculated_amount, 2) . ", got " . number_format($amount, 2) . ")";
            continue;
        }

        // Optional fields - sanitize
        $remark = isset($remarks[$i]) && $remarks[$i] !== '' ? sanitize(substr($remarks[$i], 0, 500)) : null;

        // Execute insert
        $result = $stmt->execute([
            $user_id, $region, $province, $city, $user_barangay,
            $cluster, $type, $quantity, $unit, $costPerUnit,
            $amount, $remark
        ]);

        if ($result) {
            // Get the last inserted ID
            $last_id = $pdo->lastInsertId();
            $inserted_ids[] = $last_id;
            $inserted_count++;

            // SUCCESS SECURITY LOG - Individual record
            log_security_event(
                $user_id,
                'annex20_submission_success',
                "Successfully submitted Annex20 record #{$last_id} for {$user_barangay}: {$cluster} - {$type} ({$quantity} {$unit})"
            );

            // Debug log
            error_log("Annex20 - Entry " . ($i + 1) . " inserted with ID: $last_id");
        } else {
            $errors[] = "Entry " . ($i + 1) . ": Failed to insert";
            error_log("Annex 20 insert failed for entry " . ($i + 1) . " - User: $user_id");
        }
    }

    // Log validation failures if any
    if (!empty($validation_failures)) {
        log_security_event(
            $user_id,
            'validation_failures',
            'Annex 20 submission had validation failures: ' . implode('; ', $validation_failures)
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
            'annex20_submission',
            "Successfully submitted assistance report with $inserted_count entries (IDs: $id_list) for barangay: $user_barangay" .
            (count($errors) > 0 ? " (" . count($errors) . " entries failed validation)" : "")
        );

        // SUCCESS SECURITY LOG - Batch summary
        log_security_event(
            $user_id,
            'annex20_submission_success',
            "Successfully submitted Annex20 records #{$id_list} for {$user_barangay}"
        );

        // Debug log
        error_log("Annex20 - Total inserted: $inserted_count, IDs: $id_list");

        if (count($errors) > 0) {
            set_flash("Report saved with $inserted_count entries. " . count($errors) . " entries had errors.", 'warning');
        } else {
            set_flash("Assistance report saved successfully! $inserted_count entries recorded.", 'success');
        }

        // Redirect to records page after successful submission
        header('Location: ../public/annex20_records.php');
        exit;
    } else {
        $pdo->rollBack();

        // AUDIT LOG: Failed submission
        log_audit_action(
            $user_id,
            'annex20_submission_failed',
            "Failed to submit Annex 20 report - all entries had validation errors"
        );

        set_flash('Failed to save report. Please check your entries and try again.', 'error');
        header('Location: ../public/annex20.php');
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
        'Database error in Annex 20 submission: ' . $e->getMessage()
    );

    error_log("Annex 20 save error: " . $e->getMessage());
    set_flash('Database error occurred. Please try again later.', 'error');
    header('Location: ../public/annex20.php');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // SECURITY LOG: Unexpected error
    log_security_event(
        $user_id,
        'unexpected_error',
        'Unexpected error in Annex 20 submission: ' . $e->getMessage()
    );

    error_log("Annex 20 unexpected error: " . $e->getMessage());
    set_flash('An unexpected error occurred. Please try again later.', 'error');
    header('Location: ../public/annex20.php');
    exit;
}

// Final redirect fallback
header('Location: ../public/annex20.php');
exit;
?>

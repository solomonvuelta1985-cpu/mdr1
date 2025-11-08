<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

// Simulated delay (optional)
sleep(1);

// JSON response header
header('Content-Type: application/json');

//   Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex1 save CSRF token mismatch');
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

//   Rate limiting (10 submissions/hour)
$action = 'annex1_submission';
if (!check_rate_limit($_SESSION['user_id'], $action, 10, 3600)) {
    log_security_event($_SESSION['user_id'], 'rate_limit_exceeded', 'Annex1 submission rate limit exceeded');
    echo json_encode(['success' => false, 'message' => 'Too many submission attempts. Please try again later.']);
    exit;
}

//   Get user location and barangay
$user_location = get_user_location_data($_SESSION['user_id']);
$is_admin = ($_SESSION['user_role'] === 'admin');

if ($is_admin) {
    $current_lock = get_admin_locked_barangay($_SESSION['user_id']);
    if (!$current_lock) {
        echo json_encode(['success' => false, 'message' => 'Please select a barangay first in admin dashboard']);
        exit;
    }
    $user_barangay = $current_lock;
} else {
    $user_barangay = $user_location['barangay'];
}

//   Start transaction
try {
    $pdo->beginTransaction();

    $success_count = 0;
    $error_count = 0;
    $saved_ids = [];
    $submitted_barangays = [];

    // Process each record
    if (isset($_POST['region']) && is_array($_POST['region'])) {
        foreach ($_POST['region'] as $index => $region) {

            // Skip empty rows
            if (empty($region) && empty($_POST['province'][$index]) && empty($_POST['city'][$index])) {
                log_security_event($_SESSION['user_id'], 'validation_skip', "Skipped empty entry at index {$index}");
                continue;
            }

            // Sanitize inputs
            $region = sanitize($region);
            $province = sanitize($_POST['province'][$index] ?? '');
            $city = sanitize($_POST['city'][$index] ?? '');
            $barangay = $is_admin && isset($_POST['admin_barangay'])
                ? sanitize($_POST['admin_barangay'])
                : sanitize($_POST['barangay'][$index] ?? $user_barangay);

            $incident_type = sanitize($_POST['incidentType'][$index] ?? '');
            $occurrence_date = !empty($_POST['occurrenceDate'][$index])
                ? date('Y-m-d H:i:s', strtotime($_POST['occurrenceDate'][$index]))
                : null;
            $description = sanitize($_POST['description'][$index] ?? '');
            $actions_taken = sanitize($_POST['actionsTaken'][$index] ?? '');
            $remarks = sanitize($_POST['remarks'][$index] ?? '');

            // Validate required fields
            if (empty($region) || empty($province) || empty($city) || empty($barangay) || empty($incident_type)) {
                $error_count++;
                log_security_event($_SESSION['user_id'], 'validation_skip', "Annex1 entry index {$index} missing required fields");
                continue;
            }

            // Validate barangay access
            if (!$is_admin && $barangay !== $user_barangay) {
                $error_count++;
                log_security_event($_SESSION['user_id'], 'unauthorized_barangay', "Attempt to submit to {$barangay} denied for user {$user_barangay}");
                continue;
            }

            //   Insert into database
            $stmt = db_query("
                INSERT INTO annex1_related_incidents 
                (region, province, city, barangay, incident_type, occurrence_date, description, actions_taken, remarks, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $region, $province, $city, $barangay, $incident_type,
                $occurrence_date, $description, $actions_taken, $remarks, $_SESSION['user_id']
            ]);

            if ($stmt && $stmt->rowCount() > 0) {
                $success_count++;
                $saved_ids[] = $pdo->lastInsertId();
                $submitted_barangays[] = $barangay;

                //   Audit log
                log_audit_action(
                    $_SESSION['user_id'],
                    'annex1_submission',
                    "Submitted incident report for {$barangay}: {$incident_type} ({$occurrence_date})"
                );

                // Optional: per-entry rate-limit count
                // check_rate_limit($_SESSION['user_id'], $action, 10, 3600);

            } else {
                $error_count++;
                log_security_event($_SESSION['user_id'], 'db_insert_failed', "Failed to insert Annex1 entry index {$index}");
            }
        }
    }

    $pdo->commit();

    //   Batch audit summary
    if ($success_count > 0) {
        $barangays_str = implode(', ', array_unique($submitted_barangays));
        log_audit_action(
            $_SESSION['user_id'],
            'annex1_batch_submission',
            "Successfully submitted {$success_count} incident reports for barangays: {$barangays_str}"
        );
    }

    //   Response
    if ($success_count > 0) {
        echo json_encode([
            'success' => true,
            'message' => "Successfully saved {$success_count} incident report(s)",
            'data' => [
                'success_count' => $success_count,
                'error_count' => $error_count,
                'saved_ids' => $saved_ids
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Failed to save any incident reports. {$error_count} error(s) occurred."
        ]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Annex1 save error: " . $e->getMessage());
    log_security_event(
        $_SESSION['user_id'],
        'annex1_submission_error',
        "Database or runtime error: " . $e->getMessage()
    );

    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred while saving reports: ' . $e->getMessage()
    ]);
}
?>

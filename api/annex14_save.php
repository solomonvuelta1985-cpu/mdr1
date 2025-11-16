<?php
// Start session and include required files
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in
require_login();
// Annex Access Control - Only Admin and Sheila can add/edit
require_once __DIR__ . '/../includes/check_annex_access.php';
if (!can_access_annex('14')) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to modify Annex 14 records.']);
    exit;
}


// Set JSON header
header('Content-Type: application/json');

// Handle both POST and GET
$requestData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestData = $_POST;
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    $requestData = $_GET;
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit;
}

// Debug: Log request start
error_log("ANNEX14_DEBUG: Starting submission for user " . $_SESSION['user_id']);

// Verify CSRF token
if (!verify_token($requestData['csrf_token'] ?? '')) {
    error_log("ANNEX14_DEBUG: CSRF token validation failed");
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

// Rate limiting check
if (!check_rate_limit($_SESSION['user_id'], 'annex14_submission', 10, 3600)) {
    error_log("ANNEX14_DEBUG: Rate limit exceeded for user " . $_SESSION['user_id']);
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many submissions. Please try again later.']);
    exit;
}

try {
    // Debug: Check database connection
    error_log("ANNEX14_DEBUG: Testing database connection");
    $test_stmt = db_query("SELECT 1 as test");
    if (!$test_stmt) {
        throw new Exception("Database connection failed");
    }
    error_log("ANNEX14_DEBUG: Database connection OK");

    // Begin transaction
    $pdo->beginTransaction();
    error_log("ANNEX14_DEBUG: Transaction started");

    // Get user data
    $user_data = get_user_location_data($_SESSION['user_id']);

    // Annex 14 covers the entire municipality - no barangay locking required
    error_log("ANNEX14_DEBUG: Municipality-wide submission");

    // Validate input arrays
    $regions = $requestData['region'] ?? [];
    $provinces = $requestData['province'] ?? [];
    $cities = $requestData['city'] ?? [];
    $barangays = $requestData['barangay'] ?? [];
    $types = $requestData['type'] ?? [];
    $suspensionDates = $requestData['suspensionDate'] ?? [];
    $resumptionDates = $requestData['resumptionDate'] ?? [];
    $remarks = $requestData['remarks'] ?? [];

    error_log("ANNEX14_DEBUG: Received " . count($regions) . " entries");

    // Validate array lengths
    $entryCount = count($regions);
    if ($entryCount !== count($provinces) || $entryCount !== count($cities) || 
        $entryCount !== count($barangays) || $entryCount !== count($types) || 
        $entryCount !== count($suspensionDates)) {
        throw new Exception('Invalid form data: array length mismatch');
    }

    // Limit number of entries to prevent abuse
    if ($entryCount > 50) {
        throw new Exception('Too many entries. Maximum 50 entries allowed.');
    }

    $successCount = 0;
    $errors = [];

    // Process each entry
    for ($i = 0; $i < $entryCount; $i++) {
        // Skip empty required fields
        if (empty($types[$i]) || empty($suspensionDates[$i])) {
            error_log("ANNEX14_DEBUG: Skipping entry $i - missing required fields");
            continue;
        }

        // Sanitize inputs
        $region = sanitize($regions[$i]);
        $province = sanitize($provinces[$i]);
        $city = sanitize($cities[$i]);
        $barangay = sanitize($barangays[$i]);
        $type = sanitize($types[$i]);
        $suspensionDate = sanitize($suspensionDates[$i]);
        $resumptionDate = !empty($resumptionDates[$i]) ? sanitize($resumptionDates[$i]) : null;
        $remark = !empty($remarks[$i]) ? sanitize($remarks[$i]) : null;

        error_log("ANNEX14_DEBUG: Processing entry $i - Barangay: $barangay, Type: $type");

        // Validate type
        $allowedTypes = ['Government', 'Private', 'All'];
        if (!in_array($type, $allowedTypes)) {
            $errors[] = "Invalid suspension type at entry " . ($i + 1);
            error_log("ANNEX14_DEBUG: Invalid type: $type");
            continue;
        }

        // Validate dates
        $suspensionTimestamp = strtotime($suspensionDate);
        if (!$suspensionTimestamp) {
            $errors[] = "Invalid suspension date at entry " . ($i + 1);
            error_log("ANNEX14_DEBUG: Invalid suspension date: $suspensionDate");
            continue;
        }

        if ($resumptionDate) {
            $resumptionTimestamp = strtotime($resumptionDate);
            if (!$resumptionTimestamp) {
                $errors[] = "Invalid resumption date at entry " . ($i + 1);
                error_log("ANNEX14_DEBUG: Invalid resumption date: $resumptionDate");
                continue;
            }

            // Check if resumption is after suspension
            if ($resumptionTimestamp <= $suspensionTimestamp) {
                $errors[] = "Resumption date must be after suspension date at entry " . ($i + 1);
                continue;
            }
        }

        // Annex 14 is municipality-wide - no barangay access restriction
        error_log("ANNEX14_DEBUG: Processing municipality-wide entry " . ($i + 1));

        // Insert into database
        error_log("ANNEX14_DEBUG: Attempting to insert into annex14_suspension_work");
        $stmt = db_query("
            INSERT INTO annex14_suspension_work 
            (region, province, city, barangay, type, suspension_date, resumption_date, remarks, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $region, $province, $city, $barangay, $type, 
            date('Y-m-d H:i:s', $suspensionTimestamp),
            $resumptionDate ? date('Y-m-d H:i:s', $resumptionTimestamp) : null,
            $remark, $_SESSION['user_id']
        ]);

        if ($stmt && $stmt->rowCount() > 0) {
            $successCount++;
            error_log("ANNEX14_DEBUG: Successfully inserted entry $i, ID: " . $pdo->lastInsertId());

            // TEST AUDIT LOG - Try direct insertion first
            error_log("ANNEX14_DEBUG: Attempting audit log for entry $i");
            
            // Test direct audit log insertion
            $audit_result = log_audit_action(
                $_SESSION['user_id'], 
                'annex14_submission', 
                "Submitted suspension work report for {$barangay}: {$type}"
            );
            
            error_log("ANNEX14_DEBUG: Audit log result: " . ($audit_result ? 'SUCCESS' : 'FAILED'));
            
        } else {
            $errors[] = "Failed to save entry " . ($i + 1);
            error_log("ANNEX14_DEBUG: Failed to insert entry $i");
        }
    }

    // Commit transaction
    $pdo->commit();
    error_log("ANNEX14_DEBUG: Transaction committed successfully");

    if ($successCount > 0) {
        // Log security event for successful submission
        error_log("ANNEX14_DEBUG: Attempting security event log");
        $security_result = log_security_event(
            $_SESSION['user_id'], 
            'annex14_success', 
            "Successfully submitted {$successCount} suspension work entries"
        );
        error_log("ANNEX14_DEBUG: Security event result: " . ($security_result ? 'SUCCESS' : 'FAILED'));

        echo json_encode([
            'success' => true, 
            'message' => "Successfully saved {$successCount} suspension work entries.",
            'saved_count' => $successCount,
            'errors' => $errors
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'No valid entries were saved.',
            'errors' => $errors
        ]);
    }

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
        error_log("ANNEX14_DEBUG: Transaction rolled back due to error");
    }

    // Log error
    error_log("ANNEX14_DEBUG: Exception caught: " . $e->getMessage());
    
    // Log security event for failed submission
    error_log("ANNEX14_DEBUG: Attempting security event log for error");
    $security_result = log_security_event(
        $_SESSION['user_id'], 
        'annex14_error', 
        "Submission failed: " . $e->getMessage()
    );
    error_log("ANNEX14_DEBUG: Security event error log result: " . ($security_result ? 'SUCCESS' : 'FAILED'));

    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while saving the report: ' . $e->getMessage()
    ]);
}

// Final debug log
error_log("ANNEX14_DEBUG: Script execution completed");
?>
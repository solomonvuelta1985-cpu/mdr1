<?php
/**
 * API Handler: Update Power Supply Status Record (Annex 9)
 * WITH IMAGE UPLOAD SUPPORT - COMPLETE WORKING VERSION
 */

// Enable detailed error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("=== ANNEX9 UPDATE STARTED ===");

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once '../includes/file_security.php'; // SECURITY FIX: Secure file operations
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

error_log("User ID: " . $_SESSION['user_id']);
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("ERROR: Not POST method");
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// CSRF token verification
if (!verify_token($_POST['csrf_token'] ?? '')) {
    error_log("ERROR: CSRF validation failed");
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit;
}

// Get record ID
$record_id = filter_var($_POST['record_id'] ?? 0, FILTER_VALIDATE_INT);
if (!$record_id) {
    error_log("ERROR: Invalid record ID");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
    exit;
}

error_log("Record ID: " . $record_id);

// Check permissions
$is_admin = ($_SESSION['user_role'] === 'admin');
$user_id = $_SESSION['user_id'];

if ($is_admin) {
    $stmt = db_query("SELECT * FROM annex9_power_supply WHERE id = ?", [$record_id]);
} else {
    $stmt = db_query("SELECT * FROM annex9_power_supply WHERE id = ? AND created_by = ?", [$record_id, $user_id]);
}

$current_record = $stmt->fetch();

if (!$current_record) {
    error_log("ERROR: Record not found or access denied");
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Record not found or access denied']);
    exit;
}

error_log("Current record found. Current image: " . ($current_record['image_path'] ?: 'none'));

try {
    // Validate required fields
    $required = ['region', 'province', 'city', 'barangay', 'service_provider', 'interruption_date'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            error_log("ERROR: Missing field: " . $field);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing: $field"]);
            exit;
        }
    }

    // Sanitize input
    $region = sanitize($_POST['region']);
    $province = sanitize($_POST['province']);
    $city = sanitize($_POST['city']);
    $barangay = sanitize($_POST['barangay']);
    $service_provider = sanitize($_POST['service_provider']);
    $interruption_date = sanitize($_POST['interruption_date']);
    $restored_date = !empty($_POST['restored_date']) ? sanitize($_POST['restored_date']) : null;
    $remarks = !empty($_POST['remarks']) ? sanitize(substr($_POST['remarks'], 0, 500)) : null;

    // Validate datetime
    $interruption_obj = DateTime::createFromFormat('Y-m-d\TH:i', $interruption_date);
    if (!$interruption_obj) {
        error_log("ERROR: Invalid interruption date");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid interruption date']);
        exit;
    }
    $interruption_datetime = $interruption_obj->format('Y-m-d H:i:s');

    $restored_datetime = null;
    if ($restored_date) {
        $restored_obj = DateTime::createFromFormat('Y-m-d\TH:i', $restored_date);
        if (!$restored_obj) {
            error_log("ERROR: Invalid restored date");
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid restored date']);
            exit;
        }
        if ($restored_obj < $interruption_obj) {
            error_log("ERROR: Restored before interruption");
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Restored time must be after interruption']);
            exit;
        }
        $restored_datetime = $restored_obj->format('Y-m-d H:i:s');
    }

    // Handle image upload
    $upload_dir = __DIR__ . '/../uploads/';
    error_log("Upload directory: " . $upload_dir);
    
    if (!is_dir($upload_dir)) {
        error_log("Creating upload directory");
        if (!mkdir($upload_dir, 0755, true)) {
            error_log("ERROR: Cannot create upload directory");
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server configuration error']);
            exit;
        }
    }

    if (!is_writable($upload_dir)) {
        error_log("ERROR: Upload directory not writable");
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Upload directory not writable']);
        exit;
    }

    $image_path = $current_record['image_path'];
    $remove_current = isset($_POST['remove_current_image']) && $_POST['remove_current_image'] == '1';
    $image_action = '';

    // Remove current image if checkbox checked
    if ($remove_current && $current_record['image_path']) {
        $old_file = $upload_dir . $current_record['image_path'];
        if (file_exists($old_file)) {
            unlink($old_file);
            error_log("Deleted old image: " . $current_record['image_path']);
        }
        $image_path = null;
        $image_action = 'removed';
    }

    // Handle NEW image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        error_log("NEW IMAGE UPLOAD DETECTED!");
        
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        
        error_log("File details: name=" . $file_name . ", size=" . $file_size);
        
        // Validate size
        if ($file_size > 5 * 1024 * 1024) {
            error_log("ERROR: File too large: " . $file_size);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
            exit;
        }
        
        // Validate type
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($file_tmp);
        error_log("File MIME type: " . $file_type);
        
        if (!in_array($file_type, $allowed)) {
            error_log("ERROR: Invalid file type: " . $file_type);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid image type']);
            exit;
        }
        
        // Generate secure filename
        $secure_name = generate_secure_filename($file_name);
        $destination = $upload_dir . $secure_name;
        
        error_log("Destination: " . $destination);
        
        // Delete old image if exists
        if ($current_record['image_path']) {
            $old_file = $upload_dir . $current_record['image_path'];
            if (file_exists($old_file)) {
                unlink($old_file);
                error_log("Deleted old image to replace: " . $current_record['image_path']);
            }
        }
        
        // Move uploaded file
        if (move_uploaded_file($file_tmp, $destination)) {
            $image_path = $secure_name;
            $image_action = 'uploaded';
            error_log("SUCCESS! Image uploaded: " . $secure_name);
            log_audit_action($user_id, 'image_upload', "Uploaded: " . $secure_name);
        } else {
            error_log("ERROR: move_uploaded_file failed");
            error_log("Source: " . $file_tmp);
            error_log("Destination: " . $destination);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to save image file']);
            exit;
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File too large (server)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (form)',
            UPLOAD_ERR_PARTIAL => 'Incomplete upload',
            UPLOAD_ERR_NO_TMP_DIR => 'No temp folder',
            UPLOAD_ERR_CANT_WRITE => 'Write failed',
            UPLOAD_ERR_EXTENSION => 'Extension blocked'
        ];
        $error_msg = $errors[$_FILES['image']['error']] ?? 'Unknown error';
        error_log("Upload error: " . $error_msg);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Upload error: ' . $error_msg]);
        exit;
    } else {
        error_log("No new image uploaded");
    }

    // Update database
    $sql = "UPDATE annex9_power_supply SET 
            region = ?, province = ?, city = ?, barangay = ?,
            service_provider = ?, interruption_datetime = ?, restored_datetime = ?, 
            remarks = ?, image_path = ?, updated_at = NOW()
            WHERE id = ?";
    
    error_log("Updating database with image_path: " . ($image_path ?: 'NULL'));
    
    $stmt = db_query($sql, [
        $region, $province, $city, $barangay,
        $service_provider, $interruption_datetime, $restored_datetime,
        $remarks, $image_path, $record_id
    ]);

    if ($stmt) {
        error_log("Database update successful!");
        
        $message = 'Record updated successfully';
        if ($image_action === 'uploaded') {
            $message .= ' with new image';
        } elseif ($image_action === 'removed') {
            $message .= ' (image removed)';
        }
        
        log_audit_action($user_id, 'annex9_update', "Updated record #$record_id" . ($image_action ? " ($image_action)" : ""));
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'redirect' => '../public/annex9_records.php'
        ]);
    } else {
        error_log("ERROR: Database update failed");
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }

} catch (Exception $e) {
    error_log("EXCEPTION: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

error_log("=== ANNEX9 UPDATE FINISHED ===");
?>
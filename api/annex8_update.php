<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/file_security.php'; // SECURITY FIX: Secure file operations

require_login();

// Enhanced session fingerprint validation
if (!validate_session_fingerprint()) {
    log_security_event($_SESSION['user_id'], 'session_hijack', 'Session fingerprint mismatch in Annex8 update');
    session_destroy();
    header('Location: ../login.php');
    exit;
}

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        log_security_event($_SESSION['user_id'], 'invalid_method', 'Annex8 update with non-POST method');
        exit('Method not allowed');
    }

    // ========== SIMPLIFIED CSRF VALIDATION ==========
    if (!isset($_POST['csrf_token']) || !verify_token($_POST['csrf_token'])) {
        set_flash('Security token validation failed', 'error');
        log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex8 update CSRF token mismatch');
        header('Location: ../public/annex8_records.php');
        exit;
    }
    // ========== END CSRF VALIDATION ==========

    // Concurrent request limiting
    if (!check_concurrent_requests($_SESSION['user_id'], 'annex8_update', 3)) {
        set_flash('Please complete your current request before starting another one.', 'error');
        header('Location: ../public/annex8_records.php');
        exit;
    }

    // Apply rate limiting for updates (20/hour)
    $action = 'annex8_update';
    if (!check_rate_limit($_SESSION['user_id'], $action, 20, 3600)) {
        set_flash('Too many update attempts. Please try again later.', 'error');
        log_security_event($_SESSION['user_id'], 'rate_limit_exceeded', 'Annex8 update rate limit exceeded');
        header('Location: ../public/annex8_records.php');
        exit;
    }

    // Validate record ID
    if (!isset($_POST['record_id']) || !is_numeric($_POST['record_id']) || $_POST['record_id'] <= 0) {
        set_flash('Valid record ID required', 'error');
        log_security_event($_SESSION['user_id'], 'missing_record_id', 'Annex8 update with invalid record_id');
        header('Location: ../public/annex8_records.php');
        exit;
    }

    $record_id = (int) $_POST['record_id'];
    
    // Additional record ID boundary check
    if ($record_id > 1000000) {
        set_flash('Invalid record ID', 'error');
        log_security_event($_SESSION['user_id'], 'suspicious_input', "Annex8 update with excessively large ID: {$record_id}");
        header('Location: ../public/annex8_records.php');
        exit;
    }
    
    $is_admin = ($_SESSION['user_role'] === 'admin');

    // Ownership check
    if (!$is_admin) {
        $check_stmt = db_query("SELECT * FROM annex8_road_bridge_status WHERE id = ? AND created_by = ?", [$record_id, $_SESSION['user_id']]);
        $original_record = $check_stmt ? $check_stmt->fetch() : null;

        if (!$original_record) {
            set_flash('Record not found or access denied', 'error');
            log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to update Annex8 record #{$record_id} without permission");
            header('Location: ../public/annex8_records.php');
            exit;
        }
    } else {
        $check_stmt = db_query("SELECT * FROM annex8_road_bridge_status WHERE id = ?", [$record_id]);
        $original_record = $check_stmt ? $check_stmt->fetch() : null;
    }

    if (!$original_record) {
        set_flash('Record not found', 'error');
        log_security_event($_SESSION['user_id'], 'record_not_found', "Annex8 record #{$record_id} not found");
        header('Location: ../public/annex8_records.php');
        exit;
    }

    // [Rest of your existing code remains the same...]
    // Sanitize and validate inputs
    $type = sanitize($_POST['type'] ?? '');
    $classification = sanitize($_POST['classification'] ?? '');
    $road_section = sanitize($_POST['road_section'] ?? '');
    $status = sanitize($_POST['status'] ?? '');
    $date_not_passable = !empty($_POST['date_not_passable']) ? date('Y-m-d H:i:s', strtotime($_POST['date_not_passable'])) : null;
    $date_passable = !empty($_POST['date_passable']) ? date('Y-m-d H:i:s', strtotime($_POST['date_passable'])) : null;
    $remarks = sanitize($_POST['remarks'] ?? '');

    // Enhanced enum value validation
    if (!validate_annex8_enum_values($type, $classification, $status)) {
        set_flash('Invalid form data submitted', 'error');
        log_security_event($_SESSION['user_id'], 'invalid_enum_values', "Invalid enum values in update: {$type}, {$classification}, {$status}");
        header('Location: ../public/annex8_records.php');
        exit;
    }

    if (empty($type) || empty($classification) || empty($road_section) || empty($status)) {
        set_flash('All required fields must be filled', 'error');
        log_security_event($_SESSION['user_id'], 'missing_fields', "Annex8 update missing fields for record #{$record_id}");
        header('Location: ../public/annex8_records.php');
        exit;
    }

    // [Rest of your image handling and update logic...]
    // Enhanced image upload handling
    $image_path = $original_record['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/annex8/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $image_file = $_FILES['image'];
        $file_name = $image_file['name'];
        $file_tmp = $image_file['tmp_name'];
        $file_size = $image_file['size'];

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $file_mime = finfo_file($file_info, $file_tmp);
        finfo_close($file_info);
        
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Double validation: MIME type and file extension
        if (in_array($file_mime, $allowed_types) && in_array($file_extension, $allowed_extensions) && $file_size <= 5 * 1024 * 1024) {
            
            // Additional image integrity check
            if ($file_mime === 'image/jpeg' || $file_mime === 'image/jpg') {
                if (!@imagecreatefromjpeg($file_tmp)) {
                    log_security_event($_SESSION['user_id'], 'corrupt_image', "Corrupt JPEG image uploaded for record #{$record_id}");
                    set_flash('Invalid image file uploaded', 'error');
                } else {
                    $sanitized_filename = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file_name);
                    $unique_filename = uniqid() . '_' . date('Ymd_His') . '_' . $sanitized_filename;
                    $destination = $upload_dir . $unique_filename;

                    if (move_uploaded_file($file_tmp, $destination)) {
                        $image_path = 'annex8/' . $unique_filename;

                        // Delete old image safely
                        if (!empty($original_record['image_path'])) {
                            $old_image_path = '../uploads/' . $original_record['image_path'];
                            if (file_exists($old_image_path) && is_writable($old_image_path)) {
                                unlink($old_image_path);
                            }
                        }
                    }
                }
            } elseif ($file_mime === 'image/png') {
                if (!@imagecreatefrompng($file_tmp)) {
                    log_security_event($_SESSION['user_id'], 'corrupt_image', "Corrupt PNG image uploaded for record #{$record_id}");
                    set_flash('Invalid image file uploaded', 'error');
                } else {
                    $sanitized_filename = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file_name);
                    $unique_filename = uniqid() . '_' . date('Ymd_His') . '_' . $sanitized_filename;
                    $destination = $upload_dir . $unique_filename;

                    if (move_uploaded_file($file_tmp, $destination)) {
                        $image_path = 'annex8/' . $unique_filename;

                        // Delete old image safely
                        if (!empty($original_record['image_path'])) {
                            $old_image_path = '../uploads/' . $original_record['image_path'];
                            if (file_exists($old_image_path) && is_writable($old_image_path)) {
                                unlink($old_image_path);
                            }
                        }
                    }
                }
            } else {
                // For gif and other allowed types
                $sanitized_filename = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file_name);
                $unique_filename = uniqid() . '_' . date('Ymd_His') . '_' . $sanitized_filename;
                $destination = $upload_dir . $unique_filename;

                if (move_uploaded_file($file_tmp, $destination)) {
                    $image_path = 'annex8/' . $unique_filename;

                    // Delete old image safely
                    if (!empty($original_record['image_path'])) {
                        $old_image_path = '../uploads/' . $original_record['image_path'];
                        if (file_exists($old_image_path) && is_writable($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                }
            }
        } else {
            log_security_event($_SESSION['user_id'], 'invalid_file_upload', "Invalid file upload attempt for record #{$record_id}");
            set_flash('Invalid file type or size', 'error');
        }
    }

    // Perform the update
    if ($image_path !== $original_record['image_path']) {
        $stmt = db_query("
            UPDATE annex8_road_bridge_status 
            SET type = ?, classification = ?, road_section = ?, status = ?, 
                date_not_passable = ?, date_passable = ?, remarks = ?, image_path = ?
            WHERE id = ?
        ", [$type, $classification, $road_section, $status, $date_not_passable, $date_passable, $remarks, $image_path, $record_id]);
    } else {
        $stmt = db_query("
            UPDATE annex8_road_bridge_status 
            SET type = ?, classification = ?, road_section = ?, status = ?, 
                date_not_passable = ?, date_passable = ?, remarks = ?
            WHERE id = ?
        ", [$type, $classification, $road_section, $status, $date_not_passable, $date_passable, $remarks, $record_id]);
    }

    // Audit + feedback
    if ($stmt && $stmt->rowCount() > 0) {
        $changes = [];
        if ($type !== $original_record['type']) $changes[] = "Type: {$original_record['type']} → {$type}";
        if ($classification !== $original_record['classification']) $changes[] = "Classification: {$original_record['classification']} → {$classification}";
        if ($road_section !== $original_record['road_section']) $changes[] = "Road Section: {$original_record['road_section']} → {$road_section}";
        if ($status !== $original_record['status']) $changes[] = "Status: {$original_record['status']} → {$status}";
        if ($image_path !== $original_record['image_path']) $changes[] = "Image updated";

        $changes_str = $changes ? implode(', ', $changes) : 'No significant changes';

        // Log successful update
        log_audit_action(
            $_SESSION['user_id'],
            'annex8_update',
            "Updated record #{$record_id} ({$original_record['barangay']}). Changes: {$changes_str}"
        );

        // Register this as a valid rate-limit attempt
        check_rate_limit($_SESSION['user_id'], 'annex8_update', 20, 3600);

        set_flash('Record updated successfully', 'success');
    } else {
        set_flash('Failed to update record', 'error');
        log_security_event($_SESSION['user_id'], 'update_failed', "Failed to update record #{$record_id}");
    }
} catch (Exception $e) {
    error_log("Annex8 update error for user {$_SESSION['user_id']}, record {$record_id}");
    log_security_event(
        $_SESSION['user_id'],
        'annex8_update_error',
        "Database or runtime error during update"
    );
    set_flash('An internal error occurred while updating the record.', 'error');
} finally {
    // Always release concurrent request
    release_concurrent_request($_SESSION['user_id'], 'annex8_update');
}

header('Location: ../public/annex8_records.php');
exit;
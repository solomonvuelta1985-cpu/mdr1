<?php
/**
 * Secure File Operations Utility
 * Prevents path traversal, unauthorized file access, and insecure file operations
 *
 * SECURITY FIXES:
 * - Path traversal protection using realpath()
 * - Whitelist-based file type validation
 * - File size limits enforcement
 * - Secure file deletion with audit logging
 * - Image re-encoding to strip malicious code
 *
 * @version 2.0
 * @date 2025-01-08
 */

// Allowed upload directory (outside web root for maximum security)
define('UPLOAD_BASE_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB default
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);  // 5MB for images

// Allowed MIME types for uploads
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp'
]);

define('ALLOWED_DOCUMENT_TYPES', [
    'application/pdf' => 'pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'text/csv' => 'csv'
]);

/**
 * Validate file path to prevent path traversal attacks
 *
 * @param string $file_path The file path to validate
 * @param string|null $allowed_directory The base directory (defaults to UPLOAD_BASE_DIR)
 * @return array ['valid' => bool, 'real_path' => string|false, 'error' => string]
 */
function validate_file_path($file_path, $allowed_directory = null) {
    if ($allowed_directory === null) {
        $allowed_directory = UPLOAD_BASE_DIR;
    }

    // Normalize the allowed directory path
    $real_allowed = realpath($allowed_directory);

    if ($real_allowed === false) {
        return [
            'valid' => false,
            'real_path' => false,
            'error' => 'Allowed directory does not exist'
        ];
    }

    // Resolve the actual path (prevents ../../../ traversal)
    $real_path = realpath($file_path);

    if ($real_path === false) {
        return [
            'valid' => false,
            'real_path' => false,
            'error' => 'File does not exist'
        ];
    }

    // Check if the real path is within the allowed directory
    if (strpos($real_path, $real_allowed) !== 0) {
        log_security_event(
            $_SESSION['user_id'] ?? 0,
            'path_traversal_attempt',
            "Attempted to access file outside allowed directory: {$file_path} (resolved to: {$real_path})"
        );

        return [
            'valid' => false,
            'real_path' => false,
            'error' => 'Access denied: Path traversal detected'
        ];
    }

    return [
        'valid' => true,
        'real_path' => $real_path,
        'error' => null
    ];
}

/**
 * Safely delete a file with path traversal protection
 *
 * @param string $file_path Path to file to delete
 * @param string|null $allowed_directory Allowed base directory
 * @return array ['success' => bool, 'message' => string]
 */
function safe_file_delete($file_path, $allowed_directory = null) {
    $validation = validate_file_path($file_path, $allowed_directory);

    if (!$validation['valid']) {
        return [
            'success' => false,
            'message' => $validation['error']
        ];
    }

    $real_path = $validation['real_path'];

    // Verify it's a file (not a directory)
    if (!is_file($real_path)) {
        return [
            'success' => false,
            'message' => 'Path is not a file'
        ];
    }

    // Log the deletion attempt
    log_audit_action(
        $_SESSION['user_id'] ?? 0,
        'file_delete_attempt',
        "Attempting to delete file: " . basename($real_path)
    );

    // Perform deletion
    if (@unlink($real_path)) {
        log_audit_action(
            $_SESSION['user_id'] ?? 0,
            'file_deleted',
            "Successfully deleted file: " . basename($real_path)
        );

        return [
            'success' => true,
            'message' => 'File deleted successfully'
        ];
    } else {
        log_security_event(
            $_SESSION['user_id'] ?? 0,
            'file_delete_failed',
            "Failed to delete file: " . basename($real_path)
        );

        return [
            'success' => false,
            'message' => 'Failed to delete file'
        ];
    }
}

/**
 * Validate uploaded file for security
 *
 * @param array $file The $_FILES array element
 * @param string $type 'image' or 'document'
 * @return array ['valid' => bool, 'error' => string|null, 'extension' => string|null]
 */
function validate_upload($file, $type = 'image') {
    // Check for upload errors
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['valid' => false, 'error' => 'Invalid file upload', 'extension' => null];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['valid' => false, 'error' => 'File exceeds maximum size limit', 'extension' => null];
        case UPLOAD_ERR_NO_FILE:
            return ['valid' => false, 'error' => 'No file was uploaded', 'extension' => null];
        default:
            return ['valid' => false, 'error' => 'Unknown upload error', 'extension' => null];
    }

    // Check file size
    $max_size = ($type === 'image') ? MAX_IMAGE_SIZE : MAX_FILE_SIZE;
    if ($file['size'] > $max_size) {
        return [
            'valid' => false,
            'error' => 'File size exceeds ' . format_bytes($max_size) . ' limit',
            'extension' => null
        ];
    }

    // Validate MIME type using finfo (secure method)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);

    $allowed_types = ($type === 'image') ? ALLOWED_IMAGE_TYPES : ALLOWED_DOCUMENT_TYPES;

    if (!array_key_exists($mime_type, $allowed_types)) {
        log_security_event(
            $_SESSION['user_id'] ?? 0,
            'invalid_file_upload_attempt',
            "Attempted to upload invalid file type: {$mime_type}"
        );

        return [
            'valid' => false,
            'error' => 'Invalid file type. Allowed types: ' . implode(', ', array_unique(array_values($allowed_types))),
            'extension' => null
        ];
    }

    $extension = $allowed_types[$mime_type];

    // Additional validation for images: check if it's a real image
    if ($type === 'image') {
        $image_info = @getimagesize($file['tmp_name']);
        if ($image_info === false) {
            log_security_event(
                $_SESSION['user_id'] ?? 0,
                'fake_image_upload_attempt',
                "Attempted to upload non-image file disguised as image"
            );

            return [
                'valid' => false,
                'error' => 'File is not a valid image',
                'extension' => null
            ];
        }
    }

    return [
        'valid' => true,
        'error' => null,
        'extension' => $extension
    ];
}

/**
 * Securely save uploaded image with re-encoding to strip malicious code
 *
 * @param array $file The $_FILES array element
 * @param string $destination_dir Destination directory (within uploads/)
 * @param string|null $custom_name Custom filename (without extension)
 * @return array ['success' => bool, 'filename' => string|null, 'path' => string|null, 'error' => string|null]
 */
function safe_image_upload($file, $destination_dir, $custom_name = null) {
    // Validate the upload
    $validation = validate_upload($file, 'image');

    if (!$validation['valid']) {
        return [
            'success' => false,
            'filename' => null,
            'path' => null,
            'error' => $validation['error']
        ];
    }

    $extension = $validation['extension'];

    // Create destination directory if it doesn't exist
    $full_dest_dir = UPLOAD_BASE_DIR . trim($destination_dir, '/') . '/';
    if (!is_dir($full_dest_dir)) {
        if (!@mkdir($full_dest_dir, 0755, true)) {
            return [
                'success' => false,
                'filename' => null,
                'path' => null,
                'error' => 'Failed to create upload directory'
            ];
        }
    }

    // Generate secure filename
    if ($custom_name === null) {
        $custom_name = bin2hex(random_bytes(16));
    } else {
        // Sanitize custom name
        $custom_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $custom_name);
    }

    $filename = $custom_name . '_' . time() . '.' . $extension;
    $destination_path = $full_dest_dir . $filename;

    // RE-ENCODE IMAGE (strips any embedded malicious code)
    $source_image = null;
    $mime_type = mime_content_type($file['tmp_name']);

    switch ($mime_type) {
        case 'image/jpeg':
            $source_image = @imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/png':
            $source_image = @imagecreatefrompng($file['tmp_name']);
            break;
        case 'image/gif':
            $source_image = @imagecreatefromgif($file['tmp_name']);
            break;
        case 'image/webp':
            $source_image = @imagecreatefromwebp($file['tmp_name']);
            break;
    }

    if ($source_image === false) {
        return [
            'success' => false,
            'filename' => null,
            'path' => null,
            'error' => 'Failed to process image'
        ];
    }

    // Save re-encoded image
    $save_success = false;
    switch ($extension) {
        case 'jpg':
            $save_success = imagejpeg($source_image, $destination_path, 90);
            break;
        case 'png':
            $save_success = imagepng($source_image, $destination_path, 9);
            break;
        case 'gif':
            $save_success = imagegif($source_image, $destination_path);
            break;
        case 'webp':
            $save_success = imagewebp($source_image, $destination_path, 90);
            break;
    }

    imagedestroy($source_image);

    if (!$save_success) {
        return [
            'success' => false,
            'filename' => null,
            'path' => null,
            'error' => 'Failed to save image'
        ];
    }

    // Set proper permissions
    @chmod($destination_path, 0644);

    // Log successful upload
    log_audit_action(
        $_SESSION['user_id'] ?? 0,
        'file_uploaded',
        "Uploaded file: {$filename} to {$destination_dir}"
    );

    return [
        'success' => true,
        'filename' => $filename,
        'path' => $destination_dir . '/' . $filename,
        'error' => null
    ];
}

/**
 * Format bytes to human-readable size
 *
 * @param int $bytes Number of bytes
 * @param int $precision Decimal precision
 * @return string Formatted string (e.g., "5.2 MB")
 */
function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Get safe file information
 *
 * @param string $file_path Path to file
 * @return array|false File information or false on error
 */
function get_safe_file_info($file_path) {
    $validation = validate_file_path($file_path);

    if (!$validation['valid']) {
        return false;
    }

    $real_path = $validation['real_path'];

    if (!is_file($real_path)) {
        return false;
    }

    return [
        'name' => basename($real_path),
        'size' => filesize($real_path),
        'size_formatted' => format_bytes(filesize($real_path)),
        'modified' => filemtime($real_path),
        'mime_type' => mime_content_type($real_path),
        'extension' => pathinfo($real_path, PATHINFO_EXTENSION)
    ];
}

/**
 * Sanitize filename for safe storage
 *
 * @param string $filename Original filename
 * @return string Sanitized filename
 */
function sanitize_filename($filename) {
    // Remove path information
    $filename = basename($filename);

    // Get extension
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);

    // Remove special characters
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

    // Limit length
    $name = substr($name, 0, 50);

    return $name . '.' . $extension;
}

<?php
/**
 * API Handler for Annex 3: Casualties Report
 * Secure submission with PDO prepared statements and validation
 */

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// Rate limiting for form submissions
$rate_limit_key = 'annex3_submission_' . $_SESSION['user_id'];
if (!check_rate_limit($rate_limit_key, 50, 3600)) {
    log_security_event(
        $_SESSION['user_id'],
        'rate_limit_exceeded',
        'Annex 3 submission rate limit exceeded (50 per hour)'
    );
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Too many submission attempts. Please wait before submitting again.'
    ]);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// CSRF token validation
if (!isset($_POST['csrf_token']) || !verify_token($_POST['csrf_token'])) {
    log_security_event(
        $_SESSION['user_id'],
        'csrf_validation_failed',
        'Annex 3 CSRF token validation failed'
    );
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Security token validation failed. Please refresh the page and try again.'
    ]);
    exit;
}

// Get user data
$user_data = get_user_location_data($_SESSION['user_id']);
$user_barangay = $user_data['barangay'] ?? '';
$user_role = $user_data['user_role'] ?? 'user';

// Barangay locking check for admins
if (is_admin()) {
    $current_lock = get_admin_locked_barangay($_SESSION['user_id']);
    if (!$current_lock) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Please select a barangay from the admin dashboard before submitting forms.'
        ]);
        exit;
    }
    $user_barangay = $current_lock;
}

// Validate required fields exist
$required_arrays = ['region', 'province', 'city', 'barangay', 'category', 'surname', 'firstName', 'age', 'sex', 'address', 'cause', 'source', 'validated'];
foreach ($required_arrays as $field) {
    if (!isset($_POST[$field]) || !is_array($_POST[$field])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Missing required field: $field"
        ]);
        exit;
    }
}

// Validate array lengths match
$entry_count = count($_POST['region']);
foreach ($required_arrays as $field) {
    if (count($_POST[$field]) !== $entry_count) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Field array length mismatch: $field"
        ]);
        exit;
    }
}

// Limit number of entries to prevent abuse
if ($entry_count > 100) {
    log_security_event(
        $_SESSION['user_id'],
        'entry_limit_exceeded',
        "Annex 3 entry limit exceeded: $entry_count entries"
    );
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Too many entries. Maximum 100 entries allowed per submission.'
    ]);
    exit;
}

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    $valid_categories = ['Dead', 'Injured', 'Ill', 'Missing'];
    $valid_sex = ['Male', 'Female'];
    $valid_sources = ['MDM Cluster', 'LGU', 'DOH', 'PNP', 'BFAR', 'Other'];
    $valid_validated = ['Yes', 'No'];
    
    $inserted_count = 0;
    
    // Process each entry
    for ($i = 0; $i < $entry_count; $i++) {
        // Sanitize and validate inputs
        $region = sanitize($_POST['region'][$i]);
        $province = sanitize($_POST['province'][$i]);
        $city = sanitize($_POST['city'][$i]);
        $barangay = sanitize($_POST['barangay'][$i]);
        $category = sanitize($_POST['category'][$i]);
        $surname = sanitize($_POST['surname'][$i]);
        $firstName = sanitize($_POST['firstName'][$i]);
        $middleName = isset($_POST['middleName'][$i]) ? sanitize($_POST['middleName'][$i]) : '';
        $age = intval($_POST['age'][$i]);
        $sex = sanitize($_POST['sex'][$i]);
        $address = sanitize($_POST['address'][$i]);
        $cause = sanitize($_POST['cause'][$i]);
        $remarks = isset($_POST['remarks'][$i]) ? sanitize($_POST['remarks'][$i]) : '';
        $source = sanitize($_POST['source'][$i]);
        $validated = sanitize($_POST['validated'][$i]);
        
        // Validate required fields
        if (empty($region) || empty($province) || empty($city) || empty($barangay) || 
            empty($category) || empty($surname) || empty($firstName) || empty($age) || 
            empty($sex) || empty($address) || empty($cause) || empty($source) || empty($validated)) {
            $response['errors'][] = "Entry " . ($i + 1) . ": All required fields must be filled";
            continue;
        }
        
        // Validate category
        if (!in_array($category, $valid_categories)) {
            $response['errors'][] = "Entry " . ($i + 1) . ": Invalid category selected";
            continue;
        }
        
        // Validate sex
        if (!in_array($sex, $valid_sex)) {
            $response['errors'][] = "Entry " . ($i + 1) . ": Invalid sex selected";
            continue;
        }
        
        // Validate source
        if (!in_array($source, $valid_sources)) {
            $response['errors'][] = "Entry " . ($i + 1) . ": Invalid data source selected";
            continue;
        }
        
        // Validate validated field
        if (!in_array($validated, $valid_validated)) {
            $response['errors'][] = "Entry " . ($i + 1) . ": Invalid validation status selected";
            continue;
        }
        
        // Validate age range
        if ($age < 0 || $age > 120) {
            $response['errors'][] = "Entry " . ($i + 1) . ": Age must be between 0 and 120";
            continue;
        }
        
        // Validate string lengths
        if (strlen($surname) > 100) {
            $response['errors'][] = "Entry " . ($i + 1) . ": Surname too long (max 100 characters)";
            continue;
        }
        
        if (strlen($firstName) > 100) {
            $response['errors'][] = "Entry " . ($i + 1) . ": First name too long (max 100 characters)";
            continue;
        }
        
        if (strlen($middleName) > 100) {
            $response['errors'][] = "Entry " . ($i + 1) . ": Middle name too long (max 100 characters)";
            continue;
        }
        
        if (strlen($address) > 255) {
            $response['errors'][] = "Entry " . ($i + 1) . ": Address too long (max 255 characters)";
            continue;
        }
        
        if (strlen($cause) > 500) {
            $response['errors'][] = "Entry " . ($i + 1) . ": Cause too long (max 500 characters)";
            continue;
        }
        
        if (strlen($remarks) > 500) {
            $response['errors'][] = "Entry " . ($i + 1) . ": Remarks too long (max 500 characters)";
            continue;
        }
        
        // Check if barangay matches user's barangay (for non-admins)
        if ($user_role !== 'admin' && $barangay !== $user_barangay) {
            log_security_event(
                $_SESSION['user_id'],
                'barangay_mismatch',
                "User attempted to submit for barangay: $barangay, but belongs to: $user_barangay"
            );
            $response['errors'][] = "Entry " . ($i + 1) . ": Barangay mismatch detected";
            continue;
        }
        
        // Insert the entry
        $sql = "INSERT INTO annex3_casualties (
            region, province, city, barangay, category, surname, first_name, middle_name, 
            age, sex, address, cause, remarks, source, validated, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = db_query($sql, [
            $region, $province, $city, $barangay, $category, $surname, $firstName, $middleName,
            $age, $sex, $address, $cause, $remarks, $source, $validated, $_SESSION['user_id']
        ]);
        
        if ($stmt->rowCount() > 0) {
            $inserted_count++;
            
            // Log successful entry insertion
            log_audit_action(
                $_SESSION['user_id'],
                'annex3_entry_created',
                "Created casualty entry for: $firstName $surname ($category) in $barangay"
            );
        }
    }
    
    // Check if any entries were successfully inserted
    if ($inserted_count > 0) {
        $pdo->commit();
        
        // Log successful submission
        log_audit_action(
            $_SESSION['user_id'],
            'annex3_submission_success',
            "Successfully submitted $inserted_count casualty entries"
        );
        
        $response['success'] = true;
        $response['message'] = "Successfully saved $inserted_count casualty entries";
        $response['inserted_count'] = $inserted_count;
        
        http_response_code(200);
    } else {
        $pdo->rollBack();
        
        if (empty($response['errors'])) {
            $response['errors'][] = "No valid entries to save";
        }
        
        $response['message'] = "Failed to save any entries. Please check the errors below.";
        http_response_code(400);
    }
    
} catch (PDOException $e) {
    $pdo->rollBack();
    
    // Log database error (don't expose details to user)
    error_log("Annex 3 Database Error: " . $e->getMessage());
    log_security_event(
        $_SESSION['user_id'],
        'database_error',
        'Annex 3 database error during submission'
    );
    
    $response['message'] = 'Database error occurred. Please try again.';
    http_response_code(500);
    
} catch (Exception $e) {
    $pdo->rollBack();
    
    error_log("Annex 3 General Error: " . $e->getMessage());
    log_security_event(
        $_SESSION['user_id'],
        'submission_error',
        'Annex 3 general error during submission: ' . $e->getMessage()
    );
    
    $response['message'] = 'An error occurred while saving the report. Please try again.';
    http_response_code(500);
}

// Return JSON response
echo json_encode($response);
exit;
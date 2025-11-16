<?php
// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_security_event($_SESSION['user_id'], 'invalid_method', 'Annex17 save accessed using non-POST method');
    set_flash('Invalid request method', 'error');
    header('Location: ../public/annex17.php');
    exit;
}

// Verify CSRF token
if (!verify_token($_POST['csrf_token'] ?? '')) {
    log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex17 save CSRF token mismatch');
    set_flash('Security token validation failed. Please try again.', 'error');
    header('Location: ../public/annex17.php');
    exit;
}

// Rate limiting
if (!check_rate_limit($_SESSION['user_id'], 'annex17_submission', 10, 3600)) {
    log_security_event($_SESSION['user_id'], 'rate_limit_exceeded', 'Annex17 submission rate limit exceeded');
    set_flash('Too many submission attempts. Please wait before trying again.', 'error');
    header('Location: ../public/annex17.php');
    exit;
}

// Get user data
$user_data = get_user_location_data($_SESSION['user_id']);
$user_barangay = $user_data['barangay'] ?? '';

// Check admin barangay lock
if (is_admin()) {
    $current_lock = get_admin_locked_barangay($_SESSION['user_id']);
    if (!$current_lock) {
        set_flash('Please select a barangay first.', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
    $user_barangay = $current_lock;
}

// Fetch cumulative settings for this barangay
$cumulative_settings = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM annex17_cumulative_settings WHERE barangay = ?");
    $stmt->execute([$user_barangay]);
    $cumulative_settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Error fetching Annex 17 cumulative settings: " . $e->getMessage());
    // Continue with empty settings - cumulative values will be 0
}

// Sanitize and validate inputs
// NOTE: Cumulative values are fetched from settings, NOT from POST data
$data = [
    'region' => sanitize($_POST['region'] ?? ''),
    'province' => sanitize($_POST['province'] ?? ''),
    'city_municipality' => sanitize($_POST['city_municipality'] ?? ''),
    'barangay' => sanitize($_POST['barangay'] ?? ''),
    'families' => filter_var($_POST['families'] ?? 0, FILTER_VALIDATE_INT),

    // Age distribution - Infant (cumulative from settings, current from POST)
    'infant_male_cumulative' => (int)($cumulative_settings['infant_male_cumulative'] ?? 0),
    'infant_male_current' => filter_var($_POST['infant_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'infant_female_cumulative' => (int)($cumulative_settings['infant_female_cumulative'] ?? 0),
    'infant_female_current' => filter_var($_POST['infant_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // Toddlers
    'toddler_male_cumulative' => (int)($cumulative_settings['toddler_male_cumulative'] ?? 0),
    'toddler_male_current' => filter_var($_POST['toddler_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'toddler_female_cumulative' => (int)($cumulative_settings['toddler_female_cumulative'] ?? 0),
    'toddler_female_current' => filter_var($_POST['toddler_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // Preschooler
    'preschool_male_cumulative' => (int)($cumulative_settings['preschool_male_cumulative'] ?? 0),
    'preschool_male_current' => filter_var($_POST['preschool_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'preschool_female_cumulative' => (int)($cumulative_settings['preschool_female_cumulative'] ?? 0),
    'preschool_female_current' => filter_var($_POST['preschool_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // School age
    'school_male_cumulative' => (int)($cumulative_settings['school_male_cumulative'] ?? 0),
    'school_male_current' => filter_var($_POST['school_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'school_female_cumulative' => (int)($cumulative_settings['school_female_cumulative'] ?? 0),
    'school_female_current' => filter_var($_POST['school_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // Teenage
    'teenage_male_cumulative' => (int)($cumulative_settings['teenage_male_cumulative'] ?? 0),
    'teenage_male_current' => filter_var($_POST['teenage_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'teenage_female_cumulative' => (int)($cumulative_settings['teenage_female_cumulative'] ?? 0),
    'teenage_female_current' => filter_var($_POST['teenage_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // Adult
    'adult_male_cumulative' => (int)($cumulative_settings['adult_male_cumulative'] ?? 0),
    'adult_male_current' => filter_var($_POST['adult_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'adult_female_cumulative' => (int)($cumulative_settings['adult_female_cumulative'] ?? 0),
    'adult_female_current' => filter_var($_POST['adult_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // Elderly
    'elderly_male_cumulative' => (int)($cumulative_settings['elderly_male_cumulative'] ?? 0),
    'elderly_male_current' => filter_var($_POST['elderly_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'elderly_female_cumulative' => (int)($cumulative_settings['elderly_female_cumulative'] ?? 0),
    'elderly_female_current' => filter_var($_POST['elderly_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // Pregnant/Lactating
    'pregnant_lactating_cumulative' => (int)($cumulative_settings['pregnant_lactating_cumulative'] ?? 0),
    'pregnant_lactating_current' => filter_var($_POST['pregnant_lactating_current'] ?? 0, FILTER_VALIDATE_INT),

    // Child-headed family
    'child_headed_male_cumulative' => (int)($cumulative_settings['child_headed_family_male_cumulative'] ?? 0),
    'child_headed_male_current' => filter_var($_POST['child_headed_family_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'child_headed_female_cumulative' => (int)($cumulative_settings['child_headed_family_female_cumulative'] ?? 0),
    'child_headed_female_current' => filter_var($_POST['child_headed_family_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // Single-headed family
    'single_headed_male_cumulative' => (int)($cumulative_settings['single_headed_family_male_cumulative'] ?? 0),
    'single_headed_male_current' => filter_var($_POST['single_headed_family_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'single_headed_female_cumulative' => (int)($cumulative_settings['single_headed_family_female_cumulative'] ?? 0),
    'single_headed_female_current' => filter_var($_POST['single_headed_family_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // Solo parent
    'solo_parent_male_cumulative' => (int)($cumulative_settings['solo_parent_male_cumulative'] ?? 0),
    'solo_parent_male_current' => filter_var($_POST['solo_parent_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'solo_parent_female_cumulative' => (int)($cumulative_settings['solo_parent_female_cumulative'] ?? 0),
    'solo_parent_female_current' => filter_var($_POST['solo_parent_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // PWD
    'pwd_male_cumulative' => (int)($cumulative_settings['pwd_male_cumulative'] ?? 0),
    'pwd_male_current' => filter_var($_POST['pwd_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'pwd_female_cumulative' => (int)($cumulative_settings['pwd_female_cumulative'] ?? 0),
    'pwd_female_current' => filter_var($_POST['pwd_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // IP
    'ip_male_cumulative' => (int)($cumulative_settings['ip_male_cumulative'] ?? 0),
    'ip_male_current' => filter_var($_POST['ip_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'ip_female_cumulative' => (int)($cumulative_settings['ip_female_cumulative'] ?? 0),
    'ip_female_current' => filter_var($_POST['ip_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // 4Ps
    'fourps_male_cumulative' => (int)($cumulative_settings['fourps_male_cumulative'] ?? 0),
    'fourps_male_current' => filter_var($_POST['fourps_male_current'] ?? 0, FILTER_VALIDATE_INT),
    'fourps_female_cumulative' => (int)($cumulative_settings['fourps_female_cumulative'] ?? 0),
    'fourps_female_current' => filter_var($_POST['fourps_female_current'] ?? 0, FILTER_VALIDATE_INT),

    // Additional fields
    'vulnerable_groups_resolution' => sanitize($_POST['vulnerable_groups_resolution'] ?? ''),
    'remarks' => sanitize($_POST['remarks'] ?? ''),
];

// Calculate totals
$data['total_male_cumulative'] = $data['infant_male_cumulative'] + $data['toddler_male_cumulative'] +
    $data['preschool_male_cumulative'] + $data['school_male_cumulative'] + $data['teenage_male_cumulative'] +
    $data['adult_male_cumulative'] + $data['elderly_male_cumulative'];

$data['total_male_current'] = $data['infant_male_current'] + $data['toddler_male_current'] +
    $data['preschool_male_current'] + $data['school_male_current'] + $data['teenage_male_current'] +
    $data['adult_male_current'] + $data['elderly_male_current'];

$data['total_female_cumulative'] = $data['infant_female_cumulative'] + $data['toddler_female_cumulative'] +
    $data['preschool_female_cumulative'] + $data['school_female_cumulative'] + $data['teenage_female_cumulative'] +
    $data['adult_female_cumulative'] + $data['elderly_female_cumulative'];

$data['total_female_current'] = $data['infant_female_current'] + $data['toddler_female_current'] +
    $data['preschool_female_current'] + $data['school_female_current'] + $data['teenage_female_current'] +
    $data['adult_female_current'] + $data['elderly_female_current'];

$data['total_cumulative'] = $data['total_male_cumulative'] + $data['total_female_cumulative'];
$data['total_current'] = $data['total_male_current'] + $data['total_female_current'];

// Validate required fields
$errors = [];
if (empty($data['region'])) $errors[] = 'Region is required';
if (empty($data['province'])) $errors[] = 'Province is required';
if (empty($data['city_municipality'])) $errors[] = 'City/Municipality is required';
if (empty($data['barangay'])) $errors[] = 'Barangay is required';

// Verify barangay access for non-admin users
if (!is_admin() && $data['barangay'] !== $user_barangay) {
    log_security_event($_SESSION['user_id'], 'unauthorized_barangay_access',
        "Attempted to submit Annex17 data for barangay: " . $data['barangay']);
    set_flash('You are not authorized to submit data for this barangay.', 'error');
    header('Location: ../public/annex17.php');
    exit;
}

if (!empty($errors)) {
    set_flash('Validation errors: ' . implode(', ', $errors), 'error');
    header('Location: ../public/annex17.php');
    exit;
}

// Insert into database
try {
    $stmt = $pdo->prepare("
        INSERT INTO annex17_preemptive_evacuation (
            region, province, city_municipality, barangay, families,
            infant_male_cumulative, infant_male_current, infant_female_cumulative, infant_female_current,
            toddler_male_cumulative, toddler_male_current, toddler_female_cumulative, toddler_female_current,
            preschool_male_cumulative, preschool_male_current, preschool_female_cumulative, preschool_female_current,
            school_male_cumulative, school_male_current, school_female_cumulative, school_female_current,
            teenage_male_cumulative, teenage_male_current, teenage_female_cumulative, teenage_female_current,
            adult_male_cumulative, adult_male_current, adult_female_cumulative, adult_female_current,
            elderly_male_cumulative, elderly_male_current, elderly_female_cumulative, elderly_female_current,
            total_male_cumulative, total_male_current, total_female_cumulative, total_female_current,
            total_cumulative, total_current,
            pregnant_lactating_cumulative, pregnant_lactating_current,
            child_headed_male_cumulative, child_headed_male_current, child_headed_female_cumulative, child_headed_female_current,
            single_headed_male_cumulative, single_headed_male_current, single_headed_female_cumulative, single_headed_female_current,
            solo_parent_male_cumulative, solo_parent_male_current, solo_parent_female_cumulative, solo_parent_female_current,
            pwd_male_cumulative, pwd_male_current, pwd_female_cumulative, pwd_female_current,
            ip_male_cumulative, ip_male_current, ip_female_cumulative, ip_female_current,
            fourps_male_cumulative, fourps_male_current, fourps_female_cumulative, fourps_female_current,
            vulnerable_groups_resolution, remarks, created_by
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?,
            ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?
        )
    ");

    $result = $stmt->execute([
        $data['region'], $data['province'], $data['city_municipality'], $data['barangay'], $data['families'],
        $data['infant_male_cumulative'], $data['infant_male_current'], $data['infant_female_cumulative'], $data['infant_female_current'],
        $data['toddler_male_cumulative'], $data['toddler_male_current'], $data['toddler_female_cumulative'], $data['toddler_female_current'],
        $data['preschool_male_cumulative'], $data['preschool_male_current'], $data['preschool_female_cumulative'], $data['preschool_female_current'],
        $data['school_male_cumulative'], $data['school_male_current'], $data['school_female_cumulative'], $data['school_female_current'],
        $data['teenage_male_cumulative'], $data['teenage_male_current'], $data['teenage_female_cumulative'], $data['teenage_female_current'],
        $data['adult_male_cumulative'], $data['adult_male_current'], $data['adult_female_cumulative'], $data['adult_female_current'],
        $data['elderly_male_cumulative'], $data['elderly_male_current'], $data['elderly_female_cumulative'], $data['elderly_female_current'],
        $data['total_male_cumulative'], $data['total_male_current'], $data['total_female_cumulative'], $data['total_female_current'],
        $data['total_cumulative'], $data['total_current'],
        $data['pregnant_lactating_cumulative'], $data['pregnant_lactating_current'],
        $data['child_headed_male_cumulative'], $data['child_headed_male_current'], $data['child_headed_female_cumulative'], $data['child_headed_female_current'],
        $data['single_headed_male_cumulative'], $data['single_headed_male_current'], $data['single_headed_female_cumulative'], $data['single_headed_female_current'],
        $data['solo_parent_male_cumulative'], $data['solo_parent_male_current'], $data['solo_parent_female_cumulative'], $data['solo_parent_female_current'],
        $data['pwd_male_cumulative'], $data['pwd_male_current'], $data['pwd_female_cumulative'], $data['pwd_female_current'],
        $data['ip_male_cumulative'], $data['ip_male_current'], $data['ip_female_cumulative'], $data['ip_female_current'],
        $data['fourps_male_cumulative'], $data['fourps_male_current'], $data['fourps_female_cumulative'], $data['fourps_female_current'],
        $data['vulnerable_groups_resolution'], $data['remarks'], $_SESSION['user_id']
    ]);

    if ($result) {
        log_audit_action($_SESSION['user_id'], 'annex17_submission',
            "Submitted pre-emptive evacuation report for " . $data['barangay']);

        log_security_event($_SESSION['user_id'], 'annex17_submission_success',
            "Successfully submitted Annex17 report for " . $data['barangay']);

        set_flash('Pre-emptive evacuation report submitted successfully!', 'success');
        header('Location: ../public/annex17_records.php');
        exit;
    } else {
        throw new Exception('Failed to insert record');
    }
} catch (PDOException $e) {
    error_log("Annex17 database error for user {$_SESSION['user_id']}: " . $e->getMessage());
    log_security_event($_SESSION['user_id'], 'annex17_submission_error', 'Database error during submission');
    set_flash('An error occurred while saving the report. Please try again.', 'error');
    header('Location: ../public/annex17.php');
    exit;
}
?>

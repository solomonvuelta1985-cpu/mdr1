<?php
// Define page title for the header
define('PAGE_TITLE', 'Annex 2 - Affected Population Report');

// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

// Enhanced session validation
if (isset($_SESSION['user_id']) && function_exists('validate_session_fingerprint') && !validate_session_fingerprint()) {
    log_security_event($_SESSION['user_id'] ?? 'unknown', 'session_hijack_attempt', 'Session fingerprint validation failed');
    session_regenerate_id(true);
    session_destroy();
    set_flash('Security violation detected. Please log in again.', 'error');
    header('Location: ../login.php');
    exit;
}

// Log form access for audit tracking
log_audit_action($_SESSION['user_id'], 'annex2_form_access', 'User accessed the Annex 2 Affected Population Report form');

// Get user data and check barangay selection for admins
$user_data = get_user_location_data($_SESSION['user_id']);
$user_role = $user_data['user_role'] ?? 'user';

// Check if admin has selected a barangay
if (is_admin()) {
    $current_lock = get_admin_locked_barangay($_SESSION['user_id']);
    if (!$current_lock) {
        set_flash('Please select a barangay from the admin dashboard before filling out forms.', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
    $user_barangay = $current_lock;
} else {
    // Regular users use their assigned barangay
    $user_barangay = $user_data['barangay'] ?? '';
}

// Get cumulative settings for the current barangay
$cumulative_settings = [];
if ($user_barangay) {
    $stmt = $pdo->prepare("SELECT * FROM annex2_cumulative_settings WHERE barangay = ?");
    $stmt->execute([$user_barangay]);
    $cumulative_settings = $stmt->fetch(PDO::FETCH_ASSOC) ?? [];
}

// Input validation and sanitization functions
function validate_annex2_entry($entry) {
    $errors = [];
    
    // Required fields
    $required = ['region', 'province', 'city', 'barangay'];
    foreach ($required as $field) {
        if (empty($entry[$field])) {
            $errors[] = "$field is required";
        }
    }
    
    // Validate number fields (must be non-negative)
    $number_fields = [
        'affected_families_cumulative', 'affected_families_current', 'affected_persons_cumulative', 'affected_persons_current',
        'num_ecs_cumulative', 'num_ecs_current',
        'inside_families_cumulative', 'inside_families_current', 'inside_persons_cumulative', 'inside_persons_current',
        'outside_families_cumulative', 'outside_families_current', 'outside_persons_cumulative', 'outside_persons_current',
        'total_families_cumulative', 'total_families_current', 'total_persons_cumulative', 'total_persons_current'
    ];
    
    foreach ($number_fields as $field) {
        if (isset($entry[$field]) && $entry[$field] !== '' && $entry[$field] < 0) {
            $errors[] = "$field must be a non-negative number";
        }
        if (isset($entry[$field]) && $entry[$field] !== '' && $entry[$field] > 999999999) {
            $errors[] = "$field exceeds maximum value";
        }
    }
    
    // Validate field lengths
    $max_lengths = [
        'region' => 100,
        'province' => 100,
        'city' => 100,
        'barangay' => 200,
        'remarks' => 500
    ];
    
    foreach ($max_lengths as $field => $max_length) {
        if (isset($entry[$field]) && strlen($entry[$field]) > $max_length) {
            $errors[] = "$field exceeds maximum length of $max_length characters";
        }
    }
    
    return $errors;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_token($_POST['csrf_token'])) {
        set_flash('Security token validation failed. Please try again.', 'error');
        header('Location: annex2.php');
        exit;
    }
    
    // Rate limiting - check if user has submitted too many requests
    if (!check_rate_limit($_SESSION['user_id'], 'annex2_submission', 10, 300)) { // 10 submissions per 5 minutes
        set_flash('Too many submission attempts. Please wait a few minutes before trying again.', 'error');
        header('Location: annex2.php');
        exit;
    }

    // Process each entry
    $success_count = 0;
    $error_count = 0;
    $validation_errors = [];
    
    if (isset($_POST['region']) && is_array($_POST['region'])) {
        foreach ($_POST['region'] as $index => $region) {
            // Skip empty entries (all fields empty)
            if (empty($region) && 
                empty($_POST['province'][$index]) && 
                empty($_POST['city'][$index]) && 
                empty($_POST['barangay'][$index])) {
                continue;
            }
            
            // Sanitize inputs
            $entry_data = [
                'region' => sanitize($region),
                'province' => sanitize($_POST['province'][$index] ?? ''),
                'city' => sanitize($_POST['city'][$index] ?? ''),
                'barangay' => sanitize($_POST['barangay'][$index] ?? ''),
                'affected_families_cumulative' => isset($_POST['affected_families_cumulative'][$index]) && $_POST['affected_families_cumulative'][$index] !== '' ? (int)$_POST['affected_families_cumulative'][$index] : null,
                'affected_families_current' => isset($_POST['affected_families_current'][$index]) && $_POST['affected_families_current'][$index] !== '' ? (int)$_POST['affected_families_current'][$index] : null,
                'affected_persons_cumulative' => isset($_POST['affected_persons_cumulative'][$index]) && $_POST['affected_persons_cumulative'][$index] !== '' ? (int)$_POST['affected_persons_cumulative'][$index] : null,
                'affected_persons_current' => isset($_POST['affected_persons_current'][$index]) && $_POST['affected_persons_current'][$index] !== '' ? (int)$_POST['affected_persons_current'][$index] : null,
                'num_ecs_cumulative' => isset($_POST['num_ecs_cumulative'][$index]) && $_POST['num_ecs_cumulative'][$index] !== '' ? (int)$_POST['num_ecs_cumulative'][$index] : null,
                'num_ecs_current' => isset($_POST['num_ecs_current'][$index]) && $_POST['num_ecs_current'][$index] !== '' ? (int)$_POST['num_ecs_current'][$index] : null,
                'inside_families_cumulative' => isset($_POST['inside_families_cumulative'][$index]) && $_POST['inside_families_cumulative'][$index] !== '' ? (int)$_POST['inside_families_cumulative'][$index] : null,
                'inside_families_current' => isset($_POST['inside_families_current'][$index]) && $_POST['inside_families_current'][$index] !== '' ? (int)$_POST['inside_families_current'][$index] : null,
                'inside_persons_cumulative' => isset($_POST['inside_persons_cumulative'][$index]) && $_POST['inside_persons_cumulative'][$index] !== '' ? (int)$_POST['inside_persons_cumulative'][$index] : null,
                'inside_persons_current' => isset($_POST['inside_persons_current'][$index]) && $_POST['inside_persons_current'][$index] !== '' ? (int)$_POST['inside_persons_current'][$index] : null,
                'outside_families_cumulative' => isset($_POST['outside_families_cumulative'][$index]) && $_POST['outside_families_cumulative'][$index] !== '' ? (int)$_POST['outside_families_cumulative'][$index] : null,
                'outside_families_current' => isset($_POST['outside_families_current'][$index]) && $_POST['outside_families_current'][$index] !== '' ? (int)$_POST['outside_families_current'][$index] : null,
                'outside_persons_cumulative' => isset($_POST['outside_persons_cumulative'][$index]) && $_POST['outside_persons_cumulative'][$index] !== '' ? (int)$_POST['outside_persons_cumulative'][$index] : null,
                'outside_persons_current' => isset($_POST['outside_persons_current'][$index]) && $_POST['outside_persons_current'][$index] !== '' ? (int)$_POST['outside_persons_current'][$index] : null,
                'total_families_cumulative' => isset($_POST['total_families_cumulative'][$index]) && $_POST['total_families_cumulative'][$index] !== '' ? (int)$_POST['total_families_cumulative'][$index] : null,
                'total_families_current' => isset($_POST['total_families_current'][$index]) && $_POST['total_families_current'][$index] !== '' ? (int)$_POST['total_families_current'][$index] : null,
                'total_persons_cumulative' => isset($_POST['total_persons_cumulative'][$index]) && $_POST['total_persons_cumulative'][$index] !== '' ? (int)$_POST['total_persons_cumulative'][$index] : null,
                'total_persons_current' => isset($_POST['total_persons_current'][$index]) && $_POST['total_persons_current'][$index] !== '' ? (int)$_POST['total_persons_current'][$index] : null,
                'remarks' => sanitize($_POST['remarks'][$index] ?? '')
            ];
            
            // Validate entry
            $entry_errors = validate_annex2_entry($entry_data);
            if (!empty($entry_errors)) {
                $validation_errors = array_merge($validation_errors, $entry_errors);
                $error_count++;
                continue;
            }
            
            // Verify barangay access for non-admin users
            if (!is_admin() && $entry_data['barangay'] !== $user_barangay) {
                log_security_event($_SESSION['user_id'], 'unauthorized_barangay_access', 
                    "Attempted to submit data for barangay: " . $entry_data['barangay']);
                $error_count++;
                continue;
            }
            
            // Insert into database using prepared statement
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO annex2_affected_population 
                    (region, province, city, barangay, affected_families_cumulative, affected_families_current, 
                     affected_persons_cumulative, affected_persons_current, num_ecs_cumulative, num_ecs_current,
                     inside_families_cumulative, inside_families_current, inside_persons_cumulative, inside_persons_current,
                     outside_families_cumulative, outside_families_current, outside_persons_cumulative, outside_persons_current,
                     total_families_cumulative, total_families_current, total_persons_cumulative, total_persons_current,
                     remarks, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $result = $stmt->execute([
                    $entry_data['region'], 
                    $entry_data['province'], 
                    $entry_data['city'], 
                    $entry_data['barangay'],
                    $entry_data['affected_families_cumulative'], 
                    $entry_data['affected_families_current'], 
                    $entry_data['affected_persons_cumulative'],
                    $entry_data['affected_persons_current'],
                    $entry_data['num_ecs_cumulative'],
                    $entry_data['num_ecs_current'],
                    $entry_data['inside_families_cumulative'],
                    $entry_data['inside_families_current'],
                    $entry_data['inside_persons_cumulative'],
                    $entry_data['inside_persons_current'],
                    $entry_data['outside_families_cumulative'],
                    $entry_data['outside_families_current'],
                    $entry_data['outside_persons_cumulative'],
                    $entry_data['outside_persons_current'],
                    $entry_data['total_families_cumulative'],
                    $entry_data['total_families_current'],
                    $entry_data['total_persons_cumulative'],
                    $entry_data['total_persons_current'],
                    $entry_data['remarks'],
                    $_SESSION['user_id']
                ]);
                
                if ($result) {
                    $success_count++;
                    
                    // FIXED: Use log_audit_action instead of log_audit_event
                    log_audit_action($_SESSION['user_id'], 'annex2_submission', 
                        "Submitted affected population report for " . $entry_data['barangay']);
                } else {
                    $error_count++;
                }
            } catch (PDOException $e) {
                // Log error but don't expose details to user
                error_log("Annex2 database error for user {$_SESSION['user_id']}: " . $e->getMessage());
                $error_count++;
            }
        }
    }
    
    // Set flash messages
    if ($success_count > 0) {
        set_flash("Successfully saved $success_count affected population report(s).", 'success');
    }
    if ($error_count > 0) {
        $error_msg = "Failed to save $error_count report(s).";
        if (!empty($validation_errors)) {
            $error_msg .= " Validation errors: " . implode(', ', array_slice($validation_errors, 0, 3));
        }
        set_flash($error_msg, 'error');
    }
    
    header('Location: annex2.php');
    exit;
}

// Generate CSRF token
$csrf_token = generate_token();

// Start output buffering for sidenav
ob_start();
?>
<style>
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
            padding: clamp(20px, 4vw, 25px);
            margin: 0 auto;
            width: 100%;
            max-width: 98vw;
            position: relative;
            min-height: 400px;
        }
        .header-section {
            text-align: center;
            margin-bottom: clamp(20px, 4vw, 25px);
            border-bottom: 2px solid #dee2e6;
            padding-bottom: clamp(10px, 2.5vw, 15px);
        }
        .header-section h1 {
            font-size: clamp(1.5rem, 4vw, 1.8rem);
            font-weight: bold;
            margin-bottom: 8px;
        }
        .header-section h2 {
            font-size: clamp(1.2rem, 3.5vw, 1.4rem);
            font-weight: bold;
            color: #333;
        }
        .entry-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: clamp(20px, 4vw, 25px);
            margin-bottom: clamp(20px, 4vw, 25px);
            background-color: #fff;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .section-title {
            font-size: clamp(1.1rem, 3vw, 1.2rem);
            font-weight: 600;
            color: #0d6efd;
            margin-bottom: 12px;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 6px;
        }
        .entry-card .row {
            row-gap: 15px;
        }
        .form-label {
            font-size: clamp(0.95rem, 2.5vw, 1rem);
            font-weight: 500;
            margin-bottom: 6px;
        }
        .form-control, .form-select {
            font-size: clamp(0.9rem, 2.5vw, 1rem);
            padding: clamp(8px, 2vw, 10px);
            width: 100%;
            border-radius: 6px;
            height: 48px;
        }
        .form-control::placeholder {
            color: #6c757d;
            opacity: 0.7;
        }
        .number-input {
            text-align: right;
        }
        .btn {
            font-size: clamp(0.95rem, 2.5vw, 1rem);
            padding: clamp(10px, 2.5vw, 12px) clamp(20px, 4vw, 25px);
            border-radius: 6px;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-add-entry {
            background-color: #0d6efd;
            border: none;
        }
        .btn-add-entry:hover {
            background-color: #0b5ed7;
        }
        .btn-remove {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        .btn-remove:hover {
            background-color: #c82333;
        }
        .btn-submit {
            background-color: #198754;
            border: none;
        }
        .btn-submit:hover {
            background-color: #157347;
        }
        .btn-technical-notes {
            background-color: #6c757d;
            border: none;
        }
        .btn-technical-notes:hover {
            background-color: #5a6268;
        }
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: clamp(15px, 3vw, 20px);
            gap: 15px;
        }
        .action-buttons > div {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .bg-light {
            background-color: #f8f9fa !important;
            cursor: not-allowed;
        }
        
        /* Subsection cards */
        .subsection-card {
            margin: 6px 0;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        
        .subsection-card .card-header {
            font-size: 0.98rem;
            font-weight: 700;
            padding: 0.45rem 0.75rem;
            border-radius: 0;
        }
        
        .subsection-card .card-body {
            padding: 0.5rem 0.75rem 0.75rem 0.75rem;
        }
        
        /* FLAT COLORS - Inside ECs */
        .subsection-card.subsection-inside .card-header,
        .subsection-card .card-header.subsection-inside {
            background-color: #e7f3ff;
            color: #0d6efd;
            border-bottom: 1px solid rgba(13,110,253,0.08);
        }
        
        /* FLAT COLORS - Outside ECs */
        .subsection-card.subsection-outside .card-header,
        .subsection-card .card-header.subsection-outside {
            background-color: #fff4e6;
            color: #fd7e14;
            border-bottom: 1px solid rgba(253,126,20,0.06);
        }
        
        /* FLAT COLORS - Total Displaced */
        .subsection-card.subsection-total .card-header,
        .subsection-card .card-header.subsection-total {
            background-color: #eefcf1;
            color: #198754;
            border-bottom: 1px solid rgba(25,135,84,0.06);
        }
        
        hr.subsection-sep {
            border: 0;
            border-top: 1px solid #e9ecef;
            margin: 8px 0;
        }
        
        /* Auto-calculation indicator */
        .auto-calc-indicator {
            font-size: 0.8rem;
            color: #6c757d;
            font-style: italic;
            margin-top: 2px;
        }
        
        /* Skeleton Loader Styles */
        .skeleton-loader {
            position: relative;
            width: 100%;
            background: white;
            border-radius: 10px;
            padding: clamp(20px, 4vw, 25px);
        }
        
        .skeleton-entry {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: clamp(20px, 4vw, 25px);
            margin-bottom: clamp(20px, 4vw, 25px);
            background-color: #fff;
        }
        
        .skeleton-line {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .skeleton-title {
            height: 24px;
            width: 40%;
            margin-bottom: 20px;
        }
        
        .skeleton-label {
            height: 16px;
            width: 60%;
            margin-bottom: 8px;
        }
        
        .skeleton-input {
            height: 48px;
            width: 100%;
        }
        
        .skeleton-button {
            height: 48px;
            width: 150px;
            border-radius: 6px;
        }
        
        .skeleton-action-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: clamp(15px, 3vw, 20px);
            gap: 15px;
        }
        
        .skeleton-action-buttons > div {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        @keyframes loading {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }
        
        /* Fade in animation for form content */
        .form-content {
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.5s ease, transform 0.5s ease;
            display: none;
        }
        
        .form-content.loaded {
            opacity: 1;
            transform: translateY(0);
            display: block;
        }
        
        /* Lazy loading for new entries */
        .lazy-entry {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }
        
        .lazy-entry.loaded {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .action-buttons, .skeleton-action-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            .action-buttons > div, .skeleton-action-buttons > div {
                flex-direction: column;
            }
            .btn, .skeleton-button {
                width: 100%;
            }
            .entry-card .row > div {
                min-width: 0;
            }
        }
        @media (max-width: 576px) {
            .modal-dialog {
                margin: 0.5rem;
            }
            .modal-body .row {
                flex-direction: column;
            }
            .modal-body .col-md-6 {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>

    <div class="form-container">
        <!-- Skeleton Loader for Initial Page Load -->
        <div id="pageSkeletonLoader" class="skeleton-loader">
            <!-- Skeleton Header -->
            <div class="header-section">
                <div class="skeleton-line skeleton-title" style="width: 70%; margin: 0 auto 8px;"></div>
                <div class="skeleton-line skeleton-title" style="width: 50%; margin: 0 auto;"></div>
            </div>
            
            <!-- Skeleton Entry Card -->
            <div class="skeleton-entry">
                <div class="skeleton-line skeleton-title" style="width: 30%;"></div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 40%;"></div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 35%;"></div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <div class="skeleton-line skeleton-button" style="width: 140px;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Skeleton Action Buttons -->
            <div class="skeleton-action-buttons">
                <div>
                    <div class="skeleton-line skeleton-button" style="width: 130px;"></div>
                    <div class="skeleton-line skeleton-button" style="width: 180px;"></div>
                </div>
                <div class="skeleton-line skeleton-button" style="width: 130px;"></div>
            </div>
        </div>

        <!-- Actual Form Content (Initially Hidden) -->
        <div id="formContent" class="form-content">
            <div class="header-section">
                <h1>NDRRMC Memorandum Circular No. 05, s. 2025</h1>
                <h2>Annex 2: Affected Population</h2>
                
                <?php if ($user_role === 'admin'): ?>
                    <div class="alert alert-info mt-3">
                        <strong>Admin Mode:</strong> You are currently reporting for 
                        <strong><?= htmlspecialchars($user_barangay) ?></strong> barangay.
                        <br>
                        <small class="text-muted">
                            To change barangay, visit the 
                            <a href="../admin/barangay_locking.php" class="alert-link">Barangay Locking Management</a> page.
                        </small>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Flash Messages -->
            <?php show_flash(); ?>

            <form id="affectedPopulationForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div id="entriesContainer">
                    <!-- Initial entry with PREDEFINED CUMULATIVE DATA -->
                    <div class="entry-card lazy-entry loaded">
                        <div class="section-title">Location Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="region-0" class="form-label">Region</label>
                                <?php if ($user_role === 'admin'): ?>
                                    <input type="text" class="form-control" id="region-0" name="region[]" value="REGION II" required maxlength="100">
                                <?php else: ?>
                                    <input type="text" class="form-control bg-light" id="region-0" name="region[]" value="REGION II" readonly>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="province-0" class="form-label">Province</label>
                                <?php if ($user_role === 'admin'): ?>
                                    <input type="text" class="form-control" id="province-0" name="province[]" value="CAGAYAN" required maxlength="100">
                                <?php else: ?>
                                    <input type="text" class="form-control bg-light" id="province-0" name="province[]" value="CAGAYAN" readonly>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="city-0" class="form-label">City/Municipality</label>
                                <?php if ($user_role === 'admin'): ?>
                                    <input type="text" class="form-control" id="city-0" name="city[]" value="BAGGAO" required maxlength="100">
                                <?php else: ?>
                                    <input type="text" class="form-control bg-light" id="city-0" name="city[]" value="BAGGAO" readonly>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="barangay-0" class="form-label">Affected Barangays</label>
                                <input type="text" class="form-control bg-light" id="barangay-0" name="barangay[]" 
                                    value="<?= htmlspecialchars($user_barangay) ?>" readonly>
                            </div>
                        </div>

                        <div class="section-title mt-4">Affected Population</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="affected_families_cumulative-0" class="form-label">Affected Families (Cumulative)</label>
                                <input type="number" class="form-control number-input bg-light" id="affected_families_cumulative-0" name="affected_families_cumulative[]" value="<?php echo $cumulative_settings['affected_families_cumulative'] ?? 0; ?>" readonly min="0" max="999999999">
                                <div class="auto-calc-indicator">From settings</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="affected_families_current-0" class="form-label">Affected Families (Current)</label>
                                <input type="number" class="form-control number-input" id="affected_families_current-0" name="affected_families_current[]" placeholder="0" min="0" max="999999999">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="affected_persons_cumulative-0" class="form-label">Affected Persons (Cumulative)</label>
                                <input type="number" class="form-control number-input bg-light" id="affected_persons_cumulative-0" name="affected_persons_cumulative[]" value="<?php echo $cumulative_settings['affected_persons_cumulative'] ?? 0; ?>" readonly min="0" max="999999999">
                                <div class="auto-calc-indicator">From settings</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="affected_persons_current-0" class="form-label">Affected Persons (Current)</label>
                                <input type="number" class="form-control number-input" id="affected_persons_current-0" name="affected_persons_current[]" placeholder="0" min="0" max="999999999">
                            </div>
                        </div>

                        <div class="section-title mt-4">Evacuation Centers (ECs)</div>
                        <div class="row g-3">
                        <div class="col-md-6 col-lg-6">
                            <label for="num_ecs_cumulative-0" class="form-label">Number of ECs (Cumulative)</label>
                            <input type="number" class="form-control number-input bg-light" id="num_ecs_cumulative-0" name="num_ecs_cumulative[]" value="<?php echo $cumulative_settings['num_ecs_cumulative'] ?? 0; ?>" readonly min="0" max="999999999">
                            <div class="auto-calc-indicator">From settings</div>
                        </div>
                            <div class="col-md-6 col-lg-6">
                                <label for="num_ecs_current-0" class="form-label">Number of ECs (Current)</label>
                                <input type="number" class="form-control number-input" id="num_ecs_current-0" name="num_ecs_current[]" placeholder="0" min="0" max="999999999">
                            </div>
                        </div>

                        <hr class="subsection-sep">
                        <div class="card subsection-card subsection-inside">
                            <div class="card-header subsection-inside">Inside ECs</div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-3">
                                        <label for="inside_families_cumulative-0" class="form-label">Families (Cumulative)</label>
                                        <input type="number" class="form-control number-input bg-light" id="inside_families_cumulative-0" name="inside_families_cumulative[]" value="<?php echo $cumulative_settings['inside_families_cumulative'] ?? 0; ?>" readonly min="0" max="999999999">
                                        <div class="auto-calc-indicator">From settings</div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label for="inside_families_current-0" class="form-label">Families (Current)</label>
                                        <input type="number" class="form-control number-input" id="inside_families_current-0" name="inside_families_current[]" placeholder="0" min="0" max="999999999" oninput="calculateTotals(0)">
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label for="inside_persons_cumulative-0" class="form-label">Persons (Cumulative)</label>
                                        <input type="number" class="form-control number-input bg-light" id="inside_persons_cumulative-0" name="inside_persons_cumulative[]" value="<?php echo $cumulative_settings['inside_persons_cumulative'] ?? 0; ?>" readonly min="0" max="999999999">
                                        <div class="auto-calc-indicator">From settings</div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label for="inside_persons_current-0" class="form-label">Persons (Current)</label>
                                        <input type="number" class="form-control number-input" id="inside_persons_current-0" name="inside_persons_current[]" placeholder="0" min="0" max="999999999" oninput="calculateTotals(0)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card subsection-card subsection-outside mt-3">
                            <div class="card-header subsection-outside">Outside ECs</div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-3">
                                        <label for="outside_families_cumulative-0" class="form-label">Families (Cumulative)</label>
                                        <input type="number" class="form-control number-input bg-light" id="outside_families_cumulative-0" name="outside_families_cumulative[]" value="<?php echo $cumulative_settings['outside_families_cumulative'] ?? 0; ?>" readonly min="0" max="999999999">
                                        <div class="auto-calc-indicator">From settings</div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label for="outside_families_current-0" class="form-label">Families (Current)</label>
                                        <input type="number" class="form-control number-input" id="outside_families_current-0" name="outside_families_current[]" placeholder="0" min="0" max="999999999" oninput="calculateTotals(0)">
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label for="outside_persons_cumulative-0" class="form-label">Persons (Cumulative)</label>
                                        <input type="number" class="form-control number-input bg-light" id="outside_persons_cumulative-0" name="outside_persons_cumulative[]" value="<?php echo $cumulative_settings['outside_persons_cumulative'] ?? 0; ?>" readonly min="0" max="999999999">
                                        <div class="auto-calc-indicator">From settings</div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label for="outside_persons_current-0" class="form-label">Persons (Current)</label>
                                        <input type="number" class="form-control number-input" id="outside_persons_current-0" name="outside_persons_current[]" placeholder="0" min="0" max="999999999" oninput="calculateTotals(0)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card subsection-card subsection-total mt-3">
                            <div class="card-header subsection-total">Total Displaced</div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-3">
                                        <label for="total_families_cumulative-0" class="form-label">Families (Cumulative)</label>
                                        <input type="number" class="form-control number-input bg-light" id="total_families_cumulative-0" name="total_families_cumulative[]" value="<?php echo $cumulative_settings['total_families_cumulative'] ?? 0; ?>" readonly min="0" max="999999999">
                                        <div class="auto-calc-indicator">From settings</div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label for="total_families_current-0" class="form-label">Families (Current)</label>
                                        <input type="number" class="form-control number-input bg-light" id="total_families_current-0" name="total_families_current[]" value="0" readonly min="0" max="999999999">
                                        <div class="auto-calc-indicator">Auto-calculated</div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label for="total_persons_cumulative-0" class="form-label">Persons (Cumulative)</label>
                                        <input type="number" class="form-control number-input bg-light" id="total_persons_cumulative-0" name="total_persons_cumulative[]" value="<?php echo $cumulative_settings['total_persons_cumulative'] ?? 0; ?>" readonly min="0" max="999999999">
                                        <div class="auto-calc-indicator">From settings</div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label for="total_persons_current-0" class="form-label">Persons (Current)</label>
                                        <input type="number" class="form-control number-input bg-light" id="total_persons_current-0" name="total_persons_current[]" value="0" readonly min="0" max="999999999">
                                        <div class="auto-calc-indicator">Auto-calculated</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mt-4">Additional Information</div>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="remarks-0" class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks-0" name="remarks[]" placeholder="Enter remarks" maxlength="500"></textarea>
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-remove" onclick="removeEntry(this)">Remove Entry</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <div>
                        <button type="button" class="btn btn-primary btn-add-entry" id="addEntryBtn">Add Entry</button>
                        <button type="button" class="btn btn-secondary btn-technical-notes" data-bs-toggle="modal" data-bs-target="#technicalNotesModal">
                            View Technical Notes
                        </button>
                    </div>
                    <button type="submit" class="btn btn-success btn-submit">Save Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Technical Notes Modal -->
    <div class="modal fade" id="technicalNotesModal" tabindex="-1" aria-labelledby="technicalNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="technicalNotesModalLabel">Technical Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Region, Province, City/Municipality, Barangay</strong><br>
                            Specify the exact location of the affected population being reported.</p>
                            <p><strong>Affected Population</strong><br>
                            Report both cumulative (total since onset) and current (latest situation) figures for affected families and persons.</p>
                            <p><strong>Evacuation Centers (ECs)</strong><br>
                            Indicate the number of evacuation centers established, both cumulative and current.</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Inside ECs</strong><br>
                            Report the number of families and persons currently staying inside evacuation centers.</p>
                            <p><strong>Outside ECs</strong><br>
                            Report the number of families and persons displaced but staying outside evacuation centers (e.g., with relatives, other shelters).</p>
                            <p><strong>Total Displaced</strong><br>
                            The sum of inside ECs and outside ECs populations. This is automatically calculated from the current values.</p>
                            <p><strong>Remarks</strong><br>
                            Include any other relevant details or contextual information about the affected population.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let entryCount = 1;

        // Initialize page with skeleton loader
        document.addEventListener('DOMContentLoaded', function() {
            // Show skeleton loader initially
            const skeletonLoader = document.getElementById('pageSkeletonLoader');
            const formContent = document.getElementById('formContent');
            
            skeletonLoader.style.display = 'block';
            formContent.style.display = 'none';
            
            // Simulate page loading (in a real app, this would be actual data loading)
            setTimeout(function() {
                // Hide skeleton loader
                skeletonLoader.style.display = 'none';
                
                // Show form content with fade-in animation
                formContent.style.display = 'block';
                setTimeout(function() {
                    formContent.classList.add('loaded');
                }, 50);
            }, 1200); // 1.2 second loading simulation
        });

        // Calculate totals function
        function calculateTotals(index) {
            // Get current values
            const insideFamiliesCurrent = parseInt(document.getElementById(`inside_families_current-${index}`).value) || 0;
            const insidePersonsCurrent = parseInt(document.getElementById(`inside_persons_current-${index}`).value) || 0;
            const outsideFamiliesCurrent = parseInt(document.getElementById(`outside_families_current-${index}`).value) || 0;
            const outsidePersonsCurrent = parseInt(document.getElementById(`outside_persons_current-${index}`).value) || 0;
            
            // Calculate totals
            const totalFamiliesCurrent = insideFamiliesCurrent + outsideFamiliesCurrent;
            const totalPersonsCurrent = insidePersonsCurrent + outsidePersonsCurrent;
            
            // Update total fields
            document.getElementById(`total_families_current-${index}`).value = totalFamiliesCurrent;
            document.getElementById(`total_persons_current-${index}`).value = totalPersonsCurrent;
        }

        // Add entry functionality with skeleton loader
        document.getElementById('addEntryBtn').addEventListener('click', function() {
            const entriesContainer = document.getElementById('entriesContainer');
            const button = this;
            
            // Disable button during loading
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
            
            // Create skeleton entry
            const skeletonEntry = document.createElement('div');
            skeletonEntry.className = 'entry-card skeleton-entry';
            skeletonEntry.innerHTML = `
                <div class="skeleton-line skeleton-title" style="width: 30%;"></div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 40%;"></div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 35%;"></div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <div class="skeleton-line skeleton-button" style="width: 140px;"></div>
                    </div>
                </div>
            `;
            
            entriesContainer.appendChild(skeletonEntry);
            
            // Simulate loading delay for new entry
            setTimeout(function() {
                // Remove skeleton
                skeletonEntry.remove();
                
                // Add actual entry
                const newEntry = document.createElement('div');
                newEntry.className = 'entry-card lazy-entry';
                newEntry.innerHTML = `
                    <div class="section-title">Location Details</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label for="region-${entryCount}" class="form-label">Region</label>
                            <?php if ($user_role === 'admin'): ?>
                                <input type="text" class="form-control" id="region-${entryCount}" name="region[]" value="REGION II" required maxlength="100">
                            <?php else: ?>
                                <input type="text" class="form-control bg-light" id="region-${entryCount}" name="region[]" value="REGION II" readonly>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="province-${entryCount}" class="form-label">Province</label>
                            <?php if ($user_role === 'admin'): ?>
                                <input type="text" class="form-control" id="province-${entryCount}" name="province[]" value="CAGAYAN" required maxlength="100">
                            <?php else: ?>
                                <input type="text" class="form-control bg-light" id="province-${entryCount}" name="province[]" value="CAGAYAN" readonly>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="city-${entryCount}" class="form-label">City/Municipality</label>
                            <?php if ($user_role === 'admin'): ?>
                                <input type="text" class="form-control" id="city-${entryCount}" name="city[]" value="BAGGAO" required maxlength="100">
                            <?php else: ?>
                                <input type="text" class="form-control bg-light" id="city-${entryCount}" name="city[]" value="BAGGAO" readonly>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="barangay-${entryCount}" class="form-label">Affected Barangays</label>
                            <input type="text" class="form-control bg-light" id="barangay-${entryCount}" name="barangay[]" 
                                value="<?= htmlspecialchars($user_barangay) ?>" readonly>
                        </div>
                    </div>

                    <div class="section-title mt-4">Affected Population</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label for="affected_families_cumulative-${entryCount}" class="form-label">Affected Families (Cumulative)</label>
                            <input type="number" class="form-control number-input bg-light" id="affected_families_cumulative-${entryCount}" name="affected_families_cumulative[]" value="1250" readonly min="0" max="999999999">
                            <div class="auto-calc-indicator">Predefined data</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="affected_families_current-${entryCount}" class="form-label">Affected Families (Current)</label>
                            <input type="number" class="form-control number-input" id="affected_families_current-${entryCount}" name="affected_families_current[]" placeholder="0" min="0" max="999999999">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="affected_persons_cumulative-${entryCount}" class="form-label">Affected Persons (Cumulative)</label>
                            <input type="number" class="form-control number-input bg-light" id="affected_persons_cumulative-${entryCount}" name="affected_persons_cumulative[]" value="5840" readonly min="0" max="999999999">
                            <div class="auto-calc-indicator">Predefined data</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="affected_persons_current-${entryCount}" class="form-label">Affected Persons (Current)</label>
                            <input type="number" class="form-control number-input" id="affected_persons_current-${entryCount}" name="affected_persons_current[]" placeholder="0" min="0" max="999999999">
                        </div>
                    </div>

                    <div class="section-title mt-4">Evacuation Centers (ECs)</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-6">
                            <label for="num_ecs_cumulative-${entryCount}" class="form-label">Number of ECs (Cumulative)</label>
                            <input type="number" class="form-control number-input bg-light" id="num_ecs_cumulative-${entryCount}" name="num_ecs_cumulative[]" value="15" readonly min="0" max="999999999">
                            <div class="auto-calc-indicator">Predefined data</div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <label for="num_ecs_current-${entryCount}" class="form-label">Number of ECs (Current)</label>
                            <input type="number" class="form-control number-input" id="num_ecs_current-${entryCount}" name="num_ecs_current[]" placeholder="0" min="0" max="999999999">
                        </div>
                    </div>

                    <hr class="subsection-sep">
                    <div class="card subsection-card subsection-inside">
                        <div class="card-header subsection-inside">Inside ECs</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-3">
                                    <label for="inside_families_cumulative-${entryCount}" class="form-label">Families (Cumulative)</label>
                                    <input type="number" class="form-control number-input bg-light" id="inside_families_cumulative-${entryCount}" name="inside_families_cumulative[]" value="850" readonly min="0" max="999999999">
                                    <div class="auto-calc-indicator">Predefined data</div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="inside_families_current-${entryCount}" class="form-label">Families (Current)</label>
                                    <input type="number" class="form-control number-input" id="inside_families_current-${entryCount}" name="inside_families_current[]" placeholder="0" min="0" max="999999999" oninput="calculateTotals(${entryCount})">
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="inside_persons_cumulative-${entryCount}" class="form-label">Persons (Cumulative)</label>
                                    <input type="number" class="form-control number-input bg-light" id="inside_persons_cumulative-${entryCount}" name="inside_persons_cumulative[]" value="3980" readonly min="0" max="999999999">
                                    <div class="auto-calc-indicator">Predefined data</div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="inside_persons_current-${entryCount}" class="form-label">Persons (Current)</label>
                                    <input type="number" class="form-control number-input" id="inside_persons_current-${entryCount}" name="inside_persons_current[]" placeholder="0" min="0" max="999999999" oninput="calculateTotals(${entryCount})">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card subsection-card subsection-outside mt-3">
                        <div class="card-header subsection-outside">Outside ECs</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-3">
                                    <label for="outside_families_cumulative-${entryCount}" class="form-label">Families (Cumulative)</label>
                                    <input type="number" class="form-control number-input bg-light" id="outside_families_cumulative-${entryCount}" name="outside_families_cumulative[]" value="400" readonly min="0" max="999999999">
                                    <div class="auto-calc-indicator">Predefined data</div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="outside_families_current-${entryCount}" class="form-label">Families (Current)</label>
                                    <input type="number" class="form-control number-input" id="outside_families_current-${entryCount}" name="outside_families_current[]" placeholder="0" min="0" max="999999999" oninput="calculateTotals(${entryCount})">
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="outside_persons_cumulative-${entryCount}" class="form-label">Persons (Cumulative)</label>
                                    <input type="number" class="form-control number-input bg-light" id="outside_persons_cumulative-${entryCount}" name="outside_persons_cumulative[]" value="1860" readonly min="0" max="999999999">
                                    <div class="auto-calc-indicator">Predefined data</div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="outside_persons_current-${entryCount}" class="form-label">Persons (Current)</label>
                                    <input type="number" class="form-control number-input" id="outside_persons_current-${entryCount}" name="outside_persons_current[]" placeholder="0" min="0" max="999999999" oninput="calculateTotals(${entryCount})">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card subsection-card subsection-total mt-3">
                        <div class="card-header subsection-total">Total Displaced</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-3">
                                    <label for="total_families_cumulative-${entryCount}" class="form-label">Families (Cumulative)</label>
                                    <input type="number" class="form-control number-input bg-light" id="total_families_cumulative-${entryCount}" name="total_families_cumulative[]" value="1250" readonly min="0" max="999999999">
                                    <div class="auto-calc-indicator">Auto-calculated</div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="total_families_current-${entryCount}" class="form-label">Families (Current)</label>
                                    <input type="number" class="form-control number-input bg-light" id="total_families_current-${entryCount}" name="total_families_current[]" value="0" readonly min="0" max="999999999">
                                    <div class="auto-calc-indicator">Auto-calculated</div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="total_persons_cumulative-${entryCount}" class="form-label">Persons (Cumulative)</label>
                                    <input type="number" class="form-control number-input bg-light" id="total_persons_cumulative-${entryCount}" name="total_persons_cumulative[]" value="5840" readonly min="0" max="999999999">
                                    <div class="auto-calc-indicator">Auto-calculated</div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="total_persons_current-${entryCount}" class="form-label">Persons (Current)</label>
                                    <input type="number" class="form-control number-input bg-light" id="total_persons_current-${entryCount}" name="total_persons_current[]" value="0" readonly min="0" max="999999999">
                                    <div class="auto-calc-indicator">Auto-calculated</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-title mt-4">Additional Information</div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="remarks-${entryCount}" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks-${entryCount}" name="remarks[]" placeholder="Enter remarks" maxlength="500"></textarea>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-remove" onclick="removeEntry(this)">Remove Entry</button>
                        </div>
                    </div>
                `;
                entriesContainer.appendChild(newEntry);
                
                // Trigger lazy loading animation
                setTimeout(function() {
                    newEntry.classList.add('loaded');
                }, 50);
                
                // Re-enable button
                button.disabled = false;
                button.innerHTML = 'Add Entry';
                
                entryCount++;
            }, 800); // 800ms loading delay for new entries
        });

        // Remove entry functionality with animation
        function removeEntry(button) {
            const entry = button.closest('.entry-card');
            const entriesContainer = document.getElementById('entriesContainer');
            
            // Don't remove if it's the only entry
            if (entriesContainer.children.length > 1) {
                // Add fade-out animation
                entry.style.opacity = '0';
                entry.style.transform = 'translateY(-20px)';
                
                setTimeout(function() {
                    entry.remove();
                }, 300);
            } else {
                alert("You need at least one entry in the form.");
            }
        }

        // Handle form submission with loading state
        document.getElementById('affectedPopulationForm').addEventListener('submit', function(e) {
            const submitButton = this.querySelector('.btn-submit');
            const originalText = submitButton.innerHTML;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            
            // Form will submit normally, the button will reset on page reload
        });

        // Client-side validation
        document.getElementById('affectedPopulationForm').addEventListener('submit', function(e) {
            let isValid = true;
            const entries = this.querySelectorAll('.entry-card');
            
            entries.forEach((entry, index) => {
                const region = entry.querySelector('input[name="region[]"]');
                const province = entry.querySelector('input[name="province[]"]');
                const city = entry.querySelector('input[name="city[]"]');
                const barangay = entry.querySelector('input[name="barangay[]"]');
                
                // Skip validation if all fields are empty
                if (!region.value && !province.value && !city.value && !barangay.value) {
                    return;
                }
                
                // Check required fields
                if (!region.value || !province.value || !city.value || !barangay.value) {
                    isValid = false;
                    alert(`Please fill all location fields in entry ${index + 1}`);
                    return;
                }
                
                // Check number ranges
                const numberInputs = entry.querySelectorAll('input[type="number"]');
                numberInputs.forEach(input => {
                    if (input.value !== '' && (input.value < 0 || input.value > 999999999)) {
                        isValid = false;
                        alert(`Value must be between 0 and 999,999,999 in entry ${index + 1}`);
                        return;
                    }
                });
            });
            
            if (!isValid) {
                e.preventDefault();
                const submitButton = this.querySelector('.btn-submit');
                submitButton.disabled = false;
                submitButton.innerHTML = 'Save Report';
            }
        });
    </script>

<?php
// Get the captured content and store it in a variable
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content
require_once '../includes/sidenav.php';
?>
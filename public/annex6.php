<?php
// Define page title for the header
define('PAGE_TITLE', 'Annex 6 - Damage to Infrastructure');

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
    header('Location: login.php');
    exit;
}

// Log form access for audit tracking
log_audit_action($_SESSION['user_id'], 'annex6_form_access', 'User accessed the Annex 6 Infrastructure Damage form');

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_token($_POST['csrf_token'])) {
        set_flash('Security token validation failed. Please try again.', 'error');
        header('Location: annex6.php');
        exit;
    }

    // Rate limiting - 10 submissions per 5 minutes
    if (!check_rate_limit($_SESSION['user_id'], 'annex6_submission', 10, 300)) {
        set_flash('Too many submission attempts. Please wait a few minutes before trying again.', 'error');
        header('Location: annex6.php');
        exit;
    }

    // Process each entry
    $success_count = 0;
    $error_count = 0;
    $validation_errors = [];

    if (isset($_POST['region']) && is_array($_POST['region'])) {
        try {
            $pdo->beginTransaction();

            foreach ($_POST['region'] as $index => $region) {
                // Skip empty entries
                if (empty($region) &&
                    empty($_POST['province'][$index]) &&
                    empty($_POST['city'][$index]) &&
                    empty($_POST['barangay'][$index]) &&
                    empty($_POST['type'][$index])) {
                    continue;
                }

                // Sanitize inputs
                $entry_data = [
                    'region' => sanitize($region),
                    'province' => sanitize($_POST['province'][$index] ?? ''),
                    'city' => sanitize($_POST['city'][$index] ?? ''),
                    'barangay' => sanitize($_POST['barangay'][$index] ?? ''),
                    'type' => sanitize($_POST['type'][$index] ?? ''),
                    'classification' => sanitize($_POST['classification'][$index] ?? ''),
                    'name' => sanitize($_POST['name'][$index] ?? ''),
                    'totally_damaged' => intval($_POST['totallyDamaged'][$index] ?? 0),
                    'partially_damaged' => intval($_POST['partiallyDamaged'][$index] ?? 0),
                    'total_damaged' => intval($_POST['totalDamaged'][$index] ?? 0),
                    'unit' => sanitize($_POST['unit'][$index] ?? ''),
                    'quantity' => floatval($_POST['quantity'][$index] ?? 0),
                    'cost' => sanitize($_POST['cost'][$index] ?? ''),
                    'remarks' => sanitize($_POST['remarks'][$index] ?? '')
                ];

                // Validate required fields
                if (empty($entry_data['region']) || empty($entry_data['province']) ||
                    empty($entry_data['city']) || empty($entry_data['barangay']) ||
                    empty($entry_data['type'])) {
                    $validation_errors[] = "Entry " . ($index + 1) . ": Missing required fields";
                    $error_count++;
                    continue;
                }

                // Validate type enum
                $valid_types = ['Road', 'Bridge', 'Flood Control', 'Government Facilities',
                               'Health Facilities', 'Schools', 'Cultural Heritage',
                               'Utility Service Facilities', 'Private'];
                if (!in_array($entry_data['type'], $valid_types)) {
                    $validation_errors[] = "Entry " . ($index + 1) . ": Invalid infrastructure type";
                    $error_count++;
                    continue;
                }

                // Validate classification enum
                if (!empty($entry_data['classification'])) {
                    $valid_classifications = ['National', 'Local'];
                    if (!in_array($entry_data['classification'], $valid_classifications)) {
                        $validation_errors[] = "Entry " . ($index + 1) . ": Invalid classification";
                        $error_count++;
                        continue;
                    }
                }

                // Verify barangay access for non-admin users
                if (!is_admin() && $entry_data['barangay'] !== $user_barangay) {
                    log_security_event($_SESSION['user_id'], 'unauthorized_barangay_access',
                        "Attempted to submit data for barangay: " . $entry_data['barangay']);
                    $error_count++;
                    continue;
                }

                // Validate string lengths
                if (strlen($entry_data['name']) > 255) {
                    $validation_errors[] = "Entry " . ($index + 1) . ": Infrastructure name too long";
                    $error_count++;
                    continue;
                }

                if (strlen($entry_data['remarks']) > 500) {
                    $validation_errors[] = "Entry " . ($index + 1) . ": Remarks too long";
                    $error_count++;
                    continue;
                }

                // Insert into database using prepared statement
                $stmt = $pdo->prepare("
                    INSERT INTO annex6_infrastructure_damage
                    (region, province, city, barangay, type, classification, name,
                     totally_damaged, partially_damaged, total_damaged, unit, quantity,
                     cost, remarks, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $result = $stmt->execute([
                    $entry_data['region'],
                    $entry_data['province'],
                    $entry_data['city'],
                    $entry_data['barangay'],
                    $entry_data['type'],
                    $entry_data['classification'],
                    $entry_data['name'],
                    $entry_data['totally_damaged'],
                    $entry_data['partially_damaged'],
                    $entry_data['total_damaged'],
                    $entry_data['unit'],
                    $entry_data['quantity'],
                    $entry_data['cost'],
                    $entry_data['remarks'],
                    $_SESSION['user_id']
                ]);

                if ($result) {
                    $success_count++;

                    log_audit_action($_SESSION['user_id'], 'annex6_submission',
                        "Submitted infrastructure damage report for " . $entry_data['barangay'] .
                        ": " . $entry_data['type'] . " - " . $entry_data['name']);
                } else {
                    $error_count++;
                }
            }

            $pdo->commit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Annex6 database error for user {$_SESSION['user_id']}: " . $e->getMessage());
            set_flash('Database error occurred. Please try again.', 'error');
            header('Location: annex6.php');
            exit;
        }
    }

    // Set flash messages
    if ($success_count > 0) {
        set_flash("Successfully saved $success_count infrastructure damage report(s).", 'success');
    }
    if ($error_count > 0) {
        $error_msg = "Failed to save $error_count report(s).";
        if (!empty($validation_errors)) {
            $error_msg .= " Errors: " . implode(', ', array_slice($validation_errors, 0, 3));
        }
        set_flash($error_msg, 'error');
    }

    header('Location: annex6.php');
    exit;
}

// Generate CSRF token
$csrf_token = generate_token();

// Start output buffering for sidenav
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(PAGE_TITLE) ?> - NDRRMC System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: clamp(15px, 3vw, 20px);
            font-family: Arial, sans-serif;
        }
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
        .bg-light {
            background-color: #f8f9fa !important;
            cursor: not-allowed;
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
                <h2>Annex 6: Damage to Infrastructure</h2>

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

            <form id="infrastructureDamageForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div id="entriesContainer">
                    <!-- Initial entry -->
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
                                <label for="barangay-0" class="form-label">Barangay</label>
                                <input type="text" class="form-control bg-light" id="barangay-0" name="barangay[]"
                                    value="<?= htmlspecialchars($user_barangay) ?>" readonly>
                            </div>
                        </div>

                        <div class="section-title mt-4">Infrastructure Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="type-0" class="form-label">Type</label>
                                <select class="form-select" id="type-0" name="type[]" required>
                                    <option value="">Select Type</option>
                                    <option value="Road">Road</option>
                                    <option value="Bridge">Bridge</option>
                                    <option value="Flood Control">Flood Control</option>
                                    <option value="Government Facilities">Government Facilities</option>
                                    <option value="Health Facilities">Health Facilities</option>
                                    <option value="Schools">Schools</option>
                                    <option value="Cultural Heritage">Cultural Heritage</option>
                                    <option value="Utility Service Facilities">Utility Service Facilities</option>
                                    <option value="Private">Private</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="classification-0" class="form-label">Classification</label>
                                <select class="form-select" id="classification-0" name="classification[]">
                                    <option value="">Select Classification</option>
                                    <option value="National">National</option>
                                    <option value="Local">Local</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-6">
                                <label for="name-0" class="form-label">Name of Infrastructure</label>
                                <input type="text" class="form-control" id="name-0" name="name[]" placeholder="Enter infrastructure name" maxlength="255">
                            </div>
                        </div>

                        <div class="section-title mt-4">Damage Details</div>
                        <div class="row g-3">
                            <div class="col-md-4 col-lg-2">
                                <label for="totallyDamaged-0" class="form-label">Totally Damaged</label>
                                <input type="number" class="form-control number-input" id="totallyDamaged-0" name="totallyDamaged[]" placeholder="0" min="0" onchange="calculateTotal(this)">
                            </div>
                            <div class="col-md-4 col-lg-2">
                                <label for="partiallyDamaged-0" class="form-label">Partially Damaged</label>
                                <input type="number" class="form-control number-input" id="partiallyDamaged-0" name="partiallyDamaged[]" placeholder="0" min="0" onchange="calculateTotal(this)">
                            </div>
                            <div class="col-md-4 col-lg-2">
                                <label for="totalDamaged-0" class="form-label">Total Damaged</label>
                                <input type="number" class="form-control number-input" id="totalDamaged-0" name="totalDamaged[]" placeholder="0" readonly>
                            </div>
                            <div class="col-md-4 col-lg-2">
                                <label for="unit-0" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit-0" name="unit[]" placeholder="e.g., piece, meter" maxlength="50">
                            </div>
                            <div class="col-md-4 col-lg-2">
                                <label for="quantity-0" class="form-label">Quantity</label>
                                <input type="number" step="0.01" class="form-control number-input" id="quantity-0" name="quantity[]" placeholder="0" min="0">
                            </div>
                            <div class="col-md-4 col-lg-2">
                                <label for="cost-0" class="form-label">Cost</label>
                                <input type="text" class="form-control" id="cost-0" name="cost[]" placeholder="Enter cost" maxlength="100">
                            </div>
                        </div>

                        <div class="section-title mt-4">Additional Information</div>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="remarks-0" class="form-label">Remarks</label>
                                <input type="text" class="form-control" id="remarks-0" name="remarks[]" placeholder="Enter remarks" maxlength="500">
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
                            Specify the exact geographic location where the infrastructure damage occurred.</p>

                            <p><strong>Type</strong><br>
                            Input the appropriate category:
                            <ul>
                                <li>Road</li>
                                <li>Bridge</li>
                                <li>Flood Control</li>
                                <li>Government Facilities</li>
                                <li>Health Facilities</li>
                                <li>Schools</li>
                                <li>Cultural Heritage</li>
                                <li>Utility Service Facilities</li>
                                <li>Private</li>
                            </ul>
                            </p>

                            <p><strong>Classification</strong><br>
                            Indicate whether the damaged infrastructure is national or local.</p>

                            <p><strong>Name of Infrastructure</strong><br>
                            Provide the specific name of the infrastructure, if applicable (e.g., Dolores Bridge, Municipal Building). For schools, a detailed breakdown is reported separately via DepEd RADAR. Hence, no need to list school names—just encode the number of damaged schools in the Quantity column for each city or municipality.</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Number of Damaged Infrastructure</strong><br>
                            Indicate the number of infrastructure units damaged:
                            <ul>
                                <li>Totally Damaged</li>
                                <li>Partially Damaged</li>
                                <li>Total</li>
                            </ul>
                            </p>

                            <p><strong>Unit</strong><br>
                            Specify the unit of measurement used (e.g., piece, building, meter, etc.).</p>

                            <p><strong>Quantity</strong><br>
                            Provide the total quantity of the damaged item based on the unit indicated.</p>

                            <p><strong>Cost</strong><br>
                            Indicate the total estimated cost of infrastructure damage, if available.</p>

                            <p><strong>Remarks</strong><br>
                            Include any relevant observations, clarifications, or contextual details about the reported damage.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let entryCount = 1;

        // Initialize page with skeleton loader
        document.addEventListener('DOMContentLoaded', function() {
            const skeletonLoader = document.getElementById('pageSkeletonLoader');
            const formContent = document.getElementById('formContent');

            skeletonLoader.style.display = 'block';
            formContent.style.display = 'none';

            setTimeout(function() {
                skeletonLoader.style.display = 'none';
                formContent.style.display = 'block';
                setTimeout(function() {
                    formContent.classList.add('loaded');
                }, 50);
            }, 1200);
        });

        // Add entry functionality with skeleton loader
        document.getElementById('addEntryBtn').addEventListener('click', function() {
            const entriesContainer = document.getElementById('entriesContainer');
            const button = this;

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';

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
                        <label for="barangay-${entryCount}" class="form-label">Barangay</label>
                        <input type="text" class="form-control bg-light" id="barangay-${entryCount}" name="barangay[]"
                            value="<?= htmlspecialchars($user_barangay) ?>" readonly>
                    </div>
                </div>

                <div class="section-title mt-4">Infrastructure Details</div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <label for="type-${entryCount}" class="form-label">Type</label>
                        <select class="form-select" id="type-${entryCount}" name="type[]" required>
                            <option value="">Select Type</option>
                            <option value="Road">Road</option>
                            <option value="Bridge">Bridge</option>
                            <option value="Flood Control">Flood Control</option>
                            <option value="Government Facilities">Government Facilities</option>
                            <option value="Health Facilities">Health Facilities</option>
                            <option value="Schools">Schools</option>
                            <option value="Cultural Heritage">Cultural Heritage</option>
                            <option value="Utility Service Facilities">Utility Service Facilities</option>
                            <option value="Private">Private</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="classification-${entryCount}" class="form-label">Classification</label>
                        <select class="form-select" id="classification-${entryCount}" name="classification[]">
                            <option value="">Select Classification</option>
                            <option value="National">National</option>
                            <option value="Local">Local</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <label for="name-${entryCount}" class="form-label">Name of Infrastructure</label>
                        <input type="text" class="form-control" id="name-${entryCount}" name="name[]" placeholder="Enter infrastructure name" maxlength="255">
                    </div>
                </div>

                <div class="section-title mt-4">Damage Details</div>
                <div class="row g-3">
                    <div class="col-md-4 col-lg-2">
                        <label for="totallyDamaged-${entryCount}" class="form-label">Totally Damaged</label>
                        <input type="number" class="form-control number-input" id="totallyDamaged-${entryCount}" name="totallyDamaged[]" placeholder="0" min="0" onchange="calculateTotal(this)">
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label for="partiallyDamaged-${entryCount}" class="form-label">Partially Damaged</label>
                        <input type="number" class="form-control number-input" id="partiallyDamaged-${entryCount}" name="partiallyDamaged[]" placeholder="0" min="0" onchange="calculateTotal(this)">
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label for="totalDamaged-${entryCount}" class="form-label">Total Damaged</label>
                        <input type="number" class="form-control number-input" id="totalDamaged-${entryCount}" name="totalDamaged[]" placeholder="0" readonly>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label for="unit-${entryCount}" class="form-label">Unit</label>
                        <input type="text" class="form-control" id="unit-${entryCount}" name="unit[]" placeholder="e.g., piece, meter" maxlength="50">
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label for="quantity-${entryCount}" class="form-label">Quantity</label>
                        <input type="number" step="0.01" class="form-control number-input" id="quantity-${entryCount}" name="quantity[]" placeholder="0" min="0">
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <label for="cost-${entryCount}" class="form-label">Cost</label>
                        <input type="text" class="form-control" id="cost-${entryCount}" name="cost[]" placeholder="Enter cost" maxlength="100">
                    </div>
                </div>

                <div class="section-title mt-4">Additional Information</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="remarks-${entryCount}" class="form-label">Remarks</label>
                        <input type="text" class="form-control" id="remarks-${entryCount}" name="remarks[]" placeholder="Enter remarks" maxlength="500">
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-remove" onclick="removeEntry(this)">Remove Entry</button>
                    </div>
                </div>
            `;

            entriesContainer.appendChild(newEntry);

            setTimeout(function() {
                newEntry.classList.add('loaded');
                button.disabled = false;
                button.innerHTML = 'Add Entry';
                entryCount++;
            }, 50);
        });

        // Remove entry functionality
        function removeEntry(button) {
            const entry = button.closest('.entry-card');
            const entriesContainer = document.getElementById('entriesContainer');

            if (entriesContainer.children.length > 1) {
                entry.style.opacity = '0';
                entry.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    entry.remove();
                }, 300);
            } else {
                alert("You need at least one entry in the form.");
            }
        }

        // Calculate total damaged infrastructure
        function calculateTotal(input) {
            const entry = input.closest('.entry-card');
            const totallyInput = entry.querySelector('input[name="totallyDamaged[]"]');
            const partiallyInput = entry.querySelector('input[name="partiallyDamaged[]"]');
            const totalInput = entry.querySelector('input[name="totalDamaged[]"]');

            const totally = parseInt(totallyInput.value) || 0;
            const partially = parseInt(partiallyInput.value) || 0;
            const total = totally + partially;

            totalInput.value = total;
        }

        // Handle form submission
        document.getElementById('infrastructureDamageForm').addEventListener('submit', function(e) {
            const submitButton = this.querySelector('.btn-submit');
            const originalText = submitButton.innerHTML;

            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
        });
    </script>
</body>
</html>

<?php
// Get the captured content and store it in a variable
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content
require_once '../includes/sidenav.php';
?>

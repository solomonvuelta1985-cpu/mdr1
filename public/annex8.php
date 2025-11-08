<?php
// Define page title for the header
define('PAGE_TITLE', 'Annex 8 - Status of Roads and Bridges');

// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_login();
log_audit_action($_SESSION['user_id'], 'annex8_form_access', 'User accessed the Annex 8 form');

// Check barangay access
if (is_admin()) {
    $current_lock = get_admin_locked_barangay($_SESSION['user_id']);
    if (!$current_lock) {
        set_flash('Please select barangay from admin dashboard', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
    $user_barangay = $current_lock;
} else {
    $user_data = get_user_location_data($_SESSION['user_id']);
    $user_barangay = $user_data['barangay'];
    
    // Check if barangay is locked by admin
    if (is_barangay_locked($user_barangay)) {
        set_flash('Your barangay is currently locked by administrator. Please try again later.', 'error');
        header('Location: dashboard.php');
        exit;
    }
}

// RATE LIMITING PROTECTION - ADDED
$action = 'annex8_form_access';
$max_attempts = 300;
$time_window = 360000;

if (!check_rate_limit($_SESSION['user_id'], $action, $max_attempts, $time_window)) {
    set_flash('Too many form access attempts. Please try again later.', 'error');
    log_security_event($_SESSION['user_id'], 'rate_limit_exceeded', 'Annex8 form access rate limit exceeded');
    header('Location: dashboard.php');
    exit;
}

// Log form access for audit
log_audit_action($_SESSION['user_id'], 'annex8_form_access', "Accessed Annex 8 form for {$user_barangay}");

$csrf_token = generate_token();

// Get user's existing road/bridge reports
$reports = [];
$stmt = db_query("
    SELECT * FROM annex8_road_bridge_status 
    WHERE created_by = ? AND is_archived = 0 
    ORDER BY created_at DESC
    LIMIT 5
", [$_SESSION['user_id']]);
if ($stmt) {
    $reports = $stmt->fetchAll();
}

// Get remaining submission attempts for user display
$remaining_submissions = get_remaining_attempts($_SESSION['user_id'], 'annex8_submission', 20, 3600);

// Start output buffering for sidenav
ob_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(PAGE_TITLE) ?> - NDRRMC System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
        textarea.form-control {
            height: auto;
            min-height: 100px;
        }
        .form-control::placeholder {
            color: #6c757d;
            opacity: 0.7;
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
        
        /* Image upload styles */
        .image-upload-container {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            position: relative;
        }
        .image-upload-container:hover {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        .image-upload-container.dragover {
            border-color: #198754;
            background-color: #d1f2eb;
        }
        .image-upload-container.has-file {
            border-color: #198754;
            background-color: #d1f2eb;
        }
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 15px;
            border-radius: 6px;
            display: none;
        }
        .upload-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .file-info {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 10px;
        }
        .file-info.success {
            color: #198754;
            font-weight: 600;
        }
        .remove-image-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            line-height: 1;
            z-index: 10;
        }
        .remove-image-btn:hover {
            background-color: #c82333;
        }
        .image-upload-container.has-file .remove-image-btn {
            display: flex;
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
        
        .skeleton-textarea {
            height: 100px;
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
            .image-upload-container {
                padding: 15px;
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
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 25%;"></div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 20%;"></div>
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
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 15%;"></div>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="skeleton-line" style="height: 150px; width: 100%; border-radius: 8px;"></div>
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
                <h2>Annex 8: Status of Roads and Bridges</h2>
            </div>

            <!-- Flash Messages -->
            <?php show_flash(); ?>

            <!-- Barangay Info -->
            <div class="alert alert-info mb-4">
                <strong>Reporting for:</strong> <?= htmlspecialchars($user_barangay) ?> Barangay
                <?php if ($remaining_submissions > 0): ?>
                    <!-- <br><small>Remaining submissions this hour: <strong><?= $remaining_submissions ?></strong></small> -->
                <?php endif; ?>
            </div>

            <!-- ENHANCED FORM WITH CLIENT-SIDE VALIDATION -->
            <form id="roadStatusForm" action="../api/annex8_save.php" method="POST" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div id="entriesContainer">
                    <!-- Initial entry -->
                    <div class="entry-card lazy-entry loaded">
                        <div class="section-title">Location Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="region-0" class="form-label">Region</label>
                                <input type="text" class="form-control" id="region-0" name="region[]" 
                                       placeholder="Enter region" value="REGION II" required
                                       pattern="[A-Za-z\s]{2,100}" 
                                       title="Region must be 2-100 characters long"
                                       maxlength="100" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                <div class="invalid-feedback">Please enter a valid region name (2-100 characters).</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="province-0" class="form-label">Province</label>
                                <input type="text" class="form-control" id="province-0" name="province[]" 
                                       placeholder="Enter province" value="CAGAYAN" required
                                       pattern="[A-Za-z\s]{2,100}"
                                       title="Province must be 2-100 characters long"
                                       maxlength="100" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                <div class="invalid-feedback">Please enter a valid province name (2-100 characters).</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="city-0" class="form-label">City/Municipality</label>
                                <input type="text" class="form-control" id="city-0" name="city[]" 
                                       placeholder="Enter city/mun" value="BAGGAO" required
                                       pattern="[A-Za-z\s\-]{2,100}"
                                       title="City/Municipality must be 2-100 characters long"
                                       maxlength="100" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                <div class="invalid-feedback">Please enter a valid city/municipality name (2-100 characters).</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="barangay-0" class="form-label">Barangay</label>
                                <input type="text" class="form-control" id="barangay-0" name="barangay[]" 
                                       placeholder="Enter barangay" value="<?= htmlspecialchars($user_barangay) ?>" 
                                       readonly required
                                       pattern="[A-Za-z\s\(\)\-]{2,100}"
                                       title="Barangay must be 2-100 characters long"
                                       maxlength="100" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                <div class="invalid-feedback">Please enter a valid barangay name (2-100 characters).</div>
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
                                </select>
                                <div class="invalid-feedback">Please select a type.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="classification-0" class="form-label">Classification</label>
                                <select class="form-select" id="classification-0" name="classification[]" required>
                                    <option value="">Select Class</option>
                                    <option value="National">National</option>
                                    <option value="Provincial">Provincial</option>
                                    <option value="City/Municipal">City/Municipal</option>
                                    <option value="Barangay">Barangay</option>
                                </select>
                                <div class="invalid-feedback">Please select a classification.</div>
                            </div>
                            <div class="col-md-6 col-lg-6">
                                <label for="roadSection-0" class="form-label">Road Section/Bridge Name</label>
                                <input type="text" class="form-control" id="roadSection-0" name="roadSection[]" 
                                       placeholder="Enter name" required
                                       pattern="[A-Za-z0-9\s\-\.\(\)]{3,255}"
                                       title="Road/Bridge name must be 3-255 characters long"
                                       maxlength="255">
                                <div class="invalid-feedback">Please enter a valid road/bridge name (3-255 characters).</div>
                            </div>
                        </div>

                        <div class="section-title mt-4">Status Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="status-0" class="form-label">Status</label>
                                <select class="form-select" id="status-0" name="status[]" required>
                                    <option value="">Select Status</option>
                                    <option value="Passable">Passable</option>
                                    <option value="Not Passable">Not Passable</option>
                                    <option value="Passable to Heavy Vehicles Only">Passable to Heavy Vehicles Only</option>
                                    <option value="Passable to Light Vehicles Only">Passable to Light Vehicles Only</option>
                                </select>
                                <div class="invalid-feedback">Please select a status.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="dateNotPassable-0" class="form-label">Date/Time Not Passable</label>
                                <input type="datetime-local" class="form-control" id="dateNotPassable-0" name="dateNotPassable[]"
                                       min="2020-01-01T00:00" max="2030-12-31T23:59">
                                <div class="invalid-feedback">Please enter a valid date/time.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="datePassable-0" class="form-label">Date/Time Passable</label>
                                <input type="datetime-local" class="form-control" id="datePassable-0" name="datePassable[]"
                                       min="2020-01-01T00:00" max="2030-12-31T23:59">
                                <div class="invalid-feedback">Please enter a valid date/time.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="remarks-0" class="form-label">Remarks</label>
                                <input type="text" class="form-control" id="remarks-0" name="remarks[]" 
                                       placeholder="Enter remarks"
                                       pattern="[A-Za-z0-9\s\-\.\,\(\)]{0,500}"
                                       title="Remarks can be up to 500 characters"
                                       maxlength="500">
                                <div class="invalid-feedback">Remarks can contain up to 500 characters (letters, numbers, spaces, and basic punctuation).</div>
                            </div>
                        </div>

                        <!-- Image Upload Section -->
                        <div class="section-title mt-4">Attached Image (Optional)</div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="image-upload-container" id="imageUploadContainer-0">
                                    <button type="button" class="remove-image-btn" onclick="removeImage(0, event)" title="Remove image">×</button>
                                    <div class="upload-icon">📷</div>
                                    <h5>Upload Road/Bridge Image</h5>
                                    <p class="text-muted">Drag & drop an image or click to browse</p>
                                    <input type="file" class="d-none" id="imageUpload-0" name="image[]" 
                                           accept="image/jpeg,image/jpg,image/png,image/gif"
                                           data-max-size="5242880">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('imageUpload-0').click()">
                                        Choose Image
                                    </button>
                                    <div class="file-info" id="fileInfo-0">
                                        Supported formats: JPG, PNG, GIF (Max: 5MB)
                                    </div>
                                    <img class="image-preview" id="imagePreview-0" alt="Image preview">
                                    <div class="invalid-feedback" id="imageError-0"></div>
                                </div>
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
                    <button type="submit" class="btn btn-success btn-submit" id="submitBtn">
                        <i class="fas fa-paper-plane me-2"></i>Save Report
                    </button>
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
                            Specify the exact geographic location of the road or bridge being reported.</p>
                            <p><strong>Type</strong><br>
                            Select whether the affected infrastructure is a: <strong>Road</strong> or <strong>Bridge</strong></p>
                            <p><strong>Classification</strong><br>
                            Indicate the administrative classification of the road or bridge: <strong>National</strong>, <strong>Provincial</strong>, <strong>City/Municipal</strong>, or <strong>Barangay</strong></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Road Section / Bridge Name</strong><br>
                            Provide the name or designation of the specific road section or bridge, if available.</p>
                            <p><strong>Status</strong><br>
                            Select the current status: <strong>Passable</strong>, <strong>Not Passable</strong>, <strong>Passable to Heavy Vehicles Only</strong>, or <strong>Passable to Light Vehicles Only</strong></p>
                            <p><strong>Date / Time Not Passable</strong><br>
                            Record the exact date and time the road or bridge became impassable.</p>
                            <p><strong>Date / Time Passable</strong><br>
                            Indicate the date and time when the road or bridge was restored to passable condition (if applicable).</p>
                            <p><strong>Remarks</strong><br>
                            Include any other relevant information, such as the cause of obstruction (e.g., landslide, flood), ongoing clearing operations, or temporary detour routes.</p>
                            <p><strong>Attached Image</strong><br>
                            Upload a clear photo showing the current condition of the road or bridge. This helps in visual assessment and documentation.</p>
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
    
    <!-- Custom JavaScript -->
    <script>
        let entryCount = 1;
        const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB in bytes
        
        // Store file data for each entry to persist across validations
        const fileDataStore = {};

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
            
            // Initialize image upload for first entry
            initializeImageUpload(0);
        });

        // File Size Validation Functions
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function validateFileSize(file, index) {
            const fileInput = document.getElementById(`imageUpload-${index}`);
            const imageError = document.getElementById(`imageError-${index}`);
            
            // Reset previous errors
            imageError.textContent = '';
            imageError.style.display = 'none';
            fileInput.classList.remove('is-invalid');
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                imageError.textContent = 'Please select a valid image file (JPG, PNG, GIF)';
                imageError.style.display = 'block';
                fileInput.classList.add('is-invalid');
                return false;
            }

            // Validate file size (5MB)
            if (file.size > MAX_FILE_SIZE) {
                const formattedSize = formatFileSize(file.size);
                const maxFormatted = formatFileSize(MAX_FILE_SIZE);
                imageError.textContent = `File size (${formattedSize}) exceeds maximum allowed size of ${maxFormatted}. Please choose a smaller file.`;
                imageError.style.display = 'block';
                fileInput.classList.add('is-invalid');
                return false;
            }

            fileInput.classList.add('is-valid');
            return true;
        }

        // Remove image function
        function removeImage(index, event) {
            // Stop event propagation to prevent triggering container click
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            const fileInput = document.getElementById(`imageUpload-${index}`);
            const preview = document.getElementById(`imagePreview-${index}`);
            const fileInfo = document.getElementById(`fileInfo-${index}`);
            const container = document.getElementById(`imageUploadContainer-${index}`);
            const imageError = document.getElementById(`imageError-${index}`);
            
            // Clear file input
            fileInput.value = '';
            
            // Clear stored file data
            delete fileDataStore[index];
            
            // Reset preview
            preview.style.display = 'none';
            preview.src = '';
            
            // Reset file info
            fileInfo.textContent = 'Supported formats: JPG, PNG, GIF (Max: 5MB)';
            fileInfo.classList.remove('success');
            
            // Reset container styling
            container.classList.remove('has-file');
            
            // Clear validation states
            fileInput.classList.remove('is-valid', 'is-invalid');
            imageError.textContent = '';
            imageError.style.display = 'none';
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
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 25%;"></div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 20%;"></div>
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
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 15%;"></div>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="skeleton-line" style="height: 150px; width: 100%; border-radius: 8px;"></div>
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
                            <input type="text" class="form-control" id="region-${entryCount}" name="region[]" 
                                   placeholder="Enter region" value="REGION II" required
                                   pattern="[A-Za-z\s]{2,100}" 
                                   title="Region must be 2-100 characters long"
                                   maxlength="100">
                            <div class="invalid-feedback">Please enter a valid region name (2-100 characters).</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="province-${entryCount}" class="form-label">Province</label>
                            <input type="text" class="form-control" id="province-${entryCount}" name="province[]" 
                                   placeholder="Enter province" value="CAGAYAN" required
                                   pattern="[A-Za-z\s]{2,100}"
                                   title="Province must be 2-100 characters long"
                                   maxlength="100">
                            <div class="invalid-feedback">Please enter a valid province name (2-100 characters).</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="city-${entryCount}" class="form-label">City/Municipality</label>
                            <input type="text" class="form-control" id="city-${entryCount}" name="city[]" 
                                   placeholder="Enter city/mun" value="BAGGAO" required
                                   pattern="[A-Za-z\s\-]{2,100}"
                                   title="City/Municipality must be 2-100 characters long"
                                   maxlength="100">
                            <div class="invalid-feedback">Please enter a valid city/municipality name (2-100 characters).</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="barangay-${entryCount}" class="form-label">Barangay</label>
                            <input type="text" class="form-control" id="barangay-${entryCount}" name="barangay[]" 
                                   placeholder="Enter barangay" value="<?= htmlspecialchars($user_barangay) ?>" 
                                   readonly required
                                   pattern="[A-Za-z\s\(\)\-]{2,100}"
                                   title="Barangay must be 2-100 characters long"
                                   maxlength="100">
                            <div class="invalid-feedback">Please enter a valid barangay name (2-100 characters).</div>
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
                            </select>
                            <div class="invalid-feedback">Please select a type.</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="classification-${entryCount}" class="form-label">Classification</label>
                            <select class="form-select" id="classification-${entryCount}" name="classification[]" required>
                                <option value="">Select Class</option>
                                <option value="National">National</option>
                                <option value="Provincial">Provincial</option>
                                <option value="City/Municipal">City/Municipal</option>
                                <option value="Barangay">Barangay</option>
                            </select>
                            <div class="invalid-feedback">Please select a classification.</div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <label for="roadSection-${entryCount}" class="form-label">Road Section/Bridge Name</label>
                            <input type="text" class="form-control" id="roadSection-${entryCount}" name="roadSection[]" 
                                   placeholder="Enter name" required
                                   pattern="[A-Za-z0-9\s\-\.\(\)]{3,255}"
                                   title="Road/Bridge name must be 3-255 characters long"
                                   maxlength="255">
                            <div class="invalid-feedback">Please enter a valid road/bridge name (3-255 characters).</div>
                        </div>
                    </div>

                    <div class="section-title mt-4">Status Details</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label for="status-${entryCount}" class="form-label">Status</label>
                            <select class="form-select" id="status-${entryCount}" name="status[]" required>
                                <option value="">Select Status</option>
                                <option value="Passable">Passable</option>
                                <option value="Not Passable">Not Passable</option>
                                <option value="Passable to Heavy Vehicles Only">Passable to Heavy Vehicles Only</option>
                                <option value="Passable to Light Vehicles Only">Passable to Light Vehicles Only</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="dateNotPassable-${entryCount}" class="form-label">Date/Time Not Passable</label>
                            <input type="datetime-local" class="form-control" id="dateNotPassable-${entryCount}" name="dateNotPassable[]"
                                   min="2020-01-01T00:00" max="2030-12-31T23:59">
                            <div class="invalid-feedback">Please enter a valid date/time.</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="datePassable-${entryCount}" class="form-label">Date/Time Passable</label>
                            <input type="datetime-local" class="form-control" id="datePassable-${entryCount}" name="datePassable[]"
                                   min="2020-01-01T00:00" max="2030-12-31T23:59">
                            <div class="invalid-feedback">Please enter a valid date/time.</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="remarks-${entryCount}" class="form-label">Remarks</label>
                            <input type="text" class="form-control" id="remarks-${entryCount}" name="remarks[]" 
                                   placeholder="Enter remarks"
                                   pattern="[A-Za-z0-9\s\-\.\,\(\)]{0,500}"
                                   title="Remarks can be up to 500 characters"
                                   maxlength="500">
                            <div class="invalid-feedback">Remarks can contain up to 500 characters (letters, numbers, spaces, and basic punctuation).</div>
                        </div>
                    </div>

                    <!-- Image Upload Section -->
                    <div class="section-title mt-4">Attached Image (Optional)</div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="image-upload-container" id="imageUploadContainer-${entryCount}">
                                <button type="button" class="remove-image-btn" onclick="removeImage(${entryCount}, event)" title="Remove image">×</button>
                                <div class="upload-icon">📷</div>
                                <h5>Upload Road/Bridge Image</h5>
                                <p class="text-muted">Drag & drop an image or click to browse</p>
                                <input type="file" class="d-none" id="imageUpload-${entryCount}" name="image[]" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif"
                                       data-max-size="5242880">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('imageUpload-${entryCount}').click()">
                                    Choose Image
                                </button>
                                <div class="file-info" id="fileInfo-${entryCount}">
                                    Supported formats: JPG, PNG, GIF (Max: 5MB)
                                </div>
                                <img class="image-preview" id="imagePreview-${entryCount}" alt="Image preview">
                                <div class="invalid-feedback" id="imageError-${entryCount}"></div>
                            </div>
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
                
                // Initialize image upload for new entry
                initializeImageUpload(entryCount);
                
                // Re-enable button
                button.disabled = false;
                button.innerHTML = 'Add Entry';
                
                entryCount++;
            }, 800); // 800ms loading delay for new entries
        });

        // Initialize image upload functionality
        function initializeImageUpload(index) {
            const container = document.getElementById(`imageUploadContainer-${index}`);
            const fileInput = document.getElementById(`imageUpload-${index}`);
            const preview = document.getElementById(`imagePreview-${index}`);
            const fileInfo = document.getElementById(`fileInfo-${index}`);
            const imageError = document.getElementById(`imageError-${index}`);

            // Restore file if it exists in store
            if (fileDataStore[index]) {
                restoreFileFromStore(index);
            }

            // Click to upload - only trigger if clicking on the container itself or allowed elements
            container.addEventListener('click', function(e) {
                // Don't trigger if clicking on the remove button, file input, or other buttons
                if (e.target.classList.contains('remove-image-btn') || 
                    e.target === fileInput || 
                    e.target.classList.contains('btn') ||
                    e.target.closest('.btn')) {
                    return;
                }
                fileInput.click();
            });

            // Drag and drop
            container.addEventListener('dragover', function(e) {
                e.preventDefault();
                container.classList.add('dragover');
            });

            container.addEventListener('dragleave', function() {
                container.classList.remove('dragover');
            });

            container.addEventListener('drop', function(e) {
                e.preventDefault();
                container.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    const dt = new DataTransfer();
                    dt.items.add(e.dataTransfer.files[0]);
                    fileInput.files = dt.files;
                    handleFileSelect(e.dataTransfer.files[0], preview, fileInfo, imageError, fileInput, index);
                }
            });

            // File input change
            fileInput.addEventListener('change', function(e) {
                if (this.files.length) {
                    handleFileSelect(this.files[0], preview, fileInfo, imageError, fileInput, index);
                }
            });
        }

        // Handle file selection and store file data
        function handleFileSelect(file, preview, fileInfo, imageError, fileInput, index) {
            // Reset previous errors
            imageError.textContent = '';
            imageError.style.display = 'none';
            fileInput.classList.remove('is-invalid');
            
            // Validate file
            if (!validateFileSize(file, index)) {
                return;
            }

            // Store file data for this entry
            const reader = new FileReader();
            reader.onload = function(e) {
                // Store the file data
                fileDataStore[index] = {
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    dataUrl: e.target.result
                };
                
                // Update UI
                updateFileUI(index, file, e.target.result);
            };
            reader.readAsDataURL(file);
        }

        // Update file UI
        function updateFileUI(index, file, dataUrl) {
            const preview = document.getElementById(`imagePreview-${index}`);
            const fileInfo = document.getElementById(`fileInfo-${index}`);
            const fileInput = document.getElementById(`imageUpload-${index}`);
            const container = document.getElementById(`imageUploadContainer-${index}`);
            
            // Update file info with success styling
            fileInfo.innerHTML = `✓ ${file.name} (${formatFileSize(file.size)}) - <strong>Ready to upload</strong>`;
            fileInfo.classList.add('success');
            
            // Show preview
            preview.src = dataUrl;
            preview.style.display = 'block';
            
            // Mark as valid
            fileInput.classList.add('is-valid');
            container.classList.add('has-file');
        }

        // Restore file from store
        function restoreFileFromStore(index) {
            const storedFile = fileDataStore[index];
            if (!storedFile) return;
            
            const preview = document.getElementById(`imagePreview-${index}`);
            const fileInfo = document.getElementById(`fileInfo-${index}`);
            const fileInput = document.getElementById(`imageUpload-${index}`);
            const container = document.getElementById(`imageUploadContainer-${index}`);
            
            // Update UI
            fileInfo.innerHTML = `✓ ${storedFile.name} (${formatFileSize(storedFile.size)}) - <strong>Ready to upload</strong>`;
            fileInfo.classList.add('success');
            preview.src = storedFile.dataUrl;
            preview.style.display = 'block';
            fileInput.classList.add('is-valid');
            container.classList.add('has-file');
        }

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

        // Enhanced form validation
        function validateForm() {
            const entries = document.querySelectorAll('.entry-card');
            let isValid = true;
            let hasFileErrors = false;
            let firstInvalidEntry = null;
            
            entries.forEach((entry, index) => {
                const inputs = entry.querySelectorAll('input[required], select[required]');
                let entryValid = true;
                
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        input.classList.remove('is-valid');
                        entryValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    }
                });

                // Validate patterns
                const patternInputs = entry.querySelectorAll('input[pattern]');
                patternInputs.forEach(input => {
                    if (input.value && !new RegExp(input.pattern).test(input.value)) {
                        input.classList.add('is-invalid');
                        input.classList.remove('is-valid');
                        entryValid = false;
                    }
                });

                // Validate file sizes (only if file is selected)
                const fileInput = entry.querySelector('input[type="file"]');
                if (fileInput && fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    if (file.size > MAX_FILE_SIZE) {
                        const imageError = entry.querySelector('[id^="imageError-"]');
                        imageError.textContent = `File size (${formatFileSize(file.size)}) exceeds maximum allowed size of ${formatFileSize(MAX_FILE_SIZE)}.`;
                        imageError.style.display = 'block';
                        fileInput.classList.add('is-invalid');
                        entryValid = false;
                        hasFileErrors = true;
                    }
                }

                if (!entryValid) {
                    entry.style.border = '2px solid #dc3545';
                    if (!firstInvalidEntry) {
                        firstInvalidEntry = entry;
                    }
                    isValid = false;
                } else {
                    entry.style.border = '1px solid #dee2e6';
                }
            });
            
            if (hasFileErrors) {
                alert('Some files exceed the maximum size limit of 5MB. Please remove oversized files and try again.');
            } else if (!isValid && firstInvalidEntry) {
                alert('Please fill in all required fields correctly before submitting.');
                firstInvalidEntry.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            return isValid;
        }

        // Form submission handling
        document.getElementById('roadStatusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm()) {
                return;
            }

            // Check remaining submissions
            const remaining = <?= $remaining_submissions ?>;
            if (remaining <= 0) {
                alert('You have reached your submission limit for this hour. Please try again later.');
                return;
            }

            // Mark form as being submitted to prevent beforeunload warning
            this.classList.add('submitting');

            // Restore files from store to file inputs before submission
            Object.keys(fileDataStore).forEach(index => {
                const fileInput = document.getElementById(`imageUpload-${index}`);
                if (fileInput && fileDataStore[index] && fileInput.files.length === 0) {
                    // Create a new File object from stored data
                    fetch(fileDataStore[index].dataUrl)
                        .then(res => res.blob())
                        .then(blob => {
                            const file = new File([blob], fileDataStore[index].name, { type: fileDataStore[index].type });
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            fileInput.files = dt.files;
                        });
                }
            });

            // Disable submit button to prevent double submission
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';

            // Small delay to ensure files are restored
            setTimeout(() => {
                this.submit();
            }, 500);
        });

        // Real-time validation
        document.addEventListener('input', function(e) {
            if (e.target.matches('input[pattern], input[required], select[required]')) {
                if (e.target.value.trim()) {
                    if (e.target.pattern && !new RegExp(e.target.pattern).test(e.target.value)) {
                        e.target.classList.add('is-invalid');
                        e.target.classList.remove('is-valid');
                    } else {
                        e.target.classList.remove('is-invalid');
                        e.target.classList.add('is-valid');
                    }
                } else if (e.target.hasAttribute('required')) {
                    e.target.classList.add('is-invalid');
                    e.target.classList.remove('is-valid');
                } else {
                    e.target.classList.remove('is-invalid', 'is-valid');
                }
            }
        });

        // Prevent form data loss on page unload (warn user)
  // Improved beforeunload handler
window.addEventListener('beforeunload', function(e) {
    const form = document.getElementById('roadStatusForm');
    
    // Only show warning if form has been modified
    if (form.classList.contains('form-modified') && !form.classList.contains('submitting')) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
    }
});

// Add event listeners to mark form as modified
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('roadStatusForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            form.classList.add('form-modified');
        });
        
        input.addEventListener('change', function() {
            form.classList.add('form-modified');
        });
    });
    
    // Remove modified flag when form is submitted
    form.addEventListener('submit', function() {
        form.classList.remove('form-modified');
    });
});
    </script>

<?php
// Get the captured content and store it in a variable
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content
require_once '../includes/sidenav.php';
?>
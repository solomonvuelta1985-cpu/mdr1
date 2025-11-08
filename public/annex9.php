
<?php
/**
 * Annex 9: Status of Power Supply Report Form
 * WITH IMAGE ATTACHMENT SUPPORT - ENHANCED VERSION WITH SIDENAV AND SKELETON LOADER
 */

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// Get user data
$user_id = $_SESSION['user_id'];
$user_data = get_user_location_data($user_id);
$is_admin = is_admin();

// Centralized barangay locking check for admins
if ($is_admin) {
    $user_barangay = get_admin_locked_barangay($user_id);
    if (!$user_barangay) {
        set_flash('Please select a barangay from the admin dashboard first.', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
} else {
    $user_barangay = $user_data['barangay'];
}

// Auto-fill location data
$region = $user_data['region'] ?? 'Region II';
$province = $user_data['province'] ?? 'Cagayan';
$city = $user_data['city'] ?? 'Baggao';

// Generate CSRF token
$csrf_token = generate_token();

// Log form access
log_audit_action(
    $user_id,
    'annex9_form_access',
    "Accessed Annex 9 form for barangay: {$user_barangay}"
);

// Start output buffering for sidenav
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annex 9 - Status of Power Supply - NDRRMC System</title>
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
        
        /* Form Content Styles */
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
        /* Image Upload Styling */
        .image-upload-wrapper {
            position: relative;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }
        .image-upload-wrapper:hover {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        .image-upload-wrapper.has-image {
            border-color: #198754;
            background-color: #d1e7dd;
        }
        .image-upload-label {
            cursor: pointer;
            display: block;
        }
        .image-upload-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 6px;
            display: none;
        }
        .image-filename {
            font-size: 0.9rem;
            color: #198754;
            margin-top: 10px;
            font-weight: 500;
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
        /* Submit Loading Skeleton */
        .skeleton-loader-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .skeleton-loader-overlay.active {
            display: flex;
        }
        .upload-progress-container {
            width: 300px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .upload-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 20px;
            animation: uploadPulse 1.5s ease-in-out infinite;
        }
        @keyframes uploadPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        .spinner-border-custom {
            width: 3rem;
            height: 3rem;
            border-width: 0.3rem;
            margin-bottom: 20px;
        }
        .skeleton-text {
            font-size: 1.1rem;
            color: #0d6efd;
            font-weight: 500;
            margin-bottom: 15px;
        }
        .progress {
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
            background-color: #e9ecef;
            margin-top: 15px;
        }
        .progress-bar {
            background: linear-gradient(90deg, #0d6efd, #6610f2);
            animation: progressAnimation 2s ease-in-out infinite;
        }
        @keyframes progressAnimation {
            0% {
                width: 0%;
            }
            50% {
                width: 70%;
            }
            100% {
                width: 100%;
            }
        }
        /* Validation Styles */
        .is-invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.4.4.4-.4'/%3e%3cpath d='M6 7v1'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
        .was-validated .form-control:invalid ~ .invalid-feedback,
        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
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
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 35%;"></div>
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
            <?php show_flash(); ?>
            
            <div class="header-section">
                <h1>NDRRMC Memorandum Circular No. 05, s. 2025</h1>
                <h2>Annex 9: Status of Power Supply</h2>
            </div>

            <form id="powerSupplyForm" method="POST" action="../api/annex9_save.php" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="user_barangay" value="<?php echo htmlspecialchars($user_barangay); ?>">
                
                <div id="entriesContainer">
                    <!-- Initial entry -->
                    <div class="entry-card lazy-entry loaded">
                        <div class="section-title">Location Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="region-0" class="form-label">Region</label>
                                <input type="text" class="form-control" id="region-0" name="region[]" value="<?php echo htmlspecialchars($region); ?>" required maxlength="100"readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                <div class="invalid-feedback">Please provide a valid region.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="province-0" class="form-label">Province</label>
                                <input type="text" class="form-control" id="province-0" name="province[]" value="<?php echo htmlspecialchars($province); ?>" required maxlength="100"readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                <div class="invalid-feedback">Please provide a valid province.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="city-0" class="form-label">City/Municipality</label>
                                <input type="text" class="form-control" id="city-0" name="city[]" value="<?php echo htmlspecialchars($city); ?>" required maxlength="100"readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                <div class="invalid-feedback">Please provide a valid city/municipality.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="barangay-0" class="form-label">Barangay</label>
                                <input type="text" class="form-control bg-light" id="barangay-0" name="barangay[]" value="<?php echo htmlspecialchars($user_barangay); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                <div class="invalid-feedback">Please provide a valid barangay.</div>
                            </div>
                        </div>

                        <div class="section-title mt-4">Power Supply Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="serviceProvider-0" class="form-label">Service Provider</label>
                                <input type="text" class="form-control" id="serviceProvider-0" name="serviceProvider[]" placeholder="e.g., MERALCO, local electric cooperative" required maxlength="200">
                                <div class="invalid-feedback">Please provide a service provider.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="interruptionDate-0" class="form-label">Date/Time of Interruption</label>
                                <input type="datetime-local" class="form-control" id="interruptionDate-0" name="interruptionDate[]" required>
                                <div class="invalid-feedback">Please provide interruption date and time.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="restoredDate-0" class="form-label">Date/Time Restored</label>
                                <input type="datetime-local" class="form-control" id="restoredDate-0" name="restoredDate[]">
                                <div class="invalid-feedback">Please provide a valid restoration date and time.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="remarks-0" class="form-label">Remarks</label>
                                <input type="text" class="form-control" id="remarks-0" name="remarks[]" placeholder="Enter remarks" maxlength="500">
                                <div class="invalid-feedback">Remarks cannot exceed 500 characters.</div>
                            </div>
                        </div>

                        <!-- IMAGE UPLOAD SECTION -->
                        <div class="section-title mt-4">📷 Attach Image (Optional)</div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="image-upload-wrapper" id="imageWrapper-0">
                                    <label for="image-0" class="image-upload-label">
                                        <div class="image-upload-icon">📸</div>
                                        <p class="mb-2"><strong>Click to upload image</strong></p>
                                        <p class="text-muted small mb-0">JPG, JPEG, PNG, GIF (Max 5MB)</p>
                                        <p class="text-muted small">Photos of damaged poles, transformers, affected areas, etc.</p>
                                    </label>
                                    <input type="file" class="d-none" id="image-0" name="image[]" accept="image/jpeg,image/jpg,image/png,image/gif" onchange="previewImage(this, 0)">
                                    <img id="imagePreview-0" class="image-preview" alt="Image preview">
                                    <div id="imageFilename-0" class="image-filename" style="display: none;"></div>
                                    <div class="invalid-feedback" id="imageError-0" style="display: none;"></div>
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
                    <button type="submit" class="btn btn-success btn-submit" id="submitBtn">Save Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Submit Skeleton Loader Overlay -->
    <div class="skeleton-loader-overlay" id="skeletonLoader">
        <div class="upload-progress-container">
            <div class="upload-icon">
                <i class="bi bi-cloud-upload"></i>
            </div>
            <div class="spinner-border text-primary spinner-border-custom" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="skeleton-text">Uploading images and saving report...</div>
            <div class="progress">
                <div class="progress-bar" role="progressbar"></div>
            </div>
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
                            Specify the exact geographic location affected by the power interruption.</p>
                            
                            <p><strong>Service Provider</strong><br>
                            Indicate the name of the electric company or power service provider responsible for the area (e.g., MERALCO, local electric cooperative).</p>
                            
                            <p><strong>Attach Image</strong><br>
                            Upload photos showing the damage or impact, such as toppled poles, damaged transformers, affected areas, or restoration work. This helps document the severity and supports reports.</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date / Time Interruption</strong><br>
                            State the exact date and time when the power outage began.</p>
                            
                            <p><strong>Date / Time Restored</strong><br>
                            Indicate the date and time when power supply was restored, if applicable.</p>
                            
                            <p><strong>Remarks</strong><br>
                            Include relevant details such as the cause of the power interruption (e.g., toppled poles, damaged transformers, landslide), areas still affected, or actions taken to restore the service.</p>
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
        let totalEntries = 1;
        const userRegion = <?php echo json_encode($region); ?>;
        const userProvince = <?php echo json_encode($province); ?>;
        const userCity = <?php echo json_encode($city); ?>;
        const userBarangay = <?php echo json_encode($user_barangay); ?>;

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
                
                // Set current datetime for interruption date by default
                const now = new Date();
                const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
                document.getElementById('interruptionDate-0').value = localDateTime;
            }, 1200); // 1.2 second loading simulation
        });

        // Image preview function with enhanced validation
        function previewImage(input, index) {
            const wrapper = document.getElementById(`imageWrapper-${index}`);
            const preview = document.getElementById(`imagePreview-${index}`);
            const filename = document.getElementById(`imageFilename-${index}`);
            const errorDiv = document.getElementById(`imageError-${index}`);
            
            // Reset previous errors
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';
            wrapper.classList.remove('has-image', 'is-invalid');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    errorDiv.textContent = '⚠️ File size must be less than 5MB';
                    errorDiv.style.display = 'block';
                    wrapper.classList.add('is-invalid');
                    input.value = '';
                    return;
                }
                
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    errorDiv.textContent = '⚠️ Please upload only JPG, JPEG, PNG, or GIF images';
                    errorDiv.style.display = 'block';
                    wrapper.classList.add('is-invalid');
                    input.value = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    filename.textContent = '✅ ' + file.name;
                    filename.style.display = 'block';
                    wrapper.classList.add('has-image');
                };
                reader.onerror = function() {
                    errorDiv.textContent = '⚠️ Error reading file. Please try another image.';
                    errorDiv.style.display = 'block';
                    wrapper.classList.add('is-invalid');
                };
                reader.readAsDataURL(file);
            } else {
                // Reset if no file selected
                preview.style.display = 'none';
                filename.style.display = 'none';
                wrapper.classList.remove('has-image');
            }
        }

        // Add entry functionality with skeleton loader
        document.getElementById('addEntryBtn').addEventListener('click', function() {
            // Limit maximum entries to prevent abuse
            if (totalEntries >= 10) {
                alert('Maximum of 10 entries allowed per submission.');
                return;
            }

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
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 35%;"></div>
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
                            <input type="text" class="form-control" id="region-${entryCount}" name="region[]" value="${userRegion}" required maxlength="100" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                            <div class="invalid-feedback">Please provide a valid region.</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="province-${entryCount}" class="form-label">Province</label>
                            <input type="text" class="form-control" id="province-${entryCount}" name="province[]" value="${userProvince}" required maxlength="100" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                            <div class="invalid-feedback">Please provide a valid province.</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="city-${entryCount}" class="form-label">City/Municipality</label>
                            <input type="text" class="form-control" id="city-${entryCount}" name="city[]" value="${userCity}" required maxlength="100" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                            <div class="invalid-feedback">Please provide a valid city/municipality.</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="barangay-${entryCount}" class="form-label">Barangay</label>
                            <input type="text" class="form-control bg-light" id="barangay-${entryCount}" name="barangay[]" value="${userBarangay}" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                            <div class="invalid-feedback">Please provide a valid barangay.</div>
                        </div>
                    </div>

                    <div class="section-title mt-4">Power Supply Details</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label for="serviceProvider-${entryCount}" class="form-label">Service Provider</label>
                            <input type="text" class="form-control" id="serviceProvider-${entryCount}" name="serviceProvider[]" placeholder="e.g., MERALCO, local electric cooperative" required maxlength="200">
                            <div class="invalid-feedback">Please provide a service provider.</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="interruptionDate-${entryCount}" class="form-label">Date/Time of Interruption</label>
                            <input type="datetime-local" class="form-control" id="interruptionDate-${entryCount}" name="interruptionDate[]" required>
                            <div class="invalid-feedback">Please provide interruption date and time.</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="restoredDate-${entryCount}" class="form-label">Date/Time Restored</label>
                            <input type="datetime-local" class="form-control" id="restoredDate-${entryCount}" name="restoredDate[]">
                            <div class="invalid-feedback">Please provide a valid restoration date and time.</div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="remarks-${entryCount}" class="form-label">Remarks</label>
                            <input type="text" class="form-control" id="remarks-${entryCount}" name="remarks[]" placeholder="Enter remarks" maxlength="500">
                            <div class="invalid-feedback">Remarks cannot exceed 500 characters.</div>
                        </div>
                    </div>

                    <!-- IMAGE UPLOAD SECTION -->
                    <div class="section-title mt-4">📷 Attach Image (Optional)</div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="image-upload-wrapper" id="imageWrapper-${entryCount}">
                                <label for="image-${entryCount}" class="image-upload-label">
                                    <div class="image-upload-icon">📸</div>
                                    <p class="mb-2"><strong>Click to upload image</strong></p>
                                    <p class="text-muted small mb-0">JPG, JPEG, PNG, GIF (Max 5MB)</p>
                                    <p class="text-muted small">Photos of damaged poles, transformers, affected areas, etc.</p>
                                </label>
                                <input type="file" class="d-none" id="image-${entryCount}" name="image[]" accept="image/jpeg,image/jpg,image/png,image/gif" onchange="previewImage(this, ${entryCount})">
                                <img id="imagePreview-${entryCount}" class="image-preview" alt="Image preview">
                                <div id="imageFilename-${entryCount}" class="image-filename" style="display: none;"></div>
                                <div class="invalid-feedback" id="imageError-${entryCount}" style="display: none;"></div>
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
                
                // Set current datetime for interruption date by default
                const now = new Date();
                const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
                document.getElementById(`interruptionDate-${entryCount}`).value = localDateTime;
                
                // Animate entry appearance
                setTimeout(function() {
                    newEntry.classList.add('loaded');
                }, 50);
                
                // Re-enable button
                button.disabled = false;
                button.innerHTML = 'Add Entry';
                
                // Increment counters
                entryCount++;
                totalEntries++;
            }, 800); // 0.8 second loading simulation
        });

        // Remove entry functionality
        function removeEntry(button) {
            const entryCard = button.closest('.entry-card');
            
            // Only allow removal if there's more than one entry
            if (totalEntries > 1) {
                // Add fade-out animation
                entryCard.style.opacity = '0';
                entryCard.style.transform = 'translateY(-20px)';
                
                setTimeout(function() {
                    entryCard.remove();
                    totalEntries--;
                }, 300);
            } else {
                alert('At least one entry is required.');
            }
        }

        // Form submission with skeleton loader
        document.getElementById('powerSupplyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic form validation
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }
            
            // Show skeleton loader overlay
            const skeletonLoader = document.getElementById('skeletonLoader');
            skeletonLoader.classList.add('active');
            
            // Disable submit button
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            
            // Submit form after a short delay to show the loading animation
            setTimeout(() => {
                this.submit();
            }, 1500);
        });

        // Lazy loading for entries when they come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('loaded');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        // Observe all lazy entries
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.lazy-entry').forEach(el => {
                observer.observe(el);
            });
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
[file content end]
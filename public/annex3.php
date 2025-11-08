<?php


// Define page title for the header
define('PAGE_TITLE', 'Annex 3 - Casualties');

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// Rate limiting check for form access (prevent form spam)
$rate_limit_key = 'annex3_form_access_' . $_SESSION['user_id'];
if (!check_rate_limit($rate_limit_key,20, 3600)) {
    log_security_event(
        $_SESSION['user_id'],
        'rate_limit_exceeded',
        'Annex 3 form access rate limit exceeded (20 per hour)'
    );
    set_flash('Too many form access attempts. Please wait before accessing the form again.', 'error');
    header('Location: annex3_records.php');
    exit;
}

// AUDIT LOG: Form access
log_audit_action(
    $_SESSION['user_id'],
    'annex3_form_access',
    'User accessed the Annex 3 Casualties form'
);

// SECURITY LOG: Form access
log_security_event(
    $_SESSION['user_id'],
    'form_access',
    'Accessed Annex 3 form'
);

// Get user location data
$user_data = get_user_location_data($_SESSION['user_id']);
$user_barangay = $user_data['barangay'] ?? '';
$user_role = $user_data['user_role'] ?? 'user';

// Barangay locking check for admins
if (is_admin()) {
    $current_lock = get_admin_locked_barangay($_SESSION['user_id']);
    if (!$current_lock) {
        set_flash('Please select a barangay from the admin dashboard before filling out forms.', 'error');
        header('Location: ../admin/barangay_locking.php');
        exit;
    }
    $user_barangay = $current_lock;
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* PRESERVING ORIGINAL DESIGN EXACTLY */
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
            resize: vertical;
            min-height: 100px;
            height: auto;
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
        
        /* Skeleton Loader Styles - LIKE ANNEX 5 */
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
        <!-- Skeleton Loader for Initial Page Load - LIKE ANNEX 5 -->
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
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 35%;"></div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-textarea"></div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-textarea"></div>
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
                <h2>Annex 3: Casualties</h2>
                
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

            <form id="casualtiesForm" method="POST" action="../api/annex3_save.php">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <!-- Hidden barangay field (auto-filled) -->
                <input type="hidden" name="user_barangay" value="<?php echo htmlspecialchars($user_barangay); ?>">
                
                <div id="entriesContainer">
                    <!-- Initial entry - PRESERVING ORIGINAL HTML STRUCTURE -->
                    <div class="entry-card lazy-entry loaded">
                        <div class="section-title">Location Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="region-0" class="form-label">Region</label>
                                <?php if ($user_role === 'admin'): ?>
                                    <input type="text" class="form-control" id="region-0" name="region[]" value="Region II" required maxlength="100">
                                <?php else: ?>
                                    <input type="text" class="form-control bg-light" id="region-0" name="region[]" value="Region II" readonly>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="province-0" class="form-label">Province</label>
                                <?php if ($user_role === 'admin'): ?>
                                    <input type="text" class="form-control" id="province-0" name="province[]" value="Cagayan" required maxlength="100">
                                <?php else: ?>
                                    <input type="text" class="form-control bg-light" id="province-0" name="province[]" value="Cagayan" readonly>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="city-0" class="form-label">City/Municipality</label>
                                <?php if ($user_role === 'admin'): ?>
                                    <input type="text" class="form-control" id="city-0" name="city[]" value="Baggao" required maxlength="100">
                                <?php else: ?>
                                    <input type="text" class="form-control bg-light" id="city-0" name="city[]" value="Baggao" readonly>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="barangay-0" class="form-label">Barangay</label>
                                <input type="text" class="form-control bg-light" id="barangay-0" name="barangay[]" 
                                    value="<?php echo htmlspecialchars($user_barangay); ?>" readonly required>
                            </div>
                        </div>

                        <div class="section-title mt-4">Personal Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-2">
                                <label for="category-0" class="form-label">Category</label>
                                <select class="form-select" id="category-0" name="category[]" required>
                                    <option value="">Select</option>
                                    <option value="Dead">Dead</option>
                                    <option value="Injured">Injured</option>
                                    <option value="Ill">Ill</option>
                                    <option value="Missing">Missing</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label for="surname-0" class="form-label">Surname</label>
                                <input type="text" class="form-control" id="surname-0" name="surname[]" placeholder="Surname" maxlength="100" required>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label for="firstName-0" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName-0" name="firstName[]" placeholder="First Name" maxlength="100" required>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label for="middleName-0" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middleName-0" name="middleName[]" placeholder="Middle Name" maxlength="100">
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label for="age-0" class="form-label">Age</label>
                                <input type="number" class="form-control number-input" id="age-0" name="age[]" placeholder="Age" min="0" max="120" required>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <label for="sex-0" class="form-label">Sex</label>
                                <select class="form-select" id="sex-0" name="sex[]" required>
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="section-title mt-4">Incident Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-6">
                                <label for="address-0" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address-0" name="address[]" placeholder="Address" maxlength="255" required>
                            </div>
                            <div class="col-md-6 col-lg-6">
                                <label for="cause-0" class="form-label">Cause</label>
                                <textarea class="form-control" id="cause-0" name="cause[]" placeholder="Cause of death/injury/illness/missing" maxlength="500" required></textarea>
                            </div>
                            <div class="col-md-6 col-lg-6">
                                <label for="remarks-0" class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks-0" name="remarks[]" placeholder="Remarks" maxlength="500"></textarea>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="source-0" class="form-label">Source of Data</label>
                                <select class="form-select" id="source-0" name="source[]" required>
                                    <option value="">Select</option>
                                    <option value="MDM Cluster">MDM Cluster</option>
                                    <option value="LGU">LGU</option>
                                    <option value="DOH">DOH</option>
                                    <option value="PNP">PNP</option>
                                    <option value="BFAR">BFAR</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="validated-0" class="form-label">Validated?</label>
                                <select class="form-select" id="validated-0" name="validated[]" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-remove" onclick="removeEntry(this)">
                                    <i class="bi bi-trash me-2"></i>Remove Entry
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <div>
                        <button type="button" class="btn btn-primary btn-add-entry" id="addEntryBtn">
                            <i class="bi bi-plus-circle me-2"></i>Add Entry
                        </button>
                        <button type="button" class="btn btn-secondary btn-technical-notes" data-bs-toggle="modal" data-bs-target="#technicalNotesModal">
                            <i class="bi bi-info-circle me-2"></i>View Technical Notes
                        </button>
                    </div>
                    <button type="submit" class="btn btn-success btn-submit">
                        <i class="bi bi-save me-2"></i>Save Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Technical Notes Modal - PRESERVED ORIGINAL CONTENT -->
    <div class="modal fade" id="technicalNotesModal" tabindex="-1" aria-labelledby="technicalNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="technicalNotesModalLabel">Technical Notes - Casualties</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>General Guidelines</strong><br>
                        Casualties must be categorized as either dead, injured, ill, or missing.<br>
                        It is recommended to arrange entries by category and then by validation status.<br>
                        Only report missing persons with known identity.<br><br>
                        
                        <strong>Important:</strong> If a previously reported missing person is later found (dead or alive), or if a previously reported death, injury, illness, or missing person is confirmed as not related to the incident, they should be removed from the total casualty count. However, a corresponding note must first be indicated in the Remarks column before removal in the next update.<br><br>
                        
                        <strong>Privacy Notice:</strong> Personal information of casualties must not be released to the public or media without the express consent of the next of kin. Only aggregated numbers of casualties should be publicly disclosed.
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><strong>Region, Province, City/Municipality, Barangay</strong><br>
                            Specify the exact location where the incident occurred or where the casualty was reported.</p>
                            
                            <p><strong>Category</strong><br>
                            Casualties must be categorized as either dead, injured, ill, or missing.</p>
                            
                            <p><strong>Personal Information</strong><br>
                            Include surname, first name, middle name, age, sex, and address of the casualty.</p>
                            
                            <p><strong>Cause</strong><br>
                            Provide the cause of death, nature of injury or illness, or circumstances under which the person went missing.</p>
                            <div class="alert alert-light">
                                <em>Examples: "Crushed by debris during landslide", "High fever post-evacuation", or "Last seen crossing flooded river."</em>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Remarks</strong><br>
                            Include any other relevant details or contextual information about the casualty.</p>
                            
                            <p><strong>Source of Data</strong><br>
                            Indicate the agency or cluster that reported the casualty.</p>
                            <div class="alert alert-light">
                                <em>Examples: MDM Cluster, LGU, DOH.</em>
                            </div>
                            
                            <p><strong>Validated</strong><br>
                            Indicate Yes if the casualty has been confirmed based on required documentation (e.g., LDRRMC or DOH reports, death certificate, certificate of identification), and the incident has been officially linked to the disaster or hazard event.</p>
                            
                            <div class="alert alert-warning">
                                <strong>References:</strong> MDM Field Manual and NDRRMOC SOPG 2024 Edition
                            </div>
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
        const userBarangay = <?php echo json_encode($user_barangay); ?>;
        const userRole = <?php echo json_encode($user_role); ?>;

        // Initialize page with skeleton loader - LIKE ANNEX 5
        document.addEventListener('DOMContentLoaded', function() {
            const skeletonLoader = document.getElementById('pageSkeletonLoader');
            const formContent = document.getElementById('formContent');
            
            skeletonLoader.style.display = 'block';
            formContent.style.display = 'none';
            
            // Simulate page loading
            setTimeout(function() {
                skeletonLoader.style.display = 'none';
                formContent.style.display = 'block';
                setTimeout(function() {
                    formContent.classList.add('loaded');
                }, 50);
            }, 1200);
        });

        // Add entry functionality with skeleton loader - LIKE ANNEX 5
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
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 35%;"></div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-textarea"></div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-textarea"></div>
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
                
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <div class="skeleton-line skeleton-button" style="width: 140px;"></div>
                    </div>
                </div>
            `;
            
            entriesContainer.appendChild(skeletonEntry);
            
            // Simulate loading delay for new entry
            setTimeout(function() {
                skeletonEntry.remove();
                
                // Add actual entry
                const newEntry = document.createElement('div');
                newEntry.className = 'entry-card lazy-entry';
                
                const regionInput = userRole === 'admin' 
                    ? `<input type="text" class="form-control" id="region-${entryCount}" name="region[]" value="Region II" required maxlength="100">`
                    : `<input type="text" class="form-control bg-light" id="region-${entryCount}" name="region[]" value="Region II" readonly>`;
                
                const provinceInput = userRole === 'admin'
                    ? `<input type="text" class="form-control" id="province-${entryCount}" name="province[]" value="Cagayan" required maxlength="100">`
                    : `<input type="text" class="form-control bg-light" id="province-${entryCount}" name="province[]" value="Cagayan" readonly>`;
                
                const cityInput = userRole === 'admin'
                    ? `<input type="text" class="form-control" id="city-${entryCount}" name="city[]" value="Baggao" required maxlength="100">`
                    : `<input type="text" class="form-control bg-light" id="city-${entryCount}" name="city[]" value="Baggao" readonly>`;
                
                newEntry.innerHTML = `
                    <div class="section-title">Location Details</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label for="region-${entryCount}" class="form-label">Region</label>
                            ${regionInput}
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="province-${entryCount}" class="form-label">Province</label>
                            ${provinceInput}
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="city-${entryCount}" class="form-label">City/Municipality</label>
                            ${cityInput}
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="barangay-${entryCount}" class="form-label">Barangay</label>
                            <input type="text" class="form-control bg-light" id="barangay-${entryCount}" name="barangay[]" 
                                value="${userBarangay}" readonly required>
                        </div>
                    </div>

                    <div class="section-title mt-4">Personal Details</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-2">
                            <label for="category-${entryCount}" class="form-label">Category</label>
                            <select class="form-select" id="category-${entryCount}" name="category[]" required>
                                <option value="">Select</option>
                                <option value="Dead">Dead</option>
                                <option value="Injured">Injured</option>
                                <option value="Ill">Ill</option>
                                <option value="Missing">Missing</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label for="surname-${entryCount}" class="form-label">Surname</label>
                            <input type="text" class="form-control" id="surname-${entryCount}" name="surname[]" placeholder="Surname" maxlength="100" required>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label for="firstName-${entryCount}" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName-${entryCount}" name="firstName[]" placeholder="First Name" maxlength="100" required>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label for="middleName-${entryCount}" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middleName-${entryCount}" name="middleName[]" placeholder="Middle Name" maxlength="100">
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label for="age-${entryCount}" class="form-label">Age</label>
                            <input type="number" class="form-control number-input" id="age-${entryCount}" name="age[]" placeholder="Age" min="0" max="120" required>
                        </div>
                        <div class="col-md-6 col-lg-2">
                            <label for="sex-${entryCount}" class="form-label">Sex</label>
                            <select class="form-select" id="sex-${entryCount}" name="sex[]" required>
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title mt-4">Incident Details</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-6">
                            <label for="address-${entryCount}" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address-${entryCount}" name="address[]" placeholder="Address" maxlength="255" required>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <label for="cause-${entryCount}" class="form-label">Cause</label>
                            <textarea class="form-control" id="cause-${entryCount}" name="cause[]" placeholder="Cause of death/injury/illness/missing" maxlength="500" required></textarea>
                        </div>
                        <div class="col-md-6 col-lg-6">
                            <label for="remarks-${entryCount}" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks-${entryCount}" name="remarks[]" placeholder="Remarks" maxlength="500"></textarea>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="source-${entryCount}" class="form-label">Source of Data</label>
                            <select class="form-select" id="source-${entryCount}" name="source[]" required>
                                <option value="">Select</option>
                                <option value="MDM Cluster">MDM Cluster</option>
                                <option value="LGU">LGU</option>
                                <option value="DOH">DOH</option>
                                <option value="PNP">PNP</option>
                                <option value="BFAR">BFAR</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="validated-${entryCount}" class="form-label">Validated?</label>
                            <select class="form-select" id="validated-${entryCount}" name="validated[]" required>
                                <option value="">Select</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-remove" onclick="removeEntry(this)">
                                <i class="bi bi-trash me-2"></i>Remove Entry
                            </button>
                        </div>
                    </div>
                `;
                
                entriesContainer.appendChild(newEntry);
                
                // Animate the new entry
                setTimeout(function() {
                    newEntry.classList.add('loaded');
                }, 50);
                
                // Re-enable button
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Entry';
                
                entryCount++;
            }, 800);
        });

        // Remove entry functionality with animation - LIKE ANNEX 5
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

        // Handle form submission with loading state - LIKE ANNEX 5
        document.getElementById('casualtiesForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const entries = formData.getAll('region[]');
            
            if (entries.length === 0) {
                alert('Please add at least one entry.');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
            submitBtn.disabled = true;
            
            // Submit form via fetch API
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message and redirect
                    alert('Casualties report saved successfully!');
                    window.location.href = 'annex3_records.php';
                } else {
                    // Show error message
                    let errorMessage = data.message || 'Failed to save report';
                    if (data.errors && data.errors.length > 0) {
                        errorMessage += '\n\nErrors:\n' + data.errors.join('\n');
                    }
                    alert(errorMessage);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the report. Please try again.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Client-side validation - LIKE ANNEX 5
        document.getElementById('casualtiesForm').addEventListener('submit', function(e) {
            let isValid = true;
            const entries = this.querySelectorAll('.entry-card');
            
            entries.forEach((entry, index) => {
                const region = entry.querySelector('input[name="region[]"]');
                const province = entry.querySelector('input[name="province[]"]');
                const city = entry.querySelector('input[name="city[]"]');
                const barangay = entry.querySelector('input[name="barangay[]"]');
                const category = entry.querySelector('select[name="category[]"]');
                const surname = entry.querySelector('input[name="surname[]"]');
                const firstName = entry.querySelector('input[name="firstName[]"]');
                const age = entry.querySelector('input[name="age[]"]');
                const sex = entry.querySelector('select[name="sex[]"]');
                const address = entry.querySelector('input[name="address[]"]');
                const cause = entry.querySelector('textarea[name="cause[]"]');
                const source = entry.querySelector('select[name="source[]"]');
                const validated = entry.querySelector('select[name="validated[]"]');
                
                // Check required fields
                if (!region.value || !province.value || !city.value || !barangay.value || 
                    !category.value || !surname.value || !firstName.value || !age.value || 
                    !sex.value || !address.value || !cause.value || !source.value || !validated.value) {
                    isValid = false;
                    alert(`Please fill all required fields in entry ${index + 1}`);
                    e.preventDefault();
                    return;
                }
            });
            
            if (!isValid) {
                const submitButton = this.querySelector('.btn-submit');
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bi bi-save me-2"></i>Save Report';
            }
        });
    </script>
</body>
</html>

<?php
// Get the captured content and store it in a variable - LIKE ANNEX 5
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content - LIKE ANNEX 5
require_once __DIR__ . '/../includes/sidenav.php';
?>
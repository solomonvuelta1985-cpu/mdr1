<?php
/**
 * NDRRMC Annex 19: Families Assisted Report
 * Secure PHP implementation with centralized barangay locking
 */

// Define page title for the header
define('PAGE_TITLE', 'Annex 19 - Families Assisted');

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();
// Rate limiting check for form access (prevent form spam)
$rate_limit_key = 'annex19_form_access_' . $_SESSION['user_id'];
if (!check_rate_limit($rate_limit_key, 100, 3600)) {
    log_security_event(
        $_SESSION['user_id'],
        'rate_limit_exceeded',
        'Annex 19 form access rate limit exceeded (100 per hour)'
    );
    set_flash('Too many form access attempts. Please wait before accessing the form again.', 'error');
    header('Location: annex19_records.php');
    exit;
}

// AUDIT LOG: Form access
log_audit_action(
    $_SESSION['user_id'],
    'annex19_form_access',
    'User accessed the Annex 19 Families Assisted form'
);

// SECURITY LOG: Form access
log_security_event(
    $_SESSION['user_id'],
    'form_access',
    'Accessed Annex 19 form'
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

// Get Baggao barangays for reference
$barangays = get_baggao_barangays();

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
        .readonly-field {
            background-color: #e9ecef;
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
        .bg-light {
            background-color: #f8f9fa !important;
            cursor: not-allowed;
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
                    <div class="col-12">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-12">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input" style="height: 100px;"></div>
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
                <h2>Annex 19: Families Assisted</h2>
                
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

            <form id="familiesAssistedForm" method="POST" action="../api/annex19_save.php">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <!-- Hidden barangay field (auto-filled) -->
                <input type="hidden" name="user_barangay" value="<?php echo htmlspecialchars($user_barangay); ?>">
                
                <div id="entriesContainer">
                    <!-- Initial entry -->
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

                        <div class="section-title mt-4">Assistance Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="familiesRequiringAssistance-0" class="form-label">No. of Families Requiring Assistance</label>
                                <input type="number" class="form-control number-input" id="familiesRequiringAssistance-0" name="familiesRequiringAssistance[]" placeholder="0" min="0" required>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="familiesAssisted-0" class="form-label">No. of Families Assisted</label>
                                <input type="number" class="form-control number-input" id="familiesAssisted-0" name="familiesAssisted[]" placeholder="0" min="0" required>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="percentageAssisted-0" class="form-label">% of Families Assisted</label>
                                <input type="number" class="form-control number-input readonly-field" id="percentageAssisted-0" name="percentageAssisted[]" placeholder="0" readonly>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="assistanceType-0" class="form-label">Type of Assistance</label>
                                <select class="form-select" id="assistanceType-0" name="assistanceType[]" required>
                                    <option value="">Select Type</option>
                                    <option value="Food Packs">Food Packs</option>
                                    <option value="Hygiene Kits">Hygiene Kits</option>
                                    <option value="Shelter Materials">Shelter Materials</option>
                                    <option value="Medical Aid">Medical Aid</option>
                                    <option value="Cash Assistance">Cash Assistance</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="section-title mt-4">Additional Information</div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="assistanceProvider-0" class="form-label">Assistance Provider</label>
                                <select class="form-select" id="assistanceProvider-0" name="assistanceProvider[]" required>
                                    <option value="">Select Provider</option>
                                    <option value="LGU">LGU</option>
                                    <option value="DSWD">DSWD</option>
                                    <option value="NGO">NGO</option>
                                    <option value="Private Sector">Private Sector</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="remarks-0" class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks-0" name="remarks[]" placeholder="Enter remarks (e.g., assistance gaps, reasons, target timeline)" maxlength="1000"></textarea>
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

    <!-- Technical Notes Modal -->
    <div class="modal fade" id="technicalNotesModal" tabindex="-1" aria-labelledby="technicalNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="technicalNotesModalLabel">Technical Notes - Families Assisted</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>General Guide:</strong> Captures the number of families that require humanitarian or emergency assistance during or after a disaster, and the actual number served. The data provides insight into response gaps and helps guide further support or interventions.
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><strong>Region, Province, City/Municipality, Barangay</strong><br>
                            Indicate the exact geographic location where the assistance is being monitored.</p>
                            
                            <p><strong>No. of Families Requiring Assistance</strong><br>
                            Reflects the total number of families that were identified as needing any form of assistance (e.g., food, non-food items, shelter, medical aid).</p>
                            
                            <p><strong>No. of Families Assisted</strong><br>
                            Total number of families who have already received assistance (partial or complete).</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>% of Families Assisted</strong><br>
                            This is the proportion of assisted families versus those requiring help.<br>
                            <strong>Formula:</strong> (No. of Families Assisted ÷ No. of Families Requiring Assistance) × 100</p>
                            
                            <p><strong>Type of Assistance</strong><br>
                            Categorize the assistance provided using standard terms such as food packs, hygiene kits, shelter materials, etc.</p>
                            
                            <p><strong>Assistance Provider</strong><br>
                            Indicate the source or provider of assistance (e.g., LGU, DSWD, NGOs).</p>
                            
                            <p><strong>Remarks</strong><br>
                            Include additional information such as:
                            <ul>
                                <li>Type of assistance provided</li>
                                <li>Reasons for any gaps (e.g., inaccessible areas, supply constraints)</li>
                                <li>Target timeline for full coverage</li>
                            </ul>
                            </p>
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
        const userBarangay = <?php echo json_encode($user_barangay); ?>;
        const userRole = <?php echo json_encode($user_role); ?>;

        // Initialize page with skeleton loader
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
            
            // Attach percentage calculation to initial entry
            attachPercentageCalculation(document.querySelector('.entry-card'));
        });

        // Helper: safely convert value -> number
        function toNumber(v) {
            const n = Number((v || '').toString().replace(/,/g, '').trim());
            return isNaN(n) ? 0 : n;
        }

        // Calculate percentage for a specific entry card
        function calculatePercentage(entryCard) {
            if (!entryCard) return;

            const familiesRequiring = toNumber(entryCard.querySelector('[name="familiesRequiringAssistance[]"]')?.value);
            const familiesAssisted = toNumber(entryCard.querySelector('[name="familiesAssisted[]"]')?.value);
            const percentageField = entryCard.querySelector('[name="percentageAssisted[]"]');

            if (percentageField && familiesRequiring > 0) {
                const percentage = (familiesAssisted / familiesRequiring) * 100;
                percentageField.value = percentage.toFixed(2);
            } else if (percentageField) {
                percentageField.value = 0;
            }
        }

        // Attach percentage calculation to an entry
        function attachPercentageCalculation(entryCard) {
            const selector = '[name="familiesRequiringAssistance[]"], [name="familiesAssisted[]"]';
            const inputs = entryCard.querySelectorAll(selector);
            inputs.forEach(input => {
                input.addEventListener('input', () => calculatePercentage(input.closest('.entry-card')));
                input.addEventListener('change', () => calculatePercentage(input.closest('.entry-card')));
            });
            // Initial calculation for the entry
            calculatePercentage(entryCard);
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
                    <div class="col-12">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-12">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input" style="height: 100px;"></div>
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

                    <div class="section-title mt-4">Assistance Details</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label for="familiesRequiringAssistance-${entryCount}" class="form-label">No. of Families Requiring Assistance</label>
                            <input type="number" class="form-control number-input" id="familiesRequiringAssistance-${entryCount}" name="familiesRequiringAssistance[]" placeholder="0" min="0" required>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="familiesAssisted-${entryCount}" class="form-label">No. of Families Assisted</label>
                            <input type="number" class="form-control number-input" id="familiesAssisted-${entryCount}" name="familiesAssisted[]" placeholder="0" min="0" required>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="percentageAssisted-${entryCount}" class="form-label">% of Families Assisted</label>
                            <input type="number" class="form-control number-input readonly-field" id="percentageAssisted-${entryCount}" name="percentageAssisted[]" placeholder="0" readonly>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="assistanceType-${entryCount}" class="form-label">Type of Assistance</label>
                            <select class="form-select" id="assistanceType-${entryCount}" name="assistanceType[]" required>
                                <option value="">Select Type</option>
                                <option value="Food Packs">Food Packs</option>
                                <option value="Hygiene Kits">Hygiene Kits</option>
                                <option value="Shelter Materials">Shelter Materials</option>
                                <option value="Medical Aid">Medical Aid</option>
                                <option value="Cash Assistance">Cash Assistance</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title mt-4">Additional Information</div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="assistanceProvider-${entryCount}" class="form-label">Assistance Provider</label>
                            <select class="form-select" id="assistanceProvider-${entryCount}" name="assistanceProvider[]" required>
                                <option value="">Select Provider</option>
                                <option value="LGU">LGU</option>
                                <option value="DSWD">DSWD</option>
                                <option value="NGO">NGO</option>
                                <option value="Private Sector">Private Sector</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="remarks-${entryCount}" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks-${entryCount}" name="remarks[]" placeholder="Enter remarks (e.g., assistance gaps, reasons, target timeline)" maxlength="1000"></textarea>
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
                
                // Attach percentage calculation to new entry
                attachPercentageCalculation(newEntry);
                
                // Trigger lazy loading animation
                setTimeout(function() {
                    newEntry.classList.add('loaded');
                }, 50);
                
                // Re-enable button
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Entry';
                
                entryCount++;
            }, 800);
        });

        // Remove entry functionality with animation
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

        // Handle form submission with loading state
        document.getElementById('familiesAssistedForm').addEventListener('submit', function(e) {
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
            
            // Submit form
            this.submit();
        });

        // Client-side validation
        document.getElementById('familiesAssistedForm').addEventListener('submit', function(e) {
            let isValid = true;
            const entries = this.querySelectorAll('.entry-card');
            
            entries.forEach((entry, index) => {
                const region = entry.querySelector('input[name="region[]"]');
                const province = entry.querySelector('input[name="province[]"]');
                const city = entry.querySelector('input[name="city[]"]');
                const barangay = entry.querySelector('input[name="barangay[]"]');
                const familiesRequiring = entry.querySelector('input[name="familiesRequiringAssistance[]"]');
                const familiesAssisted = entry.querySelector('input[name="familiesAssisted[]"]');
                const assistanceType = entry.querySelector('select[name="assistanceType[]"]');
                const assistanceProvider = entry.querySelector('select[name="assistanceProvider[]"]');
                
                // Check required fields
                if (!region.value || !province.value || !city.value || !barangay.value) {
                    isValid = false;
                    alert(`Please fill all location fields in entry ${index + 1}`);
                    e.preventDefault();
                    return;
                }
                
                if (!familiesRequiring.value || parseFloat(familiesRequiring.value) < 0) {
                    isValid = false;
                    alert(`Please enter a valid number for families requiring assistance in entry ${index + 1}`);
                    e.preventDefault();
                    return;
                }
                
                if (!familiesAssisted.value || parseFloat(familiesAssisted.value) < 0) {
                    isValid = false;
                    alert(`Please enter a valid number for families assisted in entry ${index + 1}`);
                    e.preventDefault();
                    return;
                }
                
                if (!assistanceType.value) {
                    isValid = false;
                    alert(`Please select assistance type in entry ${index + 1}`);
                    e.preventDefault();
                    return;
                }
                
                if (!assistanceProvider.value) {
                    isValid = false;
                    alert(`Please select assistance provider in entry ${index + 1}`);
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

<?php
// Get the captured content and store it in a variable
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content
require_once __DIR__ . '/../includes/sidenav.php';
?>
<?php
/**
 * NDRRMC Annex 18: Pre-emptive Evacuation (Animals) Report
 * Secure PHP implementation with centralized barangay locking
 */

// Define page title for the header
define('PAGE_TITLE', 'Annex 18 - Pre-emptive Evacuation (Animals)');

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();
// Rate limiting check for form access (prevent form spam)
$rate_limit_key = 'annex18_form_access_' . $_SESSION['user_id'];
if (!check_rate_limit($rate_limit_key, 100, 3600)) {
    log_security_event(
        $_SESSION['user_id'],
        'rate_limit_exceeded',
        'Annex 18 form access rate limit exceeded (100 per hour)'
    );
    set_flash('Too many form access attempts. Please wait before accessing the form again.', 'error');
    header('Location: annex18_records.php');
    exit;
}

// AUDIT LOG: Form access
log_audit_action(
    $_SESSION['user_id'],
    'annex18_form_access',
    'User accessed the Annex 18 Animal Evacuation form'
);

// SECURITY LOG: Form access
log_security_event(
    $_SESSION['user_id'],
    'form_access',
    'Accessed Annex 18 form'
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
                    <div class="col-md-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 35%;"></div>
                <div class="row g-3">
                    <div class="col-12">
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
                <h2>Annex 18: Pre-emptive Evacuation (Animals)</h2>
                
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

            <form id="animalEvacuationForm" method="POST" action="../api/annex18_save.php">
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

                        <div class="section-title mt-4">Animal Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="classification-0" class="form-label">Classification</label>
                                <select class="form-select classification-select" id="classification-0" name="classification[]" required>
                                    <option value="">Select Classification</option>
                                    <option value="Livestock">Livestock</option>
                                    <option value="Poultry">Poultry</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="type-0" class="form-label">Type</label>
                                <select class="form-select type-select" id="type-0" name="type[]" required>
                                    <option value="">Select Type</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="quantity-0" class="form-label">Quantity (No. of heads)</label>
                                <input type="number" class="form-control number-input" id="quantity-0" name="quantity[]" placeholder="0" min="0" required>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="evacuationReason-0" class="form-label">Reason for Evacuation</label>
                                <select class="form-select" id="evacuationReason-0" name="evacuationReason[]" required>
                                    <option value="">Select Reason</option>
                                    <option value="Expected Flooding">Expected Flooding</option>
                                    <option value="Volcanic Eruption">Volcanic Eruption</option>
                                    <option value="Typhoon">Typhoon</option>
                                    <option value="Storm Surge">Storm Surge</option>
                                    <option value="Landslide Risk">Landslide Risk</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="section-title mt-4">Evacuation Details</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="shelterLocation-0" class="form-label">Temporary Shelter Location</label>
                                <input type="text" class="form-control" id="shelterLocation-0" name="shelterLocation[]" placeholder="e.g., designated evacuation pens, community centers" maxlength="255">
                            </div>
                            <div class="col-md-6">
                                <label for="assistanceProvider-0" class="form-label">Assistance Provider</label>
                                <select class="form-select" id="assistanceProvider-0" name="assistanceProvider[]">
                                    <option value="">Select Provider</option>
                                    <option value="LGU">LGU</option>
                                    <option value="DA">DA (Department of Agriculture)</option>
                                    <option value="NGO">NGO</option>
                                    <option value="Private Sector">Private Sector</option>
                                    <option value="Community">Community</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="section-title mt-4">Additional Information</div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="remarks-0" class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks-0" name="remarks[]" placeholder="Enter additional details (e.g., assistance gaps, coordination needs, special requirements)" maxlength="1000"></textarea>
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
                    <h5 class="modal-title" id="technicalNotesModalLabel">Technical Notes - Pre-emptive Evacuation (Animals)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>General Guide:</strong> Pre-emptive evacuation of animals is crucial to safeguard livelihoods, prevent economic losses, and reduce risks to both humans and animals during disasters. This table captures animals evacuated ahead of anticipated hazard impacts.
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><strong>Region, Province, City/Municipality, Barangay</strong><br>
                            Indicate the specific geographic location where pre-emptive animal evacuation occurred.</p>
                            
                            <p><strong>Classification</strong><br>
                            Identify whether the animals fall under:
                            <ul>
                                <li><strong>Livestock</strong></li>
                                <li><strong>Poultry</strong></li>
                            </ul>
                            </p>
                            
                            <p><strong>Type</strong><br>
                            Provide the specific animal type based on the classification:
                            <ul>
                                <li><strong>Livestock:</strong>
                                    <ul>
                                        <li>Pig</li>
                                        <li>Cow</li>
                                        <li>Goat</li>
                                        <li>Horse</li>
                                    </ul>
                                </li>
                                <li><strong>Poultry:</strong>
                                    <ul>
                                        <li>Chicken</li>
                                        <li>Duck</li>
                                    </ul>
                                </li>
                            </ul>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Quantity</strong><br>
                            Indicate the total number of evacuated animals by type. Use whole numbers only.</p>
                            
                            <p><strong>Reason for Evacuation</strong><br>
                            Specify why the animals were evacuated (e.g., expected flooding, volcanic eruption, typhoon).</p>
                            
                            <p><strong>Temporary Shelter Location</strong><br>
                            Indicate where the animals are being temporarily housed (e.g., designated evacuation pens, community centers).</p>
                            
                            <p><strong>Assistance Provider</strong><br>
                            Identify who is providing assistance for the evacuation (e.g., LGU, DA, NGOs).</p>
                            
                            <p><strong>Remarks</strong><br>
                            Include any relevant details such as:
                            <ul>
                                <li>Assistance or coordination needs from LGU, DA, or NGOs</li>
                                <li>Gaps in resources or support</li>
                                <li>Special requirements for specific animal types</li>
                                <li>Expected duration of evacuation</li>
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

        // Animal type options based on classification
        const animalTypes = {
            'Livestock': ['Pig', 'Cow', 'Goat', 'Horse'],
            'Poultry': ['Chicken', 'Duck']
        };

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

            // Attach listeners to initial classification selects
            attachClassificationListeners(document.querySelector('.entry-card'));
        });

        // Update type options when classification changes
        function updateTypeOptions(classificationSelect, typeSelect) {
            const classification = classificationSelect.value;
            typeSelect.innerHTML = '<option value="">Select Type</option>';
            
            if (classification && animalTypes[classification]) {
                animalTypes[classification].forEach(type => {
                    const option = document.createElement('option');
                    option.value = type;
                    option.textContent = type;
                    typeSelect.appendChild(option);
                });
            }
        }

        // Attach listeners to classification selects
        function attachClassificationListeners(entryCard) {
            const classificationSelect = entryCard.querySelector('.classification-select');
            const typeSelect = entryCard.querySelector('.type-select');
            
            if (classificationSelect && typeSelect) {
                classificationSelect.addEventListener('change', function() {
                    updateTypeOptions(this, typeSelect);
                });
                
                // Initialize type options if classification is already selected
                if (classificationSelect.value) {
                    updateTypeOptions(classificationSelect, typeSelect);
                }
            }
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
                    <div class="col-md-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>
                
                <div class="skeleton-line skeleton-title mt-4" style="width: 35%;"></div>
                <div class="row g-3">
                    <div class="col-12">
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

                    <div class="section-title mt-4">Animal Details</div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label for="classification-${entryCount}" class="form-label">Classification</label>
                            <select class="form-select classification-select" id="classification-${entryCount}" name="classification[]" required>
                                <option value="">Select Classification</option>
                                <option value="Livestock">Livestock</option>
                                <option value="Poultry">Poultry</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="type-${entryCount}" class="form-label">Type</label>
                            <select class="form-select type-select" id="type-${entryCount}" name="type[]" required>
                                <option value="">Select Type</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="quantity-${entryCount}" class="form-label">Quantity (No. of heads)</label>
                            <input type="number" class="form-control number-input" id="quantity-${entryCount}" name="quantity[]" placeholder="0" min="0" required>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="evacuationReason-${entryCount}" class="form-label">Reason for Evacuation</label>
                            <select class="form-select" id="evacuationReason-${entryCount}" name="evacuationReason[]" required>
                                <option value="">Select Reason</option>
                                <option value="Expected Flooding">Expected Flooding</option>
                                <option value="Volcanic Eruption">Volcanic Eruption</option>
                                <option value="Typhoon">Typhoon</option>
                                <option value="Storm Surge">Storm Surge</option>
                                <option value="Landslide Risk">Landslide Risk</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title mt-4">Evacuation Details</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="shelterLocation-${entryCount}" class="form-label">Temporary Shelter Location</label>
                            <input type="text" class="form-control" id="shelterLocation-${entryCount}" name="shelterLocation[]" placeholder="e.g., designated evacuation pens, community centers" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label for="assistanceProvider-${entryCount}" class="form-label">Assistance Provider</label>
                            <select class="form-select" id="assistanceProvider-${entryCount}" name="assistanceProvider[]">
                                <option value="">Select Provider</option>
                                <option value="LGU">LGU</option>
                                <option value="DA">DA (Department of Agriculture)</option>
                                <option value="NGO">NGO</option>
                                <option value="Private Sector">Private Sector</option>
                                <option value="Community">Community</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title mt-4">Additional Information</div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="remarks-${entryCount}" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks-${entryCount}" name="remarks[]" placeholder="Enter additional details (e.g., assistance gaps, coordination needs, special requirements)" maxlength="1000"></textarea>
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
                
                // Add event listeners to the new entry
                attachClassificationListeners(newEntry);
                
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
        document.getElementById('animalEvacuationForm').addEventListener('submit', function(e) {
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
        document.getElementById('animalEvacuationForm').addEventListener('submit', function(e) {
            let isValid = true;
            const entries = this.querySelectorAll('.entry-card');
            
            entries.forEach((entry, index) => {
                const region = entry.querySelector('input[name="region[]"]');
                const province = entry.querySelector('input[name="province[]"]');
                const city = entry.querySelector('input[name="city[]"]');
                const barangay = entry.querySelector('input[name="barangay[]"]');
                const classification = entry.querySelector('select[name="classification[]"]');
                const type = entry.querySelector('select[name="type[]"]');
                const quantity = entry.querySelector('input[name="quantity[]"]');
                const evacuationReason = entry.querySelector('select[name="evacuationReason[]"]');
                
                // Check required fields
                if (!region.value || !province.value || !city.value || !barangay.value) {
                    isValid = false;
                    alert(`Please fill all location fields in entry ${index + 1}`);
                    e.preventDefault();
                    return;
                }
                
                if (!classification.value) {
                    isValid = false;
                    alert(`Please select classification in entry ${index + 1}`);
                    e.preventDefault();
                    return;
                }
                
                if (!type.value) {
                    isValid = false;
                    alert(`Please select type in entry ${index + 1}`);
                    e.preventDefault();
                    return;
                }
                
                if (!quantity.value || parseInt(quantity.value) < 0) {
                    isValid = false;
                    alert(`Please enter a valid quantity in entry ${index + 1}`);
                    e.preventDefault();
                    return;
                }
                
                if (!evacuationReason.value) {
                    isValid = false;
                    alert(`Please select evacuation reason in entry ${index + 1}`);
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
<?php
/**
 * NDRRMC Annex 20: Assistance Provided to Families Report
 * Secure PHP implementation with centralized barangay locking
 */

// Define page title for the header
define('PAGE_TITLE', 'Annex 20 - Assistance Provided to Families');

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();
// Annex Access Control - Only Admin and Sheila can access
require_once __DIR__ . '/../includes/check_annex_access.php';
require_annex_access_or_redirect('20');


// Rate limiting check for form access (prevent form spam)
$rate_limit_key = 'annex20_form_access_' . $_SESSION['user_id'];
if (!check_rate_limit($rate_limit_key, 100, 3600)) {
    log_security_event(
        $_SESSION['user_id'],
        'rate_limit_exceeded',
        'Annex 20 form access rate limit exceeded (100 per hour)'
    );
    set_flash('Too many form access attempts. Please wait before accessing the form again.', 'error');
    header('Location: annex20_records.php');
    exit;
}

// AUDIT LOG: Form access
log_audit_action(
    $_SESSION['user_id'],
    'annex20_form_access',
    'User accessed the Annex 20 Assistance Provided to Families form'
);

// SECURITY LOG: Form access
log_security_event(
    $_SESSION['user_id'],
    'form_access',
    'Accessed Annex 20 form'
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

// Predefined options for form
$clusters = [
    'Food',
    'WASH',
    'Shelter',
    'Health',
    'Protection',
    'Education',
    'Nutrition',
    'Camp Management',
    'Emergency Telecommunications',
    'Logistics'
];

$assistance_types = [
    'Food packs',
    'Hygiene kits',
    'Water containers',
    'Temporary shelter materials',
    'Medicines',
    'Blankets',
    'Sleeping mats',
    'Cooking utensils',
    'Mosquito nets',
    'Water purification tablets',
    'Other'
];

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
                    <div class="col-md-4">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>

                <div class="skeleton-line skeleton-title mt-4" style="width: 30%;"></div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-2">
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
                <h2>Annex 20: Assistance Provided to Families</h2>

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

            <form id="assistanceProvidedForm" method="POST" action="../api/annex20_save.php">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- Hidden barangay field (auto-filled) -->
                <input type="hidden" name="user_barangay" value="<?php echo htmlspecialchars($user_barangay); ?>">

                <div id="entriesContainer">
                    <!-- Initial entry -->
                    <div class="entry-card lazy-entry loaded">
                        <div class="section-title">Location Details</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="region-0" class="form-label">Region</label>
                                <?php if ($user_role === 'admin'): ?>
                                    <input type="text" class="form-control" id="region-0" name="region[]" value="Region II" required maxlength="100">
                                <?php else: ?>
                                    <input type="text" class="form-control bg-light" id="region-0" name="region[]" value="Region II" readonly>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label for="province-0" class="form-label">Province</label>
                                <?php if ($user_role === 'admin'): ?>
                                    <input type="text" class="form-control" id="province-0" name="province[]" value="Cagayan" required maxlength="100">
                                <?php else: ?>
                                    <input type="text" class="form-control bg-light" id="province-0" name="province[]" value="Cagayan" readonly>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label for="city-0" class="form-label">City/Municipality</label>
                                <?php if ($user_role === 'admin'): ?>
                                    <input type="text" class="form-control" id="city-0" name="city[]" value="Baggao" required maxlength="100">
                                <?php else: ?>
                                    <input type="text" class="form-control bg-light" id="city-0" name="city[]" value="Baggao" readonly>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="section-title mt-4">Assistance Details</div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="cluster-0" class="form-label">Cluster</label>
                                <select class="form-select" id="cluster-0" name="cluster[]" required>
                                    <option value="">Select Cluster</option>
                                    <?php foreach ($clusters as $cluster): ?>
                                        <option value="<?php echo htmlspecialchars($cluster); ?>"><?php echo htmlspecialchars($cluster); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="type-0" class="form-label">Type</label>
                                <select class="form-select" id="type-0" name="type[]" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($assistance_types as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="quantity-0" class="form-label">Quantity</label>
                                <input type="number" class="form-control number-input" id="quantity-0" name="quantity[]" placeholder="0" min="0" step="1" onchange="calculateAmount(this)" required>
                            </div>
                            <div class="col-md-2">
                                <label for="unit-0" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit-0" name="unit[]" placeholder="e.g., piece, pack" maxlength="50" required>
                            </div>
                            <div class="col-md-2">
                                <label for="costPerUnit-0" class="form-label">Cost per Unit</label>
                                <input type="number" class="form-control number-input" id="costPerUnit-0" name="costPerUnit[]" placeholder="0.00" min="0" step="0.01" onchange="calculateAmount(this)" required>
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-2">
                                <label for="amount-0" class="form-label">Amount</label>
                                <input type="number" class="form-control number-input bg-light" id="amount-0" name="amount[]" placeholder="0.00" readonly>
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
                    <h5 class="modal-title" id="technicalNotesModalLabel">Technical Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>General Guide:</strong> Documents the types and amounts of assistance provided directly to families affected by disasters. It helps monitor the delivery of food and non-food items (F/NFIs), assess resource needs, and inform logistical planning.
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><strong>Region, Province, City/Municipality</strong><br>
                            Indicate the specific geographic location where the assistance was delivered.</p>

                            <p><strong>Cluster</strong><br>
                            Identify the humanitarian or sectoral cluster (e.g., Food, WASH, Shelter, Health, Protection).</p>

                            <p><strong>Type</strong><br>
                            Specify the item or service provided, such as:
                            <ul>
                                <li>Food packs</li>
                                <li>Hygiene kits</li>
                                <li>Water containers</li>
                                <li>Temporary shelter materials</li>
                                <li>Medicines</li>
                            </ul>
                            </p>

                            <p><strong>Quantity</strong><br>
                            Number of items or service units distributed.</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Unit</strong><br>
                            Measurement unit used (e.g., piece, pack, kit, liter, kilogram).</p>

                            <p><strong>Cost per Unit</strong><br>
                            Estimated or actual cost of each unit.</p>

                            <p><strong>Total Amount</strong><br>
                            Total cost of the assistance provided.<br>
                            <strong>Formula:</strong> Quantity × Cost per Unit</p>

                            <p><strong>Remarks</strong><br>
                            Include relevant details such as:
                            <ul>
                                <li>Source of assistance (e.g., DSWD, LGU, NGO, private donor)</li>
                                <li>Targeted or priority recipients (e.g., IPs, PWDs, solo parents)</li>
                                <li>Delivery date or any challenges encountered during distribution</li>
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
        const clusters = <?php echo json_encode($clusters); ?>;
        const assistanceTypes = <?php echo json_encode($assistance_types); ?>;

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
        });

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
                    <div class="col-md-4">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                </div>

                <div class="skeleton-line skeleton-title mt-4" style="width: 30%;"></div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-3">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-2">
                        <div class="skeleton-line skeleton-label"></div>
                        <div class="skeleton-line skeleton-input"></div>
                    </div>
                    <div class="col-md-2">
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

                // Build cluster options
                let clusterOptions = '<option value="">Select Cluster</option>';
                clusters.forEach(cluster => {
                    clusterOptions += `<option value="${escapeHtml(cluster)}">${escapeHtml(cluster)}</option>`;
                });

                // Build assistance type options
                let typeOptions = '<option value="">Select Type</option>';
                assistanceTypes.forEach(type => {
                    typeOptions += `<option value="${escapeHtml(type)}">${escapeHtml(type)}</option>`;
                });

                newEntry.innerHTML = `
                    <div class="section-title">Location Details</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="region-${entryCount}" class="form-label">Region</label>
                            ${regionInput}
                        </div>
                        <div class="col-md-4">
                            <label for="province-${entryCount}" class="form-label">Province</label>
                            ${provinceInput}
                        </div>
                        <div class="col-md-4">
                            <label for="city-${entryCount}" class="form-label">City/Municipality</label>
                            ${cityInput}
                        </div>
                    </div>

                    <div class="section-title mt-4">Assistance Details</div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="cluster-${entryCount}" class="form-label">Cluster</label>
                            <select class="form-select" id="cluster-${entryCount}" name="cluster[]" required>
                                ${clusterOptions}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="type-${entryCount}" class="form-label">Type</label>
                            <select class="form-select" id="type-${entryCount}" name="type[]" required>
                                ${typeOptions}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="quantity-${entryCount}" class="form-label">Quantity</label>
                            <input type="number" class="form-control number-input" id="quantity-${entryCount}" name="quantity[]" placeholder="0" min="0" step="1" onchange="calculateAmount(this)" required>
                        </div>
                        <div class="col-md-2">
                            <label for="unit-${entryCount}" class="form-label">Unit</label>
                            <input type="text" class="form-control" id="unit-${entryCount}" name="unit[]" placeholder="e.g., piece, pack" maxlength="50" required>
                        </div>
                        <div class="col-md-2">
                            <label for="costPerUnit-${entryCount}" class="form-label">Cost per Unit</label>
                            <input type="number" class="form-control number-input" id="costPerUnit-${entryCount}" name="costPerUnit[]" placeholder="0.00" min="0" step="0.01" onchange="calculateAmount(this)" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-2">
                            <label for="amount-${entryCount}" class="form-label">Amount</label>
                            <input type="number" class="form-control number-input bg-light" id="amount-${entryCount}" name="amount[]" placeholder="0.00" readonly>
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
                            <button type="button" class="btn btn-remove" onclick="removeEntry(this)">
                                <i class="bi bi-trash me-2"></i>Remove Entry
                            </button>
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

        // Calculate total amount
        function calculateAmount(input) {
            const entry = input.closest('.entry-card');
            const quantityInput = entry.querySelector('input[name="quantity[]"]');
            const costPerUnitInput = entry.querySelector('input[name="costPerUnit[]"]');
            const amountInput = entry.querySelector('input[name="amount[]"]');

            const quantity = parseFloat(quantityInput.value) || 0;
            const costPerUnit = parseFloat(costPerUnitInput.value) || 0;
            const amount = quantity * costPerUnit;

            amountInput.value = amount.toFixed(2);
        }

        // HTML escape function for XSS prevention
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Handle form submission with loading state
        document.getElementById('assistanceProvidedForm').addEventListener('submit', function(e) {
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
        document.getElementById('assistanceProvidedForm').addEventListener('submit', function(e) {
            let isValid = true;
            const entries = this.querySelectorAll('.entry-card');

            entries.forEach((entry, index) => {
                const region = entry.querySelector('input[name="region[]"]');
                const province = entry.querySelector('input[name="province[]"]');
                const city = entry.querySelector('input[name="city[]"]');
                const cluster = entry.querySelector('select[name="cluster[]"]');
                const type = entry.querySelector('select[name="type[]"]');
                const quantity = entry.querySelector('input[name="quantity[]"]');
                const unit = entry.querySelector('input[name="unit[]"]');
                const costPerUnit = entry.querySelector('input[name="costPerUnit[]"]');

                // Check required fields
                if (!region.value || !province.value || !city.value) {
                    isValid = false;
                    alert(`Please fill all location fields in entry ${index + 1}`);
                    e.preventDefault();
                    return;
                }

                if (!cluster.value || !type.value) {
                    isValid = false;
                    alert(`Please select cluster and type in entry ${index + 1}`);
                    e.preventDefault();
                    return;
                }

                if (!quantity.value || !unit.value || !costPerUnit.value) {
                    isValid = false;
                    alert(`Please fill all assistance details in entry ${index + 1}`);
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

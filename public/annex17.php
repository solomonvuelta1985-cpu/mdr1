<?php
/**
 * NDRRMC Annex 17: Pre-emptive Evacuation (People) Report
 * Secure PHP implementation with centralized barangay locking
 */

// Define page title for the header
define('PAGE_TITLE', 'Annex 17 - Pre-emptive Evacuation (People)');

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// Rate limiting check for form access (prevent form spam)
$rate_limit_key = 'annex17_form_access_' . $_SESSION['user_id'];
if (!check_rate_limit($rate_limit_key, 100, 3600)) {
    log_security_event(
        $_SESSION['user_id'],
        'rate_limit_exceeded',
        'Annex 17 form access rate limit exceeded (100 per hour)'
    );
    set_flash('Too many form access attempts. Please wait before accessing the form again.', 'error');
    header('Location: annex17_records.php');
    exit;
}

// AUDIT LOG: Form access
log_audit_action(
    $_SESSION['user_id'],
    'annex17_form_access',
    'User accessed the Annex 17 Pre-emptive Evacuation (People) form'
);

// SECURITY LOG: Form access
log_security_event(
    $_SESSION['user_id'],
    'form_access',
    'Accessed Annex 17 form'
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

// Fetch cumulative settings for this barangay
$cumulative_settings = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM annex17_cumulative_settings WHERE barangay = ?");
    $stmt->execute([$user_barangay]);
    $cumulative_settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Error fetching Annex 17 cumulative settings: " . $e->getMessage());
}

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
    .btn-submit {
        background-color: #198754;
        border: none;
    }
    .btn-submit:hover {
        background-color: #157347;
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

    /* Tab Styles - Simplified */
    .tab-navigation {
        display: flex;
        gap: 5px;
        margin-bottom: 20px;
        border-bottom: 2px solid #dee2e6;
        flex-wrap: wrap;
    }
    .tab-button {
        padding: 10px 15px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        font-weight: 500;
        color: #6c757d;
        transition: all 0.2s;
        font-size: clamp(0.85rem, 2.3vw, 0.95rem);
    }
    .tab-button:hover {
        color: #0d6efd;
    }
    .tab-button.active {
        color: #0d6efd;
        border-bottom-color: #0d6efd;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }

    /* Compact field groups */
    .field-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-bottom: 15px;
    }
    .field-item label {
        font-size: clamp(0.85rem, 2.2vw, 0.9rem);
        margin-bottom: 4px;
    }
    .field-item input {
        font-size: clamp(0.85rem, 2.2vw, 0.9rem);
        height: 42px;
    }

    /* Cumulative field styling */
    .cumulative-field {
        background-color: #e9ecef !important;
        cursor: not-allowed !important;
        font-weight: 500;
        border: 1px solid #ced4da !important;
    }

    /* Toggle cumulative fields button */
    .toggle-cumulative-btn {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .toggle-cumulative-btn:hover {
        background-color: #5a6268;
    }
    .toggle-cumulative-btn.hidden-mode {
        background-color: #0d6efd;
    }
    .toggle-cumulative-btn.hidden-mode:hover {
        background-color: #0b5ed7;
    }

    /* Hide cumulative fields when toggled */
    .cumulative-hidden .cumulative-field,
    .cumulative-hidden .cumulative-label {
        display: none !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
            align-items: stretch;
        }
        .action-buttons > div {
            flex-direction: column;
        }
        .btn {
            width: 100%;
        }
        .tab-button {
            font-size: 0.8rem;
            padding: 8px 10px;
        }
    }
</style>

<div class="form-container">
    <div class="header-section">
        <h1>NDRRMC Memorandum Circular No. 05, s. 2025</h1>
        <h2>Annex 17: Pre-emptive Evacuation (People)</h2>

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

        <!-- Toggle Cumulative Fields Button -->
        <div class="mt-3">
            <button type="button" class="toggle-cumulative-btn" id="toggleCumulativeBtn" onclick="toggleCumulativeFields()">
                <i class="fas fa-eye-slash"></i>
                <span id="toggleBtnText">Hide Cumulative Fields</span>
            </button>
            <small class="d-block mt-2 text-muted">
                <i class="fas fa-info-circle"></i> Cumulative values are auto-loaded from settings and are read-only. Toggle to focus on current data entry.
            </small>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php show_flash(); ?>

    <form id="peopleEvacuationForm" method="POST" action="../api/annex17_save.php">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">

        <!-- Hidden barangay field (auto-filled) -->
        <input type="hidden" name="user_barangay" value="<?= htmlspecialchars($user_barangay); ?>">

        <div class="entry-card">
            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <button type="button" class="tab-button active" data-tab="basic">Basic Info</button>
                <button type="button" class="tab-button" data-tab="age">Age Distribution</button>
                <button type="button" class="tab-button" data-tab="vulnerable">Vulnerable Groups</button>
                <button type="button" class="tab-button" data-tab="summary">Summary</button>
            </div>

            <!-- Tab 1: Basic Information -->
            <div class="tab-content active" id="tab-basic">
                <div class="section-title">Location Details</div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <label for="region" class="form-label">Region</label>
                        <?php if ($user_role === 'admin'): ?>
                            <input type="text" class="form-control" id="region" name="region" value="Region II" required maxlength="100">
                        <?php else: ?>
                            <input type="text" class="form-control bg-light" id="region" name="region" value="Region II" readonly>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="province" class="form-label">Province</label>
                        <?php if ($user_role === 'admin'): ?>
                            <input type="text" class="form-control" id="province" name="province" value="Cagayan" required maxlength="100">
                        <?php else: ?>
                            <input type="text" class="form-control bg-light" id="province" name="province" value="Cagayan" readonly>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="city_municipality" class="form-label">City/Municipality</label>
                        <?php if ($user_role === 'admin'): ?>
                            <input type="text" class="form-control" id="city_municipality" name="city_municipality" value="Baggao" required maxlength="100">
                        <?php else: ?>
                            <input type="text" class="form-control bg-light" id="city_municipality" name="city_municipality" value="Baggao" readonly>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="barangay" class="form-label">Barangay</label>
                        <input type="text" class="form-control bg-light" id="barangay" name="barangay"
                            value="<?= htmlspecialchars($user_barangay); ?>" readonly required>
                    </div>
                </div>

                <div class="section-title mt-4">General Information</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="families" class="form-label">Number of Families</label>
                        <input type="number" class="form-control number-input" id="families" name="families" placeholder="0" min="0" value="0">
                    </div>
                </div>
            </div>

            <!-- Tab 2: Age Distribution -->
            <div class="tab-content" id="tab-age">
                <div class="section-title">Sex and Age Distribution</div>
                <p class="text-muted small mb-3">Enter Cumulative (total accumulated) and Current (latest period) figures for each age group</p>

                <?php
                $age_groups = [
                    ['title' => 'Infant (0-6 months)', 'prefix' => 'infant'],
                    ['title' => 'Toddlers (7 months - 2 years)', 'prefix' => 'toddler'],
                    ['title' => 'Preschooler (3-5 years)', 'prefix' => 'preschool'],
                    ['title' => 'School Age (6-12 years)', 'prefix' => 'school'],
                    ['title' => 'Teenage (13-17 years)', 'prefix' => 'teenage'],
                    ['title' => 'Adult (18-59 years)', 'prefix' => 'adult'],
                    ['title' => 'Elderly (60+ years)', 'prefix' => 'elderly']
                ];

                foreach ($age_groups as $group):
                    $male_cumulative = $cumulative_settings[$group['prefix'] . '_male_cumulative'] ?? 0;
                    $female_cumulative = $cumulative_settings[$group['prefix'] . '_female_cumulative'] ?? 0;
                ?>
                    <div class="mb-3 p-3" style="background: #f8f9fa; border-radius: 6px;">
                        <strong class="d-block mb-2" style="color: #0d6efd;"><?= $group['title'] ?></strong>
                        <div class="row g-2">
                            <div class="col-md-3 cumulative-label">
                                <label class="form-label small">Male (Cumulative)</label>
                                <input type="number" class="form-control form-control-sm number-input age-input cumulative-field"
                                    name="<?= $group['prefix'] ?>_male_cumulative" min="0" value="<?= $male_cumulative ?>"
                                    data-gender="male" data-type="cumulative" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Male (Current)</label>
                                <input type="number" class="form-control form-control-sm number-input age-input"
                                    name="<?= $group['prefix'] ?>_male_current" min="0" value="0" data-gender="male" data-type="current">
                            </div>
                            <div class="col-md-3 cumulative-label">
                                <label class="form-label small">Female (Cumulative)</label>
                                <input type="number" class="form-control form-control-sm number-input age-input cumulative-field"
                                    name="<?= $group['prefix'] ?>_female_cumulative" min="0" value="<?= $female_cumulative ?>"
                                    data-gender="female" data-type="cumulative" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Female (Current)</label>
                                <input type="number" class="form-control form-control-sm number-input age-input"
                                    name="<?= $group['prefix'] ?>_female_current" min="0" value="0" data-gender="female" data-type="current">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Tab 3: Vulnerable Groups -->
            <div class="tab-content" id="tab-vulnerable">
                <div class="section-title">Vulnerable Groups</div>

                <?php $pregnant_cumulative = $cumulative_settings['pregnant_lactating_cumulative'] ?? 0; ?>
                <div class="mb-3 p-3" style="background: #f8f9fa; border-radius: 6px;">
                    <strong class="d-block mb-2" style="color: #0d6efd;">Pregnant/Lactating Mothers</strong>
                    <div class="row g-2">
                        <div class="col-md-6 cumulative-label">
                            <label class="form-label small">Cumulative</label>
                            <input type="number" class="form-control form-control-sm number-input cumulative-field"
                                name="pregnant_lactating_cumulative" min="0" value="<?= $pregnant_cumulative ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Current</label>
                            <input type="number" class="form-control form-control-sm number-input"
                                name="pregnant_lactating_current" min="0" value="0">
                        </div>
                    </div>
                </div>

                <?php
                $vulnerable_groups = [
                    ['title' => 'Child-Headed Family', 'prefix' => 'child_headed_family'],
                    ['title' => 'Single-Headed Family', 'prefix' => 'single_headed_family'],
                    ['title' => 'Solo Parent', 'prefix' => 'solo_parent'],
                    ['title' => 'Persons with Disability (PWDs)', 'prefix' => 'pwd'],
                    ['title' => 'Indigenous People (IPs)', 'prefix' => 'ip'],
                    ['title' => "4P's Beneficiaries", 'prefix' => 'fourps']
                ];

                foreach ($vulnerable_groups as $group):
                    $male_cumulative = $cumulative_settings[$group['prefix'] . '_male_cumulative'] ?? 0;
                    $female_cumulative = $cumulative_settings[$group['prefix'] . '_female_cumulative'] ?? 0;
                ?>
                    <div class="mb-3 p-3" style="background: #f8f9fa; border-radius: 6px;">
                        <strong class="d-block mb-2" style="color: #0d6efd;"><?= $group['title'] ?></strong>
                        <div class="row g-2">
                            <div class="col-md-3 cumulative-label">
                                <label class="form-label small">Male (Cumulative)</label>
                                <input type="number" class="form-control form-control-sm number-input cumulative-field"
                                    name="<?= $group['prefix'] ?>_male_cumulative" min="0" value="<?= $male_cumulative ?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Male (Current)</label>
                                <input type="number" class="form-control form-control-sm number-input"
                                    name="<?= $group['prefix'] ?>_male_current" min="0" value="0">
                            </div>
                            <div class="col-md-3 cumulative-label">
                                <label class="form-label small">Female (Cumulative)</label>
                                <input type="number" class="form-control form-control-sm number-input cumulative-field"
                                    name="<?= $group['prefix'] ?>_female_cumulative" min="0" value="<?= $female_cumulative ?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Female (Current)</label>
                                <input type="number" class="form-control form-control-sm number-input"
                                    name="<?= $group['prefix'] ?>_female_current" min="0" value="0">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="mt-3">
                    <label for="vulnerable_groups_resolution" class="form-label">Vulnerable Groups Resolution Number</label>
                    <input type="text" class="form-control" id="vulnerable_groups_resolution" name="vulnerable_groups_resolution"
                        placeholder="Official resolution number (if applicable)" maxlength="255">
                </div>
            </div>

            <!-- Tab 4: Summary -->
            <div class="tab-content" id="tab-summary">
                <div class="section-title">Total Summary (Auto-calculated)</div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Total Male (Cumulative)</label>
                        <input type="number" class="form-control number-input bg-light"
                            id="total_male_cumulative" name="total_male_cumulative" value="0" readonly>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Total Male (Current)</label>
                        <input type="number" class="form-control number-input bg-light"
                            id="total_male_current" name="total_male_current" value="0" readonly>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Total Female (Cumulative)</label>
                        <input type="number" class="form-control number-input bg-light"
                            id="total_female_cumulative" name="total_female_cumulative" value="0" readonly>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Total Female (Current)</label>
                        <input type="number" class="form-control number-input bg-light"
                            id="total_female_current" name="total_female_current" value="0" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>GRAND TOTAL (Cumulative)</strong></label>
                        <input type="number" class="form-control number-input bg-light"
                            id="total_cumulative" name="total_cumulative" value="0" readonly style="font-weight: bold;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>GRAND TOTAL (Current)</strong></label>
                        <input type="number" class="form-control number-input bg-light"
                            id="total_current" name="total_current" value="0" readonly style="font-weight: bold;">
                    </div>
                </div>

                <div class="section-title mt-4">Remarks</div>
                <div>
                    <label for="remarks" class="form-label">Additional Information</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="4"
                        placeholder="Include relevant information such as areas with highest displacement, conditions in evacuation centers, special concerns, coordination needed, etc."></textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <div>
                <a href="annex17_records.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Cancel
                </a>
                <button type="button" class="btn btn-secondary btn-technical-notes" data-bs-toggle="modal" data-bs-target="#technicalNotesModal">
                    <i class="fas fa-info-circle me-2"></i>View Technical Notes
                </button>
            </div>
            <div>
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-save me-2"></i>Submit Report
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Technical Notes Modal -->
<div class="modal fade" id="technicalNotesModal" tabindex="-1" aria-labelledby="technicalNotesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="technicalNotesModalLabel">Technical Notes - Pre-emptive Evacuation (People)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>General Guide:</strong> This form captures data on people who were pre-emptively evacuated from their homes due to anticipated disaster impacts. Record both cumulative (total from start of incident) and current (latest reporting period) figures.
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="text-primary"><strong>Location Details</strong></h6>
                        <p><strong>Region, Province, City/Municipality, Barangay</strong><br>
                        Indicate the exact geographic location of the evacuated population.</p>

                        <h6 class="text-primary mt-4"><strong>Sex and Age Distribution</strong></h6>
                        <p><strong>Pregnant Women</strong> – Women currently expecting a child.</p>
                        <p><strong>Lactating Mothers</strong> – Women who are breastfeeding.<br>
                        <em>Reference: MC 012, s. 2024 - Guidelines on the Institutionalization of the Family Assistance Card in Emergencies and Disasters (FACED)</em></p>

                        <p><strong>Age Groups:</strong></p>
                        <ul>
                            <li><strong>Infant 0-6 months</strong></li>
                            <li><strong>Toddlers 7 months - 2 years old</strong></li>
                            <li><strong>Preschooler 3-5 years old</strong></li>
                            <li><strong>School Age 6-12 years old</strong></li>
                            <li><strong>Teenage 13-17 years old</strong></li>
                            <li><strong>Adult 18-59 years old</strong></li>
                            <li><strong>Elderly 60 and above</strong></li>
                        </ul>

                        <p><strong>Note:</strong> Enter both Male and Female counts for each age group, with Cumulative and Current figures.</p>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-primary"><strong>Vulnerable Groups Definitions</strong></h6>

                        <p><strong>Child-Headed Family</strong> – A household headed by a minor (below 18 years old) responsible for siblings or dependents, often due to death or absence of parents.<br>
                        <em>Reference: DSWD AO No. 009, s. 2014</em></p>

                        <p><strong>Single-Headed Family</strong> – An adult individual who is the sole breadwinner of the household but without a spouse or children of their own.</p>

                        <p><strong>Solo Parent</strong> – Defined under RA 8972 and RA 11861 as a person solely responsible for raising a child due to death, abandonment, legal separation, or similar circumstances, including spouses of OFWs away for 12+ months.<br>
                        <em>Reference: RA 8972, RA 11861</em></p>

                        <p><strong>Persons with Disability (PWDs)</strong> – Individuals with permanent or temporary impairments.</p>

                        <p><strong>Indigenous Peoples (IPs)</strong> – Members of recognized indigenous cultural communities or tribes.</p>

                        <p><strong>4P's Beneficiaries</strong> – Individuals or families registered under the Pantawid Pamilyang Pilipino Program.</p>

                        <h6 class="text-primary mt-4"><strong>Vulnerable Groups Definitions</strong></h6>
                        <p>Provide the official number of the resolution issued by the local legislative body or relevant authority declaring the SOC (State of Calamity).</p>

                        <h6 class="text-primary mt-4"><strong>Remarks</strong></h6>
                        <p>Include relevant information such as:</p>
                        <ul>
                            <li>Areas with highest displacement</li>
                            <li>Conditions in evacuation centers</li>
                            <li>Special concerns (e.g., medical needs, lack of supplies)</li>
                            <li>Coordination or assistance received or needed</li>
                        </ul>

                        <h6 class="text-primary mt-4"><strong>Cumulative vs Current</strong></h6>
                        <p><strong>Cumulative</strong> – Refers to the total count accumulated from the start of the incident or reporting period up to the present date.</p>
                        <p><strong>Current</strong> – Refers to the latest available figures for a specific date or reporting period (e.g., current day, reporting week).</p>
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
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;

            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(tc => tc.classList.remove('active'));

            this.classList.add('active');
            document.getElementById('tab-' + targetTab).classList.add('active');

            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // Auto-calculation
    const ageInputs = document.querySelectorAll('.age-input');

    ageInputs.forEach(input => {
        input.addEventListener('input', calculateTotals);
    });

    function calculateTotals() {
        let maleCum = 0, maleCur = 0, femaleCum = 0, femaleCur = 0;

        ageInputs.forEach(input => {
            const value = parseInt(input.value) || 0;
            const gender = input.dataset.gender;
            const type = input.dataset.type;

            if (gender === 'male' && type === 'cumulative') maleCum += value;
            else if (gender === 'male' && type === 'current') maleCur += value;
            else if (gender === 'female' && type === 'cumulative') femaleCum += value;
            else if (gender === 'female' && type === 'current') femaleCur += value;
        });

        document.getElementById('total_male_cumulative').value = maleCum;
        document.getElementById('total_male_current').value = maleCur;
        document.getElementById('total_female_cumulative').value = femaleCum;
        document.getElementById('total_female_current').value = femaleCur;
        document.getElementById('total_cumulative').value = maleCum + femaleCum;
        document.getElementById('total_current').value = maleCur + femaleCur;
    }

    // Initial calculation
    calculateTotals();
});

// Toggle cumulative fields visibility
function toggleCumulativeFields() {
    const formContainer = document.querySelector('.form-container');
    const toggleBtn = document.getElementById('toggleCumulativeBtn');
    const toggleBtnText = document.getElementById('toggleBtnText');
    const toggleIcon = toggleBtn.querySelector('i');

    if (formContainer.classList.contains('cumulative-hidden')) {
        // Show cumulative fields
        formContainer.classList.remove('cumulative-hidden');
        toggleBtn.classList.remove('hidden-mode');
        toggleBtnText.textContent = 'Hide Cumulative Fields';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        // Hide cumulative fields
        formContainer.classList.add('cumulative-hidden');
        toggleBtn.classList.add('hidden-mode');
        toggleBtnText.textContent = 'Show Cumulative Fields';
        toggleIcon.className = 'fas fa-eye';
    }
}
</script>

<?php
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content
require_once __DIR__ . '/../includes/sidenav.php';
?>

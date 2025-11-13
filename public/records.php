<?php
/**
 * NDRRMC Centralized Records Management Dashboard - Accordion Version
 * Compact accordion layout for all annex records
 */

// Define page title for the header
define('PAGE_TITLE', 'Records Management - Accordion View');

// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

// AUDIT LOG: Page access
log_audit_action(
    $_SESSION['user_id'],
    'records_accordion_access',
    'Accessed accordion-style records dashboard'
);

// Get user data
$user_data = get_user_location_data($_SESSION['user_id']);
$is_admin = ($_SESSION['user_role'] === 'admin');

// Function to check if table exists
function tableExists($table) {
    global $pdo;
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Function to get record count for each annex
function getRecordCount($table, $user_id, $is_admin) {
    if (!tableExists($table)) {
        return 0;
    }

    try {
        if ($is_admin) {
            $stmt = db_query("SELECT COUNT(*) FROM {$table} WHERE (is_archived = 0 OR is_archived IS NULL)");
        } else {
            $stmt = db_query("SELECT COUNT(*) FROM {$table} WHERE created_by = ? AND (is_archived = 0 OR is_archived IS NULL)", [$user_id]);
        }

        if ($stmt === false) {
            return 0;
        }

        $count = $stmt->fetchColumn();
        return $count !== false ? $count : 0;
    } catch (Exception $e) {
        return 0;
    }
}

// Get record counts for all annexes
$recordCounts = [
    'annex1' => getRecordCount('annex1_related_incidents', $_SESSION['user_id'], $is_admin),
    'annex2' => getRecordCount('annex2_affected_population', $_SESSION['user_id'], $is_admin),
    'annex3' => getRecordCount('annex3_casualties', $_SESSION['user_id'], $is_admin),
    'annex4' => getRecordCount('annex4_damaged_houses', $_SESSION['user_id'], $is_admin),
    'annex5' => getRecordCount('annex5_agriculture_damage', $_SESSION['user_id'], $is_admin),
    'annex6' => getRecordCount('annex6_infrastructure_damage', $_SESSION['user_id'], $is_admin),
    'annex7' => getRecordCount('annex7_other_assets', $_SESSION['user_id'], $is_admin),
    'annex8' => getRecordCount('annex8_road_bridge_status', $_SESSION['user_id'], $is_admin),
    'annex9' => getRecordCount('annex9_power_supply', $_SESSION['user_id'], $is_admin),
    'annex11' => getRecordCount('annex11_communication', $_SESSION['user_id'], $is_admin),
    'annex14' => getRecordCount('annex14_work_suspension', $_SESSION['user_id'], $is_admin),
    'annex15' => getRecordCount('annex15_class_suspension', $_SESSION['user_id'], $is_admin),
    'annex18' => getRecordCount('annex18_evacuation_animals', $_SESSION['user_id'], $is_admin),
    'annex19' => getRecordCount('annex19_families_assisted', $_SESSION['user_id'], $is_admin),
    'annex20' => getRecordCount('annex20_assistance', $_SESSION['user_id'], $is_admin),
    'annex21' => getRecordCount('annex21_lgus_agencies', $_SESSION['user_id'], $is_admin),
];

// Calculate totals
$grandTotal = array_sum($recordCounts);

// Start output buffering
ob_start();
?>
<style>
    body {
        background-color: #f5f5f5;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .records-accordion-container {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        padding: clamp(20px, 4vw, 30px);
        margin: 20px;
    }

    .header-section {
        margin-bottom: clamp(25px, 5vw, 30px);
        padding-bottom: clamp(15px, 3vw, 20px);
        border-bottom: 2px solid #dee2e6;
    }

    .header-section h1 {
        font-size: clamp(1.5rem, 4vw, 1.75rem);
        font-weight: 600;
        color: #212529;
        margin-bottom: clamp(5px, 1.5vw, 8px);
    }

    .header-section h2 {
        font-size: clamp(1.1rem, 3vw, 1.25rem);
        color: #6c757d;
        margin: 0;
        font-weight: 500;
    }

    .view-toggle {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .toggle-btn {
        padding: clamp(8px, 2vw, 10px) clamp(15px, 3.5vw, 18px);
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 500;
        text-decoration: none;
        color: #495057;
        font-size: clamp(0.85rem, 2.3vw, 0.9rem);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .toggle-btn:hover {
        border-color: #0d6efd;
        color: #0d6efd;
        background: #f8f9fa;
    }

    .toggle-btn.active {
        background: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }

    /* Summary Bar - Clean, Flat Design */
    .summary-bar {
        background-color: #0d6efd;
        color: white;
        padding: clamp(15px, 3vw, 20px);
        border-radius: 6px;
        margin-bottom: clamp(20px, 4vw, 25px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #0b5ed7;
    }

    .summary-bar h2 {
        margin: 0;
        font-size: clamp(1.5rem, 4vw, 2rem);
        font-weight: 600;
    }

    .summary-bar p {
        margin: 5px 0 0;
        font-size: clamp(0.85rem, 2.3vw, 0.9rem);
        opacity: 0.95;
    }

    .summary-icon {
        font-size: clamp(2rem, 5vw, 3rem);
        opacity: 0.8;
    }

    /* Accordion Styles */
    .accordion {
        --bs-accordion-border-color: #dee2e6;
        --bs-accordion-btn-focus-box-shadow: none;
    }

    .accordion-item {
        border: 1px solid #dee2e6;
        margin-bottom: 10px;
        border-radius: 6px !important;
        overflow: hidden;
        transition: all 0.2s;
    }

    .accordion-item:hover {
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        border-color: #adb5bd;
    }

    .accordion-button {
        padding: 18px 20px;
        font-weight: 600;
        font-size: 1rem;
        background-color: #f8f9fa;
        border: none;
    }

    .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #0d6efd;
        box-shadow: none;
    }

    .accordion-button::after {
        margin-left: auto;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: #dee2e6;
    }

    .category-icon {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.3rem;
        border: 1px solid rgba(0,0,0,0.1);
    }

    .category-incident .category-icon { background-color: #dc3545; color: white; }
    .category-damage .category-icon { background-color: #fd7e14; color: white; }
    .category-status .category-icon { background-color: #0dcaf0; color: white; }
    .category-evacuation .category-icon { background-color: #6f42c1; color: white; }
    .category-assistance .category-icon { background-color: #198754; color: white; }

    .category-badge {
        background-color: #6c757d;
        color: white;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-left: 10px;
    }

    .accordion-body {
        padding: 0;
        background-color: #fff;
    }

    /* Annex List */
    .annex-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .annex-item {
        border-bottom: 1px solid #f0f0f0;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background-color 0.2s;
    }

    .annex-item:last-child {
        border-bottom: none;
    }

    .annex-item:hover {
        background-color: #f8f9fa;
    }

    .annex-info {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .annex-number {
        width: 45px;
        height: 45px;
        border-radius: 6px;
        background-color: #0d6efd;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        border: 1px solid #0b5ed7;
    }

    .annex-details h5 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: #212529;
    }

    .annex-details p {
        margin: 3px 0 0;
        font-size: 0.85rem;
        color: #6c757d;
    }

    .annex-stats {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .stat-badge {
        background-color: #e7f1ff;
        color: #0d6efd;
        padding: 8px 15px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .annex-actions {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: none;
        cursor: pointer;
    }

    .btn-view {
        background-color: #0d6efd;
        color: white;
        border: 1px solid #0b5ed7;
    }

    .btn-view:hover {
        background-color: #0b5ed7;
        color: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }

    .btn-add {
        background-color: #198754;
        color: white;
        border: 1px solid #157347;
    }

    .btn-add:hover {
        background-color: #157347;
        color: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }

    @media (max-width: 768px) {
        .annex-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .annex-stats {
            width: 100%;
            justify-content: space-between;
        }

        .annex-actions {
            width: 100%;
        }

        .btn-action {
            flex: 1;
            justify-content: center;
        }

        .summary-bar {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
    }
</style>

<div class="records-accordion-container">
    <!-- Header -->
    <div class="header-section">
        <h1>
            <i class="bi bi-list-nested"></i>
            Records Management
            <span style="font-size: 0.5em; color: #6c757d; font-weight: 400;">(Accordion View)</span>
        </h1>
        <p>Compact view of all NDRRMC reporting records organized by category</p>
        <?php if ($is_admin): ?>
            <div class="alert alert-info mt-3 mb-0">
                <i class="bi bi-shield-check"></i> <strong>Admin Mode:</strong> Viewing all records from all barangays
            </div>
        <?php endif; ?>
    </div>

    <?php show_flash(); ?>

    <!-- View Toggle -->
    <div class="view-toggle">
        <a href="records.php" class="toggle-btn">
            <i class="bi bi-grid-3x3"></i> Tab View
        </a>
        <a href="records_accordion.php" class="toggle-btn active">
            <i class="bi bi-list-nested"></i> Accordion View
        </a>
    </div>

    <!-- Summary Bar -->
    <div class="summary-bar">
        <div>
            <h2><?php echo number_format($grandTotal); ?></h2>
            <p>Total Records Across All Categories</p>
        </div>
        <i class="bi bi-folder2-open" style="font-size: 3rem; opacity: 0.7;"></i>
    </div>

    <!-- Accordion -->
    <div class="accordion" id="recordsAccordion">

        <!-- Incident Reports -->
        <div class="accordion-item category-incident">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIncident">
                    <span class="category-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </span>
                    <span>Incident Reports</span>
                    <span class="category-badge"><?php echo $recordCounts['annex1'] + $recordCounts['annex2'] + $recordCounts['annex3']; ?> Records</span>
                </button>
            </h2>
            <div id="collapseIncident" class="accordion-collapse collapse show" data-bs-parent="#recordsAccordion">
                <div class="accordion-body">
                    <ul class="annex-list">
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A1</div>
                                <div class="annex-details">
                                    <h5>Related Incident</h5>
                                    <p>Disaster incident information and classification</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex1']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex1_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex1.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A2</div>
                                <div class="annex-details">
                                    <h5>Affected Population</h5>
                                    <p>Population affected by the disaster</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex2']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex2_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex2.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A3</div>
                                <div class="annex-details">
                                    <h5>Casualties</h5>
                                    <p>Casualties from the disaster incident</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex3']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex3_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex3.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Damage Assessment -->
        <div class="accordion-item category-damage">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDamage">
                    <span class="category-icon">
                        <i class="bi bi-house"></i>
                    </span>
                    <span>Damage Assessment</span>
                    <span class="category-badge"><?php echo $recordCounts['annex4'] + $recordCounts['annex5'] + $recordCounts['annex6'] + $recordCounts['annex7']; ?> Records</span>
                </button>
            </h2>
            <div id="collapseDamage" class="accordion-collapse collapse" data-bs-parent="#recordsAccordion">
                <div class="accordion-body">
                    <ul class="annex-list">
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A4</div>
                                <div class="annex-details">
                                    <h5>Damaged Houses</h5>
                                    <p>Houses damaged or destroyed</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex4']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex4_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex4.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A5</div>
                                <div class="annex-details">
                                    <h5>Agriculture</h5>
                                    <p>Damage and losses to agriculture</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex5']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex5_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex5.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A6</div>
                                <div class="annex-details">
                                    <h5>Infrastructure</h5>
                                    <p>Damage to infrastructure facilities</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex6']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex6_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex6.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A7</div>
                                <div class="annex-details">
                                    <h5>Other Assets</h5>
                                    <p>Damage to other properties and assets</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex7']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex7_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex7.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Status Reports -->
        <div class="accordion-item category-status">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStatus">
                    <span class="category-icon">
                        <i class="bi bi-clipboard-check"></i>
                    </span>
                    <span>Status Reports</span>
                    <span class="category-badge"><?php echo $recordCounts['annex8'] + $recordCounts['annex9'] + $recordCounts['annex11'] + $recordCounts['annex14'] + $recordCounts['annex15']; ?> Records</span>
                </button>
            </h2>
            <div id="collapseStatus" class="accordion-collapse collapse" data-bs-parent="#recordsAccordion">
                <div class="accordion-body">
                    <ul class="annex-list">
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A8</div>
                                <div class="annex-details">
                                    <h5>Roads & Bridges</h5>
                                    <p>Status of roads and bridges</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex8']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex8_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex8.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A9</div>
                                <div class="annex-details">
                                    <h5>Power Supply</h5>
                                    <p>Electric power supply status</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex9']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex9_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex9.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A11</div>
                                <div class="annex-details">
                                    <h5>Communication</h5>
                                    <p>Status of communication lines</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex11']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex11_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex11.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A14</div>
                                <div class="annex-details">
                                    <h5>Work Suspension</h5>
                                    <p>Work suspension due to disaster</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex14']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex14_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex14.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A15</div>
                                <div class="annex-details">
                                    <h5>Class Suspension</h5>
                                    <p>Class suspension in schools</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex15']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex15_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex15.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Evacuation Reports -->
        <div class="accordion-item category-evacuation">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEvacuation">
                    <span class="category-icon">
                        <i class="bi bi-people-fill"></i>
                    </span>
                    <span>Evacuation Reports</span>
                    <span class="category-badge"><?php echo $recordCounts['annex18']; ?> Records</span>
                </button>
            </h2>
            <div id="collapseEvacuation" class="accordion-collapse collapse" data-bs-parent="#recordsAccordion">
                <div class="accordion-body">
                    <ul class="annex-list">
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A18</div>
                                <div class="annex-details">
                                    <h5>Evacuation (Animals)</h5>
                                    <p>Pre-emptive evacuation of animals</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex18']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex18_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex18.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Assistance Reports -->
        <div class="accordion-item category-assistance">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAssistance">
                    <span class="category-icon">
                        <i class="bi bi-heart"></i>
                    </span>
                    <span>Assistance Reports</span>
                    <span class="category-badge"><?php echo $recordCounts['annex19'] + $recordCounts['annex20'] + $recordCounts['annex21']; ?> Records</span>
                </button>
            </h2>
            <div id="collapseAssistance" class="accordion-collapse collapse" data-bs-parent="#recordsAccordion">
                <div class="accordion-body">
                    <ul class="annex-list">
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A19</div>
                                <div class="annex-details">
                                    <h5>Families Assisted</h5>
                                    <p>Assistance provided to families</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex19']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex19_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex19.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A20</div>
                                <div class="annex-details">
                                    <h5>Assistance Provided to Families</h5>
                                    <p>Detailed assistance by cluster and type</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex20']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex20_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex20.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="annex-item">
                            <div class="annex-info">
                                <div class="annex-number">A21</div>
                                <div class="annex-details">
                                    <h5>To LGUs/Agencies</h5>
                                    <p>Assistance to local government units and agencies</p>
                                </div>
                            </div>
                            <div class="annex-stats">
                                <span class="stat-badge"><?php echo $recordCounts['annex21']; ?> records</span>
                                <div class="annex-actions">
                                    <a href="annex21_records.php" class="btn-action btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="annex21.php" class="btn-action btn-add">
                                        <i class="bi bi-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Smooth accordion animations
document.addEventListener('DOMContentLoaded', function() {
    const accordionButtons = document.querySelectorAll('.accordion-button');

    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Animate items when accordion opens
            const targetId = this.getAttribute('data-bs-target');
            const targetAccordion = document.querySelector(targetId);

            if (!this.classList.contains('collapsed')) {
                setTimeout(() => {
                    const items = targetAccordion.querySelectorAll('.annex-item');
                    items.forEach((item, index) => {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(-20px)';

                        setTimeout(() => {
                            item.style.transition = 'all 0.3s ease';
                            item.style.opacity = '1';
                            item.style.transform = 'translateX(0)';
                        }, index * 50);
                    });
                }, 100);
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require_once '../includes/sidenav.php';
?>

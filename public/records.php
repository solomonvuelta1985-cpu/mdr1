<?php
/**
 * NDRRMC Centralized Records Management Dashboard
 * All annex records in one organized location
 */

// Define page title for the header
define('PAGE_TITLE', 'Records Management Dashboard');

// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

// AUDIT LOG: Page access
log_audit_action(
    $_SESSION['user_id'],
    'records_dashboard_access',
    'Accessed centralized records management dashboard'
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
    // First check if table exists
    if (!tableExists($table)) {
        return 0;
    }

    try {
        if ($is_admin) {
            $stmt = db_query("SELECT COUNT(*) FROM {$table} WHERE (is_archived = 0 OR is_archived IS NULL)");
        } else {
            $stmt = db_query("SELECT COUNT(*) FROM {$table} WHERE created_by = ? AND (is_archived = 0 OR is_archived IS NULL)", [$user_id]);
        }

        // Check if query was successful
        if ($stmt === false) {
            return 0;
        }

        $count = $stmt->fetchColumn();
        return $count !== false ? $count : 0;
    } catch (Exception $e) {
        // Table might exist but have different structure
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
    'annex8' => getRecordCount('annex8_roads_bridges', $_SESSION['user_id'], $is_admin),
    'annex9' => getRecordCount('annex9_power_supply', $_SESSION['user_id'], $is_admin),
    'annex11' => getRecordCount('annex11_communication', $_SESSION['user_id'], $is_admin),
    'annex14' => getRecordCount('annex14_work_suspension', $_SESSION['user_id'], $is_admin),
    'annex15' => getRecordCount('annex15_class_suspension', $_SESSION['user_id'], $is_admin),
    'annex18' => getRecordCount('annex18_evacuation_animals', $_SESSION['user_id'], $is_admin),
    'annex19' => getRecordCount('annex19_families_assisted', $_SESSION['user_id'], $is_admin),
    'annex21' => getRecordCount('annex21_lgus_agencies', $_SESSION['user_id'], $is_admin),
];

// Calculate totals per category
$incidentTotal = $recordCounts['annex1'] + $recordCounts['annex2'] + $recordCounts['annex3'];
$damageTotal = $recordCounts['annex4'] + $recordCounts['annex5'] + $recordCounts['annex6'] + $recordCounts['annex7'];
$statusTotal = $recordCounts['annex8'] + $recordCounts['annex9'] + $recordCounts['annex11'] + $recordCounts['annex14'] + $recordCounts['annex15'];
$evacuationTotal = $recordCounts['annex18'];
$assistanceTotal = $recordCounts['annex19'] + $recordCounts['annex21'];
$grandTotal = array_sum($recordCounts);

// Start output buffering
ob_start();
?>
<style>
    .records-dashboard {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        padding: clamp(20px, 4vw, 30px);
        margin: 20px;
    }

    .header-section {
        margin-bottom: clamp(25px, 5vw, 30px);
        padding-bottom: clamp(15px, 3vw, 20px);
        border-bottom: 2px solid #e0e0e0;
    }

    .header-section h1 {
        font-size: clamp(1.5rem, 4vw, 2rem);
        font-weight: 600;
        color: #212529;
        margin-bottom: clamp(5px, 1.5vw, 10px);
    }

    .header-section p {
        font-size: clamp(0.9rem, 2.5vw, 1rem);
        color: #6c757d;
        margin: 0;
    }

    /* Summary Stats */
    .summary-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }

    .summary-card:hover {
        transform: translateY(-5px);
    }

    .summary-card h3 {
        font-size: 2.5rem;
        margin: 10px 0;
        font-weight: 700;
    }

    .summary-card p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .summary-card i {
        font-size: 2rem;
        opacity: 0.8;
    }

    /* Tabs */
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 25px;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
        padding: 12px 20px;
        margin-right: 5px;
        border-radius: 8px 8px 0 0;
        transition: all 0.2s;
    }

    .nav-tabs .nav-link:hover {
        color: #0d6efd;
        background-color: #f8f9fa;
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd;
        background-color: #e7f1ff;
        border-bottom: 3px solid #0d6efd;
        font-weight: 600;
    }

    /* Annex Cards Grid */
    .annex-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .annex-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .annex-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #0d6efd;
    }

    .annex-card:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        transform: translateY(-5px);
    }

    .annex-card-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .annex-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .annex-card-title {
        flex: 1;
    }

    .annex-card-title h5 {
        margin: 0;
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
    }

    .annex-card-title h4 {
        margin: 5px 0 0;
        font-size: 1.1rem;
        color: #212529;
        font-weight: 600;
    }

    .annex-stats {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        border-top: 1px solid #e9ecef;
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 15px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-item h3 {
        font-size: 1.8rem;
        margin: 0;
        color: #0d6efd;
        font-weight: 700;
    }

    .stat-item p {
        margin: 5px 0 0;
        font-size: 0.8rem;
        color: #6c757d;
    }

    .annex-actions {
        display: flex;
        gap: 8px;
    }

    .btn-annex {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .btn-view-records {
        background-color: #0d6efd;
        color: white;
    }

    .btn-view-records:hover {
        background-color: #0b5ed7;
        color: white;
    }

    .btn-add-new {
        background-color: #198754;
        color: white;
    }

    .btn-add-new:hover {
        background-color: #157347;
        color: white;
    }

    /* Color variations for different categories */
    .category-incident .annex-card::before { background: #dc3545; }
    .category-incident .annex-icon { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }

    .category-damage .annex-card::before { background: #fd7e14; }
    .category-damage .annex-icon { background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%); }

    .category-status .annex-card::before { background: #0dcaf0; }
    .category-status .annex-icon { background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); }

    .category-evacuation .annex-card::before { background: #6f42c1; }
    .category-evacuation .annex-icon { background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); }

    .category-assistance .annex-card::before { background: #198754; }
    .category-assistance .annex-icon { background: linear-gradient(135deg, #198754 0%, #146c43 100%); }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    @media (max-width: 768px) {
        .summary-stats {
            grid-template-columns: 1fr;
        }

        .annex-cards {
            grid-template-columns: 1fr;
        }

        .nav-tabs .nav-link {
            font-size: 0.85rem;
            padding: 10px 15px;
        }
    }
</style>

<div class="records-dashboard">
    <!-- Header -->
    <div class="header-section">
        <h1><i class="bi bi-folder2-open"></i> Records Management Dashboard</h1>
        <p>Centralized access to all NDRRMC reporting records</p>
        <?php if ($is_admin): ?>
            <div class="alert alert-info mt-3 mb-0">
                <i class="bi bi-shield-check"></i> <strong>Admin Mode:</strong> Viewing all records from all barangays
            </div>
        <?php endif; ?>
    </div>

    <?php show_flash(); ?>

    <!-- Summary Stats -->
    <div class="summary-stats">
        <div class="summary-card">
            <i class="bi bi-files"></i>
            <h3><?php echo number_format($grandTotal); ?></h3>
            <p>Total Records</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs" id="recordsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="incident-tab" data-bs-toggle="tab" data-bs-target="#incident" type="button" role="tab">
                <i class="bi bi-exclamation-triangle"></i> Incident Reports
                <span class="badge bg-danger ms-2"><?php echo $incidentTotal; ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="damage-tab" data-bs-toggle="tab" data-bs-target="#damage" type="button" role="tab">
                <i class="bi bi-house"></i> Damage Assessment
                <span class="badge bg-warning ms-2"><?php echo $damageTotal; ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="status-tab" data-bs-toggle="tab" data-bs-target="#status" type="button" role="tab">
                <i class="bi bi-clipboard-check"></i> Status Reports
                <span class="badge bg-info ms-2"><?php echo $statusTotal; ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="evacuation-tab" data-bs-toggle="tab" data-bs-target="#evacuation" type="button" role="tab">
                <i class="bi bi-people-fill"></i> Evacuation
                <span class="badge bg-secondary ms-2"><?php echo $evacuationTotal; ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="assistance-tab" data-bs-toggle="tab" data-bs-target="#assistance" type="button" role="tab">
                <i class="bi bi-heart"></i> Assistance
                <span class="badge bg-success ms-2"><?php echo $assistanceTotal; ?></span>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="recordsTabContent">

        <!-- Incident Reports Tab -->
        <div class="tab-pane fade show active" id="incident" role="tabpanel">
            <div class="annex-cards category-incident">
                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-1-circle"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 1</h5>
                            <h4>Related Incident</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex1']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex1_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex1.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>

                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-2-circle"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 2</h5>
                            <h4>Affected Population</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex2']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex2_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex2.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>

                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-3-circle"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 3</h5>
                            <h4>Casualties</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex3']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex3_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex3.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Damage Assessment Tab -->
        <div class="tab-pane fade" id="damage" role="tabpanel">
            <div class="annex-cards category-damage">
                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-4-circle"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 4</h5>
                            <h4>Damaged Houses</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex4']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex4_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex4.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>

                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-5-circle"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 5</h5>
                            <h4>Agriculture</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex5']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex5_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex5.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>

                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-6-circle"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 6</h5>
                            <h4>Infrastructure</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex6']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex6_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex6.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>

                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-7-circle"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 7</h5>
                            <h4>Other Assets</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex7']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex7_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex7.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Reports Tab -->
        <div class="tab-pane fade" id="status" role="tabpanel">
            <div class="annex-cards category-status">
                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-8-circle"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 8</h5>
                            <h4>Roads & Bridges</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex8']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex8_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex8.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>

                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-9-circle"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 9</h5>
                            <h4>Power Supply</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex9']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex9_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex9.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>

                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 11</h5>
                            <h4>Communication</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex11']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex11_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex11.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>

                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-briefcase"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 14</h5>
                            <h4>Work Suspension</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex14']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex14_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex14.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>

                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-backpack"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 15</h5>
                            <h4>Class Suspension</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex15']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex15_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex15.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evacuation Reports Tab -->
        <div class="tab-pane fade" id="evacuation" role="tabpanel">
            <div class="annex-cards category-evacuation">
                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-piggy-bank"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 18</h5>
                            <h4>Evacuation (Animals)</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex18']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex18_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex18.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assistance Reports Tab -->
        <div class="tab-pane fade" id="assistance" role="tabpanel">
            <div class="annex-cards category-assistance">
                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-house-heart"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 19</h5>
                            <h4>Families Assisted</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex19']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex19_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex19.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>

                <div class="annex-card">
                    <div class="annex-card-header">
                        <div class="annex-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="annex-card-title">
                            <h5>Annex 21</h5>
                            <h4>To LGUs/Agencies</h4>
                        </div>
                    </div>
                    <div class="annex-stats">
                        <div class="stat-item">
                            <h3><?php echo $recordCounts['annex21']; ?></h3>
                            <p>Records</p>
                        </div>
                    </div>
                    <div class="annex-actions">
                        <a href="annex21_records.php" class="btn-annex btn-view-records">
                            <i class="bi bi-eye"></i> View Records
                        </a>
                        <a href="annex21.php" class="btn-annex btn-add-new">
                            <i class="bi bi-plus-lg"></i> Add New
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Smooth tab transitions
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');

    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function(event) {
            // Animate cards when tab is shown
            const targetPane = document.querySelector(event.target.getAttribute('data-bs-target'));
            const cards = targetPane.querySelectorAll('.annex-card');

            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require_once '../includes/sidenav.php';
?>

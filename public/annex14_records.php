<?php
// Define page title for the header
define('PAGE_TITLE', 'Annex 14 - Suspension of Work Records');

// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in, redirect to login if not
require_login();

// RATE LIMITING: Check if user is not flooding the page with requests
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex14_records_access', 100, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex14 records page access rate limit exceeded');
    set_flash('Too many requests. Please try again later.', 'error');
    header('Location: dashboard.php');
    exit;
}

// AUDIT LOG: Page access
log_audit_action(
    $_SESSION['user_id'],
    'annex14_records_access',
    'Accessed Annex 14 suspension of work records page'
);

// SECURITY LOG: Page access
log_security_event(
    $_SESSION['user_id'],
    'page_access',
    'Accessed Annex 14 records page'
);

// Database connection
$pdo = $pdo;

// Get current user's data for filtering
$user_location = get_user_location_data($_SESSION['user_id']);
$is_admin = ($_SESSION['user_role'] === 'admin');

// Ensure is_archived column exists
try {
    $column_check = $pdo->query("SHOW COLUMNS FROM annex14_suspension_work LIKE 'is_archived'");
    if ($column_check->rowCount() == 0) {
        // SECURITY: Do NOT modify database schema from web application
        error_log("CRITICAL: Missing is_archived column. Run migrations manually");
        set_flash('Database schema error. Contact administrator.', 'error');
        header('Location: dashboard.php');
        exit;
    }
    if (false) { // DISABLED FOR SECURITY
    if ($column_check->rowCount() == 0) {
        // SECURITY FIX - DO NOT AUTO-ALTER: // $pdo->exec("ALTER TABLE annex14_suspension_work ADD COLUMN is_archived TINYINT(1) DEFAULT 0");
    }
} catch (PDOException $e) {
    error_log("Failed to add is_archived column: " . $e->getMessage());
}

// Handle record actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Verification
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        set_flash('Security token validation failed', 'error');
        log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex14 records CSRF token mismatch');
        header('Location: annex14_records.php');
        exit;
    }
    
    // RATE LIMITING: Check action-specific rate limits
    if (!check_rate_limit($user_id, 'annex14_post_action', 30, 3600)) {
        log_security_event($user_id, 'rate_limit_exceeded', 'Annex14 POST action rate limit exceeded');
        set_flash('Too many actions. Please try again later.', 'error');
        header('Location: annex14_records.php');
        exit;
    }
    
    // Handle delete action
    if (isset($_POST['delete_id'])) {
        $delete_id = filter_var($_POST['delete_id'], FILTER_VALIDATE_INT);
        
        if (!$delete_id) {
            set_flash('Invalid record ID', 'error');
            log_security_event($_SESSION['user_id'], 'invalid_input', 'Invalid delete_id for Annex14');
            header('Location: annex14_records.php');
            exit;
        }
        
        // RATE LIMITING: Specific check for delete operations
        if (!check_rate_limit($user_id, 'annex14_delete', 10, 3600)) {
            log_security_event($user_id, 'rate_limit_exceeded', 'Annex14 delete rate limit exceeded');
            set_flash('Too many delete attempts. Please try again later.', 'error');
            header('Location: annex14_records.php');
            exit;
        }
        
        // Check if user owns the record (unless admin)
        if (!$is_admin) {
            $check_stmt = db_query("SELECT * FROM annex14_suspension_work WHERE id = ? AND created_by = ?", [$delete_id, $_SESSION['user_id']]);
            $record = $check_stmt->fetch();
            
            if (!$record) {
                set_flash('Record not found or access denied', 'error');
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to delete Annex14 record #{$delete_id} without permission");
                header('Location: annex14_records.php');
                exit;
            }
        } else {
            $check_stmt = db_query("SELECT * FROM annex14_suspension_work WHERE id = ?", [$delete_id]);
            $record = $check_stmt->fetch();
        }
        
        if ($record) {
            $stmt = db_query("DELETE FROM annex14_suspension_work WHERE id = ?", [$delete_id]);
            
            if ($stmt && $stmt->rowCount() > 0) {
                // AUDIT LOG: Record deletion
                log_audit_action(
                    $_SESSION['user_id'],
                    'annex14_delete',
                    "Permanently deleted record #{$delete_id} for {$record['barangay']}: {$record['type']}"
                );
                
                // SECURITY LOG: Successful deletion
                log_security_event(
                    $_SESSION['user_id'],
                    'annex14_delete_success',
                    "Successfully deleted Annex14 record #{$delete_id}"
                );
                
                set_flash('Record deleted successfully', 'success');
            } else {
                set_flash('Failed to delete record', 'error');
                log_security_event($_SESSION['user_id'], 'delete_failure', "Failed to delete Annex14 record #{$delete_id}");
            }
        } else {
            set_flash('Record not found', 'error');
        }
        header('Location: annex14_records.php');
        exit;
    }
    
    // Handle archive action
    if (isset($_POST['archive_id'])) {
        $archive_id = filter_var($_POST['archive_id'], FILTER_VALIDATE_INT);
        
        if (!$archive_id) {
            set_flash('Invalid record ID', 'error');
            log_security_event($_SESSION['user_id'], 'invalid_input', 'Invalid archive_id for Annex14');
            header('Location: annex14_records.php');
            exit;
        }
        
        // RATE LIMITING: Specific check for archive operations
        if (!check_rate_limit($user_id, 'annex14_archive', 20, 3600)) {
            log_security_event($user_id, 'rate_limit_exceeded', 'Annex14 archive rate limit exceeded');
            set_flash('Too many archive attempts. Please try again later.', 'error');
            header('Location: annex14_records.php');
            exit;
        }
        
        // Check if user owns the record (unless admin)
        if (!$is_admin) {
            $check_stmt = db_query("SELECT * FROM annex14_suspension_work WHERE id = ? AND created_by = ?", [$archive_id, $_SESSION['user_id']]);
            $record = $check_stmt->fetch();
            
            if (!$record) {
                set_flash('Record not found or access denied', 'error');
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to archive Annex14 record #{$archive_id} without permission");
                header('Location: annex14_records.php');
                exit;
            }
        } else {
            $check_stmt = db_query("SELECT * FROM annex14_suspension_work WHERE id = ?", [$archive_id]);
            $record = $check_stmt->fetch();
        }
        
        if ($record) {
            // Archive the record
            $stmt = db_query("UPDATE annex14_suspension_work SET is_archived = 1 WHERE id = ?", [$archive_id]);
            
            if ($stmt && $stmt->rowCount() > 0) {
                // AUDIT LOG: Record archival
                log_audit_action(
                    $_SESSION['user_id'],
                    'annex14_archive',
                    "Archived record #{$archive_id} for {$record['barangay']}: {$record['type']}"
                );
                
                // SECURITY LOG: Successful archival
                log_security_event(
                    $_SESSION['user_id'],
                    'annex14_archive_success',
                    "Successfully archived Annex14 record #{$archive_id}"
                );
                
                set_flash('Record archived successfully', 'success');
            } else {
                set_flash('Failed to archive record', 'error');
                log_security_event($_SESSION['user_id'], 'archive_failure', "Failed to archive Annex14 record #{$archive_id}");
            }
        } else {
            set_flash('Record not found', 'error');
        }
        header('Location: annex14_records.php');
        exit;
    }
}

// Generate CSRF token
$csrf_token = generate_token();

// Fetch records based on user role
if ($is_admin) {
    // Admin can see all non-archived records
    $stmt = db_query("
        SELECT a.*, u.full_name, u.barangay as user_barangay 
        FROM annex14_suspension_work a 
        LEFT JOIN users u ON a.created_by = u.id 
        WHERE (a.is_archived = 0 OR a.is_archived IS NULL)
        ORDER BY a.created_at DESC
    ");
} else {
    // Regular users can only see their own non-archived records
    $stmt = db_query("
        SELECT * FROM annex14_suspension_work 
        WHERE created_by = ? AND (is_archived = 0 OR is_archived IS NULL)
        ORDER BY created_at DESC
    ", [$_SESSION['user_id']]);
}

$records = $stmt->fetchAll();

// Calculate statistics
$total_records = count($records);
$government_count = 0;
$private_count = 0;
$all_count = 0;
$ongoing_count = 0;

foreach ($records as $record) {
    switch ($record['type']) {
        case 'Government':
            $government_count++;
            break;
        case 'Private':
            $private_count++;
            break;
        case 'All':
            $all_count++;
            break;
    }
    
    if (empty($record['resumption_date'])) {
        $ongoing_count++;
    }
}

// Start output buffering
ob_start();
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f5f5f5;
        padding: clamp(15px, 3vw, 20px);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .records-container {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        padding: clamp(20px, 4vw, 30px);
        margin: 0 auto;
        width: 100%;
        max-width: 98vw;
    }
    .header-section {
        margin-bottom: clamp(25px, 5vw, 30px);
        padding-bottom: clamp(15px, 3vw, 20px);
        border-bottom: 2px solid #e0e0e0;
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
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: clamp(12px, 2.5vw, 15px);
        margin-bottom: clamp(25px, 5vw, 30px);
    }
    .stat-card {
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        padding: clamp(15px, 3.5vw, 20px);
        border-radius: 6px;
        border-left: 4px solid #0d6efd;
        transition: box-shadow 0.2s;
    }
    .stat-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .stat-card h3 {
        font-size: clamp(1.5rem, 4vw, 1.75rem);
        margin-bottom: clamp(5px, 1.5vw, 8px);
        font-weight: 600;
        color: #212529;
    }
    .stat-card p {
        margin: 0;
        color: #6c757d;
        font-size: clamp(0.85rem, 2.3vw, 0.9rem);
    }
    .stat-card:nth-child(1) { border-left-color: #0d6efd; }
    .stat-card:nth-child(2) { border-left-color: #0dcaf0; }
    .stat-card:nth-child(3) { border-left-color: #6f42c1; }
    .stat-card:nth-child(4) { border-left-color: #198754; }
    .stat-card:nth-child(5) { border-left-color: #fd7e14; }
    .action-section {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: clamp(10px, 2.5vw, 15px);
        margin-bottom: clamp(20px, 4vw, 25px);
        padding: clamp(15px, 3vw, 20px);
        background-color: #f8f9fa;
        border-radius: 6px;
    }
    .search-box {
        flex: 1;
        min-width: 250px;
    }
    .search-box input {
        width: 100%;
        padding: clamp(8px, 2vw, 10px);
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: clamp(0.85rem, 2.3vw, 0.9rem);
    }
    .search-box input:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
    }
    .action-buttons {
        display: flex;
        gap: clamp(8px, 2vw, 10px);
        flex-wrap: wrap;
    }
    .btn-custom {
        padding: clamp(8px, 2vw, 10px) clamp(15px, 3.5vw, 18px);
        font-size: clamp(0.85rem, 2.3vw, 0.9rem);
        border-radius: 4px;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: clamp(5px, 1.5vw, 8px);
        white-space: nowrap;
        font-weight: 500;
    }
    .btn-primary-custom {
        background-color: #0d6efd;
        color: white;
        border: 1px solid #0d6efd;
    }
    .btn-primary-custom:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    .btn-secondary-custom {
        background-color: #6c757d;
        color: white;
        border: 1px solid #6c757d;
    }
    .btn-secondary-custom:hover {
        background-color: #5c636a;
        border-color: #565e64;
    }
    .btn-success-custom {
        background-color: #198754;
        color: white;
        border: 1px solid #198754;
    }
    .btn-success-custom:hover {
        background-color: #157347;
        border-color: #146c43;
    }
    .table-container {
        overflow-x: auto;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        background-color: white;
    }
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
        font-size: clamp(0.8rem, 2.2vw, 0.875rem);
    }
    .custom-table thead {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    .custom-table th {
        padding: clamp(12px, 2.5vw, 15px);
        text-align: left;
        font-weight: 600;
        white-space: nowrap;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    .custom-table td {
        padding: clamp(10px, 2.5vw, 12px);
        border-bottom: 1px solid #dee2e6;
        color: #212529;
    }
    .custom-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }
    .badge {
        padding: clamp(4px, 1.2vw, 5px) clamp(8px, 2vw, 10px);
        border-radius: 4px;
        font-size: clamp(0.7rem, 2vw, 0.75rem);
        font-weight: 500;
        white-space: nowrap;
    }
    .badge-government { 
        background-color: #cfe2ff; 
        color: #084298;
        border: 1px solid #b6d4fe;
    }
    .badge-private { 
        background-color: #e2d9f3; 
        color: #422874;
        border: 1px solid #d4c4ed;
    }
    .badge-all { 
        background-color: #d1e7dd; 
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    .badge-ongoing { 
        background-color: #fff3cd; 
        color: #664d03;
        border: 1px solid #ffecb5;
    }
    .badge-completed { 
        background-color: #d1e7dd; 
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    .btn-table {
        padding: clamp(5px, 1.5vw, 6px) clamp(10px, 2.5vw, 12px);
        font-size: clamp(0.75rem, 2vw, 0.8rem);
        border: 1px solid;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        margin: 2px;
        font-weight: 500;
    }
    .btn-view {
        background-color: #0dcaf0;
        color: #000;
        border-color: #0dcaf0;
    }
    .btn-view:hover {
        background-color: #31d2f2;
        border-color: #25cff2;
    }
    .btn-edit {
        background-color: #ffc107;
        color: #000;
        border-color: #ffc107;
    }
    .btn-edit:hover {
        background-color: #ffca2c;
        border-color: #ffc720;
    }
    .btn-archive {
        background-color: #6c757d;
        color: white;
        border-color: #6c757d;
    }
    .btn-archive:hover {
        background-color: #5c636a;
        border-color: #565e64;
    }
    .btn-delete {
        background-color: #dc3545;
        color: white;
        border-color: #dc3545;
    }
    .btn-delete:hover {
        background-color: #bb2d3b;
        border-color: #b02a37;
    }
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .skeleton-loader {
        display: none;
    }
    .loading .skeleton-loader {
        display: table-row-group;
    }
    .loading .table-content {
        display: none;
    }
    .skeleton-card {
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        padding: clamp(15px, 3.5vw, 20px);
        border-radius: 6px;
        border-left: 4px solid #e0e0e0;
    }
    .skeleton-title {
        height: clamp(1.5rem, 4vw, 1.75rem);
        margin-bottom: clamp(5px, 1.5vw, 8px);
        border-radius: 4px;
    }
    .skeleton-text {
        height: clamp(0.85rem, 2.3vw, 0.9rem);
        border-radius: 4px;
        margin-bottom: 5px;
    }
    .skeleton-text.short {
        width: 60%;
    }
    .skeleton-icon {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-bottom: 8px;
    }
    .loading .stats-cards .stat-card {
        display: none;
    }
    .loading .stats-cards .skeleton-card {
        display: block;
    }
    .stats-cards .skeleton-card {
        display: none;
    }
    .skeleton-row {
        display: table-row;
    }
    .skeleton-cell {
        display: table-cell;
        padding: clamp(10px, 2.5vw, 12px);
        border-bottom: 1px solid #dee2e6;
    }
    .skeleton-bar {
        height: 12px;
        border-radius: 4px;
        margin: 4px 0;
    }
    .skeleton-bar.short {
        width: 60%;
    }
    .skeleton-bar.medium {
        width: 80%;
    }
    .skeleton-bar.long {
        width: 95%;
    }
    .admin-alert {
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
        padding: 12px;
        border-radius: 4px;
        margin-top: 15px;
    }
    @media (max-width: 768px) {
        .stats-cards {
            grid-template-columns: repeat(2, 1fr);
        }
        .action-section {
            flex-direction: column;
            align-items: stretch;
        }
        .search-box {
            width: 100%;
        }
        .action-buttons {
            justify-content: center;
        }
    }
    @media (max-width: 576px) {
        .stats-cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="records-container">
    <!-- Header -->
    <div class="header-section">
        <h1><i class="fas fa-business-time"></i> Suspension of Work Records</h1>
        <h2>NDRRMC Memorandum Circular No. 05, s. 2025 - Annex 14</h2>
        <?php if ($is_admin): ?>
            <div class="admin-alert">
                <strong><i class="fas fa-shield-alt"></i> Admin Mode:</strong> You are viewing all records from all barangays.
            </div>
        <?php endif; ?>
    </div>

    <?php show_flash(); ?>

    <!-- Statistics Cards -->
    <div class="stats-cards" id="statsCards">
        <!-- Skeleton Cards -->
        <div class="stat-card skeleton-card">
            <div class="skeleton-title skeleton"></div>
            <div class="skeleton-icon skeleton"></div>
            <div class="skeleton-text skeleton short"></div>
        </div>
        <div class="stat-card skeleton-card">
            <div class="skeleton-title skeleton"></div>
            <div class="skeleton-icon skeleton"></div>
            <div class="skeleton-text skeleton short"></div>
        </div>
        <div class="stat-card skeleton-card">
            <div class="skeleton-title skeleton"></div>
            <div class="skeleton-icon skeleton"></div>
            <div class="skeleton-text skeleton short"></div>
        </div>
        <div class="stat-card skeleton-card">
            <div class="skeleton-title skeleton"></div>
            <div class="skeleton-icon skeleton"></div>
            <div class="skeleton-text skeleton short"></div>
        </div>
        <div class="stat-card skeleton-card">
            <div class="skeleton-title skeleton"></div>
            <div class="skeleton-icon skeleton"></div>
            <div class="skeleton-text skeleton short"></div>
        </div>

        <!-- Actual Cards -->
        <div class="stat-card">
            <h3><?php echo $total_records; ?></h3>
            <p><i class="fas fa-file-alt"></i> Total Records</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $government_count; ?></h3>
            <p><i class="fas fa-landmark"></i> Government</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $private_count; ?></h3>
            <p><i class="fas fa-building"></i> Private</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $all_count; ?></h3>
            <p><i class="fas fa-globe"></i> All Sectors</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $ongoing_count; ?></h3>
            <p><i class="fas fa-clock"></i> Ongoing</p>
        </div>
    </div>

    <!-- Action Section -->
    <div class="action-section">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="🔍 Search records..." onkeyup="searchRecords()">
        </div>
        <div class="action-buttons">
            <a href="annex14.php" class="btn-custom btn-primary-custom">
                <i class="fas fa-plus"></i> Add New Record
            </a>
            <a href="annex14_archived.php" class="btn-custom btn-secondary-custom">
                <i class="fas fa-archive"></i> View Archived
            </a>
            <button onclick="exportToCSV()" class="btn-custom btn-success-custom">
                <i class="fas fa-file-csv"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Records Table -->
    <div class="table-container" id="tableContainer">
        <table class="custom-table" id="recordsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Barangay</th>
                    <th>Type</th>
                    <th>Suspension Date</th>
                    <th>Resumption Date</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Date Created</th>
                    <?php if ($is_admin): ?>
                    <th>Created By</th>
                    <?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            
            <!-- Skeleton Loader -->
            <tbody class="skeleton-loader" id="skeletonLoader">
                <?php for ($i = 0; $i < 5; $i++): ?>
                <tr class="skeleton-row">
                    <td class="skeleton-cell">
                        <div class="skeleton-bar short skeleton"></div>
                    </td>
                    <td class="skeleton-cell">
                        <div class="skeleton-bar medium skeleton"></div>
                    </td>
                    <td class="skeleton-cell">
                        <div class="skeleton-bar short skeleton"></div>
                    </td>
                    <td class="skeleton-cell">
                        <div class="skeleton-bar medium skeleton"></div>
                    </td>
                    <td class="skeleton-cell">
                        <div class="skeleton-bar medium skeleton"></div>
                    </td>
                    <td class="skeleton-cell">
                        <div class="skeleton-bar short skeleton"></div>
                    </td>
                    <td class="skeleton-cell">
                        <div class="skeleton-bar short skeleton"></div>
                    </td>
                    <td class="skeleton-cell">
                        <div class="skeleton-bar long skeleton"></div>
                    </td>
                    <td class="skeleton-cell">
                        <div class="skeleton-bar short skeleton"></div>
                        <div class="skeleton-bar short skeleton"></div>
                    </td>
                    <?php if ($is_admin): ?>
                    <td class="skeleton-cell">
                        <div class="skeleton-bar medium skeleton"></div>
                    </td>
                    <?php endif; ?>
                    <td class="skeleton-cell">
                        <div class="skeleton-bar short skeleton" style="width: 70px; display: inline-block; margin-right: 5px;"></div>
                        <div class="skeleton-bar short skeleton" style="width: 70px; display: inline-block; margin-right: 5px;"></div>
                        <div class="skeleton-bar short skeleton" style="width: 70px; display: inline-block; margin-right: 5px;"></div>
                        <div class="skeleton-bar short skeleton" style="width: 70px; display: inline-block;"></div>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
            
            <!-- Actual Content -->
            <tbody class="table-content" id="tableContent">
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="<?php echo $is_admin ? '11' : '10'; ?>" style="text-align: center; padding: 30px;">
                            <i class="fas fa-business-time" style="font-size: 3rem; color: #ccc;"></i>
                            <p style="color: #6c757d; margin-top: 15px;">No suspension of work records found.</p>
                            <a href="annex14.php" class="btn-custom btn-primary-custom" style="margin-top: 15px;">
                                <i class="fas fa-plus"></i> Create First Record
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><strong>#<?php echo $record['id']; ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($record['barangay'] ?? $record['user_barangay'] ?? 'N/A'); ?></strong>
                                <?php if ($is_admin && isset($record['full_name'])): ?>
                                    <br><small class="text-muted">by <?php echo htmlspecialchars($record['full_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badge_class = match($record['type']) {
                                    'Government' => 'badge-government',
                                    'Private' => 'badge-private',
                                    'All' => 'badge-all',
                                    default => 'badge-secondary'
                                };
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($record['type']); ?>
                                </span>
                            </td>
                            <td>
                                <small><?php echo date('M j, Y', strtotime($record['suspension_date'])); ?></small>
                                <br><small class="text-muted"><?php echo date('g:i A', strtotime($record['suspension_date'])); ?></small>
                            </td>
                            <td>
                                <?php if (!empty($record['resumption_date'])): ?>
                                    <small><?php echo date('M j, Y', strtotime($record['resumption_date'])); ?></small>
                                    <br><small class="text-muted"><?php echo date('g:i A', strtotime($record['resumption_date'])); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($record['resumption_date'])): ?>
                                    <?php
                                    $suspension = new DateTime($record['suspension_date']);
                                    $resumption = new DateTime($record['resumption_date']);
                                    $interval = $suspension->diff($resumption);
                                    echo $interval->format('%a days %h hrs');
                                    ?>
                                <?php else: ?>
                                    <span class="text-muted">Ongoing</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($record['resumption_date'])): ?>
                                    <span class="badge badge-completed">Completed</span>
                                <?php else: ?>
                                    <span class="badge badge-ongoing">Ongoing</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($record['remarks'])): ?>
                                    <div style="max-width: 200px;">
                                        <?php echo htmlspecialchars($record['remarks']); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?php echo date('M j, Y', strtotime($record['created_at'])); ?></small>
                                <br><small class="text-muted"><?php echo date('g:i A', strtotime($record['created_at'])); ?></small>
                            </td>
                            <?php if ($is_admin): ?>
                            <td><?php echo htmlspecialchars($record['full_name'] ?? 'Unknown'); ?></td>
                            <?php endif; ?>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-table btn-view" onclick="viewRecord(<?php echo $record['id']; ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-table btn-edit" onclick="editRecord(<?php echo $record['id']; ?>)" title="Edit Record">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirmArchive(<?php echo $record['id']; ?>)">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="archive_id" value="<?php echo $record['id']; ?>">
                                        <button type="submit" class="btn btn-table btn-archive" title="Archive Record">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirmDelete(<?php echo $record['id']; ?>)">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="delete_id" value="<?php echo $record['id']; ?>">
                                        <button type="submit" class="btn btn-table btn-delete" title="Delete Record">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Showing <?php echo count($records); ?> records
        </div>
        <nav>
            <ul class="pagination mb-0">
                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div>
</div>

<!-- View Record Modal -->
<div class="modal fade" id="viewRecordModal" tabindex="-1" aria-labelledby="viewRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRecordModalLabel">Suspension of Work Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewRecordContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading record data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Record Modal -->
<div class="modal fade" id="editRecordModal" tabindex="-1" aria-labelledby="editRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRecordModalLabel">Edit Suspension of Work Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editRecordContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading record data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Skeleton Loader
document.addEventListener('DOMContentLoaded', function() {
    // Show skeleton loader for both table and cards
    const tableContainer = document.getElementById('tableContainer');
    const statsCards = document.getElementById('statsCards');
    
    tableContainer.classList.add('loading');
    statsCards.classList.add('loading');
    
    // Simulate loading delay (you can remove this in production)
    setTimeout(function() {
        tableContainer.classList.remove('loading');
        statsCards.classList.remove('loading');
    }, 1500);
});

// Search function
function searchRecords() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('recordsTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let txtValue = tr[i].textContent || tr[i].innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            tr[i].style.display = '';
        } else {
            tr[i].style.display = 'none';
        }
    }
}

// View record function
function viewRecord(recordId) {
    const modal = new bootstrap.Modal(document.getElementById('viewRecordModal'));
    modal.show();
    
    fetch(`../api/annex14_view.php?id=${recordId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('viewRecordContent').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('viewRecordContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error loading record details: ${error}
                </div>
            `;
        });
}

// Edit record function with confirmation
function editRecord(recordId) {
    if (!confirm('⚠️ You are about to make changes to Record #' + recordId + '.\n\nClick OK to proceed with editing.')) {
        return;
    }
    
    // Show loading state
    const modal = new bootstrap.Modal(document.getElementById('editRecordModal'));
    modal.show();
    
    // Load edit form via AJAX
    fetch(`../api/annex14_edit.php?id=${recordId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('editRecordContent').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('editRecordContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error loading record for editing: ${error}
                </div>
            `;
        });
}

// Confirm delete function
function confirmDelete(recordId) {
    return confirm('🗑️ Are you sure you want to delete Record #' + recordId + '?\n\nThis action cannot be undone.');
}

// Confirm archive function
function confirmArchive(recordId) {
    return confirm('📁 Are you sure you want to archive Record #' + recordId + '?\n\nArchived records can be restored later from the archived records page.');
}

// Export to CSV function
function exportToCSV() {
    const records = <?php echo json_encode($records); ?>;
    let csvContent = "data:text/csv;charset=utf-8,";
    
    csvContent += "ID,Region,Province,City,Barangay,Type,Suspension Date,Resumption Date,Duration,Status,Remarks,Created By,Created At\n";
    
    records.forEach(record => {
        const suspensionDate = new Date(record.suspension_date);
        const resumptionDate = record.resumption_date ? new Date(record.resumption_date) : null;
        let duration = '';
        let status = '';
        
        if (resumptionDate) {
            const diffTime = Math.abs(resumptionDate - suspensionDate);
            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
            const diffHours = Math.floor((diffTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            duration = `${diffDays} days ${diffHours} hrs`;
            status = 'Completed';
        } else {
            duration = 'Ongoing';
            status = 'Ongoing';
        }
        
        const row = [
            record.id,
            `"${record.region}"`,
            `"${record.province}"`,
            `"${record.city}"`,
            `"${record.barangay || record.user_barangay || 'Unknown'}"`,
            `"${record.type}"`,
            `"${suspensionDate.toLocaleString()}"`,
            resumptionDate ? `"${resumptionDate.toLocaleString()}"` : '',
            `"${duration}"`,
            `"${status}"`,
            `"${(record.remarks || '').replace(/"/g, '""')}"`,
            `"${record.full_name || 'Current User'}"`,
            `"${record.created_at}"`
        ].join(',');
        csvContent += row + "\n";
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "annex14_suspension_work_" + new Date().toISOString().split('T')[0] + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php
$content = ob_get_clean();
require_once '../includes/sidenav.php';
?>
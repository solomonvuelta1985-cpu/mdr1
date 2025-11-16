<?php
// Define page title for the header
define('PAGE_TITLE', 'Annex 17 - Pre-emptive Evacuation (People) Records');

// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

// RATE LIMITING
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex17_records_access', 100, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex17 records page access rate limit exceeded');
    set_flash('Too many requests. Please try again later.', 'error');
    header('Location: dashboard.php');
    exit;
}

// AUDIT LOG
log_audit_action(
    $_SESSION['user_id'],
    'annex17_records_access',
    'Accessed Annex 17 pre-emptive evacuation (people) records page'
);

// SECURITY LOG
log_security_event(
    $_SESSION['user_id'],
    'page_access',
    'Accessed Annex 17 records page'
);

// Database connection
$pdo = $pdo;

// Get current user's data for filtering
$user_location = get_user_location_data($_SESSION['user_id']);
$is_admin = ($_SESSION['user_role'] === 'admin');

// Ensure is_archived column exists
try {
    $column_check = $pdo->query("SHOW COLUMNS FROM annex17_preemptive_evacuation LIKE 'is_archived'");
    if ($column_check->rowCount() == 0) {
        error_log("CRITICAL: Missing is_archived column. Run migrations manually");
        set_flash('Database schema error. Contact administrator.', 'error');
        header('Location: dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Failed to check is_archived column: " . $e->getMessage());
}

// Handle record actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        set_flash('Security token validation failed', 'error');
        log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex17 records CSRF token mismatch');
        header('Location: annex17_records.php');
        exit;
    }

    if (!check_rate_limit($user_id, 'annex17_post_action', 30, 3600)) {
        log_security_event($user_id, 'rate_limit_exceeded', 'Annex17 POST action rate limit exceeded');
        set_flash('Too many actions. Please try again later.', 'error');
        header('Location: annex17_records.php');
        exit;
    }

    // Handle delete
    if (isset($_POST['delete_id'])) {
        $delete_id = filter_var($_POST['delete_id'], FILTER_VALIDATE_INT);

        if (!$delete_id) {
            set_flash('Invalid record ID', 'error');
            log_security_event($_SESSION['user_id'], 'invalid_input', 'Invalid delete_id for Annex17');
            header('Location: annex17_records.php');
            exit;
        }

        if (!check_rate_limit($user_id, 'annex17_delete', 10, 3600)) {
            log_security_event($user_id, 'rate_limit_exceeded', 'Annex17 delete rate limit exceeded');
            set_flash('Too many delete attempts. Please try again later.', 'error');
            header('Location: annex17_records.php');
            exit;
        }

        // Check ownership
        if (!$is_admin) {
            $check_stmt = db_query("SELECT * FROM annex17_preemptive_evacuation WHERE id = ? AND created_by = ?", [$delete_id, $_SESSION['user_id']]);
            $record = $check_stmt->fetch();

            if (!$record) {
                set_flash('Record not found or access denied', 'error');
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to delete Annex17 record #{$delete_id} without permission");
                header('Location: annex17_records.php');
                exit;
            }
        } else {
            $check_stmt = db_query("SELECT * FROM annex17_preemptive_evacuation WHERE id = ?", [$delete_id]);
            $record = $check_stmt->fetch();
        }

        if ($record) {
            $stmt = db_query("DELETE FROM annex17_preemptive_evacuation WHERE id = ?", [$delete_id]);

            if ($stmt && $stmt->rowCount() > 0) {
                log_audit_action($_SESSION['user_id'], 'annex17_delete', "Permanently deleted record #{$delete_id} for {$record['barangay']}");
                log_security_event($_SESSION['user_id'], 'annex17_delete_success', "Successfully deleted Annex17 record #{$delete_id}");
                set_flash('Record deleted successfully', 'success');
            } else {
                set_flash('Failed to delete record', 'error');
                log_security_event($_SESSION['user_id'], 'delete_failure', "Failed to delete Annex17 record #{$delete_id}");
            }
        } else {
            set_flash('Record not found', 'error');
        }
        header('Location: annex17_records.php');
        exit;
    }

    // Handle archive
    if (isset($_POST['archive_id'])) {
        $archive_id = filter_var($_POST['archive_id'], FILTER_VALIDATE_INT);

        if (!$archive_id) {
            set_flash('Invalid record ID', 'error');
            log_security_event($_SESSION['user_id'], 'invalid_input', 'Invalid archive_id for Annex17');
            header('Location: annex17_records.php');
            exit;
        }

        if (!check_rate_limit($user_id, 'annex17_archive', 20, 3600)) {
            log_security_event($user_id, 'rate_limit_exceeded', 'Annex17 archive rate limit exceeded');
            set_flash('Too many archive attempts. Please try again later.', 'error');
            header('Location: annex17_records.php');
            exit;
        }

        // Check ownership
        if (!$is_admin) {
            $check_stmt = db_query("SELECT * FROM annex17_preemptive_evacuation WHERE id = ? AND created_by = ?", [$archive_id, $_SESSION['user_id']]);
            $record = $check_stmt->fetch();

            if (!$record) {
                set_flash('Record not found or access denied', 'error');
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to archive Annex17 record #{$archive_id} without permission");
                header('Location: annex17_records.php');
                exit;
            }
        } else {
            $check_stmt = db_query("SELECT * FROM annex17_preemptive_evacuation WHERE id = ?", [$archive_id]);
            $record = $check_stmt->fetch();
        }

        if ($record) {
            $stmt = db_query("UPDATE annex17_preemptive_evacuation SET is_archived = 1 WHERE id = ?", [$archive_id]);

            if ($stmt && $stmt->rowCount() > 0) {
                log_audit_action($_SESSION['user_id'], 'annex17_archive', "Archived record #{$archive_id} for {$record['barangay']}");
                log_security_event($_SESSION['user_id'], 'annex17_archive_success', "Successfully archived Annex17 record #{$archive_id}");
                set_flash('Record archived successfully', 'success');
            } else {
                set_flash('Failed to archive record', 'error');
                log_security_event($_SESSION['user_id'], 'archive_failure', "Failed to archive Annex17 record #{$archive_id}");
            }
        } else {
            set_flash('Record not found', 'error');
        }
        header('Location: annex17_records.php');
        exit;
    }
}

// Generate CSRF token
$csrf_token = generate_token();

// Fetch records based on user role
if ($is_admin) {
    $stmt = db_query("
        SELECT a.*, u.full_name, u.barangay as user_barangay
        FROM annex17_preemptive_evacuation a
        LEFT JOIN users u ON a.created_by = u.id
        WHERE (a.is_archived = 0 OR a.is_archived IS NULL)
        ORDER BY a.created_at DESC
    ");
} else {
    $stmt = db_query("
        SELECT * FROM annex17_preemptive_evacuation
        WHERE created_by = ? AND (is_archived = 0 OR is_archived IS NULL)
        ORDER BY created_at DESC
    ", [$_SESSION['user_id']]);
}

$records = $stmt->fetchAll();

// Calculate total statistics
$total_records = count($records);
$total_families = 0;
$total_persons_cumulative = 0;
$total_persons_current = 0;

foreach ($records as $record) {
    $total_families += $record['families'] ?? 0;
    $total_persons_cumulative += $record['total_cumulative'] ?? 0;
    $total_persons_current += $record['total_current'] ?? 0;
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
    .stat-card:nth-child(2) { border-left-color: #198754; }
    .stat-card:nth-child(3) { border-left-color: #fd7e14; }
    .stat-card:nth-child(4) { border-left-color: #0dcaf0; }

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

    .empty-state {
        text-align: center;
        padding: clamp(40px, 8vw, 60px);
        color: #6c757d;
    }
    .empty-state i {
        font-size: clamp(3rem, 8vw, 4rem);
        margin-bottom: clamp(15px, 3vw, 20px);
        opacity: 0.5;
    }
</style>

<div class="records-container">
    <div class="header-section">
        <h1>Annex 17: Pre-emptive Evacuation (People)</h1>
        <h2>Records Management</h2>
    </div>

    <!-- Flash Messages -->
    <?php show_flash(); ?>

    <!-- Statistics Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <h3><?= number_format($total_records) ?></h3>
            <p>Total Records</p>
        </div>
        <div class="stat-card">
            <h3><?= number_format($total_families) ?></h3>
            <p>Total Families Evacuated</p>
        </div>
        <div class="stat-card">
            <h3><?= number_format($total_persons_cumulative) ?></h3>
            <p>Total Persons (Cumulative)</p>
        </div>
        <div class="stat-card">
            <h3><?= number_format($total_persons_current) ?></h3>
            <p>Total Persons (Current)</p>
        </div>
    </div>

    <!-- Action Section -->
    <div class="action-section">
        <div class="action-buttons">
            <a href="annex17.php" class="btn-custom btn-success-custom">
                <i class="fas fa-plus"></i>
                <span>New Report</span>
            </a>
            <a href="dashboard.php" class="btn-custom btn-primary-custom">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Records Table -->
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Barangay</th>
                    <th>Families</th>
                    <th>Total Persons (C/R)</th>
                    <th>Male (C/R)</th>
                    <th>Female (C/R)</th>
                    <th>Created Date</th>
                    <?php if ($is_admin): ?>
                        <th>Created By</th>
                    <?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="<?= $is_admin ? 9 : 8 ?>" style="border-bottom: none;">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p><strong>No records found</strong></p>
                                <p>Click "New Report" to create your first evacuation report.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['id']) ?></td>
                            <td><strong><?= htmlspecialchars($record['barangay']) ?></strong></td>
                            <td><?= number_format($record['families']) ?></td>
                            <td>
                                <?= number_format($record['total_cumulative']) ?> /
                                <?= number_format($record['total_current']) ?>
                            </td>
                            <td>
                                <?= number_format($record['total_male_cumulative']) ?> /
                                <?= number_format($record['total_male_current']) ?>
                            </td>
                            <td>
                                <?= number_format($record['total_female_cumulative']) ?> /
                                <?= number_format($record['total_female_current']) ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($record['created_at'])) ?></td>
                            <?php if ($is_admin): ?>
                                <td><?= htmlspecialchars($record['full_name'] ?? 'N/A') ?></td>
                            <?php endif; ?>
                            <td>
                                <a href="../api/annex17_view.php?id=<?= $record['id'] ?>" class="btn-table btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="../api/annex17_edit.php?id=<?= $record['id'] ?>" class="btn-table btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to archive this record?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="archive_id" value="<?= $record['id'] ?>">
                                    <button type="submit" class="btn-table btn-archive">
                                        <i class="fas fa-archive"></i> Archive
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to permanently delete this record? This action cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="delete_id" value="<?= $record['id'] ?>">
                                    <button type="submit" class="btn-table btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content
require_once '../includes/sidenav.php';
?>

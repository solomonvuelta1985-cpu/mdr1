<?php
// Define page title for the header
define('PAGE_TITLE', 'Annex 2 - Affected Population Records');

// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in, redirect to login if not
require_login();

// Get current user's data for filtering
$user_location = get_user_location_data($_SESSION['user_id']);
$is_admin = ($_SESSION['user_role'] === 'admin');

// Handle record actions (delete or archive)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Log suspicious method calls
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        log_security_event($_SESSION['user_id'], 'invalid_method', 'Annex2 records accessed using non-POST method');
        set_flash('Invalid request method', 'error');
        header('Location: annex2_records.php');
        exit;
    }

    // Verify CSRF token
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex2 records action CSRF token mismatch');
        set_flash('Security token validation failed', 'error');
        header('Location: annex2_records.php');
        exit;
    }

    // Determine action type for rate limiting
    $action_type = isset($_POST['delete_id']) ? 'annex2_delete' : (isset($_POST['archive_id']) ? 'annex2_archive' : null);
    if ($action_type && !check_rate_limit($_SESSION['user_id'], $action_type, 10, 3600)) { // 10 per hour
        log_security_event($_SESSION['user_id'], 'rate_limit_exceeded', "Annex2 {$action_type} rate limit exceeded");
        set_flash('Too many attempts. Please try again later.', 'error');
        header('Location: annex2_records.php');
        exit;
    }

    // Handle Delete Action
    if (isset($_POST['delete_id'])) {
        $delete_id = sanitize($_POST['delete_id']);

        // Verify ownership
        if (!$is_admin) {
            $check_stmt = db_query("SELECT id FROM annex2_affected_population WHERE id = ? AND created_by = ?", [$delete_id, $_SESSION['user_id']]);
            if (!$check_stmt->fetch()) {
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted delete of record #{$delete_id}");
                set_flash('Record not found or access denied', 'error');
                header('Location: annex2_records.php');
                exit;
            }
        }

        // Execute deletion
        $stmt = db_query("DELETE FROM annex2_affected_population WHERE id = ?", [$delete_id]);

        if ($stmt && $stmt->rowCount() > 0) {
            log_audit_action($_SESSION['user_id'], 'annex2_delete', "Deleted record #{$delete_id}");
            log_security_event($_SESSION['user_id'], 'annex2_delete_success', "Successfully deleted Annex2 record #{$delete_id}");
            set_flash('Record deleted successfully', 'success');
        } else {
            log_security_event($_SESSION['user_id'], 'annex2_delete_failed', "Failed to delete record #{$delete_id}");
            set_flash('Failed to delete record', 'error');
        }

        header('Location: annex2_records.php');
        exit;
    }

    // Handle Archive Action
    if (isset($_POST['archive_id'])) {
        $archive_id = sanitize($_POST['archive_id']);

        // Verify ownership
        if (!$is_admin) {
            $check_stmt = db_query("SELECT id FROM annex2_affected_population WHERE id = ? AND created_by = ?", [$archive_id, $_SESSION['user_id']]);
            if (!$check_stmt->fetch()) {
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted archive of record #{$archive_id}");
                set_flash('Record not found or access denied', 'error');
                header('Location: annex2_records.php');
                exit;
            }
        }

        // Execute archive
        $stmt = db_query("UPDATE annex2_affected_population SET is_archived = 1 WHERE id = ?", [$archive_id]);

        if ($stmt && $stmt->rowCount() > 0) {
            log_audit_action($_SESSION['user_id'], 'annex2_archive', "Archived record #{$archive_id}");
            log_security_event($_SESSION['user_id'], 'annex2_archive_success', "Successfully archived Annex2 record #{$archive_id}");
            set_flash('Record archived successfully', 'success');
        } else {
            log_security_event($_SESSION['user_id'], 'annex2_archive_failed', "Failed to archive record #{$archive_id}");
            set_flash('Failed to archive record', 'error');
        }

        header('Location: annex2_records.php');
        exit;
    }
}

$csrf_token = generate_token();

if ($is_admin) {
    $stmt = db_query("
        SELECT a.*, u.full_name, u.barangay as user_barangay 
        FROM annex2_affected_population a 
        LEFT JOIN users u ON a.created_by = u.id 
        WHERE a.is_archived = 0 
        ORDER BY a.created_at DESC
    ");
} else {
    $stmt = db_query("
        SELECT * FROM annex2_affected_population 
        WHERE created_by = ? AND is_archived = 0 
        ORDER BY created_at DESC
    ", [$_SESSION['user_id']]);
}

$records = $stmt->fetchAll();

// Start output buffering
ob_start();
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    body {
        background-color: #f8f9fa;
        padding: clamp(15px, 3vw, 20px);
        font-family: Arial, sans-serif;
    }
    .records-container {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 12px rgba(0,0,0,0.15);
        padding: clamp(20px, 4vw, 25px);
        margin: 0 auto;
        width: 100%;
        max-width: 98vw;
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
    
    /* Table Styles */
    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    .records-table {
        width: 100%;
        margin-bottom: 0;
    }
    .records-table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
        padding: 12px 15px;
        white-space: nowrap;
    }
    .records-table td {
        padding: 12px 15px;
        vertical-align: top;
        border-bottom: 1px solid #dee2e6;
    }
    .records-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .records-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: nowrap;
    }
    .btn-table {
        padding: 6px 12px;
        font-size: 0.875rem;
        border: none;
        border-radius: 4px;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-view {
        background-color: #0dcaf0;
        color: #000;
    }
    .btn-view:hover {
        background-color: #31d2f2;
    }
    .btn-edit {
        background-color: #0d6efd;
        color: white;
    }
    .btn-edit:hover {
        background-color: #0b5ed7;
    }
    .btn-delete {
        background-color: #dc3545;
        color: white;
    }
    .btn-delete:hover {
        background-color: #bb2d3b;
    }
    .btn-archive {
        background-color: #6c757d;
        color: white;
    }
    .btn-archive:hover {
        background-color: #5c636a;
    }
    
    /* Export Dropdown */
    .dropdown-menu {
        min-width: 160px;
    }
    .dropdown-item {
        padding: 8px 16px;
    }
    .dropdown-item i {
        width: 20px;
        text-align: center;
        margin-right: 8px;
    }
    
    /* Print Styles */
    @media print {
        body * {
            visibility: hidden;
        }
        .records-container, .records-container * {
            visibility: visible;
        }
        .records-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none;
            padding: 0;
        }
        .action-buttons, .btn, .stats-card, .alert {
            display: none !important;
        }
        .table-container {
            overflow: visible;
            border: none;
        }
        .records-table {
            font-size: 12px;
        }
        .records-table th, .records-table td {
            padding: 6px 8px;
        }
    }
    
    /* Skeleton Loader */
    .skeleton-loader {
        display: none;
    }
    .skeleton-item {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: 4px;
        margin-bottom: 8px;
    }
    .skeleton-text {
        height: 16px;
    }
    .skeleton-button {
        height: 32px;
        width: 70px;
    }
    
    @keyframes loading {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }
    
    .loading .skeleton-loader {
        display: table-row-group;
    }
    .loading .table-content {
        display: none;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    /* Stats Card */
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .stats-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stats-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .table-container {
            font-size: 0.875rem;
        }
        .records-table th,
        .records-table td {
            padding: 8px 10px;
        }
        .action-buttons {
            flex-direction: column;
            gap: 4px;
        }
        .btn-table {
            padding: 4px 8px;
            font-size: 0.8rem;
        }
    }
</style>

<div class="container-fluid">
    <div class="records-container">
        <div class="header-section">
            <h1>NDRRMC Memorandum Circular No. 05, s. 2025</h1>
            <h2>Annex 2: Affected Population Records</h2>
            <?php if ($is_admin): ?>
                <div class="alert alert-info mt-3">
                    <strong>Admin Mode:</strong> You are viewing all records from all barangays.
                </div>
            <?php endif; ?>
        </div>

        <!-- Flash Messages -->
        <?php show_flash(); ?>

        <!-- Statistics Card -->
        <div class="stats-card">
            <div class="row">
                <div class="col-md-3 text-center">
                    <div class="stats-number"><?php echo count($records); ?></div>
                    <div class="stats-label">Active Records</div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="stats-number"><?php echo $is_admin ? 'All' : htmlspecialchars($user_location['barangay']); ?></div>
                    <div class="stats-label">Barangay</div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="stats-number"><?php echo date('M j, Y'); ?></div>
                    <div class="stats-label">Last Updated</div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="stats-number"><?php echo $is_admin ? count($records) : count($records); ?></div>
                    <div class="stats-label">Your Records</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-4 flex-wrap gap-3">
            <a href="annex2.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Record
            </a>
            <div class="action-buttons">
                <!-- Print Button -->
                <button class="btn btn-outline-secondary" onclick="printAnnex2()">
                    <i class="fas fa-print me-2"></i>Print
                </button>
                
                <!-- Export Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="exportToWord()"><i class="fas fa-file-word text-primary"></i>Export to Word</a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportToExcel()"><i class="fas fa-file-excel text-success"></i>Export to Excel</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="exportToCSV()"><i class="fas fa-file-csv text-info"></i>Export to CSV</a></li>
                    </ul>
                </div>
                
                <a href="annex2_archived.php" class="btn btn-outline-warning">
                    <i class="fas fa-archive me-2"></i>View Archived
                </a>
            </div>
        </div>

        <!-- Records Table -->
        <div class="table-container">
            <table class="records-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Region</th>
                        <th>Province</th>
                        <th>City/Municipality</th>
                        <th>Barangay</th>
                        <th>Families (Cum/Cur)</th>
                        <th>Persons (Cum/Cur)</th>
                        <th>ECs (Cum/Cur)</th>
                        <th>Total Displaced</th>
                        <th>Remarks</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                
                <!-- Skeleton Loader -->
                <tbody class="skeleton-loader">
                    <?php for($i = 0; $i < 5; $i++): ?>
                    <tr>
                        <td><div class="skeleton-item skeleton-text" style="width: 50px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 100px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 100px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 120px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 80px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 80px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 80px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 100px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 100px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 150px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 120px;"></div></td>
                        <td>
                            <div class="d-flex gap-2">
                                <div class="skeleton-item skeleton-button"></div>
                                <div class="skeleton-item skeleton-button"></div>
                                <div class="skeleton-item skeleton-button"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
                
                <!-- Actual Content -->
                <tbody class="table-content">
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="12">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h3>No Records Found</h3>
                                    <p>No affected population reports have been submitted yet.</p>
                                    <a href="annex2.php" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus me-2"></i>Create First Report
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td><strong>#<?php echo $record['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($record['region']); ?></td>
                                <td><?php echo htmlspecialchars($record['province']); ?></td>
                                <td><?php echo htmlspecialchars($record['city']); ?></td>
                                <td><?php echo htmlspecialchars($record['barangay']); ?></td>
                                <td class="text-center">
                                    <small><?php echo htmlspecialchars($record['affected_families_cumulative'] ?? '0'); ?></small> /
                                    <small><?php echo htmlspecialchars($record['affected_families_current'] ?? '0'); ?></small>
                                </td>
                                <td class="text-center">
                                    <small><?php echo htmlspecialchars($record['affected_persons_cumulative'] ?? '0'); ?></small> /
                                    <small><?php echo htmlspecialchars($record['affected_persons_current'] ?? '0'); ?></small>
                                </td>
                                <td class="text-center">
                                    <small><?php echo htmlspecialchars($record['num_ecs_cumulative'] ?? '0'); ?></small> /
                                    <small><?php echo htmlspecialchars($record['num_ecs_current'] ?? '0'); ?></small>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $total_displaced = ($record['total_persons_cumulative'] ?? 0);
                                    echo htmlspecialchars($total_displaced);
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($record['remarks'])): ?>
                                        <div class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($record['remarks']); ?>">
                                            <?php echo htmlspecialchars($record['remarks']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No remarks</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo date('M j, Y', strtotime($record['created_at'])); ?></small>
                                    <br><small class="text-muted"><?php echo date('g:i A', strtotime($record['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-table btn-view" onclick="viewRecord(<?php echo $record['id']; ?>)" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-table btn-edit" onclick="editRecord(<?php echo $record['id']; ?>)" title="Edit Record">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmDelete(<?php echo $record['id']; ?>)">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="delete_id" value="<?php echo $record['id']; ?>">
                                            <button type="submit" class="btn btn-table btn-delete" title="Delete Record">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmArchive(<?php echo $record['id']; ?>)">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="archive_id" value="<?php echo $record['id']; ?>">
                                            <button type="submit" class="btn btn-table btn-archive" title="Archive Record">
                                                <i class="fas fa-archive"></i>
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
        
        <!-- Pagination (for future use) -->
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
</div>

<!-- View Record Modal -->
<div class="modal fade" id="viewRecordModal" tabindex="-1" aria-labelledby="viewRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRecordModalLabel">Affected Population Details</h5>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRecordModalLabel">Edit Affected Population Record</h5>
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
    // Show skeleton loader initially
    document.querySelector('.table-container').classList.add('loading');
    
    // Simulate loading time
    setTimeout(function() {
        document.querySelector('.table-container').classList.remove('loading');
    }, 1000);
});

// View record function
function viewRecord(recordId) {
    const modal = new bootstrap.Modal(document.getElementById('viewRecordModal'));
    modal.show();

    // Load view content via AJAX
    fetch(`../api/annex2_view.php?id=${recordId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('viewRecordContent').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('viewRecordContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error loading record details: ${error.message}
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
    fetch(`../api/annex2_edit.php?id=${recordId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('editRecordContent').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('editRecordContent').innerHTML = `
                <div class="alert alert-danger">
                    Error loading record: ${error.message}
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
    return confirm('📁 Are you sure you want to archive Record #' + recordId + '?\n\nArchived records can be restored later.');
}

// Print function
function printAnnex2() {
    // Open print template in new window
    const printWindow = window.open('../print_templates/annex2_print_template.php', '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');

    // Fallback if popup is blocked
    if (!printWindow) {
        alert('Please allow popups for this site to use the print feature.');
        // Alternative: redirect to print template
        window.location.href = '../print_templates/annex2_print_template.php';
    }
}

// Export to Word function
function exportToWord() {
    const records = <?php echo json_encode($records); ?>;

    let htmlContent = `
        <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
            <meta charset="utf-8">
            <title>Annex 2 - Affected Population</title>
            <style>
                body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; }
                table { border-collapse: collapse; width: 100%; border: 2px solid #000; }
                th, td { border: 1px solid #000; padding: 4px 6px; text-align: center; font-size: 8pt; }
                th { background-color: #f0f0f0; font-weight: bold; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                .text-left { text-align: left; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1 style="font-size: 12pt; margin-bottom: 5px;">NDRRMC Memorandum Circular No. 05, s. 2025 re NDRRMC Reporting Templates</h1>
                <h2 style="font-size: 11pt;">Annex 2: Affected Population</h2>
                <p style="font-size: 9pt;">Generated on: ${new Date().toLocaleString()}</p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="3">Region</th>
                        <th rowspan="3">Province</th>
                        <th rowspan="3">City / Municipality</th>
                        <th rowspan="3">Barangay</th>
                        <th rowspan="2" colspan="2">Affected Families</th>
                        <th rowspan="2" colspan="2">Affected Persons</th>
                        <th rowspan="2" colspan="2">No of ECs</th>
                        <th colspan="4">Inside ECs</th>
                        <th colspan="4">Outside ECs</th>
                        <th colspan="4">Total Displaced</th>
                        <th rowspan="3">Remarks</th>
                    </tr>
                    <tr>
                        <th colspan="2">Families</th>
                        <th colspan="2">Persons</th>
                        <th colspan="2">Families</th>
                        <th colspan="2">Persons</th>
                        <th colspan="2">Families</th>
                        <th colspan="2">Persons</th>
                    </tr>
                    <tr>
                        <th>Cumulative</th>
                        <th>Current</th>
                        <th>Cumulative</th>
                        <th>Current</th>
                        <th>Cumulative</th>
                        <th>Current</th>
                        <th>Cumulative</th>
                        <th>Current</th>
                        <th>Cumulative</th>
                        <th>Current</th>
                        <th>Cumulative</th>
                        <th>Current</th>
                        <th>Cumulative</th>
                        <th>Current</th>
                        <th>Cumulative</th>
                        <th>Current</th>
                    </tr>
                </thead>
                <tbody>
    `;

    records.forEach(record => {
        htmlContent += `
            <tr>
                <td class="text-left">${record.region || ''}</td>
                <td class="text-left">${record.province || ''}</td>
                <td class="text-left">${record.city || ''}</td>
                <td class="text-left">${record.barangay || ''}</td>
                <td>${record.affected_families_cumulative || '0'}</td>
                <td>${record.affected_families_current || '0'}</td>
                <td>${record.affected_persons_cumulative || '0'}</td>
                <td>${record.affected_persons_current || '0'}</td>
                <td>${record.num_ecs_cumulative || '0'}</td>
                <td>${record.num_ecs_current || '0'}</td>
                <td>${record.inside_families_cumulative || '0'}</td>
                <td>${record.inside_families_current || '0'}</td>
                <td>${record.inside_persons_cumulative || '0'}</td>
                <td>${record.inside_persons_current || '0'}</td>
                <td>${record.outside_families_cumulative || '0'}</td>
                <td>${record.outside_families_current || '0'}</td>
                <td>${record.outside_persons_cumulative || '0'}</td>
                <td>${record.outside_persons_current || '0'}</td>
                <td>${record.total_families_cumulative || '0'}</td>
                <td>${record.total_families_current || '0'}</td>
                <td>${record.total_persons_cumulative || '0'}</td>
                <td>${record.total_persons_current || '0'}</td>
                <td class="text-left">${record.remarks || ''}</td>
            </tr>
        `;
    });

    htmlContent += `
                </tbody>
            </table>
        </body>
        </html>
    `;

    const blob = new Blob([htmlContent], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'annex2_affected_population_' + new Date().toISOString().split('T')[0] + '.doc';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Export to Excel function
function exportToExcel() {
    const records = <?php echo json_encode($records); ?>;

    let csvContent = "data:application/vnd.ms-excel;charset=utf-8,";

    // Headers - matching the NDRRMC template structure
    csvContent += "Region\tProvince\tCity/Municipality\tBarangay\t";
    csvContent += "Affected Families (Cumulative)\tAffected Families (Current)\t";
    csvContent += "Affected Persons (Cumulative)\tAffected Persons (Current)\t";
    csvContent += "No. of ECs (Cumulative)\tNo. of ECs (Current)\t";
    csvContent += "Inside ECs - Families (Cumulative)\tInside ECs - Families (Current)\t";
    csvContent += "Inside ECs - Persons (Cumulative)\tInside ECs - Persons (Current)\t";
    csvContent += "Outside ECs - Families (Cumulative)\tOutside ECs - Families (Current)\t";
    csvContent += "Outside ECs - Persons (Cumulative)\tOutside ECs - Persons (Current)\t";
    csvContent += "Total Displaced - Families (Cumulative)\tTotal Displaced - Families (Current)\t";
    csvContent += "Total Displaced - Persons (Cumulative)\tTotal Displaced - Persons (Current)\t";
    csvContent += "Remarks\n";

    // Data
    records.forEach(record => {
        const row = [
            record.region || '',
            record.province || '',
            record.city || '',
            record.barangay || '',
            record.affected_families_cumulative || '0',
            record.affected_families_current || '0',
            record.affected_persons_cumulative || '0',
            record.affected_persons_current || '0',
            record.num_ecs_cumulative || '0',
            record.num_ecs_current || '0',
            record.inside_families_cumulative || '0',
            record.inside_families_current || '0',
            record.inside_persons_cumulative || '0',
            record.inside_persons_current || '0',
            record.outside_families_cumulative || '0',
            record.outside_families_current || '0',
            record.outside_persons_cumulative || '0',
            record.outside_persons_current || '0',
            record.total_families_cumulative || '0',
            record.total_families_current || '0',
            record.total_persons_cumulative || '0',
            record.total_persons_current || '0',
            (record.remarks || '').replace(/\t/g, ' ').replace(/\n/g, ' ')
        ].join('\t');
        csvContent += row + "\n";
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "annex2_affected_population_" + new Date().toISOString().split('T')[0] + ".xls");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Export to CSV function
function exportToCSV() {
    const records = <?php echo json_encode($records); ?>;
    let csvContent = "data:text/csv;charset=utf-8,";

    // Headers - matching the NDRRMC template structure
    csvContent += "Region,Province,City/Municipality,Barangay,";
    csvContent += "Affected Families (Cumulative),Affected Families (Current),";
    csvContent += "Affected Persons (Cumulative),Affected Persons (Current),";
    csvContent += "No. of ECs (Cumulative),No. of ECs (Current),";
    csvContent += "Inside ECs - Families (Cumulative),Inside ECs - Families (Current),";
    csvContent += "Inside ECs - Persons (Cumulative),Inside ECs - Persons (Current),";
    csvContent += "Outside ECs - Families (Cumulative),Outside ECs - Families (Current),";
    csvContent += "Outside ECs - Persons (Cumulative),Outside ECs - Persons (Current),";
    csvContent += "Total Displaced - Families (Cumulative),Total Displaced - Families (Current),";
    csvContent += "Total Displaced - Persons (Cumulative),Total Displaced - Persons (Current),";
    csvContent += "Remarks,Created By,Created At\n";

    // Data
    records.forEach(record => {
        const row = [
            `"${record.region || ''}"`,
            `"${record.province || ''}"`,
            `"${record.city || ''}"`,
            `"${record.barangay || ''}"`,
            `"${record.affected_families_cumulative || '0'}"`,
            `"${record.affected_families_current || '0'}"`,
            `"${record.affected_persons_cumulative || '0'}"`,
            `"${record.affected_persons_current || '0'}"`,
            `"${record.num_ecs_cumulative || '0'}"`,
            `"${record.num_ecs_current || '0'}"`,
            `"${record.inside_families_cumulative || '0'}"`,
            `"${record.inside_families_current || '0'}"`,
            `"${record.inside_persons_cumulative || '0'}"`,
            `"${record.inside_persons_current || '0'}"`,
            `"${record.outside_families_cumulative || '0'}"`,
            `"${record.outside_families_current || '0'}"`,
            `"${record.outside_persons_cumulative || '0'}"`,
            `"${record.outside_persons_current || '0'}"`,
            `"${record.total_families_cumulative || '0'}"`,
            `"${record.total_families_current || '0'}"`,
            `"${record.total_persons_cumulative || '0'}"`,
            `"${record.total_persons_current || '0'}"`,
            `"${(record.remarks || '').replace(/"/g, '""')}"`,
            `"${record.full_name || 'Current User'}"`,
            `"${record.created_at}"`
        ].join(',');
        csvContent += row + "\n";
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "annex2_affected_population_" + new Date().toISOString().split('T')[0] + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php
// Get the captured content and store it in a variable
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content
require_once '../includes/sidenav.php';
?>
<?php
// Define page title for the header
define('PAGE_TITLE', 'Annex 1 - Records Management');

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

    //  Log suspicious method calls
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        log_security_event($_SESSION['user_id'], 'invalid_method', 'Annex1 records accessed using non-POST method');
        set_flash('Invalid request method', 'error');
        header('Location: annex1_records.php');
        exit;
    }

    //  Verify CSRF token
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex1 records action CSRF token mismatch');
        set_flash('Security token validation failed', 'error');
        header('Location: annex1_records.php');
        exit;
    }

    //  Determine action type for rate limiting
    $action_type = isset($_POST['delete_id']) ? 'annex1_delete' : (isset($_POST['archive_id']) ? 'annex1_archive' : null);
    if ($action_type && !check_rate_limit($_SESSION['user_id'], $action_type, 10, 3600)) { // 10 per hour
        log_security_event($_SESSION['user_id'], 'rate_limit_exceeded', "Annex1 {$action_type} rate limit exceeded");
        set_flash('Too many attempts. Please try again later.', 'error');
        header('Location: annex1_records.php');
        exit;
    }

    //  Handle Delete Action
    if (isset($_POST['delete_id'])) {
        $delete_id = sanitize($_POST['delete_id']);

        // Verify ownership
        if (!$is_admin) {
            $check_stmt = db_query("SELECT id FROM annex1_related_incidents WHERE id = ? AND created_by = ?", [$delete_id, $_SESSION['user_id']]);
            if (!$check_stmt->fetch()) {
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted delete of record #{$delete_id}");
                set_flash('Record not found or access denied', 'error');
                header('Location: annex1_records.php');
                exit;
            }
        }

        // Execute deletion
        $stmt = db_query("DELETE FROM annex1_related_incidents WHERE id = ?", [$delete_id]);

        if ($stmt && $stmt->rowCount() > 0) {
            log_audit_action($_SESSION['user_id'], 'annex1_delete', "Deleted record #{$delete_id}");
            log_security_event($_SESSION['user_id'], 'annex1_delete_success', "Successfully deleted Annex1 record #{$delete_id}");
            set_flash('Record deleted successfully', 'success');
        } else {
            log_security_event($_SESSION['user_id'], 'annex1_delete_failed', "Failed to delete record #{$delete_id}");
            set_flash('Failed to delete record', 'error');
        }

        header('Location: annex1_records.php');
        exit;
    }

    // Handle Archive Action
    if (isset($_POST['archive_id'])) {
        $archive_id = sanitize($_POST['archive_id']);

        // Verify ownership
        if (!$is_admin) {
            $check_stmt = db_query("SELECT id FROM annex1_related_incidents WHERE id = ? AND created_by = ?", [$archive_id, $_SESSION['user_id']]);
            if (!$check_stmt->fetch()) {
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted archive of record #{$archive_id}");
                set_flash('Record not found or access denied', 'error');
                header('Location: annex1_records.php');
                exit;
            }
        }

        // Execute archive
        $stmt = db_query("UPDATE annex1_related_incidents SET is_archived = 1 WHERE id = ?", [$archive_id]);

        if ($stmt && $stmt->rowCount() > 0) {
            log_audit_action($_SESSION['user_id'], 'annex1_archive', "Archived record #{$archive_id}");
            log_security_event($_SESSION['user_id'], 'annex1_archive_success', "Successfully archived Annex1 record #{$archive_id}");
            set_flash('Record archived successfully', 'success');
        } else {
            log_security_event($_SESSION['user_id'], 'annex1_archive_failed', "Failed to archive record #{$archive_id}");
            set_flash('Failed to archive record', 'error');
        }

        header('Location: annex1_records.php');
        exit;
    }
}


$csrf_token = generate_token();


if ($is_admin) {
    $stmt = db_query("
        SELECT a.*, u.full_name, u.barangay as user_barangay 
        FROM annex1_related_incidents a 
        LEFT JOIN users u ON a.created_by = u.id 
        WHERE a.is_archived = 0 
        ORDER BY a.created_at DESC
    ");
} else {
    $stmt = db_query("
        SELECT * FROM annex1_related_incidents 
        WHERE created_by = ? AND is_archived = 0 
        ORDER BY created_at DESC
    ", [$_SESSION['user_id']]);
}

$records = $stmt->fetchAll();

// Start output buffering (unchanged)
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
            <h2>Annex 1: Related Incident Records</h2>
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
            <a href="annex1.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Incident
            </a>
            <div class="d-flex gap-2 flex-wrap">
                <!-- Print Button -->
                <button class="btn btn-success" onclick="openPrintPreview()">
                    <i class="fas fa-print me-2"></i>Print
                </button>

                <!-- Export Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-info dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export me-2"></i>Export
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="#" onclick="exportRecords('pdf')"><i class="fas fa-file-pdf text-danger me-2"></i>PDF</a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportRecords('excel')"><i class="fas fa-file-excel text-success me-2"></i>Excel</a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportRecords('word')"><i class="fas fa-file-word text-primary me-2"></i>Word</a></li>
                    </ul>
                </div>

                <a href="annex1_archived.php" class="btn btn-outline-warning">
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
                        <th>Barangay</th>
                        <th>Incident Type</th>
                        <th>Occurrence Date</th>
                        <th>Description</th>
                        <th>Actions Taken</th>
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
                        <td><div class="skeleton-item skeleton-text" style="width: 120px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 150px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 200px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 180px;"></div></td>
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
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h3>No Records Found</h3>
                                    <p>No incident reports have been submitted yet.</p>
                                    <a href="annex1.php" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus me-2"></i>Create First Report
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td><strong>#<?php echo $record['id']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($record['barangay']); ?>
                                    <?php if ($is_admin): ?>
                                        <br><small class="text-muted">by <?php echo htmlspecialchars($record['full_name'] ?? 'Unknown'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($record['incident_type']); ?></span>
                                </td>
                                <td>
                                    <?php echo $record['occurrence_date'] ? date('M j, Y g:i A', strtotime($record['occurrence_date'])) : '<span class="text-muted">Not set</span>'; ?>
                                </td>
                                <td>
                                    <?php if (!empty($record['description'])): ?>
                                        <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($record['description']); ?>">
                                            <?php echo htmlspecialchars($record['description']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No description</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($record['actions_taken'])): ?>
                                        <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($record['actions_taken']); ?>">
                                            <?php echo htmlspecialchars($record['actions_taken']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No actions</span>
                                    <?php endif; ?>
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
                                        <button class="btn btn-table btn-edit" onclick="editRecord(<?php echo $record['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmDelete(<?php echo $record['id']; ?>)">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="delete_id" value="<?php echo $record['id']; ?>">
                                            <button type="submit" class="btn btn-table btn-delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmArchive(<?php echo $record['id']; ?>)">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="archive_id" value="<?php echo $record['id']; ?>">
                                            <button type="submit" class="btn btn-table btn-archive">
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

<!-- Edit Record Modal -->
<div class="modal fade" id="editRecordModal" tabindex="-1" aria-labelledby="editRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRecordModalLabel">Edit Incident Record</h5>
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

// Edit record function with confirmation
function editRecord(recordId) {
    if (!confirm('⚠️ You are about to make changes to Record #' + recordId + '.\n\nClick OK to proceed with editing.')) {
        return;
    }
    
    // Show loading state
    const modal = new bootstrap.Modal(document.getElementById('editRecordModal'));
    modal.show();
    
    // Load edit form via AJAX
    fetch(`../api/annex1_edit.php?id=${recordId}`)
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

// Export to CSV function
function exportToCSV() {
    const records = <?php echo json_encode($records); ?>;
    let csvContent = "data:text/csv;charset=utf-8,";

    // Headers
    csvContent += "ID,Barangay,Incident Type,Occurrence Date,Description,Actions Taken,Remarks,Created By,Created At\n";

    // Data
    records.forEach(record => {
        const row = [
            record.id,
            `"${record.barangay}"`,
            `"${record.incident_type}"`,
            `"${record.occurrence_date}"`,
            `"${(record.description || '').replace(/"/g, '""')}"`,
            `"${(record.actions_taken || '').replace(/"/g, '""')}"`,
            `"${(record.remarks || '').replace(/"/g, '""')}"`,
            `"${record.full_name || 'Current User'}"`,
            `"${record.created_at}"`
        ].join(',');
        csvContent += row + "\n";
    });

    // Download
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "annex1_records_" + new Date().toISOString().split('T')[0] + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Open print preview
function openPrintPreview() {
    const printWindow = window.open('../print_templates/annex1_print_template.php', 'Print Preview', 'width=1200,height=800,scrollbars=yes,resizable=yes');
    if (!printWindow) {
        alert('Please allow pop-ups to open the print preview.');
    }
}

// Export records to specified format
function exportRecords(format) {
    let url = '../api/annex1_export.php?type=' + format;

    if (format === 'pdf') {
        // For PDF, open in new window
        const pdfWindow = window.open(url, 'PDF Export', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        if (!pdfWindow) {
            alert('Please allow pop-ups to generate PDF.');
        }
    } else {
        // For Excel and Word, trigger download
        window.location.href = url;
    }

    return false;
}
</script>

<?php
// Get the captured content and store it in a variable
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content
require_once '../includes/sidenav.php';
?>
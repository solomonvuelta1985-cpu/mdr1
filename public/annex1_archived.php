<?php
// Define page title for the header
define('PAGE_TITLE', 'Annex 1 - Archived Records');

// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in, redirect to login if not
require_login();

// Database connection
$pdo = $pdo;

// Get current user's data for filtering
$user_location = get_user_location_data($_SESSION['user_id']);
$is_admin = ($_SESSION['user_role'] === 'admin');

// Handle restore action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        set_flash('Security token validation failed', 'error');
        header('Location: annex1_archived.php');
        exit;
    }
    
    // Handle restore action
    if (isset($_POST['restore_id'])) {
        $restore_id = sanitize($_POST['restore_id']);
        
        // Check if user owns the record (unless admin)
        if (!$is_admin) {
            $check_stmt = db_query("SELECT id FROM annex1_related_incidents WHERE id = ? AND created_by = ?", [$restore_id, $_SESSION['user_id']]);
            if (!$check_stmt->fetch()) {
                set_flash('Record not found or access denied', 'error');
                header('Location: annex1_archived.php');
                exit;
            }
        }
        
        $stmt = db_query("UPDATE annex1_related_incidents SET is_archived = 0 WHERE id = ?", [$restore_id]);
        
        if ($stmt && $stmt->rowCount() > 0) {
            set_flash('Record restored successfully', 'success');
        } else {
            set_flash('Failed to restore record', 'error');
        }
        header('Location: annex1_archived.php');
        exit;
    }
    
    // Handle permanent delete action
    if (isset($_POST['permanent_delete_id'])) {
        $delete_id = sanitize($_POST['permanent_delete_id']);
        
        // Check if user owns the record (unless admin)
        if (!$is_admin) {
            $check_stmt = db_query("SELECT id FROM annex1_related_incidents WHERE id = ? AND created_by = ?", [$delete_id, $_SESSION['user_id']]);
            if (!$check_stmt->fetch()) {
                set_flash('Record not found or access denied', 'error');
                header('Location: annex1_archived.php');
                exit;
            }
        }
        
        $stmt = db_query("DELETE FROM annex1_related_incidents WHERE id = ?", [$delete_id]);
        
        if ($stmt && $stmt->rowCount() > 0) {
            set_flash('Record permanently deleted', 'success');
        } else {
            set_flash('Failed to delete record', 'error');
        }
        header('Location: annex1_archived.php');
        exit;
    }
}

// Generate CSRF token
$csrf_token = generate_token();

// Fetch archived records based on user role
if ($is_admin) {
    // Admin can see all archived records
    $stmt = db_query("
        SELECT a.*, u.full_name, u.barangay as user_barangay 
        FROM annex1_related_incidents a 
        LEFT JOIN users u ON a.created_by = u.id 
        WHERE a.is_archived = 1 
        ORDER BY a.updated_at DESC
    ");
} else {
    // Regular users can only see their own archived records
    $stmt = db_query("
        SELECT * FROM annex1_related_incidents 
        WHERE created_by = ? AND is_archived = 1 
        ORDER BY updated_at DESC
    ", [$_SESSION['user_id']]);
}

$archived_records = $stmt->fetchAll();

// Start output buffering
ob_start();
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f8f9fa;
        padding: clamp(15px, 3vw, 20px);
        font-family: Arial, san
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
        background-color: #fff3cd;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #856404;
        padding: 12px 15px;
        white-space: nowrap;
    }
    .records-table td {
        padding: 12px 15px;
        vertical-align: top;
        border-bottom: 1px solid #dee2e6;
    }
    .records-table tbody tr {
        background-color: #fffdf6;
    }
    .records-table tbody tr:hover {
        background-color: #fff9e6;
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
    .btn-restore {
        background-color: #198754;
        color: white;
    }
    .btn-restore:hover {
        background-color: #157347;
    }
    .btn-delete {
        background-color: #dc3545;
        color: white;
    }
    .btn-delete:hover {
        background-color: #bb2d3b;
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
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
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
    
    /* Warning Alert */
    .warning-alert {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
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
            <h2>Annex 1: Archived Incident Records</h2>
            <?php if ($is_admin): ?>
                <div class="alert alert-info mt-3">
                    <strong>Admin Mode:</strong> You are viewing all archived records from all barangays.
                </div>
            <?php endif; ?>
        </div>

        <!-- Flash Messages -->
        <?php show_flash(); ?>

        <!-- Warning Alert -->
        <div class="warning-alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle text-warning me-3 fs-4"></i>
                <div>
                    <h5 class="mb-1">Archived Records</h5>
                    <p class="mb-0">These records are not visible in the main records list. You can restore them to make them active again or permanently delete them.</p>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="stats-card">
            <div class="row">
                <div class="col-md-4 text-center">
                    <div class="stats-number"><?php echo count($archived_records); ?></div>
                    <div class="stats-label">Archived Records</div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="stats-number"><?php echo $is_admin ? 'All' : htmlspecialchars($user_location['barangay']); ?></div>
                    <div class="stats-label">Barangay</div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="stats-number"><?php echo date('M j, Y'); ?></div>
                    <div class="stats-label">Last Updated</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-4 flex-wrap gap-3">
            <a href="annex1_records.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Active Records
            </a>
            <div class="action-buttons">
                <button class="btn btn-outline-secondary" onclick="exportToCSV()">
                    <i class="fas fa-download me-2"></i>Export CSV
                </button>
                <?php if (!empty($archived_records)): ?>
                <form method="POST" style="display: inline;" onsubmit="return confirmRestoreAll()">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="restore_all" value="1">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-undo me-2"></i>Restore All
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Archived Records Table -->
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
                        <th>Archived Date</th>
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
                            </div>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
                
                <!-- Actual Content -->
                <tbody class="table-content">
                    <?php if (empty($archived_records)): ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-archive"></i>
                                    <h3>No Archived Records</h3>
                                    <p>There are no archived incident reports.</p>
                                    <a href="annex1_records.php" class="btn btn-primary mt-3">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Active Records
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($archived_records as $record): ?>
                            <tr>
                                <td><strong>#<?php echo $record['id']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($record['barangay']); ?>
                                    <?php if ($is_admin): ?>
                                        <br><small class="text-muted">by <?php echo htmlspecialchars($record['full_name'] ?? 'Unknown'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($record['incident_type']); ?></span>
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
                                    <small><?php echo date('M j, Y', strtotime($record['updated_at'])); ?></small>
                                    <br><small class="text-muted"><?php echo date('g:i A', strtotime($record['updated_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" style="display: inline;" onsubmit="return confirmRestore(<?php echo $record['id']; ?>)">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="restore_id" value="<?php echo $record['id']; ?>">
                                            <button type="submit" class="btn btn-table btn-restore" title="Restore Record">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmPermanentDelete(<?php echo $record['id']; ?>)">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="permanent_delete_id" value="<?php echo $record['id']; ?>">
                                            <button type="submit" class="btn btn-table btn-delete" title="Permanently Delete">
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
                Showing <?php echo count($archived_records); ?> archived records
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

// Confirm restore function
function confirmRestore(recordId) {
    return confirm('🔄 Are you sure you want to restore Record #' + recordId + '?\n\nThis will make the record active again.');
}

// Confirm permanent delete function
function confirmPermanentDelete(recordId) {
    return confirm('🗑️⚠️ Are you sure you want to PERMANENTLY DELETE Record #' + recordId + '?\n\nThis action cannot be undone and the record will be lost forever.');
}

// Confirm restore all function
function confirmRestoreAll() {
    const count = <?php echo count($archived_records); ?>;
    return confirm('🔄 Are you sure you want to restore all ' + count + ' archived records?\n\nThis will make all records active again.');
}

// Export to CSV function
function exportToCSV() {
    const records = <?php echo json_encode($archived_records); ?>;
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Headers
    csvContent += "ID,Barangay,Incident Type,Occurrence Date,Description,Actions Taken,Remarks,Created By,Archived Date\n";
    
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
            `"${record.updated_at}"`
        ].join(',');
        csvContent += row + "\n";
    });
    
    // Download
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "annex1_archived_records_" + new Date().toISOString().split('T')[0] + ".csv");
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
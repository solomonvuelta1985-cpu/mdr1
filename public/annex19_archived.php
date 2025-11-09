<?php
// Define page title for the header
define('PAGE_TITLE', 'Annex 19 - Archived Families Assisted Records');

// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();
// AUDIT LOG: Page access
log_audit_action(
    $_SESSION['user_id'],
    'annex19_archived_access',
    'Accessed Annex 19 archived records page'
);
// Database connection
$pdo = $pdo;

// Get current user's data for filtering
$user_location = get_user_location_data($_SESSION['user_id']);
$is_admin = ($_SESSION['user_role'] === 'admin');

// Ensure is_archived column exists
try {
    $column_check = $pdo->query("SHOW COLUMNS FROM annex19_families_assisted LIKE 'is_archived'");
    if ($column_check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE annex19_families_assisted ADD COLUMN is_archived TINYINT(1) DEFAULT 0 AFTER remarks");
    }
} catch (PDOException $e) {
    error_log("Failed to add is_archived column: " . $e->getMessage());
}

// Handle restore and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        set_flash('Security token validation failed', 'error');
        log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex19 archived CSRF token mismatch');
        header('Location: annex19_archived.php');
        exit;
    }
    
    // Handle restore action
    if (isset($_POST['restore_id'])) {
        $restore_id = sanitize($_POST['restore_id']);
        
        // Check if user owns the record (unless admin)
        if (!$is_admin) {
            $check_stmt = db_query("SELECT * FROM annex19_families_assisted WHERE id = ? AND created_by = ?", [$restore_id, $_SESSION['user_id']]);
            $record = $check_stmt->fetch();
            
            if (!$record) {
                set_flash('Record not found or access denied', 'error');
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to restore Annex19 record #{$restore_id} without permission");
                header('Location: annex19_archived.php');
                exit;
            }
        } else {
            $check_stmt = db_query("SELECT * FROM annex19_families_assisted WHERE id = ?", [$restore_id]);
            $record = $check_stmt->fetch();
        }
        
        if ($record) {
            $stmt = db_query("UPDATE annex19_families_assisted SET is_archived = 0 WHERE id = ?", [$restore_id]);
            
            if ($stmt && $stmt->rowCount() > 0) {
                log_audit_action(
                    $_SESSION['user_id'],
                    'annex19_restore',
                    "Restored record #{$restore_id} for {$record['barangay']}: {$record['assistance_type']} by {$record['assistance_provider']}"
                );
                
                set_flash('Record restored successfully', 'success');
            } else {
                set_flash('Failed to restore record', 'error');
            }
        } else {
            set_flash('Record not found', 'error');
        }
        header('Location: annex19_archived.php');
        exit;
    }
    
    // Handle permanent delete action
    if (isset($_POST['permanent_delete_id'])) {
        $delete_id = sanitize($_POST['permanent_delete_id']);
        
        // Check if user owns the record (unless admin)
        if (!$is_admin) {
            $check_stmt = db_query("SELECT * FROM annex19_families_assisted WHERE id = ? AND created_by = ?", [$delete_id, $_SESSION['user_id']]);
            $record = $check_stmt->fetch();
            
            if (!$record) {
                set_flash('Record not found or access denied', 'error');
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to permanently delete Annex19 record #{$delete_id} without permission");
                header('Location: annex19_archived.php');
                exit;
            }
        } else {
            $check_stmt = db_query("SELECT * FROM annex19_families_assisted WHERE id = ?", [$delete_id]);
            $record = $check_stmt->fetch();
        }
        
        if ($record) {
            $stmt = db_query("DELETE FROM annex19_families_assisted WHERE id = ?", [$delete_id]);
            
            if ($stmt && $stmt->rowCount() > 0) {
                log_audit_action(
                    $_SESSION['user_id'],
                    'annex19_permanent_delete',
                    "Permanently deleted archived record #{$delete_id} for {$record['barangay']}: {$record['assistance_type']} by {$record['assistance_provider']}"
                );
                
                set_flash('Record permanently deleted', 'success');
            } else {
                set_flash('Failed to delete record', 'error');
            }
        } else {
            set_flash('Record not found', 'error');
        }
        header('Location: annex19_archived.php');
        exit;
    }
}

// Generate CSRF token
$csrf_token = generate_token();

// Fetch archived records based on user role
if ($is_admin) {
    $stmt = db_query("
        SELECT a.*, u.full_name, u.barangay as user_barangay 
        FROM annex19_families_assisted a 
        LEFT JOIN users u ON a.created_by = u.id 
        WHERE a.is_archived = 1 
        ORDER BY a.updated_at DESC
    ");
} else {
    $stmt = db_query("
        SELECT * FROM annex19_families_assisted 
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
        font-family: Arial, sans-serif;
        font-size: clamp(1rem, 3vw, 1.1rem);
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
    .btn-view {
        background-color: #0d6efd;
        color: white;
    }
    .btn-view:hover {
        background-color: #0b5ed7;
    }
    
    /* Badge Styles */
    .badge {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.375rem;
    }
    .badge-food {
        background-color: #198754;
        color: white;
    }
    .badge-hygiene {
        background-color: #0dcaf0;
        color: white;
    }
    .badge-shelter {
        background-color: #fd7e14;
        color: white;
    }
    .badge-medical {
        background-color: #dc3545;
        color: white;
    }
    .badge-cash {
        background-color: #198754;
        color: white;
    }
    .badge-other {
        background-color: #6c757d;
        color: white;
    }
    .badge-provider {
        background-color: #6f42c1;
        color: white;
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
    
    /* Progress Bar */
    .progress {
        height: 6px;
        margin-top: 4px;
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
        .records-table th:nth-child(6),
        .records-table td:nth-child(6),
        .records-table th:nth-child(7),
        .records-table td:nth-child(7) {
            display: none;
        }
    }
</style>

<div class="container-fluid">
    <div class="records-container">
        <div class="header-section">
            <h1>NDRRMC Memorandum Circular No. 05, s. 2025</h1>
            <h2>Annex 19: Archived Families Assisted Records</h2>
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
            <a href="annex19_records.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Active Records
            </a>
            <div class="action-buttons">
                <button class="btn btn-outline-secondary" onclick="exportToCSV()">
                    <i class="fas fa-download me-2"></i>Export CSV
                </button>
            </div>
        </div>

        <!-- Archived Records Table -->
        <div class="table-container">
            <table class="records-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Barangay</th>
                        <th>Assistance Type</th>
                        <th>Provider</th>
                        <th>Families Requiring</th>
                        <th>Families Assisted</th>
                        <th>% Assisted</th>
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
                        <td><div class="skeleton-item skeleton-text" style="width: 80px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 80px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 80px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 60px;"></div></td>
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
                    <?php if (empty($archived_records)): ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-archive"></i>
                                    <h3>No Archived Records</h3>
                                    <p>There are no archived families assisted reports.</p>
                                    <a href="annex19_records.php" class="btn btn-primary mt-3">
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
                                    <?php echo htmlspecialchars($record['barangay'] ?? $record['user_barangay'] ?? 'Unknown'); ?>
                                    <?php if ($is_admin && isset($record['full_name'])): ?>
                                        <br><small class="text-muted">by <?php echo htmlspecialchars($record['full_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badge_class = match($record['assistance_type']) {
                                        'Food Packs' => 'badge-food',
                                        'Hygiene Kits' => 'badge-hygiene',
                                        'Shelter Materials' => 'badge-shelter',
                                        'Medical Aid' => 'badge-medical',
                                        'Cash Assistance' => 'badge-cash',
                                        'Other' => 'badge-other',
                                        default => 'badge-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($record['assistance_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-provider">
                                        <?php echo htmlspecialchars($record['assistance_provider']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($record['families_requiring_assistance']); ?></td>
                                <td><?php echo number_format($record['families_assisted']); ?></td>
                                <td>
                                    <strong><?php echo number_format($record['percentage_assisted'], 1); ?>%</strong>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: <?php echo min($record['percentage_assisted'], 100); ?>%" 
                                             aria-valuenow="<?php echo $record['percentage_assisted']; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small><?php echo date('M j, Y', strtotime($record['updated_at'])); ?></small>
                                    <br><small class="text-muted"><?php echo date('g:i A', strtotime($record['updated_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-table btn-view" onclick="viewRecord(<?php echo $record['id']; ?>)" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
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

<!-- View Record Modal -->
<div class="modal fade" id="viewRecordModal" tabindex="-1" aria-labelledby="viewRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRecordModalLabel">Families Assisted Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewRecordContent">
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
    document.querySelector('.table-container').classList.add('loading');
    setTimeout(function() {
        document.querySelector('.table-container').classList.remove('loading');
    }, 1000);
});

// View record function
function viewRecord(recordId) {
    const modal = new bootstrap.Modal(document.getElementById('viewRecordModal'));
    modal.show();
    
    fetch(`../api/annex19_view.php?id=${recordId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('viewRecordContent').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('viewRecordContent').innerHTML = `
                <div class="alert alert-danger">
                    Error loading record: ${error.message}
                </div>
            `;
        });
}

// Confirm restore function
function confirmRestore(recordId) {
    return confirm('🔄 Are you sure you want to restore Record #' + recordId + '?\n\nThis will make the record active again.');
}

// Confirm permanent delete function
function confirmPermanentDelete(recordId) {
    return confirm('🗑️⚠️ Are you sure you want to PERMANENTLY DELETE Record #' + recordId + '?\n\nThis action cannot be undone and the record will be lost forever.');
}

// Export to CSV function
function exportToCSV() {
    const records = <?php echo json_encode($archived_records); ?>;
    let csvContent = "data:text/csv;charset=utf-8,";
    
    csvContent += "ID,Region,Province,City,Barangay,Families Requiring Assistance,Families Assisted,Percentage Assisted,Assistance Type,Assistance Provider,Remarks,Created By,Archived Date\n";
    
    records.forEach(record => {
        const row = [
            record.id,
            `"${record.region}"`,
            `"${record.province}"`,
            `"${record.city}"`,
            `"${record.barangay || record.user_barangay || 'Unknown'}"`,
            record.families_requiring_assistance,
            record.families_assisted,
            record.percentage_assisted,
            `"${record.assistance_type}"`,
            `"${record.assistance_provider}"`,
            `"${(record.remarks || '').replace(/"/g, '""')}"`,
            `"${record.full_name || 'Current User'}"`,
            `"${record.updated_at}"`
        ].join(',');
        csvContent += row + "\n";
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "annex19_archived_" + new Date().toISOString().split('T')[0] + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php
$content = ob_get_clean();
require_once '../includes/sidenav.php';
?>
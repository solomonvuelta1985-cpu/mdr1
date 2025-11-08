<?php
/**
 * Annex 9: Power Supply Status Records View
 * Updated to match Annex 8 design pattern with image thumbnails
 */

// Define page title for the header
define('PAGE_TITLE', 'Annex 9 - Power Supply Status Records');

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

// Handle record actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        set_flash('Security token validation failed', 'error');
        log_security_event($_SESSION['user_id'], 'csrf_failure', 'Annex9 records CSRF token mismatch');
        header('Location: annex9_records.php');
        exit;
    }
    
    // Handle delete action
    if (isset($_POST['delete_id'])) {
        $delete_id = sanitize($_POST['delete_id']);
        
        // Check if user owns the record (unless admin)
        if (!$is_admin) {
            $check_stmt = db_query("SELECT * FROM annex9_power_supply WHERE id = ? AND created_by = ?", [$delete_id, $_SESSION['user_id']]);
            if ($check_stmt) {
                $record = $check_stmt->fetch();
            } else {
                $record = false;
            }
            
            if (!$record) {
                set_flash('Record not found or access denied', 'error');
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to delete Annex9 record #{$delete_id} without permission");
                header('Location: annex9_records.php');
                exit;
            }
        } else {
            $check_stmt = db_query("SELECT * FROM annex9_power_supply WHERE id = ?", [$delete_id]);
            if ($check_stmt) {
                $record = $check_stmt->fetch();
            } else {
                $record = false;
            }
        }
        
        if ($record) {
            // Delete associated image file
            if (!empty($record['image_path'])) {
                $image_path = '../uploads/' . $record['image_path'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $stmt = db_query("DELETE FROM annex9_power_supply WHERE id = ?", [$delete_id]);
            
            if ($stmt && $stmt->rowCount() > 0) {
                // Log the deletion
                log_audit_action(
                    $_SESSION['user_id'],
                    'annex9_delete',
                    "Permanently deleted record #{$delete_id} for {$record['barangay']}: {$record['service_provider']}"
                );
                
                set_flash('Record deleted successfully', 'success');
            } else {
                set_flash('Failed to delete record', 'error');
            }
        } else {
            set_flash('Record not found', 'error');
        }
        header('Location: annex9_records.php');
        exit;
    }
    
    // Handle archive action
    if (isset($_POST['archive_id'])) {
        $archive_id = sanitize($_POST['archive_id']);
        
        // Check if user owns the record (unless admin)
        if (!$is_admin) {
            $check_stmt = db_query("SELECT * FROM annex9_power_supply WHERE id = ? AND created_by = ?", [$archive_id, $_SESSION['user_id']]);
            if ($check_stmt) {
                $record = $check_stmt->fetch();
            } else {
                $record = false;
            }
            
            if (!$record) {
                set_flash('Record not found or access denied', 'error');
                log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to archive Annex9 record #{$archive_id} without permission");
                header('Location: annex9_records.php');
                exit;
            }
        } else {
            $check_stmt = db_query("SELECT * FROM annex9_power_supply WHERE id = ?", [$archive_id]);
            if ($check_stmt) {
                $record = $check_stmt->fetch();
            } else {
                $record = false;
            }
        }
        
        if ($record) {
            // Archive the record
            $stmt = db_query("UPDATE annex9_power_supply SET is_archived = 1 WHERE id = ?", [$archive_id]);
            
            if ($stmt && $stmt->rowCount() > 0) {
                // Log the archiving
                log_audit_action(
                    $_SESSION['user_id'],
                    'annex9_archive',
                    "Archived record #{$archive_id} for {$record['barangay']}: {$record['service_provider']}"
                );
                
                set_flash('Record archived successfully', 'success');
            } else {
                set_flash('Failed to archive record', 'error');
            }
        } else {
            set_flash('Record not found', 'error');
        }
        header('Location: annex9_records.php');
        exit;
    }
}

// Generate CSRF token
$csrf_token = generate_token();

// DEBUG: Check what's in the database
error_log("DEBUG: User ID: " . $_SESSION['user_id']);
error_log("DEBUG: Is Admin: " . ($is_admin ? 'Yes' : 'No'));

// Fetch records based on user role - FIXED QUERY WITH DEBUGGING
try {
    if ($is_admin) {
        // Admin can see all non-archived records
        error_log("DEBUG: Running admin query");
        $stmt = db_query("
            SELECT a.*, u.full_name, u.barangay as user_barangay 
            FROM annex9_power_supply a 
            LEFT JOIN users u ON a.created_by = u.id 
            WHERE a.is_archived = 0 
            ORDER BY a.created_at DESC
        ");
    } else {
        // Regular users can only see their own non-archived records
        error_log("DEBUG: Running user query for user_id: " . $_SESSION['user_id']);
        $stmt = db_query("
            SELECT a.*, u.full_name, u.barangay as user_barangay 
            FROM annex9_power_supply a 
            LEFT JOIN users u ON a.created_by = u.id 
            WHERE a.created_by = ? AND a.is_archived = 0 
            ORDER BY a.created_at DESC
        ", [$_SESSION['user_id']]);
    }

    // Check if query was successful
    if ($stmt) {
        $records = $stmt->fetchAll();
        error_log("DEBUG: Found " . count($records) . " records");
        
        // Debug: Log first record if exists
        if (count($records) > 0) {
            error_log("DEBUG: First record: " . print_r($records[0], true));
        }
    } else {
        $records = [];
        error_log("ERROR: Database query failed in annex9_records.php");
        set_flash('Database error occurred while loading records', 'error');
    }
} catch (Exception $e) {
    $records = [];
    error_log("ERROR: Exception fetching records: " . $e->getMessage());
    set_flash('Error loading records. Please try again.', 'error');
}

// Calculate total statistics
$total_records = count($records);
$restored_count = 0;
$interrupted_count = 0;
$with_images_count = 0;

foreach ($records as $record) {
    if ($record['restored_datetime'] && $record['restored_datetime'] != '0000-00-00 00:00:00') {
        $restored_count++;
    } else {
        $interrupted_count++;
    }
    
    if (!empty($record['image_path'])) {
        $with_images_count++;
    }
}

// Start output buffering
ob_start();
?>
<!-- Rest of your HTML and CSS remains the same -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
    .btn-view {
        background-color: #198754;
        color: white;
    }
    .btn-view:hover {
        background-color: #157347;
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
    .badge-restored {
        background-color: #198754;
        color: white;
    }
    .badge-interrupted {
        background-color: #dc3545;
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
    
    /* Image Thumbnail */
    .image-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #dee2e6;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .image-thumbnail:hover {
        transform: scale(1.1);
        border-color: #0d6efd;
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
        .records-table th:nth-child(5),
        .records-table td:nth-child(5),
        .records-table th:nth-child(6),
        .records-table td:nth-child(6),
        .records-table th:nth-child(7),
        .records-table td:nth-child(7) {
            display: none;
        }
    }
</style>
<!-- ALERT CONTAINER FOR MESSAGES - Place this at the top of annex9_records.php -->
<div id="mainAlertContainer" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 99999; width: 90%; max-width: 600px;"></div>
<div class="container-fluid">
    <div class="records-container">
        <div class="header-section">
            <h1>NDRRMC Memorandum Circular No. 05, s. 2025</h1>
            <h2>Annex 9: Power Supply Status Records</h2>
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
                    <div class="stats-number"><?php echo $total_records; ?></div>
                    <div class="stats-label">Total Records</div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="stats-number"><?php echo $restored_count; ?></div>
                    <div class="stats-label">Power Restored</div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="stats-number"><?php echo $interrupted_count; ?></div>
                    <div class="stats-label">Still Interrupted</div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="stats-number"><?php echo $with_images_count; ?></div>
                    <div class="stats-label">With Images</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-4 flex-wrap gap-3">
            <a href="annex9.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Report
            </a>
            <div class="action-buttons">
                <button class="btn btn-outline-secondary" onclick="exportToCSV()">
                    <i class="fas fa-download me-2"></i>Export CSV
                </button>
                <a href="annex9_archived.php" class="btn btn-outline-warning">
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
                        <th>Service Provider</th>
                        <th>Interruption</th>
                        <th>Restored</th>
                        <th>Status</th>
                        <th>Image</th>
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
                        <td><div class="skeleton-item skeleton-text" style="width: 100px;"></div></td>
                        <td><div class="skeleton-item skeleton-text" style="width: 100px;"></div></td>
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
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-bolt"></i>
                                    <h3>No Records Found</h3>
                                    <p>No power supply status reports have been submitted yet.</p>
                                    <a href="annex9.php" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus me-2"></i>Create First Report
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                            <?php
                            // Calculate duration
                            $duration_text = '-';
                            if ($record['restored_datetime'] && $record['restored_datetime'] != '0000-00-00 00:00:00') {
                                $start = new DateTime($record['interruption_datetime']);
                                $end = new DateTime($record['restored_datetime']);
                                $interval = $start->diff($end);
                                
                                $parts = [];
                                if ($interval->d > 0) $parts[] = $interval->d . 'd';
                                if ($interval->h > 0) $parts[] = $interval->h . 'h';
                                if ($interval->i > 0) $parts[] = $interval->i . 'm';
                                
                                $duration_text = !empty($parts) ? implode(' ', $parts) : '<1m';
                            }
                            ?>
                            <tr>
                                <td><strong>#<?php echo $record['id']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($record['barangay'] ?? $record['user_barangay'] ?? 'Unknown'); ?>
                                    <?php if ($is_admin && isset($record['full_name'])): ?>
                                        <br><small class="text-muted">by <?php echo htmlspecialchars($record['full_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($record['service_provider']); ?></td>
                                <td>
                                    <?php if ($record['interruption_datetime'] && $record['interruption_datetime'] != '0000-00-00 00:00:00'): ?>
                                        <small><?php echo date('M j, Y', strtotime($record['interruption_datetime'])); ?></small>
                                        <br><small class="text-muted"><?php echo date('g:i A', strtotime($record['interruption_datetime'])); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($record['restored_datetime'] && $record['restored_datetime'] != '0000-00-00 00:00:00'): ?>
                                        <small><?php echo date('M j, Y', strtotime($record['restored_datetime'])); ?></small>
                                        <br><small class="text-muted"><?php echo date('g:i A', strtotime($record['restored_datetime'])); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($record['restored_datetime'] && $record['restored_datetime'] != '0000-00-00 00:00:00'): ?>
                                        <span class="badge badge-restored">
                                            Restored
                                        </span>
                                        <br><small class="text-muted"><?php echo $duration_text; ?></small>
                                    <?php else: ?>
                                        <span class="badge badge-interrupted">
                                            Interrupted
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($record['image_path'])): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($record['image_path']); ?>" 
                                             class="image-thumbnail" 
                                             alt="Power Supply Image"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imageModal"
                                             onclick="showImageModal('<?php echo htmlspecialchars($record['image_path']); ?>', '<?php echo htmlspecialchars($record['service_provider']); ?>')">
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
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
</div>

<!-- Rest of your modals and JavaScript remains the same -->
<!-- View Record Modal -->
<div class="modal fade" id="viewRecordModal" tabindex="-1" aria-labelledby="viewRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRecordModalLabel">Power Supply Status Details</h5>
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
                <h5 class="modal-title" id="editRecordModalLabel">Edit Power Supply Status Record</h5>
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

<!-- Image Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Power Supply Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Power Supply Image" class="img-fluid" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>

// Use event delegation - works for AJAX-loaded content!
// ANNEX 9 EDIT MODAL - IMAGE UPLOAD HANDLER
// Place this script in annex9_records.php BEFORE </body>

console.log('🚀 Annex9 Edit Handler Loaded');

// Use event delegation - works for AJAX-loaded content!
document.addEventListener('click', function(e) {
    // Handle upload area click
    const uploadArea = e.target.closest('#uploadArea');
    if (uploadArea) {
        console.log('📤 Upload area clicked');
        const input = document.getElementById('imageInput');
        if (input) {
            input.click();
        }
    }
    
    // Handle remove NEW image button
    if (e.target.closest('#removeNewImageBtn')) {
        console.log('🗑️ Remove NEW image button clicked');
        e.stopPropagation();
        resetNewImagePreview();
    }
    
    // Handle remove CURRENT image checkbox
    const removeCheck = e.target.closest('#removeCurrentCheck');
    if (removeCheck && removeCheck.checked) {
        if (!confirm('⚠️ Remove current image completely? You can also just upload a replacement instead.')) {
            removeCheck.checked = false;
        } else {
            showEditAlert('Current image will be deleted when you save', 'warning');
        }
    }
});

// Handle file input change
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'imageInput') {
        console.log('📁 File input changed');
        handleImageSelection(e.target);
    }
});

// Handle form submission
document.addEventListener('submit', function(e) {
    if (e.target && e.target.id === 'editRecordForm') {
        console.log('💾 Form submitted');
        e.preventDefault();
        handleFormSubmit(e.target);
    }
});

function handleImageSelection(input) {
    console.log('🖼️ Processing image selection');
    
    const uploadArea = document.getElementById('uploadArea');
    const errorArea = document.getElementById('errorArea');
    const newImagePreview = document.getElementById('newImagePreview');
    const preview = document.getElementById('imagePreview');
    const fileName = document.getElementById('fileName');
    const currentImageSection = document.getElementById('currentImageSection');
    
    if (!input.files || !input.files[0]) {
        console.log('❌ No file selected');
        return;
    }
    
    const file = input.files[0];
    console.log('📄 File selected:', {
        name: file.name,
        size: file.size + ' bytes',
        type: file.type
    });
    
    // Hide error
    if (errorArea) {
        errorArea.style.display = 'none';
        uploadArea.classList.remove('has-error');
    }
    
    // Validate size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        console.error('❌ File too large:', sizeMB + 'MB');
        errorArea.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>File Too Large!</strong><br>
            Maximum size: 5MB<br>
            Your file: ${sizeMB}MB
        `;
        errorArea.style.display = 'block';
        uploadArea.classList.add('has-error');
        input.value = '';
        return;
    }
    
    // Validate type
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!validTypes.includes(file.type)) {
        console.error('❌ Invalid file type:', file.type);
        errorArea.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Invalid File Type!</strong><br>
            Allowed: JPG, JPEG, PNG, GIF<br>
            Your file: ${file.type}
        `;
        errorArea.style.display = 'block';
        uploadArea.classList.add('has-error');
        input.value = '';
        return;
    }
    
    // Read and show NEW image preview
    const reader = new FileReader();
    reader.onload = function(e) {
        console.log('✅ NEW Image loaded - Showing preview');
        
        // Set the preview image
        preview.src = e.target.result;
        fileName.innerHTML = `
            <strong>${file.name}</strong><br>
            Size: ${(file.size / 1024).toFixed(2)} KB
        `;
        
        // Show NEW image preview section
        newImagePreview.style.display = 'block';
        uploadArea.classList.add('has-new-image');
        
        // HIDE the upload area since image is already selected
        uploadArea.style.display = 'none';
        
        // DIM the current/old image to show new one is replacing it
        if (currentImageSection) {
            currentImageSection.classList.add('dimmed');
        }
        
        // Scroll to new preview
        newImagePreview.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        showEditAlert('✅ NEW IMAGE READY! The preview above shows your new image. Click "Save Changes" to upload it.', 'success');
    };
    
    reader.onerror = function() {
        console.error('❌ Failed to read file');
        errorArea.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error!</strong> Cannot read file. Try another image.
        `;
        errorArea.style.display = 'block';
        uploadArea.classList.add('has-error');
        input.value = '';
    };
    
    reader.readAsDataURL(file);
}

function resetNewImagePreview() {
    console.log('🔄 Resetting NEW image preview');
    
    const input = document.getElementById('imageInput');
    const uploadArea = document.getElementById('uploadArea');
    const newImagePreview = document.getElementById('newImagePreview');
    const errorArea = document.getElementById('errorArea');
    const currentImageSection = document.getElementById('currentImageSection');
    
    if (input) input.value = '';
    if (newImagePreview) newImagePreview.style.display = 'none';
    if (errorArea) errorArea.style.display = 'none';
    if (uploadArea) {
        uploadArea.classList.remove('has-new-image', 'has-error');
        uploadArea.style.display = 'flex'; // Show the upload area again
    }
    
    // Un-dim the current image
    if (currentImageSection) {
        currentImageSection.classList.remove('dimmed');
    }
    
    showEditAlert('New image cancelled. You can select another one.', 'info');
}

function handleFormSubmit(form) {
    console.log('📤 Submitting form...');
    
    const saveBtn = document.getElementById('saveBtn');
    const originalText = saveBtn.innerHTML;
    const imageInput = document.getElementById('imageInput');
    
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    saveBtn.disabled = true;
    
    if (imageInput && imageInput.files.length > 0) {
        console.log('📸 Image will be uploaded:', imageInput.files[0].name);
        showEditAlert('⏳ Uploading new image and saving changes...', 'info');
    } else {
        console.log('ℹ️ No new image to upload');
    }
    
    const formData = new FormData(form);
    
    // Debug: Log what's being sent
    console.log('📦 Form data being sent:');
    for (let pair of formData.entries()) {
        if (pair[1] instanceof File) {
            console.log('  ' + pair[0] + ': [FILE] ' + pair[1].name);
        } else {
            console.log('  ' + pair[0] + ': ' + pair[1]);
        }
    }
    
    fetch('../api/annex9_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        if (!response.ok) {
            throw new Error('Server error: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('✅ Server response:', data);
        if (data.success) {
            showEditAlert('✅ ' + (data.message || 'Record updated successfully!'), 'success');
            setTimeout(() => {
                console.log('🔄 Reloading page...');
                location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Update failed');
        }
    })
    .catch(error => {
        console.error('❌ Error:', error);
        showEditAlert('❌ ' + error.message, 'danger');
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function showEditAlert(message, type) {
    console.log('📢 Alert:', type, '-', message);
    
    // Try to find alert container on MAIN PAGE first (outside modal)
    let container = document.getElementById('mainAlertContainer');
    
    // If not found, try the one inside modal (fallback)
    if (!container) {
        container = document.getElementById('editAlertContainer');
    }
    
    if (!container) {
        console.warn('⚠️ Alert container not found on page or in modal');
        // Create one at the top of the page if it doesn't exist
        container = document.createElement('div');
        container.id = 'mainAlertContainer';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.left = '50%';
        container.style.transform = 'translateX(-50%)';
        container.style.zIndex = '99999';
        container.style.width = '90%';
        container.style.maxWidth = '600px';
        document.body.insertBefore(container, document.body.firstChild);
    }
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show shadow-lg`;
    alert.style.marginBottom = '10px';
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        <strong>${message}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.innerHTML = '';
    container.appendChild(alert);
    
    // Scroll to top to see the alert
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    setTimeout(() => {
        if (alert.parentNode) alert.remove();
    }, 5000);
}


</script>

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
    // Show loading state
    const modal = new bootstrap.Modal(document.getElementById('viewRecordModal'));
    modal.show();
    
    // Load view content via AJAX
    fetch(`../api/annex9_view.php?id=${recordId}`)
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

// Edit record function with confirmation
function editRecord(recordId) {
    if (!confirm('⚠️ You are about to make changes to Record #' + recordId + '.\n\nClick OK to proceed with editing.')) {
        return;
    }
    
    // Show loading state
    const modal = new bootstrap.Modal(document.getElementById('editRecordModal'));
    modal.show();
    
    // Load edit form via AJAX
    fetch(`../api/annex9_edit.php?id=${recordId}`)
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

// Show image in modal
function showImageModal(imagePath, title) {
    document.getElementById('modalImage').src = '../uploads/' + imagePath;
    document.getElementById('imageModalLabel').textContent = title;
}

// Export to CSV function
function exportToCSV() {
    const records = <?php echo json_encode($records); ?>;
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Headers
    csvContent += "ID,Barangay,Service Provider,Interruption Date,Restored Date,Status,Remarks,Created By,Created At\n";
    
    // Data
    records.forEach(record => {
        const row = [
            record.id,
            `"${record.barangay || record.user_barangay || 'Unknown'}"`,
            `"${record.service_provider}"`,
            `"${record.interruption_datetime}"`,
            `"${record.restored_datetime || ''}"`,
            record.restored_datetime ? 'Restored' : 'Interrupted',
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
    link.setAttribute("download", "annex9_power_supply_status_" + new Date().toISOString().split('T')[0] + ".csv");
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
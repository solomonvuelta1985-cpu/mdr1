<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_admin();

$page_title = "Barangay Lock Management";
include '../includes/header.php';

// Handle unlock actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'unlock_all') {
        force_unlock_all_barangays();
        set_flash('All barangays have been unlocked successfully.', 'success');
    }
    elseif ($action === 'unlock_specific') {
        $barangay = sanitize($_POST['barangay'] ?? '');
        if ($barangay) {
            unlock_specific_barangay($barangay);
            set_flash("Barangay {$barangay} has been unlocked.", 'success');
        }
    }
}

// Get current locks
$stmt = db_query("
    SELECT bl.*, u.username as admin_username 
    FROM barangay_locks bl 
    JOIN users u ON bl.admin_id = u.id 
    WHERE bl.lock_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ORDER BY bl.lock_time DESC
");
$active_locks = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Barangay Lock Management</h4>
        </div>
        <div class="card-body">
            <?php show_flash(); ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h5>Active Locks</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($active_locks): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Barangay</th>
                                                <th>Locked By</th>
                                                <th>Lock Time</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($active_locks as $lock): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($lock['barangay']) ?></td>
                                                    <td><?= htmlspecialchars($lock['admin_username']) ?></td>
                                                    <td><?= date('M j, Y g:i A', strtotime($lock['lock_time'])) ?></td>
                                                    <td>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="unlock_specific">
                                                            <input type="hidden" name="barangay" value="<?= htmlspecialchars($lock['barangay']) ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger">Unlock</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No active locks</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5>Emergency Actions</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Use these actions carefully:</p>
                            
                            <form method="POST" class="mb-3">
                                <input type="hidden" name="action" value="unlock_all">
                                <button type="submit" class="btn btn-danger w-100" 
                                        onclick="return confirm('Are you sure you want to unlock ALL barangays?')">
                                    Unlock All Barangays
                                </button>
                            </form>
                            
                            <div class="alert alert-info">
                                <small>
                                    <strong>Note:</strong> Locks automatically expire after 1 hour. 
                                    Use this only if you need immediate unlocking.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
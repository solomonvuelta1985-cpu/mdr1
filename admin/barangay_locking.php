<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_admin();

// Handle barangay selection BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_barangay = sanitize($_POST['barangay'] ?? '');
    $action = $_POST['action'] ?? 'lock';

    if ($action === 'lock' && !empty($selected_barangay)) {
        // Check if barangay is already locked by another admin
        $existing_lock = is_barangay_locked($selected_barangay);
        if ($existing_lock && $existing_lock['admin_id'] != $_SESSION['user_id']) {
            set_flash("Barangay <strong>{$selected_barangay}</strong> is currently being used by administrator <strong>{$existing_lock['admin_name']}</strong>. Please select another barangay.", 'error');
        } else {
            // Lock the barangay
            if (set_admin_barangay_session($selected_barangay)) {
                set_flash("You are now reporting for <strong>{$selected_barangay}</strong> barangay. Regular users from this barangay cannot login.", 'success');
            } else {
                set_flash('Failed to lock barangay. Please try again.', 'error');
            }
        }
    }
    elseif ($action === 'unlock') {
        // Unlock current barangay
        unlock_barangay($_SESSION['user_id']);
        set_flash('Barangay unlocked successfully. Regular users can now login.', 'success');
    }

    header('Location: barangay_locking.php');
    exit;
}

$page_title = "Barangay Locking Management";
include '../includes/header.php';

// Get barangay list
$barangays = get_baggao_barangays();

// Get current lock status
$current_lock = get_admin_locked_barangay($_SESSION['user_id']);
$csrf_token = generate_token();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-lock me-2"></i>Barangay Locking Management
                    </h4>
                </div>
                <div class="card-body">
                    <?php show_flash(); ?>
                    
                    <!-- Current Lock Status -->
                    <div class="alert alert-info">
                        <h5>Current Status</h5>
                        <?php if ($current_lock): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Currently reporting for:</strong> 
                                    <span class="text-success"><?= htmlspecialchars($current_lock) ?></span>
                                </div>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="unlock">
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="fas fa-unlock me-1"></i>Unlock
                                    </button>
                                </form>
                            </div>
                            <p class="mt-2 mb-0 text-muted">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    Regular users from <?= htmlspecialchars($current_lock) ?> cannot login while this lock is active.
                                </small>
                            </p>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-times-circle text-secondary me-2"></i>
                                    <strong>No barangay selected</strong>
                                </div>
                                <span class="text-muted">Select a barangay below to start reporting</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Barangay Selection Form -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Select Barangay for Reporting</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="action" value="lock">
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <label for="barangay" class="form-label">Choose Barangay</label>
                                        <select class="form-select" id="barangay" name="barangay" required>
                                            <option value="">-- Select Barangay --</option>
                                            <?php foreach ($barangays as $barangay): 
                                                $lock_info = is_barangay_locked($barangay);
                                                $is_locked = $lock_info && $lock_info['admin_id'] != $_SESSION['user_id'];
                                                $is_current = $barangay === $current_lock;
                                            ?>
                                                <option value="<?= htmlspecialchars($barangay) ?>" 
                                                    <?= $is_current ? 'selected' : '' ?>
                                                    <?= $is_locked ? 'disabled' : '' ?>>
                                                    <?= htmlspecialchars($barangay) ?>
                                                    <?php if ($is_locked): ?>
                                                         🔒 (In use by <?= htmlspecialchars($lock_info['admin_name']) ?>)
                                                    <?php elseif ($is_current): ?>
                                                         ✅ (Currently selected)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100" 
                                                <?= $current_lock ? 'disabled' : '' ?>>
                                            <i class="fas fa-lock me-2"></i>
                                            <?= $current_lock ? 'Change Barangay' : 'Lock Barangay' ?>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <div class="mb-1">
                                            <span class="text-danger">🔒</span> = Barangay locked by another admin
                                        </div>
                                        <div class="mb-1">
                                            <span class="text-success">✅</span> = Currently selected by you
                                        </div>
                                        <div>
                                            Locking a barangay will prevent regular users from that barangay from logging in.
                                        </div>
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Active Locks Overview -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Active Barangay Locks</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $stmt = db_query("
                                SELECT bl.*, u.username, u.full_name 
                                FROM barangay_locks bl 
                                JOIN users u ON bl.admin_id = u.id 
                                WHERE bl.lock_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                                ORDER BY bl.lock_time DESC
                            ");
                            $active_locks = $stmt->fetchAll();
                            ?>
                            
                            <?php if ($active_locks): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Barangay</th>
                                                <th>Locked By</th>
                                                <th>Lock Time</th>
                                                <th>Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($active_locks as $lock): ?>
                                                <tr class="<?= $lock['admin_id'] == $_SESSION['user_id'] ? 'table-success' : '' ?>">
                                                    <td>
                                                        <?= htmlspecialchars($lock['barangay']) ?>
                                                        <?php if ($lock['admin_id'] == $_SESSION['user_id']): ?>
                                                            <span class="badge bg-success">You</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($lock['full_name']) ?></td>
                                                    <td><?= date('M j, g:i A', strtotime($lock['lock_time'])) ?></td>
                                                    <td>
                                                        <?php
                                                        $lock_time = strtotime($lock['lock_time']);
                                                        $current_time = time();
                                                        $duration = $current_time - $lock_time;
                                                        $hours = floor($duration / 3600);
                                                        $minutes = floor(($duration % 3600) / 60);
                                                        echo "{$hours}h {$minutes}m";
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Locks automatically expire after 1 hour of inactivity.
                                </small>
                            <?php else: ?>
                                <p class="text-muted mb-0">No active barangay locks</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
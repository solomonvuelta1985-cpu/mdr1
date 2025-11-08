<?php
/**
 * API Handler: View Power Supply Status Record Details (Annex 9)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// Get record ID
$record_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$record_id) {
    echo '<div class="alert alert-danger">Invalid record ID</div>';
    exit;
}

// Get user info
$is_admin = ($_SESSION['user_role'] === 'admin');

// Fetch record
try {
    if ($is_admin) {
        $sql = "SELECT p.*, u.full_name 
                FROM annex9_power_supply p 
                LEFT JOIN users u ON p.created_by = u.id 
                WHERE p.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id]);
    } else {
        $sql = "SELECT p.*, u.full_name 
                FROM annex9_power_supply p 
                LEFT JOIN users u ON p.created_by = u.id 
                WHERE p.id = ? AND p.created_by = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id, $_SESSION['user_id']]);
    }

    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        echo '<div class="alert alert-warning">Record not found or access denied.</div>';
        exit;
    }
    
    // Log the view action
    log_audit_action(
        $_SESSION['user_id'],
        'annex9_view',
        "Viewed power supply record #{$record_id} for {$record['barangay']}"
    );
    
    // SECURITY LOG: Record view
    log_security_event(
        $_SESSION['user_id'],
        'record_view',
        "Viewed Annex 9 record #{$record_id}"
    );
    
} catch (PDOException $e) {
    error_log("Annex9 View Error: " . $e->getMessage());
    echo '<div class="alert alert-danger">Unable to load record. Please try again.</div>';
    exit;
}

// Calculate duration if both dates present
$duration = null;
if ($record['interruption_datetime'] && $record['restored_datetime']) {
    $start = new DateTime($record['interruption_datetime']);
    $end = new DateTime($record['restored_datetime']);
    $interval = $start->diff($end);
    
    $duration_parts = [];
    if ($interval->d > 0) $duration_parts[] = $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
    if ($interval->h > 0) $duration_parts[] = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '');
    if ($interval->i > 0) $duration_parts[] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
    
    $duration = !empty($duration_parts) ? implode(', ', $duration_parts) : 'Less than 1 minute';
}
?>
<?php if ($record['image_path']): ?>
<div class="mb-4">
    <div class="section-header">📷 Attached Image</div>
    <div class="info-row">
        <div class="info-value" style="width: 100%;">
            <img src="../uploads/<?php echo htmlspecialchars($record['image_path']); ?>" 
                 alt="Power supply damage photo" 
                 class="img-fluid rounded shadow-sm" 
                 style="max-width: 100%; max-height: 400px; cursor: pointer;"
                 onclick="window.open(this.src, '_blank')">
            <p class="text-muted small mt-2 mb-0">
                <i class="fas fa-info-circle"></i> Click image to view full size
            </p>
        </div>
    </div>
</div>
<?php endif; ?>
<style>
    .view-container {
        padding: 20px;
    }
    .section-header {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
    }
    .info-row {
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid #f1f3f5;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        width: 40%;
        font-weight: 500;
        color: #6c757d;
    }
    .info-value {
        width: 60%;
        color: #212529;
    }
    .badge-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .status-restored {
        background-color: #198754;
        color: white;
    }
    .status-interrupted {
        background-color: #dc3545;
        color: white;
    }
    .highlight {
        font-size: 1.1rem;
        font-weight: 600;
        color: #0d6efd;
    }
</style>

<div class="view-container">
    <!-- Header -->
    <div class="mb-4">
        <h5 class="mb-2">Power Supply Status Report #<?php echo $record['id']; ?></h5>
        <small class="text-muted">
            <?php echo htmlspecialchars($record['barangay']); ?>, 
            <?php echo htmlspecialchars($record['city']); ?>, 
            <?php echo htmlspecialchars($record['province']); ?>
        </small>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Service Provider -->
            <div class="mb-4">
                <div class="section-header">Service Provider</div>
                <div class="info-row">
                    <div class="info-label">Provider:</div>
                    <div class="info-value highlight"><?php echo htmlspecialchars($record['service_provider']); ?></div>
                </div>
            </div>

            <!-- Interruption Details -->
            <div class="mb-4">
                <div class="section-header">Interruption Details</div>
                <div class="info-row">
                    <div class="info-label">Interruption Started:</div>
                    <div class="info-value"><?php echo date('M j, Y g:i A', strtotime($record['interruption_datetime'])); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Power Restored:</div>
                    <div class="info-value">
                        <?php if ($record['restored_datetime']): ?>
                            <?php echo date('M j, Y g:i A', strtotime($record['restored_datetime'])); ?>
                        <?php else: ?>
                            <span class="badge-status status-interrupted">Not yet restored</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($duration): ?>
                <div class="info-row">
                    <div class="info-label">Outage Duration:</div>
                    <div class="info-value"><strong><?php echo $duration; ?></strong></div>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value">
                        <?php if ($record['restored_datetime']): ?>
                            <span class="badge-status status-restored">Power Restored</span>
                        <?php else: ?>
                            <span class="badge-status status-interrupted">Power Interrupted</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Remarks -->
            <?php if ($record['remarks']): ?>
            <div class="mb-4">
                <div class="section-header">Remarks</div>
                <div class="info-row">
                    <div class="info-value" style="width: 100%;">
                        <?php echo nl2br(htmlspecialchars($record['remarks'])); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Record Info -->
            <div class="mb-4">
                <div class="section-header">Record Information</div>
                <?php if (isset($record['full_name'])): ?>
                <div class="info-row">
                    <div class="info-label">Created By:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['full_name']); ?></div>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <div class="info-label">Created:</div>
                    <div class="info-value"><?php echo date('M j, Y g:i A', strtotime($record['created_at'])); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Updated:</div>
                    <div class="info-value"><?php echo date('M j, Y g:i A', strtotime($record['updated_at'])); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print
    </button>
</div>
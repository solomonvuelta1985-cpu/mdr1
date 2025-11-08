<?php
/**
 * API: View Communication Line Record Details
 * Simplified minimalist display
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// Get record ID from query parameter
$record_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$record_id) {
    echo '<div class="alert alert-danger">Invalid record ID</div>';
    exit;
}

// Check user permissions
$is_admin = ($_SESSION['user_role'] === 'admin');

if ($is_admin) {
    $stmt = db_query("
        SELECT c.*, u.full_name, u.barangay as user_barangay 
        FROM annex11_communication_lines c 
        LEFT JOIN users u ON c.created_by = u.id 
        WHERE c.id = ?
    ", [$record_id]);
} else {
    $stmt = db_query("
        SELECT * FROM annex11_communication_lines 
        WHERE id = ? AND created_by = ?
    ", [$record_id, $_SESSION['user_id']]);
}

$record = $stmt->fetch();

if (!$record) {
    echo '<div class="alert alert-danger">Record not found or access denied</div>';
    exit;
}

// Log the view action
log_audit_action(
    $_SESSION['user_id'],
    'annex11_view',
    "Viewed record #{$record_id} for {$record['barangay']}: {$record['telecom_provider']}"
);

// Calculate duration if restored
$duration = null;
if (!empty($record['restored_date'])) {
    $interruption = new DateTime($record['interruption_date']);
    $restoration = new DateTime($record['restored_date']);
    $interval = $interruption->diff($restoration);
    
    $duration_parts = [];
    if ($interval->d > 0) $duration_parts[] = $interval->d . 'd';
    if ($interval->h > 0) $duration_parts[] = $interval->h . 'h';
    if ($interval->i > 0) $duration_parts[] = $interval->i . 'm';
    
    $duration = !empty($duration_parts) ? implode(' ', $duration_parts) : '<1m';
}

// Determine status
$status = !empty($record['restored_date']) ? 'Restored' : 'Interrupted';
$status_badge = !empty($record['restored_date']) 
    ? '<span class="badge bg-success">Restored</span>' 
    : '<span class="badge bg-danger">Interrupted</span>';

// Telecom badge
$telecom_badges = [
    'PLDT' => 'primary',
    'Globe' => 'info',
    'Smart' => 'success',
    'DITO' => 'danger',
    'Other' => 'secondary'
];
$telecom_color = $telecom_badges[$record['telecom_provider']] ?? 'secondary';
?>

<style>
    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        color: #6c757d;
        min-width: 180px;
        font-size: 0.9rem;
    }
    .info-value {
        color: #212529;
        flex: 1;
    }
    .section-divider {
        border-top: 2px solid #dee2e6;
        margin: 20px 0;
    }
</style>

<div class="p-3">
    <!-- Record ID & Status -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Record #<?php echo $record['id']; ?></h6>
        <?php echo $status_badge; ?>
    </div>

    <!-- Location -->
    <div class="info-row">
        <div class="info-label">Location</div>
        <div class="info-value">
            <?php echo htmlspecialchars($record['region']); ?>, 
            <?php echo htmlspecialchars($record['province']); ?>, 
            <?php echo htmlspecialchars($record['city']); ?>, 
            <strong><?php echo htmlspecialchars($record['barangay'] ?? $record['user_barangay'] ?? 'Unknown'); ?></strong>
        </div>
    </div>

    <!-- Telecom Provider -->
    <div class="info-row">
        <div class="info-label">Telecom Provider</div>
        <div class="info-value">
            <span class="badge bg-<?php echo $telecom_color; ?>">
                <?php echo htmlspecialchars($record['telecom_provider']); ?>
            </span>
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- Interruption Date -->
    <div class="info-row">
        <div class="info-label">Interruption</div>
        <div class="info-value">
            <i class="fas fa-calendar-times text-danger me-2"></i>
            <?php echo date('M j, Y g:i A', strtotime($record['interruption_date'])); ?>
        </div>
    </div>

    <!-- Restored Date -->
    <div class="info-row">
        <div class="info-label">Restored</div>
        <div class="info-value">
            <?php if (!empty($record['restored_date'])): ?>
                <i class="fas fa-calendar-check text-success me-2"></i>
                <?php echo date('M j, Y g:i A', strtotime($record['restored_date'])); ?>
            <?php else: ?>
                <span class="text-muted">Not yet restored</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($duration): ?>
    <!-- Duration -->
    <div class="info-row">
        <div class="info-label">Duration</div>
        <div class="info-value">
            <strong><?php echo $duration; ?></strong>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($record['remarks'])): ?>
    <div class="section-divider"></div>

    <!-- Remarks -->
    <div class="info-row">
        <div class="info-label">Remarks</div>
        <div class="info-value">
            <?php echo nl2br(htmlspecialchars($record['remarks'])); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="section-divider"></div>

    <!-- Metadata -->
    <?php if ($is_admin && isset($record['full_name'])): ?>
    <div class="info-row">
        <div class="info-label">Created By</div>
        <div class="info-value">
            <?php echo htmlspecialchars($record['full_name']); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="info-row">
        <div class="info-label">Created</div>
        <div class="info-value">
            <?php echo date('M j, Y g:i A', strtotime($record['created_at'])); ?>
        </div>
    </div>

    <div class="info-row">
        <div class="info-label">Last Updated</div>
        <div class="info-value">
            <?php echo date('M j, Y g:i A', strtotime($record['updated_at'])); ?>
        </div>
    </div>
</div>
<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('Record ID required');
}

$record_id = (int)$_GET['id'];
$is_admin = ($_SESSION['user_role'] === 'admin');

// Fetch record - allow viewing archived records
if ($is_admin) {
    $stmt = db_query("
        SELECT a.*, u.full_name 
        FROM annex8_road_bridge_status a 
        LEFT JOIN users u ON a.created_by = u.id 
        WHERE a.id = ?
    ", [$record_id]);
} else {
    $stmt = db_query("
        SELECT * FROM annex8_road_bridge_status 
        WHERE id = ? AND created_by = ?
    ", [$record_id, $_SESSION['user_id']]);
}

$record = $stmt->fetch();

if (!$record) {
    echo '<div class="alert alert-danger">Record not found or access denied.</div>';
    exit;
}

// Log the view action for archived records
if ($record['is_archived']) {
    log_audit_action(
        $_SESSION['user_id'],
        'annex8_view_archived',
        "Viewed archived record #{$record_id} for {$record['barangay']}"
    );
}
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-muted">Location Details</h6>
        <table class="table table-sm">
            <tr><td><strong>Region:</strong></td><td><?= htmlspecialchars($record['region']) ?></td></tr>
            <tr><td><strong>Province:</strong></td><td><?= htmlspecialchars($record['province']) ?></td></tr>
            <tr><td><strong>City/Municipality:</strong></td><td><?= htmlspecialchars($record['city']) ?></td></tr>
            <tr><td><strong>Barangay:</strong></td><td><?= htmlspecialchars($record['barangay']) ?></td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6 class="text-muted">Infrastructure Details</h6>
        <table class="table table-sm">
            <tr><td><strong>Type:</strong></td><td><span class="badge badge-<?= strtolower($record['type']) ?>"><?= htmlspecialchars($record['type']) ?></span></td></tr>
            <tr><td><strong>Classification:</strong></td><td><span class="badge badge-<?= strtolower(explode('/', $record['classification'])[0]) ?>"><?= htmlspecialchars($record['classification']) ?></span></td></tr>
            <tr><td><strong>Name:</strong></td><td><?= htmlspecialchars($record['road_section']) ?></td></tr>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <h6 class="text-muted">Status Details</h6>
        <table class="table table-sm">
            <tr><td><strong>Status:</strong></td>
                <td>
                    <?php
                    $status_class = match($record['status']) {
                        'Passable' => 'badge-passable',
                        'Not Passable' => 'badge-not-passable',
                        'Passable to Heavy Vehicles Only' => 'badge-heavy-only',
                        'Passable to Light Vehicles Only' => 'badge-light-only',
                        default => 'badge-secondary'
                    };
                    ?>
                    <span class="badge <?= $status_class ?>"><?= htmlspecialchars($record['status']) ?></span>
                </td>
            </tr>
            <?php if ($record['date_not_passable']): ?>
            <tr><td><strong>Not Passable Since:</strong></td><td><?= date('M j, Y g:i A', strtotime($record['date_not_passable'])) ?></td></tr>
            <?php endif; ?>
            <?php if ($record['date_passable']): ?>
            <tr><td><strong>Became Passable:</strong></td><td><?= date('M j, Y g:i A', strtotime($record['date_passable'])) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>
    <div class="col-md-6">
        <h6 class="text-muted">Additional Information</h6>
        <table class="table table-sm">
            <tr><td><strong>Remarks:</strong></td><td><?= !empty($record['remarks']) ? htmlspecialchars($record['remarks']) : '<span class="text-muted">-</span>' ?></td></tr>
            <tr><td><strong>Reported By:</strong></td><td><?= htmlspecialchars($record['full_name'] ?? 'Current User') ?></td></tr>
            <tr><td><strong>Created:</strong></td><td><?= date('M j, Y g:i A', strtotime($record['created_at'])) ?></td></tr>
            <tr><td><strong>Last Updated:</strong></td><td><?= date('M j, Y g:i A', strtotime($record['updated_at'])) ?></td></tr>
            <?php if ($record['is_archived']): ?>
            <tr><td><strong>Status:</strong></td><td><span class="badge bg-warning">Archived</span></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php if (!empty($record['image_path'])): ?>
<div class="row mt-3">
    <div class="col-12">
        <h6 class="text-muted">Attached Image</h6>
        <img src="../uploads/<?= htmlspecialchars($record['image_path']) ?>" class="img-fluid rounded" alt="Road/Bridge Image">
    </div>
</div>
<?php endif; ?>
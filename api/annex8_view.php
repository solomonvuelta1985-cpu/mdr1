<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/annex8_status_history.php';

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

// Fetch status history
$status_history = get_status_history($record_id);
?>

<style>
/* Clean, minimal design focused on data readability */
.view-container {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: #333;
}

.section {
    margin-bottom: 24px;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 20px;
}

.section:last-child {
    border-bottom: none;
}

.section-title {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    color: #6b7280;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
    padding-bottom: 6px;
    border-bottom: 2px solid #e5e7eb;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table td {
    padding: 8px 12px;
    border-bottom: 1px solid #f3f4f6;
}

.data-table td:first-child {
    font-weight: 500;
    color: #6b7280;
    width: 200px;
    padding-left: 0;
}

.data-table td:last-child {
    color: #111827;
}

.data-table tr:last-child td {
    border-bottom: none;
}

.status-current {
    display: inline-block;
    padding: 4px 12px;
    background: #f3f4f6;
    border-left: 3px solid #3b82f6;
    font-weight: 500;
}

.status-passable {
    border-left-color: #10b981;
}

.status-not-passable {
    border-left-color: #ef4444;
}

.status-restricted {
    border-left-color: #f59e0b;
}

.image-container {
    margin-top: 12px;
    text-align: center;
}

.image-container img {
    max-width: 100%;
    max-height: 300px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
}

/* History Timeline - Clean Bordered Table */
.history-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
    border: 1px solid #d1d5db;
}

.history-table thead {
    background: #f9fafb;
}

.history-table th {
    padding: 10px 12px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border: 1px solid #d1d5db;
}

.history-table td {
    padding: 12px;
    border: 1px solid #d1d5db;
    vertical-align: top;
}

.history-table tr.current-status {
    background: #fefce8;
}

.history-status-badge {
    display: inline-block;
    padding: 2px 8px;
    font-size: 12px;
    font-weight: 500;
    border-radius: 3px;
    background: #f3f4f6;
    color: #374151;
}

.current-indicator {
    display: inline-block;
    margin-left: 8px;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: 600;
    background: #3b82f6;
    color: white;
    border-radius: 3px;
}

.history-date {
    color: #6b7280;
    font-size: 13px;
}

.history-user {
    color: #6b7280;
    font-size: 13px;
}

.no-data {
    color: #9ca3af;
    font-style: italic;
}

.badge {
    display: inline-block;
    padding: 3px 8px;
    font-size: 12px;
    font-weight: 500;
    border-radius: 3px;
    background: #f3f4f6;
    color: #374151;
}
</style>

<div class="view-container">

    <!-- Location Information -->
    <div class="section">
        <div class="section-title">Location Information</div>
        <table class="data-table">
            <tr>
                <td>Region</td>
                <td><?= htmlspecialchars($record['region']) ?></td>
            </tr>
            <tr>
                <td>Province</td>
                <td><?= htmlspecialchars($record['province']) ?></td>
            </tr>
            <tr>
                <td>City/Municipality</td>
                <td><?= htmlspecialchars($record['city']) ?></td>
            </tr>
            <tr>
                <td>Barangay</td>
                <td><?= htmlspecialchars($record['barangay']) ?></td>
            </tr>
        </table>
    </div>

    <!-- Infrastructure Details -->
    <div class="section">
        <div class="section-title">Infrastructure Details</div>
        <table class="data-table">
            <tr>
                <td>Type</td>
                <td><span class="badge"><?= htmlspecialchars($record['type']) ?></span></td>
            </tr>
            <tr>
                <td>Classification</td>
                <td><span class="badge"><?= htmlspecialchars($record['classification']) ?></span></td>
            </tr>
            <tr>
                <td>Road Section / Bridge Name</td>
                <td><strong><?= htmlspecialchars($record['road_section']) ?></strong></td>
            </tr>
        </table>
    </div>

    <!-- Current Status -->
    <div class="section">
        <div class="section-title">Current Status</div>
        <table class="data-table">
            <tr>
                <td>Status</td>
                <td>
                    <?php
                    $status_class = 'status-current';
                    if ($record['status'] === 'Passable') {
                        $status_class .= ' status-passable';
                    } elseif ($record['status'] === 'Not Passable') {
                        $status_class .= ' status-not-passable';
                    } else {
                        $status_class .= ' status-restricted';
                    }
                    ?>
                    <span class="<?= $status_class ?>"><?= htmlspecialchars($record['status']) ?></span>
                </td>
            </tr>
            <?php if ($record['date_not_passable']): ?>
            <tr>
                <td>Date/Time Not Passable</td>
                <td><?= date('F j, Y - g:i A', strtotime($record['date_not_passable'])) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($record['date_passable']): ?>
            <tr>
                <td>Date/Time Passable</td>
                <td><?= date('F j, Y - g:i A', strtotime($record['date_passable'])) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($record['remarks'])): ?>
            <tr>
                <td>Remarks</td>
                <td><?= nl2br(htmlspecialchars($record['remarks'])) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Record Metadata -->
    <div class="section">
        <div class="section-title">Record Information</div>
        <table class="data-table">
            <tr>
                <td>Reported By</td>
                <td><?= htmlspecialchars($record['full_name'] ?? 'Current User') ?></td>
            </tr>
            <tr>
                <td>Date Created</td>
                <td><?= date('F j, Y - g:i A', strtotime($record['created_at'])) ?></td>
            </tr>
            <tr>
                <td>Last Updated</td>
                <td><?= date('F j, Y - g:i A', strtotime($record['updated_at'])) ?></td>
            </tr>
            <?php if ($record['is_archived']): ?>
            <tr>
                <td>Archive Status</td>
                <td><span class="badge" style="background: #fef3c7; color: #92400e;">Archived</span></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Attached Image -->
    <?php if (!empty($record['image_path'])): ?>
    <div class="section">
        <div class="section-title">Attached Image</div>
        <div class="image-container">
            <img src="../uploads/<?= htmlspecialchars($record['image_path']) ?>"
                 alt="Road/Bridge Image">
        </div>
    </div>
    <?php endif; ?>

    <!-- Status History -->
    <?php if (!empty($status_history)): ?>
    <div class="section">
        <div class="section-title">Status Update History (<?= count($status_history) ?> Record<?= count($status_history) != 1 ? 's' : '' ?>)</div>

        <table class="history-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Status</th>
                    <th style="width: 25%;">Date & Time</th>
                    <th style="width: 30%;">Remarks</th>
                    <th style="width: 15%;">Updated By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($status_history as $index => $history): ?>
                <tr class="<?= $index === 0 ? 'current-status' : '' ?>">
                    <td>
                        <span class="history-status-badge"><?= htmlspecialchars($history['status']) ?></span>
                        <?php if ($index === 0): ?>
                            <span class="current-indicator">CURRENT</span>
                        <?php endif; ?>
                    </td>
                    <td class="history-date">
                        <?= date('M j, Y - g:i A', strtotime($history['status_date'])) ?>
                    </td>
                    <td>
                        <?= !empty($history['remarks']) ? htmlspecialchars($history['remarks']) : '<span class="no-data">No remarks</span>' ?>
                    </td>
                    <td class="history-user">
                        <?= htmlspecialchars($history['changed_by_name'] ?? 'Unknown') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

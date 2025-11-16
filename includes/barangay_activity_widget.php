<?php
/**
 * Barangay Activity Monitor Widget
 * Displays 3-hour activity status for all barangays
 * For admin and staff use only
 */

// Ensure user is admin or staff
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'staff')) {
    return;
}

require_once __DIR__ . '/notifications.php';

// Get barangay activity (3-hour threshold)
$activity = get_barangay_activity(3);

// Calculate statistics
$total_barangays = count($activity);
$overdue_count = 0;
$updated_count = 0;
$no_activity_count = 0;

foreach ($activity as $item) {
    if ($item['last_activity'] === null) {
        $no_activity_count++;
    } elseif ($item['is_overdue']) {
        $overdue_count++;
    } else {
        $updated_count++;
    }
}
?>

<style>
.activity-widget {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 20px;
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e5e7eb;
}

.activity-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.activity-stats {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
}

.stat-card {
    flex: 1;
    padding: 12px;
    border-radius: 6px;
    text-align: center;
}

.stat-card.overdue {
    background: #fef2f2;
    border-left: 4px solid #dc3545;
}

.stat-card.updated {
    background: #f0fdf4;
    border-left: 4px solid #10b981;
}

.stat-card.no-activity {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    padding: 10px 12px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.activity-item:hover {
    background: #f9fafb;
}

.activity-item.overdue {
    background: #fef2f2;
    border-left: 3px solid #dc3545;
}

.activity-item.no-activity {
    background: #fefce8;
}

.barangay-name {
    font-weight: 600;
    color: #111827;
    font-size: 14px;
}

.activity-info {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
}

.activity-status {
    text-align: right;
}

.hours-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.hours-badge.ok {
    background: #d1fae5;
    color: #065f46;
}

.hours-badge.warning {
    background: #fed7aa;
    color: #9a3412;
}

.hours-badge.overdue {
    background: #fecaca;
    color: #991b1b;
}

.activity-time {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 2px;
}
</style>

<div class="activity-widget">
    <div class="activity-header">
        <h4><i class="fas fa-clock me-2"></i>Barangay Activity Monitor (3-Hour Window)</h4>
        <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>

    <div class="activity-stats">
        <div class="stat-card updated">
            <div class="stat-number"><?= $updated_count ?></div>
            <div class="stat-label">Updated (< 3h)</div>
        </div>
        <div class="stat-card overdue">
            <div class="stat-number"><?= $overdue_count ?></div>
            <div class="stat-label">Overdue (> 3h)</div>
        </div>
        <div class="stat-card no-activity">
            <div class="stat-number"><?= $no_activity_count ?></div>
            <div class="stat-label">No Activity</div>
        </div>
    </div>

    <div class="activity-list">
        <?php foreach ($activity as $item): ?>
            <?php
            $item_class = '';
            $badge_class = 'ok';
            $status_text = '';

            if ($item['last_activity'] === null) {
                $item_class = 'no-activity';
                $status_text = 'No activity recorded';
            } elseif ($item['is_overdue']) {
                $item_class = 'overdue';
                $badge_class = 'overdue';
                $status_text = number_format($item['hours_since_update'], 1) . ' hours ago';
            } else {
                $badge_class = $item['hours_since_update'] > 2 ? 'warning' : 'ok';
                $status_text = number_format($item['hours_since_update'], 1) . ' hours ago';
            }
            ?>

            <div class="activity-item <?= $item_class ?>">
                <div class="activity-details">
                    <div class="barangay-name"><?= htmlspecialchars($item['barangay']) ?></div>
                    <?php if ($item['last_activity']): ?>
                        <div class="activity-info">
                            Last update: <?= htmlspecialchars($item['last_annex'] ?? 'Unknown') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="activity-status">
                    <?php if ($item['last_activity']): ?>
                        <div class="hours-badge <?= $badge_class ?>">
                            <?= $status_text ?>
                        </div>
                        <div class="activity-time">
                            <?= date('M j, g:i A', strtotime($item['last_activity'])) ?>
                        </div>
                    <?php else: ?>
                        <div class="hours-badge warning">
                            No Data
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($activity)): ?>
            <div style="padding: 40px 20px; text-align: center; color: #9ca3af;">
                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.3;"></i>
                <p>No barangays found</p>
            </div>
        <?php endif; ?>
    </div>
</div>

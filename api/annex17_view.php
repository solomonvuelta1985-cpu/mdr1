<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$record_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$record_id) {
    echo '<div class="alert alert-danger">Invalid record ID</div>';
    exit;
}

// Fetch record
$is_admin = ($_SESSION['user_role'] === 'admin');

if ($is_admin) {
    $sql = "SELECT a.*, u.full_name
            FROM annex17_preemptive_evacuation a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.id = ?";
    $stmt = db_query($sql, [$record_id]);
} else {
    $sql = "SELECT a.*, u.full_name
            FROM annex17_preemptive_evacuation a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.id = ? AND a.created_by = ?";
    $stmt = db_query($sql, [$record_id, $_SESSION['user_id']]);
}

$record = $stmt->fetch();

if (!$record) {
    echo '<div class="alert alert-warning">Record not found or access denied.</div>';
    exit;
}

// Log the view action
log_audit_action(
    $_SESSION['user_id'],
    'annex17_view',
    "Viewed pre-emptive evacuation record #{$record_id} for {$record['barangay']}"
);
?>

<style>
    .table-view-container {
        padding: clamp(15px, 3vw, 20px);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: white;
    }

    .view-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 6px;
        padding: clamp(12px, 2.5vw, 16px);
        margin-bottom: clamp(15px, 3vw, 20px);
        border-left: 4px solid #0d6efd;
    }

    .view-title {
        font-size: clamp(1.1rem, 3vw, 1.3rem);
        font-weight: 600;
        color: #212529;
        margin-bottom: 4px;
    }

    .view-subtitle {
        font-size: clamp(0.8rem, 2.2vw, 0.9rem);
        color: #6c757d;
        margin: 0;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: clamp(0.8rem, 2.2vw, 0.875rem);
        margin-bottom: clamp(15px, 3vw, 20px);
    }

    .table-section-header {
        background-color: #0d6efd;
        color: white;
        border: 1px solid #0d6efd;
        padding: clamp(10px, 2.5vw, 12px) clamp(12px, 2.5vw, 15px);
        font-weight: 600;
        font-size: clamp(0.9rem, 2.5vw, 1rem);
    }

    .data-row {
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
    }

    .data-label {
        width: 35%;
        padding: clamp(10px, 2.5vw, 12px) clamp(12px, 2.5vw, 15px);
        background-color: #f8f9fa;
        font-weight: 500;
        color: #495057;
        border-right: 1px solid #dee2e6;
        vertical-align: top;
    }

    .data-value {
        padding: clamp(10px, 2.5vw, 12px) clamp(12px, 2.5vw, 15px);
        color: #212529;
        vertical-align: top;
        background: white;
    }

    .number-display {
        font-weight: 600;
        color: #0d6efd;
    }
</style>

<div class="table-view-container">
    <div class="view-header">
        <h2 class="view-title">Pre-emptive Evacuation Record #<?= $record_id ?></h2>
        <p class="view-subtitle">Created: <?= date('F d, Y h:i A', strtotime($record['created_at'])) ?></p>
    </div>

    <!-- Basic Information -->
    <table class="data-table">
        <tr>
            <td colspan="2" class="table-section-header">Basic Information</td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Region</td>
            <td class="data-value"><?= htmlspecialchars($record['region']) ?></td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Province</td>
            <td class="data-value"><?= htmlspecialchars($record['province']) ?></td>
        </tr>
        <tr class="data-row">
            <td class="data-label">City/Municipality</td>
            <td class="data-value"><?= htmlspecialchars($record['city_municipality']) ?></td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Barangay</td>
            <td class="data-value"><strong><?= htmlspecialchars($record['barangay']) ?></strong></td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Number of Families</td>
            <td class="data-value"><span class="number-display"><?= number_format($record['families']) ?></span></td>
        </tr>
    </table>

    <!-- Totals Summary -->
    <table class="data-table">
        <tr>
            <td colspan="2" class="table-section-header">Summary Totals</td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Total Persons (Cumulative)</td>
            <td class="data-value"><span class="number-display"><?= number_format($record['total_cumulative']) ?></span></td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Total Persons (Current)</td>
            <td class="data-value"><span class="number-display"><?= number_format($record['total_current']) ?></span></td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Male (Cumulative / Current)</td>
            <td class="data-value">
                <span class="number-display"><?= number_format($record['total_male_cumulative']) ?></span> /
                <span class="number-display"><?= number_format($record['total_male_current']) ?></span>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Female (Cumulative / Current)</td>
            <td class="data-value">
                <span class="number-display"><?= number_format($record['total_female_cumulative']) ?></span> /
                <span class="number-display"><?= number_format($record['total_female_current']) ?></span>
            </td>
        </tr>
    </table>

    <!-- Age Distribution -->
    <table class="data-table">
        <tr>
            <td colspan="2" class="table-section-header">Age Distribution (Cumulative / Current)</td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Infant 0-6 months</td>
            <td class="data-value">
                Male: <?= number_format($record['infant_male_cumulative']) ?> / <?= number_format($record['infant_male_current']) ?> |
                Female: <?= number_format($record['infant_female_cumulative']) ?> / <?= number_format($record['infant_female_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Toddlers 7mo-2yo</td>
            <td class="data-value">
                Male: <?= number_format($record['toddler_male_cumulative']) ?> / <?= number_format($record['toddler_male_current']) ?> |
                Female: <?= number_format($record['toddler_female_cumulative']) ?> / <?= number_format($record['toddler_female_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Preschooler 3-5yo</td>
            <td class="data-value">
                Male: <?= number_format($record['preschool_male_cumulative']) ?> / <?= number_format($record['preschool_male_current']) ?> |
                Female: <?= number_format($record['preschool_female_cumulative']) ?> / <?= number_format($record['preschool_female_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">School Age 6-12yo</td>
            <td class="data-value">
                Male: <?= number_format($record['school_male_cumulative']) ?> / <?= number_format($record['school_male_current']) ?> |
                Female: <?= number_format($record['school_female_cumulative']) ?> / <?= number_format($record['school_female_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Teenage 13-17yo</td>
            <td class="data-value">
                Male: <?= number_format($record['teenage_male_cumulative']) ?> / <?= number_format($record['teenage_male_current']) ?> |
                Female: <?= number_format($record['teenage_female_cumulative']) ?> / <?= number_format($record['teenage_female_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Adult 18-59yo</td>
            <td class="data-value">
                Male: <?= number_format($record['adult_male_cumulative']) ?> / <?= number_format($record['adult_male_current']) ?> |
                Female: <?= number_format($record['adult_female_cumulative']) ?> / <?= number_format($record['adult_female_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Elderly 60+ yo</td>
            <td class="data-value">
                Male: <?= number_format($record['elderly_male_cumulative']) ?> / <?= number_format($record['elderly_male_current']) ?> |
                Female: <?= number_format($record['elderly_female_cumulative']) ?> / <?= number_format($record['elderly_female_current']) ?>
            </td>
        </tr>
    </table>

    <!-- Vulnerable Groups -->
    <table class="data-table">
        <tr>
            <td colspan="2" class="table-section-header">Vulnerable Groups (Cumulative / Current)</td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Pregnant/Lactating Mothers</td>
            <td class="data-value">
                <?= number_format($record['pregnant_lactating_cumulative']) ?> / <?= number_format($record['pregnant_lactating_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Child-Headed Family</td>
            <td class="data-value">
                Male: <?= number_format($record['child_headed_male_cumulative']) ?> / <?= number_format($record['child_headed_male_current']) ?> |
                Female: <?= number_format($record['child_headed_female_cumulative']) ?> / <?= number_format($record['child_headed_female_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Single-Headed Family</td>
            <td class="data-value">
                Male: <?= number_format($record['single_headed_male_cumulative']) ?> / <?= number_format($record['single_headed_male_current']) ?> |
                Female: <?= number_format($record['single_headed_female_cumulative']) ?> / <?= number_format($record['single_headed_female_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Solo Parent</td>
            <td class="data-value">
                Male: <?= number_format($record['solo_parent_male_cumulative']) ?> / <?= number_format($record['solo_parent_male_current']) ?> |
                Female: <?= number_format($record['solo_parent_female_cumulative']) ?> / <?= number_format($record['solo_parent_female_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Persons with Disability (PWDs)</td>
            <td class="data-value">
                Male: <?= number_format($record['pwd_male_cumulative']) ?> / <?= number_format($record['pwd_male_current']) ?> |
                Female: <?= number_format($record['pwd_female_cumulative']) ?> / <?= number_format($record['pwd_female_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Indigenous People (IPs)</td>
            <td class="data-value">
                Male: <?= number_format($record['ip_male_cumulative']) ?> / <?= number_format($record['ip_male_current']) ?> |
                Female: <?= number_format($record['ip_female_cumulative']) ?> / <?= number_format($record['ip_female_current']) ?>
            </td>
        </tr>
        <tr class="data-row">
            <td class="data-label">4P's Beneficiaries</td>
            <td class="data-value">
                Male: <?= number_format($record['fourps_male_cumulative']) ?> / <?= number_format($record['fourps_male_current']) ?> |
                Female: <?= number_format($record['fourps_female_cumulative']) ?> / <?= number_format($record['fourps_female_current']) ?>
            </td>
        </tr>
        <?php if ($record['vulnerable_groups_resolution']): ?>
        <tr class="data-row">
            <td class="data-label">Resolution Number</td>
            <td class="data-value"><?= htmlspecialchars($record['vulnerable_groups_resolution']) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- Additional Information -->
    <table class="data-table">
        <tr>
            <td colspan="2" class="table-section-header">Additional Information</td>
        </tr>
        <?php if ($record['remarks']): ?>
        <tr class="data-row">
            <td class="data-label">Remarks</td>
            <td class="data-value"><?= nl2br(htmlspecialchars($record['remarks'])) ?></td>
        </tr>
        <?php endif; ?>
        <tr class="data-row">
            <td class="data-label">Created By</td>
            <td class="data-value"><?= htmlspecialchars($record['full_name'] ?? 'N/A') ?></td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Created At</td>
            <td class="data-value"><?= date('F d, Y h:i A', strtotime($record['created_at'])) ?></td>
        </tr>
        <tr class="data-row">
            <td class="data-label">Last Updated</td>
            <td class="data-value"><?= date('F d, Y h:i A', strtotime($record['updated_at'])) ?></td>
        </tr>
    </table>
</div>

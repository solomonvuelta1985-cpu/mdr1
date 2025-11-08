<?php
/**
 * API Handler: View Affected Population Record Details (Annex 2)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// RATE LIMITING: Prevent view spam
$user_id = $_SESSION['user_id'] ?? 0;
if (!check_rate_limit($user_id, 'annex2_view', 50, 3600)) {
    log_security_event($user_id, 'rate_limit_exceeded', 'Annex2 view rate limit exceeded');
    echo '<div class="alert alert-danger">Too many requests. Please try again later.</div>';
    exit;
}

// Get record ID
$record_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$record_id) {
    echo '<div class="alert alert-danger">Invalid record ID</div>';
    log_security_event($_SESSION['user_id'], 'invalid_input', 'Invalid record ID for Annex2 view');
    exit;
}

// Get user info
$is_admin = ($_SESSION['user_role'] === 'admin');

// Fetch record
try {
    if ($is_admin) {
        $sql = "SELECT a.*, u.full_name 
                FROM annex2_affected_population a 
                LEFT JOIN users u ON a.created_by = u.id 
                WHERE a.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id]);
    } else {
        $sql = "SELECT a.*, u.full_name 
                FROM annex2_affected_population a 
                LEFT JOIN users u ON a.created_by = u.id 
                WHERE a.id = ? AND a.created_by = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id, $_SESSION['user_id']]);
    }

    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        echo '<div class="alert alert-warning">Record not found or access denied.</div>';
        log_security_event($_SESSION['user_id'], 'unauthorized_access', "Attempted to view Annex2 record #{$record_id} without permission");
        exit;
    }
    
    // AUDIT LOG: Record view
    log_audit_action(
        $_SESSION['user_id'],
        'annex2_view',
        "Viewed affected population record #{$record_id} for {$record['barangay']}"
    );
    
    // SECURITY LOG: Record view
    log_security_event(
        $_SESSION['user_id'],
        'record_view',
        "Viewed Annex 2 record #{$record_id}"
    );
    
} catch (PDOException $e) {
    error_log("Annex2 View Error: " . $e->getMessage());
    echo '<div class="alert alert-danger">Unable to load record. Please try again.</div>';
    exit;
}
?>

<style>
    .view-container {
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .record-header {
        text-align: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #dee2e6;
    }
    .record-header h5 {
        color: #212529;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 1.25rem;
    }
    .record-header .location {
        color: #6c757d;
        font-size: 0.95rem;
    }
    .view-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        overflow: hidden;
    }
    .view-table thead {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    .view-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    .view-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #dee2e6;
        font-size: 0.875rem;
    }
    .view-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    .view-table tbody tr:hover {
        background-color: #e9ecef;
    }
    .view-table tbody tr:last-child td {
        border-bottom: none;
    }
    .section-title {
        background-color: #f8f9fa;
        color: #495057;
        padding: 10px 15px;
        font-weight: 600;
        font-size: 0.95rem;
        margin: 25px 0 0 0;
        border-left: 4px solid #0d6efd;
        border-radius: 4px;
    }
    .label-col {
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
        width: 45%;
    }
    .value-col {
        color: #212529;
    }
    .highlight-value {
        font-weight: 700;
        color: #198754;
        font-size: 1rem;
    }
    .remarks-box {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 12px 15px;
        margin-top: 10px;
        border-radius: 4px;
        color: #664d03;
        font-size: 0.875rem;
    }
</style>

<div class="view-container">
    <!-- Header -->
    <div class="record-header">
        <h5>Affected Population Report #<?php echo $record['id']; ?></h5>
        <div class="location">
            <?php echo htmlspecialchars($record['barangay']); ?>, 
            <?php echo htmlspecialchars($record['city']); ?>, 
            <?php echo htmlspecialchars($record['province']); ?>
        </div>
    </div>

    <!-- Location Information -->
    <div class="section-title">📍 Location Information</div>
    <table class="view-table">
        <tbody>
            <tr>
                <td class="label-col">Region</td>
                <td class="value-col"><?php echo htmlspecialchars($record['region']); ?></td>
            </tr>
            <tr>
                <td class="label-col">Province</td>
                <td class="value-col"><?php echo htmlspecialchars($record['province']); ?></td>
            </tr>
            <tr>
                <td class="label-col">City/Municipality</td>
                <td class="value-col"><?php echo htmlspecialchars($record['city']); ?></td>
            </tr>
            <tr>
                <td class="label-col">Barangay</td>
                <td class="value-col"><?php echo htmlspecialchars($record['barangay']); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Affected Population -->
    <div class="section-title">👥 Affected Population</div>
    <table class="view-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Cumulative</th>
                <th>Current</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label-col">Affected Families</td>
                <td><?php echo number_format($record['affected_families_cumulative'] ?? 0); ?></td>
                <td><?php echo number_format($record['affected_families_current'] ?? 0); ?></td>
            </tr>
            <tr>
                <td class="label-col">Affected Persons</td>
                <td><?php echo number_format($record['affected_persons_cumulative'] ?? 0); ?></td>
                <td><?php echo number_format($record['affected_persons_current'] ?? 0); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Evacuation Centers -->
    <div class="section-title">🏢 Evacuation Centers</div>
    <table class="view-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Cumulative</th>
                <th>Current</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label-col">Number of Evacuation Centers</td>
                <td><?php echo number_format($record['num_ecs_cumulative'] ?? 0); ?></td>
                <td><?php echo number_format($record['num_ecs_current'] ?? 0); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Displaced Population -->
    <div class="section-title">🚶 Displaced Population (Inside & Outside ECs)</div>
    <table class="view-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Cumulative</th>
                <th>Current</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label-col">Inside EC - Families</td>
                <td><?php echo number_format($record['inside_families_cumulative'] ?? 0); ?></td>
                <td><?php echo number_format($record['inside_families_current'] ?? 0); ?></td>
            </tr>
            <tr>
                <td class="label-col">Inside EC - Persons</td>
                <td><?php echo number_format($record['inside_persons_cumulative'] ?? 0); ?></td>
                <td><?php echo number_format($record['inside_persons_current'] ?? 0); ?></td>
            </tr>
            <tr>
                <td class="label-col">Outside EC - Families</td>
                <td><?php echo number_format($record['outside_families_cumulative'] ?? 0); ?></td>
                <td><?php echo number_format($record['outside_families_current'] ?? 0); ?></td>
            </tr>
            <tr>
                <td class="label-col">Outside EC - Persons</td>
                <td><?php echo number_format($record['outside_persons_cumulative'] ?? 0); ?></td>
                <td><?php echo number_format($record['outside_persons_current'] ?? 0); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Total Displaced -->
    <div class="section-title">📊 Total Displaced (Summary)</div>
    <table class="view-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Cumulative</th>
                <th>Current</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label-col"><strong>TOTAL Families</strong></td>
                <td class="highlight-value"><?php echo number_format($record['total_families_cumulative'] ?? 0); ?></td>
                <td><?php echo number_format($record['total_families_current'] ?? 0); ?></td>
            </tr>
            <tr>
                <td class="label-col"><strong>TOTAL Persons</strong></td>
                <td class="highlight-value"><?php echo number_format($record['total_persons_cumulative'] ?? 0); ?></td>
                <td><?php echo number_format($record['total_persons_current'] ?? 0); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Remarks -->
    <?php if (!empty($record['remarks'])): ?>
    <div class="section-title">📝 Remarks</div>
    <div class="remarks-box">
        <?php echo nl2br(htmlspecialchars($record['remarks'])); ?>
    </div>
    <?php endif; ?>

    <!-- Record Information -->
    <div class="section-title">ℹ️ Record Information</div>
    <table class="view-table">
        <tbody>
            <?php if (isset($record['full_name'])): ?>
            <tr>
                <td class="label-col">Created By</td>
                <td class="value-col"><?php echo htmlspecialchars($record['full_name']); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="label-col">Created Date</td>
                <td class="value-col"><?php echo date('F j, Y g:i A', strtotime($record['created_at'])); ?></td>
            </tr>
            <tr>
                <td class="label-col">Last Updated</td>
                <td class="value-col"><?php echo date('F j, Y g:i A', strtotime($record['updated_at'])); ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print
    </button>
</div>
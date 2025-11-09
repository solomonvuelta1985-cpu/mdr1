<?php
/**
 * NDRRMC Annex 1: Related Incident - Print Template
 * Landscape format matching official NDRRMC template
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is logged in
require_login();

// Get current user info
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] === 'admin');

// Get all records
if ($is_admin) {
    $stmt = db_query("
        SELECT a.*, u.full_name, u.username, u.barangay as user_barangay
        FROM annex1_related_incidents a
        LEFT JOIN users u ON a.created_by = u.id
        WHERE a.is_archived = 0
        ORDER BY a.occurrence_date DESC, a.created_at DESC
    ");
} else {
    $stmt = db_query("
        SELECT * FROM annex1_related_incidents
        WHERE created_by = ? AND is_archived = 0
        ORDER BY occurrence_date DESC, created_at DESC
    ", [$user_id]);
}

$records = $stmt->fetchAll();
$current_date = date('F d, Y');
$memo_number = "OC-___";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NDRRMC Annex 1 - Related Incident</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.2;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 11pt;
            font-weight: bold;
        }
        .memo-info {
            font-size: 9pt;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
            font-size: 9pt;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .col-region { width: 8%; }
        .col-province { width: 9%; }
        .col-city { width: 9%; }
        .col-barangay { width: 9%; }
        .col-type { width: 9%; }
        .col-date { width: 10%; }
        .col-description { width: 15%; }
        .col-actions { width: 15%; }
        .col-remarks { width: 16%; }
    </style>
</head>
<body>
    <div class="header">
        <h1>NDRRMC Memorandum Circular No. <?php echo htmlspecialchars($memo_number); ?>, <?php echo date('Y'); ?> re NDRRMC Reporting Templates</h1>
        <h2>Annex 1: Related Incident</h2>
    </div>

    <div class="memo-info">
        <strong>Date Generated:</strong> <?php echo $current_date; ?><br>
        <strong>Total Records:</strong> <?php echo count($records); ?>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-region">Region</th>
                <th class="col-province">Province</th>
                <th class="col-city">City / Municipality</th>
                <th class="col-barangay">Barangay</th>
                <th class="col-type">Type of Incident</th>
                <th class="col-date">Date / time of occurrence</th>
                <th class="col-description">Description</th>
                <th class="col-actions">Actions Taken</th>
                <th class="col-remarks">Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
            <tr>
                <td><?php echo htmlspecialchars($record['region']); ?></td>
                <td><?php echo htmlspecialchars($record['province']); ?></td>
                <td><?php echo htmlspecialchars($record['city']); ?></td>
                <td><?php echo htmlspecialchars($record['barangay']); ?></td>
                <td><?php echo htmlspecialchars($record['incident_type']); ?></td>
                <td><?php echo date('M d, Y h:i A', strtotime($record['occurrence_date'])); ?></td>
                <td><?php echo htmlspecialchars($record['description'] ?: ''); ?></td>
                <td><?php echo htmlspecialchars($record['actions_taken'] ?: ''); ?></td>
                <td><?php echo htmlspecialchars($record['remarks'] ?: ''); ?></td>
            </tr>
            <?php endforeach; ?>

            <?php if (count($records) === 0): ?>
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">No records found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // Auto-trigger browser print dialog
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

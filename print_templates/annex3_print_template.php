<?php
/**
 * NDRRMC Annex 3: Casualties - Print Template
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
        FROM annex3_casualties a
        LEFT JOIN users u ON a.created_by = u.id
        WHERE a.is_archived = 0
        ORDER BY a.region, a.province, a.city
    ");
} else {
    $stmt = db_query("
        SELECT * FROM annex3_casualties
        WHERE created_by = ? AND is_archived = 0
        ORDER BY region, province, city
    ", [$user_id]);
}

$records = $stmt->fetchAll();
$current_date = date('F d, Y');
$memo_number = "05";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NDRRMC Annex 3 - Casualties</title>
    <style>
        @page {
            size: 8.5in 13in landscape;
            margin: 0.75in 0.5in;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: none;
            padding-bottom: 0;
        }
        .header h1 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 4px;
            line-height: 1.2;
        }
        .header h2 {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 2px;
        }
        .memo-info {
            font-size: 9pt;
            margin: 15px 0 20px 0;
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-top: 5px;
            table-layout: auto;
        }
        th, td {
            border: 0.5px solid #000;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 9pt;
            word-wrap: break-word;
            line-height: 1.3;
        }
        th {
            background-color: white;
            font-weight: bold;
            text-align: center;
        }

        /* Column headers */
        th {
            font-size: 8pt;
            padding: 4px 3px;
        }

        .text-left {
            text-align: left;
            padding-left: 6px;
            font-size: 9pt;
        }

        .text-center {
            text-align: center;
            font-size: 9pt;
        }

        /* Print specific styles */
        @media print {
            body {
                padding: 10px;
            }
            .no-print {
                display: none !important;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>NDRRMC Memorandum Circular No. <?php echo htmlspecialchars($memo_number); ?>, s. 2025 re NDRRMC Reporting Templates</h1>
        <h2>Annex 3: Casualties</h2>
    </div>

    <div class="memo-info">
        <div>
            <strong>Date Generated:</strong> <?php echo $current_date; ?>
        </div>
        <div>
            <strong>Total Records:</strong> <?php echo count($records); ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Region</th>
                <th>Province</th>
                <th>City /<br>Municipality</th>
                <th>Barangay</th>
                <th>Category<br>(dead, injured,<br>ill, or missing)</th>
                <th>Surname</th>
                <th>First<br>Name</th>
                <th>Middle<br>Name</th>
                <th>Age</th>
                <th>Sex</th>
                <th>Address</th>
                <th>Cause</th>
                <th>Remarks</th>
                <th>Source<br>of Data</th>
                <th>Validated?<br>(yes or no)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
            <tr>
                <td class="text-left"><?php echo htmlspecialchars($record['region'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['province'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['city'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['barangay'] ?: ''); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($record['category'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['surname'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['first_name'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['middle_name'] ?: ''); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($record['age'] ?: ''); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($record['sex'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['address'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['cause'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['remarks'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['source'] ?: ''); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($record['validated'] ?: ''); ?></td>
            </tr>
            <?php endforeach; ?>

            <?php if (count($records) === 0): ?>
            <tr>
                <td colspan="15" style="text-align: center; padding: 20px;">No records found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Summary Section -->
    <?php if (count($records) > 0): ?>
    <div style="margin-top: 25px; font-size: 9pt; line-height: 1.6;">
        <strong>Summary:</strong><br>
        <?php
        $total_dead = 0;
        $total_injured = 0;
        $total_ill = 0;
        $total_missing = 0;
        $total_validated = 0;

        foreach ($records as $record) {
            switch ($record['category']) {
                case 'Dead': $total_dead++; break;
                case 'Injured': $total_injured++; break;
                case 'Ill': $total_ill++; break;
                case 'Missing': $total_missing++; break;
            }
            if ($record['validated'] === 'Yes') {
                $total_validated++;
            }
        }
        ?>
        Total Dead: <strong><?php echo $total_dead; ?></strong> &nbsp;|&nbsp;
        Total Injured: <strong><?php echo $total_injured; ?></strong> &nbsp;|&nbsp;
        Total Ill: <strong><?php echo $total_ill; ?></strong> &nbsp;|&nbsp;
        Total Missing: <strong><?php echo $total_missing; ?></strong><br>
        Total Validated: <strong><?php echo $total_validated; ?></strong> out of <strong><?php echo count($records); ?></strong> records
    </div>
    <?php endif; ?>

    <div class="memo-info no-print" style="margin-top: 20px; font-style: italic;">
        <p>This document was automatically generated by the NDRRMC Reporting System on <?php echo $current_date; ?></p>
    </div>

    <script>
        // Auto-trigger browser print dialog
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };

        // Optional: Close window after printing
        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
</body>
</html>

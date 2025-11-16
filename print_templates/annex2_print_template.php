<?php
/**
 * NDRRMC Annex 2: Affected Population - Print Template
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
        FROM annex2_affected_population a
        LEFT JOIN users u ON a.created_by = u.id
        WHERE a.is_archived = 0
        ORDER BY a.region, a.province, a.city
    ");
} else {
    $stmt = db_query("
        SELECT * FROM annex2_affected_population
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
    <title>NDRRMC Annex 2 - Affected Population</title>
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

        /* First row headers - main categories */
        th[rowspan="3"] {
            font-size: 8pt;
            padding: 4px 2px;
            min-width: 50px;
        }

        th[rowspan="2"] {
            font-size: 8pt;
            padding: 4px 2px;
        }

        /* Second row headers */
        th[colspan="4"], th[colspan="2"] {
            font-size: 8pt;
            padding: 3px 2px;
        }

        /* Sub-headers (Cumulative/Current) */
        .sub-header {
            font-size: 8pt;
            font-weight: normal;
            text-align: center;
            padding: 3px 2px;
        }

        .numeric {
            text-align: center;
            font-weight: normal;
            font-size: 9pt;
            padding: 4px 3px;
        }
        .text-left {
            text-align: left;
            padding-left: 6px;
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
        <h2>Annex 2: Affected Population</h2>
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
                <th class="col-region" rowspan="3">Region</th>
                <th class="col-province" rowspan="3">Province</th>
                <th class="col-city" rowspan="3">City /<br>Municipality</th>
                <th class="col-barangay" rowspan="3">Barangay</th>
                <th class="col-families" rowspan="2" colspan="2">Affected<br>Families</th>
                <th class="col-persons" rowspan="2" colspan="2">Affected<br>Persons</th>
                <th class="col-ec" rowspan="2" colspan="2">No of<br>ECs</th>
                <th colspan="4">Inside ECs</th>
                <th colspan="4">Outside ECs</th>
                <th colspan="4">Total Displaced</th>
                <th class="col-remarks" rowspan="3">Remarks</th>
            </tr>
            <tr>
                <!-- Second header row for Inside/Outside/Total -->
                <th colspan="2">Families</th>
                <th colspan="2">Persons</th>
                <th colspan="2">Families</th>
                <th colspan="2">Persons</th>
                <th colspan="2">Families</th>
                <th colspan="2">Persons</th>
            </tr>
            <tr>
                <!-- Third header row for Cumulative/Current -->
                <th class="sub-header">Cumulative</th>
                <th class="sub-header">Current</th>
                <th class="sub-header">Cumulative</th>
                <th class="sub-header">Current</th>
                <th class="sub-header">Cumulative</th>
                <th class="sub-header">Current</th>
                <th class="sub-header">Cumulative</th>
                <th class="sub-header">Current</th>
                <th class="sub-header">Cumulative</th>
                <th class="sub-header">Current</th>
                <th class="sub-header">Cumulative</th>
                <th class="sub-header">Current</th>
                <th class="sub-header">Cumulative</th>
                <th class="sub-header">Current</th>
                <th class="sub-header">Cumulative</th>
                <th class="sub-header">Current</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
            <tr>
                <td class="text-left"><?php echo htmlspecialchars($record['region'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['province'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['city'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['barangay'] ?: ''); ?></td>
                <td class="numeric"><?php echo number_format($record['affected_families_cumulative'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['affected_families_current'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['affected_persons_cumulative'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['affected_persons_current'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['num_ecs_cumulative'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['num_ecs_current'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['inside_families_cumulative'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['inside_families_current'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['inside_persons_cumulative'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['inside_persons_current'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['outside_families_cumulative'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['outside_families_current'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['outside_persons_cumulative'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['outside_persons_current'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['total_families_cumulative'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['total_families_current'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['total_persons_cumulative'] ?: 0); ?></td>
                <td class="numeric"><?php echo number_format($record['total_persons_current'] ?: 0); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['remarks'] ?: ''); ?></td>
            </tr>
            <?php endforeach; ?>

            <?php if (count($records) === 0): ?>
            <tr>
                <td colspan="23" style="text-align: center; padding: 20px;">No records found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Summary Section -->
    <?php if (count($records) > 0): ?>
    <div style="margin-top: 25px; font-size: 9pt; line-height: 1.6;">
        <strong>Summary:</strong><br>
        <?php
        $total_families_cum = array_sum(array_column($records, 'affected_families_cumulative'));
        $total_families_cur = array_sum(array_column($records, 'affected_families_current'));
        $total_persons_cum = array_sum(array_column($records, 'affected_persons_cumulative'));
        $total_persons_cur = array_sum(array_column($records, 'affected_persons_current'));
        $total_ecs_cum = array_sum(array_column($records, 'num_ecs_cumulative'));
        $total_ecs_cur = array_sum(array_column($records, 'num_ecs_current'));
        $total_displaced_families = array_sum(array_column($records, 'total_families_cumulative'));
        $total_displaced_persons = array_sum(array_column($records, 'total_persons_cumulative'));
        ?>
        Total Affected Families (Cumulative): <strong><?php echo number_format($total_families_cum); ?></strong> &nbsp;|&nbsp;
        Total Affected Families (Current): <strong><?php echo number_format($total_families_cur); ?></strong><br>
        Total Affected Persons (Cumulative): <strong><?php echo number_format($total_persons_cum); ?></strong> &nbsp;|&nbsp;
        Total Affected Persons (Current): <strong><?php echo number_format($total_persons_cur); ?></strong><br>
        Total Displaced Persons: <strong><?php echo number_format($total_displaced_persons); ?></strong>
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
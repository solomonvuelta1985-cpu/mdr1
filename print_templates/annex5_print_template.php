<?php
/**
 * NDRRMC Annex 5: Damage and Losses to Agriculture - Print Template
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
        FROM annex5_agriculture_damage a
        LEFT JOIN users u ON a.created_by = u.id
        WHERE a.is_archived = 0
        ORDER BY a.region, a.province, a.city
    ");
} else {
    $stmt = db_query("
        SELECT * FROM annex5_agriculture_damage
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
    <title>NDRRMC Annex 5 - Damage and Losses to Agriculture</title>
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
            padding: 5px 3px;
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
            padding: 3px 2px;
        }

        .text-left {
            text-align: left;
            padding-left: 5px;
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
        <h2>Annex 5: Damage and Losses to Agriculture</h2>
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
                <th rowspan="2">Region</th>
                <th rowspan="2">Province</th>
                <th rowspan="2">City /<br>Municipality</th>
                <th rowspan="2">Barangay</th>
                <th rowspan="2">Classification</th>
                <th rowspan="2">Type</th>
                <th rowspan="2">Number of<br>fisherfolks or<br>farmers affected</th>
                <th colspan="3">Affected crop area (ha)</th>
                <th rowspan="2">Production<br>Loss in<br>volume<br>(MT)</th>
                <th rowspan="2">Number of<br>heads<br>(livestock<br>and poultry)</th>
                <th colspan="3">Number of damaged<br>infrastructures, machineries<br>and equipment</th>
                <th rowspan="2">Production<br>loss / cost<br>damage in<br>value</th>
            </tr>
            <tr>
                <th>With no<br>chance of<br>recovery</th>
                <th>With<br>chance of<br>recovery</th>
                <th>Total</th>
                <th>Totally</th>
                <th>Partially</th>
                <th>total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
            <tr>
                <td class="text-left"><?php echo htmlspecialchars($record['region'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['province'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['city'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['barangay'] ?: $record['user_barangay'] ?: ''); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($record['classification'] ?: ''); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($record['type'] ?: ''); ?></td>
                <td class="text-center"><?php echo number_format($record['affected_people'] ?: 0); ?></td>
                <td class="text-center"><?php echo number_format($record['no_recovery_area'] ?: 0, 2); ?></td>
                <td class="text-center"><?php echo number_format($record['with_recovery_area'] ?: 0, 2); ?></td>
                <td class="text-center"><?php echo number_format($record['total_crop_area'] ?: 0, 2); ?></td>
                <td class="text-center"><?php echo number_format($record['production_loss_volume'] ?: 0, 2); ?></td>
                <td class="text-center"><?php echo number_format($record['animal_heads'] ?: 0); ?></td>
                <td class="text-center"><?php echo number_format($record['totally_damaged'] ?: 0); ?></td>
                <td class="text-center"><?php echo number_format($record['partially_damaged'] ?: 0); ?></td>
                <td class="text-center"><?php echo number_format($record['total_damaged'] ?: 0); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['damage_value'] ?: ''); ?></td>
            </tr>
            <?php endforeach; ?>

            <?php if (count($records) === 0): ?>
            <tr>
                <td colspan="16" style="text-align: center; padding: 20px;">No records found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Summary Section -->
    <?php if (count($records) > 0): ?>
    <div style="margin-top: 25px; font-size: 9pt; line-height: 1.6;">
        <strong>Summary:</strong><br>
        <?php
        $total_affected_people = array_sum(array_column($records, 'affected_people'));
        $total_crop_area = array_sum(array_column($records, 'total_crop_area'));
        $total_production_loss = array_sum(array_column($records, 'production_loss_volume'));
        $total_animal_heads = array_sum(array_column($records, 'animal_heads'));
        $total_infrastructure_damaged = array_sum(array_column($records, 'total_damaged'));
        ?>
        Total Affected People: <strong><?php echo number_format($total_affected_people); ?></strong> &nbsp;|&nbsp;
        Total Crop Area Affected: <strong><?php echo number_format($total_crop_area, 2); ?> ha</strong><br>
        Total Production Loss: <strong><?php echo number_format($total_production_loss, 2); ?> MT</strong> &nbsp;|&nbsp;
        Total Animal Heads: <strong><?php echo number_format($total_animal_heads); ?></strong><br>
        Total Infrastructure Damaged: <strong><?php echo number_format($total_infrastructure_damaged); ?></strong>
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

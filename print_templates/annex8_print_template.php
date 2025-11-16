<?php
/**
 * NDRRMC Annex 8: Status of Roads and Bridges - Print Template
 * Landscape format matching official NDRRMC template
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/annex8_status_history.php';

// Check if user is logged in
require_login();

// Get current user info
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] === 'admin');

// Get all records
if ($is_admin) {
    $stmt = db_query("
        SELECT a.*, u.full_name, u.username, u.barangay as user_barangay
        FROM annex8_road_bridge_status a
        LEFT JOIN users u ON a.created_by = u.id
        WHERE a.is_archived = 0
        ORDER BY a.region, a.province, a.city
    ");
} else {
    $stmt = db_query("
        SELECT * FROM annex8_road_bridge_status
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
    <title>NDRRMC Annex 8 - Status of Roads and Bridges</title>
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
        <h2>Annex 8: Status of Roads and Bridges</h2>
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
                <th>Type</th>
                <th>Classification</th>
                <th>Road Section /<br>Bridge Name</th>
                <th>Status</th>
                <th>Date / Time<br>Not Passable</th>
                <th>Date / Time<br>Passable</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
            <tr>
                <td class="text-left"><?php echo htmlspecialchars($record['region'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['province'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['city'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['barangay'] ?: $record['user_barangay'] ?: ''); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($record['type'] ?: ''); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($record['classification'] ?: ''); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($record['road_section'] ?: ''); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($record['status'] ?: ''); ?></td>
                <td class="text-center">
                    <?php
                    if ($record['date_not_passable']) {
                        echo date('M j, Y g:i A', strtotime($record['date_not_passable']));
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php
                    if ($record['date_passable']) {
                        echo date('M j, Y g:i A', strtotime($record['date_passable']));
                    }
                    ?>
                </td>
                <td class="text-left"><?php echo htmlspecialchars($record['remarks'] ?: ''); ?></td>
            </tr>
            <?php endforeach; ?>

            <?php if (count($records) === 0): ?>
            <tr>
                <td colspan="11" style="text-align: center; padding: 20px;">No records found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Summary Section -->
    <?php if (count($records) > 0): ?>
    <div style="margin-top: 25px; font-size: 9pt; line-height: 1.6;">
        <strong>Summary:</strong><br>
        <?php
        $total_roads = 0;
        $total_bridges = 0;
        $passable_count = 0;
        $not_passable_count = 0;
        $heavy_only_count = 0;
        $light_only_count = 0;

        foreach ($records as $record) {
            if ($record['type'] === 'Road') {
                $total_roads++;
            } else if ($record['type'] === 'Bridge') {
                $total_bridges++;
            }

            switch ($record['status']) {
                case 'Passable':
                    $passable_count++;
                    break;
                case 'Not Passable':
                    $not_passable_count++;
                    break;
                case 'Passable to Heavy Vehicles Only':
                    $heavy_only_count++;
                    break;
                case 'Passable to Light Vehicles Only':
                    $light_only_count++;
                    break;
            }
        }
        ?>
        Total Roads: <strong><?php echo $total_roads; ?></strong> &nbsp;|&nbsp;
        Total Bridges: <strong><?php echo $total_bridges; ?></strong><br>
        Passable: <strong><?php echo $passable_count; ?></strong> &nbsp;|&nbsp;
        Not Passable: <strong><?php echo $not_passable_count; ?></strong> &nbsp;|&nbsp;
        Restricted (Heavy Only): <strong><?php echo $heavy_only_count; ?></strong> &nbsp;|&nbsp;
        Restricted (Light Only): <strong><?php echo $light_only_count; ?></strong>
    </div>
    <?php endif; ?>

    <!-- Status Change History Section -->
    <?php if (count($records) > 0): ?>
    <div style="margin-top: 30px; page-break-before: always;">
        <h3 style="font-size: 11pt; font-weight: bold; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 5px;">
            DETAILED STATUS UPDATE HISTORY
        </h3>

        <?php foreach ($records as $record): ?>
            <?php
            $history = get_status_history($record['id']);
            if (!empty($history) && count($history) > 1): // Only show if there are status changes
            ?>
            <div style="margin-bottom: 25px; page-break-inside: avoid;">
                <div style="background: #f0f0f0; padding: 8px; font-size: 9pt; font-weight: bold; border-left: 4px solid #0066cc;">
                    <?php echo htmlspecialchars($record['road_section']); ?>
                    (<?php echo htmlspecialchars($record['type']); ?> - <?php echo htmlspecialchars($record['barangay']); ?>)
                </div>

                <table style="margin-top: 5px; font-size: 8pt;">
                    <thead>
                        <tr style="background: #e0e0e0;">
                            <th style="width: 5%;">#</th>
                            <th style="width: 25%;">Status</th>
                            <th style="width: 20%;">Date/Time</th>
                            <th style="width: 30%;">Remarks</th>
                            <th style="width: 20%;">Changed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $index => $h): ?>
                        <tr style="<?php echo $index === 0 ? 'background: #fffacd;' : ''; ?>">
                            <td class="text-center"><?php echo $index + 1; ?></td>
                            <td class="text-center">
                                <?php echo htmlspecialchars($h['status']); ?>
                                <?php if ($index === 0): ?>
                                    <span style="font-size: 7pt; color: #0066cc;">(CURRENT)</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php echo date('M j, Y g:i A', strtotime($h['status_date'])); ?>
                            </td>
                            <td class="text-left">
                                <?php echo htmlspecialchars($h['remarks'] ?: '-'); ?>
                            </td>
                            <td class="text-left">
                                <?php echo htmlspecialchars($h['changed_by_name'] ?? 'Unknown'); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php
        // Check if any records have history
        $has_history = false;
        foreach ($records as $record) {
            $history = get_status_history($record['id']);
            if (!empty($history) && count($history) > 1) {
                $has_history = true;
                break;
            }
        }
        if (!$has_history):
        ?>
        <p style="font-size: 9pt; font-style: italic; color: #666;">
            No status changes recorded for any roads/bridges.
        </p>
        <?php endif; ?>
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

<?php
/**
 * NDRRMC Annex 1: Related Incident - Print Template
 * Generates a printable view matching the official NDRRMC format
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

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

// Get current date for the header
$current_date = date('F d, Y');
$memo_number = "OC-___"; // Placeholder for memo number
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDRRMC Annex 1: Related Incident - Print Preview</title>
    <style>
        /* Print-specific styles */
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
            background: #fff;
        }

        .print-container {
            width: 100%;
            max-width: 297mm;
            margin: 0 auto;
            padding: 10mm;
            background: #fff;
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
            margin-bottom: 3px;
        }

        .memo-info {
            font-size: 9pt;
            margin: 10px 0;
            text-align: left;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
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

        td {
            min-height: 25px;
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

        .footer {
            margin-top: 20px;
            font-size: 9pt;
            page-break-inside: avoid;
        }

        .signature-block {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-item {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            font-weight: bold;
        }

        .no-records {
            text-align: center;
            padding: 30px;
            font-size: 11pt;
            color: #666;
        }

        /* Screen-only styles */
        @media screen {
            body {
                background: #e0e0e0;
                padding: 20px;
            }

            .print-container {
                box-shadow: 0 0 10px rgba(0,0,0,0.3);
            }

            .print-buttons {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                background: #fff;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            }

            .btn {
                display: inline-block;
                padding: 10px 20px;
                margin: 5px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                text-decoration: none;
                transition: all 0.3s;
            }

            .btn-print {
                background: #4CAF50;
                color: white;
            }

            .btn-print:hover {
                background: #45a049;
            }

            .btn-close {
                background: #f44336;
                color: white;
            }

            .btn-close:hover {
                background: #da190b;
            }
        }

        /* Print-only styles */
        @media print {
            .print-buttons {
                display: none !important;
            }

            body {
                background: #fff;
                padding: 0;
            }

            .print-container {
                box-shadow: none;
                max-width: 100%;
                padding: 0;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
        }

        .empty-row td {
            height: 30px;
        }
    </style>
</head>
<body>
    <!-- Print/Close buttons (visible on screen only) -->
    <div class="print-buttons">
        <button onclick="window.print()" class="btn btn-print">🖨️ Print</button>
        <button onclick="window.close()" class="btn btn-close">✖ Close</button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <h1>NDRRMC Memorandum Circular No. <?php echo htmlspecialchars($memo_number); ?>, <?php echo date('Y'); ?> re NDRRMC Reporting Templates</h1>
            <h2>Annex 1: Related Incident</h2>
        </div>

        <!-- Memo Information -->
        <!-- <div class="memo-info">
            <strong>Date Printed:</strong> <?php echo $current_date; ?><br>
            <strong>Total Records:</strong> <?php echo count($records); ?><br>
            <?php if (!$is_admin): ?>
            <strong>Prepared By:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?><br>
            <?php endif; ?>
        </div> -->

        <!-- Records Table -->
        <div class="table-container">
            <?php if (count($records) > 0): ?>
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
                        <td class="col-region"><?php echo htmlspecialchars($record['region']); ?></td>
                        <td class="col-province"><?php echo htmlspecialchars($record['province']); ?></td>
                        <td class="col-city"><?php echo htmlspecialchars($record['city']); ?></td>
                        <td class="col-barangay"><?php echo htmlspecialchars($record['barangay']); ?></td>
                        <td class="col-type"><?php echo htmlspecialchars($record['incident_type']); ?></td>
                        <td class="col-date"><?php echo date('M d, Y h:i A', strtotime($record['occurrence_date'])); ?></td>
                        <td class="col-description"><?php echo htmlspecialchars($record['description'] ?: ''); ?></td>
                        <td class="col-actions"><?php echo htmlspecialchars($record['actions_taken'] ?: ''); ?></td>
                        <td class="col-remarks"><?php echo htmlspecialchars($record['remarks'] ?: ''); ?></td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- Add empty rows to fill the page -->
                    <?php for ($i = count($records); $i < 3; $i++): ?>
                    <tr class="empty-row">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-records">
                <p>No records found to display.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer / Signature Block -->
        <?php if (count($records) > 0): ?>
        <div class="footer">
            <div class="signature-block">
                <div class="signature-item">
                    <div class="signature-line">
                        Prepared by
                    </div>
                </div>
                <div class="signature-item">
                    <div class="signature-line">
                        Noted by
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-print option (uncomment if needed)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>

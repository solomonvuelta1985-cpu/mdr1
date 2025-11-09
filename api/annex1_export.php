<?php
/**
 * NDRRMC Annex 1: Related Incident - Export Handler
 * Handles PDF, Excel, and Word exports
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

// Get current user info
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] === 'admin');

// Get export parameters
$export_type = isset($_GET['type']) ? strtolower($_GET['type']) : 'pdf';

// Validate export type
if (!in_array($export_type, ['pdf', 'excel', 'word'])) {
    die('Invalid export type');
}

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

// Route to appropriate export function
switch ($export_type) {
    case 'pdf':
        exportToPDF($records);
        break;
    case 'excel':
        exportToExcel($records);
        break;
    case 'word':
        exportToWord($records);
        break;
}

/**
 * Export to PDF format
 */
function exportToPDF($records) {
    // PDF generation using HTML2PDF approach
    header('Content-Type: text/html; charset=utf-8');

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
            // Auto-trigger browser print dialog for PDF
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        </script>
    </body>
    </html>
    <?php
}

/**
 * Export to Excel format (CSV with proper Excel formatting)
 */
function exportToExcel($records) {
    $filename = 'NDRRMC_Annex1_Related_Incident_' . date('Y-m-d_His') . '.csv';

    // Set headers for Excel download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for proper Excel encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Add title rows
    fputcsv($output, ['NDRRMC Annex 1: Related Incident']);
    fputcsv($output, ['Generated: ' . date('F d, Y h:i A')]);
    fputcsv($output, ['Total Records: ' . count($records)]);
    fputcsv($output, []); // Empty row

    // Add column headers
    fputcsv($output, [
        'ID',
        'Region',
        'Province',
        'City / Municipality',
        'Barangay',
        'Type of Incident',
        'Date / Time of Occurrence',
        'Description',
        'Actions Taken',
        'Remarks',
        'Created By',
        'Created At'
    ]);

    // Add data rows
    foreach ($records as $record) {
        fputcsv($output, [
            $record['id'],
            $record['region'],
            $record['province'],
            $record['city'],
            $record['barangay'],
            $record['incident_type'],
            date('M d, Y h:i A', strtotime($record['occurrence_date'])),
            $record['description'] ?: '',
            $record['actions_taken'] ?: '',
            $record['remarks'] ?: '',
            $record['full_name'] ?: $record['username'] ?: 'N/A',
            date('M d, Y h:i A', strtotime($record['created_at']))
        ]);
    }

    fclose($output);
    exit;
}

/**
 * Export to Word format (HTML-based DOC)
 */
function exportToWord($records) {
    $filename = 'NDRRMC_Annex1_Related_Incident_' . date('Y-m-d_His') . '.doc';

    // Set headers for Word download
    header('Content-Type: application/vnd.ms-word');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $current_date = date('F d, Y');
    $memo_number = "OC-___";

    ?>
    <html xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:w="urn:schemas-microsoft-com:office:word"
          xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta charset="UTF-8">
        <title>NDRRMC Annex 1 - Related Incident</title>
        <style>
            @page Section1 {
                size: 11.0in 8.5in;
                margin: 0.75in 0.5in 0.75in 0.5in;
                mso-page-orientation: landscape;
            }
            div.Section1 { page: Section1; }
            body {
                font-family: 'Times New Roman', Times, serif;
                font-size: 11pt;
            }
            .header {
                text-align: center;
                margin-bottom: 15pt;
                border-bottom: 2pt solid black;
                padding-bottom: 10pt;
            }
            .header h1 {
                font-size: 14pt;
                font-weight: bold;
                margin-bottom: 5pt;
            }
            .header h2 {
                font-size: 13pt;
                font-weight: bold;
            }
            .memo-info {
                font-size: 10pt;
                margin: 10pt 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                border: 2pt solid black;
                margin-top: 10pt;
            }
            th, td {
                border: 1pt solid black;
                padding: 4pt 6pt;
                vertical-align: top;
                font-size: 10pt;
            }
            th {
                background-color: #f0f0f0;
                font-weight: bold;
                text-align: center;
            }
            td {
                text-align: left;
            }
        </style>
    </head>
    <body>
        <div class="Section1">
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
                        <th>Region</th>
                        <th>Province</th>
                        <th>City / Municipality</th>
                        <th>Barangay</th>
                        <th>Type of Incident</th>
                        <th>Date / time of occurrence</th>
                        <th>Description</th>
                        <th>Actions Taken</th>
                        <th>Remarks</th>
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
                        <td colspan="9" style="text-align: center; padding: 20pt;">No records found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top: 40pt;">
                <table style="border: none;">
                    <tr>
                        <td style="width: 45%; border: none; text-align: center;">
                            <div style="margin-top: 40pt; border-top: 1pt solid black; padding-top: 5pt;">
                                <strong>Prepared by</strong>
                            </div>
                        </td>
                        <td style="width: 10%; border: none;"></td>
                        <td style="width: 45%; border: none; text-align: center;">
                            <div style="margin-top: 40pt; border-top: 1pt solid black; padding-top: 5pt;">
                                <strong>Noted by</strong>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>

<?php
/**
 * MDRRMO Situational Report - Export Handler (Simplified)
 * Handles PDF and Word exports - Table Format
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Get export type
$export_type = isset($_GET['type']) ? strtolower($_GET['type']) : 'pdf';

if (!in_array($export_type, ['pdf', 'word'])) {
    die('Invalid export type');
}

// Get settings
$settings = [
    'report_number' => 3,
    'weather_system' => '',
    'mayor_status' => 'In',
    'vm_status' => 'In',
    'weather_clouds' => 'CLEAR SKY',
    'weather_rains' => 'NONE',
    'weather_winds' => 'NORMAL'
];

try {
    $stmt = db_query("SELECT * FROM sitrep_settings ORDER BY id DESC LIMIT 1");
    if ($stmt) {
        $db_settings = $stmt->fetch();
        if ($db_settings) {
            $settings = array_merge($settings, $db_settings);
        }
    }
} catch (Exception $e) {}

// Get data
$data = [];

// A.1 Related Incidents
try {
    $stmt = db_query("SELECT COUNT(*) as count FROM annex1_related_incidents WHERE is_archived = 0");
    if ($stmt) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['incidents'] = $result['count'] ?? 0;
    } else {
        $data['incidents'] = 0;
    }
} catch (Exception $e) {
    $data['incidents'] = 0;
}

// A.2 Affected Population
try {
    $stmt = db_query("SELECT SUM(total_families) as fam, SUM(total_persons) as per FROM annex2_affected_population WHERE is_archived = 0");
    if ($stmt) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['affected_families'] = $result['fam'] ?? 0;
        $data['affected_persons'] = $result['per'] ?? 0;
    } else {
        $data['affected_families'] = 0;
        $data['affected_persons'] = 0;
    }
} catch (Exception $e) {
    $data['affected_families'] = 0;
    $data['affected_persons'] = 0;
}

// A.3 Casualties
try {
    $stmt = db_query("SELECT COUNT(*) as count FROM annex3_casualties WHERE is_archived = 0");
    if ($stmt) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['casualties'] = $result['count'] ?? 0;
    } else {
        $data['casualties'] = 0;
    }
} catch (Exception $e) {
    $data['casualties'] = 0;
}

// A.4 Damaged Houses
try {
    $stmt = db_query("SELECT COUNT(*) as count FROM annex4_damaged_houses WHERE is_archived = 0");
    if ($stmt) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['houses'] = $result['count'] ?? 0;
    } else {
        $data['houses'] = 0;
    }
} catch (Exception $e) {
    $data['houses'] = 0;
}

// A.5 Agriculture Damage
try {
    $stmt = db_query("SELECT COUNT(*) as count FROM annex5_agriculture_damage WHERE is_archived = 0");
    if ($stmt) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['agriculture'] = $result['count'] ?? 0;
    } else {
        $data['agriculture'] = 0;
    }
} catch (Exception $e) {
    $data['agriculture'] = 0;
}

if ($export_type === 'pdf') {
    exportToPDF($settings, $data);
} else {
    exportToWord($settings, $data);
}

function exportToPDF($settings, $data) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>MDRRMO SITREP No. <?php echo $settings['report_number']; ?></title>
        <style>
            @page { size: A4; margin: 20mm; }
            body { font-family: Arial, sans-serif; font-size: 11pt; }
            .header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
            .header h1 { font-size: 14pt; margin: 0; }
            .header h2 { font-size: 12pt; margin: 5px 0; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #000; padding: 8px; }
            th { background: #333; color: white; text-align: left; }
            .section-title { font-weight: bold; background: #f0f0f0; padding: 8px; margin: 15px 0 10px 0; border-left: 4px solid #000; }
            .footer { text-align: center; margin-top: 30px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>MDRRMO LGU-BAGGAO</h1>
            <h2>Situational Report No. <?php echo $settings['report_number']; ?></h2>
            <p><strong>Weather System:</strong> <?php echo htmlspecialchars($settings['weather_system']); ?></p>
            <p>As of <?php echo date('F d, Y - h:i a'); ?></p>
        </div>

        <table>
            <tr><td style="width:30%; font-weight:600; background:#f8f9fa;">Mayor</td><td><?php echo htmlspecialchars($settings['mayor_status']); ?></td></tr>
            <tr><td style="width:30%; font-weight:600; background:#f8f9fa;">VM</td><td><?php echo htmlspecialchars($settings['vm_status']); ?></td></tr>
        </table>

        <div class="section-title">Weather Condition</div>
        <table>
            <tr><td style="width:30%; font-weight:600; background:#f8f9fa;">Clouds</td><td><?php echo htmlspecialchars($settings['weather_clouds']); ?></td></tr>
            <tr><td style="width:30%; font-weight:600; background:#f8f9fa;">Rains</td><td><?php echo htmlspecialchars($settings['weather_rains']); ?></td></tr>
            <tr><td style="width:30%; font-weight:600; background:#f8f9fa;">Winds</td><td><?php echo htmlspecialchars($settings['weather_winds']); ?></td></tr>
        </table>

        <div class="section-title">Summary Report</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Count / Details</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>A.1 Related Incidents</strong></td>
                    <td><?php echo $data['incidents'] > 0 ? $data['incidents'] . ' incident(s)' : 'NONE'; ?></td>
                    <td><?php echo $data['incidents'] > 0 ? 'Active' : 'Clear'; ?></td>
                </tr>
                <tr>
                    <td><strong>A.2 Affected Population</strong></td>
                    <td><?php echo ($data['affected_families'] > 0 || $data['affected_persons'] > 0) ? number_format($data['affected_families']) . ' families, ' . number_format($data['affected_persons']) . ' persons' : 'NONE'; ?></td>
                    <td><?php echo ($data['affected_families'] > 0) ? 'Monitoring' : 'Clear'; ?></td>
                </tr>
                <tr>
                    <td><strong>A.3 Casualties</strong></td>
                    <td><?php echo $data['casualties'] > 0 ? $data['casualties'] . ' casualty(ies)' : 'NONE'; ?></td>
                    <td><?php echo $data['casualties'] > 0 ? 'Critical' : 'Clear'; ?></td>
                </tr>
                <tr>
                    <td><strong>A.4 Damaged Houses</strong></td>
                    <td><?php echo $data['houses'] > 0 ? $data['houses'] . ' house(s)' : 'NONE'; ?></td>
                    <td><?php echo $data['houses'] > 0 ? 'Assessment' : 'Clear'; ?></td>
                </tr>
                <tr>
                    <td><strong>A.5 Agriculture Damage</strong></td>
                    <td><?php echo $data['agriculture'] > 0 ? $data['agriculture'] . ' report(s)' : 'NO REPORTED YET'; ?></td>
                    <td><?php echo $data['agriculture'] > 0 ? 'Assessment' : 'Pending'; ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Keep safe everyone!<br>
            Baggao IC3
        </div>

        <script>
            window.onload = function() { setTimeout(function() { window.print(); }, 500); };
        </script>
    </body>
    </html>
    <?php
}

function exportToWord($settings, $data) {
    $filename = 'MDRRMO_SITREP_No' . $settings['report_number'] . '_' . date('Y-m-d') . '.doc';
    header('Content-Type: application/vnd.ms-word');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    ?>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; font-size: 11pt; }
            .header { text-align: center; border-bottom: 3pt solid black; padding-bottom: 10pt; margin-bottom: 20pt; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20pt; }
            th, td { border: 1pt solid black; padding: 8pt; }
            th { background: #333; color: white; }
            .section-title { font-weight: bold; background: #f0f0f0; padding: 8pt; margin: 15pt 0 10pt 0; border-left: 4pt solid black; }
            .footer { text-align: center; margin-top: 30pt; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>MDRRMO LGU-BAGGAO</h1>
            <h2>Situational Report No. <?php echo $settings['report_number']; ?></h2>
            <p><strong>Weather System:</strong> <?php echo htmlspecialchars($settings['weather_system']); ?></p>
            <p>As of <?php echo date('F d, Y - h:i a'); ?></p>
        </div>

        <table>
            <tr><td style="width:30%; font-weight:600; background:#f8f9fa;">Mayor</td><td><?php echo htmlspecialchars($settings['mayor_status']); ?></td></tr>
            <tr><td style="width:30%; font-weight:600; background:#f8f9fa;">VM</td><td><?php echo htmlspecialchars($settings['vm_status']); ?></td></tr>
        </table>

        <div class="section-title">Weather Condition</div>
        <table>
            <tr><td style="width:30%; font-weight:600; background:#f8f9fa;">Clouds</td><td><?php echo htmlspecialchars($settings['weather_clouds']); ?></td></tr>
            <tr><td style="width:30%; font-weight:600; background:#f8f9fa;">Rains</td><td><?php echo htmlspecialchars($settings['weather_rains']); ?></td></tr>
            <tr><td style="width:30%; font-weight:600; background:#f8f9fa;">Winds</td><td><?php echo htmlspecialchars($settings['weather_winds']); ?></td></tr>
        </table>

        <div class="section-title">Summary Report</div>
        <table>
            <thead>
                <tr><th>Category</th><th>Count / Details</th><th>Status</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>A.1 Related Incidents</strong></td>
                    <td><?php echo $data['incidents'] > 0 ? $data['incidents'] . ' incident(s)' : 'NONE'; ?></td>
                    <td><?php echo $data['incidents'] > 0 ? 'Active' : 'Clear'; ?></td>
                </tr>
                <tr>
                    <td><strong>A.2 Affected Population</strong></td>
                    <td><?php echo ($data['affected_families'] > 0 || $data['affected_persons'] > 0) ? number_format($data['affected_families']) . ' families, ' . number_format($data['affected_persons']) . ' persons' : 'NONE'; ?></td>
                    <td><?php echo ($data['affected_families'] > 0) ? 'Monitoring' : 'Clear'; ?></td>
                </tr>
                <tr>
                    <td><strong>A.3 Casualties</strong></td>
                    <td><?php echo $data['casualties'] > 0 ? $data['casualties'] . ' casualty(ies)' : 'NONE'; ?></td>
                    <td><?php echo $data['casualties'] > 0 ? 'Critical' : 'Clear'; ?></td>
                </tr>
                <tr>
                    <td><strong>A.4 Damaged Houses</strong></td>
                    <td><?php echo $data['houses'] > 0 ? $data['houses'] . ' house(s)' : 'NONE'; ?></td>
                    <td><?php echo $data['houses'] > 0 ? 'Assessment' : 'Clear'; ?></td>
                </tr>
                <tr>
                    <td><strong>A.5 Agriculture Damage</strong></td>
                    <td><?php echo $data['agriculture'] > 0 ? $data['agriculture'] . ' report(s)' : 'NO REPORTED YET'; ?></td>
                    <td><?php echo $data['agriculture'] > 0 ? 'Assessment' : 'Pending'; ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Keep safe everyone!<br>
            Baggao IC3
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>

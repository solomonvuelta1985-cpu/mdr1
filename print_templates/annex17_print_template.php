<?php
/**
 * NDRRMC Annex 17: Pre-emptive Evacuation (People) - Print Template
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
        FROM annex17_preemptive_evacuation a
        LEFT JOIN users u ON a.created_by = u.id
        WHERE a.is_archived = 0
        ORDER BY a.region, a.province, a.city_municipality
    ");
} else {
    $stmt = db_query("
        SELECT * FROM annex17_preemptive_evacuation
        WHERE created_by = ? AND is_archived = 0
        ORDER BY region, province, city_municipality
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
    <title>NDRRMC Annex 17 - Pre-emptive Evacuation (People)</title>
    <style>
        @page {
            size: 13in 8.5in landscape;
            margin: 0.5in;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.2;
            color: #000;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .header h2 {
            font-size: 10pt;
            font-weight: bold;
        }
        .memo-info {
            font-size: 8pt;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 10px;
        }
        th, td {
            border: 0.5px solid #000;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 7pt;
        }
        .location-col {
            text-align: left;
            padding-left: 5px;
        }
        .rotate {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            transform: rotate(180deg);
            white-space: nowrap;
            font-size: 6pt;
        }
        .section-header {
            background-color: #d0d0d0;
            font-weight: bold;
            text-align: center;
            font-size: 8pt;
        }
        .page-break {
            page-break-after: always;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>NDRRMC MEMORANDUM CIRCULAR NO. <?= $memo_number ?>, s. 2025 re NDRRMC REPORTING TEMPLATES</h1>
        <h2>Annex 17: Pre-emptive Evacuation (People)</h2>
    </div>

    <div class="memo-info">
        <strong>Date Generated:</strong> <?= $current_date ?>
    </div>

    <!-- PAGE 1: Age Distribution Summary -->
    <table>
        <thead>
            <tr>
                <th rowspan="3" style="width: 8%;">Region</th>
                <th rowspan="3" style="width: 8%;">Province</th>
                <th rowspan="3" style="width: 10%;">City/<br>Municipality</th>
                <th rowspan="3" style="width: 10%;">Barangay</th>
                <th rowspan="3" style="width: 5%;">Families</th>
                <th colspan="28" class="section-header">Sex and Age Distribution of Pre-emptive Evacuation</th>
            </tr>
            <tr>
                <th colspan="4">Infant 0-6 mos</th>
                <th colspan="4">Toddlers 7mo-2yo</th>
                <th colspan="4">Preschool 3-5yo</th>
                <th colspan="4">School 6-12yo</th>
                <th colspan="4">Teen 13-17yo</th>
                <th colspan="4">Adult 18-59yo</th>
                <th colspan="4">Elderly 60+</th>
            </tr>
            <tr>
                <?php for ($i = 0; $i < 7; $i++): ?>
                    <th>M-C</th>
                    <th>M-R</th>
                    <th>F-C</th>
                    <th>F-R</th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="33">No records found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td class="location-col"><?= htmlspecialchars($record['region']) ?></td>
                        <td class="location-col"><?= htmlspecialchars($record['province']) ?></td>
                        <td class="location-col"><?= htmlspecialchars($record['city_municipality']) ?></td>
                        <td class="location-col"><strong><?= htmlspecialchars($record['barangay']) ?></strong></td>
                        <td><?= number_format($record['families']) ?></td>

                        <!-- Infant -->
                        <td><?= number_format($record['infant_male_cumulative']) ?></td>
                        <td><?= number_format($record['infant_male_current']) ?></td>
                        <td><?= number_format($record['infant_female_cumulative']) ?></td>
                        <td><?= number_format($record['infant_female_current']) ?></td>

                        <!-- Toddler -->
                        <td><?= number_format($record['toddler_male_cumulative']) ?></td>
                        <td><?= number_format($record['toddler_male_current']) ?></td>
                        <td><?= number_format($record['toddler_female_cumulative']) ?></td>
                        <td><?= number_format($record['toddler_female_current']) ?></td>

                        <!-- Preschool -->
                        <td><?= number_format($record['preschool_male_cumulative']) ?></td>
                        <td><?= number_format($record['preschool_male_current']) ?></td>
                        <td><?= number_format($record['preschool_female_cumulative']) ?></td>
                        <td><?= number_format($record['preschool_female_current']) ?></td>

                        <!-- School -->
                        <td><?= number_format($record['school_male_cumulative']) ?></td>
                        <td><?= number_format($record['school_male_current']) ?></td>
                        <td><?= number_format($record['school_female_cumulative']) ?></td>
                        <td><?= number_format($record['school_female_current']) ?></td>

                        <!-- Teenage -->
                        <td><?= number_format($record['teenage_male_cumulative']) ?></td>
                        <td><?= number_format($record['teenage_male_current']) ?></td>
                        <td><?= number_format($record['teenage_female_cumulative']) ?></td>
                        <td><?= number_format($record['teenage_female_current']) ?></td>

                        <!-- Adult -->
                        <td><?= number_format($record['adult_male_cumulative']) ?></td>
                        <td><?= number_format($record['adult_male_current']) ?></td>
                        <td><?= number_format($record['adult_female_cumulative']) ?></td>
                        <td><?= number_format($record['adult_female_current']) ?></td>

                        <!-- Elderly -->
                        <td><?= number_format($record['elderly_male_cumulative']) ?></td>
                        <td><?= number_format($record['elderly_male_current']) ?></td>
                        <td><?= number_format($record['elderly_female_cumulative']) ?></td>
                        <td><?= number_format($record['elderly_female_current']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Totals Row -->
                <tr style="font-weight: bold; background-color: #f0f0f0;">
                    <td colspan="4" class="location-col">TOTAL</td>
                    <td><?= number_format(array_sum(array_column($records, 'families'))) ?></td>
                    <?php
                    $age_fields = [
                        'infant_male_cumulative', 'infant_male_current', 'infant_female_cumulative', 'infant_female_current',
                        'toddler_male_cumulative', 'toddler_male_current', 'toddler_female_cumulative', 'toddler_female_current',
                        'preschool_male_cumulative', 'preschool_male_current', 'preschool_female_cumulative', 'preschool_female_current',
                        'school_male_cumulative', 'school_male_current', 'school_female_cumulative', 'school_female_current',
                        'teenage_male_cumulative', 'teenage_male_current', 'teenage_female_cumulative', 'teenage_female_current',
                        'adult_male_cumulative', 'adult_male_current', 'adult_female_cumulative', 'adult_female_current',
                        'elderly_male_cumulative', 'elderly_male_current', 'elderly_female_cumulative', 'elderly_female_current'
                    ];
                    foreach ($age_fields as $field):
                    ?>
                        <td><?= number_format(array_sum(array_column($records, $field))) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- PAGE 2: Vulnerable Groups -->
    <div class="header">
        <h1>NDRRMC MEMORANDUM CIRCULAR NO. <?= $memo_number ?>, s. 2025 re NDRRMC REPORTING TEMPLATES</h1>
        <h2>Annex 17: Pre-emptive Evacuation (People) - Vulnerable Groups</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="3" style="width: 10%;">Region</th>
                <th rowspan="3" style="width: 10%;">Province</th>
                <th rowspan="3" style="width: 12%;">City/Municipality</th>
                <th rowspan="3" style="width: 12%;">Barangay</th>
                <th colspan="2">Pregnant/<br>Lactating</th>
                <th colspan="4">Child-Headed<br>Family</th>
                <th colspan="4">Single-Headed<br>Family</th>
                <th colspan="4">Solo Parent</th>
                <th colspan="4">PWD</th>
                <th colspan="4">Indigenous<br>People</th>
                <th colspan="4">4P's<br>Beneficiaries</th>
                <th rowspan="3" style="width: 8%;">Remarks</th>
            </tr>
            <tr>
                <th rowspan="2">Cum</th>
                <th rowspan="2">Cur</th>
                <th colspan="2">Male</th>
                <th colspan="2">Female</th>
                <th colspan="2">Male</th>
                <th colspan="2">Female</th>
                <th colspan="2">Male</th>
                <th colspan="2">Female</th>
                <th colspan="2">Male</th>
                <th colspan="2">Female</th>
                <th colspan="2">Male</th>
                <th colspan="2">Female</th>
                <th colspan="2">Male</th>
                <th colspan="2">Female</th>
            </tr>
            <tr>
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <th>C</th>
                    <th>R</th>
                    <th>C</th>
                    <th>R</th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="31">No records found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td class="location-col"><?= htmlspecialchars($record['region']) ?></td>
                        <td class="location-col"><?= htmlspecialchars($record['province']) ?></td>
                        <td class="location-col"><?= htmlspecialchars($record['city_municipality']) ?></td>
                        <td class="location-col"><strong><?= htmlspecialchars($record['barangay']) ?></strong></td>

                        <!-- Pregnant/Lactating -->
                        <td><?= number_format($record['pregnant_lactating_cumulative']) ?></td>
                        <td><?= number_format($record['pregnant_lactating_current']) ?></td>

                        <!-- Child-Headed Family -->
                        <td><?= number_format($record['child_headed_male_cumulative']) ?></td>
                        <td><?= number_format($record['child_headed_male_current']) ?></td>
                        <td><?= number_format($record['child_headed_female_cumulative']) ?></td>
                        <td><?= number_format($record['child_headed_female_current']) ?></td>

                        <!-- Single-Headed Family -->
                        <td><?= number_format($record['single_headed_male_cumulative']) ?></td>
                        <td><?= number_format($record['single_headed_male_current']) ?></td>
                        <td><?= number_format($record['single_headed_female_cumulative']) ?></td>
                        <td><?= number_format($record['single_headed_female_current']) ?></td>

                        <!-- Solo Parent -->
                        <td><?= number_format($record['solo_parent_male_cumulative']) ?></td>
                        <td><?= number_format($record['solo_parent_male_current']) ?></td>
                        <td><?= number_format($record['solo_parent_female_cumulative']) ?></td>
                        <td><?= number_format($record['solo_parent_female_current']) ?></td>

                        <!-- PWD -->
                        <td><?= number_format($record['pwd_male_cumulative']) ?></td>
                        <td><?= number_format($record['pwd_male_current']) ?></td>
                        <td><?= number_format($record['pwd_female_cumulative']) ?></td>
                        <td><?= number_format($record['pwd_female_current']) ?></td>

                        <!-- IP -->
                        <td><?= number_format($record['ip_male_cumulative']) ?></td>
                        <td><?= number_format($record['ip_male_current']) ?></td>
                        <td><?= number_format($record['ip_female_cumulative']) ?></td>
                        <td><?= number_format($record['ip_female_current']) ?></td>

                        <!-- 4Ps -->
                        <td><?= number_format($record['fourps_male_cumulative']) ?></td>
                        <td><?= number_format($record['fourps_male_current']) ?></td>
                        <td><?= number_format($record['fourps_female_cumulative']) ?></td>
                        <td><?= number_format($record['fourps_female_current']) ?></td>

                        <td class="location-col" style="font-size: 6pt;"><?= htmlspecialchars(substr($record['remarks'], 0, 50)) ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Totals Row -->
                <tr style="font-weight: bold; background-color: #f0f0f0;">
                    <td colspan="4" class="location-col">TOTAL</td>
                    <?php
                    $vuln_fields = [
                        'pregnant_lactating_cumulative', 'pregnant_lactating_current',
                        'child_headed_male_cumulative', 'child_headed_male_current', 'child_headed_female_cumulative', 'child_headed_female_current',
                        'single_headed_male_cumulative', 'single_headed_male_current', 'single_headed_female_cumulative', 'single_headed_female_current',
                        'solo_parent_male_cumulative', 'solo_parent_male_current', 'solo_parent_female_cumulative', 'solo_parent_female_current',
                        'pwd_male_cumulative', 'pwd_male_current', 'pwd_female_cumulative', 'pwd_female_current',
                        'ip_male_cumulative', 'ip_male_current', 'ip_female_cumulative', 'ip_female_current',
                        'fourps_male_cumulative', 'fourps_male_current', 'fourps_female_cumulative', 'fourps_female_current'
                    ];
                    foreach ($vuln_fields as $field):
                    ?>
                        <td><?= number_format(array_sum(array_column($records, $field))) ?></td>
                    <?php endforeach; ?>
                    <td>-</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 7pt;">
        <strong>Legend:</strong> C = Cumulative | R = Current | M = Male | F = Female | PWD = Persons with Disability
    </div>

    <script>
        // Auto-print on page load
        window.onload = function() {
            // Uncomment the line below to enable auto-print
            // window.print();
        }
    </script>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 12pt; cursor: pointer;">
            Print Report
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 12pt; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>
</body>
</html>

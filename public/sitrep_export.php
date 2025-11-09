<?php
// sitrep_export.php
// Export SITREP to PDF or Word format

// Database configuration
$host = '127.0.0.1';
$dbname = 'annex_management_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Include all the same functions from sitrep.php
function getLatestIncidents($pdo, $limit = 10) {
    $stmt = $pdo->prepare("SELECT barangay, incident_type, occurrence_date, description, actions_taken FROM annex1_related_incidents WHERE is_archived = 0 ORDER BY occurrence_date DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAffectedPopulation($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, affected_families_cumulative, affected_families_current, affected_persons_cumulative, affected_persons_current FROM annex2_affected_population WHERE is_archived = 0 ORDER BY barangay");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCasualtiesSummary($pdo) {
    $stmt = $pdo->prepare("SELECT category, COUNT(*) as count, SUM(CASE WHEN sex = 'Male' THEN 1 ELSE 0 END) as male_count, SUM(CASE WHEN sex = 'Female' THEN 1 ELSE 0 END) as female_count FROM annex3_casualties WHERE is_archived = 0 GROUP BY category");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDamagedHouses($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, totally_damaged, partially_damaged, total_damaged FROM annex4_damaged_houses WHERE is_archived = 0 ORDER BY barangay");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAgricultureDamage($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, classification, type, affected_people, damage_value, production_loss_volume FROM annex5_agriculture_damage WHERE is_archived = 0 ORDER BY barangay");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getInfrastructureDamage($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, type, classification, name, totally_damaged, partially_damaged, total_damaged, cost FROM annex6_infrastructure_damage WHERE is_archived = 0 ORDER BY barangay");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOtherAssetsDamage($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, classification, particulars, unit, quantity, cost FROM annex7_other_assets_damage WHERE is_archived = 0 ORDER BY barangay");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRoadBridgeStatus($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, type, road_section, status, date_not_passable, date_passable FROM annex8_road_bridge_status WHERE is_archived = 0 ORDER BY type, status DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPowerSupplyStatus($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, service_provider, interruption_datetime, restored_datetime FROM annex9_power_supply WHERE is_archived = 0 ORDER BY interruption_datetime DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCommunicationStatus($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, telecom_provider, interruption_date, restored_date FROM annex11_communication_lines WHERE is_archived = 0 ORDER BY interruption_date DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getWorkSuspension($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, type, suspension_date, resumption_date FROM annex14_suspension_work WHERE is_archived = 0 ORDER BY suspension_date DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getClassesSuspension($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, level, type, suspension_date, resumption_date, suspension_reason FROM annex15_suspension_classes WHERE is_archived = 0 ORDER BY suspension_date DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAnimalEvacuation($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, classification, type, quantity, evacuation_reason, shelter_location FROM annex18_animal_evacuation WHERE is_archived = 0 ORDER BY barangay");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFamiliesAssisted($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, families_requiring_assistance, families_assisted, percentage_assisted, assistance_type, assistance_provider FROM annex19_families_assisted WHERE is_archived = 0 ORDER BY barangay");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAssistanceProvided($pdo) {
    $stmt = $pdo->prepare("SELECT cluster, type, quantity, unit, cost_per_unit, amount FROM annex20_assistance_provided WHERE is_archived = 0 ORDER BY cluster");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAssistanceLGU($pdo) {
    $stmt = $pdo->prepare("SELECT barangay, recipient, cluster, type, quantity, unit, cost_per_unit, amount, source FROM annex21_assistance_lgu WHERE is_archived = 0 ORDER BY barangay");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSitrepMetadata($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM sitrep_metadata WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return default values if no metadata exists
    if (!$result) {
        return [
            'report_number' => 1,
            'weather_system' => null,
            'weather_clouds' => 'CLEAR SKY',
            'weather_rains' => 'NONE',
            'weather_winds' => 'NORMAL',
            'mayor_status' => 'In',
            'vice_mayor_status' => 'In',
            'report_date' => date('Y-m-d H:i:s'),
            'update_interval_hours' => 1
        ];
    }

    return $result;
}

// Get SITREP metadata
$sitrepMetadata = getSitrepMetadata($pdo);

// Get ACTUAL data
$incidents = getLatestIncidents($pdo);
$affectedPopulation = getAffectedPopulation($pdo);
$casualties = getCasualtiesSummary($pdo);
$damagedHouses = getDamagedHouses($pdo);
$agricultureDamage = getAgricultureDamage($pdo);
$infrastructureDamage = getInfrastructureDamage($pdo);
$otherAssetsDamage = getOtherAssetsDamage($pdo);
$roadBridgeStatus = getRoadBridgeStatus($pdo);
$powerStatus = getPowerSupplyStatus($pdo);
$communicationStatus = getCommunicationStatus($pdo);
$workSuspension = getWorkSuspension($pdo);
$classesSuspension = getClassesSuspension($pdo);
$animalEvacuation = getAnimalEvacuation($pdo);
$familiesAssisted = getFamiliesAssisted($pdo);
$assistanceProvided = getAssistanceProvided($pdo);
$assistanceLGU = getAssistanceLGU($pdo);

// Calculate totals
$totalIncidents = count($incidents);
$totalAffectedFamilies = array_sum(array_column($affectedPopulation, 'affected_families_current'));
$totalAffectedPersons = array_sum(array_column($affectedPopulation, 'affected_persons_current'));
$totalDamagedHouses = array_sum(array_column($damagedHouses, 'total_damaged'));
$nonPassableBridges = count(array_filter($roadBridgeStatus, function($item) {
    return $item['type'] === 'Bridge' && $item['status'] === 'Not Passable';
}));

$currentDate = date('F d, Y g:i A');
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';

if ($format === 'word') {
    // Export as Word (HTML format that Word can open)
    header("Content-Type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=SITREP_" . date('Y-m-d_His') . ".doc");
    header("Pragma: no-cache");
    header("Expires: 0");
} else {
    // For PDF, we'll use HTML that can be printed to PDF by the browser
    header("Content-Type: text/html; charset=utf-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SITREP Export - <?php echo $currentDate; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 11pt;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        h2 {
            text-align: center;
            color: #34495e;
            font-size: 14pt;
            margin-top: 5px;
        }
        .meta-info {
            text-align: center;
            margin: 10px 0 20px 0;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: auto;
        }
        th {
            background-color: #34495e;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10pt;
            border: 1px solid #2c3e50;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #bdc3c7;
            font-size: 9pt;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .section-title {
            background-color: #ecf0f1;
            font-weight: bold;
            padding: 10px;
            margin-top: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #3498db;
            font-size: 12pt;
            page-break-after: avoid;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #7f8c8d;
        }
        .summary-section {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            text-align: center;
        }
        .summary-item {
            flex: 1;
            padding: 10px;
        }
        .summary-number {
            font-size: 18pt;
            font-weight: bold;
            color: #2c3e50;
        }
        .summary-label {
            font-size: 9pt;
            color: #7f8c8d;
        }
        @media print {
            body {
                margin: 0;
            }
            .page-break {
                page-break-before: always;
            }
        }
        <?php if ($format === 'pdf'): ?>
        @page {
            size: A4;
            margin: 15mm;
        }
        <?php endif; ?>
    </style>
    <?php if ($format === 'pdf'): ?>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    <?php endif; ?>
</head>
<body>
    <h1>MDRRMO LGU-BAGGAO</h1>
    <h2>Situational Report No. <?php echo $sitrepMetadata['report_number']; ?></h2>
    <?php if (!empty($sitrepMetadata['weather_system'])): ?>
        <div style="text-align: center; margin: 5px 0;">
            <strong>Weather System:</strong> <?php echo htmlspecialchars($sitrepMetadata['weather_system']); ?>
        </div>
    <?php endif; ?>
    <div class="meta-info">
        <strong>Report ID:</strong> SITREP-<?php echo str_pad($sitrepMetadata['report_number'], 4, '0', STR_PAD_LEFT); ?> |
        <strong>Report Date:</strong> <?php echo $currentDate; ?> |
        <strong>Update Interval:</strong> Every <?php echo $sitrepMetadata['update_interval_hours']; ?> hour(s)<br>
        <strong>Data Source:</strong> Live Database System |
        <strong>Mayor:</strong> <?php echo $sitrepMetadata['mayor_status']; ?> |
        <strong>Vice Mayor:</strong> <?php echo $sitrepMetadata['vice_mayor_status']; ?><br>
        <strong>Weather Condition:</strong>
        Clouds: <?php echo htmlspecialchars($sitrepMetadata['weather_clouds']); ?> |
        Rains: <?php echo htmlspecialchars($sitrepMetadata['weather_rains']); ?> |
        Winds: <?php echo htmlspecialchars($sitrepMetadata['weather_winds']); ?>
    </div>

    <!-- Summary Statistics -->
    <div class="summary-section">
        <div class="summary-item">
            <div class="summary-number"><?php echo $totalIncidents > 0 ? $totalIncidents : '0'; ?></div>
            <div class="summary-label">Related Incidents</div>
        </div>
        <div class="summary-item">
            <div class="summary-number"><?php echo $totalAffectedPersons > 0 ? $totalAffectedPersons : '0'; ?></div>
            <div class="summary-label">Affected Individuals</div>
        </div>
        <div class="summary-item">
            <div class="summary-number"><?php echo $totalAffectedFamilies > 0 ? $totalAffectedFamilies : '0'; ?></div>
            <div class="summary-label">Affected Families</div>
        </div>
        <div class="summary-item">
            <div class="summary-number"><?php echo $nonPassableBridges > 0 ? $nonPassableBridges : '0'; ?></div>
            <div class="summary-label">Non-Passable Bridges</div>
        </div>
        <div class="summary-item">
            <div class="summary-number"><?php echo $totalDamagedHouses > 0 ? $totalDamagedHouses : '0'; ?></div>
            <div class="summary-label">Damaged Houses</div>
        </div>
    </div>

    <!-- A.1 Related Incidents -->
    <div class="section-title">A.1 Related Incidents</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Incident Type</th>
                <th>Date/Time</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($incidents) > 0): ?>
                <?php foreach ($incidents as $incident): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($incident['barangay']); ?></strong></td>
                        <td><?php echo htmlspecialchars($incident['incident_type']); ?></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($incident['occurrence_date'])); ?></td>
                        <td><?php echo !empty($incident['description']) ? htmlspecialchars($incident['description']) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.2 Affected Population -->
    <div class="section-title">A.2 Affected Population</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Families (Current)</th>
                <th>Individuals (Current)</th>
                <th>Families (Cumulative)</th>
                <th>Individuals (Cumulative)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($affectedPopulation) > 0): ?>
                <?php foreach ($affectedPopulation as $population): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($population['barangay']); ?></strong></td>
                        <td><?php echo $population['affected_families_current'] > 0 ? $population['affected_families_current'] : '0'; ?></td>
                        <td><?php echo $population['affected_persons_current'] > 0 ? $population['affected_persons_current'] : '0'; ?></td>
                        <td><?php echo $population['affected_families_cumulative'] > 0 ? $population['affected_families_cumulative'] : '0'; ?></td>
                        <td><?php echo $population['affected_persons_cumulative'] > 0 ? $population['affected_persons_cumulative'] : '0'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.3 Casualties -->
    <div class="section-title">A.3 Casualties</div>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Male</th>
                <th>Female</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($casualties) > 0): ?>
                <?php foreach ($casualties as $casualty): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($casualty['category']); ?></strong></td>
                        <td><?php echo $casualty['male_count'] > 0 ? $casualty['male_count'] : '0'; ?></td>
                        <td><?php echo $casualty['female_count'] > 0 ? $casualty['female_count'] : '0'; ?></td>
                        <td><strong><?php echo $casualty['count'] > 0 ? $casualty['count'] : '0'; ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.4 Damaged Houses -->
    <div class="section-title">A.4 Damaged Houses</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Totally Damaged</th>
                <th>Partially Damaged</th>
                <th>Total Damaged</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($damagedHouses) > 0): ?>
                <?php foreach ($damagedHouses as $house): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($house['barangay']); ?></strong></td>
                        <td><?php echo $house['totally_damaged'] > 0 ? $house['totally_damaged'] : '0'; ?></td>
                        <td><?php echo $house['partially_damaged'] > 0 ? $house['partially_damaged'] : '0'; ?></td>
                        <td><strong><?php echo $house['total_damaged'] > 0 ? $house['total_damaged'] : '0'; ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.5 Agriculture Damage -->
    <div class="section-title">A.5 Damage to Agriculture</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Classification</th>
                <th>Type</th>
                <th>Affected People</th>
                <th>Damage Value (PHP)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($agricultureDamage) > 0): ?>
                <?php foreach ($agricultureDamage as $agri): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($agri['barangay']); ?></strong></td>
                        <td><?php echo htmlspecialchars($agri['classification']); ?></td>
                        <td><?php echo !empty($agri['type']) ? htmlspecialchars($agri['type']) : '-'; ?></td>
                        <td><?php echo $agri['affected_people'] > 0 ? $agri['affected_people'] : '0'; ?></td>
                        <td><?php echo number_format($agri['damage_value'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- A.6 Infrastructure Damage -->
    <div class="section-title">A.6 Damage to Infrastructure</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Type</th>
                <th>Classification</th>
                <th>Name</th>
                <th>Total Damaged</th>
                <th>Cost (PHP)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($infrastructureDamage) > 0): ?>
                <?php foreach ($infrastructureDamage as $infra): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($infra['barangay']); ?></strong></td>
                        <td><?php echo htmlspecialchars($infra['type']); ?></td>
                        <td><?php echo htmlspecialchars($infra['classification']); ?></td>
                        <td><?php echo !empty($infra['name']) ? htmlspecialchars($infra['name']) : '-'; ?></td>
                        <td><?php echo $infra['total_damaged'] > 0 ? $infra['total_damaged'] : '0'; ?></td>
                        <td><?php echo !empty($infra['cost']) ? htmlspecialchars($infra['cost']) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.7 Other Assets Damage -->
    <div class="section-title">A.7 Damage to Other Assets</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Classification</th>
                <th>Particulars</th>
                <th>Quantity</th>
                <th>Cost (PHP)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($otherAssetsDamage) > 0): ?>
                <?php foreach ($otherAssetsDamage as $asset): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($asset['barangay']); ?></strong></td>
                        <td><?php echo htmlspecialchars($asset['classification']); ?></td>
                        <td><?php echo !empty($asset['particulars']) ? htmlspecialchars($asset['particulars']) : '-'; ?></td>
                        <td><?php echo $asset['quantity'] > 0 ? number_format($asset['quantity'], 2) : '0'; ?></td>
                        <td><?php echo !empty($asset['cost']) ? htmlspecialchars($asset['cost']) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.8 Road and Bridge Status -->
    <div class="section-title">A.8 Status of Roads and Bridges</div>
    <table>
        <thead>
            <tr>
                <th>Location</th>
                <th>Barangay</th>
                <th>Type</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($roadBridgeStatus) > 0): ?>
                <?php foreach ($roadBridgeStatus as $status): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($status['road_section']); ?></strong></td>
                        <td><?php echo htmlspecialchars($status['barangay']); ?></td>
                        <td><?php echo htmlspecialchars($status['type']); ?></td>
                        <td><?php echo htmlspecialchars($status['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.9 Power Supply Status -->
    <div class="section-title">A.9 Status of Power Supply</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Service Provider</th>
                <th>Interruption Date</th>
                <th>Restored Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($powerStatus) > 0): ?>
                <?php foreach ($powerStatus as $power): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($power['barangay']); ?></strong></td>
                        <td><?php echo htmlspecialchars($power['service_provider']); ?></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($power['interruption_datetime'])); ?></td>
                        <td><?php echo $power['restored_datetime'] ? date('M j, Y g:i A', strtotime($power['restored_datetime'])) : '-'; ?></td>
                        <td><?php echo $power['restored_datetime'] ? 'RESTORED' : 'INTERRUPTED'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- A.10 Communication Lines -->
    <div class="section-title">A.10 Status of Communication Lines</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Provider</th>
                <th>Interruption Date</th>
                <th>Restored Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($communicationStatus) > 0): ?>
                <?php foreach ($communicationStatus as $comm): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($comm['barangay']); ?></strong></td>
                        <td><?php echo htmlspecialchars($comm['telecom_provider']); ?></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($comm['interruption_date'])); ?></td>
                        <td><?php echo $comm['restored_date'] ? date('M j, Y g:i A', strtotime($comm['restored_date'])) : '-'; ?></td>
                        <td><?php echo $comm['restored_date'] ? 'RESTORED' : 'INTERRUPTED'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.11 Suspension of Work -->
    <div class="section-title">A.11 Suspension of Work</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Type</th>
                <th>Suspension Date</th>
                <th>Resumption Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($workSuspension) > 0): ?>
                <?php foreach ($workSuspension as $work): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($work['barangay']); ?></strong></td>
                        <td><?php echo htmlspecialchars($work['type']); ?></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($work['suspension_date'])); ?></td>
                        <td><?php echo $work['resumption_date'] && $work['resumption_date'] != '0000-00-00 00:00:00' ? date('M j, Y g:i A', strtotime($work['resumption_date'])) : '-'; ?></td>
                        <td><?php echo ($work['resumption_date'] && $work['resumption_date'] != '0000-00-00 00:00:00') ? 'RESUMED' : 'SUSPENDED'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.15 Suspension of Classes -->
    <div class="section-title">A.15 Suspension of Classes</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Level</th>
                <th>Type</th>
                <th>Suspension Date</th>
                <th>Resumption Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($classesSuspension) > 0): ?>
                <?php foreach ($classesSuspension as $classes): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($classes['barangay']); ?></strong></td>
                        <td><?php echo htmlspecialchars($classes['level']); ?></td>
                        <td><?php echo htmlspecialchars($classes['type']); ?></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($classes['suspension_date'])); ?></td>
                        <td><?php echo $classes['resumption_date'] ? date('M j, Y g:i A', strtotime($classes['resumption_date'])) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.18 Pre-emptive Evacuation of Animals -->
    <div class="section-title">A.18 Pre-emptive Evacuation of Animals</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Classification</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Shelter Location</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($animalEvacuation) > 0): ?>
                <?php foreach ($animalEvacuation as $animal): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($animal['barangay']); ?></strong></td>
                        <td><?php echo htmlspecialchars($animal['classification']); ?></td>
                        <td><?php echo htmlspecialchars($animal['type']); ?></td>
                        <td><?php echo $animal['quantity']; ?></td>
                        <td><?php echo !empty($animal['shelter_location']) ? htmlspecialchars($animal['shelter_location']) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- A.19 Families Assisted -->
    <div class="section-title">A.19 Status of Assistance to Affected Families</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Families Requiring</th>
                <th>Families Assisted</th>
                <th>Percentage (%)</th>
                <th>Assistance Type</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($familiesAssisted) > 0): ?>
                <?php foreach ($familiesAssisted as $family): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($family['barangay']); ?></strong></td>
                        <td><?php echo $family['families_requiring_assistance']; ?></td>
                        <td><?php echo $family['families_assisted']; ?></td>
                        <td><?php echo number_format($family['percentage_assisted'], 2); ?>%</td>
                        <td><?php echo htmlspecialchars($family['assistance_type']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.20 Assistance Provided (LGU and NGO) -->
    <div class="section-title">A.20 Assistance Provided (LGU and NGO)</div>
    <table>
        <thead>
            <tr>
                <th>Cluster</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Amount (PHP)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($assistanceProvided) > 0): ?>
                <?php foreach ($assistanceProvided as $assistance): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($assistance['cluster']); ?></strong></td>
                        <td><?php echo htmlspecialchars($assistance['type']); ?></td>
                        <td><?php echo number_format($assistance['quantity'], 2); ?></td>
                        <td><?php echo htmlspecialchars($assistance['unit']); ?></td>
                        <td><?php echo number_format($assistance['amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- A.21 Assistance Provided by LGU -->
    <div class="section-title">A.21 Assistance Provided by LGU</div>
    <table>
        <thead>
            <tr>
                <th>Barangay</th>
                <th>Recipient</th>
                <th>Cluster</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Amount (PHP)</th>
                <th>Source</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($assistanceLGU) > 0): ?>
                <?php foreach ($assistanceLGU as $lgu): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($lgu['barangay']); ?></strong></td>
                        <td><?php echo htmlspecialchars($lgu['recipient']); ?></td>
                        <td><?php echo htmlspecialchars($lgu['cluster']); ?></td>
                        <td><?php echo htmlspecialchars($lgu['type']); ?></td>
                        <td><?php echo $lgu['quantity']; ?></td>
                        <td><?php echo number_format($lgu['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($lgu['source']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="no-data">- NO SUBMITTED REPORT YET -</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 40px; text-align: center; font-size: 10pt; color: #7f8c8d;">
        <p><strong>Keep safe everyone!</strong><br>Baggao IC3 - MDRRMO</p>
        <p style="font-size: 8pt;">Generated: <?php echo $currentDate; ?></p>
    </div>
</body>
</html>

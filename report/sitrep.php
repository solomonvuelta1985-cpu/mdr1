<?php
// public_sitrep_simple.php
// Public Situational Report - Simple Design with REAL database data

require_once '../includes/config.php';

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

// Database functions (unchanged from original)
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Situational Report - MDRRMO LGU-BAGGAO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #ecf0f1;
            --text-color: #333;
            --light-text: #7f8c8d;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: clamp(0.5rem, 2vw, 1rem);
        }
        
        .container {
            max-width: clamp(320px, 95vw, 1200px);
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 0 auto;
            padding: 0;
            overflow: hidden;
        }
        
        .header {
            background: var(--primary-color);
            color: white;
            padding: clamp(1rem, 3vw, 2rem);
            border-bottom: clamp(2px, 0.3vw, 3px) solid var(--secondary-color);
            position: relative;
        }
        
        .header h1 {
            margin: 0;
            font-size: clamp(1.2rem, 4vw, 1.8rem);
            font-weight: 600;
        }
        
        .header p {
            margin: clamp(0.3rem, 1vw, 0.5rem) 0 0 0;
            opacity: 0.9;
            font-size: clamp(0.9rem, 2.5vw, 1.1rem);
        }
        
        .meta-info {
            background: var(--accent-color);
            padding: clamp(0.8rem, 2vw, 1rem) clamp(1rem, 3vw, 2rem);
            border-bottom: 1px solid #bdc3c7;
            font-size: clamp(0.8rem, 2vw, 0.9rem);
        }
        
        .section {
            padding: clamp(1rem, 2.5vw, 1.5rem) clamp(1rem, 3vw, 2rem);
            border-bottom: 1px solid var(--accent-color);
        }
        
        .section:last-child {
            border-bottom: none;
        }
        
        .section-title {
            font-size: clamp(1rem, 3vw, 1.2rem);
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: clamp(0.8rem, 2vw, 1rem);
            padding-bottom: clamp(0.3rem, 1vw, 0.5rem);
            border-bottom: clamp(1px, 0.2vw, 2px) solid var(--secondary-color);
        }
        
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }
        
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: clamp(0.75rem, 2vw, 0.9rem);
            min-width: 500px;
        }
        
        .table-custom th {
            background: #34495e;
            color: white;
            font-weight: 600;
            padding: clamp(0.5rem, 1.5vw, 0.8rem);
            text-align: left;
            border: none;
            white-space: nowrap;
        }
        
        .table-custom td {
            padding: clamp(0.5rem, 1.5vw, 0.8rem);
            border-bottom: 1px solid var(--accent-color);
        }
        
        .table-custom tr:hover {
            background: #f8f9fa;
        }
        
        .status-active {
            background: var(--success-color);
            color: white;
            padding: clamp(0.2rem, 0.8vw, 0.3rem) clamp(0.4rem, 1.2vw, 0.6rem);
            border-radius: 3px;
            font-size: clamp(0.7rem, 1.8vw, 0.8rem);
            display: inline-block;
        }
        
        .status-inactive {
            background: var(--danger-color);
            color: white;
            padding: clamp(0.2rem, 0.8vw, 0.3rem) clamp(0.4rem, 1.2vw, 0.6rem);
            border-radius: 3px;
            font-size: clamp(0.7rem, 1.8vw, 0.8rem);
            display: inline-block;
        }
        
        .status-warning {
            background: var(--warning-color);
            color: white;
            padding: clamp(0.2rem, 0.8vw, 0.3rem) clamp(0.4rem, 1.2vw, 0.6rem);
            border-radius: 3px;
            font-size: clamp(0.7rem, 1.8vw, 0.8rem);
            display: inline-block;
        }
        
        .no-data {
            color: var(--light-text);
            font-style: italic;
            text-align: center;
            padding: clamp(0.8rem, 2vw, 1rem);
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(clamp(150px, 20vw, 180px), 1fr));
            gap: clamp(0.8rem, 2vw, 1rem);
            padding: clamp(1rem, 2.5vw, 1.5rem) clamp(1rem, 3vw, 2rem);
            background: var(--accent-color);
        }
        
        .summary-item {
            background: white;
            padding: clamp(0.8rem, 2vw, 1.2rem);
            text-align: center;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .summary-number {
            font-size: clamp(1.4rem, 4vw, 1.8rem);
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: clamp(0.2rem, 0.8vw, 0.3rem);
        }
        
        .summary-label {
            font-size: clamp(0.75rem, 2vw, 0.85rem);
            color: var(--light-text);
            font-weight: 500;
        }
        
        .footer {
            background: #34495e;
            color: white;
            padding: clamp(0.8rem, 2vw, 1rem) clamp(1rem, 3vw, 2rem);
            text-align: center;
            font-size: clamp(0.8rem, 2vw, 0.9rem);
        }
        
        .action-bar {
            background: var(--accent-color);
            padding: clamp(0.8rem, 2vw, 1rem) clamp(1rem, 3vw, 2rem);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #bdc3c7;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-actions {
            position: static;
            margin-top: clamp(0.8rem, 2vw, 1rem);
            display: flex;
            gap: clamp(0.3rem, 1vw, 0.5rem);
            flex-wrap: wrap;
        }

        .btn-print, .btn-export {
            background: white;
            border: 1px solid rgba(255,255,255,0.3);
            color: var(--primary-color);
            padding: clamp(0.4rem, 1.2vw, 0.5rem) clamp(0.8rem, 2vw, 1rem);
            border-radius: 4px;
            cursor: pointer;
            font-size: clamp(0.8rem, 2vw, 0.9rem);
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-print:hover, .btn-export:hover {
            background: var(--accent-color);
        }

        .export-dropdown {
            position: relative;
            display: inline-block;
        }

        .export-menu {
            display: none;
            position: absolute;
            right: 0;
            background: white;
            min-width: clamp(140px, 20vw, 150px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 4px;
            overflow: hidden;
        }

        .export-menu.show {
            display: block;
        }

        .export-menu a {
            color: var(--primary-color);
            padding: clamp(0.6rem, 1.5vw, 0.75rem) clamp(0.8rem, 2vw, 1rem);
            text-decoration: none;
            display: block;
            transition: background 0.2s;
            font-size: clamp(0.8rem, 2vw, 0.9rem);
        }

        .export-menu a:hover {
            background: var(--accent-color);
        }

        @media print {
            .header-actions, .action-bar, .no-print {
                display: none !important;
            }
        }
        
        @media (min-width: 768px) {
            .header-actions {
                position: absolute;
                top: clamp(1rem, 3vw, 2rem);
                right: clamp(1rem, 3vw, 2rem);
                margin-top: 0;
            }
        }
        
        @media (max-width: 480px) {
            .summary-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>MDRRMO LGU-BAGGAO</h1>
            <p style="font-size: clamp(0.9rem, 2.5vw, 1.1rem); margin: clamp(0.2rem, 0.8vw, 0.3rem) 0;">Situational Report No. <?php echo $sitrepMetadata['report_number']; ?></p>
            <?php if (!empty($sitrepMetadata['weather_system'])): ?>
                <p style="margin: clamp(0.2rem, 0.8vw, 0.3rem) 0;">Weather System: <strong><?php echo htmlspecialchars($sitrepMetadata['weather_system']); ?></strong></p>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="header-actions">
                <button class="btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <div class="export-dropdown">
                    <button class="btn-export" onclick="toggleExportMenu()">
                        <i class="fas fa-download"></i> Export <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="export-menu" id="exportMenu">
                        <a href="sitrep_export.php?format=pdf" target="_blank">
                            <i class="fas fa-file-pdf"></i> Export as PDF
                        </a>
                        <a href="sitrep_export.php?format=word" target="_blank">
                            <i class="fas fa-file-word"></i> Export as Word
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Metadata -->
        <div class="meta-info">
            <div class="row">
                <div class="col-md-4">
                    <strong>Report ID:</strong> SITREP-<?php echo str_pad($sitrepMetadata['report_number'], 4, '0', STR_PAD_LEFT); ?><br>
                    <strong>Report Date:</strong> <?php echo $currentDate; ?><br>
                    <strong>Update Interval:</strong> Every <?php echo $sitrepMetadata['update_interval_hours']; ?> hour(s)
                </div>
                <div class="col-md-4">
                    <strong>Data Source:</strong> Live Database System<br>
                    <strong>Mayor:</strong> <?php echo $sitrepMetadata['mayor_status']; ?><br>
                    <strong>Vice Mayor:</strong> <?php echo $sitrepMetadata['vice_mayor_status']; ?>
                </div>
                <div class="col-md-4">
                    <strong>Weather Condition:</strong><br>
                    - Clouds: <strong><?php echo htmlspecialchars($sitrepMetadata['weather_clouds']); ?></strong><br>
                    - Rains: <strong><?php echo htmlspecialchars($sitrepMetadata['weather_rains']); ?></strong><br>
                    - Winds: <strong><?php echo htmlspecialchars($sitrepMetadata['weather_winds']); ?></strong>
                </div>
            </div>
        </div>
        
        <!-- Summary Statistics -->
        <div class="summary-grid">
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
        <div class="section">
            <div class="section-title">A.1 Related Incidents</div>
            <div class="table-responsive">
                <table class="table-custom">
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
            </div>
        </div>
        
        <!-- A.2 Affected Population -->
        <div class="section">
            <div class="section-title">A.2 Affected Population</div>
            <div class="table-responsive">
                <table class="table-custom">
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
            </div>
        </div>
        
        <!-- A.3 Casualties -->
        <div class="section">
            <div class="section-title">A.3 Casualties</div>
            <div class="table-responsive">
                <table class="table-custom">
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
            </div>
        </div>
        
        <!-- A.4 Damaged Houses -->
        <div class="section">
            <div class="section-title">A.4 Damaged Houses</div>
            <div class="table-responsive">
                <table class="table-custom">
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
            </div>
        </div>
        
        <!-- A.8 Road and Bridge Status -->
        <div class="section">
            <div class="section-title">A.8 Status of Roads and Bridges</div>
            <div class="table-responsive">
                <table class="table-custom">
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
                                    <td>
                                        <?php 
                                        $statusClass = 'status-active';
                                        $statusText = $status['status'];
                                        
                                        if ($status['status'] === 'Not Passable') {
                                            $statusClass = 'status-inactive';
                                        } elseif (strpos($status['status'], 'Passable to') !== false) {
                                            $statusClass = 'status-warning';
                                        }
                                        ?>
                                        <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- A.9 Power Supply Status -->
        <div class="section">
            <div class="section-title">A.9 Status of Power Supply</div>
            <div class="table-responsive">
                <table class="table-custom">
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
                                    <td>
                                        <span class="<?php echo $power['restored_datetime'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $power['restored_datetime'] ? 'RESTORED' : 'INTERRUPTED'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- A.10 Communication Lines -->
        <div class="section">
            <div class="section-title">A.10 Status of Communication Lines</div>
            <div class="table-responsive">
                <table class="table-custom">
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
                                    <td>
                                        <span class="<?php echo $comm['restored_date'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $comm['restored_date'] ? 'RESTORED' : 'INTERRUPTED'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- A.11 Suspension of Work -->
        <div class="section">
            <div class="section-title">A.11 Suspension of Work</div>
            <div class="table-responsive">
                <table class="table-custom">
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
                                    <td>
                                        <span class="<?php echo ($work['resumption_date'] && $work['resumption_date'] != '0000-00-00 00:00:00') ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo ($work['resumption_date'] && $work['resumption_date'] != '0000-00-00 00:00:00') ? 'RESUMED' : 'SUSPENDED'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- A.5 Agriculture Damage -->
        <div class="section">
            <div class="section-title">A.5 Damage to Agriculture</div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Barangay</th>
                            <th>Classification</th>
                            <th>Type</th>
                            <th>Affected People</th>
                            <th>Damage Value (PHP)</th>
                            <th>Production Loss Volume</th>
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
                                    <td><?php echo $agri['production_loss_volume'] > 0 ? number_format($agri['production_loss_volume'], 2) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- A.6 Infrastructure Damage -->
        <div class="section">
            <div class="section-title">A.6 Damage to Infrastructure</div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Barangay</th>
                            <th>Type</th>
                            <th>Classification</th>
                            <th>Name</th>
                            <th>Totally Damaged</th>
                            <th>Partially Damaged</th>
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
                                    <td><?php echo $infra['totally_damaged'] > 0 ? $infra['totally_damaged'] : '0'; ?></td>
                                    <td><?php echo $infra['partially_damaged'] > 0 ? $infra['partially_damaged'] : '0'; ?></td>
                                    <td><?php echo !empty($infra['cost']) ? htmlspecialchars($infra['cost']) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- A.7 Other Assets Damage -->
        <div class="section">
            <div class="section-title">A.7 Damage to Other Assets</div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Barangay</th>
                            <th>Classification</th>
                            <th>Particulars</th>
                            <th>Unit</th>
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
                                    <td><?php echo !empty($asset['unit']) ? htmlspecialchars($asset['unit']) : '-'; ?></td>
                                    <td><?php echo $asset['quantity'] > 0 ? number_format($asset['quantity'], 2) : '0'; ?></td>
                                    <td><?php echo !empty($asset['cost']) ? htmlspecialchars($asset['cost']) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- A.15 Suspension of Classes -->
        <div class="section">
            <div class="section-title">A.15 Suspension of Classes</div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Barangay</th>
                            <th>Level</th>
                            <th>Type</th>
                            <th>Suspension Date</th>
                            <th>Resumption Date</th>
                            <th>Reason</th>
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
                                    <td><?php echo htmlspecialchars($classes['suspension_reason']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- A.18 Pre-emptive Evacuation of Animals -->
        <div class="section">
            <div class="section-title">A.18 Pre-emptive Evacuation of Animals</div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Barangay</th>
                            <th>Classification</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Reason</th>
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
                                    <td><?php echo htmlspecialchars($animal['evacuation_reason']); ?></td>
                                    <td><?php echo !empty($animal['shelter_location']) ? htmlspecialchars($animal['shelter_location']) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- A.19 Families Assisted -->
        <div class="section">
            <div class="section-title">A.19 Status of Assistance to Affected Families</div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Barangay</th>
                            <th>Families Requiring Assistance</th>
                            <th>Families Assisted</th>
                            <th>Percentage (%)</th>
                            <th>Assistance Type</th>
                            <th>Provider</th>
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
                                    <td><?php echo htmlspecialchars($family['assistance_provider']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- A.20 Assistance Provided (LGU and NGO) -->
        <div class="section">
            <div class="section-title">A.20 Assistance Provided (LGU and NGO)</div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Cluster</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Cost per Unit (PHP)</th>
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
                                    <td><?php echo number_format($assistance['cost_per_unit'], 2); ?></td>
                                    <td><?php echo number_format($assistance['amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- A.21 Assistance Provided by LGU -->
        <div class="section">
            <div class="section-title">A.21 Assistance Provided by LGU</div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Barangay</th>
                            <th>Recipient</th>
                            <th>Cluster</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Cost per Unit (PHP)</th>
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
                                    <td><?php echo htmlspecialchars($lgu['unit']); ?></td>
                                    <td><?php echo number_format($lgu['cost_per_unit'], 2); ?></td>
                                    <td><?php echo number_format($lgu['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($lgu['source']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="no-data">- NO SUBMITTED REPORT YET -</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <div>
                <strong>Last Updated:</strong> <?php echo $currentDate; ?>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Keep safe everyone!<br>Baggao IC3 - MDRRMO</p>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Toggle export dropdown menu
        function toggleExportMenu() {
            const menu = document.getElementById('exportMenu');
            menu.classList.toggle('show');
        }

        // Close dropdown if clicked outside
        window.onclick = function(event) {
            if (!event.target.matches('.btn-export')) {
                const dropdowns = document.getElementsByClassName('export-menu');
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        // Auto-refresh based on update interval from database
        setTimeout(function() {
            window.location.reload();
        }, <?php echo $sitrepMetadata['update_interval_hours'] * 3600000; ?>); // Convert hours to milliseconds
    </script>
</body>
</html>
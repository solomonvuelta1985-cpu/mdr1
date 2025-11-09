<?php
// public_sitrep_simple.php
// Public Situational Report - Simple Design with REAL database data

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

// Database functions
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

// Get ACTUAL data
$incidents = getLatestIncidents($pdo);
$affectedPopulation = getAffectedPopulation($pdo);
$casualties = getCasualtiesSummary($pdo);
$damagedHouses = getDamagedHouses($pdo);
$roadBridgeStatus = getRoadBridgeStatus($pdo);
$powerStatus = getPowerSupplyStatus($pdo);
$communicationStatus = getCommunicationStatus($pdo);
$workSuspension = getWorkSuspension($pdo);

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
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px auto;
            padding: 0;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 2rem;
            border-bottom: 3px solid #3498db;
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .meta-info {
            background: #ecf0f1;
            padding: 1rem 2rem;
            border-bottom: 1px solid #bdc3c7;
            font-size: 0.9rem;
        }
        
        .section {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .section:last-child {
            border-bottom: none;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #3498db;
        }
        
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        
        .table-custom th {
            background: #34495e;
            color: white;
            font-weight: 600;
            padding: 0.8rem;
            text-align: left;
            border: none;
        }
        
        .table-custom td {
            padding: 0.8rem;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .table-custom tr:hover {
            background: #f8f9fa;
        }
        
        .status-active {
            background: #27ae60;
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .status-inactive {
            background: #e74c3c;
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .status-warning {
            background: #f39c12;
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .no-data {
            color: #7f8c8d;
            font-style: italic;
            text-align: center;
            padding: 1rem;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            padding: 1.5rem 2rem;
            background: #ecf0f1;
        }
        
        .summary-item {
            background: white;
            padding: 1.2rem;
            text-align: center;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .summary-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }
        
        .summary-label {
            font-size: 0.85rem;
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .footer {
            background: #34495e;
            color: white;
            padding: 1rem 2rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .action-bar {
            background: #ecf0f1;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #bdc3c7;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            
            .header {
                padding: 1.5rem;
            }
            
            .section {
                padding: 1rem;
            }
            
            .summary-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
            
            .table-custom {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>MDRRMO LGU-BAGGAO</h1>
            <p>Public Situational Report - Live Database Data</p>
        </div>
        
        <!-- Metadata -->
        <div class="meta-info">
            <div class="row">
                <div class="col-md-6">
                    <strong>Report Date:</strong> <?php echo $currentDate; ?>
                </div>
                <div class="col-md-6">
                    <strong>Data Source:</strong> Live Database System
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
                            <td colspan="4" class="no-data">- NO REPORTED YET -</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- A.2 Affected Population -->
        <div class="section">
            <div class="section-title">A.2 Affected Population</div>
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
                            <td colspan="5" class="no-data">- NO REPORTED YET -</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- A.3 Casualties -->
        <div class="section">
            <div class="section-title">A.3 Casualties</div>
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
                            <td colspan="4" class="no-data">- NO REPORTED YET -</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- A.4 Damaged Houses -->
        <div class="section">
            <div class="section-title">A.4 Damaged Houses</div>
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
                            <td colspan="4" class="no-data">- NO REPORTED YET -</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- A.8 Road and Bridge Status -->
        <div class="section">
            <div class="section-title">A.8 Status of Roads and Bridges</div>
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
                            <td colspan="4" class="no-data">- NO REPORTED YET -</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- A.9 Power Supply Status -->
        <div class="section">
            <div class="section-title">A.9 Status of Power Supply</div>
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
                            <td colspan="5" class="no-data">- NO REPORTED YET -</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- A.10 Communication Lines -->
        <div class="section">
            <div class="section-title">A.10 Status of Communication Lines</div>
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
                            <td colspan="5" class="no-data">- NO REPORTED YET -</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- A.11 Suspension of Work -->
        <div class="section">
            <div class="section-title">A.11 Suspension of Work</div>
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
                            <td colspan="5" class="no-data">- NO REPORTED YET -</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

    <script>
        // Auto-refresh every 5 minutes
        setTimeout(function() {
            window.location.reload();
        }, 300000);
    </script>
</body>
</html>
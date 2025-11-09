<?php
/**
 * MDRRMO Situational Report - Complete Format
 * PUBLIC ACCESS - Displays all annex sections
 * Only ADMIN can edit
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in (for admin check)
$is_admin = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    $is_admin = ($_SESSION['user_role'] === 'admin');
}

// Get SITREP settings
$settings = [
    'report_number' => 3,
    'weather_system' => '',
    'report_date' => date('F d, Y'),
    'report_time' => date('h:i a'),
    'mayor_status' => 'In',
    'vm_status' => 'In',
    'weather_clouds' => 'CLEAR SKY',
    'weather_rains' => 'NONE',
    'weather_winds' => 'NORMAL'
];

// Try to get from database
try {
    $stmt = db_query("SELECT * FROM sitrep_settings ORDER BY id DESC LIMIT 1");
    if ($stmt) {
        $db_settings = $stmt->fetch();
        if ($db_settings) {
            $settings = array_merge($settings, $db_settings);
        }
    }
} catch (Exception $e) {}

// Get data from database
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MDRRMO Situational Report No. <?php echo $settings['report_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            font-size: 11pt;
            line-height: 1.6;
            min-height: 100vh;
        }
        .sitrep-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 45px 50px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            position: relative;
        }
        .sitrep-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%);
            border-radius: 16px 16px 0 0;
        }
        .header {
            text-align: center;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px 25px;
            margin: -45px -50px 35px -50px;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .header h1 {
            font-size: 1.9rem;
            font-weight: 700;
            margin: 0 0 10px 0;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .header h2 {
            font-size: 1.4rem;
            margin: 8px 0;
            color: #ffd700;
            font-weight: 600;
        }
        .header p {
            margin: 5px 0;
            font-size: 1.05rem;
            color: #e0e0e0;
        }
        .header p strong {
            color: #ffd700;
        }
        .section {
            margin-bottom: 22px;
            padding: 18px 22px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 5px solid #667eea;
            transition: all 0.3s ease;
        }
        .section:hover {
            background: #e9ecef;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transform: translateX(5px);
        }
        .section-title {
            font-weight: 700;
            margin: 0 0 12px 0;
            color: #1e3c72;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title::before {
            content: '▸';
            color: #667eea;
            font-size: 1.3rem;
        }
        .section-content {
            margin-left: 25px;
            line-height: 2;
            color: #333;
            font-size: 1.02rem;
        }
        /* EMPHASIZE DATA VALUES */
        .data-value {
            font-weight: 700;
            color: #dc3545;
            font-size: 1.15rem;
        }
        .data-value-active {
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 4px 14px;
            border-radius: 20px;
            box-shadow: 0 3px 8px rgba(245, 87, 108, 0.4);
            display: inline-block;
            font-size: 1.05rem;
        }
        .data-value-clear {
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #5cb85c 0%, #4cae4c 100%);
            padding: 4px 14px;
            border-radius: 20px;
            box-shadow: 0 3px 8px rgba(76, 174, 76, 0.4);
            display: inline-block;
        }
        .data-value-pending {
            font-weight: 600;
            color: #6c757d;
            background: #e9ecef;
            padding: 4px 14px;
            border-radius: 20px;
            font-style: italic;
            display: inline-block;
        }
        .export-buttons {
            text-align: center;
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .export-buttons .btn {
            margin: 5px;
            padding: 12px 28px;
            font-weight: 600;
            border-radius: 25px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .export-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.25);
        }
        .export-buttons .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
        }
        .export-buttons .btn-danger {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            border: none;
        }
        .export-buttons .btn-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
        }
        .export-buttons .btn-warning {
            background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
            border: none;
            color: #333;
        }
        .footer {
            text-align: center;
            margin-top: 45px;
            padding: 25px 20px 0 20px;
            border-top: 3px solid #667eea;
            font-weight: 700;
            font-size: 1.15rem;
            color: #1e3c72;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin-left: -50px;
            margin-right: -50px;
            margin-bottom: -45px;
            padding-bottom: 25px;
            border-radius: 0 0 16px 16px;
        }
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: white;
            border-radius: 20px;
            margin: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .status-indicator strong {
            color: #1e3c72;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .export-buttons {
                display: none;
            }
            .sitrep-container {
                box-shadow: none;
                padding: 30px;
            }
            .sitrep-container::before {
                display: none;
            }
            .header {
                background: #1e3c72;
                margin: -30px -30px 20px -30px;
            }
            .section {
                page-break-inside: avoid;
                background: white;
                border: 1px solid #ddd;
            }
            .section:hover {
                transform: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="sitrep-container">
        <!-- Export Buttons -->
        <div class="export-buttons">
            <button class="btn btn-success btn-lg me-2" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Print
            </button>
            <a href="sitrep_export.php?type=pdf" class="btn btn-danger btn-lg me-2">
                <i class="fas fa-file-pdf me-2"></i> PDF
            </a>
            <a href="sitrep_export.php?type=word" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-file-word me-2"></i> Word
            </a>
            <?php if ($is_admin): ?>
            <a href="edit_sitrep.php" class="btn btn-warning btn-lg">
                <i class="fas fa-edit me-2"></i> Edit Settings
            </a>
            <?php endif; ?>
        </div>

        <!-- Header -->
        <div class="header">
            <h1>MDRRMO LGU-BAGGAO</h1>
            <h2>Situational Report No. <?php echo $settings['report_number']; ?></h2>
            <p><strong>Weather System:</strong> <?php echo htmlspecialchars($settings['weather_system']); ?></p>
            <p><strong>As of</strong> <?php echo $settings['report_date']; ?></p>
            <p><?php echo $settings['report_time']; ?></p>
        </div>

        <!-- Status -->
        <div class="section">
            <div class="section-title">Officials Status</div>
            <div class="section-content">
                <span class="status-indicator">
                    <strong>Mayor:</strong> <?php echo htmlspecialchars($settings['mayor_status']); ?>
                </span>
                <span class="status-indicator">
                    <strong>Vice Mayor:</strong> <?php echo htmlspecialchars($settings['vm_status']); ?>
                </span>
            </div>
        </div>

        <!-- Weather Condition -->
        <div class="section">
            <div class="section-title">Weather Condition:</div>
            <div class="section-content">
                - Clouds: <span class="data-value"><?php echo htmlspecialchars($settings['weather_clouds']); ?></span><br>
                - Rains: <span class="data-value"><?php echo htmlspecialchars($settings['weather_rains']); ?></span><br>
                - Winds: <span class="data-value"><?php echo htmlspecialchars($settings['weather_winds']); ?></span>
            </div>
        </div>

        <!-- A.1 Related Incidents -->
        <div class="section">
            <div class="section-title">A. 1 Related Incidents</div>
            <div class="section-content">
                - <?php if ($data['incidents'] > 0): ?>
                    <span class="data-value-active"><?php echo $data['incidents']; ?> incident(s)</span>
                <?php else: ?>
                    <span class="data-value-clear">NONE</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- A.2 Affected Population -->
        <div class="section">
            <div class="section-title">A. 2 Affected Population</div>
            <div class="section-content">
                <?php if ($data['affected_families'] > 0 || $data['affected_persons'] > 0): ?>
                    - Total Families: <span class="data-value-active"><?php echo number_format($data['affected_families']); ?></span><br>
                    - Total Persons: <span class="data-value-active"><?php echo number_format($data['affected_persons']); ?></span>
                <?php else: ?>
                    - <span class="data-value-clear">NONE</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- A.3 Casualties -->
        <div class="section">
            <div class="section-title">A. 3 Casualties</div>
            <div class="section-content">
                - <?php if ($data['casualties'] > 0): ?>
                    <span class="data-value-active"><?php echo $data['casualties']; ?> casualty(ies)</span>
                <?php else: ?>
                    <span class="data-value-clear">NONE</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- A.4 Damage House -->
        <div class="section">
            <div class="section-title">A. 4 Damage House</div>
            <div class="section-content">
                - <?php if ($data['houses'] > 0): ?>
                    <span class="data-value-active"><?php echo $data['houses']; ?> house(s)</span>
                <?php else: ?>
                    <span class="data-value-clear">NONE</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- A.5 Damage Losses to Agri -->
        <div class="section">
            <div class="section-title">A. 5 Damage Losses to Agri</div>
            <div class="section-content">
                - <?php if ($data['agriculture'] > 0): ?>
                    <span class="data-value-active"><?php echo $data['agriculture']; ?> report(s)</span>
                <?php else: ?>
                    <span class="data-value-pending">NO REPORTED YET</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- A.6 Damage to Infrastructure -->
        <div class="section">
            <div class="section-title">A. 6 Damage to Infrastructure</div>
            <div class="section-content">
                - <span class="data-value-pending">NO REPORTED YET</span>
            </div>
        </div>

        <!-- A.7 Damage to other Asset -->
        <div class="section">
            <div class="section-title">A. 7 Damage to other Asset</div>
            <div class="section-content">
                - <span class="data-value-pending">NO REPORTED YET</span>
            </div>
        </div>

        <!-- A.8 Status of Roads and Bridges -->
        <div class="section">
            <div class="section-title">A. 8 Status of Roads and Bridges</div>
            <div class="section-content">
                - Bridges are <span class="data-value-clear">all passable</span> as of <?php echo date('g:i a, F d, Y'); ?><br>
                - Roads are <span class="data-value-clear">all passable</span> as of <?php echo date('g:i a, F d, Y'); ?>
            </div>
        </div>

        <!-- A.9 Status of Power Supply -->
        <div class="section">
            <div class="section-title">A. 9 Status of Power Supply</div>
            <div class="section-content">
                - <span class="data-value-clear">All lines are in good condition</span>
            </div>
        </div>

        <!-- A.10 Status of Communication Lines -->
        <div class="section">
            <div class="section-title">A. 10 Status of Communication Lines</div>
            <div class="section-content">
                - <span class="data-value-clear">All lines are in good condition</span>
            </div>
        </div>

        <!-- A.11 Suspension of Work -->
        <div class="section">
            <div class="section-title">A. 11 Suspension of Work</div>
            <div class="section-content">
                - <span class="data-value-clear">NONE</span>
            </div>
        </div>

        <!-- A.12 Suspension of Classes -->
        <div class="section">
            <div class="section-title">A. 12 Suspension of Classes</div>
            <div class="section-content">
                - <span class="data-value-clear">NONE</span>
            </div>
        </div>

        <!-- A.13 Declaration of state of calamity -->
        <div class="section">
            <div class="section-title">A. 13 Declaration of state of calamity</div>
            <div class="section-content">
                - <span class="data-value-clear">NONE</span>
            </div>
        </div>

        <!-- A.14 Pre-Emptive Evac(people) -->
        <div class="section">
            <div class="section-title">A. 14 Pre-Emptive Evac(people)</div>
            <div class="section-content">
                - <span class="data-value-pending">NO REPORTED YET</span>
            </div>
        </div>

        <!-- A.18 Pre-Emp Evac (animals) -->
        <div class="section">
            <div class="section-title">A. 18 Pre-Emp Evac (animals)</div>
            <div class="section-content">
                - <span class="data-value-pending">NO REPORTED YET</span>
            </div>
        </div>

        <!-- A.19 Families Assisted -->
        <div class="section">
            <div class="section-title">A. 19 Families Assisted</div>
            <div class="section-content">
                - <span class="data-value-pending">NO REPORTED YET</span>
            </div>
        </div>

        <!-- A.15 Assistance provided to families -->
        <div class="section">
            <div class="section-title">A. 15 Assistance provided to families</div>
            <div class="section-content">
                - <span class="data-value-clear">NONE</span>
            </div>
        </div>

        <!-- A.16 Assistance provided to Lgu and Agencies -->
        <div class="section">
            <div class="section-title">A. 16 Assistance provided to Lgu and Agencies</div>
            <div class="section-content">
                - <span class="data-value-clear">NONE</span>
            </div>
        </div>

        <!-- PREPAREDNESS MEASURES -->
        <div class="section">
            <div class="section-title">PREPAREDNESS MEASURE BEING UNDERTAKEN/ACTIONS TAKEN:</div>
            <div class="section-content">
                - Continuous Monitoring on PAGASA Latest Weather Update and Advisories<br>
                - Close Coordination with BDRRMCs<br>
                - Continuous Sending of Sitrep to OCD, PDRRMC, and Other Agencies<br>
                - All personnel are on alert status
            </div>
        </div>

        <!-- WATER LEVEL -->
        <div class="section">
            <div class="section-title">WATER LEVEL:</div>
            <div class="section-content">
                - Abusag Bridge water level: <span class="data-value-clear">Normal</span> as of <?php echo date('g:i a, F d, Y'); ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Keep safe everyone!<br>
            Baggao IC3
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

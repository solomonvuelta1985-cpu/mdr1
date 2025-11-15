<?php
/**
 * Terminal Report Generator for MDRRM-ARMS
 * Generates 16-page Terminal Reports in DOCX format
 *
 * @author MDRRMO Baggao
 * @version 1.0 - Phase 1 (Using Existing 50% Data)
 * @date 2025-01-14
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/config.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\SimpleType\Jc;

class TerminalReportGenerator {

    private $phpWord;
    private $section;
    private $conn;
    private $eventId;
    private $eventData;

    // Font styles
    private $fontTitle = ['name' => 'Arial', 'size' => 14, 'bold' => true];
    private $fontHeader = ['name' => 'Arial', 'size' => 12, 'bold' => true];
    private $fontNormal = ['name' => 'Arial', 'size' => 11];
    private $fontSmall = ['name' => 'Arial', 'size' => 10];

    /**
     * Constructor
     */
    public function __construct($eventId = null) {
        global $pdo;
        $this->conn = $pdo;
        $this->eventId = $eventId;
        $this->phpWord = new PhpWord();

        // Set default font
        $this->phpWord->setDefaultFontName('Arial');
        $this->phpWord->setDefaultFontSize(11);

        // Load event data
        if ($eventId) {
            $this->loadEventData();
        }
    }

    /**
     * Load disaster event data
     */
    private function loadEventData() {
        $sql = "SELECT * FROM disaster_events WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $this->eventData = $stmt->fetch();
    }

    /**
     * Generate complete Terminal Report
     */
    public function generate() {
        // Create section with page setup
        $this->section = $this->phpWord->addSection([
            'marginLeft' => 1440,   // 1 inch
            'marginRight' => 1440,
            'marginTop' => 1440,
            'marginBottom' => 1440
        ]);

        // Add header and footer
        $this->addHeaderFooter();

        // Generate all sections
        $this->addCoverPage();
        $this->addSectionI_SituationOverview();
        $this->addSectionII_PreemptiveEvacuation();
        $this->addSectionIII_CalamityDeclaration();
        $this->addSectionIV_ResponseAssets();
        $this->addSectionV_Effects();
        $this->addSectionVI_ResponseOperations();
        $this->addSectionVII_AssistanceExtended();
        $this->addSectionVIII_PreparednessMeasures();

        return $this;
    }

    /**
     * Add header and footer to all pages
     */
    private function addHeaderFooter() {
        // Header
        $header = $this->section->addHeader();
        $header->addText(
            'Municipality of Baggao, Province of Cagayan',
            ['name' => 'Arial', 'size' => 10, 'bold' => true],
            ['alignment' => Jc::CENTER]
        );
        $header->addText(
            'MUNICIPAL DISASTER RISK REDUCTION AND MANAGEMENT OFFICE',
            ['name' => 'Arial', 'size' => 9],
            ['alignment' => Jc::CENTER]
        );

        // Footer with page number
        $footer = $this->section->addFooter();
        $footer->addPreserveText(
            'Page {PAGE} of {NUMPAGES}',
            ['name' => 'Arial', 'size' => 9],
            ['alignment' => Jc::CENTER]
        );
    }

    /**
     * Cover Page / Title Page
     */
    private function addCoverPage() {
        $eventName = $this->eventData['event_name'] ?? 'TERMINAL REPORT';
        $startDate = isset($this->eventData['start_date']) ?
            date('F d, Y', strtotime($this->eventData['start_date'])) : '';
        $endDate = isset($this->eventData['end_date']) ?
            date('F d, Y', strtotime($this->eventData['end_date'])) : '';

        $this->section->addText(
            'REPUBLIC OF THE PHILIPPINES',
            ['name' => 'Arial', 'size' => 12, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );

        $this->section->addText(
            'PROVINCE OF CAGAYAN',
            ['name' => 'Arial', 'size' => 12, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );

        $this->section->addText(
            'MUNICIPALITY OF BAGGAO',
            ['name' => 'Arial', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 240]
        );

        $this->section->addTextBreak(3);

        $this->section->addText(
            'TERMINAL REPORT',
            ['name' => 'Arial', 'size' => 18, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 240]
        );

        $this->section->addText(
            $eventName,
            ['name' => 'Arial', 'size' => 16, 'bold' => true, 'color' => '0070C0'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );

        if ($startDate && $endDate) {
            $this->section->addText(
                $startDate . ' - ' . $endDate,
                ['name' => 'Arial', 'size' => 12],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 480]
            );
        }

        $this->section->addPageBreak();
    }

    /**
     * SECTION I: SITUATION OVERVIEW
     */
    private function addSectionI_SituationOverview() {
        $this->addSectionTitle('I. SITUATION OVERVIEW');

        // A. Weather Forecast
        $this->addSubsectionTitle('A. WEATHER FORECAST');
        $weatherData = $this->getWeatherData();
        if (empty($weatherData)) {
            $this->section->addText('No weather forecast data available.', $this->fontNormal);
        } else {
            // TODO: Display weather data from weather_data table
            $this->section->addText('[Weather forecast data will be displayed here]', $this->fontNormal);
        }
        $this->section->addTextBreak();

        // B. Present Weather
        $this->addSubsectionTitle('B. PRESENT WEATHER');
        $this->section->addText('No present weather data available.', $this->fontNormal);
        $this->section->addTextBreak();

        // C. Water Level Station
        $this->addSubsectionTitle('C. WATER LEVEL STATION');
        $waterLevels = $this->getWaterLevelData();
        if (empty($waterLevels)) {
            $this->section->addText('No water level monitoring data available.', $this->fontNormal);
        } else {
            // TODO: Display water level data
            $this->section->addText('[Water level data will be displayed here]', $this->fontNormal);
        }
        $this->section->addTextBreak();

        // D. Status of Lifelines
        $this->addSubsectionTitle('D. STATUS OF LIFELINES');

        // D.1 - Roads and Bridges (annex8 - EXISTING DATA)
        $this->addSubsectionTitle('D.1 ROADS AND BRIDGES', 12);
        $this->displayRoadsBridges();
        $this->section->addTextBreak();

        // D.2 - Electricity (annex9 - EXISTING DATA)
        $this->addSubsectionTitle('D.2 ELECTRICITY', 12);
        $this->displayElectricityStatus();
        $this->section->addTextBreak();

        // D.3 - Communications (annex11 - EXISTING DATA)
        $this->addSubsectionTitle('D.3 COMMUNICATIONS', 12);
        $this->displayCommunicationStatus();

        $this->section->addPageBreak();
    }

    /**
     * Display Roads and Bridges Status from annex8
     */
    private function displayRoadsBridges() {
        $sql = "SELECT * FROM annex8_road_bridge_status WHERE event_id = ? OR event_id IS NULL ORDER BY barangay";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            // Create table
            $table = $this->section->addTable([
                'borderSize' => 6,
                'borderColor' => '000000',
                'width' => 100 * 50,
                'unit' => 'pct'
            ]);

            // Header row
            $table->addRow(400);
            $table->addCell(2000)->addText('Type', $this->fontHeader);
            $table->addCell(2000)->addText('Classification', $this->fontHeader);
            $table->addCell(3000)->addText('Road Section/Bridge Name', $this->fontHeader);
            $table->addCell(2000)->addText('Barangay', $this->fontHeader);
            $table->addCell(2000)->addText('Status', $this->fontHeader);

            // Data rows
            foreach ($result as $row) {
                $table->addRow();
                $table->addCell(2000)->addText($row['type'], $this->fontSmall);
                $table->addCell(2000)->addText($row['classification'] ?? '', $this->fontSmall);
                $table->addCell(3000)->addText($row['road_section'] ?? '', $this->fontSmall);
                $table->addCell(2000)->addText($row['barangay'], $this->fontSmall);
                $table->addCell(2000)->addText($row['status'], $this->fontSmall);
            }
        } else {
            $this->section->addText('No roads and bridges status data available.', $this->fontNormal);
        }
    }

    /**
     * Display Electricity Status from annex9
     */
    private function displayElectricityStatus() {
        $sql = "SELECT * FROM annex9_power_supply WHERE event_id = ? OR event_id IS NULL ORDER BY barangay";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            $table = $this->section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

            // Header
            $table->addRow(400);
            $table->addCell(3000)->addText('Service Provider', $this->fontHeader);
            $table->addCell(2000)->addText('Barangay', $this->fontHeader);
            $table->addCell(3000)->addText('Interruption Time', $this->fontHeader);
            $table->addCell(3000)->addText('Restored Time', $this->fontHeader);

            // Data
            foreach ($result as $row) {
                $table->addRow();
                $table->addCell(3000)->addText($row['service_provider'], $this->fontSmall);
                $table->addCell(2000)->addText($row['barangay'], $this->fontSmall);
                $interruptionTime = $row['interruption_datetime'] ? date('M d, Y g:i A', strtotime($row['interruption_datetime'])) : 'N/A';
                $restoredTime = $row['restored_datetime'] ? date('M d, Y g:i A', strtotime($row['restored_datetime'])) : 'Ongoing';
                $table->addCell(3000)->addText($interruptionTime, $this->fontSmall);
                $table->addCell(3000)->addText($restoredTime, $this->fontSmall);
            }
        } else {
            $this->section->addText('No electricity status data available.', $this->fontNormal);
        }
    }

    /**
     * Display Communication Status from annex11
     */
    private function displayCommunicationStatus() {
        $sql = "SELECT * FROM annex11_communication_lines WHERE event_id = ? OR event_id IS NULL ORDER BY barangay";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            $table = $this->section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

            // Header
            $table->addRow(400);
            $table->addCell(3000)->addText('Telecom Provider', $this->fontHeader);
            $table->addCell(2000)->addText('Barangay', $this->fontHeader);
            $table->addCell(3000)->addText('Interruption Date', $this->fontHeader);
            $table->addCell(3000)->addText('Restored Date', $this->fontHeader);
            $table->addCell(2000)->addText('Remarks', $this->fontHeader);

            // Data
            foreach ($result as $row) {
                $table->addRow();
                $table->addCell(3000)->addText($row['telecom_provider'], $this->fontSmall);
                $table->addCell(2000)->addText($row['barangay'], $this->fontSmall);
                $interruptionDate = $row['interruption_date'] ? date('M d, Y g:i A', strtotime($row['interruption_date'])) : 'N/A';
                $restoredDate = $row['restored_date'] ? date('M d, Y g:i A', strtotime($row['restored_date'])) : 'Ongoing';
                $table->addCell(3000)->addText($interruptionDate, $this->fontSmall);
                $table->addCell(3000)->addText($restoredDate, $this->fontSmall);
                $table->addCell(2000)->addText($row['remarks'] ?? '', $this->fontSmall);
            }
        } else {
            $this->section->addText('No communication status data available.', $this->fontNormal);
        }
    }

    /**
     * SECTION II: PRE-EMPTIVE EVACUATION
     */
    private function addSectionII_PreemptiveEvacuation() {
        $this->addSectionTitle('II. PRE-EMPTIVE EVACUATION');

        // Get evacuation data from annex2 (EXISTING DATA)
        $sql = "SELECT * FROM annex2_affected_population WHERE event_id = ? OR event_id IS NULL ORDER BY barangay";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            // Create summary table
            $table = $this->section->addTable([
                'borderSize' => 6,
                'borderColor' => '000000',
                'width' => 100 * 50,
                'unit' => 'pct'
            ]);

            // Header row
            $table->addRow(600);
            $table->addCell(2000)->addText('Barangay', $this->fontHeader);
            $table->addCell(1500)->addText('Families Affected', $this->fontHeader);
            $table->addCell(1500)->addText('Persons Affected', $this->fontHeader);
            $table->addCell(1500)->addText('Inside ECs', $this->fontHeader);
            $table->addCell(1500)->addText('Outside ECs', $this->fontHeader);
            $table->addCell(1500)->addText('No. of ECs', $this->fontHeader);

            $totalFamilies = 0;
            $totalPersons = 0;
            $totalInsideECs = 0;
            $totalOutsideECs = 0;
            $totalNumECs = 0;

            // Data rows
            foreach ($result as $row) {
                $table->addRow();
                $table->addCell(2000)->addText($row['barangay'], $this->fontSmall);
                $table->addCell(1500)->addText($row['affected_families_cumulative'] ?? '0', $this->fontSmall);
                $table->addCell(1500)->addText($row['affected_persons_cumulative'] ?? '0', $this->fontSmall);
                $table->addCell(1500)->addText($row['inside_families'] ?? '0', $this->fontSmall);
                $table->addCell(1500)->addText($row['outside_families'] ?? '0', $this->fontSmall);
                $table->addCell(1500)->addText($row['num_ecs_cumulative'] ?? '0', $this->fontSmall);

                $totalFamilies += $row['affected_families_cumulative'] ?? 0;
                $totalPersons += $row['affected_persons_cumulative'] ?? 0;
                $totalInsideECs += $row['inside_families'] ?? 0;
                $totalOutsideECs += $row['outside_families'] ?? 0;
                $totalNumECs += $row['num_ecs_cumulative'] ?? 0;
            }

            // Total row
            $table->addRow(400);
            $table->addCell(2000)->addText('TOTAL', $this->fontHeader);
            $table->addCell(1500)->addText((string)$totalFamilies, $this->fontHeader);
            $table->addCell(1500)->addText((string)$totalPersons, $this->fontHeader);
            $table->addCell(1500)->addText((string)$totalInsideECs, $this->fontHeader);
            $table->addCell(1500)->addText((string)$totalOutsideECs, $this->fontHeader);
            $table->addCell(1500)->addText((string)$totalNumECs, $this->fontHeader);

        } else {
            $this->section->addText('No evacuation data available.', $this->fontNormal);
        }

        $this->section->addPageBreak();
    }

    /**
     * SECTION III: STATE OF CALAMITY DECLARATION
     */
    private function addSectionIII_CalamityDeclaration() {
        $this->addSectionTitle('III. DECLARATION UNDER STATE OF CALAMITY');

        $sql = "SELECT * FROM calamity_declarations WHERE event_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            $decl = $result[0];
            $this->section->addText(
                'The Municipality of ' . ($decl['city'] ?? 'Baggao') . ', Province of ' . ($decl['province'] ?? 'Cagayan') .
                ' was declared under State of Calamity through ' . ($decl['resolution_number'] ?? '[Resolution Number]') .
                ' dated ' . date('F d, Y', strtotime($decl['declaration_date'])) . '.',
                $this->fontNormal
            );
        } else {
            $this->section->addText('No calamity declaration data available. Please add data to the calamity_declarations table.',
                ['name' => 'Arial', 'size' => 11, 'italic' => true, 'color' => 'FF0000']
            );
        }

        $this->section->addPageBreak();
    }

    /**
     * SECTION IV: RESPONSE ASSETS DEPLOYMENT
     */
    private function addSectionIV_ResponseAssets() {
        $this->addSectionTitle('IV. PRE-POSITIONING/DEPLOYMENT OF RESPONSE ASSETS');

        $sql = "SELECT * FROM response_teams WHERE event_id = ? ORDER BY team_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            // Create table
            $table = $this->section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

            // Header
            $table->addRow(400);
            $table->addCell(2500)->addText('Team/Unit', $this->fontHeader);
            $table->addCell(2000)->addText('Team Leader', $this->fontHeader);
            $table->addCell(1500)->addText('Personnel', $this->fontHeader);
            $table->addCell(2500)->addText('Assets', $this->fontHeader);
            $table->addCell(2500)->addText('Area of Deployment', $this->fontHeader);

            // Data
            foreach ($result as $row) {
                $table->addRow();
                $table->addCell(2500)->addText($row['team_name'], $this->fontSmall);
                $table->addCell(2000)->addText($row['team_leader'] ?? '', $this->fontSmall);
                $table->addCell(1500)->addText($row['personnel_deployed'] ?? '0', $this->fontSmall);
                $table->addCell(2500)->addText($row['response_assets'] ?? '', $this->fontSmall);
                $table->addCell(2500)->addText($row['area_of_deployment'] ?? '', $this->fontSmall);
            }
        } else {
            $this->section->addText('No response asset deployment data available. Please add data to the response_teams table.',
                ['name' => 'Arial', 'size' => 11, 'italic' => true, 'color' => 'FF0000']
            );
        }

        $this->section->addPageBreak();
    }

    /**
     * SECTION V: EFFECTS (Most comprehensive section)
     */
    private function addSectionV_Effects() {
        $this->addSectionTitle('V. EFFECTS');

        // V.A - Incident Monitored (annex1 - EXISTING)
        $this->addSubsectionTitle('V.A INCIDENT MONITORED');
        $this->displayIncidentMonitored();
        $this->section->addTextBreak();

        // V.B - Affected Population (annex2 - EXISTING)
        $this->addSubsectionTitle('V.B AFFECTED POPULATION (FLOODED)');
        $this->section->addText('[Same data as Section II - Evacuation]', $this->fontNormal);
        $this->section->addTextBreak();

        // V.B.1 - Casualties (annex3 - EXISTING)
        $this->addSubsectionTitle('V.B.1 CASUALTIES');
        $this->displayCasualties();
        $this->section->addTextBreak();

        // V.D - Damaged Houses (annex4 - EXISTING)
        $this->addSubsectionTitle('V.D DAMAGED HOUSES');
        $this->displayDamagedHouses();
        $this->section->addTextBreak();

        // V.E - Suspension of Classes and Work (annex14, annex15 - EXISTING)
        $this->addSubsectionTitle('V.E SUSPENSION OF CLASSES AND WORK');
        $this->displaySuspensions();
        $this->section->addTextBreak();

        // V.F - Cost of Damages
        $this->addSubsectionTitle('V.F COST OF DAMAGES');
        $this->displayCostOfDamages();

        $this->section->addPageBreak();
    }

    /**
     * Display Incident Monitored from annex1
     */
    private function displayIncidentMonitored() {
        $sql = "SELECT * FROM annex1_related_incidents WHERE event_id = ? OR event_id IS NULL ORDER BY occurrence_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            $count = 1;
            foreach ($result as $row) {
                $this->section->addText(
                    $count . '. ' . $row['incident_type'] . ' - ' . $row['description'],
                    $this->fontNormal
                );
                if (!empty($row['actions_taken'])) {
                    $this->section->addText(
                        '   Actions: ' . $row['actions_taken'],
                        $this->fontSmall
                    );
                }
                $count++;
            }
        } else {
            $this->section->addText('No incidents monitored.', $this->fontNormal);
        }
    }

    /**
     * Display Casualties from annex3
     */
    private function displayCasualties() {
        // Dead
        $this->section->addText('DEAD:', $this->fontHeader);
        $this->displayCasualtyCategory('Dead');
        $this->section->addTextBreak();

        // Injured
        $this->section->addText('INJURED:', $this->fontHeader);
        $this->displayCasualtyCategory('Injured');
        $this->section->addTextBreak();

        // Missing
        $this->section->addText('MISSING:', $this->fontHeader);
        $this->displayCasualtyCategory('Missing');
    }

    /**
     * Display casualties by category
     */
    private function displayCasualtyCategory($category) {
        $sql = "SELECT * FROM annex3_casualties WHERE category = ? AND (event_id = ? OR event_id IS NULL) ORDER BY barangay";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$category, $this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            $table = $this->section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

            // Header
            $table->addRow(400);
            $table->addCell(3000)->addText('Name', $this->fontHeader);
            $table->addCell(1000)->addText('Age', $this->fontHeader);
            $table->addCell(1000)->addText('Sex', $this->fontHeader);
            $table->addCell(2500)->addText('Address', $this->fontHeader);
            $table->addCell(2500)->addText('Cause', $this->fontHeader);

            // Data
            foreach ($result as $row) {
                $fullName = trim(($row['surname'] ?? '') . ', ' . ($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? ''));
                $table->addRow();
                $table->addCell(3000)->addText($fullName, $this->fontSmall);
                $table->addCell(1000)->addText($row['age'] ?? '', $this->fontSmall);
                $table->addCell(1000)->addText($row['sex'] ?? '', $this->fontSmall);
                $table->addCell(2500)->addText($row['address'] ?? '', $this->fontSmall);
                $table->addCell(2500)->addText($row['cause'] ?? '', $this->fontSmall);
            }
        } else {
            $this->section->addText('No ' . strtolower($category) . ' casualties reported.', $this->fontNormal);
        }
    }

    /**
     * Display Damaged Houses from annex4
     */
    private function displayDamagedHouses() {
        $sql = "SELECT * FROM annex4_damaged_houses WHERE event_id = ? OR event_id IS NULL ORDER BY barangay";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            $table = $this->section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

            // Header
            $table->addRow(400);
            $table->addCell(3000)->addText('Barangay', $this->fontHeader);
            $table->addCell(2000)->addText('Partially Damaged', $this->fontHeader);
            $table->addCell(2000)->addText('Totally Damaged', $this->fontHeader);
            $table->addCell(2000)->addText('Total', $this->fontHeader);

            $totalPartial = 0;
            $totalTotally = 0;
            $grandTotal = 0;

            // Data
            foreach ($result as $row) {
                $table->addRow();
                $table->addCell(3000)->addText($row['barangay'], $this->fontSmall);
                $table->addCell(2000)->addText($row['partially_damaged'] ?? '0', $this->fontSmall);
                $table->addCell(2000)->addText($row['totally_damaged'] ?? '0', $this->fontSmall);
                $table->addCell(2000)->addText($row['total_damaged'] ?? '0', $this->fontSmall);

                $totalPartial += $row['partially_damaged'] ?? 0;
                $totalTotally += $row['totally_damaged'] ?? 0;
                $grandTotal += $row['total_damaged'] ?? 0;
            }

            // Total row
            $table->addRow();
            $table->addCell(3000)->addText('TOTAL', $this->fontHeader);
            $table->addCell(2000)->addText((string)$totalPartial, $this->fontHeader);
            $table->addCell(2000)->addText((string)$totalTotally, $this->fontHeader);
            $table->addCell(2000)->addText((string)$grandTotal, $this->fontHeader);
        } else {
            $this->section->addText('No damaged houses data available.', $this->fontNormal);
        }
    }

    /**
     * Display Class and Work Suspensions
     */
    private function displaySuspensions() {
        // Class Suspension
        $this->section->addText('E.1 SUSPENSION OF CLASSES', $this->fontHeader);
        $sql = "SELECT * FROM annex15_suspension_classes WHERE event_id = ? OR event_id IS NULL ORDER BY suspension_date";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            foreach ($result as $row) {
                $text = 'Classes at ' . ($row['level'] ?? 'all levels') . ' suspended on ' .
                        date('F d, Y', strtotime($row['suspension_date']));
                if ($row['resumption_date']) {
                    $text .= ', resumed ' . date('F d, Y', strtotime($row['resumption_date']));
                }
                $this->section->addText($text, $this->fontNormal);
            }
        } else {
            $this->section->addText('No class suspension data available.', $this->fontNormal);
        }

        $this->section->addTextBreak();

        // Work Suspension
        $this->section->addText('E.2 SUSPENSION OF WORK', $this->fontHeader);
        $sql = "SELECT * FROM annex14_suspension_work WHERE event_id = ? OR event_id IS NULL ORDER BY suspension_date";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            foreach ($result as $row) {
                $text = 'Work in ' . ($row['type'] ?? 'all sectors') . ' suspended on ' .
                        date('F d, Y', strtotime($row['suspension_date']));
                if ($row['resumption_date']) {
                    $text .= ', resumed ' . date('F d, Y', strtotime($row['resumption_date']));
                }
                $this->section->addText($text, $this->fontNormal);
            }
        } else {
            $this->section->addText('No work suspension data available.', $this->fontNormal);
        }
    }

    /**
     * Display Cost of Damages (Agriculture & Infrastructure)
     */
    private function displayCostOfDamages() {
        // Agriculture
        $this->section->addText('F.a AGRICULTURE', $this->fontHeader);
        $sql = "SELECT SUM(damage_value) as total FROM annex5_agriculture_damage WHERE event_id = ? OR event_id IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();
        $row = $result[0];
        $agriTotal = $row['total'] ?? 0;
        $this->section->addText('Total Agricultural Damage: PHP ' . number_format($agriTotal, 2), $this->fontNormal);
        $this->section->addTextBreak();

        // Infrastructure
        $this->section->addText('F.e INFRASTRUCTURE (Roads, Bridges, Buildings)', $this->fontHeader);
        $sql = "SELECT SUM(cost) as total FROM annex6_infrastructure_damage WHERE event_id = ? OR event_id IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();
        $row = $result[0];
        $infraTotal = $row['total'] ?? 0;
        $this->section->addText('Total Infrastructure Damage: PHP ' . number_format($infraTotal, 2), $this->fontNormal);
        $this->section->addTextBreak();

        // Grand Total
        $grandTotal = $agriTotal + $infraTotal;
        $this->section->addText('GRAND TOTAL DAMAGES: PHP ' . number_format($grandTotal, 2), $this->fontHeader);
    }

    /**
     * SECTION VI: RESPONSE OPERATIONS
     */
    private function addSectionVI_ResponseOperations() {
        $this->addSectionTitle('VI. RESPONSE OPERATIONS');

        $sql = "SELECT * FROM response_operations WHERE event_id = ? ORDER BY operation_start";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            $count = 1;
            foreach ($result as $row) {
                $this->section->addText($count . '. ' . $row['team_unit'], $this->fontHeader);
                $this->section->addText('Incident: ' . $row['incident_responded'], $this->fontNormal);
                $this->section->addText(
                    'Time: ' . date('F d, Y g:i A', strtotime($row['operation_start'])) .
                    ($row['operation_end'] ? ' - ' . date('g:i A', strtotime($row['operation_end'])) : ''),
                    $this->fontSmall
                );
                if (!empty($row['actions_taken'])) {
                    $this->section->addText('Actions Taken:', $this->fontSmall);
                    $this->section->addText($row['actions_taken'], $this->fontSmall);
                }
                $this->section->addTextBreak();
                $count++;
            }
        } else {
            $this->section->addText('No response operations data available. Please add data to the response_operations table.',
                ['name' => 'Arial', 'size' => 11, 'italic' => true, 'color' => 'FF0000']
            );
        }

        $this->section->addPageBreak();
    }

    /**
     * SECTION VII: ASSISTANCE EXTENDED
     */
    private function addSectionVII_AssistanceExtended() {
        $this->addSectionTitle('VII. ASSISTANCE EXTENDED');

        // Assistance to Families (annex20 - EXISTING)
        $sql = "SELECT * FROM annex20_assistance_provided WHERE event_id = ? OR event_id IS NULL ORDER BY cluster, type";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            $table = $this->section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

            // Header
            $table->addRow(400);
            $table->addCell(2000)->addText('Cluster', $this->fontHeader);
            $table->addCell(2500)->addText('Type', $this->fontHeader);
            $table->addCell(1500)->addText('Quantity', $this->fontHeader);
            $table->addCell(1000)->addText('Unit', $this->fontHeader);
            $table->addCell(2000)->addText('Amount', $this->fontHeader);
            $table->addCell(2000)->addText('Remarks', $this->fontHeader);

            // Data
            foreach ($result as $row) {
                $table->addRow();
                $table->addCell(2000)->addText($row['cluster'] ?? '', $this->fontSmall);
                $table->addCell(2500)->addText($row['type'] ?? '', $this->fontSmall);
                $table->addCell(1500)->addText($row['quantity'] ?? '0', $this->fontSmall);
                $table->addCell(1000)->addText($row['unit'] ?? '', $this->fontSmall);
                $table->addCell(2000)->addText('PHP ' . number_format($row['amount'] ?? 0, 2), $this->fontSmall);
                $table->addCell(2000)->addText($row['remarks'] ?? '', $this->fontSmall);
            }
        } else {
            $this->section->addText('No assistance data available.', $this->fontNormal);
        }

        $this->section->addPageBreak();
    }

    /**
     * SECTION VIII: PREPAREDNESS MEASURES
     */
    private function addSectionVIII_PreparednessMeasures() {
        $this->addSectionTitle('VIII. PREPAREDNESS MEASURES/ACTIONS TAKEN');

        $sql = "SELECT * FROM preparedness_actions WHERE event_id = ? ORDER BY action_datetime";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            $count = 1;
            foreach ($result as $row) {
                $this->section->addText(
                    $count . '. ' . date('F d, Y', strtotime($row['action_datetime'])) . ' - ' .
                    $row['action_description'] . ' (' . ($row['responsible_unit'] ?? 'MDRRMO') . ')',
                    $this->fontNormal
                );
                $count++;
            }
        } else {
            $this->section->addText('No preparedness actions data available. Please add data to the preparedness_actions table.',
                ['name' => 'Arial', 'size' => 11, 'italic' => true, 'color' => 'FF0000']
            );
        }

        $this->section->addTextBreak(2);

        // Signature Block
        $this->addSignatureBlock();
    }

    /**
     * Add signature block
     */
    private function addSignatureBlock() {
        $this->section->addTextBreak(2);

        // Prepared by
        $this->section->addText('Prepared by:', $this->fontNormal);
        $this->section->addTextBreak(2);

        // Get user who created the report (you may want to customize this)
        $this->section->addText(
            '__________________________________________',
            ['name' => 'Arial', 'size' => 11, 'bold' => true]
        );
        $this->section->addText(
            '[LDRRMO Name]',
            ['name' => 'Arial', 'size' => 10]
        );
        $this->section->addText(
            'LDRRMO III',
            ['name' => 'Arial', 'size' => 10]
        );

        $this->section->addTextBreak(2);

        // Approved by
        $this->section->addText('Approved by:', $this->fontNormal);
        $this->section->addTextBreak(2);

        $this->section->addText(
            '__________________________________________',
            ['name' => 'Arial', 'size' => 11, 'bold' => true]
        );
        $this->section->addText(
            '[Municipal Mayor Name]',
            ['name' => 'Arial', 'size' => 10]
        );
        $this->section->addText(
            'Municipal Mayor/Chairperson, MDRRMC',
            ['name' => 'Arial', 'size' => 10]
        );
    }

    /**
     * Helper: Add section title
     */
    private function addSectionTitle($title) {
        $this->section->addText(
            $title,
            ['name' => 'Arial', 'size' => 14, 'bold' => true],
            ['spaceAfter' => 240]
        );
    }

    /**
     * Helper: Add subsection title
     */
    private function addSubsectionTitle($title, $size = 12) {
        $this->section->addText(
            $title,
            ['name' => 'Arial', 'size' => $size, 'bold' => true],
            ['spaceAfter' => 120]
        );
    }

    /**
     * Helper: Get weather data
     */
    private function getWeatherData() {
        $sql = "SELECT * FROM weather_data WHERE event_id = ? ORDER BY forecast_datetime";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        return $stmt->fetchAll();
    }

    /**
     * Helper: Get water level data
     */
    private function getWaterLevelData() {
        $sql = "SELECT * FROM water_level_monitoring WHERE event_id = ? ORDER BY reading_datetime DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->eventId]);
        return $stmt->fetchAll();
    }

    /**
     * Save document to file
     */
    public function save($filename = null) {
        if (!$filename) {
            $eventName = $this->eventData['event_name'] ?? 'Terminal_Report';
            $filename = 'Terminal_Report_' . str_replace(' ', '_', $eventName) . '_' . date('Ymd') . '.docx';
        }

        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $objWriter->save($filename);

        return $filename;
    }

    /**
     * Download document
     */
    public function download($filename = null) {
        if (!$filename) {
            $eventName = $this->eventData['event_name'] ?? 'Terminal_Report';
            $filename = 'Terminal_Report_' . str_replace(' ', '_', $eventName) . '_' . date('Ymd') . '.docx';
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $objWriter->save('php://output');
        exit;
    }
}

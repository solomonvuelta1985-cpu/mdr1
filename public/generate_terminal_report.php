<?php
/**
 * Terminal Report Generator - Test/Generate Page
 * Use this page to generate Terminal Reports
 */

require_once __DIR__ . '/../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user has admin or special_access role
if (!in_array($_SESSION['user_role'], ['admin', 'special_access'])) {
    $_SESSION['error_message'] = "Access Denied. You do not have permission to access Terminal Report Generator.";
    header("Location: dashboard.php");
    exit();
}

$pageTitle = "Generate Terminal Report";

// Start output buffering
ob_start();
?>

<div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-file-word"></i> Terminal Report Generator
                        </h4>
                    </div>
                    <div class="card-body">

                        <?php if (isset($_SESSION['error_message'])): ?>
                        <!-- Error Message -->
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error:</strong> <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); endif; ?>

                        <?php if (isset($_SESSION['success_message'])): ?>
                        <!-- Success Message -->
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); endif; ?>

                        <!-- Instructions -->
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> How to Use</h5>
                            <ol>
                                <li>Select a disaster event from the dropdown</li>
                                <li>Click "Generate Report"</li>
                                <li>Document will download automatically</li>
                            </ol>
                            <p class="mb-0"><strong>Note:</strong> The report uses existing data from all Annex forms. Sections with missing data will show placeholder text.</p>
                        </div>

                        <?php if (!file_exists('../vendor/autoload.php')): ?>
                        <!-- PHPWord Not Installed Warning -->
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> PHPWord Library Not Installed</h5>
                            <p>Before you can generate reports, you need to install PHPWord library.</p>
                            <h6>Installation Steps:</h6>
                            <ol>
                                <li>Download Composer: <a href="https://getcomposer.org/download/" target="_blank">https://getcomposer.org/download/</a></li>
                                <li>Install Composer (use default settings)</li>
                                <li>Open Command Prompt in: <code>c:\xampp\htdocs\mdr1</code></li>
                                <li>Run: <code>composer install</code></li>
                                <li>Refresh this page</li>
                            </ol>
                            <p class="mb-0">See: <a href="../INSTALL_PHPWORD.md" target="_blank">INSTALL_PHPWORD.md</a> for detailed instructions</p>
                        </div>
                        <?php else: ?>

                        <!-- Generate Form -->
                        <form method="POST" action="process_terminal_report.php" id="generateForm">

                            <!-- Event Selection -->
                            <div class="mb-3">
                                <label for="event_id" class="form-label">Select Disaster Event *</label>
                                <select name="event_id" id="event_id" class="form-select" required>
                                    <option value="">-- Select Event --</option>
                                    <?php
                                    // Get all disaster events
                                    $sql = "SELECT * FROM disaster_events ORDER BY start_date DESC";
                                    $stmt = $pdo->query($sql);
                                    $events = $stmt->fetchAll();

                                    if ($events && count($events) > 0) {
                                        foreach ($events as $event) {
                                            $eventLabel = $event['event_name'] . ' (' .
                                                         date('M d, Y', strtotime($event['start_date'])) . ')';
                                            echo '<option value="' . $event['id'] . '">' .
                                                 htmlspecialchars($eventLabel) . '</option>';
                                        }
                                    } else {
                                        echo '<option value="" disabled>No disaster events found</option>';
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted">
                                    If no events are listed, you need to run the database migration first.
                                    See: <a href="../TERMINAL_REPORT_SETUP_GUIDE.md">Setup Guide</a>
                                </small>
                            </div>

                            <!-- Output Format -->
                            <div class="mb-3">
                                <label class="form-label">Output Format</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="format_docx" value="docx" checked>
                                    <label class="form-check-label" for="format_docx">
                                        <i class="fas fa-file-word text-primary"></i> Microsoft Word (.docx)
                                    </label>
                                </div>
                            </div>

                            <!-- Include Options -->
                            <div class="mb-3">
                                <label class="form-label">Include in Report</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_empty_sections" id="include_empty_sections" checked>
                                    <label class="form-check-label" for="include_empty_sections">
                                        Show sections with no data (with placeholder text)
                                    </label>
                                </div>
                            </div>

                            <!-- Generate Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="generateBtn">
                                    <i class="fas fa-file-download"></i> Generate Terminal Report
                                </button>
                            </div>

                        </form>

                        <!-- Data Completeness Status -->
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">Data Completeness Status</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-success"><i class="fas fa-check-circle"></i> Available Data (Existing Tables)</h6>
                                        <ul class="small">
                                            <li>✅ Incident Reports (Annex 1)</li>
                                            <li>✅ Affected Population (Annex 2)</li>
                                            <li>✅ Casualties (Annex 3)</li>
                                            <li>✅ Damaged Houses (Annex 4)</li>
                                            <li>✅ Agriculture Damage (Annex 5)</li>
                                            <li>✅ Infrastructure Damage (Annex 6)</li>
                                            <li>✅ Roads & Bridges (Annex 8)</li>
                                            <li>✅ Power Supply (Annex 9)</li>
                                            <li>✅ Communications (Annex 11)</li>
                                            <li>✅ Work Suspension (Annex 14)</li>
                                            <li>✅ Class Suspension (Annex 15)</li>
                                            <li>✅ Assistance Provided (Annex 20, 21)</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-warning"><i class="fas fa-exclamation-triangle"></i> Needs Data Entry (New Tables)</h6>
                                        <ul class="small">
                                            <li>⚠️ Weather Forecast (weather_data)</li>
                                            <li>⚠️ Water Level (water_level_monitoring)</li>
                                            <li>⚠️ Water Supply (water_supply_status)</li>
                                            <li>⚠️ Calamity Declaration (calamity_declarations)</li>
                                            <li>⚠️ Response Teams (response_teams)</li>
                                            <li>⚠️ Response Operations (response_operations)</li>
                                            <li>⚠️ Preparedness Actions (preparedness_actions)</li>
                                            <li>⚠️ Evacuation Centers (evacuation_centers)</li>
                                        </ul>
                                        <p class="small text-muted mb-0">
                                            <strong>Note:</strong> Report will show these sections with placeholder text until data is entered.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Form submission handling
document.getElementById('generateForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';
});
</script>

<?php
// Get the captured content and store it in a variable
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content
require_once '../includes/sidenav.php';
?>

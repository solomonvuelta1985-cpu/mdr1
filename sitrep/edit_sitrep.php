<?php
/**
 * MDRRMO Situational Report - Edit Settings
 * ADMIN ONLY - Edit SITREP settings
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require login
require_login();

// Get current settings
$settings = [
    'report_number' => 3,
    'weather_system' => '',
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        set_flash('Security token validation failed', 'error');
        header('Location: edit_sitrep.php');
        exit;
    }

    $report_number = sanitize($_POST['report_number']);
    $weather_system = sanitize($_POST['weather_system']);
    $mayor_status = sanitize($_POST['mayor_status']);
    $vm_status = sanitize($_POST['vm_status']);
    $weather_clouds = sanitize($_POST['weather_clouds']);
    $weather_rains = sanitize($_POST['weather_rains']);
    $weather_winds = sanitize($_POST['weather_winds']);

    // Create table if not exists
    try {
        db_query("CREATE TABLE IF NOT EXISTS sitrep_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_number INT NOT NULL,
            weather_system VARCHAR(200),
            mayor_status VARCHAR(50),
            vm_status VARCHAR(50),
            weather_clouds VARCHAR(100),
            weather_rains VARCHAR(100),
            weather_winds VARCHAR(100),
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by INT
        )");
    } catch (Exception $e) {}

    // Insert new settings
    try {
        $stmt = db_query("INSERT INTO sitrep_settings
            (report_number, weather_system, mayor_status, vm_status, weather_clouds, weather_rains, weather_winds, updated_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$report_number, $weather_system, $mayor_status, $vm_status, $weather_clouds, $weather_rains, $weather_winds, $_SESSION['user_id']]
        );

        set_flash('SITREP settings updated successfully', 'success');
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        set_flash('Failed to update settings: ' . $e->getMessage(), 'error');
    }
}

$csrf_token = generate_token();
ob_start();
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    .settings-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .section-title {
        font-weight: bold;
        margin: 25px 0 15px 0;
        padding: 10px 15px;
        background: #e9ecef;
        border-left: 4px solid #0d6efd;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-label {
        font-weight: 600;
        margin-bottom: 5px;
    }
    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 12px;
    }
</style>

<div class="container-fluid mt-4">
    <div class="settings-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit SITREP Settings</h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to SITREP
            </a>
        </div>

        <?php show_flash(); ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <!-- Report Information -->
            <div class="section-title">Report Information</div>

            <div class="form-group">
                <label class="form-label">Report Number</label>
                <input type="number" name="report_number" class="form-control"
                       value="<?php echo htmlspecialchars($settings['report_number']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Weather System (Optional)</label>
                <input type="text" name="weather_system" class="form-control"
                       value="<?php echo htmlspecialchars($settings['weather_system']); ?>"
                       placeholder="e.g., Tropical Depression, Typhoon Name">
            </div>

            <!-- Status -->
            <div class="section-title">Officials Status</div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Mayor</label>
                        <select name="mayor_status" class="form-select">
                            <option value="In" <?php echo $settings['mayor_status'] === 'In' ? 'selected' : ''; ?>>In</option>
                            <option value="Out" <?php echo $settings['mayor_status'] === 'Out' ? 'selected' : ''; ?>>Out</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Vice Mayor</label>
                        <select name="vm_status" class="form-select">
                            <option value="In" <?php echo $settings['vm_status'] === 'In' ? 'selected' : ''; ?>>In</option>
                            <option value="Out" <?php echo $settings['vm_status'] === 'Out' ? 'selected' : ''; ?>>Out</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Weather Condition -->
            <div class="section-title">Weather Condition</div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Clouds</label>
                        <input type="text" name="weather_clouds" class="form-control"
                               value="<?php echo htmlspecialchars($settings['weather_clouds']); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Rains</label>
                        <input type="text" name="weather_rains" class="form-control"
                               value="<?php echo htmlspecialchars($settings['weather_rains']); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Winds</label>
                        <input type="text" name="weather_winds" class="form-control"
                               value="<?php echo htmlspecialchars($settings['weather_winds']); ?>">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i> Save Settings
                </button>
                <a href="index.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times me-2"></i> Cancel
                </a>
            </div>
        </form>

        <!-- Info Box -->
        <div class="alert alert-info mt-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> The data counts (incidents, casualties, etc.) are automatically pulled from the database.
            This form only edits the report header information and weather conditions.
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once '../includes/sidenav.php';
?>

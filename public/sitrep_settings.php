<?php
// sitrep_settings.php - Manage SITREP Metadata
session_start();

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_number = $_POST['report_number'];
    $weather_system = $_POST['weather_system'];
    $weather_clouds = $_POST['weather_clouds'];
    $weather_rains = $_POST['weather_rains'];
    $weather_winds = $_POST['weather_winds'];
    $mayor_status = $_POST['mayor_status'];
    $vice_mayor_status = $_POST['vice_mayor_status'];
    $update_interval_hours = $_POST['update_interval_hours'];

    // Deactivate all previous records
    $stmt = $pdo->prepare("UPDATE sitrep_metadata SET is_active = 0");
    $stmt->execute();

    // Insert new metadata
    $stmt = $pdo->prepare("INSERT INTO sitrep_metadata
        (report_number, weather_system, weather_clouds, weather_rains, weather_winds, mayor_status, vice_mayor_status, report_date, update_interval_hours, is_active, created_by)
        VALUES (:report_number, :weather_system, :weather_clouds, :weather_rains, :weather_winds, :mayor_status, :vice_mayor_status, NOW(), :update_interval_hours, 1, 1)");

    $stmt->execute([
        ':report_number' => $report_number,
        ':weather_system' => $weather_system,
        ':weather_clouds' => $weather_clouds,
        ':weather_rains' => $weather_rains,
        ':weather_winds' => $weather_winds,
        ':mayor_status' => $mayor_status,
        ':vice_mayor_status' => $vice_mayor_status,
        ':update_interval_hours' => $update_interval_hours
    ]);

    $successMessage = "SITREP metadata updated successfully!";
}

// Get current metadata
$stmt = $pdo->prepare("SELECT * FROM sitrep_metadata WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$currentMetadata = $stmt->fetch(PDO::FETCH_ASSOC);

// Default values if no metadata exists
if (!$currentMetadata) {
    $currentMetadata = [
        'report_number' => 1,
        'weather_system' => '',
        'weather_clouds' => 'CLEAR SKY',
        'weather_rains' => 'NONE',
        'weather_winds' => 'NORMAL',
        'mayor_status' => 'In',
        'vice_mayor_status' => 'In',
        'update_interval_hours' => 1
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SITREP Settings - MDRRMO LGU-BAGGAO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #ecf0f1;
        }
        .form-section:last-child {
            border-bottom: none;
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .btn-primary {
            background: #3498db;
            border: none;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <div class="header">
            <h1><i class="fas fa-cog"></i> SITREP Settings</h1>
            <p style="margin: 0;">Manage Situational Report Metadata</p>
        </div>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Report Information -->
            <div class="form-section">
                <div class="section-title"><i class="fas fa-file-alt"></i> Report Information</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="report_number" class="form-label">Report Number</label>
                        <input type="number" class="form-control" id="report_number" name="report_number"
                               value="<?php echo htmlspecialchars($currentMetadata['report_number']); ?>" required min="1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="update_interval_hours" class="form-label">Update Interval (hours)</label>
                        <select class="form-select" id="update_interval_hours" name="update_interval_hours" required>
                            <option value="1" <?php echo $currentMetadata['update_interval_hours'] == 1 ? 'selected' : ''; ?>>Every 1 hour</option>
                            <option value="2" <?php echo $currentMetadata['update_interval_hours'] == 2 ? 'selected' : ''; ?>>Every 2 hours</option>
                            <option value="3" <?php echo $currentMetadata['update_interval_hours'] == 3 ? 'selected' : ''; ?>>Every 3 hours</option>
                            <option value="6" <?php echo $currentMetadata['update_interval_hours'] == 6 ? 'selected' : ''; ?>>Every 6 hours</option>
                            <option value="12" <?php echo $currentMetadata['update_interval_hours'] == 12 ? 'selected' : ''; ?>>Every 12 hours</option>
                            <option value="24" <?php echo $currentMetadata['update_interval_hours'] == 24 ? 'selected' : ''; ?>>Every 24 hours</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Officials Status -->
            <div class="form-section">
                <div class="section-title"><i class="fas fa-user-tie"></i> Officials Status</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mayor_status" class="form-label">Mayor Status</label>
                        <select class="form-select" id="mayor_status" name="mayor_status" required>
                            <option value="In" <?php echo $currentMetadata['mayor_status'] == 'In' ? 'selected' : ''; ?>>In</option>
                            <option value="Out" <?php echo $currentMetadata['mayor_status'] == 'Out' ? 'selected' : ''; ?>>Out</option>
                            <option value="On Leave" <?php echo $currentMetadata['mayor_status'] == 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="vice_mayor_status" class="form-label">Vice Mayor Status</label>
                        <select class="form-select" id="vice_mayor_status" name="vice_mayor_status" required>
                            <option value="In" <?php echo $currentMetadata['vice_mayor_status'] == 'In' ? 'selected' : ''; ?>>In</option>
                            <option value="Out" <?php echo $currentMetadata['vice_mayor_status'] == 'Out' ? 'selected' : ''; ?>>Out</option>
                            <option value="On Leave" <?php echo $currentMetadata['vice_mayor_status'] == 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Weather Information -->
            <div class="form-section">
                <div class="section-title"><i class="fas fa-cloud-sun"></i> Weather Information</div>

                <div class="mb-3">
                    <button type="button" class="btn btn-info btn-sm" onclick="fetchWeatherData()">
                        <i class="fas fa-cloud-download-alt"></i> Auto-Fetch Weather Data
                    </button>
                    <span id="fetch-status" style="margin-left: 10px;"></span>
                </div>

                <div class="mb-3">
                    <label for="weather_system" class="form-label">Weather System (Optional)</label>
                    <input type="text" class="form-control" id="weather_system" name="weather_system"
                           value="<?php echo htmlspecialchars($currentMetadata['weather_system'] ?? ''); ?>"
                           placeholder='e.g., Tropical Storm "NANDO"'>
                    <div class="form-text">Leave blank if no specific weather system, or click "Auto-Fetch" to get current data</div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="weather_clouds" class="form-label">Clouds</label>
                        <select class="form-select" id="weather_clouds" name="weather_clouds" required>
                            <option value="CLEAR SKY" <?php echo $currentMetadata['weather_clouds'] == 'CLEAR SKY' ? 'selected' : ''; ?>>CLEAR SKY</option>
                            <option value="PARTLY CLOUDY" <?php echo $currentMetadata['weather_clouds'] == 'PARTLY CLOUDY' ? 'selected' : ''; ?>>PARTLY CLOUDY</option>
                            <option value="CLOUDY" <?php echo $currentMetadata['weather_clouds'] == 'CLOUDY' ? 'selected' : ''; ?>>CLOUDY</option>
                            <option value="OVERCAST" <?php echo $currentMetadata['weather_clouds'] == 'OVERCAST' ? 'selected' : ''; ?>>OVERCAST</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="weather_rains" class="form-label">Rains</label>
                        <select class="form-select" id="weather_rains" name="weather_rains" required>
                            <option value="NONE" <?php echo $currentMetadata['weather_rains'] == 'NONE' ? 'selected' : ''; ?>>NONE</option>
                            <option value="LIGHT" <?php echo $currentMetadata['weather_rains'] == 'LIGHT' ? 'selected' : ''; ?>>LIGHT</option>
                            <option value="MODERATE" <?php echo $currentMetadata['weather_rains'] == 'MODERATE' ? 'selected' : ''; ?>>MODERATE</option>
                            <option value="HEAVY" <?php echo $currentMetadata['weather_rains'] == 'HEAVY' ? 'selected' : ''; ?>>HEAVY</option>
                            <option value="LIGHT TO MODERATE" <?php echo $currentMetadata['weather_rains'] == 'LIGHT TO MODERATE' ? 'selected' : ''; ?>>LIGHT TO MODERATE</option>
                            <option value="MODERATE TO HEAVY" <?php echo $currentMetadata['weather_rains'] == 'MODERATE TO HEAVY' ? 'selected' : ''; ?>>MODERATE TO HEAVY</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="weather_winds" class="form-label">Winds</label>
                        <select class="form-select" id="weather_winds" name="weather_winds" required>
                            <option value="NORMAL" <?php echo $currentMetadata['weather_winds'] == 'NORMAL' ? 'selected' : ''; ?>>NORMAL</option>
                            <option value="LIGHT" <?php echo $currentMetadata['weather_winds'] == 'LIGHT' ? 'selected' : ''; ?>>LIGHT</option>
                            <option value="MODERATE" <?php echo $currentMetadata['weather_winds'] == 'MODERATE' ? 'selected' : ''; ?>>MODERATE</option>
                            <option value="STRONG" <?php echo $currentMetadata['weather_winds'] == 'STRONG' ? 'selected' : ''; ?>>STRONG</option>
                            <option value="LIGHT TO MODERATE" <?php echo $currentMetadata['weather_winds'] == 'LIGHT TO MODERATE' ? 'selected' : ''; ?>>LIGHT TO MODERATE</option>
                            <option value="MODERATE TO STRONG" <?php echo $currentMetadata['weather_winds'] == 'MODERATE TO STRONG' ? 'selected' : ''; ?>>MODERATE TO STRONG</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                <a href="sitrep.php" class="btn btn-secondary" target="_blank">
                    <i class="fas fa-eye"></i> View SITREP
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Settings
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function fetchWeatherData() {
            const statusElement = document.getElementById('fetch-status');
            statusElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching weather data...';

            fetch('fetch_weather_api.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update form fields with fetched data
                        const weatherData = data.data;

                        // Update weather system if available
                        if (weatherData.weather_system) {
                            document.getElementById('weather_system').value = weatherData.weather_system;
                        }

                        // Update clouds
                        document.getElementById('weather_clouds').value = weatherData.weather_clouds;

                        // Update rains
                        document.getElementById('weather_rains').value = weatherData.weather_rains;

                        // Update winds
                        document.getElementById('weather_winds').value = weatherData.weather_winds;

                        statusElement.innerHTML = '<span style="color: green;"><i class="fas fa-check-circle"></i> Weather data updated! Temperature: ' + weatherData.temperature + '°C, Wind: ' + weatherData.wind_speed + ' km/h</span>';

                        // Clear status after 5 seconds
                        setTimeout(() => {
                            statusElement.innerHTML = '';
                        }, 5000);
                    } else {
                        statusElement.innerHTML = '<span style="color: red;"><i class="fas fa-exclamation-circle"></i> Error: ' + data.error + '</span>';
                    }
                })
                .catch(error => {
                    statusElement.innerHTML = '<span style="color: red;"><i class="fas fa-exclamation-circle"></i> Failed to fetch weather data</span>';
                    console.error('Error:', error);
                });
        }
    </script>
</body>
</html>

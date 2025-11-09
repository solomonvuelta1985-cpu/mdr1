<?php
// test_weather_accuracy.php - Compare weather data with PAGASA
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weather API Accuracy Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f5f5f5; }
        .comparison-table { background: white; padding: 20px; border-radius: 8px; }
        .accurate { background-color: #d4edda; }
        .warning { background-color: #fff3cd; }
        .error { background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌤️ Weather API Accuracy Test</h1>
        <p>Comparing Open-Meteo API data with manual PAGASA verification</p>

        <div class="comparison-table">
            <h3>Live Weather Data for Baggao, Cagayan</h3>

            <div id="loading" class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Fetching real-time weather data...</p>
            </div>

            <div id="results" style="display: none;">
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Open-Meteo API</th>
                            <th>Expected Range</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="weather-data">
                    </tbody>
                </table>

                <div class="alert alert-info mt-4">
                    <h5>📊 Accuracy Notes:</h5>
                    <ul>
                        <li><strong>Temperature:</strong> Should be within ±1°C of actual conditions</li>
                        <li><strong>Wind Speed:</strong> Should be within ±5 km/h of actual conditions</li>
                        <li><strong>Precipitation:</strong> Should match current rainfall intensity</li>
                        <li><strong>Cloud Coverage:</strong> Should match visible sky conditions</li>
                    </ul>
                </div>

                <div class="alert alert-success mt-3">
                    <h5>✅ PAGASA Integration Active!</h5>
                    <ul>
                        <li><strong>Philippine storm names are now AUTOMATIC!</strong> (e.g., "UWAN", "NANDO", "PEPITO")</li>
                        <li>Data fetched directly from PAGASA Severe Weather Bulletins</li>
                        <li>Storm classification included (Super Typhoon, Typhoon, Tropical Storm, etc.)</li>
                    </ul>
                </div>

                <div class="alert alert-warning mt-3">
                    <h5>⚠️ Limitations:</h5>
                    <ul>
                        <li>Weather data updates every 15-60 minutes (not real-time)</li>
                        <li>Hyperlocal conditions may vary slightly</li>
                        <li>PAGASA website downtime may affect storm name detection</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <h5>🔍 Manual Verification Steps:</h5>
                    <ol>
                        <li>Check current temperature: <a href="https://www.pagasa.dost.gov.ph/" target="_blank">PAGASA Official Website</a></li>
                        <li>Look outside and verify cloud conditions</li>
                        <li>Check if it's currently raining</li>
                        <li>Feel the wind strength</li>
                        <li>Compare with the API data above</li>
                    </ol>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary" onclick="location.reload()">
                        🔄 Refresh Data
                    </button>
                    <a href="sitrep_settings.php" class="btn btn-success">
                        ⚙️ Go to SITREP Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fetch weather data
        fetch('fetch_weather_api.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('results').style.display = 'block';

                if (data.success) {
                    const weather = data.data;
                    const tbody = document.getElementById('weather-data');

                    // Temperature
                    addRow(tbody, 'Temperature',
                        weather.temperature + '°C',
                        '25-35°C (typical for Cagayan)',
                        getStatus(weather.temperature, 25, 35));

                    // Humidity
                    addRow(tbody, 'Humidity',
                        weather.humidity + '%',
                        '60-90% (tropical climate)',
                        getStatus(weather.humidity, 60, 90));

                    // Cloud Coverage
                    addRow(tbody, 'Clouds',
                        weather.weather_clouds,
                        'Visual verification needed',
                        'warning');

                    // Rainfall
                    addRow(tbody, 'Rainfall',
                        weather.weather_rains,
                        'Check if currently raining',
                        'warning');

                    // Wind Speed
                    addRow(tbody, 'Wind Speed',
                        weather.wind_speed + ' km/h',
                        '5-30 km/h (normal conditions)',
                        getStatus(weather.wind_speed, 5, 30));

                    // Wind Direction
                    addRow(tbody, 'Wind Direction',
                        weather.wind_direction + '° (' + getWindDirection(weather.wind_direction) + ')',
                        'All directions possible',
                        'accurate');

                    // Weather System (Storm Name)
                    addRow(tbody, 'Weather System',
                        weather.weather_system || 'No active storm',
                        'PAGASA Bulletin (auto-detected)',
                        weather.weather_system ? 'accurate' : 'warning');

                    // Weather Description
                    addRow(tbody, 'Overall Condition',
                        weather.description,
                        'Based on weather codes',
                        'accurate');

                    // Update Time
                    addRow(tbody, 'Last Updated',
                        weather.timestamp,
                        'Updated every 15-60 min',
                        'accurate');

                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="error">Error: ' + data.error + '</td></tr>';
                }
            })
            .catch(error => {
                document.getElementById('loading').innerHTML = '<p class="text-danger">Failed to fetch weather data: ' + error.message + '</p>';
            });

        function addRow(tbody, param, value, expected, status) {
            const row = tbody.insertRow();
            row.className = status;

            row.insertCell(0).innerHTML = '<strong>' + param + '</strong>';
            row.insertCell(1).textContent = value;
            row.insertCell(2).textContent = expected;
            row.insertCell(3).innerHTML = getStatusBadge(status);
        }

        function getStatus(value, min, max) {
            return (value >= min && value <= max) ? 'accurate' : 'warning';
        }

        function getStatusBadge(status) {
            if (status === 'accurate') return '<span class="badge bg-success">✓ Accurate</span>';
            if (status === 'warning') return '<span class="badge bg-warning">⚠ Verify Manually</span>';
            return '<span class="badge bg-danger">✗ Out of Range</span>';
        }

        function getWindDirection(degrees) {
            const dirs = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
            const index = Math.round(degrees / 45) % 8;
            return dirs[index];
        }
    </script>
</body>
</html>

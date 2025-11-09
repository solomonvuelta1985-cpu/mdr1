<?php
// fetch_weather_api.php - Fetch real-time weather data from multiple sources
header('Content-Type: application/json');

// Baggao, Cagayan coordinates
$latitude = 17.6167;
$longitude = 121.9333;
$location = "Baggao, Cagayan, Philippines";

// Initialize response
$response = [
    'success' => false,
    'data' => null,
    'error' => null
];

try {
    // Option 1: OpenWeatherMap API (Free tier available)
    // Sign up at: https://openweathermap.org/api
    // For this example, we'll use Open-Meteo which doesn't require an API key

    // Fetch current weather from Open-Meteo (Free, no API key needed)
    $weatherUrl = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current=temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,rain,weather_code,cloud_cover,wind_speed_10m,wind_direction_10m&timezone=Asia/Manila";

    $weatherData = @file_get_contents($weatherUrl);

    if ($weatherData === false) {
        throw new Exception("Failed to fetch weather data");
    }

    $weather = json_decode($weatherData, true);

    if (!$weather || !isset($weather['current'])) {
        throw new Exception("Invalid weather data received");
    }

    $current = $weather['current'];

    // Map weather codes to descriptions
    $weatherCode = $current['weather_code'];
    $weatherDescription = getWeatherDescription($weatherCode);

    // Determine cloud coverage
    $cloudCover = $current['cloud_cover'];
    if ($cloudCover < 20) {
        $clouds = 'CLEAR SKY';
    } elseif ($cloudCover < 50) {
        $clouds = 'PARTLY CLOUDY';
    } elseif ($cloudCover < 80) {
        $clouds = 'CLOUDY';
    } else {
        $clouds = 'OVERCAST';
    }

    // Determine rain intensity
    $precipitation = $current['precipitation'];
    if ($precipitation == 0) {
        $rains = 'NONE';
    } elseif ($precipitation < 2.5) {
        $rains = 'LIGHT';
    } elseif ($precipitation < 10) {
        $rains = 'MODERATE';
    } else {
        $rains = 'HEAVY';
    }

    // Determine wind strength
    $windSpeed = $current['wind_speed_10m'];
    if ($windSpeed < 12) {
        $winds = 'LIGHT';
    } elseif ($windSpeed < 28) {
        $winds = 'MODERATE';
    } elseif ($windSpeed < 50) {
        $winds = 'STRONG';
    } else {
        $winds = 'VERY STRONG';
    }

    // Try to fetch typhoon/tropical storm data from PAGASA or global sources
    $weatherSystem = fetchTyphoonData($latitude, $longitude);

    $response['success'] = true;
    $response['data'] = [
        'location' => $location,
        'weather_system' => $weatherSystem,
        'weather_clouds' => $clouds,
        'weather_rains' => $rains,
        'weather_winds' => $winds,
        'temperature' => round($current['temperature_2m'], 1),
        'humidity' => $current['relative_humidity_2m'],
        'wind_speed' => round($current['wind_speed_10m'], 1),
        'wind_direction' => $current['wind_direction_10m'],
        'description' => $weatherDescription,
        'timestamp' => $current['time']
    ];

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);

// Helper function to get weather description from code
function getWeatherDescription($code) {
    $descriptions = [
        0 => 'Clear sky',
        1 => 'Mainly clear',
        2 => 'Partly cloudy',
        3 => 'Overcast',
        45 => 'Foggy',
        48 => 'Depositing rime fog',
        51 => 'Light drizzle',
        53 => 'Moderate drizzle',
        55 => 'Dense drizzle',
        61 => 'Slight rain',
        63 => 'Moderate rain',
        65 => 'Heavy rain',
        71 => 'Slight snow',
        73 => 'Moderate snow',
        75 => 'Heavy snow',
        77 => 'Snow grains',
        80 => 'Slight rain showers',
        81 => 'Moderate rain showers',
        82 => 'Violent rain showers',
        85 => 'Slight snow showers',
        86 => 'Heavy snow showers',
        95 => 'Thunderstorm',
        96 => 'Thunderstorm with slight hail',
        99 => 'Thunderstorm with heavy hail'
    ];

    return $descriptions[$code] ?? 'Unknown';
}

// Fetch typhoon/tropical storm data
function fetchTyphoonData($lat, $lon) {
    // ALWAYS check PAGASA for active storms first
    // This ensures we get Philippine storm names regardless of weather conditions
    $stormName = checkForActiveStorms();

    if ($stormName) {
        return $stormName;
    }

    // If no PAGASA storm found, check weather severity as fallback
    try {
        $alertsUrl = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current=weather_code&alerts=true&timezone=Asia/Manila";
        $alertsData = @file_get_contents($alertsUrl);

        if ($alertsData) {
            $alerts = json_decode($alertsData, true);

            if (isset($alerts['current']) && isset($alerts['current']['weather_code'])) {
                $code = $alerts['current']['weather_code'];

                // Weather codes 95-99 indicate severe weather/storms
                if ($code >= 95) {
                    return "Severe Weather Alert";
                }
            }
        }
    } catch (Exception $e) {
        // Silently fail - weather system is optional
    }

    return null;
}

// Check for active named storms in the Western Pacific
function checkForActiveStorms() {
    // Try to scrape PAGASA Severe Weather Bulletin for active storm information
    try {
        $pagasaUrl = "https://www.pagasa.dost.gov.ph/tropical-cyclone/severe-weather-bulletin";

        // Set context options to mimic browser request
        $options = [
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
            ]
        ];
        $context = stream_context_create($options);

        $html = @file_get_contents($pagasaUrl, false, $context);

        if ($html === false) {
            // If web scraping fails, return null
            return null;
        }

        // Parse HTML to find storm name and classification
        // PAGASA uses format: Super Typhoon &quot;Uwan&quot; or Super Typhoon "Uwan"

        // Pattern 1: Look for PAGASA's exact format with HTML entities (&quot;)
        if (preg_match('/(Super Typhoon|Typhoon|Severe Tropical Storm|Tropical Storm|Tropical Depression)\s+&quot;([A-Za-z]+)&quot;/i', $html, $matches)) {
            $classification = $matches[1];
            $stormName = ucfirst(strtolower($matches[2])); // Convert to title case
            return $classification . ' "' . strtoupper($stormName) . '"';
        }

        // Pattern 2: Look for regular quotes
        if (preg_match('/(Super Typhoon|Typhoon|Severe Tropical Storm|Tropical Storm|Tropical Depression)\s+"([A-Za-z]+)"/i', $html, $matches)) {
            $classification = $matches[1];
            $stormName = $matches[2];
            return $classification . ' "' . strtoupper($stormName) . '"';
        }

        // Pattern 3: Look in <h3> tags specifically (PAGASA bulletin title)
        if (preg_match('/<h3>(Super Typhoon|Typhoon|Severe Tropical Storm|Tropical Storm|Tropical Depression)\s+&quot;([A-Za-z]+)&quot;/i', $html, $h3Matches)) {
            $classification = $h3Matches[1];
            $stormName = $h3Matches[2];
            return $classification . ' "' . strtoupper($stormName) . '"';
        }

    } catch (Exception $e) {
        // Silently fail and return null
    }

    return null;
}
?>

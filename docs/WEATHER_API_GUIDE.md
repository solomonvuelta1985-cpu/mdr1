# Weather API Integration Guide

## Current Implementation

The SITREP system now includes **automatic weather data fetching** from online sources!

### Features
- ✅ Real-time weather conditions (clouds, rain, wind)
- ✅ Temperature and humidity data
- ✅ Wind speed and direction
- ✅ Automatic weather classification
- ✅ **PAGASA Tropical Storm/Typhoon Name Detection** (AUTOMATIC!)

### How to Use

1. **Go to SITREP Settings**: http://localhost/mdr1/public/sitrep_settings.php
2. **Click "Auto-Fetch Weather Data"** button
3. The system will automatically:
   - Fetch current weather for Baggao, Cagayan
   - Update clouds, rains, and winds fields
   - Show temperature and wind speed
   - **AUTOMATICALLY DETECT PAGASA STORM NAMES** (e.g., Super Typhoon "UWAN", Tropical Storm "NANDO")
4. **Review the data** and click Save
5. The storm name will appear on your SITREP report!

## API Integration Details

### Current APIs

#### 1. PAGASA Storm Data (Web Scraping)
- **Provider**: PAGASA (Philippine Atmospheric, Geophysical and Astronomical Services Administration)
- **Requires API Key**: NO
- **Source**: https://www.pagasa.dost.gov.ph/tropical-cyclone/severe-weather-bulletin
- **Method**: Real-time web scraping of PAGASA Severe Weather Bulletins
- **Data Provided**:
  - Active tropical cyclone names (Philippine designation)
  - Storm classification (Super Typhoon, Typhoon, Tropical Storm, etc.)
  - Automatically formatted for SITREP display
- **Update Frequency**: Real-time (fetches latest bulletin)

#### 2. Open-Meteo Weather API (Free)
- **Provider**: Open-Meteo
- **Requires API Key**: NO
- **Coordinates**: Baggao, Cagayan (17.6167°N, 121.9333°E)
- **Data Provided**:
  - Cloud coverage
  - Precipitation/rainfall
  - Wind speed and direction
  - Temperature
  - Humidity
  - Weather codes

### Weather Classification

**Clouds:**
- `< 20%` coverage = CLEAR SKY
- `20-50%` = PARTLY CLOUDY
- `50-80%` = CLOUDY
- `> 80%` = OVERCAST

**Rains:**
- `0 mm` = NONE
- `< 2.5 mm/h` = LIGHT
- `2.5-10 mm/h` = MODERATE
- `> 10 mm/h` = HEAVY

**Winds:**
- `< 12 km/h` = LIGHT
- `12-28 km/h` = MODERATE
- `28-50 km/h` = STRONG
- `> 50 km/h` = VERY STRONG

## PAGASA Integration - ALREADY IMPLEMENTED! ✅

The system now **automatically fetches Philippine typhoon/storm names** from PAGASA!

### How It Works

1. **Source**: Fetches from https://www.pagasa.dost.gov.ph/tropical-cyclone/severe-weather-bulletin
2. **Method**: Parses HTML to extract storm classification and name
3. **Format Detection**: Recognizes PAGASA's format `Super Typhoon &quot;Uwan&quot;`
4. **Output**: Returns formatted string like `Super Typhoon "UWAN"`

### Current Detection Capability

The system can automatically detect:
- **Super Typhoon** (e.g., "UWAN", "PEPITO")
- **Typhoon**
- **Severe Tropical Storm**
- **Tropical Storm** (e.g., "NANDO")
- **Tropical Depression**

### Example Detection

**Right now (November 9, 2025)**, the system is detecting:
```
Super Typhoon "UWAN"
```

This is the actual active storm in the Philippine Area of Responsibility!

### Manual Override (If Needed)

The system allows manual input as a backup:
1. If auto-fetch doesn't detect a storm (rare)
2. Manually type the storm name in "Weather System" field
3. Format: `Tropical Storm "NANDO"` or `Super Typhoon "PEPITO"`

**Note**: Manual override is rarely needed since the system now automatically detects PAGASA storm names!

## Testing

Test the API integration:

```bash
# Direct API test
curl http://localhost/mdr1/public/fetch_weather_api.php
```

Expected response (with active storm):
```json
{
    "success": true,
    "data": {
        "location": "Baggao, Cagayan, Philippines",
        "weather_system": "Super Typhoon \"UWAN\"",
        "weather_clouds": "OVERCAST",
        "weather_rains": "LIGHT",
        "weather_winds": "MODERATE",
        "temperature": 24.6,
        "humidity": 93,
        "wind_speed": 24.7,
        "wind_direction": 352,
        "description": "Thunderstorm",
        "timestamp": "2025-11-09T17:45"
    }
}
```

Expected response (no active storm):
```json
{
    "success": true,
    "data": {
        "location": "Baggao, Cagayan, Philippines",
        "weather_system": null,
        "weather_clouds": "PARTLY CLOUDY",
        "weather_rains": "NONE",
        "weather_winds": "MODERATE",
        "temperature": 28.5,
        "humidity": 75,
        "wind_speed": 15.2,
        "wind_direction": 180,
        "description": "Partly cloudy",
        "timestamp": "2025-11-09T10:00"
    }
}
```

## Troubleshooting

### Issue: "Failed to fetch weather data"
**Solution:**
- Check internet connection
- Verify PHP `allow_url_fopen` is enabled in php.ini
- Check firewall/proxy settings

### Issue: No storm name detected (when there IS an active storm)
**Solution:**
- PAGASA website might be down temporarily
- Storm bulletin page format may have changed
- Manually enter storm name as backup
- Check PAGASA website: https://www.pagasa.dost.gov.ph/tropical-cyclone/severe-weather-bulletin

### Issue: Weather data is inaccurate
**Solution:**
- Verify coordinates are correct (Baggao: 17.6167°N, 121.9333°E)
- Try different weather API (OpenWeatherMap, WeatherAPI.com)
- Cross-reference with PAGASA official data

## Future Enhancements

1. ✅ ~~**PAGASA Storm Detection**~~ - **ALREADY IMPLEMENTED!**
2. ✅ ~~**Automatic Storm Name Fetching**~~ - **ALREADY IMPLEMENTED!**
3. **Historical Weather Data** - Track weather changes over time
4. **Weather Alerts** - Send notifications for severe weather
5. **Multi-location Support** - Track weather for different barangays
6. **Offline Mode** - Cache weather data for when internet is unavailable
7. **International Storm Name** - Display both Philippine and international names (e.g., "UWAN (USAGI)")

## Files Modified

- ✅ `sitrep_settings.php` - Added "Auto-Fetch Weather Data" button with AJAX integration
- ✅ `fetch_weather_api.php` - Weather API integration + PAGASA storm name scraping
- ✅ `sitrep.php` - Displays storm name in SITREP header
- ✅ `sitrep_export.php` - Includes storm name in exported documents
- ✅ `test_weather_accuracy.php` - Live accuracy testing tool
- ✅ This guide document - Comprehensive integration documentation

## Support

For API integration support:
- Open-Meteo: https://open-meteo.com/
- PAGASA: https://www.pagasa.dost.gov.ph/
- OpenWeatherMap: https://openweathermap.org/ (alternative, requires API key)

# BMKG API Integration Documentation

## Overview

This application integrates with the official BMKG (Badan Meteorologi, Klimatologi, dan Geofisika) Weather Forecast API to provide real-time weather data for activity scheduling.

## API Endpoint

**Base URL:** `https://api.bmkg.go.id/publik/prakiraan-cuaca`

**Parameters:**
- `adm4`: Administrative code level 4 (village/kelurahan code)

## Integration Features

### 1. Weather Data Retrieval
- Fetches 3-day weather forecast from BMKG API
- Parses weather conditions, temperature, humidity, and wind speed
- Handles API failures gracefully with fallback mock data

### 2. Activity Scheduling
- Analyzes weather conditions for outdoor activities
- Filters suitable time slots based on weather criteria
- Provides recommendations for optimal activity timing

### 3. Database Logging
- Saves planned activities with selected time slots
- Stores weather data used for decision making
- Tracks activity status (planned, completed, cancelled)

## Weather Condition Mapping

The BMKG API returns weather codes and descriptions that are mapped to simplified conditions:

| BMKG Description | Simplified Condition | Suitable for Outdoor |
|------------------|---------------------|---------------------|
| Cerah / Clear    | cerah               | ✅ Yes              |
| Berawan / Cloudy | berawan             | ✅ Yes              |
| Berawan Sebagian | berawan sebagian    | ✅ Yes              |
| Kabut / Fog      | kabut               | ✅ Yes (limited)    |
| Hujan Ringan     | hujan ringan        | ❌ No               |
| Hujan / Rain     | hujan               | ❌ No               |

## Weather Suitability Criteria

An activity time slot is considered suitable when:
- Weather condition is not rainy (codes 60-65, 95, 97)
- Temperature is between 20°C and 35°C
- Humidity is below 85%

## Usage Examples

### Testing the API Integration

```bash
# Test with default region (Jakarta)
php artisan bmkg:test-api

# Test with specific region code
php artisan bmkg:test-api 11.01.01.2001
```

### Using in Laravel Application

```php
use App\Services\BMKGWeatherService;

$weatherService = new BMKGWeatherService();

// Get weather suggestions for activity planning
$suggestions = $weatherService->getWeatherSuggestions([
    'activity_name' => 'Field Survey',
    'location' => 'Jakarta',
    'region_code' => '31.01.01.1001',
    'preferred_date' => '2025-08-20',
]);

if ($suggestions['success']) {
    foreach ($suggestions['suggestions'] as $day) {
        echo "Date: {$day['date']}\n";
        echo "Day: {$day['day_name']}\n";
        echo "Optimal slots: " . count($day['time_slots']) . "\n";
    }
}
```

## Response Format

### BMKG API Response Structure

```json
{
    "lokasi": {
        "adm4": "31.01.01.1001",
        "provinsi": "DKI Jakarta",
        "kotkab": "Administrasi Kepulauan Seribu",
        "kecamatan": "Kepulauan Seribu Utara",
        "desa": "Pulau Panggang"
    },
    "data": [
        {
            "cuaca": [
                [
                    {
                        "datetime": "2025-08-19T19:00:00Z",
                        "t": 27,
                        "weather": 60,
                        "weather_desc": "Hujan Ringan",
                        "hu": 80,
                        "ws": 9,
                        "local_datetime": "2025-08-20 02:00:00"
                    }
                ]
            ]
        }
    ]
}
```

### Application Response Format

```json
{
    "success": true,
    "activity_name": "Field Survey",
    "location": "Jakarta",
    "suggestions": [
        {
            "date": "2025-08-20",
            "day_name": "Selasa",
            "time_slots": [
                {
                    "time": "08:00",
                    "period": "Pagi",
                    "weather_condition": "cerah",
                    "temperature": 27,
                    "humidity": 75,
                    "recommendation": "Cuaca cerah, sempurna untuk aktivitas outdoor"
                }
            ]
        }
    ]
}
```

## Error Handling

The application implements robust error handling:

1. **API Timeout**: 15-second timeout for BMKG API requests
2. **Connection Errors**: Graceful fallback to mock data
3. **Invalid Responses**: Data validation and sanitization
4. **Rate Limiting**: Respectful API usage patterns

## Logging

All API interactions are logged for monitoring and debugging:

```php
Log::info("Activity saved successfully", [
    'activity_id' => $activity->id,
    'region_code' => $regionCode,
    'selected_slots_count' => count($selectedSlots)
]);

Log::warning("BMKG API unavailable, using mock data", [
    'region_code' => $regionCode
]);

Log::error("Error fetching weather data", [
    'error' => $exception->getMessage(),
    'region_code' => $regionCode
]);
```

## Performance Considerations

- API responses are processed efficiently with minimal memory usage
- Mock data fallback ensures application availability
- Database queries use appropriate indexes for performance
- Frontend includes loading states for better UX

## Security

- Input validation on all user inputs
- SQL injection protection via Eloquent ORM
- XSS protection in Blade templates
- CSRF protection on form submissions

## Future Enhancements

1. **Caching**: Implement Redis caching for API responses
2. **Real-time Updates**: WebSocket notifications for weather changes
3. **Historical Data**: Store historical weather patterns
4. **Advanced Analytics**: Weather prediction accuracy tracking
5. **Notifications**: Email/SMS alerts for weather changes

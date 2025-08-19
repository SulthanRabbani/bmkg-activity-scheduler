<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BMKGWeatherService
{
    private $baseUrl = 'https://api.bmkg.go.id/publik/prakiraan-cuaca';

    /**
     * Get weather forecast data from BMKG API
     */
    public function getWeatherForecast($regionCode)
    {
        try {
            // Call the actual BMKG API
            $response = Http::timeout(15)->get($this->baseUrl, [
                'adm4' => $regionCode
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']) && count($data['data']) > 0) {
                    return $this->parseBMKGApiResponse($data);
                }
            }

            // If API fails, log the error and return mock data for demo
            Log::warning("BMKG API unavailable or returned empty data for region code: {$regionCode}");
            Log::debug("API Response status: " . $response->status());
            Log::debug("API Response body: " . $response->body());

            return $this->generateMockWeatherData();

        } catch (\Exception $e) {
            Log::error("Error fetching weather data from BMKG API: " . $e->getMessage());
            return $this->generateMockWeatherData();
        }
    }

    /**
     * Parse BMKG API response data
     */
    private function parseBMKGApiResponse($data)
    {
        try {
            $forecast = [];

            if (!isset($data['data']) || empty($data['data'])) {
                return $this->generateMockWeatherData();
            }

            // Get the first region's data (should be the requested region)
            $regionData = $data['data'][0];

            if (!isset($regionData['cuaca']) || empty($regionData['cuaca'])) {
                return $this->generateMockWeatherData();
            }

            // Parse weather data for 3 days
            $weatherData = $regionData['cuaca'];

            for ($dayIndex = 0; $dayIndex < min(3, count($weatherData)); $dayIndex++) {
                $dayData = $weatherData[$dayIndex];
                $dailyForecast = [];

                // Group forecasts by time periods
                foreach ($dayData as $timeSlot) {
                    $hour = (int) Carbon::parse($timeSlot['local_datetime'])->format('H');

                    // Define periods based on hour
                    if ($hour >= 6 && $hour < 12) {
                        $period = 'morning';
                    } elseif ($hour >= 12 && $hour < 18) {
                        $period = 'afternoon';
                    } else {
                        $period = 'evening';
                    }

                    // Only store if we haven't recorded this period yet
                    if (!isset($dailyForecast[$period])) {
                        $condition = $this->translateWeatherCondition($timeSlot['weather_desc']);
                        $suitable = $this->isConditionSuitable($timeSlot['weather']);

                        $dailyForecast[$period] = [
                            'condition' => $condition,
                            'temperature' => $timeSlot['t'],
                            'humidity' => $timeSlot['hu'],
                            'wind_speed' => $timeSlot['ws'],
                            'suitable' => $suitable,
                            'raw_weather_code' => $timeSlot['weather'],
                            'original_desc' => $timeSlot['weather_desc']
                        ];
                    }
                }

                $forecast[] = $dailyForecast;
            }

            return $forecast;

        } catch (\Exception $e) {
            Log::error("Error parsing BMKG API response: " . $e->getMessage());
            return $this->generateMockWeatherData();
        }
    }

    /**
     * Translate BMKG weather description to simplified condition
     */
    private function translateWeatherCondition($weatherDesc)
    {
        $condition = strtolower($weatherDesc);

        if (strpos($condition, 'cerah') !== false) {
            return 'cerah';
        } elseif (strpos($condition, 'berawan') !== false) {
            return 'berawan';
        } elseif (strpos($condition, 'kabut') !== false) {
            return 'kabut';
        } elseif (strpos($condition, 'hujan ringan') !== false || strpos($condition, 'light rain') !== false) {
            return 'hujan ringan';
        } elseif (strpos($condition, 'hujan') !== false || strpos($condition, 'rain') !== false) {
            return 'hujan';
        } else {
            return 'berawan sebagian';
        }
    }

    /**
     * Check if weather condition is suitable based on BMKG weather code
     */
    private function isConditionSuitable($weatherCode)
    {
        // BMKG weather codes:
        // 0-1: Clear/Sunny
        // 2-3: Partly Cloudy/Cloudy
        // 60-65: Rain (Light to Heavy)
        // etc.

        $unsuitableCodes = [60, 61, 62, 63, 64, 65, 95, 97]; // Rain and storm codes

        return !in_array($weatherCode, $unsuitableCodes);
    }

    /**
     * Generate realistic mock weather data for demo purposes
     */
    private function generateMockWeatherData()
    {
        $weatherConditions = [
            'cerah' => ['probability' => 0.3, 'suitable' => true],
            'berawan' => ['probability' => 0.25, 'suitable' => true],
            'berawan sebagian' => ['probability' => 0.2, 'suitable' => true],
            'kabut' => ['probability' => 0.1, 'suitable' => true],
            'hujan ringan' => ['probability' => 0.1, 'suitable' => false],
            'hujan' => ['probability' => 0.05, 'suitable' => false],
        ];

        $forecast = [];

        for ($day = 0; $day < 3; $day++) {
            $dailyForecast = [];

            $periods = ['morning', 'afternoon', 'evening'];

            foreach ($periods as $period) {
                $condition = $this->getRandomWeatherCondition($weatherConditions);
                $baseTemp = $this->getBaseTemperature($period);

                $dailyForecast[$period] = [
                    'condition' => $condition,
                    'temperature' => $baseTemp + rand(-3, 3),
                    'humidity' => rand(55, 90),
                    'wind_speed' => rand(5, 20),
                    'suitable' => $weatherConditions[$condition]['suitable']
                ];
            }

            $forecast[] = $dailyForecast;
        }

        return $forecast;
    }

    /**
     * Get random weather condition based on probability
     */
    private function getRandomWeatherCondition($conditions)
    {
        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0;

        foreach ($conditions as $condition => $data) {
            $cumulative += $data['probability'];
            if ($rand <= $cumulative) {
                return $condition;
            }
        }

        return 'cerah'; // Fallback
    }

    /**
     * Get base temperature for different periods
     */
    private function getBaseTemperature($period)
    {
        switch ($period) {
            case 'morning':
                return rand(22, 26);
            case 'afternoon':
                return rand(28, 33);
            case 'evening':
                return rand(24, 29);
            default:
                return 25;
        }
    }

    /**
     * Check if weather conditions are suitable for outdoor activities
     */
    public function isWeatherSuitable($weatherData)
    {
        $condition = strtolower($weatherData['condition']);
        $temperature = $weatherData['temperature'];
        $humidity = $weatherData['humidity'];

        // Define suitable conditions
        $suitableConditions = ['cerah', 'berawan', 'berawan sebagian', 'kabut'];

        return in_array($condition, $suitableConditions)
            && $temperature >= 20
            && $temperature <= 35
            && $humidity <= 85;
    }

    /**
     * Get weather recommendation text
     */
    public function getWeatherRecommendation($weatherData)
    {
        $temp = $weatherData['temperature'];
        $condition = strtolower($weatherData['condition']);
        $humidity = $weatherData['humidity'];

        if (strpos($condition, 'hujan') !== false) {
            return "Cuaca hujan, tidak disarankan untuk aktivitas outdoor";
        }

        if ($temp >= 32) {
            return "Cuaca sangat panas, pastikan membawa topi, sunscreen, dan air minum yang cukup";
        }

        if ($temp >= 28) {
            return "Cuaca cukup panas, disarankan membawa topi dan air minum";
        }

        if ($temp <= 22) {
            return "Cuaca sejuk dan nyaman untuk aktivitas outdoor";
        }

        if ($humidity >= 80) {
            return "Kelembaban tinggi, pastikan tetap terhidrasi dengan baik";
        }

        if (strpos($condition, 'berawan') !== false) {
            return "Cuaca berawan, kondisi ideal untuk aktivitas outdoor";
        }

        if ($condition === 'cerah') {
            return "Cuaca cerah, sempurna untuk aktivitas outdoor";
        }

        return "Kondisi cuaca cukup baik untuk aktivitas outdoor";
    }

    /**
     * Get activity recommendations based on weather
     */
    public function getActivityRecommendations($weatherData)
    {
        $recommendations = [];
        $condition = strtolower($weatherData['condition']);
        $temp = $weatherData['temperature'];

        if ($this->isWeatherSuitable($weatherData)) {
            if ($condition === 'cerah' && $temp <= 30) {
                $recommendations[] = "Ideal untuk survey lapangan";
                $recommendations[] = "Cocok untuk pemeliharaan alat outdoor";
                $recommendations[] = "Sempurna untuk dokumentasi foto/video";
            } elseif (strpos($condition, 'berawan') !== false) {
                $recommendations[] = "Baik untuk aktivitas yang memerlukan konsentrasi";
                $recommendations[] = "Cocok untuk training outdoor";
                $recommendations[] = "Ideal untuk kunjungan lapangan";
            }
        } else {
            $recommendations[] = "Lebih baik ditunda atau dilakukan indoor";
        }

        return $recommendations;
    }

    /**
     * Get weather suggestions for activity planning
     */
    public function getWeatherSuggestions($requestData)
    {
        try {
            $activityName = $requestData['activity_name'];
            $location = $requestData['location'];
            $regionCode = $requestData['region_code'];
            $preferredDate = $requestData['preferred_date'];

            // Get weather forecast for the region code
            $weatherData = $this->getWeatherForecast($regionCode);

            // Generate suggestions for the next 3 days starting from preferred date
            $suggestions = [];
            $startDate = Carbon::parse($preferredDate);

            for ($i = 0; $i < 3; $i++) {
                $currentDate = $startDate->copy()->addDays($i);
                $dayName = $currentDate->locale('id')->isoFormat('dddd');

                $timeSlots = [];

                // Check if we have real data for this day
                if (isset($weatherData[$i])) {
                    $dayWeatherData = $weatherData[$i];

                    // Process each period of the day
                    foreach (['morning', 'afternoon', 'evening'] as $period) {
                        if (isset($dayWeatherData[$period])) {
                            $periodData = $dayWeatherData[$period];

                            // Check if weather is suitable for outdoor activity
                            if ($periodData['suitable']) {
                                $timeSlots[] = [
                                    'time' => $this->getPeriodTime($period),
                                    'period' => $this->getPeriodLabel($period),
                                    'weather_condition' => $periodData['condition'],
                                    'temperature' => $periodData['temperature'],
                                    'humidity' => $periodData['humidity'],
                                    'wind_speed' => $periodData['wind_speed'],
                                    'recommendation' => $this->getWeatherRecommendation($periodData)
                                ];
                            }
                        }
                    }
                } else {
                    // Fallback to simulated data if real data not available
                    for ($hour = 6; $hour <= 18; $hour += 3) {
                        $timeSlot = sprintf('%02d:00', $hour);
                        $period = $hour < 12 ? 'Pagi' : ($hour < 15 ? 'Siang' : 'Sore');

                        $weatherCondition = $this->simulateWeatherCondition($hour, $i);
                        $temperature = $this->simulateTemperature($hour);
                        $humidity = $this->simulateHumidity($hour);

                        if ($this->isWeatherSuitable([
                            'condition' => $weatherCondition,
                            'temperature' => $temperature,
                            'humidity' => $humidity
                        ])) {
                            $timeSlots[] = [
                                'time' => $timeSlot,
                                'period' => $period,
                                'weather_condition' => $weatherCondition,
                                'temperature' => $temperature,
                                'humidity' => $humidity,
                                'recommendation' => $this->getTimeSlotRecommendation($weatherCondition, $temperature, $period)
                            ];
                        }
                    }
                }

                $suggestions[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'day_name' => $dayName,
                    'time_slots' => $timeSlots
                ];
            }

            return [
                'success' => true,
                'activity_name' => $activityName,
                'location' => $location,
                'suggestions' => $suggestions
            ];

        } catch (\Exception $e) {
            Log::error("Error generating weather suggestions: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menganalisis data cuaca.'
            ];
        }
    }

    /**
     * Get time string for period
     */
    private function getPeriodTime($period)
    {
        switch ($period) {
            case 'morning':
                return '08:00';
            case 'afternoon':
                return '14:00';
            case 'evening':
                return '17:00';
            default:
                return '12:00';
        }
    }

    /**
     * Get Indonesian label for period
     */
    private function getPeriodLabel($period)
    {
        switch ($period) {
            case 'morning':
                return 'Pagi';
            case 'afternoon':
                return 'Siang';
            case 'evening':
                return 'Sore';
            default:
                return 'Siang';
        }
    }

    /**
     * Simulate weather condition for demo purposes
     */
    private function simulateWeatherCondition($hour, $dayOffset)
    {
        $conditions = ['cerah', 'berawan sebagian', 'berawan', 'kabut', 'hujan ringan'];

        // Better weather in the morning, chance of rain in afternoon
        if ($hour >= 6 && $hour <= 10) {
            return $conditions[rand(0, 1)]; // cerah or berawan sebagian
        } elseif ($hour >= 11 && $hour <= 14) {
            return $conditions[rand(1, 2)]; // berawan sebagian or berawan
        } else {
            return $conditions[rand(2, 4)]; // berawan, kabut, or hujan ringan
        }
    }

    /**
     * Simulate temperature based on time
     */
    private function simulateTemperature($hour)
    {
        if ($hour >= 6 && $hour <= 9) {
            return rand(24, 28);
        } elseif ($hour >= 10 && $hour <= 15) {
            return rand(28, 33);
        } else {
            return rand(25, 30);
        }
    }

    /**
     * Simulate humidity based on time
     */
    private function simulateHumidity($hour)
    {
        if ($hour >= 6 && $hour <= 9) {
            return rand(70, 85);
        } elseif ($hour >= 10 && $hour <= 15) {
            return rand(60, 75);
        } else {
            return rand(65, 80);
        }
    }

    /**
     * Get specific recommendation for time slot
     */
    private function getTimeSlotRecommendation($condition, $temperature, $period)
    {
        $recommendations = [
            'Waktu optimal untuk aktivitas outdoor',
            'Cuaca mendukung untuk kegiatan lapangan',
            'Kondisi baik untuk survey lokasi',
            'Ideal untuk dokumentasi dan inspeksi',
            'Cocok untuk kegiatan pelatihan outdoor'
        ];

        if ($temperature > 30) {
            return 'Suhu cukup panas, bawa air minum yang cukup';
        }

        if (strpos($condition, 'cerah') !== false) {
            return 'Cuaca cerah, sempurna untuk aktivitas outdoor';
        }

        return $recommendations[array_rand($recommendations)];
    }
}

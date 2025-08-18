<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Services\BMKGWeatherService;

class ActivitySchedulerController extends Controller
{
    protected $weatherService;

    public function __construct(BMKGWeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Display the main activity scheduler form
     */
    public function index()
    {
        return view('activity-scheduler');
    }

    /**
     * Get weather forecast suggestions for activity scheduling
     */
    public function getWeatherSuggestions(Request $request)
    {
        $request->validate([
            'activity_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'preferred_date' => 'required|date|after_or_equal:today',
        ]);

        try {
            // Get weather forecast from BMKG API
            $weatherData = $this->weatherService->getWeatherForecast($request->location);

            if (!$weatherData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch weather data for the specified location.'
                ], 400);
            }

            // Parse and filter suitable time slots
            $suggestions = $this->parseWeatherSuggestions($weatherData, $request->preferred_date);

            return response()->json([
                'success' => true,
                'activity_name' => $request->activity_name,
                'location' => $request->location,
                'preferred_date' => $request->preferred_date,
                'suggestions' => $suggestions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse weather data and suggest optimal time slots
     */
    private function parseWeatherSuggestions($weatherData, $preferredDate)
    {
        $suggestions = [];
        $startDate = Carbon::parse($preferredDate);

        // Check 3 days starting from preferred date
        for ($i = 0; $i < 3; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dayForecast = $weatherData[$i] ?? null;

            if ($dayForecast) {
                $timeSlots = $this->generateTimeSlots($date, $dayForecast);
                if (!empty($timeSlots)) {
                    $suggestions[] = [
                        'date' => $date->format('Y-m-d'),
                        'day_name' => $date->format('l'),
                        'time_slots' => $timeSlots
                    ];
                }
            }
        }

        return $suggestions;
    }

    /**
     * Generate suitable time slots based on weather conditions
     */
    private function generateTimeSlots($date, $dayForecast)
    {
        $timeSlots = [];
        $periods = [
            ['time' => '06:00', 'period' => 'Pagi', 'weather' => $dayForecast['morning']],
            ['time' => '08:00', 'period' => 'Pagi', 'weather' => $dayForecast['morning']],
            ['time' => '10:00', 'period' => 'Pagi', 'weather' => $dayForecast['morning']],
            ['time' => '13:00', 'period' => 'Siang', 'weather' => $dayForecast['afternoon']],
            ['time' => '15:00', 'period' => 'Sore', 'weather' => $dayForecast['afternoon']],
            ['time' => '17:00', 'period' => 'Sore', 'weather' => $dayForecast['evening']],
        ];

        foreach ($periods as $period) {
            if ($this->weatherService->isWeatherSuitable($period['weather'])) {
                $timeSlots[] = [
                    'time' => $period['time'],
                    'period' => $period['period'],
                    'weather_condition' => $period['weather']['condition'],
                    'temperature' => $period['weather']['temperature'],
                    'humidity' => $period['weather']['humidity'],
                    'wind_speed' => $period['weather']['wind_speed'] ?? 'N/A',
                    'recommendation' => $this->weatherService->getWeatherRecommendation($period['weather']),
                    'activity_suggestions' => $this->weatherService->getActivityRecommendations($period['weather'])
                ];
            }
        }

        return $timeSlots;
    }
}

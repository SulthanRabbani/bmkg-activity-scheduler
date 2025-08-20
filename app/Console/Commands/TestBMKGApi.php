<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BMKGWeatherService;
use App\Models\Region;

class TestBMKGApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bmkg:test-api {region_code?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test BMKG API integration with weather forecast';

    protected $weatherService;

    public function __construct(BMKGWeatherService $weatherService)
    {
        parent::__construct();
        $this->weatherService = $weatherService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $regionCode = $this->argument('region_code');

        if (!$regionCode) {
            // Use a default region code (Jakarta)
            $regionCode = '31.01.01.1001';
            $this->info("Using default region code: {$regionCode} (Pulau Panggang, Jakarta)");
        }

        $this->info("Testing BMKG API for region code: {$regionCode}");
        $this->line("=========================================");

        try {
            // Test weather forecast
            $this->info("Fetching weather forecast...");
            $weatherData = $this->weatherService->getWeatherForecast($regionCode);

            if (!empty($weatherData)) {
                $this->info("✅ Weather data retrieved successfully!");
                $this->line("Days of data: " . count($weatherData));

                foreach ($weatherData as $dayIndex => $dayData) {
                    $this->line("\nDay " . ($dayIndex + 1) . ":");
                    foreach ($dayData as $period => $data) {
                        $this->line("  {$period}: {$data['condition']}, {$data['temperature']}°C, {$data['humidity']}% humidity");
                    }
                }
            } else {
                $this->warn("⚠️  No weather data returned");
            }

            // Test weather suggestions
            $this->line("\n=========================================");
            $this->info("Testing weather suggestions...");

            $suggestions = $this->weatherService->getWeatherSuggestions([
                'activity_name' => 'Test Activity',
                'location' => 'Test Location',
                'region_code' => $regionCode,
                'preferred_date' => now()->format('Y-m-d')
            ]);

            if ($suggestions['success']) {
                $this->info("✅ Weather suggestions generated successfully!");
                $this->line("Total days: " . count($suggestions['suggestions']));

                foreach ($suggestions['suggestions'] as $dayIndex => $day) {
                    $this->line("\n{$day['day_name']} ({$day['date']}):");
                    $this->line("  Optimal time slots: " . count($day['time_slots']));

                    foreach ($day['time_slots'] as $slot) {
                        $this->line("    {$slot['time']} ({$slot['period']}): {$slot['weather_condition']}, {$slot['temperature']}°C");
                    }
                }
            } else {
                $this->error("❌ Failed to generate weather suggestions: " . $suggestions['message']);
            }

        } catch (\Exception $e) {
            $this->error("❌ Error occurred: " . $e->getMessage());
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
        }

        $this->line("\n=========================================");
        $this->info("Test completed!");
    }
}

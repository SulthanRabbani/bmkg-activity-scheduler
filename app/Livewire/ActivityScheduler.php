<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\BMKGWeatherService;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;

class ActivityScheduler extends Component
{
    #[Validate('required|string|min:3')]
    public $activityName = '';

    #[Validate('required|string|min:3')]
    public $location = '';

    #[Validate('required|date|after_or_equal:today')]
    public $preferredDate = '';

    public $suggestions = [];
    public $loading = false;
    public $errorMessage = '';
    public $showResults = false;

    protected $weatherService;

    public function boot(BMKGWeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function mount()
    {
        $this->preferredDate = now()->format('Y-m-d');
    }

    public function searchOptimalTime()
    {
        $this->validate();

        $this->loading = true;
        $this->errorMessage = '';
        $this->showResults = false;
        $this->suggestions = [];

        try {
            // Simulate API call delay for better UX
            sleep(1);

            $result = $this->weatherService->getWeatherSuggestions([
                'activity_name' => $this->activityName,
                'location' => $this->location,
                'preferred_date' => $this->preferredDate,
            ]);

            if ($result['success']) {
                $this->suggestions = $result['suggestions'];
                $this->showResults = true;
            } else {
                $this->errorMessage = $result['message'] ?? 'Terjadi kesalahan saat memproses permintaan Anda.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
            Log::error('Weather service error: ' . $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    public function resetForm()
    {
        $this->reset(['activityName', 'location', 'suggestions', 'errorMessage', 'showResults']);
        $this->preferredDate = now()->format('Y-m-d');
    }

    public function getWeatherIcon($condition)
    {
        $icons = [
            'cerah' => '☀️',
            'berawan' => '⛅',
            'berawan sebagian' => '🌤️',
            'kabut' => '🌫️',
            'hujan' => '🌧️',
            'hujan ringan' => '🌦️'
        ];

        return $icons[strtolower($condition)] ?? '🌤️';
    }

    public function render()
    {
        return view('livewire.activity-scheduler')
            ->layout('layouts.app');
    }
}

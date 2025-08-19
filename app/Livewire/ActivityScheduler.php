<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\BMKGWeatherService;
use App\Models\Region;
use App\Models\Activity;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class ActivityScheduler extends Component
{
    public $activityName = '';
    public $location = '';
    public $selectedRegionCode = '';
    public $preferredDate = '';

    public $locationSearch = '';
    public $showLocationDropdown = false;
    public $availableRegions = [];
    public $searchLoading = false;

    public $suggestions = [];
    public $weatherApiData = []; // Store the raw API response
    public $loading = false;
    public $errorMessage = '';
    public $showResults = false;
    public $selectedTimeSlots = []; // Track user-selected time slots
    public $activitySaved = false;

    protected $listeners = ['hideDropdown'];
    protected $weatherService;

    public function boot(BMKGWeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function mount()
    {
        $this->preferredDate = now()->format('Y-m-d');
        $this->loadRegions();
    }

    public function loadRegions()
    {
        // Load districts and villages for location selection
        $this->availableRegions = Region::whereIn('level', [3, 4])
            ->with('parent.parent.parent') // Load full hierarchy
            ->orderBy('name')
            ->limit(100) // Limit for initial load
            ->get()
            ->map(function ($region) {
                return [
                    'code' => $region->code,
                    'name' => $region->name,
                    'full_path' => $region->full_path,
                    'level' => $region->level,
                    'level_name' => $region->level_name
                ];
            });
    }

    public function updatedLocationSearch()
    {
        if (strlen($this->locationSearch) >= 2) {
            $this->searchLoading = true;

            $this->availableRegions = Region::whereIn('level', [3, 4])
                ->where('name', 'like', '%' . $this->locationSearch . '%')
                ->with('parent.parent.parent')
                ->orderBy('name')
                ->limit(50)
                ->get()
                ->map(function ($region) {
                    return [
                        'code' => $region->code,
                        'name' => $region->name,
                        'full_path' => $region->full_path,
                        'level' => $region->level,
                        'level_name' => $region->level_name
                    ];
                });

            $this->searchLoading = false;
            $this->showLocationDropdown = true;
        } else {
            $this->showLocationDropdown = false;
            $this->searchLoading = false;
            $this->loadRegions();
        }
    }

    public function selectRegion($regionCode, $regionName, $fullPath)
    {
        $this->selectedRegionCode = $regionCode;
        $this->location = $regionName;
        $this->locationSearch = $fullPath;
        $this->showLocationDropdown = false;
    }

    public function showLocationDropdown()
    {
        $this->showLocationDropdown = true;
    }

    public function hideLocationDropdown()
    {
        // Add small delay to allow click on dropdown items
        $this->dispatch('hideDropdownDelayed');
    }

    public function hideDropdown()
    {
        $this->showLocationDropdown = false;
    }

    public function searchOptimalTime()
    {
        $this->validate([
            'activityName' => 'required|string|min:3',
            'selectedRegionCode' => 'required|string',
            'preferredDate' => 'required|date|after_or_equal:today'
        ], [
            'selectedRegionCode.required' => 'Silakan pilih lokasi dari daftar yang tersedia.',
            'activityName.required' => 'Nama aktivitas harus diisi.',
            'activityName.min' => 'Nama aktivitas minimal 3 karakter.',
            'preferredDate.required' => 'Tanggal preferensi harus diisi.',
            'preferredDate.after_or_equal' => 'Tanggal preferensi tidak boleh kurang dari hari ini.'
        ]);

        $this->loading = true;
        $this->errorMessage = '';
        $this->showResults = false;
        $this->suggestions = [];
        $this->selectedTimeSlots = [];
        $this->activitySaved = false;
        $this->weatherApiData = [];

        try {
            // Simulate API call delay for better UX
            sleep(1);

            $result = $this->weatherService->getWeatherSuggestions([
                'activity_name' => $this->activityName,
                'location' => $this->location,
                'region_code' => $this->selectedRegionCode,
                'preferred_date' => $this->preferredDate,
            ]);

            if ($result['success']) {
                $this->suggestions = $result['suggestions'];
                $this->weatherApiData = $result; // Store the full API response
                $this->showResults = true;
                // Dispatch event untuk scroll ke hasil
                $this->dispatch('scrollToResults');
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

    public function selectTimeSlot($dayIndex, $slotIndex)
    {
        $slotKey = "{$dayIndex}-{$slotIndex}";
        
        if (in_array($slotKey, $this->selectedTimeSlots)) {
            // Remove from selection
            $this->selectedTimeSlots = array_filter($this->selectedTimeSlots, function($slot) use ($slotKey) {
                return $slot !== $slotKey;
            });
        } else {
            // Add to selection
            $this->selectedTimeSlots[] = $slotKey;
        }
        
        $this->selectedTimeSlots = array_values($this->selectedTimeSlots); // Re-index array
    }

    public function saveActivity()
    {
        if (empty($this->selectedTimeSlots)) {
            $this->errorMessage = 'Silakan pilih minimal satu waktu yang optimal untuk aktivitas Anda.';
            return;
        }

        try {
            // Prepare selected time slots data
            $selectedSlotsData = [];
            foreach ($this->selectedTimeSlots as $slotKey) {
                [$dayIndex, $slotIndex] = explode('-', $slotKey);
                if (isset($this->suggestions[$dayIndex]['time_slots'][$slotIndex])) {
                    $slot = $this->suggestions[$dayIndex]['time_slots'][$slotIndex];
                    $selectedSlotsData[] = [
                        'date' => $this->suggestions[$dayIndex]['date'],
                        'day_name' => $this->suggestions[$dayIndex]['day_name'],
                        'time' => $slot['time'],
                        'period' => $slot['period'],
                        'weather_condition' => $slot['weather_condition'],
                        'temperature' => $slot['temperature'],
                        'humidity' => $slot['humidity'],
                        'recommendation' => $slot['recommendation']
                    ];
                }
            }

            // Save activity to database
            $activity = Activity::create([
                'name' => $this->activityName,
                'location' => $this->location,
                'region_code' => $this->selectedRegionCode,
                'preferred_date' => $this->preferredDate,
                'selected_time_slots' => $selectedSlotsData,
                'weather_data' => $this->weatherApiData,
                'status' => 'planned'
            ]);

            $this->activitySaved = true;
            $this->errorMessage = '';

            Log::info("Activity saved successfully", [
                'activity_id' => $activity->id,
                'name' => $this->activityName,
                'location' => $this->location,
                'region_code' => $this->selectedRegionCode,
                'selected_slots_count' => count($selectedSlotsData)
            ]);

            // Dispatch success event
            $this->dispatch('activitySaved', ['activity_id' => $activity->id]);

        } catch (\Exception $e) {
            $this->errorMessage = 'Terjadi kesalahan saat menyimpan aktivitas. Silakan coba lagi.';
            Log::error('Error saving activity: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset([
            'activityName', 
            'location', 
            'locationSearch', 
            'selectedRegionCode', 
            'suggestions', 
            'weatherApiData',
            'selectedTimeSlots',
            'activitySaved',
            'errorMessage', 
            'showResults', 
            'showLocationDropdown', 
            'searchLoading'
        ]);
        $this->preferredDate = now()->format('Y-m-d');
        $this->loadRegions();
    }

    public function isTimeSlotSelected($dayIndex, $slotIndex)
    {
        $slotKey = "{$dayIndex}-{$slotIndex}";
        return in_array($slotKey, $this->selectedTimeSlots);
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
        return view('livewire.activity-scheduler');
    }
}

<div>
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-8 sm:py-12 lg:py-16">
        <div class="container mx-auto px-2 sm:px-4 lg:px-8">
            <div class="text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-bold mb-2 sm:mb-4">
                    <i class="fas fa-calendar-alt mr-2 sm:mr-3"></i>
                    BMKG Activity Scheduler
                </h1>
                <p class="text-sm sm:text-lg lg:text-xl text-blue-100 px-4">Jadwalkan aktivitas outdoor Anda dengan prediksi cuaca terpercaya</p>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-2 sm:px-4 lg:px-8 -mt-4 sm:-mt-6 lg:-mt-8 relative z-10">
        <!-- Activity Form -->
        <div class="w-full max-w-sm mx-auto sm:max-w-lg lg:max-w-2xl xl:max-w-4xl">
            <div class="bg-white rounded-lg sm:rounded-xl shadow-lg sm:shadow-xl p-3 sm:p-6 lg:p-8 mx-2 sm:mx-0">
                <h3 class="text-base sm:text-xl lg:text-2xl font-bold text-center mb-3 sm:mb-6 lg:mb-8 text-gray-800">
                    <i class="fas fa-tasks text-blue-600 mr-1 sm:mr-3 text-sm sm:text-base"></i>
                    <span class="block sm:inline">Rencanakan Aktivitas Anda</span>
                </h3>

                <form wire:submit="searchOptimalTime" class="space-y-3 sm:space-y-6">
                    <div>
                        <label for="activityName" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                            <i class="fas fa-clipboard-list mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                            <span class="text-xs sm:text-sm">Nama Aktivitas</span>
                        </label>
                        <input type="text"
                               wire:model="activityName"
                               class="w-full px-2 py-2 sm:px-4 sm:py-3 border border-gray-300 rounded-md sm:rounded-lg focus:ring-1 sm:focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 placeholder-gray-400 text-xs sm:text-base @error('activityName') border-red-500 @enderror"
                               id="activityName"
                               placeholder="Contoh: Kunjungan lapangan, Survey lokasi">
                        @error('activityName')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="location" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                            <i class="fas fa-map-marker-alt mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                            <span class="text-xs sm:text-sm">Lokasi (Kecamatan/Desa)</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   wire:model.live.debounce.300ms="locationSearch"
                                   wire:focus="showLocationDropdown"
                                   wire:blur="hideLocationDropdown"
                                   class="location-input w-full px-2 py-2 sm:px-4 sm:py-3 border border-gray-300 rounded-md sm:rounded-lg focus:ring-1 sm:focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 placeholder-gray-400 text-xs sm:text-base @error('selectedRegionCode') border-red-500 @enderror"
                                   id="location"
                                   placeholder="Ketik untuk mencari lokasi..."
                                   autocomplete="off">

                            @if($showLocationDropdown && count($availableRegions) > 0)
                                <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg overflow-hidden">
                                    <!-- Header dengan info jumlah hasil -->
                                    <div class="bg-gray-50 px-3 py-2 text-xs text-gray-600 border-b border-gray-200 font-medium">
                                        <i class="fas fa-search mr-1"></i>
                                        Ditemukan {{ count($availableRegions) }} lokasi
                                        @if(count($availableRegions) >= 50)
                                            <span class="text-orange-600">(maksimal 50 ditampilkan)</span>
                                        @endif
                                    </div>

                                    <!-- Daftar regions -->
                                    <div class="dropdown-container max-h-64">
                                        @foreach($availableRegions as $index => $region)
                                            <div wire:click="selectRegion('{{ $region['code'] }}', '{{ $region['name'] }}', '{{ $region['full_path'] }}')"
                                                 class="region-item px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors duration-150 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">

                                                <!-- Region name -->
                                                <div class="font-medium text-sm text-gray-900">
                                                    <i class="fas fa-map-marker-alt text-blue-500 mr-2 text-xs"></i>
                                                    {{ $region['name'] }}
                                                </div>

                                                <!-- Full path -->
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $region['full_path'] }}
                                                </div>


                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Footer dengan tips -->
                                    @if(count($availableRegions) > 5)
                                        <div class="bg-gray-50 px-3 py-2 text-xs text-gray-500 border-t border-gray-200">
                                            <i class="fas fa-lightbulb mr-1 text-yellow-500"></i>
                                            Tip: Ketik lebih spesifik untuk mempersempit pencarian
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($searchLoading)
                                <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                    <div class="px-4 py-3 text-center">
                                        <div class="inline-flex items-center">
                                            <i class="fas fa-spinner fa-spin text-blue-500 mr-2"></i>
                                            <span class="text-sm text-gray-600">Mencari lokasi...</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($showLocationDropdown && count($availableRegions) === 0 && strlen($locationSearch) >= 2)
                                <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                    <div class="px-4 py-4 text-center">
                                        <div class="text-gray-400 mb-2">
                                            <i class="fas fa-search text-xl"></i>
                                        </div>
                                        <div class="text-sm text-gray-600 font-medium mb-1">
                                            Tidak ada lokasi yang ditemukan
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Coba gunakan kata kunci yang berbeda
                                        </div>
                                    </div>
                                </div>
                            @endif


                        </div>
                        @error('location')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @error('selectedRegionCode')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="preferredDate" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                            <i class="fas fa-calendar mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                            <span class="text-xs sm:text-sm">Tanggal Preferensi</span>
                        </label>
                        <input type="date"
                               wire:model="preferredDate"
                               class="w-full px-2 py-2 sm:px-4 sm:py-3 border border-gray-300 rounded-md sm:rounded-lg focus:ring-1 sm:focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 text-xs sm:text-base @error('preferredDate') border-red-500 @enderror"
                               id="preferredDate"
                               min="{{ date('Y-m-d') }}">
                        @error('preferredDate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 pt-2 sm:pt-4">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold px-4 py-2 sm:px-8 sm:py-3 rounded-md sm:rounded-lg transition duration-200 shadow-md sm:shadow-lg text-xs sm:text-base">
                            <span wire:loading.remove>
                                <i class="fas fa-search mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                Cari Waktu Optimal
                            </span>
                            <span wire:loading>
                                <i class="fas fa-spinner fa-spin mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                Menganalisis...
                            </span>
                        </button>

                        @if($showResults || $suggestions)
                            <button type="button"
                                    wire:click="resetForm"
                                    class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 sm:px-6 sm:py-3 rounded-md sm:rounded-lg transition duration-200 text-xs sm:text-base">
                                <i class="fas fa-redo mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                Reset
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Error Message -->
        @if($errorMessage)
            <div class="w-full max-w-sm mx-auto sm:max-w-lg lg:max-w-2xl mt-4 sm:mt-8 px-2 sm:px-0">
                <div class="bg-red-50 border border-red-200 rounded-md sm:rounded-lg p-2 sm:p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-500 text-xs sm:text-base"></i>
                        </div>
                        <div class="ml-2 sm:ml-3">
                            <p class="text-red-800 text-xs sm:text-base">
                                <strong>Terjadi Kesalahan:</strong>
                                {{ $errorMessage }}
                            </p>
                        </div>
                        <button wire:click="$set('errorMessage', '')" class="ml-auto">
                            <i class="fas fa-times text-red-500 hover:text-red-700 text-xs sm:text-base"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Results Section -->
        @if($showResults && count($suggestions) > 0)
            <div id="results-section" class="mt-6 sm:mt-12 px-2 sm:px-0" wire:transition>
                <h4 class="text-sm sm:text-xl lg:text-2xl font-bold text-center mb-3 sm:mb-6 lg:mb-8 text-gray-800">
                    <i class="fas fa-cloud-sun text-yellow-500 mr-1 sm:mr-3 text-sm sm:text-base"></i>
                    <span class="block sm:inline text-xs sm:text-base">Rekomendasi Waktu Aktivitas</span>
                </h4>

                <!-- Activity Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-md sm:rounded-lg p-2 sm:p-4 mb-3 sm:mb-6 mx-2 sm:mx-0">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-1 sm:mr-2 text-xs sm:text-base"></i>
                        <p class="text-blue-800 font-medium text-xs sm:text-sm lg:text-base">
                            <strong>Aktivitas:</strong> {{ $activityName }} di <strong>{{ $location }}</strong>
                        </p>
                    </div>
                </div>

                <!-- Weather Suggestions -->
                <div class="space-y-3 sm:space-y-6">
                    @foreach($suggestions as $day)
                        <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg border-l-4 border-green-500 mb-3 sm:mb-6 mx-2 sm:mx-0 overflow-hidden hover:shadow-lg sm:hover:shadow-xl transition-shadow duration-300">
                            <div class="bg-blue-600 text-white p-2 sm:p-4">
                                <h5 class="text-xs sm:text-lg font-semibold">
                                    <i class="fas fa-calendar-day mr-1 sm:mr-2 text-xs sm:text-base"></i>
                                    <span class="text-xs sm:text-base">{{ $day['day_name'] }}, {{ \Carbon\Carbon::parse($day['date'])->locale('id')->isoFormat('D MMMM Y') }}</span>
                                </h5>
                            </div>
                            <div class="p-2 sm:p-4 lg:p-6">
                                @if(count($day['time_slots']) > 0)
                                    @foreach($day['time_slots'] as $slot)
                                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-md sm:rounded-lg p-2 sm:p-3 mb-2 sm:mb-4 border-l-4 border-blue-500">
                                            <div class="space-y-1 sm:space-y-2 lg:space-y-0 lg:grid lg:grid-cols-3 lg:gap-4 lg:items-center">
                                                <div class="lg:col-span-1">
                                                    <p class="font-semibold text-blue-700 text-xs sm:text-sm lg:text-base">
                                                        <i class="fas fa-clock mr-1 text-xs sm:text-sm"></i>
                                                        <span class="text-xs sm:text-sm">{{ $slot['time'] }} ({{ $slot['period'] }})</span>
                                                    </p>
                                                </div>
                                                <div class="lg:col-span-1">
                                                    <span class="text-sm sm:text-base lg:text-xl mr-1 sm:mr-2">{{ $this->getWeatherIcon($slot['weather_condition']) }}</span>
                                                    <span class="text-gray-700 text-xs sm:text-sm lg:text-base">{{ ucfirst($slot['weather_condition']) }}</span>
                                                </div>
                                                <div class="flex flex-wrap gap-1 sm:gap-2 lg:gap-4 lg:col-span-1">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-thermometer-half text-red-500 mr-1 text-xs"></i>
                                                        <span class="text-gray-700 text-xs sm:text-sm lg:text-base">{{ $slot['temperature'] }}°C</span>
                                                    </span>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-tint text-blue-500 mr-1 text-xs"></i>
                                                        <span class="text-gray-700 text-xs sm:text-sm lg:text-base">{{ $slot['humidity'] }}%</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mt-1 sm:mt-2 lg:mt-3 pt-1 sm:pt-2 lg:pt-3 border-t border-gray-200">
                                                <p class="text-xs sm:text-sm text-gray-600">
                                                    <i class="fas fa-lightbulb text-yellow-500 mr-1 text-xs"></i>
                                                    <span class="text-xs sm:text-sm">{{ $slot['recommendation'] }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-gray-500 py-6 sm:py-8">
                                        <i class="fas fa-cloud-rain text-2xl sm:text-3xl lg:text-4xl mb-2 sm:mb-4 text-gray-400"></i>
                                        <p class="text-sm sm:text-base lg:text-lg">Tidak ada waktu yang optimal untuk hari ini</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($showResults && count($suggestions) === 0)
            <div id="results-section" class="mt-6 sm:mt-12 px-2 sm:px-0">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 sm:p-8 text-center mx-2 sm:mx-0">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl sm:text-3xl lg:text-4xl mb-2 sm:mb-4"></i>
                    <h5 class="text-lg sm:text-xl font-semibold text-yellow-800 mb-2">Tidak Ada Waktu Optimal</h5>
                    <p class="text-yellow-700 text-sm sm:text-base">Maaf, tidak ditemukan waktu yang optimal untuk aktivitas outdoor dalam 3 hari ke depan. Silakan coba tanggal lain.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-12 sm:mt-16 lg:mt-20 py-6 sm:py-8">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-gray-300 text-xs sm:text-sm lg:text-base">
                    <i class="fas fa-cloud mr-1 sm:mr-2"></i>
                    Data cuaca dari BMKG (Badan Meteorologi, Klimatologi, dan Geofisika)
                </p>
            </div>
        </div>
    </footer>

    <!-- Custom CSS for enhanced scrollable select box -->
    <style>
        /* Custom scrollbar styling */
        .scrollbar-thin::-webkit-scrollbar {
            width: 8px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Firefox scrollbar */
        .scrollbar-thin {
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }

        /* Better dropdown container */
        .dropdown-container {
            max-height: 320px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }

        .dropdown-container::-webkit-scrollbar {
            width: 8px;
        }

        .dropdown-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .dropdown-container::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 4px;
        }

        .dropdown-container::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }        /* Smooth scrolling for dropdown */
        .dropdown-smooth-scroll {
            scroll-behavior: smooth;
        }

        /* Enhanced hover effects */
        .region-item:hover {
            transform: translateX(2px);
            transition: all 0.15s ease-in-out;
        }

        /* Loading animation for search */
        .search-loading {
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        /* Enhanced focus ring for accessibility */
        .location-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Dropdown shadow enhancement */
        .dropdown-shadow {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('scrollToResults', () => {
                // Delay sedikit untuk memastikan DOM sudah ter-update
                setTimeout(() => {
                    const resultsSection = document.getElementById('results-section');
                    if (resultsSection) {
                        // Hitung offset untuk header yang mungkin fixed
                        const offset = 80; // Sesuaikan dengan tinggi header jika ada
                        const elementTop = resultsSection.getBoundingClientRect().top;
                        const offsetPosition = elementTop + window.pageYOffset - offset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                }, 100);
            });

            Livewire.on('hideDropdownDelayed', () => {
                setTimeout(() => {
                    Livewire.dispatch('hideDropdown');
                }, 200);
            });

            // Enhanced keyboard navigation for dropdown
            let selectedIndex = -1;

            document.addEventListener('keydown', function(e) {
                const dropdown = document.querySelector('.dropdown-container');
                const items = document.querySelectorAll('.region-item');

                if (!dropdown || items.length === 0) return;

                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                        updateSelection(items);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        selectedIndex = Math.max(selectedIndex - 1, -1);
                        updateSelection(items);
                        break;
                    case 'Enter':
                        if (selectedIndex >= 0 && items[selectedIndex]) {
                            e.preventDefault();
                            items[selectedIndex].click();
                        }
                        break;
                    case 'Escape':
                        selectedIndex = -1;
                        updateSelection(items);
                        break;
                }
            });

            function updateSelection(items) {
                items.forEach((item, index) => {
                    if (index === selectedIndex) {
                        item.classList.add('bg-blue-100', 'border-blue-300');
                        item.classList.remove('bg-white', 'bg-gray-50');
                        // Scroll into view
                        item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    } else {
                        item.classList.remove('bg-blue-100', 'border-blue-300');
                        if (index % 2 === 0) {
                            item.classList.add('bg-white');
                            item.classList.remove('bg-gray-50');
                        } else {
                            item.classList.add('bg-gray-50');
                            item.classList.remove('bg-white');
                        }
                    }
                });
            }
        });
    </script>
</div>

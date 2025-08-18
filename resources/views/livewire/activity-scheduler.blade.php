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
                        <input type="text"
                               wire:model="location"
                               class="w-full px-2 py-2 sm:px-4 sm:py-3 border border-gray-300 rounded-md sm:rounded-lg focus:ring-1 sm:focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 placeholder-gray-400 text-xs sm:text-base @error('location') border-red-500 @enderror"
                               id="location"
                               placeholder="Contoh: Jakarta Pusat, Bogor">
                        @error('location')
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
                                class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold px-4 py-2 sm:px-8 sm:py-3 rounded-md sm:rounded-lg transition duration-200 transform hover:scale-105 shadow-md sm:shadow-lg text-xs sm:text-base">
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
            <div class="mt-6 sm:mt-12 px-2 sm:px-0" wire:transition>
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
            <div class="mt-6 sm:mt-12 px-2 sm:px-0">
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
</div>

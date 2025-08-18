<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMKG Activity Scheduler</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        .loading-spinner {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-8 sm:py-12 lg:py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
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

                <form id="activityForm" class="space-y-3 sm:space-y-6">
                    <div>
                        <label for="activityName" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                            <i class="fas fa-clipboard-list mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                            <span class="text-xs sm:text-sm">Nama Aktivitas</span>
                        </label>
                        <input type="text"
                               class="w-full px-2 py-2 sm:px-4 sm:py-3 border border-gray-300 rounded-md sm:rounded-lg focus:ring-1 sm:focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 placeholder-gray-400 text-xs sm:text-base"
                               id="activityName"
                               name="activity_name"
                               placeholder="Contoh: Kunjungan lapangan, Survey lokasi"
                               required>
                    </div>

                    <div>
                        <label for="location" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                            <i class="fas fa-map-marker-alt mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                            <span class="text-xs sm:text-sm">Lokasi (Kecamatan/Desa)</span>
                        </label>
                        <input type="text"
                               class="w-full px-2 py-2 sm:px-4 sm:py-3 border border-gray-300 rounded-md sm:rounded-lg focus:ring-1 sm:focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 placeholder-gray-400 text-xs sm:text-base"
                               id="location"
                               name="location"
                               placeholder="Contoh: Jakarta Pusat, Bogor"
                               required>
                    </div>

                    <div>
                        <label for="preferredDate" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1 sm:mb-2">
                            <i class="fas fa-calendar mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                            <span class="text-xs sm:text-sm">Tanggal Preferensi</span>
                        </label>
                        <input type="date"
                               class="w-full px-2 py-2 sm:px-4 sm:py-3 border border-gray-300 rounded-md sm:rounded-lg focus:ring-1 sm:focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 text-xs sm:text-base"
                               id="preferredDate"
                               name="preferred_date"
                               min="{{ date('Y-m-d') }}"
                               required>
                    </div>

                    <div class="text-center pt-2 sm:pt-4">
                        <button type="submit"
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 sm:px-8 sm:py-3 rounded-md sm:rounded-lg transition duration-200 transform hover:scale-105 shadow-md sm:shadow-lg text-xs sm:text-base">
                            <i class="fas fa-search mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                            Cari Waktu Optimal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div class="text-center py-6 sm:py-12 loading-spinner" id="loadingSpinner">
            <div class="inline-block animate-spin rounded-full h-6 w-6 sm:h-12 sm:w-12 border-b-2 border-blue-600"></div>
            <p class="mt-2 sm:mt-4 text-xs sm:text-base text-gray-600 font-medium">Menganalisis data cuaca BMKG...</p>
        </div>

        <!-- Results Section -->
        <div class="mt-6 sm:mt-12 px-2 sm:px-0" id="resultsSection" style="display: none;">
            <h4 class="text-sm sm:text-xl lg:text-2xl font-bold text-center mb-3 sm:mb-6 lg:mb-8 text-gray-800">
                <i class="fas fa-cloud-sun text-yellow-500 mr-1 sm:mr-3 text-sm sm:text-base"></i>
                <span class="block sm:inline text-xs sm:text-base">Rekomendasi Waktu Aktivitas</span>
            </h4>
            <div id="weatherSuggestions" class="space-y-3 sm:space-y-6"></div>
        </div>

        <!-- Error Message -->
        <div class="w-full max-w-sm mx-auto sm:max-w-lg lg:max-w-2xl mt-4 sm:mt-8 px-2 sm:px-0" id="errorSection" style="display: none;">
            <div class="bg-red-50 border border-red-200 rounded-md sm:rounded-lg p-2 sm:p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xs sm:text-base"></i>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <p class="text-red-800 text-xs sm:text-base">
                            <strong>Terjadi Kesalahan:</strong>
                            <span id="errorMessage"></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
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

    <script>
        // Set minimum date to today
        document.getElementById('preferredDate').value = new Date().toISOString().split('T')[0];

        // Form submission handler
        document.getElementById('activityForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Show loading spinner
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('resultsSection').style.display = 'none';
            document.getElementById('errorSection').style.display = 'none';

            // Scroll to loading section
            document.getElementById('loadingSpinner').scrollIntoView({ behavior: 'smooth' });

            // Send request to backend
            fetch('/weather-suggestions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingSpinner').style.display = 'none';

                if (data.success) {
                    displayWeatherSuggestions(data);
                } else {
                    showError(data.message || 'Terjadi kesalahan saat memproses permintaan Anda.');
                }
            })
            .catch(error => {
                document.getElementById('loadingSpinner').style.display = 'none';
                showError('Terjadi kesalahan koneksi. Silakan coba lagi.');
                console.error('Error:', error);
            });
        });

        function displayWeatherSuggestions(data) {
            const container = document.getElementById('weatherSuggestions');
            let html = '';

            if (data.suggestions && data.suggestions.length > 0) {
                html += `
                    <div class="bg-blue-50 border border-blue-200 rounded-md sm:rounded-lg p-2 sm:p-4 mb-3 sm:mb-6 mx-2 sm:mx-0">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-1 sm:mr-2 text-xs sm:text-base"></i>
                            <p class="text-blue-800 font-medium text-xs sm:text-sm lg:text-base">
                                <strong>Aktivitas:</strong> ${data.activity_name} di <strong>${data.location}</strong>
                            </p>
                        </div>
                    </div>
                `;

                data.suggestions.forEach(day => {
                    html += `
                        <div class="card mb-3 sm:mb-6 mx-2 sm:mx-0">
                            <div class="bg-blue-600 text-white p-2 sm:p-4">
                                <h5 class="text-xs sm:text-lg font-semibold">
                                    <i class="fas fa-calendar-day mr-1 sm:mr-2 text-xs sm:text-base"></i>
                                    <span class="text-xs sm:text-base">${day.day_name}, ${formatDate(day.date)}</span>
                                </h5>
                            </div>
                            <div class="p-2 sm:p-4 lg:p-6">
                    `;

                    if (day.time_slots && day.time_slots.length > 0) {
                        day.time_slots.forEach(slot => {
                            const weatherIcon = getWeatherIcon(slot.weather_condition);
                            html += `
                                <div class="time-slot mb-2 sm:mb-4 p-2 sm:p-3">
                                    <div class="space-y-1 sm:space-y-2 lg:space-y-0 lg:grid lg:grid-cols-3 lg:gap-4 lg:items-center">
                                        <div class="lg:col-span-1">
                                            <p class="font-semibold text-blue-700 text-xs sm:text-sm lg:text-base">
                                                <i class="fas fa-clock mr-1 text-xs sm:text-sm"></i>
                                                <span class="text-xs sm:text-sm">${slot.time} (${slot.period})</span>
                                            </p>
                                        </div>
                                        <div class="lg:col-span-1">
                                            <span class="weather-icon text-sm sm:text-base lg:text-xl">${weatherIcon}</span>
                                            <span class="text-gray-700 text-xs sm:text-sm lg:text-base">${capitalizeFirst(slot.weather_condition)}</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1 sm:gap-2 lg:gap-4 lg:col-span-1">
                                            <span class="flex items-center">
                                                <i class="fas fa-thermometer-half text-red-500 mr-1 text-xs"></i>
                                                <span class="text-gray-700 text-xs sm:text-sm lg:text-base">${slot.temperature}°C</span>
                                            </span>
                                            <span class="flex items-center">
                                                <i class="fas fa-tint text-blue-500 mr-1 text-xs"></i>
                                                <span class="text-gray-700 text-xs sm:text-sm lg:text-base">${slot.humidity}%</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-1 sm:mt-2 lg:mt-3 pt-1 sm:pt-2 lg:pt-3 border-t border-gray-200">
                                        <p class="text-xs sm:text-sm text-gray-600">
                                            <i class="fas fa-lightbulb text-yellow-500 mr-1 text-xs"></i>
                                            <span class="text-xs sm:text-sm">${slot.recommendation}</span>
                                        </p>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html += `
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-cloud-rain text-4xl mb-4 text-gray-400"></i>
                                <p class="text-lg">Tidak ada waktu yang optimal untuk hari ini</p>
                            </div>
                        `;
                    }

                    html += `
                            </div>
                        </div>
                    `;
                });
            } else {
                html = `
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-8 text-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-4"></i>
                        <h5 class="text-xl font-semibold text-yellow-800 mb-2">Tidak Ada Waktu Optimal</h5>
                        <p class="text-yellow-700">Maaf, tidak ditemukan waktu yang optimal untuk aktivitas outdoor dalam 3 hari ke depan. Silakan coba tanggal lain.</p>
                    </div>
                `;
            }

            container.innerHTML = html;
            document.getElementById('resultsSection').style.display = 'block';
            document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
        }

        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorSection').style.display = 'block';
            document.getElementById('errorSection').scrollIntoView({ behavior: 'smooth' });
        }

        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        }

        function getWeatherIcon(condition) {
            const icons = {
                'cerah': '☀️',
                'berawan': '⛅',
                'berawan sebagian': '🌤️',
                'kabut': '🌫️',
                'hujan': '🌧️',
                'hujan ringan': '🌦️'
            };
            return icons[condition.toLowerCase()] || '🌤️';
        }

        function capitalizeFirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    </script>
</body>
</html>

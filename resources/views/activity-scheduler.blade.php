<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMKG Activity Scheduler</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .weather-card {
            transition: transform 0.2s ease-in-out;
            border-left: 4px solid #28a745;
        }
        
        .weather-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .time-slot {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 10px;
            margin: 5px 0;
            border-left: 3px solid #007bff;
        }
        
        .weather-icon {
            font-size: 1.2em;
            margin-right: 8px;
        }
        
        .loading-spinner {
            display: none;
        }
        
        .header-bg {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 2rem 0;
        }
        
        .form-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: -2rem;
            position: relative;
            z-index: 1;
        }
        
        .results-section {
            margin-top: 2rem;
        }
        
        .alert-custom {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <div class="header-bg">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="mb-2">
                        <i class="fas fa-calendar-alt me-2"></i>
                        BMKG Activity Scheduler
                    </h1>
                    <p class="lead mb-0">Jadwalkan aktivitas outdoor Anda dengan prediksi cuaca terpercaya</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Activity Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-section">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-tasks text-primary me-2"></i>
                        Rencanakan Aktivitas Anda
                    </h3>
                    
                    <form id="activityForm">
                        <div class="mb-3">
                            <label for="activityName" class="form-label">
                                <i class="fas fa-clipboard-list me-1"></i>
                                Nama Aktivitas
                            </label>
                            <input type="text" class="form-control" id="activityName" name="activity_name" 
                                   placeholder="Contoh: Kunjungan lapangan, Pemeliharaan alat, Survey lokasi" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                Lokasi (Kecamatan/Desa)
                            </label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   placeholder="Contoh: Jakarta Pusat, Bogor, Bandung" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="preferredDate" class="form-label">
                                <i class="fas fa-calendar me-1"></i>
                                Tanggal Preferensi
                            </label>
                            <input type="date" class="form-control" id="preferredDate" name="preferred_date" 
                                   min="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-search me-2"></i>
                                Cari Waktu Optimal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div class="row justify-content-center loading-spinner" id="loadingSpinner">
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Menganalisis data cuaca BMKG...</p>
            </div>
        </div>

        <!-- Results Section -->
        <div class="results-section" id="resultsSection" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-cloud-sun text-warning me-2"></i>
                        Rekomendasi Waktu Aktivitas
                    </h4>
                    <div id="weatherSuggestions"></div>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div class="row justify-content-center" id="errorSection" style="display: none;">
            <div class="col-lg-8">
                <div class="alert alert-danger alert-custom" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Terjadi Kesalahan:</strong>
                    <span id="errorMessage"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">
                        <i class="fas fa-cloud me-2"></i>
                        Data cuaca dari BMKG (Badan Meteorologi, Klimatologi, dan Geofisika)
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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
                    <div class="alert alert-info alert-custom mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Aktivitas:</strong> ${data.activity_name} di <strong>${data.location}</strong>
                    </div>
                `;
                
                data.suggestions.forEach(day => {
                    html += `
                        <div class="weather-card card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    ${day.day_name}, ${formatDate(day.date)}
                                </h5>
                            </div>
                            <div class="card-body">
                    `;
                    
                    if (day.time_slots && day.time_slots.length > 0) {
                        day.time_slots.forEach(slot => {
                            const weatherIcon = getWeatherIcon(slot.weather_condition);
                            html += `
                                <div class="time-slot">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <strong class="text-primary">
                                                <i class="fas fa-clock me-1"></i>
                                                ${slot.time} (${slot.period})
                                            </strong>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="weather-icon">${weatherIcon}</span>
                                            ${capitalizeFirst(slot.weather_condition)}
                                        </div>
                                        <div class="col-md-3">
                                            <i class="fas fa-thermometer-half text-danger me-1"></i>
                                            ${slot.temperature}°C
                                            <span class="ms-2">
                                                <i class="fas fa-tint text-info me-1"></i>
                                                ${slot.humidity}%
                                            </span>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-lightbulb me-1"></i>
                                                ${slot.recommendation}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html += `
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-cloud-rain fa-2x mb-2"></i>
                                <p>Tidak ada waktu yang optimal untuk hari ini</p>
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
                    <div class="alert alert-warning alert-custom text-center">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <h5>Tidak Ada Waktu Optimal</h5>
                        <p class="mb-0">Maaf, tidak ditemukan waktu yang optimal untuk aktivitas outdoor dalam 3 hari ke depan. Silakan coba tanggal lain.</p>
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

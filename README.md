
# BMKG Activity Scheduler

BMKG Activity Scheduler adalah aplikasi web untuk penjadwalan aktivitas dan integrasi data cuaca dari BMKG (Badan Meteorologi, Klimatologi, dan Geofisika Indonesia).

## Teknologi yang Digunakan

- **Laravel**: Framework PHP untuk backend dan logika aplikasi
- **Livewire**: Paket Laravel untuk membangun UI dinamis dengan Blade dan PHP
- **Vite**: Build tool modern untuk frontend dan asset bundling
- **MySQL**: Database ringan untuk pengembangan lokal
- **PHPUnit**: Framework testing untuk PHP
- **BMKG API**: Integrasi data cuaca

## Prasyarat

- PHP >= 8.1
- Composer
- Node.js & npm

## Instalasi & Menjalankan Secara Lokal

1. **Clone repository**
	```bash
	git clone https://github.com/SulthanRabbani/bmkg-activity-scheduler.git
	cd bmkg-activity-scheduler
	```

2. **Install dependensi PHP**
	```bash
	composer install
	```

3. **Install dependensi Node.js**
	```bash
	npm install
	```

4. **Copy file environment dan konfigurasi**
	```bash
	cp .env.example .env
	# Edit .env sesuai kebutuhan (database, API key BMKG, dll)
	```

5. **Generate application key**
	```bash
	php artisan key:generate
	```

6. **Jalankan migrasi dan seeder**
	```bash
	php artisan migrate --seed
	```

7. **Build asset frontend**
	```bash
	npm run build
	```

8. **Jalankan server pengembangan**
	```bash
	php artisan serve
	```
	Aplikasi akan tersedia di http://localhost:8000

## Menjalankan Test

```bash
php artisan test
```

## Kontribusi

Silakan fork, buka issue, atau submit pull request.

## Lisensi

Proyek ini menggunakan lisensi MIT.

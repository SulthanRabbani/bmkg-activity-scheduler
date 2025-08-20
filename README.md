
# BMKG Activity Scheduler

BMKG Activity Scheduler adalah aplikasi web untuk penjadwalan aktivitas dan integrasi data cuaca dari BMKG (Badan Meteorologi, Klimatologi, dan Geofisika Indonesia).

## Teknologi yang Digunakan

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/Livewire-4E56A6?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire" />
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/PHPUnit-366488?style=for-the-badge&logo=php&logoColor=white" alt="PHPUnit" />
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
</p>

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

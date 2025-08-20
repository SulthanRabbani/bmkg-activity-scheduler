
# BMKG Activity Scheduler

BMKG Activity Scheduler is a web application for activity scheduling and weather data integration from BMKG (Indonesia's Meteorology, Climatology, and Geophysical Agency).

## Tech Stack

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/Livewire-4E56A6?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire" />
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/PHPUnit-366488?style=for-the-badge&logo=php&logoColor=white" alt="PHPUnit" />
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
</p>

## Prerequisites

- PHP >= 8.1
- Composer
- Node.js & npm

## Installation & Running Locally

1. **Clone the repository**
	```bash
	git clone https://github.com/SulthanRabbani/bmkg-activity-scheduler.git
	cd bmkg-activity-scheduler
	```

2. **Install PHP dependencies**
	```bash
	composer install
	```

3. **Install Node.js dependencies**
	```bash
	npm install
	```

4. **Copy environment file and configure**
	```bash
	cp .env.example .env
	# Edit .env as needed (database, BMKG API key, etc)
	```

5. **Generate application key**
	```bash
	php artisan key:generate
	```

6. **Run migrations and seeders**
	```bash
	php artisan migrate --seed
	```

7. **Build frontend assets**
	```bash
	npm run build
	```

8. **Start the development server**
	```bash
	php artisan serve
	```
	The app will be available at http://localhost:8000

## Running Tests

```bash
php artisan test
```

## Contributing

Feel free to fork, open issues, or submit pull requests.

## License

This project is licensed under the MIT license.

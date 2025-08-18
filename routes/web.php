<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivitySchedulerController;

Route::get('/', [ActivitySchedulerController::class, 'index']);
Route::post('/weather-suggestions', [ActivitySchedulerController::class, 'getWeatherSuggestions']);

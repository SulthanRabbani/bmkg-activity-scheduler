<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\ActivityScheduler::class)->name('home');

Route::get('/activity-scheduler', \App\Livewire\ActivityScheduler::class)->name('activity-scheduler');

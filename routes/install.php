<?php

use App\Install\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

// Route::get('/', [InstallController::class, 'index'])
//    ->name('install.index');

Route::livewire('/', 'pages::install.index')
    ->name('install.index');

Route::post('/', [InstallController::class, 'store'])
    ->name('install.store');

Route::fallback(fn () => redirect()->route('install.index'));

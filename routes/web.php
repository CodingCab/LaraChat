<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('terminal', function () {
    return Inertia::render('Terminal');
})->middleware(['auth', 'verified'])->name('terminal');

Route::get('claude', function () {
    return Inertia::render('Claude');
})->middleware(['auth', 'verified'])->name('claude');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

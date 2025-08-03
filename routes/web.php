<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('claude', function () {
    return Inertia::render('Claude');
})->middleware(['auth', 'verified'])->name('claude');

Route::get('claude/{session}', function ($session) {
    return Inertia::render('Claude', ['sessionFile' => $session]);
})->middleware(['auth', 'verified'])->name('claude.session');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

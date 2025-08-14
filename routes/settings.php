<?php

use App\Http\Controllers\Settings\JobsController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SystemMessageController;
use App\Http\Controllers\SystemUpdateController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    Route::get('settings/system-update', function () {
        return Inertia::render('settings/SystemUpdate');
    })->name('settings.system-update');

    Route::post('settings/system-update', [SystemUpdateController::class, 'update'])
        ->name('settings.system-update');

    Route::get('settings/jobs', [JobsController::class, 'index'])->name('settings.jobs');
    Route::get('settings/jobs/status', [JobsController::class, 'status'])->name('settings.jobs.status');
    Route::post('settings/jobs/control', [JobsController::class, 'control'])->name('settings.jobs.control');
    Route::post('settings/jobs/retry/{id}', [JobsController::class, 'retry'])->name('settings.jobs.retry');
    Route::delete('settings/jobs/discard/{id}', [JobsController::class, 'discard'])->name('settings.jobs.discard');

    Route::get('settings/system-messages', [SystemMessageController::class, 'index'])->name('settings.system-messages');
    Route::post('settings/system-messages', [SystemMessageController::class, 'store'])->name('settings.system-messages.store');
    Route::put('settings/system-messages/{systemMessage}', [SystemMessageController::class, 'update'])->name('settings.system-messages.update');
    Route::delete('settings/system-messages/{systemMessage}', [SystemMessageController::class, 'destroy'])->name('settings.system-messages.destroy');
    Route::post('settings/system-messages/{systemMessage}/toggle', [SystemMessageController::class, 'toggle'])->name('settings.system-messages.toggle');
});

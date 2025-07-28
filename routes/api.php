<?php

use App\Http\Controllers\CommandController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/run-command', [CommandController::class, 'runCommand']);
});
<?php

use App\Http\Controllers\CommandController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/run-command', [CommandController::class, 'runCommand']);
    Route::post('/command/execute', [CommandController::class, 'runCommand']);
    Route::post('/command/stream', [CommandController::class, 'streamCommand']);
    Route::get('/command/available', [CommandController::class, 'getAvailableCommands']);
    Route::get('/command/history', [CommandController::class, 'getCommandHistory']);
});
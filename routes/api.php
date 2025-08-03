<?php

use App\Http\Controllers\ClaudeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/claude', [ClaudeController::class, 'store']);
    Route::post('/claude/save-response', [ClaudeController::class, 'saveResponse']);
    Route::get('/claude/sessions', [ClaudeController::class, 'getSessions']);
    Route::get('/claude/sessions/{filename}', [ClaudeController::class, 'getSessionMessages']);
    Route::get('/claude/debug/{filename}', [ClaudeController::class, 'debugSession']);
});

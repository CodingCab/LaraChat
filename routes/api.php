<?php

use App\Http\Controllers\ClaudeController;
use App\Http\Controllers\GitHubWebhookController;
use App\Http\Controllers\RepositoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/claude', [ClaudeController::class, 'store']);
    Route::post('/claude/save-response', [ClaudeController::class, 'saveResponse']);
    Route::get('/claude/sessions', [ClaudeController::class, 'getSessions']);
    Route::get('/claude/sessions/{filename}', [ClaudeController::class, 'getSessionMessages']);
    Route::get('/claude/debug/{filename}', [ClaudeController::class, 'debugSession']);

    Route::get('/repositories', [RepositoryController::class, 'index']);
    Route::post('/repositories', [RepositoryController::class, 'store']);
    Route::delete('/repositories/{repository}', [RepositoryController::class, 'destroy']);
    Route::post('/repositories/{repository}/pull', [RepositoryController::class, 'pull']);
});

Route::post('/github/webhook', [GitHubWebhookController::class, 'handle']);
Route::get('/github/webhook', [GitHubWebhookController::class, 'handle']);

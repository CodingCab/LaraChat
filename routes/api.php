<?php

use App\Http\Controllers\ClaudeController;
use App\Http\Controllers\ConversationsController;
use App\Http\Controllers\GitHubWebhookController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\RepositoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/claude', [ClaudeController::class, 'store']);
//    Route::get('/claude/sessions', [ClaudeController::class, 'getSessions']);
    Route::get('/claude/sessions/{filename}', [ClaudeController::class, 'getSessionMessages']);

    Route::get('/repositories', [RepositoryController::class, 'index']);
    Route::post('/repositories', [RepositoryController::class, 'store']);
    Route::delete('/repositories/{repository}', [RepositoryController::class, 'destroy']);
    Route::post('/repositories/{repository}/pull', [RepositoryController::class, 'pull']);
    Route::post('/repositories/{repository}/copy-to-hot', [RepositoryController::class, 'copyToHot']);

    Route::get('/conversations', [ConversationsController::class, 'index']);
    Route::get('/claude/conversations', [ConversationsController::class, 'index']); // TODO: deprecated, replace usage with /conversations

    // Messages API
    Route::get('/conversations/{conversation}/messages', [MessagesController::class, 'index']);
    Route::post('/conversations/{conversation}/messages', [MessagesController::class, 'store']);
});

Route::post('/github/webhook', [GitHubWebhookController::class, 'handle']);
Route::get('/github/webhook', [GitHubWebhookController::class, 'handle']);

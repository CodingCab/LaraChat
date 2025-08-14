<?php

use App\Http\Controllers\ClaudeController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\ConversationsController;
use App\Http\Controllers\GitHubWebhookController;
use App\Http\Controllers\RepositoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/run-command', [CommandController::class, 'run']);
    Route::post('/claude', [ClaudeController::class, 'store']);
//    Route::get('/claude/sessions', [ClaudeController::class, 'getSessions']);
    Route::get('/claude/sessions/{filename}', [ClaudeController::class, 'getSessionMessages'])->where('filename', '.*');

    Route::get('/repositories', [RepositoryController::class, 'index']);
    Route::post('/repositories', [RepositoryController::class, 'store']);
    Route::delete('/repositories/{repository}', [RepositoryController::class, 'destroy']);
    Route::post('/repositories/{repository}/pull', [RepositoryController::class, 'pull']);
    Route::post('/repositories/{repository}/copy-to-hot', [RepositoryController::class, 'copyToHot']);

    Route::get('/conversations', [ConversationsController::class, 'index']);
    Route::post('/conversations', [ConversationsController::class, 'store']);
    Route::get('/conversations/archived', [ConversationsController::class, 'archived']);
    Route::post('/conversations/{conversation}/archive', [ConversationsController::class, 'archive']);
    Route::post('/conversations/{conversation}/unarchive', [ConversationsController::class, 'unarchive']);

    Route::get('/claude/conversations', [ConversationsController::class, 'index']); // TODO: deprecated, replace usage with /conversations
});

Route::post('/github/webhook', [GitHubWebhookController::class, 'handle']);
Route::get('/github/webhook', [GitHubWebhookController::class, 'handle']);

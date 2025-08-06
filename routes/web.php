<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');



Route::get('claude', function () {
    return Inertia::render('Claude', [
        'repository' => request()->query('repository')
    ]);
})->middleware(['auth', 'verified'])->name('claude');

Route::get('claude/{session}', function ($session) {
    return Inertia::render('Claude', [
        'sessionFile' => $session,
        'repository' => request()->query('repository')
    ]);
})->middleware(['auth', 'verified'])->name('claude.session');

Route::get('claude/conversation/{conversation}', function ($conversation) {
    $conv = \App\Models\Conversation::findOrFail($conversation);
    return Inertia::render('Claude', [
        'conversationId' => $conv->id,
        'repository' => $conv->repository,
        'sessionId' => $conv->claude_session_id,
        'sessionFile' => $conv->filename
    ]);
})->middleware(['auth', 'verified'])->name('claude.conversation');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

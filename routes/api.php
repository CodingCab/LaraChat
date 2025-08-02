<?php

use App\Http\Controllers\ClaudeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/claude', [ClaudeController::class, 'store']);
});

<?php

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Here is where you can register PUBLIC web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "public" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ManifestController;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('manifest.json', [ManifestController::class, 'index'])->name('manifest.json');

Route::view('help', 'help');

// you can register only first user then he should invite others
try {
    $exists = User::query()->exists();

    if (User::query()->exists()) {
        Auth::routes(['register' => false]);
        Route::redirect('register', 'login');

        return;
    }

    Auth::routes(['register' => true]);
} catch (\Exception $exception) {
    Auth::routes(['register' => false]);
}

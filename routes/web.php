<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// No need for a redirect anymore since Filament is at the root path

// Remove any other default Laravel routes that you don't need
// If you want to keep these routes, make sure they don't conflict with Filament
// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Route::view('profile', 'profile')
//     ->middleware(['auth'])
//     ->name('profile');

// require __DIR__.'/auth.php';

// Remove cache management route
// Route::get('/admin/refresh-cache', [App\Http\Controllers\CacheController::class, 'refresh'])
//     ->middleware(['auth'])
//     ->name('backoffice.refresh-cache');

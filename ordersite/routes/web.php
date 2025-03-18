<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Store\DashboardController as StoreDashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

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

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Store Routes
Route::middleware('role:store')->prefix('store')->name('store.')->group(function () {
    Route::get('/dashboard', [StoreDashboardController::class, 'index'])->name('dashboard');
    Route::post('/order', [StoreDashboardController::class, 'storeOrder'])->name('order.store');
});

// Admin Routes
Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/schedule/{id}', [AdminDashboardController::class, 'showSchedule'])->name('schedule');
    Route::post('/schedule', [AdminDashboardController::class, 'storeSchedule'])->name('schedule.store');
    Route::put('/schedule/{id}', [AdminDashboardController::class, 'updateSchedule'])->name('schedule.update');
    Route::get('/schedule/{id}/export', [AdminDashboardController::class, 'exportSchedule'])->name('schedule.export');
}); 
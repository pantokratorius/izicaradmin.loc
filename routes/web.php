<?php

use App\Http\Controllers\CarController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\SettingController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::redirect('/dashboard', '/clients');
Route::middleware('auth')->group(function () {




Route::resource('clients', ClientController::class);
Route::resource('vehicles', VehicleController::class);
Route::resource('orders', OrderController::class);
Route::resource('orderitems', OrderItemController::class);

Route::get('/cars', [CarController::class, 'index'])->name('cars.index');

Route::get('/cars/models/{brand}', [CarController::class, 'models']);
Route::get('/cars/generations/{model}', [CarController::class, 'generations']);
Route::get('/cars/series/{generation}', [CarController::class, 'series']);
Route::get('/cars/modifications/{serie}', [CarController::class, 'modifications']);
Route::get('/cars/characteristics/{modification}', [CarController::class, 'characteristics']);

Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

Route::post('/set-session', [ClientController::class, 'setSessionAjax'])->name('set.session');



    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

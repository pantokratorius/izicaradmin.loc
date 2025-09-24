<?php

use App\Http\Controllers\SearchController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PartSearchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::redirect('/dashboard', '/clients');
Route::middleware('auth')->group(function () {



Route::get('/orders/{order}/print', [OrderController::class, 'print'])
    ->name('orders.print');

Route::get('/orders/{order}/print2', [OrderController::class, 'print2'])
    ->name('orders.print2');


Route::resource('clients', ClientController::class)->except(['show']);
Route::get('/clients/{client}/vehicles', [ClientController::class, 'vehicles']);
Route::resource('vehicles', VehicleController::class)->except(['show']);
Route::resource('orders', OrderController::class);
Route::resource('orderitems', OrderItemController::class);

Route::get('/cars', [CarController::class, 'index'])->name('cars.index');

Route::get('/cars/models/{brand}', [CarController::class, 'models']);
Route::get('/cars/generations/{model}', [CarController::class, 'generations']);
Route::get('/cars/series/{generation}', [CarController::class, 'series']);
Route::get('/cars/modifications/{serie}', [CarController::class, 'modifications']);

Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data');


Route::post('/set-session', [ClientController::class, 'setSessionAjax'])->name('set.session');

Route::get('/api/supplier-search-stream', [SupplierController::class, 'searchStream']);



Route::get('/api/supplier1', [PartSearchController::class, 'supplier1']);
Route::get('/api/supplier2', [PartSearchController::class, 'supplier2']);



Route::resource('stocks', StockController::class);




    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

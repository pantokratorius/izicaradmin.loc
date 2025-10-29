<?php

use App\Http\Controllers\BrandGroupController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\PartSearchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TempPartsController;
use App\Models\TempParts;

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

Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');


Route::resource('clients', ClientController::class)->except(['show']);
Route::get('/clients/{client}/vehicles', [ClientController::class, 'vehicles']);

Route::get('/vehicles/search', [VehicleController::class, 'search'])->name('vehicles.search');
Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');


Route::resource('vehicles', VehicleController::class)->except(['show']);
Route::resource('orders', OrderController::class);
Route::resource('orderitems', OrderItemController::class);
Route::get('/orderitems/create/{order}', [OrderItemController::class, 'create'])->name('orderitems.create');
Route::get('/orders/{order}/copy', [OrderController::class, 'copy'])->name('orders.copy');


Route::get('/cars', [CarController::class, 'index'])->name('cars.index');

Route::get('/cars/models/{brand}', [CarController::class, 'models']);
Route::get('/cars/generations/{model}', [CarController::class, 'generations']);
Route::get('/cars/series/{generation}', [CarController::class, 'series']);
Route::get('/cars/modifications/{serie}', [CarController::class, 'modifications']);

Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
Route::post('/settings/update-percent', [SettingController::class, 'updatePercent'])
    ->name('settings.updatePercent');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data');


Route::post('/set-session', [ClientController::class, 'setSessionAjax'])->name('set.session');

Route::get('/api/supplier-search-stream', [SupplierController::class, 'searchStream']);
Route::get('/api/supplier-parts', [SupplierController::class, 'getParts']);



Route::get('/api/brands', [PartController::class, 'streamBrands']);

Route::get('/api/items', [PartController::class, 'streamItems']);

Route::post('/parts/import', [PartController::class, 'import'])->name('parts.import');

Route::resource('brand-groups', BrandGroupController::class)->except(['show', 'create', 'edit']);
Route::post('/brand-groups/update-ajax', [BrandGroupController::class, 'updateAjax'])
     ->name('brand-groups.update-ajax');

Route::resource('stocks', StockController::class);

Route::post('/stocks/store_ajax', [StockController::class, 'store_ajax'])->name('store_ajax');

Route::post('/orderitems/orderitem_store_ajax', [OrderItemController::class, 'store_ajax'])->name('orderitem_store_ajax');
Route::post('/orderitem/{id}/status', [OrderItemController::class, 'updateStatus'])->name('orderitems.updateStatus');

Route::post('/', [TempPartsController::class, 'store'])->name('temp_parts.store');
Route::post('/{part}/move-to-stock', [TempPartsController::class, 'moveToStock'])->name('temp_parts.moveToStock');
Route::post('/{part}/move-to-order', [TempPartsController::class, 'moveToOrder'])->name('temp_parts.moveToOrder');
Route::delete('/{part}', [TempPartsController::class, 'destroy'])->name('temp_parts.destroy');


Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

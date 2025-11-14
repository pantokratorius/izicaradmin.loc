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

Route::post('/orders/copy-to-new', [OrderController::class, 'copyToNew'])->name('orders.copyToNew');
Route::post('/orders/copy-to-existing/{order_number}', [OrderController::class, 'copyToExisting'])
     ->name('orders.copyToExisting');
Route::post('/orders/copy-to-new2', [OrderController::class, 'copyToNew2'])->name('orders.copyToNew2');
Route::post('/orders/copy-to-existing2/{order_number}', [OrderController::class, 'copyToExisting2'])
     ->name('orders.copyToExisting2');

Route::get('/orders/{order}/print', [OrderController::class, 'print'])
    ->name('orders.print');

Route::get('/orders/{order}/print2', [OrderController::class, 'print2'])
    ->name('orders.print2');

Route::get('/searches/{search}/print', [SearchController::class, 'print'])
    ->name('search.print');

Route::get('/searches/{search}/print2', [SearchController::class, 'print2'])
    ->name('search.print2');

Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');


Route::resource('clients', ClientController::class)->except(['show']);
Route::get('/clients/{client}/vehicles', [ClientController::class, 'vehicles']);

Route::get('/vehicles/search', [VehicleController::class, 'search'])->name('vehicles.search');
Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');

Route::get('/clients/list', [ClientController::class, 'list']);
Route::get('/vehicles/by-client/{client}', [VehicleController::class, 'getByClient']);


Route::resource('vehicles', VehicleController::class)->except(['show']);
Route::resource('orders', OrderController::class);
Route::resource('orderitems', OrderItemController::class);
Route::get('/orderitems/search', [OrderItemController::class, 'search'])
    ->name('orderitems.search');
Route::get('/orderitems/create/{order}', [OrderItemController::class, 'create'])->name('orderitems.create');
Route::post('/orderitems/batch-delete', [OrderItemController::class, 'batchDelete']);
    


Route::get('/orders/{order}/copy', [OrderController::class, 'copy'])->name('orders.copy');


Route::get('/cars', [CarController::class, 'index'])->name('cars.index');

Route::get('/cars/models/{brand}', [CarController::class, 'models']);
Route::get('/cars/generations/{model}', [CarController::class, 'generations']);
Route::get('/cars/series/{generation}', [CarController::class, 'series']);
Route::get('/cars/modifications/{serie}', [CarController::class, 'modifications']);

Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');
Route::post('/settings/update-suppliers', [SettingController::class, 'updateSuppliers'])->name('settings.updateSuppliers');
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
Route::get('/search/show', [SearchController::class, 'show'])->name('search.show');
Route::get('search/{search}/edit', [SearchController::class, 'edit'])->name('search.edit');
Route::put('/search/{search}', [SearchController::class, 'update'])->name('search.update');
Route::get('/search/store', [SearchController::class, 'store'])->name('search.store');
Route::delete('/search/clear', [SearchController::class, 'clear'])->name('search.clear');
Route::delete('/search/destroy/{id}', [SearchController::class, 'destroy'])->name('search.destroy');
Route::post('/search/store_ajax', [SearchController::class, 'store_ajax'])->name('search_store_ajax');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

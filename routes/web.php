<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesSessionController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseStockController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware('can:categories.manage')->prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::patch('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
    });

    Route::middleware('can:brands.manage')->prefix('brands')->name('brands.')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('index');
        Route::post('/', [BrandController::class, 'store'])->name('store');
        Route::put('/{brand}', [BrandController::class, 'update'])->name('update');
        Route::patch('/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('toggle-status');
    });

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index')->middleware('can:products.view');
        Route::get('/create', [ProductController::class, 'create'])->name('create')->middleware('can:products.create');
        Route::post('/', [ProductController::class, 'store'])->name('store')->middleware('can:products.create');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit')->middleware('can:products.update');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update')->middleware('can:products.update');
        Route::patch('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggle-status')->middleware('can:products.deactivate');
        Route::post('/{product}/units', [ProductUnitController::class, 'store'])->name('units.store')->middleware('can:products.update');
        Route::patch('/{product}/units/{unit}/toggle-status', [ProductUnitController::class, 'toggleStatus'])->name('units.toggle-status')->middleware('can:products.update');
    });

    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index')->middleware('can:warehouses.view');
        Route::get('/create', [WarehouseController::class, 'create'])->name('create')->middleware('can:warehouses.create');
        Route::post('/', [WarehouseController::class, 'store'])->name('store')->middleware('can:warehouses.create');
        Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit')->middleware('can:warehouses.update');
        Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update')->middleware('can:warehouses.update');
        Route::patch('/{warehouse}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('toggle-status')->middleware('can:warehouses.deactivate');
    });

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index')->middleware('can:customers.view');
        Route::get('/create', [CustomerController::class, 'create'])->name('create')->middleware('can:customers.create');
        Route::post('/', [CustomerController::class, 'store'])->name('store')->middleware('can:customers.create');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit')->middleware('can:customers.update');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update')->middleware('can:customers.update');
        Route::patch('/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('toggle-status')->middleware('can:customers.deactivate');
    });

    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index')->middleware('can:suppliers.view');
        Route::get('/create', [SupplierController::class, 'create'])->name('create')->middleware('can:suppliers.create');
        Route::post('/', [SupplierController::class, 'store'])->name('store')->middleware('can:suppliers.create');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit')->middleware('can:suppliers.update');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update')->middleware('can:suppliers.update');
        Route::patch('/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('toggle-status')->middleware('can:suppliers.deactivate');
    });

    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index')->middleware('can:purchases.view');
        Route::get('/create', [PurchaseController::class, 'create'])->name('create')->middleware('can:purchases.create');
        Route::post('/', [PurchaseController::class, 'store'])->name('store')->middleware('can:purchases.create');
        Route::get('/receive', [PurchaseController::class, 'receiveIndex'])->name('receive.index')->middleware('can:purchases.receive');
        Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('show')->middleware('can:purchases.view');
        Route::post('/{purchase}/confirm', [PurchaseController::class, 'confirm'])->name('confirm')->middleware('can:purchases.confirm');
        Route::post('/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('cancel')->middleware('can:purchases.confirm');
        Route::get('/{purchase}/receive', [PurchaseController::class, 'receiveForm'])->name('receive.form')->middleware('can:purchases.receive');
        Route::post('/{purchase}/receive', [PurchaseController::class, 'receive'])->name('receive')->middleware('can:purchases.receive');
    });

    Route::get('/pos', [SaleController::class, 'pos'])->name('sales.pos')->middleware('can:sales.create');

    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index')->middleware('can:sales.browse');
        Route::post('/', [SaleController::class, 'store'])->name('store')->middleware('can:sales.create');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show')->middleware('can:sales.browse');
    });

    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [ReturnController::class, 'index'])->name('index')->middleware('can:returns.browse');
        Route::get('/create/{sale}', [ReturnController::class, 'create'])->name('create')->middleware('can:returns.create');
        Route::post('/', [ReturnController::class, 'store'])->name('store')->middleware('can:returns.create');
        Route::get('/{return}', [ReturnController::class, 'show'])->name('show')->middleware('can:returns.browse');
    });

    Route::prefix('sales-sessions')->name('sales-sessions.')->group(function () {
        Route::get('/', [SalesSessionController::class, 'index'])->name('index')->middleware('can:sessions.browse');
        Route::post('/', [SalesSessionController::class, 'store'])->name('store')->middleware('can:sessions.start');
        Route::get('/{session}', [SalesSessionController::class, 'show'])->name('show')->middleware('can:sessions.browse');
        Route::post('/{session}/close', [SalesSessionController::class, 'close'])->name('close')->middleware('can:sessions.close');
    });

    Route::prefix('stock-adjustments')->name('stock-adjustments.')->group(function () {
        Route::get('/', [StockAdjustmentController::class, 'index'])->name('index')->middleware('can:inventory.view');
        Route::get('/create', [StockAdjustmentController::class, 'create'])->name('create')->middleware('can:inventory.adjust');
        Route::post('/', [StockAdjustmentController::class, 'store'])->name('store')->middleware('can:inventory.adjust');
        Route::get('/{adjustment}', [StockAdjustmentController::class, 'show'])->name('show')->middleware('can:inventory.view');
    });

    Route::prefix('stock-transfers')->name('stock-transfers.')->group(function () {
        Route::get('/', [StockTransferController::class, 'index'])->name('index')->middleware('can:inventory.view');
        Route::get('/create', [StockTransferController::class, 'create'])->name('create')->middleware('can:inventory.transfer');
        Route::post('/', [StockTransferController::class, 'store'])->name('store')->middleware('can:inventory.transfer');
        Route::get('/{transfer}', [StockTransferController::class, 'show'])->name('show')->middleware('can:inventory.view');
    });

    Route::get('/warehouse-stocks', [WarehouseStockController::class, 'index'])->name('warehouse-stocks.index')->middleware('can:inventory.view');
    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index')->middleware('can:stock_movements.view');
});

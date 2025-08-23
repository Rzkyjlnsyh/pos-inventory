<?php

use App\Http\Controllers\Owner\PurchaseReturnController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Owner\UserOwnerController;
use App\Http\Controllers\Owner\ProductOwnerController;
use App\Http\Controllers\Owner\ProfileOwnerController;
use App\Http\Controllers\Owner\NotificationOwnerController;
use App\Http\Controllers\Owner\ContactController;
use App\Http\Controllers\Owner\CategoryController;

// Base and auth routes
Route::get('/', fn() => view('welcome'));

Route::get('/dashboard', fn() => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Owner routes, protected by 'auth' and 'owner' middleware
Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.')->group(function () {
    // Dashboard
    Route::view('/', 'owner.dashboard')->name('index');
    Route::get('dashboard', fn() => view('owner.dashboard'))->name('dashboard');

    // Products
    Route::resource('product', ProductOwnerController::class);
    Route::get('catalog/products/search', [ProductOwnerController::class, 'search'])->name('catalog.products.search');
    Route::get('product/import', [ProductOwnerController::class, 'importForm'])->name('product.import.form');
    Route::post('product/import', [ProductOwnerController::class, 'import'])->name('product.import');
    Route::get('product/download-template', [ProductOwnerController::class, 'downloadTemplate'])->name('product.download-template');

    // Categories
    Route::resource('categories', CategoryController::class)
        ->names([
            'index' => 'category.index',
            'create' => 'category.create',
            'store' => 'category.store',
            'edit' => 'category.edit',
            'update' => 'category.update',
            'destroy' => 'category.destroy',
        ])->except(['show']);
    Route::get('category/import', [CategoryController::class, 'importForm'])->name('category.import');
    Route::post('category/import', [CategoryController::class, 'import'])->name('category.import');
    Route::get('category/download-template', [CategoryController::class, 'downloadTemplate'])->name('category.download-template');

    // User management
    Route::resource('user', UserOwnerController::class);

    // Purchase Returns
    Route::prefix('purchase-returns')->name('purchase-returns.')->group(function () {
        Route::get('/', [PurchaseReturnController::class, 'index'])->name('index');
        Route::get('create/{purchase}', [PurchaseReturnController::class, 'create'])->name('create');
        Route::post('store/{purchase}', [PurchaseReturnController::class, 'store'])->name('store');
        Route::get('{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('show');
        Route::post('{purchaseReturn}/confirm', [PurchaseReturnController::class, 'confirm'])->name('confirm');
        Route::post('{purchaseReturn}/cancel', [PurchaseReturnController::class, 'cancel'])->name('cancel');
    });

    // Redirect old purchase returns tab to new route
    Route::get('purchases/returns', fn() => redirect()->route('owner.purchase-returns.index'))
        ->name('purchases.returns-redirect');

    // Purchases
    Route::resource('purchases', \App\Http\Controllers\Owner\PurchaseOrderController::class)
        ->parameters(['purchases' => 'purchase'])
        ->only(['index','create','store','show']);
    Route::post('purchases/{purchase}/submit', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'submit'])->name('purchases.submit');
    Route::post('purchases/{purchase}/approve', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'approve'])->name('purchases.approve');
    Route::post('purchases/{purchase}/receive', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'receive'])->name('purchases.receive');
    Route::patch('purchases/{purchase}/cancel', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'cancel'])->name('purchases.cancel');

    // Inventory routes
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::view('/', 'owner.inventory.index')->name('index');
        Route::get('stock-ins', [\App\Http\Controllers\Owner\StockInController::class, 'index'])->name('stock-ins.index');
        Route::prefix('stock-opnames')->name('stock-opnames.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\StockOpnameController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Owner\StockOpnameController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Owner\StockOpnameController::class, 'store'])->name('store');
            Route::get('{id}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'show'])->name('show');
            Route::post('{id}/approve', [\App\Http\Controllers\Owner\StockOpnameController::class, 'approve'])->name('approve');
            Route::delete('{id}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'destroy'])->name('destroy');
        });
        Route::get('stock-movements', [\App\Http\Controllers\Owner\StockMovementController::class, 'index'])->name('stock-movements.index');
        Route::get('stock-movements/{productId}/{date}', [\App\Http\Controllers\Owner\StockMovementController::class, 'getProductMovements'])->name('stock-movements.details');
    });

    // Notifications
    Route::resource('notification', NotificationOwnerController::class);
    Route::get('notifications/unread-count', [NotificationOwnerController::class, 'unreadCount']);
    Route::post('notifications/mark-as-read', [NotificationOwnerController::class, 'markAsRead']);
    Route::post('notifications/clear-all', [NotificationOwnerController::class, 'clearAll']);

    // Profile (owner)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileOwnerController::class, 'index'])->name('index');
        Route::put('update', [ProfileOwnerController::class, 'update'])->name('update');
        Route::put('password', [ProfileOwnerController::class, 'updatePassword'])->name('password');
        Route::delete('destroy', [ProfileOwnerController::class, 'destroy'])->name('destroy');
    });

    // Contacts (Customer & Supplier)
    Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('contacts/customers', [ContactController::class, 'storeCustomer'])->name('contacts.customers.store');
    Route::post('contacts/suppliers', [ContactController::class, 'storeSupplier'])->name('contacts.suppliers.store');
});

// Finance routes
Route::middleware(['auth', 'finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::view('/', 'finance.dashboard')->name('index');
    Route::get('dashboard', fn() => view('finance.dashboard'))->name('dashboard');
});

// Kepala Toko routes
Route::middleware(['auth', 'kepala_toko'])->prefix('kepala-toko')->name('kepala_toko.')->group(function () {
    Route::view('/', 'kepala_toko.dashboard')->name('index');
    Route::get('dashboard', fn() => view('kepala_toko.dashboard'))->name('dashboard');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin.dashboard')->name('index');
    Route::get('dashboard', fn() => view('admin.dashboard'))->name('dashboard');
});

// Editor routes
Route::middleware(['auth', 'editor'])->prefix('editor')->name('editor.')->group(function () {
    Route::view('/', 'editor.dashboard')->name('index');
    Route::get('dashboard', fn() => view('editor.dashboard'))->name('dashboard');
});

// Auth routes (login, registration, etc)
require __DIR__.'/auth.php';
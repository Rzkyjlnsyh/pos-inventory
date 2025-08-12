<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransaksiQrisController;
use App\Http\Controllers\TransaksiTunaiController;
use App\Http\Controllers\Owner\UserOwnerController;
use App\Http\Controllers\Owner\StockOwnerController;
use App\Http\Controllers\Owner\ReportOwnerController;
use App\Http\Controllers\Owner\CashierOwnerController;
use App\Http\Controllers\Owner\ProductOwnerController;
use App\Http\Controllers\Owner\ProfileOwnerController;
use App\Http\Controllers\Owner\DashboardOwnerController;
use App\Http\Controllers\Owner\NotificationOwnerController;
use App\Http\Controllers\Owner\MenuBestSellerOwnerController;
use App\Http\Controllers\Owner\ContactController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Owner Routes
Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.')->group(function () {
    
    // Dashboard
    Route::resource('dashboard', DashboardOwnerController::class);
    
    // Cashier
    Route::resource('cashier', CashierOwnerController::class);
    
    // Product (legacy UI)
    Route::resource('product', ProductOwnerController::class);

    // Catalog (new UI)
    Route::prefix('catalog')->name('catalog.')->group(function () {
        Route::resource('categories', \App\Http\Controllers\Owner\CategoryController::class)->names([
            'index' => 'category.index',
            'create' => 'category.create',
            'store' => 'category.store',
            'edit' => 'category.edit',
            'update' => 'category.update',
            'destroy' => 'category.destroy',
        ])->except(['show']);

        Route::resource('products', \App\Http\Controllers\Owner\ProductCatalogController::class)->names([
            'index' => 'products.index',
            'create' => 'products.create',
            'store' => 'products.store',
            'edit' => 'products.edit',
            'update' => 'products.update',
            'destroy' => 'products.destroy',
        ])->except(['show']);
        Route::get('products-search', [\App\Http\Controllers\Owner\ProductCatalogController::class, 'search'])->name('products.search');
    });
    
    // Transactions
    Route::resource('transaksitunai', TransaksiTunaiController::class);
    Route::resource('transaksiqris', TransaksiQrisController::class);
    
    // Stock
    Route::resource('stock', StockOwnerController::class);
    Route::patch('stock/increment/{id}', [StockOwnerController::class, 'incrementQty'])->name('stock.increment');
    Route::patch('stock/decrement/{id}', [StockOwnerController::class, 'decrementQty'])->name('stock.decrement');
    
    // User
    Route::resource('user', UserOwnerController::class);
    
    // Report
    Route::get('report/daily-income', [ReportOwnerController::class, 'dailyIncome'])->name('report.daily-income');
    Route::get('report/export-excel', [ReportOwnerController::class, 'exportExcel'])->name('report.export-excel');
    Route::resource('report', ReportOwnerController::class);   

    // Purchases
    Route::resource('purchases', \App\Http\Controllers\Owner\PurchaseOrderController::class)
        ->parameters(['purchases' => 'purchase'])
        ->only(['index','create','store','show']);
    Route::post('purchases/{purchase}/submit', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'submit'])->name('purchases.submit');
    Route::post('purchases/{purchase}/approve', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'approve'])->name('purchases.approve');
    Route::post('purchases/{purchase}/receive', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'receive'])->name('purchases.receive');

    // Inventory - Tabs page and subpages
    Route::view('inventory', 'owner.inventory.index')->name('inventory.index');
    Route::get('inventory/stock-ins', [\App\Http\Controllers\Owner\StockInController::class, 'index'])->name('inventory.stock-ins.index');
    Route::get('inventory/opname', [\App\Http\Controllers\Owner\StockOpnameController::class, 'index'])->name('inventory.opname.index');
    Route::get('inventory/movements', [\App\Http\Controllers\Owner\StockMovementController::class, 'index'])->name('inventory.movements.index');
    
    // Notifications
    Route::resource('notification', NotificationOwnerController::class);
    Route::get('notifications/unread-count', [NotificationOwnerController::class, 'unreadCount']);
    Route::post('notifications/mark-as-read', [NotificationOwnerController::class, 'markAsRead']);
    Route::post('notifications/clear-all', [NotificationOwnerController::class, 'clearAll']);
    
    // Profile
    Route::get('profile', [ProfileOwnerController::class, 'index'])->name('profile.index');
    Route::put('profile/update', [ProfileOwnerController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileOwnerController::class, 'updatePassword'])->name('profile.password');
    Route::delete('profile/destroy', [ProfileOwnerController::class, 'destroy'])->name('profile.destroy');

    Route::resource('menu-best-sellers', MenuBestSellerOwnerController::class);

    // Contacts: Customer & Supplier (single page with two tabs)
    Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('contacts/customers', [ContactController::class, 'storeCustomer'])->name('contacts.customers.store');
    Route::post('contacts/suppliers', [ContactController::class, 'storeSupplier'])->name('contacts.suppliers.store');
});

// Finance Routes
Route::middleware(['auth', 'finance'])->prefix('finance')->name('finance.')->group(function () {
    // Dashboard
    Route::get('dashboard', function () {
        return view('finance.dashboard');
    })->name('dashboard');
    
    // Placeholder routes - will be implemented later
    Route::view('/', 'finance.dashboard')->name('index');
});

// Kepala Toko Routes  
Route::middleware(['auth', 'kepala_toko'])->prefix('kepala-toko')->name('kepala_toko.')->group(function () {
    // Dashboard
    Route::get('dashboard', function () {
        return view('kepala_toko.dashboard');
    })->name('dashboard');
    
    // Placeholder routes - will be implemented later
    Route::view('/', 'kepala_toko.dashboard')->name('index');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    
    // Placeholder routes - will be implemented later
    Route::view('/', 'admin.dashboard')->name('index');
});

// Editor Routes
Route::middleware(['auth', 'editor'])->prefix('editor')->name('editor.')->group(function () {
    // Dashboard
    Route::get('dashboard', function () {
        return view('editor.dashboard');
    })->name('dashboard');
    
    // Placeholder routes - will be implemented later
    Route::view('/', 'editor.dashboard')->name('index');
});

require __DIR__.'/auth.php';

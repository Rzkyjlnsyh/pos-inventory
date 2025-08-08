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
use App\Http\Controllers\Karyawan\CashierKaryawanController;
use App\Http\Controllers\Karyawan\ProfileKaryawanController;
use App\Http\Controllers\Owner\MenuBestSellerOwnerController;
use App\Http\Controllers\Inventaris\StockInventarisController;
use App\Http\Controllers\Inventaris\ProfileInventarisController;
use App\Http\Controllers\Karyawan\NotificationKaryawanController;
use App\Http\Controllers\Karyawan\MenuBestSellerKaryawanController;
use App\Http\Controllers\Inventaris\NotificationInventarisController;
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

// Karyawan routes
Route::middleware(['auth', 'karyawan'])->prefix('karyawan')->name('karyawan.')->group(function (){

    // Cashier
    Route::resource('cashier', CashierKaryawanController::class);

    // Transactions
    Route::resource('transaksitunai', TransaksiTunaiController::class);
    Route::resource('transaksiqris', TransaksiQrisController::class);

    Route::resource('menu-best-sellers', MenuBestSellerKaryawanController::class);

    // Profile
    Route::get('profile', [ProfileKaryawanController::class, 'index'])->name('profile.index');
    Route::put('profile/update', [ProfileKaryawanController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileKaryawanController::class, 'updatePassword'])->name('profile.password');
    Route::delete('profile/destroy', [ProfileKaryawanController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::resource('notification', NotificationKaryawanController::class);
    Route::get('notifications/unread-count', [NotificationKaryawanController::class, 'unreadCount']);
    Route::post('notifications/mark-as-read', [NotificationKaryawanController::class, 'markAsRead']);
    Route::post('notifications/clear-all', [NotificationKaryawanController::class, 'clearAll']);
});

// Inventaris Route
Route::middleware(['auth', 'inventaris'])->prefix('inventaris')->name('inventaris.')->group(function (){

    // Stock
    Route::resource('stock', StockInventarisController::class);
    Route::patch('stock/increment/{id}', [StockInventarisController::class, 'incrementQty'])->name('stock.increment');
    Route::patch('stock/decrement/{id}', [StockInventarisController::class, 'decrementQty'])->name('stock.decrement');

    // Profile
    Route::get('profile', [ProfileInventarisController::class, 'index'])->name('profile.index');
    Route::put('profile/update', [ProfileInventarisController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileInventarisController::class, 'updatePassword'])->name('profile.password');
    Route::delete('profile/destroy', [ProfileInventarisController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::resource('notification', NotificationInventarisController::class);
    Route::get('notifications/unread-count', [NotificationInventarisController::class, 'unreadCount']);
    Route::post('notifications/mark-as-read', [NotificationInventarisController::class, 'markAsRead']);
    Route::post('notifications/clear-all', [NotificationInventarisController::class, 'clearAll']);
});

// Owner Routes
Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.')->group(function () {
    
    // Dashboard
    Route::resource('dashboard', DashboardOwnerController::class);
    
    // Cashier
    Route::resource('cashier', CashierOwnerController::class);
    
    // Product
    Route::resource('product', ProductOwnerController::class);
    
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

require __DIR__.'/auth.php';

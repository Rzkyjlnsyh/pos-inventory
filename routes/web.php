<?php

use App\Http\Controllers\Admin\CategoryAdminController;
use App\Http\Controllers\Owner\PurchaseReturnController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Owner\UserOwnerController;
use App\Http\Controllers\Owner\ProductOwnerController;
use App\Http\Controllers\Owner\ProfileOwnerController;
use App\Http\Controllers\Owner\NotificationOwnerController;
use App\Http\Controllers\Owner\ContactController;
use App\Http\Controllers\Owner\CategoryController;
use App\Http\Controllers\Owner\SalesOrderController;
use App\Http\Controllers\Owner\ShiftController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Editor\ContactController as EditorContactController;
use App\Http\Controllers\Finance\ContactController as FinanceContactController;
use App\Http\Controllers\KepalaToko\ContactController as KepalaTokoContactController;
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
    Route::post('owner/product/import', [ProductOwnerController::class, 'import'])->name('product.import');
    Route::get('owner/product/download-template', [ProductOwnerController::class, 'downloadTemplate'])->name('product.download-template');

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
    Route::post('purchases/{purchase}/update-status', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'updateWorkflowStatus'])->name('purchases.update-status');

    // Sales
    Route::resource('sales', SalesOrderController::class)
        ->parameters(['sales' => 'salesOrder']);
    Route::post('/sales/{salesOrder}/approve', [SalesOrderController::class, 'approve'])->name('sales.approve');
    Route::post('/sales/{salesOrder}/addPayment', [SalesOrderController::class, 'addPayment'])->name('sales.addPayment');
    Route::post('/sales/{salesOrder}/startProcess', [SalesOrderController::class, 'startProcess'])->name('sales.startProcess');
    Route::post('/sales/{salesOrder}/processJahit', [SalesOrderController::class, 'processJahit'])->name('sales.processJahit');
    Route::post('/sales/{salesOrder}/markAsJadi', [SalesOrderController::class, 'markAsJadi'])->name('sales.markAsJadi');
    Route::post('/sales/{salesOrder}/markAsDiterimaToko', [SalesOrderController::class, 'markAsDiterimaToko'])->name('sales.markAsDiterimaToko');
    Route::post('/sales/{salesOrder}/complete', [SalesOrderController::class, 'complete'])->name('sales.complete');
    Route::get('/payments/{payment}/nota', [SalesOrderController::class, 'printNota'])->name('sales.printNota');
    Route::get('/sales/{payment}/nota-direct', [SalesOrderController::class, 'printNotaDirect'])->name('sales.printNotaDirect');

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

        // Shift routes
        Route::get('shift/dashboard', [ShiftController::class, 'dashboard'])->name('shift.dashboard');
        Route::post('shift/start', [ShiftController::class, 'start'])->name('shift.start');
        Route::post('shift/end', [ShiftController::class, 'end'])->name('shift.end');
        Route::post('shift/expense', [ShiftController::class, 'expense'])->name('shift.expense');
        Route::get('/shift/history', [App\Http\Controllers\Owner\ShiftController::class, 'history'])->name('shift.history');
        Route::get('/shift/export', [App\Http\Controllers\Owner\ShiftController::class, 'export'])->name('shift.export');
        Route::get('/shift/export-pdf', [App\Http\Controllers\Owner\ShiftController::class, 'exportPdf'])->name('shift.export-pdf');
        Route::get('/shift/{shift}', [App\Http\Controllers\Owner\ShiftController::class, 'show'])->name('shift.show');
        Route::get('/shift/{shift}/export-detail', [App\Http\Controllers\Owner\ShiftController::class, 'exportDetail'])->name('shift.export-detail');
        Route::get('/shift/{shift}/export-detail-pdf', [App\Http\Controllers\Owner\ShiftController::class, 'exportDetailPdf'])->name('shift.export-detail-pdf');

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
    Route::post('contacts/customers/import', [ContactController::class, 'importCustomers'])->name('contacts.customers.import');
    Route::post('contacts/suppliers/import', [ContactController::class, 'importSuppliers'])->name('contacts.suppliers.import');
    Route::get('contacts/customers/template', [ContactController::class, 'downloadCustomerTemplate'])->name('contacts.customers.template');
    Route::get('contacts/suppliers/template', [ContactController::class, 'downloadSupplierTemplate'])->name('contacts.suppliers.template');
});

// Finance routes
Route::middleware(['auth', 'finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::view('/', 'finance.dashboard')->name('index');
    Route::get('dashboard', fn() => view('finance.dashboard'))->name('dashboard');

        // Contacts (Customer & Supplier)
        Route::get('contacts', [FinanceContactController::class, 'index'])->name('contacts.index');
        Route::post('contacts/customers', [FinanceContactController::class, 'storeCustomer'])->name('contacts.customers.store');
        Route::post('contacts/suppliers', [FinanceContactController::class, 'storeSupplier'])->name('contacts.suppliers.store');
        Route::post('contacts/customers/import', [FinanceContactController::class, 'importCustomers'])->name('contacts.customers.import');
        Route::post('contacts/suppliers/import', [FinanceContactController::class, 'importSuppliers'])->name('contacts.suppliers.import');
        Route::get('contacts/customers/template', [FinanceContactController::class, 'downloadCustomerTemplate'])->name('contacts.customers.template');
        Route::get('contacts/suppliers/template', [FinanceContactController::class, 'downloadSupplierTemplate'])->name('contacts.suppliers.template');
});

// Kepala Toko routes
Route::middleware(['auth', 'kepala_toko'])->prefix('kepala-toko')->name('kepala_toko.')->group(function () {
    Route::view('/', 'kepala_toko.dashboard')->name('index');
    Route::get('dashboard', fn() => view('kepala_toko.dashboard'))->name('dashboard');

        // Contacts (Customer & Supplier)
        Route::get('contacts', [KepalaTokoContactController::class, 'index'])->name('contacts.index');
        Route::post('contacts/customers', [KepalaTokoContactController::class, 'storeCustomer'])->name('contacts.customers.store');
        Route::post('contacts/suppliers', [KepalaTokoContactController::class, 'storeSupplier'])->name('contacts.suppliers.store');
        Route::post('contacts/customers/import', [KepalaTokoContactController::class, 'importCustomers'])->name('contacts.customers.import');
        Route::post('contacts/suppliers/import', [KepalaTokoContactController::class, 'importSuppliers'])->name('contacts.suppliers.import');
        Route::get('contacts/customers/template', [KepalaTokoContactController::class, 'downloadCustomerTemplate'])->name('contacts.customers.template');
        Route::get('contacts/suppliers/template', [KepalaTokoContactController::class, 'downloadSupplierTemplate'])->name('contacts.suppliers.template');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard.index'); // Nama route yang benar
    
    Route::get('dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    Route::resource('product', \App\Http\Controllers\Admin\ProductAdminController::class);
    Route::get('catalog/products/search', [\App\Http\Controllers\Admin\ProductAdminController::class, 'search'])->name('catalog.products.search');
    Route::post('admin/product/import', [\App\Http\Controllers\Admin\ProductAdminController::class, 'import'])->name('product.import');
    Route::get('admin/product/download-template', [\App\Http\Controllers\Admin\ProductAdminController::class, 'downloadTemplate'])->name('product.download-template');

    // Categories
    Route::resource('categories', CategoryAdminController::class)
        ->names([
            'index' => 'category.index',
            'create' => 'category.create',
            'store' => 'category.store',
            'edit' => 'category.edit',
            'update' => 'category.update',
            'destroy' => 'category.destroy',
        ])->except(['show']);
    Route::get('category/import', [CategoryAdminController::class, 'importForm'])->name('category.import');
    Route::post('category/import', [CategoryAdminController::class, 'import'])->name('category.import');
    Route::get('category/download-template', [CategoryAdminController::class, 'downloadTemplate'])->name('category.download-template');

    // Contacts (Customer & Supplier)
    Route::get('contacts', [AdminContactController::class, 'index'])->name('contacts.index');
    Route::post('contacts/customers', [AdminContactController::class, 'storeCustomer'])->name('contacts.customers.store');
    Route::post('contacts/suppliers', [AdminContactController::class, 'storeSupplier'])->name('contacts.suppliers.store');
    Route::post('contacts/customers/import', [AdminContactController::class, 'importCustomers'])->name('contacts.customers.import');
    Route::post('contacts/suppliers/import', [AdminContactController::class, 'importSuppliers'])->name('contacts.suppliers.import');
    Route::get('contacts/customers/template', [AdminContactController::class, 'downloadCustomerTemplate'])->name('contacts.customers.template');
    Route::get('contacts/suppliers/template', [AdminContactController::class, 'downloadSupplierTemplate'])->name('contacts.suppliers.template');
});

// Editor routes
Route::middleware(['auth', 'editor'])->prefix('editor')->name('editor.')->group(function () {
    Route::view('/', 'editor.dashboard')->name('index');
    Route::get('dashboard', fn() => view('editor.dashboard'))->name('dashboard');

        // Contacts (Customer & Supplier)
        Route::get('contacts', [EditorContactController::class, 'index'])->name('contacts.index');
        Route::post('contacts/customers', [EditorContactController::class, 'storeCustomer'])->name('contacts.customers.store');
        Route::post('contacts/suppliers', [EditorContactController::class, 'storeSupplier'])->name('contacts.suppliers.store');
        Route::post('contacts/customers/import', [EditorContactController::class, 'importCustomers'])->name('contacts.customers.import');
        Route::post('contacts/suppliers/import', [EditorContactController::class, 'importSuppliers'])->name('contacts.suppliers.import');
        Route::get('contacts/customers/template', [EditorContactController::class, 'downloadCustomerTemplate'])->name('contacts.customers.template');
        Route::get('contacts/suppliers/template', [EditorContactController::class, 'downloadSupplierTemplate'])->name('contacts.suppliers.template');
});

// Auth routes (login, registration, etc)
require __DIR__.'/auth.php';
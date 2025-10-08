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

    Route::get('shift/test-closing-summary', function () {
        $shift = \App\Models\Shift::latest()->first();
        $incomes = \App\Models\Income::where('shift_id', $shift->id)->get();
        $expenses = \App\Models\Expense::where('shift_id', $shift->id)->get();
        $salesOrders = \App\Models\SalesOrder::whereHas('payments', function ($query) use ($shift) {
            $query->where('created_by', $shift->user_id)
                  ->where('created_at', '>=', $shift->start_time)
                  ->where('created_at', '<=', $shift->end_time ?? now());
        })->with(['customer', 'payments'])->get();
        return view('owner.shift.closing_summary', compact('shift', 'incomes', 'expenses', 'salesOrders'));
    })->name('shift.test-closing-summary');

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
    Route::post('purchases/{purchase}/payment', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'payment'])->name('purchases.payment');
    Route::post('purchases/{purchase}/receive', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'receive'])->name('purchases.receive');
    Route::patch('purchases/{purchase}/cancel', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'cancel'])->name('purchases.cancel');
    Route::post('purchases/{purchase}/update-status', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'updateWorkflowStatus'])->name('purchases.update-status');
    Route::post('purchases/{purchase}/update-workflow-status', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'updateWorkflowStatus'])
    ->name('purchases.update-workflow-status');
    Route::post('purchases/{purchase}/return', [\App\Http\Controllers\Owner\PurchaseOrderController::class, 'return'])
    ->name('purchases.return');

// Sales
Route::middleware(['check.shift'])->group(function () {
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
    Route::get('/payments/{payment}/nota-direct', [SalesOrderController::class, 'printNotaDirect'])->name('sales.printNotaDirect');
    Route::post('/sales/{salesOrder}/payment/{payment}/upload-proof', [SalesOrderController::class, 'uploadProof'])->name('sales.uploadProof');
});

    // Inventory routes
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Owner\InventoryController::class, 'index'])->name('index');
        
        Route::get('stock-ins', [\App\Http\Controllers\Owner\StockInController::class, 'index'])->name('stock-ins.index');
        
        Route::prefix('stock-opnames')->name('stock-opnames.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\StockOpnameController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Owner\StockOpnameController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Owner\StockOpnameController::class, 'store'])->name('store');
            Route::get('download-template', [\App\Http\Controllers\Owner\StockOpnameController::class, 'downloadTemplate'])->name('template');
            Route::get('{stockOpname}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'show'])->name('show');
            Route::get('{stockOpname}/edit', [\App\Http\Controllers\Owner\StockOpnameController::class, 'edit'])->name('edit');
            Route::put('{stockOpname}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'update'])->name('update');
            Route::post('{stockOpname}/approve', [\App\Http\Controllers\Owner\StockOpnameController::class, 'approve'])->name('approve');
            Route::delete('{stockOpname}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'destroy'])->name('destroy');
            Route::get('{stockOpname}/pdf', [\App\Http\Controllers\Owner\StockOpnameController::class, 'exportPdf'])->name('pdf');
            Route::post('import', [\App\Http\Controllers\Owner\StockOpnameController::class, 'import'])->name('import');
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
    Route::get('/shift/{shift}/download-summary', [App\Http\Controllers\Owner\ShiftController::class, 'downloadSummary'])->name('shift.download-summary');
    Route::get('/shift/{shift}/print-preview', [App\Http\Controllers\Owner\ShiftController::class, 'printPreview'])->name('shift.print-preview');
    Route::get('/shift/{shift}/print-summary', [App\Http\Controllers\Owner\ShiftController::class, 'printSummary'])->name('shift.print-summary');
            Route::post('shift/income', [ShiftController::class, 'income'])->name('shift.income');

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

    Route::resource('product', \App\Http\Controllers\Finance\ProductFinanceController::class);
    Route::get('catalog/products/search', [\App\Http\Controllers\Finance\ProductFinanceController::class, 'search'])->name('catalog.products.search');
    Route::post('finance/product/import', [\App\Http\Controllers\Finance\ProductFinanceController::class, 'import'])->name('product.import');
    Route::get('finance/product/download-template', [\App\Http\Controllers\Finance\ProductFinanceController::class, 'downloadTemplate'])->name('product.download-template');

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
    
    // Inventory Finance routes
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Owner\InventoryController::class, 'index'])->name('index');
        Route::get('stock-ins', [\App\Http\Controllers\Owner\StockInController::class, 'index'])->name('stock-ins.index');  // Read-only
        Route::prefix('stock-opnames')->name('stock-opnames.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\StockOpnameController::class, 'index'])->name('index');
            Route::get('{id}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'show'])->name('show');
            Route::post('{id}/approve', [\App\Http\Controllers\Owner\StockOpnameController::class, 'approve'])->name('approve');
            Route::put('{stockOpname}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'update'])->name('update');
            Route::get('{stockOpname}/pdf', [\App\Http\Controllers\Owner\StockOpnameController::class, 'exportPdf'])->name('pdf');
            // No create/edit/delete
        });
        Route::get('stock-movements', [\App\Http\Controllers\Owner\StockMovementController::class, 'index'])->name('stock-movements.index');  // Read-only
        Route::get('stock-movements/{productId}/{date}', [\App\Http\Controllers\Owner\StockMovementController::class, 'getProductMovements'])->name('stock-movements.details');
    });
    Route::prefix('purchases')->middleware('auth')->name('purchases.')->group(function () {
        Route::get('/', [App\Http\Controllers\Finance\PurchaseOrderController::class, 'index'])->name('index');
        Route::get('create', [App\Http\Controllers\Finance\PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Finance\PurchaseOrderController::class, 'store'])->name('store');
        Route::get('{purchase}', [App\Http\Controllers\Finance\PurchaseOrderController::class, 'show'])->name('show');
        Route::post('{purchase}/submit', [App\Http\Controllers\Finance\PurchaseOrderController::class, 'submit'])->name('submit');
        Route::post('{purchase}/approve', [App\Http\Controllers\Finance\PurchaseOrderController::class, 'approve'])->name('approve');
        Route::post('{purchase}/payment', [App\Http\Controllers\Finance\PurchaseOrderController::class, 'payment'])->name('payment');
        Route::post('{purchase}/update-status', [App\Http\Controllers\Finance\PurchaseOrderController::class, 'updateWorkflowStatus'])->name('update-status');
        Route::patch('{purchase}/cancel', [App\Http\Controllers\Finance\PurchaseOrderController::class, 'cancel'])->name('cancel');
    });
        // Shift Routes - Hanya lihat dan export
    Route::prefix('shift')->name('shift.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Finance\ShiftController::class, 'dashboard'])->name('dashboard');
        Route::get('/history', [\App\Http\Controllers\Finance\ShiftController::class, 'history'])->name('history');
        Route::get('/{shift}', [\App\Http\Controllers\Finance\ShiftController::class, 'show'])->name('show');
        Route::get('/export', [\App\Http\Controllers\Finance\ShiftController::class, 'export'])->name('export');
        Route::get('/{shift}/export-detail', [\App\Http\Controllers\Finance\ShiftController::class, 'exportDetail'])->name('export-detail');
        // TIDAK ADA route post (start, end, expense) untuk finance
    });

  });

// Kepala Toko routes
Route::middleware(['auth', 'kepala_toko'])->prefix('kepala-toko')->name('kepala-toko.')->group(function () {
    Route::view('/', 'kepala-toko.dashboard')->name('index');
    Route::get('dashboard', fn() => view('kepala-toko.dashboard'))->name('dashboard');

    // Contacts (Customer & Supplier)
    Route::get('contacts', [KepalaTokoContactController::class, 'index'])->name('contacts.index');
    Route::post('contacts/customers', [KepalaTokoContactController::class, 'storeCustomer'])->name('contacts.customers.store');
    Route::post('contacts/suppliers', [KepalaTokoContactController::class, 'storeSupplier'])->name('contacts.suppliers.store');
    Route::post('contacts/customers/import', [KepalaTokoContactController::class, 'importCustomers'])->name('contacts.customers.import');
    Route::post('contacts/suppliers/import', [KepalaTokoContactController::class, 'importSuppliers'])->name('contacts.suppliers.import');
    Route::get('contacts/customers/template', [KepalaTokoContactController::class, 'downloadCustomerTemplate'])->name('contacts.customers.template');
    Route::get('contacts/suppliers/template', [KepalaTokoContactController::class, 'downloadSupplierTemplate'])->name('contacts.suppliers.template');

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

    // Inventory Kepala Toko routes
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::view('/', 'kepala-toko.inventory.index')->name('index');
        Route::get('stock-ins', [\App\Http\Controllers\Owner\StockInController::class, 'index'])->name('stock-ins.index');  // Read-only
        Route::prefix('stock-opnames')->name('stock-opnames.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\StockOpnameController::class, 'index'])->name('index');
            Route::get('{id}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'show'])->name('show');
            Route::post('{id}/approve', [\App\Http\Controllers\Owner\StockOpnameController::class, 'approve'])->name('approve');
            // No create/edit/delete
        });
        Route::get('stock-movements', [\App\Http\Controllers\Owner\StockMovementController::class, 'index'])->name('stock-movements.index');  // Read-only
        Route::get('stock-movements/{productId}/{date}', [\App\Http\Controllers\Owner\StockMovementController::class, 'getProductMovements'])->name('stock-movements.details');
    });
    Route::prefix('purchases')->middleware('auth')->name('purchases.')->group(function () {
        Route::get('/', [App\Http\Controllers\KepalaToko\PurchaseOrderController::class, 'index'])->name('index');
        Route::get('create', [App\Http\Controllers\KepalaToko\PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\KepalaToko\PurchaseOrderController::class, 'store'])->name('store');
        Route::get('{purchase}', [App\Http\Controllers\KepalaToko\PurchaseOrderController::class, 'show'])->name('show');
        Route::post('{purchase}/submit', [App\Http\Controllers\KepalaToko\PurchaseOrderController::class, 'submit'])->name('submit');
        Route::post('{purchase}/approve', [App\Http\Controllers\KepalaToko\PurchaseOrderController::class, 'approve'])->name('approve');
        Route::post('{purchase}/update-status', [App\Http\Controllers\KepalaToko\PurchaseOrderController::class, 'updateWorkflowStatus'])->name('update-status');
        Route::patch('{purchase}/cancel', [App\Http\Controllers\KepalaToko\PurchaseOrderController::class, 'cancel'])->name('cancel');
    });
    Route::prefix('purchase-returns')->name('purchase-returns.')->group(function () {
        Route::get('/', [App\Http\Controllers\KepalaToko\PurchaseReturnController::class, 'index'])->name('index');
        Route::get('create/{purchase}', [App\Http\Controllers\KepalaToko\PurchaseReturnController::class, 'create'])->name('create');
        Route::post('store/{purchase}', [App\Http\Controllers\KepalaToko\PurchaseReturnController::class, 'store'])->name('store');
        Route::get('{purchaseReturn}', [App\Http\Controllers\KepalaToko\PurchaseReturnController::class, 'show'])->name('show');
        Route::post('{purchaseReturn}/confirm', [App\Http\Controllers\KepalaToko\PurchaseReturnController::class, 'confirm'])->name('confirm');
        Route::post('{purchaseReturn}/cancel', [App\Http\Controllers\KepalaToko\PurchaseReturnController::class, 'cancel'])->name('cancel');
    });
    Route::prefix('shift')->name('shift.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\KepalaToko\ShiftController::class, 'dashboard'])->name('dashboard');
        Route::get('/history', [\App\Http\Controllers\KepalaToko\ShiftController::class, 'history'])->name('history');
        Route::get('/{shift}', [\App\Http\Controllers\KepalaToko\ShiftController::class, 'show'])->name('show');
        Route::get('/export', [\App\Http\Controllers\KepalaToko\ShiftController::class, 'export'])->name('export');
        Route::get('/{shift}/export-detail', [\App\Http\Controllers\KepalaToko\ShiftController::class, 'exportDetail'])->name('export-detail');
        Route::post('/start', [\App\Http\Controllers\KepalaToko\ShiftController::class, 'start'])->name('start');
        Route::post('/end', [\App\Http\Controllers\KepalaToko\ShiftController::class, 'end'])->name('end');
        Route::post('/expense', [\App\Http\Controllers\KepalaToko\ShiftController::class, 'expense'])->name('expense');
        Route::get('/shift/history', [App\Http\Controllers\Admin\ShiftController::class, 'history'])->name('shift.history');
    });
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard.index');
    
    Route::get('dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::resource('product', \App\Http\Controllers\Admin\ProductAdminController::class);
    Route::get('catalog/products/search', [\App\Http\Controllers\Admin\ProductAdminController::class, 'search'])->name('catalog.products.search');
    Route::post('admin/product/import', [\App\Http\Controllers\Admin\ProductAdminController::class, 'import'])->name('product.import');
    Route::get('admin/product/download-template', [\App\Http\Controllers\Admin\ProductAdminController::class, 'downloadTemplate'])->name('product.download-template');

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

    // Contacts (Customer & Supplier)
    Route::get('contacts', [AdminContactController::class, 'index'])->name('contacts.index');
    Route::post('contacts/customers', [AdminContactController::class, 'storeCustomer'])->name('contacts.customers.store');
    Route::post('contacts/suppliers', [AdminContactController::class, 'storeSupplier'])->name('contacts.suppliers.store');
    Route::post('contacts/customers/import', [AdminContactController::class, 'importCustomers'])->name('contacts.customers.import');
    Route::post('contacts/suppliers/import', [AdminContactController::class, 'importSuppliers'])->name('contacts.suppliers.import');
    Route::get('contacts/customers/template', [AdminContactController::class, 'downloadCustomerTemplate'])->name('contacts.customers.template');
    Route::get('contacts/suppliers/template', [AdminContactController::class, 'downloadSupplierTemplate'])->name('contacts.suppliers.template');

    // Inventory Admin routes
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Owner\InventoryController::class, 'index'])->name('index');

        Route::get('stock-ins', [\App\Http\Controllers\Owner\StockInController::class, 'index'])->name('stock-ins.index');  // Read-only
        Route::prefix('stock-opnames')->name('stock-opnames.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\StockOpnameController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Owner\StockOpnameController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Owner\StockOpnameController::class, 'store'])->name('store');
            Route::get('template', [\App\Http\Controllers\Owner\StockOpnameController::class, 'downloadTemplate'])->name('template');
            Route::get('{stockOpname}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'show'])->name('show');
            Route::get('{stockOpname}/edit', [\App\Http\Controllers\Owner\StockOpnameController::class, 'edit'])->name('edit');
            Route::put('{stockOpname}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'update'])->name('update');
            Route::delete('{stockOpname}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'destroy'])->name('destroy');
            Route::get('{stockOpname}/pdf', [\App\Http\Controllers\Owner\StockOpnameController::class, 'exportPdf'])->name('pdf');
            Route::post('import', [\App\Http\Controllers\Owner\StockOpnameController::class, 'import'])->name('import');

        });
        Route::get('stock-movements', [\App\Http\Controllers\Owner\StockMovementController::class, 'index'])->name('stock-movements.index');
        Route::get('stock-movements/{productId}/{date}', [\App\Http\Controllers\Owner\StockMovementController::class, 'getProductMovements'])->name('stock-movements.details');
    });
    Route::prefix('purchases')->middleware('auth')->name('purchases.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'index'])->name('index');
        Route::get('create', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'store'])->name('store');
        Route::get('{purchase}', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'show'])->name('show');
        Route::post('{purchase}/submit', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'submit'])->name('submit');
        Route::post('{purchase}/update-status', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'updateWorkflowStatus'])->name('update-status');
        Route::patch('{purchase}/cancel', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'cancel'])->name('cancel');
    });
    Route::prefix('purchase-returns')->name('purchase-returns.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PurchaseReturnController::class, 'index'])->name('index');
        Route::get('create/{purchase}', [App\Http\Controllers\Admin\PurchaseReturnController::class, 'create'])->name('create');
        Route::post('store/{purchase}', [App\Http\Controllers\Admin\PurchaseReturnController::class, 'store'])->name('store');
        Route::get('{purchaseReturn}', [App\Http\Controllers\Admin\PurchaseReturnController::class, 'show'])->name('show');
        Route::post('{purchaseReturn}/cancel', [App\Http\Controllers\Admin\PurchaseReturnController::class, 'cancel'])->name('cancel');
        // TIDAK ADA confirm route untuk admin
    });
        // Shift routes
        Route::get('shift/dashboard', [App\Http\Controllers\Admin\ShiftController::class, 'dashboard'])->name('shift.dashboard');
        Route::post('shift/start', [App\Http\Controllers\Admin\ShiftController::class, 'start'])->name('shift.start');
        Route::post('shift/end', [App\Http\Controllers\Admin\ShiftController::class, 'end'])->name('shift.end');
        Route::post('shift/expense', [App\Http\Controllers\Admin\ShiftController::class, 'expense'])->name('shift.expense');
        Route::get('/shift/history', [App\Http\Controllers\Admin\ShiftController::class, 'history'])->name('shift.history');
        Route::post('shift/income', [ShiftController::class, 'income'])->name('shift.income'); // <-- TAMBAH INI

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
    Route::get('/payments/{payment}/nota-direct', [SalesOrderController::class, 'printNotaDirect'])->name('sales.printNotaDirect');
    Route::post('/sales/{salesOrder}/payment/{payment}/upload-proof', [SalesOrderController::class, 'uploadProof'])->name('sales.uploadProof');
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

    Route::resource('product', \App\Http\Controllers\Editor\ProductEditorController::class);
    Route::get('catalog/products/search', [\App\Http\Controllers\Editor\ProductEditorController::class, 'search'])->name('catalog.products.search');
    Route::post('editor/product/import', [\App\Http\Controllers\Editor\ProductEditorController::class, 'import'])->name('product.import');
    Route::get('editor/product/download-template', [\App\Http\Controllers\Editor\ProductEditorController::class, 'downloadTemplate'])->name('product.download-template');

    // Inventory Editor routes
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::view('/', 'editor.inventory.index')->name('index');
        Route::get('stock-ins', [\App\Http\Controllers\Owner\StockInController::class, 'index'])->name('stock-ins.index');  // Read-only
        Route::prefix('stock-opnames')->name('stock-opnames.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\StockOpnameController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Owner\StockOpnameController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Owner\StockOpnameController::class, 'store'])->name('store');
            Route::get('{id}', [\App\Http\Controllers\Owner\StockOpnameController::class, 'show'])->name('show');
            // No approve/delete
        });
        Route::get('stock-movements', [\App\Http\Controllers\Owner\StockMovementController::class, 'index'])->name('stock-movements.index');  // Read-only
        Route::get('stock-movements/{productId}/{date}', [\App\Http\Controllers\Owner\StockMovementController::class, 'getProductMovements'])->name('stock-movements.details');
    });
    Route::prefix('purchases')->middleware('auth')->name('purchases.')->group(function () {
        Route::get('/', [App\Http\Controllers\Editor\PurchaseOrderController::class, 'index'])->name('index');
        Route::get('create', [App\Http\Controllers\Editor\PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Editor\PurchaseOrderController::class, 'store'])->name('store');
        Route::get('{purchase}', [App\Http\Controllers\Editor\PurchaseOrderController::class, 'show'])->name('show');
        Route::post('{purchase}/submit', [App\Http\Controllers\Editor\PurchaseOrderController::class, 'submit'])->name('submit');
        Route::post('{purchase}/update-status', [App\Http\Controllers\Editor\PurchaseOrderController::class, 'updateWorkflowStatus'])->name('update-status');
        Route::patch('{purchase}/cancel', [App\Http\Controllers\Editor\PurchaseOrderController::class, 'cancel'])->name('cancel');
    });
});

// Auth routes (login, registration, etc)
require __DIR__.'/auth.php';
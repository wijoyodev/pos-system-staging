<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClerekController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EodController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OfflineController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PendingOrderController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\PriceLabelController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreSettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── PWA Routes ──
Route::get('/pwa/icon-{size}.png', function ($size) {
    // Generate a simple PNG icon using pure PHP (no GD required)
    // This creates a minimal valid PNG with the POS branding
    $sizes = ['192' => 192, '512' => 512];
    $s = $sizes[$size] ?? 192;

    // Return SVG as fallback for PNG requests
    $svg = file_get_contents(public_path('pwa/icon.svg'));

    return response($svg, 200, [
        'Content-Type' => 'image/svg+xml',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('size', '192|512');

Route::get('/offline', function () {
    return response()->view('pages.offline', [], 503);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginUI'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
});

// Public routes (no auth required)
Route::get('/presensi-page', [PresensiController::class, 'presensiPage']);

// ── All authenticated users (kasir, admin, super_admin) ──
Route::middleware(['auth', 'cek_presensi'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/', [HomeController::class, 'index']);
    Route::get('/api/products/search', [HomeController::class, 'searchProduct']);

    Route::get('/presensi', [PresensiController::class, 'index']);
    Route::post('/presensi', [PresensiController::class, 'presensi']);
    Route::post('/presensi/checkout', [PresensiController::class, 'checkout']);
    Route::get('/presensi/check', [PresensiController::class, 'checkStatus']);
    Route::post('/presensi/scan', [PresensiController::class, 'scan']);
    Route::post('/presensi/scan-pulang', [PresensiController::class, 'scanPulang']);
    Route::get('/presensi/api/data', [PresensiController::class, 'getPresensiData']);

    Route::get('/history', [HistoryController::class, 'index']);
    Route::get('/void-otp', [HistoryController::class, 'voidOtpPage'])->name('void.otp.page');
    Route::post('/void-otp/search', [HistoryController::class, 'searchVoidTransaction'])->name('void.otp.search');
    Route::post('/void-otp/generate', [HistoryController::class, 'generateVoidOtp'])->name('void.otp.generate');
    Route::post('/history/{history}/void', [HistoryController::class, 'void'])->name('history.void');

    Route::post('/payment/checkout', [PaymentController::class, 'checkout']);
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
    Route::post('/payment/process', [PaymentController::class, 'process']);
    Route::post('/payment/voucher/check', [PaymentController::class, 'checkVoucher']);
    Route::post('/payment/qris/generate', [PaymentController::class, 'generateQris']);
    Route::get('/payment/qris/status/{order_id}', [PaymentController::class, 'checkQrisStatus']);

    Route::get('/recipe/{history}', [ReceiptController::class, 'index'])->name('recipe');
    Route::get('/payment/receipt/{history}/print', [ReceiptController::class, 'print'])->name('receipt.print');
    Route::get('/payment/receipt/{history}/print-faktur', [ReceiptController::class, 'printFaktur'])->name('receipt.print-faktur');

    // Customer search & quick register (all roles can use from POS)
    Route::get('/customers/search', [CustomerController::class, 'search']);
    Route::post('/customers/quick-register', [CustomerController::class, 'store'])->name('customers.quick-register');

    // Clerek/Shift Settlement
    Route::get('/clerek/summary', [ClerekController::class, 'summary']);
    Route::post('/clerek/submit', [ClerekController::class, 'store']);
    Route::post('/clerek/process', [ClerekController::class, 'process']);

    // Pending Orders
    Route::get('/pending-orders', [PendingOrderController::class, 'index'])->name('pending-orders.index');
    Route::post('/pending-orders', [PendingOrderController::class, 'store'])->name('pending-orders.store');
    Route::get('/pending-orders/{pendingOrder}/load', [PendingOrderController::class, 'load'])->name('pending-orders.load');
    Route::get('/pending-orders/{pendingOrder}', [PendingOrderController::class, 'show'])->name('pending-orders.show');
    Route::put('/pending-orders/{pendingOrder}', [PendingOrderController::class, 'update'])->name('pending-orders.update');
    Route::delete('/pending-orders/{pendingOrder}', [PendingOrderController::class, 'destroy'])->name('pending-orders.destroy');
    Route::post('/pending-orders/{pendingOrder}/cancel', [PendingOrderController::class, 'cancel'])->name('pending-orders.cancel');

    // Expired Products (all authenticated users can view)
    Route::get('/stock-in/expired', [StockInController::class, 'expired']);
});

// ── Admin & Super Admin only ──
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/presensi/qr', [PresensiController::class, 'qr']);

    // Product listing (read-only for admin)
    Route::get('/product', [ProductController::class, 'index']);
    Route::get('/product/{product}/detail', [ProductController::class, 'detail']);

    Route::post('/product/recipe', [RecipeController::class, 'store']);
    Route::get('/product/recipe/{productId}', [RecipeController::class, 'getRecipe']);

    Route::get('/category', [CategoryController::class, 'index']);
    Route::get('/units', [UnitController::class, 'index']);
    Route::post('/units', [UnitController::class, 'store'])->middleware('role:super_admin');
    Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->middleware('role:super_admin');

    Route::get('/stock', [StockController::class, 'index']);
    Route::post('/stock/import', [StockController::class, 'import']);

    Route::get('/setting', [SettingController::class, 'index']);
    Route::post('/setting', [SettingController::class, 'update']);
    Route::post('/setting/cashier/{cashier}', [SettingController::class, 'updateCashier']);

    // Store Settings (per-store configuration)
    Route::get('/store-settings', [StoreSettingController::class, 'index']);
    Route::post('/store-settings', [StoreSettingController::class, 'update']);
    Route::post('/store-settings/global', [StoreSettingController::class, 'updateGlobal']);

    // Customer management (list + add)
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);

    // Clerek Data (Reports & Reconciliation)
    Route::get('/clerek/data', [ClerekController::class, 'index']);
    Route::get('/clerek/{closing}/print', [ClerekController::class, 'print'])->name('clerek.print');

    // Reports & Expenses
    Route::get('/expenses', [ReportController::class, 'expenses']);
    Route::post('/expenses', [ReportController::class, 'storeExpense']);
    Route::delete('/expenses/{expense}', [ReportController::class, 'destroyExpense']);
    Route::get('/reports/pnl', [ReportController::class, 'pnl']);

    // Promotions
    Route::get('/promotions', [PromotionController::class, 'index']);
    Route::post('/promotions', [PromotionController::class, 'store']);
    Route::get('/promotions/{promotion}', [PromotionController::class, 'show']);
    Route::put('/promotions/{promotion}', [PromotionController::class, 'update']);
    Route::delete('/promotions/{promotion}', [PromotionController::class, 'destroy']);
    Route::post('/promotions/bulk-delete', [PromotionController::class, 'bulkDestroy']);
    Route::post('/promotions/{promotion}/toggle', [PromotionController::class, 'toggle']);
    Route::get('/promotions/products', [PromotionController::class, 'getProducts']);
    Route::get('/promotions/categories', [PromotionController::class, 'getCategories']);

    // Supplier Management
    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::post('/suppliers', [SupplierController::class, 'store']);
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update']);
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy']);
    Route::post('/suppliers/bulk-delete', [SupplierController::class, 'bulkDestroy']);

    // Stock In Management
    Route::get('/stock-in', [StockInController::class, 'index']);
    Route::get('/stock-in/create', [StockInController::class, 'create']);
    Route::post('/stock-in', [StockInController::class, 'store']);
    Route::get('/stock-in/{stockIn}', [StockInController::class, 'show']);
    Route::get('/stock-in/{stockIn}/print-faktur', [StockInController::class, 'printFaktur']);
    Route::delete('/stock-in/{stockIn}', [StockInController::class, 'destroy']);
    Route::post('/stock-in/expired/update', [StockInController::class, 'updateExpired']);

    // Stock Transfer (Mutasi Barang)
    Route::get('/stock-transfer', [StockTransferController::class, 'index']);
    Route::get('/stock-transfer/create', [StockTransferController::class, 'create'])->middleware('role:super_admin');
    Route::post('/stock-transfer', [StockTransferController::class, 'store'])->middleware('role:super_admin');
    Route::get('/stock-transfer/{stockTransfer}', [StockTransferController::class, 'show']);
    Route::post('/stock-transfer/{stockTransfer}/send', [StockTransferController::class, 'send']);
    Route::post('/stock-transfer/{stockTransfer}/receive', [StockTransferController::class, 'receive']);
    Route::post('/stock-transfer/{stockTransfer}/reject', [StockTransferController::class, 'reject']);
    Route::delete('/stock-transfer/{stockTransfer}', [StockTransferController::class, 'destroy']);

    // EOD (End Of Day)
    Route::get('/eod', [EodController::class, 'index']);
    Route::post('/eod/generate', [EodController::class, 'generate']);
    Route::get('/eod/{eodReport}', [EodController::class, 'show']);
    Route::get('/eod/{eodReport}/print', [EodController::class, 'print']);

    // Purchase Orders
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
    Route::get('/purchase-orders/{purchaseOrder}/print-faktur', [PurchaseOrderController::class, 'printFaktur']);
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);
    Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
    Route::delete('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);

    // Shift Schedule Routes
    Route::get('/shift-schedule', [ShiftController::class, 'index']);
    Route::post('/shift-schedule', [ShiftController::class, 'store']);
    Route::put('/shift-schedule/{shift}', [ShiftController::class, 'update']);
    Route::delete('/shift-schedule/{shift}', [ShiftController::class, 'destroy']);
    Route::post('/shift-schedule/init-default', [ShiftController::class, 'initDefaultShifts']);

    // Shift Assignment Routes
    Route::get('/shift-assignment', [ShiftController::class, 'assignments']);
    Route::post('/shift-assignment', [ShiftController::class, 'assign']);
    Route::delete('/shift-assignment/{assignment}', [ShiftController::class, 'deleteAssignment']);

    // Price Label
    Route::get('/price-label', [PriceLabelController::class, 'index']);
    Route::post('/price-label/print', [PriceLabelController::class, 'print']);
});

// ── Super Admin only ──
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/bulk-delete', [UserController::class, 'bulkDestroy']);

    // Branch & Store management
    Route::get('/branches', [BranchController::class, 'index']);
    Route::post('/branches', [BranchController::class, 'store']);
    Route::put('/branches/{branch}', [BranchController::class, 'update']);
    Route::delete('/branches/{branch}', [BranchController::class, 'destroy']);

    Route::post('/stores', [StoreController::class, 'store']);
    Route::put('/stores/{store}', [StoreController::class, 'update']);
    Route::delete('/stores/{store}', [StoreController::class, 'destroy']);
    Route::post('/stores/{store}/toggle', [StoreController::class, 'toggleStatus']);
    Route::post('/stores/{store}/assign', [StoreController::class, 'assignUser']);

    // Product & Category management (Super Admin only)
    Route::post('/product', [ProductController::class, 'store']);
    Route::post('/product/promo-import', [ProductController::class, 'promoImport']);
    Route::match(['PUT', 'POST'], '/product/{product}', [ProductController::class, 'update']);
    Route::delete('/product/{product}', [ProductController::class, 'destroy']);
    Route::post('/product/bulk-delete', [ProductController::class, 'bulkDestroy']);

    Route::post('/category', [CategoryController::class, 'store']);
    Route::delete('/category/{category}', [CategoryController::class, 'destroy']);
    Route::post('/category/bulk-delete', [CategoryController::class, 'bulkDestroy']);

    // Customer edit, delete (Super Admin only)
    Route::put('/customers/{customer}', [CustomerController::class, 'update']);
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);
    Route::post('/customers/bulk-delete', [CustomerController::class, 'bulkDestroy']);

    // Database Backup
    Route::get('/backup', [BackupController::class, 'index']);
    Route::post('/backup/create', [BackupController::class, 'create']);
    Route::post('/backup/restore', [BackupController::class, 'restore']);
    Route::get('/backup/{filename}/download', [BackupController::class, 'download']);
    Route::delete('/backup/{filename}', [BackupController::class, 'destroy']);
    Route::post('/backup/{backup}/restore-cloud', [BackupController::class, 'restoreCloud']);
    Route::post('/backup/{backup}/retry-sync', [BackupController::class, 'retrySync']);

    // Pengaturan Integrasi
    Route::get('/pengaturan-integrasi', [StoreSettingController::class, 'paymentSetting']);
    Route::post('/pengaturan-integrasi', [StoreSettingController::class, 'updatePayment']);
});

// Offline API Routes (authenticated)
Route::middleware(['auth'])->prefix('api/offline')->group(function () {
    Route::get('/products', [OfflineController::class, 'getProducts']);
    Route::get('/stocks', [OfflineController::class, 'getStocks']);
    Route::get('/categories', [OfflineController::class, 'getCategories']);
    Route::post('/transactions', [OfflineController::class, 'saveTransaction']);
    Route::post('/stock-deduction', [OfflineController::class, 'stockDeduction']);
    Route::get('/pending-transactions', [OfflineController::class, 'getPendingTransactions']);
});

// Stock Opname Routes (UI)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/stock-opname', function () {
        return view('pages.stock-opname');
    });
    Route::get('/stock-opname/{session}/print-kertas', [StockOpnameController::class, 'printKertasView']);
    Route::get('/stock-opname/{session}/print-selisih', [StockOpnameController::class, 'printSelisihView']);
});

Route::middleware(['auth'])->prefix('api/opname')->group(function () {
    Route::get('/sessions', [StockOpnameController::class, 'index']);
    Route::post('/sessions', [StockOpnameController::class, 'store'])->middleware('role:super_admin');
    Route::get('/sessions/{session}', [StockOpnameController::class, 'show']);
    Route::put('/sessions/{session}', [StockOpnameController::class, 'update']);

    Route::post('/sessions/{session}/print-kertas', [StockOpnameController::class, 'printKertas']);
    Route::post('/sessions/{session}/entry', [StockOpnameController::class, 'startEntry']);
    Route::post('/sessions/{session}/check-data', [StockOpnameController::class, 'checkData']);
    Route::post('/sessions/{session}/proses', [StockOpnameController::class, 'proses']);
    Route::post('/sessions/{session}/print-selisih', [StockOpnameController::class, 'printSelisih']);
    Route::post('/sessions/{session}/edit-data', [StockOpnameController::class, 'editData']);
    Route::post('/sessions/{session}/fixed', [StockOpnameController::class, 'fixed']);
    Route::post('/sessions/{session}/adjust', [StockOpnameController::class, 'adjust']);

    Route::post('/sessions/{session}/cancel', [StockOpnameController::class, 'cancel']);
    Route::post('/sessions/{session}/change-status/{status}', [StockOpnameController::class, 'changeStatus']);

    Route::get('/sessions/{session}/products', [StockOpnameController::class, 'getProducts']);
    Route::get('/sessions/{session}/unentered', [StockOpnameController::class, 'getUnenteredProducts']);
    Route::get('/sessions/{session}/entered', [StockOpnameController::class, 'getEnteredProducts']);
    Route::post('/sessions/{session}/counts', [StockOpnameController::class, 'submitCounts']);
    Route::post('/sessions/{session}/edit-counts', [StockOpnameController::class, 'editCounts']);
    Route::put('/counts/{count}', [StockOpnameController::class, 'updateCount']);

    Route::put('/adjustments/{adjustment}', [StockOpnameController::class, 'updateAdjustment']);
    Route::post('/adjustments/{adjustment}/approve', [StockOpnameController::class, 'approveAdjustment']);
    Route::post('/adjustments/{adjustment}/reject', [StockOpnameController::class, 'rejectAdjustment']);
});

<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. PUBLIC WEBSITE (Customer View)
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/api/products', [HomeController::class, 'getProducts'])->name('api.products');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show'); // Public product view
Route::get('/story', function () {
    return Inertia::render('Story');
})->name('story.index');
Route::get('/wishlist', function () {
    return Inertia::render('Wishlist');
})->name('wishlist.index');
Route::get('/cart', function () {
    return Inertia::render('Cart');
})->name('cart.index');
Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/process', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');

// ==========================================
// ORDER & RECEIPT ROUTES (Public Access)
// ==========================================
Route::get('/orders/{id}', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('orders.show');
Route::get('/orders/{id}/receipt', [\App\Http\Controllers\CheckoutController::class, 'downloadReceipt'])->name('orders.receipt');
Route::get('/orders/{id}/invoice', [\App\Http\Controllers\CheckoutController::class, 'downloadInvoice'])->name('orders.invoice');


// ==========================================
// 2. AUTHENTICATION ROUTES
// ==========================================
require __DIR__.'/auth.php';


// ==========================================
// 3. PROFILE ROUTES (For All Authenticated Users)
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// ==========================================
// 4. ADMIN & STAFF DASHBOARD (Protected - Admin/Staff Only)
// ==========================================
Route::middleware(['auth', 'verified', 'admin.staff'])->group(function () {

    // ----------------------------------------------------------------
    // DASHBOARD ROUTES
    // ----------------------------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/realtime', [DashboardController::class, 'realTimeData'])->name('dashboard.realtime');
    Route::get('/dashboard/notifications', [DashboardController::class, 'notifications'])->name('dashboard.notifications');

    // --- ADMIN MODULES ---
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/search', [PosController::class, 'searchProduct'])->name('pos.search');

    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('promotions', PromotionController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('roles', RoleController::class);

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock', [AttendanceController::class, 'store'])->name('attendance.store');

    // Settings routes
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/clear-cache', [\App\Http\Controllers\SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    Route::post('/settings/backup', [\App\Http\Controllers\SettingsController::class, 'createBackup'])->name('settings.backup');
    Route::post('/settings/test-telegram', [\App\Http\Controllers\SettingsController::class, 'testTelegram'])->name('settings.test-telegram');

});
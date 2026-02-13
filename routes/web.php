<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::get('/checkout', function () {
    return view('order');
});

Route::post('/checkout/process', [App\Http\Controllers\CheckoutController::class, 'process']);
Route::post('/checkout/confirm', [App\Http\Controllers\CheckoutController::class, 'confirm']);

// Admin Routes
// Admin Routes - Public
Route::view('/admin/login', 'admin.login')->name('login');
Route::post('/admin/login', [App\Http\Controllers\AdminController::class, 'login']);
Route::get('/admin/logout', [App\Http\Controllers\AdminController::class, 'logout']);

// Admin Routes - Protected
Route::middleware(['admin'])->group(function () {
    Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index']);
    Route::get('/admin/orders', [App\Http\Controllers\OrderController::class, 'index']);
    Route::get('/admin/orders/{id}', [App\Http\Controllers\OrderController::class, 'show']);
    Route::get('/admin/orders/{id}/verify-payment', [App\Http\Controllers\OrderController::class, 'verifyPayment']);
    Route::get('/admin/orders/{id}/verify-content', [App\Http\Controllers\OrderController::class, 'verifyContent']);
    Route::get('/admin/orders/{id}/approve', [App\Http\Controllers\OrderController::class, 'approve']);
    Route::post('/admin/orders/{id}/reject-content', [App\Http\Controllers\OrderController::class, 'rejectContent']);
    Route::post('/admin/orders/{id}/complete', [App\Http\Controllers\OrderController::class, 'complete']);
    Route::get('/admin/orders/{id}/submit-review', [App\Http\Controllers\OrderController::class, 'submitForReview']);
    Route::get('/admin/quick-complete/{id}', [App\Http\Controllers\OrderController::class, 'quickComplete']);

    Route::post('/admin/orders/{id}/assign', [App\Http\Controllers\OrderController::class, 'assignStaff']);
    Route::get('/admin/orders/{id}/start-work', [App\Http\Controllers\OrderController::class, 'startWork']);

    // Staff Management
    Route::get('/admin/staff', [App\Http\Controllers\StaffController::class, 'index']);
    Route::post('/admin/staff', [App\Http\Controllers\StaffController::class, 'store']);
    Route::get('/admin/staff/delete/{id}', [App\Http\Controllers\StaffController::class, 'destroy']);

    // Reports
    Route::get('/admin/reports/sales', [App\Http\Controllers\SalesReportController::class, 'index']);
    Route::get('/admin/reports/sales/export', [App\Http\Controllers\SalesReportController::class, 'export']);
    Route::get('/admin/reports/sales/download-pdf', [App\Http\Controllers\SalesReportController::class, 'downloadPdf']);

    Route::get('/admin/customers', [App\Http\Controllers\AdminController::class, 'customers']);
    Route::get('/admin/paid/{id}', [App\Http\Controllers\AdminController::class, 'markPaid']);
    Route::get('/admin/completed/{id}', [App\Http\Controllers\AdminController::class, 'markCompleted']);
    Route::get('/admin/reject/{id}', [App\Http\Controllers\AdminController::class, 'markRejected']);
    Route::get('/admin/delete/{id}', [App\Http\Controllers\AdminController::class, 'deleteOrder']);
    Route::get('/admin/invoice/{id}', [App\Http\Controllers\AdminController::class, 'invoice']);
    Route::get('/admin/invoice/{id}/download', [App\Http\Controllers\AdminController::class, 'downloadInvoice']);
    Route::get('/admin/export/orders', [App\Http\Controllers\AdminController::class, 'exportOrders']);
    Route::get('/admin/services', [App\Http\Controllers\ServiceController::class, 'index']);
    Route::post('/admin/services', [App\Http\Controllers\ServiceController::class, 'update']);
    Route::get('/admin/settings', [App\Http\Controllers\AdminController::class, 'settings']);
    Route::post('/admin/settings', [App\Http\Controllers\AdminController::class, 'updateSettings']);
    Route::get('/admin/edit/{id}', [App\Http\Controllers\AdminController::class, 'edit']);
    Route::post('/admin/update/{id}', [App\Http\Controllers\AdminController::class, 'update']);
    Route::post('/admin/batch', [App\Http\Controllers\AdminController::class, 'batchUpdate']);
    Route::get('/admin/api/updates', [App\Http\Controllers\AdminController::class, 'getLatestUpdates']);
});

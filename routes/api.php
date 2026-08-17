<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AppSettingController;

// Public mobile client system API configuration endpoint
Route::get('/v1/app-config', [AppSettingController::class, 'getPublicConfig']);

// Sanctum authenticated user info route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API version 1 routes with authentication and shop scope
// Auth routes (no auth middleware)
Route::post('/v1/auth/login', [App\Http\Controllers\Api\AuthApiController::class, 'login']);
Route::post('/v1/auth/register', [App\Http\Controllers\Api\AuthApiController::class, 'register']);

// API version 1 routes with authentication and shop scope
Route::prefix('v1')->middleware(['auth:sanctum', 'shop.scope', 'idempotency'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Api\DashboardApiController::class, 'index']);

    // Backup & Restore for Shop Owner
    Route::get('/backup/export', [\App\Http\Controllers\Api\ShopOwner\BackupApiController::class, 'export']);
    Route::post('/backup/restore', [\App\Http\Controllers\Api\ShopOwner\BackupApiController::class, 'restore']);

    // Sync
    Route::post('/sync/batch', [\App\Http\Controllers\Api\SyncBatchController::class, 'batch']);

    // Invoice Settings
    Route::get('/invoice-settings', [\App\Http\Controllers\Api\InvoiceSettingApiController::class, 'show']);
    Route::post('/invoice-settings', [\App\Http\Controllers\Api\InvoiceSettingApiController::class, 'update']);

    // Category CRUD
    Route::apiResource('categories', \App\Http\Controllers\Api\CategoryApiController::class);

    // Product CRUD
    Route::apiResource('products', \App\Http\Controllers\Api\ProductApiController::class);

    // Customer CRUD & Due Payments
    Route::apiResource('customers', \App\Http\Controllers\Api\CustomerApiController::class);
    Route::post('/customers/{id}/collect-payment', [\App\Http\Controllers\Api\CustomerApiController::class, 'recordPayment']);

    // Supplier CRUD & Due Payments
    Route::apiResource('suppliers', \App\Http\Controllers\Api\SupplierApiController::class);
    Route::post('/suppliers/{id}/pay-due', [\App\Http\Controllers\Api\SupplierApiController::class, 'recordPayment']);

    // Sale CRUD
    Route::apiResource('sales', \App\Http\Controllers\Api\SaleApiController::class);
    Route::post('/sales/{id}/return', [\App\Http\Controllers\Api\SaleApiController::class, 'returnSale']);
    Route::get('/sales/{id}/invoice', [\App\Http\Controllers\Api\InvoiceApiController::class, 'generatePDF']);
    Route::post('/sales/{id}/email-invoice', [\App\Http\Controllers\Api\InvoiceApiController::class, 'emailSaleInvoice']);

    // Purchase CRUD
    Route::apiResource('purchases', \App\Http\Controllers\Api\PurchaseApiController::class);
    Route::get('/purchases/{id}/invoice', [\App\Http\Controllers\Api\InvoiceApiController::class, 'generatePurchasePDF']);
    Route::post('/purchases/{id}/email-invoice', [\App\Http\Controllers\Api\InvoiceApiController::class, 'emailPurchaseInvoice']);
    Route::post('/purchases/{id}/return', [\App\Http\Controllers\Api\PurchaseApiController::class, 'returnPurchase']);

    // CashBook
    Route::apiResource('cashbooks', \App\Http\Controllers\Api\CashBookApiController::class);

    // Bank accounts
    Route::apiResource('bank-accounts', \App\Http\Controllers\Api\BankAccountApiController::class);

    // Expenses
    Route::apiResource('expenses', \App\Http\Controllers\Api\ExpenseApiController::class);

    // Reports
    Route::get('/reports', [\App\Http\Controllers\Api\ReportApiController::class, 'index']);
});

// ShopOwner Common API Authentication Module
Route::prefix('v1/shopowner')->group(function () {
    Route::post('/register', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'register']);
    Route::post('/verify-otp', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'verifyOtp']);
    Route::post('/resend-otp', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'resendOtp']);
    Route::post('/login', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'login']);
    Route::post('/forgot-password', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'forgotPassword']);
    Route::post('/reset-password', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/shop-setup', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'shopSetup']);
        Route::post('/change-password', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'changePassword']);
        Route::post('/logout', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'logout']);
        Route::get('/profile', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'profile']);
        Route::post('/profile', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'updateProfile']);

        // Subscription plans & current plan status
        Route::get('/subscription-plans', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'plans']);
        Route::get('/subscription', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'current']);
        Route::post('/subscription/cancel', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'cancel']);
        Route::post('/subscription/upgrade', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'upgrade']);
        Route::post('/subscription/create-order', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'createOrder']);
        Route::post('/subscription/verify-payment', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'verifyPayment']);

        // Support tickets (own tickets only)
        Route::apiResource('support-tickets', \App\Http\Controllers\Api\ShopOwner\SupportTicketApiController::class)
            ->except(['edit', 'create']);
    });
});


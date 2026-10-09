<?php

// -- Public routes (no authentication required) ------------------------------
Route::prefix('public')->group(function () {
    Route::get('/plans', [\App\Http\Controllers\Api\PublicApiController::class, 'plans']);
    Route::get('/addons', [\App\Http\Controllers\Api\PublicApiController::class, 'addons']);
});

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AppSettingController;

// Public mobile client system API configuration endpoint
Route::get('/v1/app-config', [AppSettingController::class, 'getPublicConfig']);

// Sanctum authenticated user info route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Shop-owner login/registration lives only under /v1/shopowner (OTP verification, suspension
// check, device limit). The old unchecked /v1/auth/login and /v1/auth/register were removed.

// API version 1 routes with authentication and shop scope
Route::prefix('v1')->middleware(['auth:sanctum', 'account.active', 'shop.scope', 'idempotency'])->group(function () {
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

    // Stock movement audit log (real server-side history, replacing each client's local-only log)
    Route::get('/stock-movements', [\App\Http\Controllers\Api\StockMovementApiController::class, 'index']);
    Route::post('/products/{id}/adjust-stock', [\App\Http\Controllers\Api\StockMovementApiController::class, 'adjust']);

    // Every customer's / supplier's custom price sheet in one call (offline caching)
    Route::get('/product-prices/customers', [\App\Http\Controllers\Api\CustomerProductPriceApiController::class, 'bulk']);
    Route::get('/product-prices/suppliers', [\App\Http\Controllers\Api\SupplierProductPriceApiController::class, 'bulk']);

    // Customer CRUD & Due Payments
    Route::apiResource('customers', \App\Http\Controllers\Api\CustomerApiController::class);
    Route::post('/customers/{id}/collect-payment', [\App\Http\Controllers\Api\CustomerApiController::class, 'recordPayment']);
    Route::get('/customers/{id}/product-prices', [\App\Http\Controllers\Api\CustomerProductPriceApiController::class, 'index']);
    Route::post('/customers/{id}/product-prices', [\App\Http\Controllers\Api\CustomerProductPriceApiController::class, 'update']);

    // Supplier CRUD & Due Payments
    Route::apiResource('suppliers', \App\Http\Controllers\Api\SupplierApiController::class);
    Route::post('/suppliers/{id}/pay-due', [\App\Http\Controllers\Api\SupplierApiController::class, 'recordPayment']);
    Route::get('/suppliers/{id}/product-prices', [\App\Http\Controllers\Api\SupplierProductPriceApiController::class, 'index']);
    Route::post('/suppliers/{id}/product-prices', [\App\Http\Controllers\Api\SupplierProductPriceApiController::class, 'update']);

    // Sale CRUD
    Route::apiResource('sales', \App\Http\Controllers\Api\SaleApiController::class);
    Route::post('/sales/{id}/cancel', [\App\Http\Controllers\Api\SaleApiController::class, 'cancel']);
    Route::post('/sales/{id}/return', [\App\Http\Controllers\Api\SaleApiController::class, 'returnSale']);
    Route::get('/sales/{id}/invoice', [\App\Http\Controllers\Api\InvoiceApiController::class, 'generatePDF']);
    Route::post('/sales/{id}/email-invoice', [\App\Http\Controllers\Api\InvoiceApiController::class, 'emailSaleInvoice']);
    Route::post('/sales/{id}/whatsapp', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'sendSaleInvoice']);

    // Purchase CRUD
    Route::apiResource('purchases', \App\Http\Controllers\Api\PurchaseApiController::class);
    Route::post('/purchases/{id}/cancel', [\App\Http\Controllers\Api\PurchaseApiController::class, 'cancel']);
    Route::get('/purchases/{id}/invoice', [\App\Http\Controllers\Api\InvoiceApiController::class, 'generatePurchasePDF']);
    Route::post('/purchases/{id}/email-invoice', [\App\Http\Controllers\Api\InvoiceApiController::class, 'emailPurchaseInvoice']);
    Route::post('/purchases/{id}/whatsapp', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'sendPurchaseInvoice']);
    Route::post('/purchases/{id}/return', [\App\Http\Controllers\Api\PurchaseApiController::class, 'returnPurchase']);

    // WhatsApp messaging: settings, credits wallet & pack purchase
    Route::get('/whatsapp/settings', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'settings']);
    Route::post('/whatsapp/settings', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'updateSettings']);
    Route::post('/whatsapp/reminders/{type}/{id}', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'sendReminder'])->whereIn('type', ['customer', 'supplier']);
    Route::get('/whatsapp/messages', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'messages']);
    Route::get('/whatsapp/payment-claims', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'paymentClaims']);
    Route::post('/whatsapp/payment-claims/{id}/confirm', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'confirmPaymentClaim']);
    Route::post('/whatsapp/payment-claims/{id}/reject', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'rejectPaymentClaim']);
    Route::get('/whatsapp/wallet', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'wallet']);
    Route::post('/whatsapp/packs/{id}/purchase', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'purchasePack']);
    Route::post('/whatsapp/packs/verify-payment', [\App\Http\Controllers\Api\WhatsAppApiController::class, 'verifyPackPayment']);

    // CashBook
    Route::apiResource('cashbooks', \App\Http\Controllers\Api\CashBookApiController::class)->except(['update']);

    // Bank transfers (Deposit / Withdraw)
    Route::post('/bank-transfers', [\App\Http\Controllers\Api\BankTransferApiController::class, 'store']);

    // Register closures (Daily cash count)
    Route::get('/register-closures/current-status', [\App\Http\Controllers\Api\RegisterClosureApiController::class, 'currentStatus']);
    Route::apiResource('register-closures', \App\Http\Controllers\Api\RegisterClosureApiController::class)->only(['index', 'store']);

    // Bank account: one default account per shop — view and edit its details only
    Route::apiResource('bank-accounts', \App\Http\Controllers\Api\BankAccountApiController::class)->only(['index', 'show', 'update']);

    // Expenses & Expense Categories
    Route::apiResource('expense-categories', \App\Http\Controllers\Api\ExpenseCategoryApiController::class);
    Route::apiResource('expenses', \App\Http\Controllers\Api\ExpenseApiController::class);

    // Reports
    Route::get('/reports', [\App\Http\Controllers\Api\ReportApiController::class, 'index']);

    // Credit Notes
    Route::apiResource('credit-notes', \App\Http\Controllers\Api\CreditNoteApiController::class)->only(['index', 'show']);

    // Returnable containers & deposits — optional module, switched on per shop by the admin
    Route::middleware('shop.feature:containers')->group(function () {
        Route::get('/containers/summary', [\App\Http\Controllers\Api\ContainerApiController::class, 'summary']);
        Route::get('/containers/customers', [\App\Http\Controllers\Api\ContainerApiController::class, 'customers']);
        Route::get('/containers/customers/{customerId}', [\App\Http\Controllers\Api\ContainerApiController::class, 'customer']);
        Route::get('/containers/entries', [\App\Http\Controllers\Api\ContainerApiController::class, 'index']);
        Route::post('/containers/entries', [\App\Http\Controllers\Api\ContainerApiController::class, 'store']);
        Route::get('/containers/entries/{id}', [\App\Http\Controllers\Api\ContainerApiController::class, 'show']);
        Route::post('/containers/entries/{id}/reverse', [\App\Http\Controllers\Api\ContainerApiController::class, 'reverse']);
        Route::apiResource('container-types', \App\Http\Controllers\Api\ContainerTypeApiController::class);
        Route::post('/container-types/{id}/adjust-stock', [\App\Http\Controllers\Api\ContainerTypeApiController::class, 'adjustStock']);
    });
});

// WhatsApp Cloud API callbacks — called by Meta, so no user auth. The webhook checks Meta's
// signature; invoice PDFs are only reachable through temporary signed links.
Route::prefix('v1/whatsapp')->group(function () {
    Route::get('/webhook', [\App\Http\Controllers\Api\WhatsAppWebhookController::class, 'verify']);
    Route::post('/webhook', [\App\Http\Controllers\Api\WhatsAppWebhookController::class, 'handle']);
    Route::get('/invoices/sale/{id}', [\App\Http\Controllers\Api\InvoiceApiController::class, 'signedSalePDF'])
        ->middleware('signed:relative')->name('whatsapp.invoice.sale');
    Route::get('/invoices/purchase/{id}', [\App\Http\Controllers\Api\InvoiceApiController::class, 'signedPurchasePDF'])
        ->middleware('signed:relative')->name('whatsapp.invoice.purchase');
});

// ShopOwner Common API Authentication Module
Route::prefix('v1/shopowner')->group(function () {
    Route::post('/register', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'register']);
    Route::post('/verify-otp', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'verifyOtp']);
    Route::post('/resend-otp', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'resendOtp']);
    Route::post('/login', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'login']);
    Route::post('/forgot-password', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'forgotPassword']);
    Route::post('/reset-password', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'resetPassword']);
    Route::post('/razorpay/webhook', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'handleWebhook']);

    Route::middleware(['auth:sanctum', 'account.active'])->group(function () {
        Route::post('/shop-setup', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'shopSetup']);
        Route::post('/change-password', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'changePassword']);
        Route::post('/logout', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'logout']);
        Route::get('/profile', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'profile']);
        Route::post('/profile', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'updateProfile']);
        Route::get('/pincode/{pincode}', [\App\Http\Controllers\Api\ShopOwner\AuthApiController::class, 'getPincodeDetails']);

        // Subscription plans & current plan status
        Route::get('/subscription-plans', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'plans']);
        Route::get('/subscription', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'current']);
        Route::post('/subscription/cancel', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'cancel']);
        Route::post('/subscription/upgrade', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'upgrade']);
        Route::post('/subscription/create-order', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'createOrder']);
        Route::post('/subscription/verify-payment', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'verifyPayment']);
        Route::post('/subscription/verify', [\App\Http\Controllers\Api\ShopOwner\SubscriptionApiController::class, 'verifyPayment']);

        // Add-ons (Shop / Website) — available from both the web panel and the app
        Route::get('/add-ons', [\App\Http\Controllers\Api\ShopOwner\AddOnApiController::class, 'plans']);
        Route::get('/add-ons/current', [\App\Http\Controllers\Api\ShopOwner\AddOnApiController::class, 'current']);
        Route::post('/add-ons/purchase', [\App\Http\Controllers\Api\ShopOwner\AddOnApiController::class, 'purchase']);
        Route::post('/add-ons/verify-payment', [\App\Http\Controllers\Api\ShopOwner\AddOnApiController::class, 'verifyPayment']);
        Route::post('/add-ons/{id}/cancel', [\App\Http\Controllers\Api\ShopOwner\AddOnApiController::class, 'cancel']);

        // Support tickets (own tickets only)
        Route::post('support-tickets/{id}/reply', [\App\Http\Controllers\Api\ShopOwner\SupportTicketApiController::class, 'reply']);
        Route::apiResource('support-tickets', \App\Http\Controllers\Api\ShopOwner\SupportTicketApiController::class)
        ->names('shopowner.support-tickets')
            ->except(['edit', 'create']);

        // Notification inbox (own notifications only) — fed by the admin's Broadcast Center
        Route::get('/notifications', [\App\Http\Controllers\Api\ShopOwner\NotificationApiController::class, 'index']);
        Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\ShopOwner\NotificationApiController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\ShopOwner\NotificationApiController::class, 'markRead']);
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\ShopOwner\NotificationApiController::class, 'markAllRead']);
        Route::delete('/notifications/{id}', [\App\Http\Controllers\Api\ShopOwner\NotificationApiController::class, 'destroy']);
    });
});

// Direct v1 Support Tickets endpoint for mobile app clients
Route::prefix('v1')->middleware(['auth:sanctum', 'account.active'])->group(function () {
    Route::post('support-tickets/{id}/reply', [\App\Http\Controllers\Api\ShopOwner\SupportTicketApiController::class, 'reply']);
    Route::apiResource('support-tickets', \App\Http\Controllers\Api\ShopOwner\SupportTicketApiController::class)
    ->names('v1.support-tickets')
        ->except(['edit', 'create']);

    // Notification inbox — same controller as the shopowner group, mirrored here for mobile clients
    Route::get('/notifications', [\App\Http\Controllers\Api\ShopOwner\NotificationApiController::class, 'index']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\ShopOwner\NotificationApiController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\ShopOwner\NotificationApiController::class, 'markRead']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\ShopOwner\NotificationApiController::class, 'markAllRead']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\Api\ShopOwner\NotificationApiController::class, 'destroy']);
});




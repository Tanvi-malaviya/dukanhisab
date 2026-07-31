<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\Admin\InvoiceSettingController;
use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\AuditLogController;

// Main customer landing page placeholder
Route::get('/', function () {
    return view('app');
});

// Admin Auth Routes
Route::get('admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Super Admin Protected Panel
Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => ['admin', 'audit']
], function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // User Management
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('users/{id}/subscription', [UserController::class, 'updateSubscription'])->name('users.subscription');
    Route::get('users/{id}/backup', [UserController::class, 'backup'])->name('users.backup');
    Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::post('users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset_password');
    Route::get('users/{id}/login-as', [UserController::class, 'loginAs'])->name('users.login_as');
    Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::delete('users/devices/{id}', [UserController::class, 'revokeDevice'])->name('users.device.revoke');

    // Shop Management
    Route::get('shops', [ShopController::class, 'index'])->name('shops.index');
    Route::post('shops', [ShopController::class, 'store'])->name('shops.store');
    Route::get('shops/{id}', [ShopController::class, 'show'])->name('shops.show');
    Route::put('shops/{id}', [ShopController::class, 'update'])->name('shops.update');
    Route::post('shops/{id}/toggle', [ShopController::class, 'toggleStatus'])->name('shops.toggle');
    Route::post('shops/{id}/subscription', [ShopController::class, 'updateSubscription'])->name('shops.subscription');

    // Subscription Management
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('subscriptions/plan', [SubscriptionController::class, 'storePlan'])->name('subscriptions.plan.store');
    Route::post('subscriptions/plan/{id}', [SubscriptionController::class, 'updatePlan'])->name('subscriptions.plan.update');
    Route::delete('subscriptions/plan/{id}', [SubscriptionController::class, 'destroyPlan'])->name('subscriptions.plan.destroy');
    Route::post('subscriptions/{id}/expire', [SubscriptionController::class, 'expireSubscription'])->name('subscriptions.expire');
    Route::post('subscriptions/{id}/extend', [SubscriptionController::class, 'extendSubscription'])->name('subscriptions.extend');
    Route::post('subscriptions/{id}/reactivate', [SubscriptionController::class, 'reactivateSubscription'])->name('subscriptions.reactivate');

    // Payment & Refund Management
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/{id}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    Route::post('refunds/{id}/status', [PaymentController::class, 'updateRefundStatus'])->name('refunds.update_status');

    // Reports Module
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // App Configuration Settings
    Route::get('settings/app', [AppSettingController::class, 'index'])->name('settings.app');
    Route::post('settings/app', [AppSettingController::class, 'update'])->name('settings.app.update');

    // Invoice Layout Settings
    Route::get('settings/invoice', [InvoiceSettingController::class, 'index'])->name('settings.invoice');
    Route::post('settings/invoice', [InvoiceSettingController::class, 'update'])->name('settings.invoice.update');

    // Advertisement Management
    Route::get('ads', [AdvertisementController::class, 'index'])->name('ads.index');
    Route::post('ads', [AdvertisementController::class, 'store'])->name('ads.store');
    Route::post('ads/{id}', [AdvertisementController::class, 'update'])->name('ads.update');
    Route::post('ads/{id}/toggle', [AdvertisementController::class, 'toggleStatus'])->name('ads.toggle');
    Route::delete('ads/{id}', [AdvertisementController::class, 'destroy'])->name('ads.destroy');

    // Notification Center
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/send', [NotificationController::class, 'send'])->name('notifications.send');

    // Support Helpdesk Module
    Route::get('support', [SupportTicketController::class, 'index'])->name('support.index');
    Route::post('support/{id}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');
    Route::match(['get', 'post'], 'support/{id}/status/{status}', [SupportTicketController::class, 'updateStatus'])->name('support.status');
    Route::delete('support/{id}', [SupportTicketController::class, 'destroy'])->name('support.destroy');

    // System Backups
    Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('backups', [BackupController::class, 'create'])->name('backups.create');
    Route::get('backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
    Route::post('backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
    Route::delete('backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');

    // Audit Logs Viewer
    Route::get('logs', [AuditLogController::class, 'index'])->name('logs.index');
});

// Add SPA wildcard route for the Shop Web Panel
Route::get('/dukanhisab/{any?}', function () {
    return view('app');
})->where('any', '.*');

// ShopOwner Web Panel SPA route
Route::get('/shopowner/{any?}', function () {
    return view('shopowner');
})->where('any', '.*');



// Fallback/wildcard route for subdirectory installations where prefix is stripped (e.g. /sales, /products)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!admin|api|shopowner).*$');

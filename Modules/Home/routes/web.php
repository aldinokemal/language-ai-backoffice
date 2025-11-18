<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Route;
use Modules\Home\Http\Controllers\DashboardController;
use Modules\Home\Http\Controllers\MyAccountController;
use Modules\Home\Http\Controllers\NotificationController;
use Modules\Home\Http\Controllers\SwitchController;

Route::middleware([
    Authenticate::class,
    EnsureEmailIsVerified::class,
])->group(function () {
    Route::redirect('/', '/dashboard');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications');
        Route::get('/test', [NotificationController::class, 'test'])->name('notification.test');
        Route::get('/open/{id}', [NotificationController::class, 'open'])->name('notification.open');
        Route::get('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notification.markAllAsRead');
        Route::post('/store-token', [NotificationController::class, 'storeToken'])->name('notification.storeToken');
    });

    Route::prefix('my-account')->group(function () {
        Route::get('/', [MyAccountController::class, 'index'])->name('my-account.profile');
        Route::post('/update-basic-settings', [MyAccountController::class, 'updateBasicSettings'])->name('my-account.updateBasicSettings');
        Route::post('/update-password', [MyAccountController::class, 'updatePassword'])->name('my-account.updatePassword');
        Route::post('/update-account-id', [MyAccountController::class, 'updateAccountID'])->name('my-account.updateAccountID');
        Route::post('/send-email-otp', [MyAccountController::class, 'sendEmailOTP'])->name('my-account.sendEmailOTP')->middleware('throttle:3,1');
    });

    Route::get('/switch-organization', [SwitchController::class, 'switchOrganization'])
        ->name('switch.organization');
    Route::get('/switch-role', [SwitchController::class, 'switchRole'])
        ->name('switch.role');
});

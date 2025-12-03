<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\ForgotPasswordController;
use Modules\Auth\Http\Controllers\LoginController;

Route::prefix('auth')->middleware(RedirectIfAuthenticated::class)->group(function () {
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/login', [LoginController::class, 'loginPost'])->name('login.post');

    Route::get('forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPasswordPost'])->name('forgot-password.post');

    Route::get('forgot-password-success', [ForgotPasswordController::class, 'forgotPasswordSuccess'])->name('forgot-password-success');
    Route::post('resend-email', [ForgotPasswordController::class, 'resendEmail'])->name('resend-email');

    Route::get('reset-password/{token}', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');
    Route::post('reset-password', [ForgotPasswordController::class, 'resetPasswordPost'])->name('password.update');
});

Route::prefix('auth')->middleware(Authenticate::class)->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

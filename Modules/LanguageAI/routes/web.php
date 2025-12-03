<?php

use App\Http\Middleware\CachedAuth;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Route;
use Modules\LanguageAI\Http\Controllers\LaiDashboardController;
use Modules\LanguageAI\Http\Controllers\LaiPlanController;
use Modules\LanguageAI\Http\Controllers\LaiUserController;

Route::middleware([CachedAuth::class, EnsureEmailIsVerified::class])
    ->prefix('language-ai')
    ->group(function () {
        Route::prefix('dashboard')
            ->name('language-ai.dashboard.')
            ->group(function () {
                Route::get('/', [LaiDashboardController::class, 'index'])->name('index');
                Route::post('/ajax/metrics', [LaiDashboardController::class, 'ajaxMetrics'])->name('ajax.metrics');
                Route::post('/ajax/chart-data', [LaiDashboardController::class, 'ajaxChartData'])->name('ajax.chart-data');
            });

        Route::prefix('users')
            ->name('language-ai.users.')
            ->group(function () {
                Route::get('/', [LaiUserController::class, 'index'])->name('index');
                Route::post('/ajax/datagrid', [LaiUserController::class, 'ajaxDatagrid'])->name('ajax.datagrid');
                Route::get('/{id}', [LaiUserController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [LaiUserController::class, 'edit'])->name('edit');
                Route::put('/{id}', [LaiUserController::class, 'update'])->name('update');
                Route::post('/{id}/ajax/chat-usage', [LaiUserController::class, 'ajaxChatUsage'])->name('ajax.chat-usage');
            });

        Route::prefix('plans')
            ->name('language-ai.plans.')
            ->group(function () {
                Route::get('/', [LaiPlanController::class, 'index'])->name('index');
                Route::post('/ajax/datagrid', [LaiPlanController::class, 'ajaxDatagrid'])->name('ajax.datagrid');
                Route::get('/upsert/{id?}', [LaiPlanController::class, 'upsert'])->name('upsert');
                Route::post('/save/{id?}', [LaiPlanController::class, 'save'])->name('save');
                Route::delete('/{id}', [LaiPlanController::class, 'destroy'])->name('destroy');
            });
    });

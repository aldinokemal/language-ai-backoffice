<?php

use Illuminate\Support\Facades\Route;
use Modules\LanguageAI\Http\Controllers\LanguageAIController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('languageais', LanguageAIController::class)->names('languageai');
});

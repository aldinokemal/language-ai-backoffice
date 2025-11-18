<?php

use Illuminate\Support\Facades\Route;
use Modules\LanguageAI\Http\Controllers\LanguageAIController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('languageais', LanguageAIController::class)->names('languageai');
});

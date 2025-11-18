<?php

use Illuminate\Support\Facades\Route;
use Modules\Public\Http\Controllers\Api\EventRegistrationController;

Route::prefix('event')
    ->controller(EventRegistrationController::class)
    ->group(function () {
        Route::get('{id}', 'eventDetail')->name('public.event.detail');
        Route::get('{id}/participant-image', 'eventParticipantImage');
        Route::post('{id}/registration', 'eventRegistration');
        Route::post('{id}/presence/face-recognition/{participant_id}', 'eventPresenceFaceRecognition');
    });

<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\VenueController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');

    Route::group([
        'middleware' => 'auth:api',
        'prefix' => 'venues',
    ], function () {
        Route::get('/', [VenueController::class, 'index']);
        Route::get('/{venue}', [VenueController::class, 'show']);
        Route::post('/', [VenueController::class, 'store']);
        Route::patch('/{venue}', [VenueController::class, 'update']);
        Route::delete('/{venueId}', [VenueController::class, 'destroy']);
    });

    Route::group([
        'middleware' => 'auth:api',
        'prefix' => 'events',
    ], function () {
        Route::get('/', [EventController::class, 'index']);
        Route::get('/{eventId}', [EventController::class, 'show']);
        Route::post('/', [EventController::class, 'store']);
        Route::patch('/{event}', [EventController::class, 'update']);
        Route::delete('/{eventId}', [EventController::class, 'destroy']);
    });
});

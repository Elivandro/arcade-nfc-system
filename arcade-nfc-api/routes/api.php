<?php

use App\Http\Controllers\CardController;
use App\Http\Middleware\EnsureMachineIsValid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware([EnsureMachineIsValid::class])
    ->prefix('v1/arcade')
    ->group(function () {
        Route::get('/reader-mode', [CardController::class, 'mode']);
        Route::post('/register-card', [CardController::class, 'store']);
        Route::post('/validate-play', [CardController::class, 'cardValidate']);
    });
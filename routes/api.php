<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AvailableSlotsController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return new UserResource($request->user()->load('branch'));
    });

    Route::get('/doctors/{doctor}/available-slots', AvailableSlotsController::class);

    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);

});

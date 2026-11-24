<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AvailableSlotsController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return new UserResource($request->user()->load('branch'));
    });

    // Doctors
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/{doctor}/available-slots', AvailableSlotsController::class);

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);

    // Medical Records (nested under appointments)
    Route::get('/appointments/{appointment}/medical-record', [MedicalRecordController::class, 'show']);
    Route::post('/appointments/{appointment}/medical-record', [MedicalRecordController::class, 'store']);
    Route::patch('/appointments/{appointment}/medical-record', [MedicalRecordController::class, 'update']);

    // Attachments on medical records
    Route::post('/medical-records/{medicalRecord}/attachments', [AttachmentController::class, 'store']);
    Route::delete('/medical-records/{medicalRecord}/attachments/{attachment}', [AttachmentController::class, 'destroy']);

});

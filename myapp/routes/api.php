<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\TimeSlotController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Api\Admin\TimeSlotController as AdminTimeSlotController;
use App\Http\Controllers\Api\Admin\DisabledDateController;
use App\Http\Controllers\Api\Admin\ConflictAttemptController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

Route::get('/time-slots', [TimeSlotController::class, 'index']);
Route::get('/time-slots/available', [TimeSlotController::class, 'available']);
Route::get('/disabled-dates', [TimeSlotController::class, 'disabledDates']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('appointments', AppointmentController::class);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    Route::get('/appointments', [AdminAppointmentController::class, 'index']);
    Route::get('/appointments/pending', [AdminAppointmentController::class, 'pending']);
    Route::get('/appointments/history', [AdminAppointmentController::class, 'history']);
    Route::get('/appointments/{appointment}', [AdminAppointmentController::class, 'show']);
    Route::post('/appointments/{appointment}/approve', [AdminAppointmentController::class, 'approve']);
    Route::post('/appointments/{appointment}/reject', [AdminAppointmentController::class, 'reject']);

    Route::apiResource('time-slots', AdminTimeSlotController::class);
    Route::post('/time-slots/{timeSlot}/toggle', [AdminTimeSlotController::class, 'toggle']);

    Route::get('/disabled-dates', [DisabledDateController::class, 'index']);
    Route::post('/disabled-dates', [DisabledDateController::class, 'store']);
    Route::post('/disabled-dates/full-day', [DisabledDateController::class, 'disableFullDay']);
    Route::delete('/disabled-dates/{disabledDate}', [DisabledDateController::class, 'destroy']);

    Route::get('/conflict-attempts', [ConflictAttemptController::class, 'index']);
    Route::get('/conflict-attempts/stats', [ConflictAttemptController::class, 'stats']);
});

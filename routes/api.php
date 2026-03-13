<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\ActivityTypeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. They are stateless and intended
| for mobile app consumption.
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');

    // Activity Types
    Route::get('activity-types', [ActivityTypeController::class, 'index'])->name('api.activity-types.index');
    Route::post('activity-types', [ActivityTypeController::class, 'store'])->name('api.activity-types.store');
    Route::get('activity-types/{activity_type}', [ActivityTypeController::class, 'show'])->name('api.activity-types.show');
    Route::put('activity-types/{activity_type}', [ActivityTypeController::class, 'update'])->name('api.activity-types.update');
    Route::delete('activity-types/{activity_type}', [ActivityTypeController::class, 'destroy'])->name('api.activity-types.destroy');

    // Participants
    Route::get('participants/form-options', [ParticipantController::class, 'formOptions'])->name('api.participants.form-options');
    Route::get('participants', [ParticipantController::class, 'index'])->name('api.participants.index');
    Route::post('participants', [ParticipantController::class, 'store'])->name('api.participants.store');
    Route::get('participants/{participant}', [ParticipantController::class, 'show'])->name('api.participants.show');
    Route::put('participants/{participant}', [ParticipantController::class, 'update'])->name('api.participants.update');
    Route::delete('participants/{participant}', [ParticipantController::class, 'destroy'])->name('api.participants.destroy');

    // Activities
    Route::get('activities', [ActivityController::class, 'index'])->name('api.activities.index');
    Route::post('activities', [ActivityController::class, 'store'])->name('api.activities.store');
    Route::get('activities/{activity}', [ActivityController::class, 'show'])->name('api.activities.show');
    Route::put('activities/{activity}', [ActivityController::class, 'update'])->name('api.activities.update');
    Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('api.activities.destroy');

    // Attendance
    Route::get('activities/{activity}/attendance', [AttendanceController::class, 'index'])
        ->name('api.attendance.index');
    Route::post('activities/{activity}/attendance', [AttendanceController::class, 'store'])
        ->name('api.attendance.store');

    // Reports
    Route::get('reports/quarterly', [ReportController::class, 'quarterlyReport'])
        ->name('api.reports.quarterly');
});

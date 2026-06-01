<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingRequestController;
use App\Http\Controllers\Api\LoadController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\SavedLoadController;
use Illuminate\Support\Facades\Route;

Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/loads', [LoadController::class, 'index']);
    Route::post('/loads', [LoadController::class, 'store']);
    Route::get('/loads/{id}', [LoadController::class, 'show']);
    Route::put('/loads/{load}', [LoadController::class, 'update']);
    Route::delete('/loads/{load}', [LoadController::class, 'destroy']);
    Route::patch('/loads/{load}/complete', [LoadController::class, 'complete']);
    Route::patch('/loads/{load}/cancel', [LoadController::class, 'cancel']);
    Route::get('/my-loads', [LoadController::class, 'myLoads']);

    Route::get('/routes', [RouteController::class, 'index']);
    Route::get('/cities', [RouteController::class, 'cities']);
    Route::get('/cities/search', [RouteController::class, 'searchCities']);

    Route::get('/my-routes', [RouteController::class, 'myRoutes']);
    Route::post('/my-routes', [RouteController::class, 'store']);
    Route::get('/my-routes/{id}', [RouteController::class, 'show']);
    Route::put('/my-routes/{id}', [RouteController::class, 'update']);
    Route::delete('/my-routes/{id}', [RouteController::class, 'destroy']);

    Route::get('/matches', [MatchController::class, 'index']);

    Route::get('/saved-loads', [SavedLoadController::class, 'index']);
    Route::post('/saved-loads/{load}', [SavedLoadController::class, 'toggle']);

    Route::post('/bookings', [BookingRequestController::class, 'store']);
    Route::get('/bookings/my-requests', [BookingRequestController::class, 'myRequests']);
    Route::get('/bookings/received', [BookingRequestController::class, 'receivedRequests']);
    Route::put('/bookings/{id}/accept', [BookingRequestController::class, 'accept']);
    Route::put('/bookings/{id}/reject', [BookingRequestController::class, 'reject']);
    Route::put('/bookings/{id}/cancel', [BookingRequestController::class, 'cancel']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/push-tokens', [NotificationController::class, 'registerToken']);
    Route::delete('/push-tokens', [NotificationController::class, 'unregisterToken']);
});

<?php

use App\Http\Controllers\BookingController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/bookings/health', fn () => response()->json(['success' => true, 'service' => 'booking-service']));
Route::post('/bookings/midtrans-notification', [BookingController::class, 'midtransNotification']);
Route::get('/bookings/{booking}/simulate-payment', [BookingController::class, 'simulatePayment']);
Route::post('/bookings/{booking}/payment-callback', [BookingController::class, 'paymentCallback']);

Route::middleware(AuthMiddleware::class)->group(function () {
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/my-bookings', [BookingController::class, 'myBookings']);
    Route::get('/bookings/admin/all', [BookingController::class, 'all']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
});

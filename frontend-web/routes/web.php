<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LandingPageController;
use App\Http\Middleware\AuthCheckMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('landing');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/bookings/payment-finish/{booking}', [BookingController::class, 'paymentFinish'])->name('bookings.payment-finish');

Route::middleware(AuthCheckMiddleware::class)->group(function () {
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/admin/bookings', [BookingController::class, 'admin'])->name('bookings.admin');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/update-payment', [BookingController::class, 'updatePayment'])->name('bookings.update-payment');
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('dashboard.user');
    Route::get('/admin', [DashboardController::class, 'adminDashboard'])->name('dashboard.admin');
    Route::get('/admin/verify', [AdminController::class, 'verifyPage'])->name('admin.verify');
    Route::post('/admin/verify', [AdminController::class, 'verifyTicket'])->name('admin.verify.post');
    Route::get('/admin/events', [AdminController::class, 'eventManagement'])->name('admin.events');
    Route::get('/admin/tickets', [AdminController::class, 'ticketManagement'])->name('admin.tickets');
    Route::get('/admin/transactions', [AdminController::class, 'transactionManagement'])->name('admin.transactions');
});

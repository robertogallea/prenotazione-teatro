<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SeatController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [SeatController::class, 'index'])->name('dashboard');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::get('/export/csv', [ExportController::class, 'csv'])->name('export.csv');
    Route::get('/export/pdf', [ExportController::class, 'pdf'])->name('export.pdf');
});

require __DIR__.'/settings.php';

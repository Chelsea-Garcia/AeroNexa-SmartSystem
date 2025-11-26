<?php

use Illuminate\Support\Facades\Route;

// PSA Controllers
use App\Http\Controllers\api\v1\psa\FlightController;
use App\Http\Controllers\api\v1\psa\PassengerController;
use App\Http\Controllers\api\v1\psa\BookingController;

// AeroPay Controller
use App\Http\Controllers\api\v1\aeropay\TransactionController;

use App\Http\Controllers\api\v1\aureliya\PropertyController;
use App\Http\Controllers\api\v1\aureliya\AmenityController;
use App\Http\Controllers\api\v1\aureliya\ReviewController;
use App\Http\Controllers\api\v1\aureliya\BookingController as AureliyaBookingController;
/*
|--------------------------------------------------------------------------
| PSA API
|--------------------------------------------------------------------------
*/

Route::prefix('psa')->group(function () {

    // Flights
    Route::get('/flights', [FlightController::class, 'index']);
    Route::get('/flights/{id}', [FlightController::class, 'show']);
    Route::get('/flights/search', [FlightController::class, 'search']);

    // Passengers
    Route::post('/passengers', [PassengerController::class, 'store']);
    Route::get('/passengers/user/{id}', [PassengerController::class, 'showByUser']);
    Route::put('/passengers/{id}', [PassengerController::class, 'update']);

    // Bookings
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{user_id}', [BookingController::class, 'userBookings']);
    Route::get('/booking/{id}', [BookingController::class, 'show']);
    Route::put('/booking/{id}/passenger', [BookingController::class, 'updatePassenger']);
    Route::post('/booking/{id}/cancel', [BookingController::class, 'cancel']);
});

/*
|--------------------------------------------------------------------------
| AEROPAY API
|--------------------------------------------------------------------------
*/

Route::prefix('aeropay')->group(function () {

    Route::post('/charge', [TransactionController::class, 'charge']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    Route::get('/transactions/user/{user_id}', [TransactionController::class, 'userTransactions']);
    Route::get('/transactions/status/{status}', [TransactionController::class, 'filterByStatus']);
    Route::put('/transactions/{id}/status', [TransactionController::class, 'updateStatus']);

    Route::post('/webhook', [TransactionController::class, 'webhook']);
});

Route::prefix('aureliya')->group(function () {

    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/properties/{id}', [PropertyController::class, 'show']);

    Route::get('/amenities', [AmenityController::class, 'index']);
    Route::get('/amenities/{id}', [AmenityController::class, 'show']);

    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{id}', [ReviewController::class, 'show']);
    Route::post('/reviews', [ReviewController::class, 'store']);   // create
    Route::put('/reviews/{id}', [ReviewController::class, 'update']); // update only

    Route::get('/bookings', [AureliyaBookingController::class, 'index']);
    Route::get('/bookings/{id}', [AureliyaBookingController::class, 'show']);
    Route::post('/bookings', [AureliyaBookingController::class, 'store']);   // create
    Route::put('/bookings/{id}', [AureliyaBookingController::class, 'update']); // update only
});

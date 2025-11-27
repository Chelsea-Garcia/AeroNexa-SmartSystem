<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PSA
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\api\v1\psa\FlightController;
use App\Http\Controllers\api\v1\psa\PassengerController;
use App\Http\Controllers\api\v1\psa\BookingController as PsaBookingController;

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
    Route::post('/bookings', [PsaBookingController::class, 'store']);
    Route::get('/bookings/{user_id}', [PsaBookingController::class, 'userBookings']);
    Route::get('/booking/{id}', [PsaBookingController::class, 'show']);
    Route::put('/booking/{id}/passenger', [PsaBookingController::class, 'updatePassenger']);
    Route::post('/booking/{id}/cancel', [PsaBookingController::class, 'cancel']);

    // ⭐ NEW — Update Payment Status
    Route::put('/booking/{id}/status', [PsaBookingController::class, 'updateStatus']);
});


/*
|--------------------------------------------------------------------------
| AEROPAY (GLOBAL PAYMENT PROVIDER)
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\api\v1\aeropay\TransactionController;

Route::prefix('aeropay')->group(function () {

    // Charge from any microservice
    Route::post('/charge', [TransactionController::class, 'charge']);

    // Read transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::get('/transactions/user/{user_id}', [TransactionController::class, 'userTransactions']);
    Route::get('/transactions/status/{status}', [TransactionController::class, 'filterByStatus']);

    // Update payment status
    Route::put('/transactions/{id}/status', [TransactionController::class, 'updateStatus']);

    // Webhook for all services (PSA / Skyroute / Aureliya)
    Route::post('/webhook', [TransactionController::class, 'webhook']);
});


/*
|--------------------------------------------------------------------------
| AURELIYA ACCOMMODATIONS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\api\v1\aureliya\PropertyController;
use App\Http\Controllers\api\v1\aureliya\AmenityController;
use App\Http\Controllers\api\v1\aureliya\ReviewController;
use App\Http\Controllers\api\v1\aureliya\BookingController as AureliyaBookingController;

Route::prefix('aureliya')->group(function () {

    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/properties/{id}', [PropertyController::class, 'show']);

    Route::get('/amenities', [AmenityController::class, 'index']);
    Route::get('/amenities/{id}', [AmenityController::class, 'show']);

    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{id}', [ReviewController::class, 'show']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);

    // Bookings
    Route::get('/bookings', [AureliyaBookingController::class, 'index']);
    Route::get('/bookings/{id}', [AureliyaBookingController::class, 'show']);
    Route::post('/bookings', [AureliyaBookingController::class, 'store']);
    Route::put('/bookings/{id}', [AureliyaBookingController::class, 'update']);

    // ⭐ NEW — Update Payment Status
    Route::put('/booking/{id}/status', [AureliyaBookingController::class, 'updateStatus']);
});


/*
|--------------------------------------------------------------------------
| SKYROUTE LAND TRANSPORTATION
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\api\v1\skyroute\TripController;
use App\Http\Controllers\api\v1\skyroute\BookingController as SkyrouteBookingController;

Route::prefix('skyroute')->group(function () {

    // Trips
    Route::get('/trips', [TripController::class, 'index']);
    Route::get('/trips/city/{city}', [TripController::class, 'byCity']);
    Route::get('/trips/type/{type}', [TripController::class, 'byType']);
    Route::get('/trip/{id}', [TripController::class, 'show']);

    // Bookings
    Route::post('/booking', [SkyrouteBookingController::class, 'store']);
    Route::get('/bookings/{user_id}', [SkyrouteBookingController::class, 'userBookings']);
    Route::get('/booking/{id}', [SkyrouteBookingController::class, 'show']);
    Route::post('/booking/{id}/cancel', [SkyrouteBookingController::class, 'cancel']);

    // ⭐ NEW — Update Payment Status
    Route::put('/booking/{id}/status', [SkyrouteBookingController::class, 'updateStatus']);
});

<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PSA
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\api\v1\psa\FlightController;
use App\Http\Controllers\api\v1\psa\PassengerController;
use App\Http\Controllers\api\v1\psa\AirportController;
use App\Http\Controllers\api\v1\psa\BookingController as PsaBookingController;

Route::prefix('psa')->group(function () {

    // Flights
    Route::get('/flights', [FlightController::class, 'index']);
    Route::get('/flights/{id}', [FlightController::class, 'show']);
    Route::get('/flights/search', [FlightController::class, 'search']);

    // Passengers
    Route::post('/passengers', [PassengerController::class, 'store']);
    Route::get('/passengers/user/{user_id}', [PassengerController::class, 'showByUser']);
    Route::put('/passengers/{id}', [PassengerController::class, 'update']);

    // Airport
    Route::get('/airports', [AirportController::class, 'index']);
    Route::get('/airports/{id}', [AirportController::class, 'show']);

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
| SKYROUTE
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\api\v1\skyroute\LocationController;
use App\Http\Controllers\api\v1\skyroute\VehicleController;
use App\Http\Controllers\api\v1\skyroute\BookingController as SkyrouteBookingController;

Route::prefix('skyroute')->group(function () {

    // Locations (origin/destination)
    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/locations/{id}', [LocationController::class, 'show']);

    // Vehicles by city
    Route::get('/vehicles/city/{city}', [VehicleController::class, 'vehiclesByCity']);

    // Bookings
    Route::post('/bookings', [SkyrouteBookingController::class, 'store']);
    Route::get('/bookings/user/{user_id}', [SkyrouteBookingController::class, 'userBookings']);
    Route::get('/booking/{id}', [SkyrouteBookingController::class, 'show']);
    Route::post('/booking/{id}/cancel', [SkyrouteBookingController::class, 'cancel']);

    // Payment status
    Route::put('/booking/{id}/status', [SkyrouteBookingController::class, 'updateStatus']);
});

/*
|--------------------------------------------------------------------------
| TruTravel
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\api\v1\trutravel\PackageController;
use App\Http\Controllers\api\v1\trutravel\BookingController as trutravelBookingController;

Route::prefix('trutravel')->group(function () {
    // Packages
    Route::get('/packages', [PackageController::class, 'index']);
    Route::get('/packages/{id}', [PackageController::class, 'show']);

    // Bookings
    Route::post('/bookings', [trutravelBookingController::class, 'store']);
    Route::get('/bookings/user/{user_id}', [trutravelBookingController::class, 'userBookings']);
    Route::get('/booking/{id}', [trutravelBookingController::class, 'show']);
});

Route::prefix('trutravel')->group(function () {
    // Packages
    Route::get('/packages', [PackageController::class, 'index']);
    Route::get('/packages/{id}', [PackageController::class, 'show']);

    // Bookings
    Route::post('/bookings', [trutravelBookingController::class, 'store']);
    Route::get('/bookings/user/{user_id}', [trutravelBookingController::class, 'userBookings']);
    Route::get('/booking/{id}', [trutravelBookingController::class, 'show']);

    // NEW ROUTES - Add these
    Route::post('/booking/{id}/cancel', [trutravelBookingController::class, 'cancel']);
    Route::put('/booking/{id}/status', [trutravelBookingController::class, 'updateStatus']);
    Route::post('/webhook', [trutravelBookingController::class, 'webhook']);
});

use App\Http\Controllers\api\v1\aeronexa\UserController;

Route::prefix('aeronexa')->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::post('users', [UserController::class, 'store']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);


    // simple login (no tokens)
    Route::post('users/login', [UserController::class, 'login']);
});

<?php

namespace App\Http\Controllers\api\v1\psa;

use App\Http\Controllers\Controller;
use App\Models\psa\Booking;
use App\Models\psa\Flight;
use App\Models\psa\Passenger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $req)
    {
        $data = $req->validate([
            'user_id'      => 'required|string',
            'passenger_id' => 'required|string',
            'flight_id'    => 'required|string',
            'flight_date'  => 'required|date',
        ]);

        // -----------------------------------------
        // FIND PASSENGER + FLIGHT
        // -----------------------------------------
        $passenger = Passenger::find($data['passenger_id']);
        $flight    = Flight::find($data['flight_id']);

        if (!$passenger) {
            return response()->json(['error' => 'Passenger not found'], 404);
        }
        if (!$flight) {
            return response()->json(['error' => 'Flight not found'], 404);
        }

        $dep = substr($flight->departure_time, 0, 5);
        $arr = substr($flight->arrival_time, 0, 5);

        // -----------------------------------------
        // STEP 1: CREATE BOOKING FIRST (UNPAID)
        // -----------------------------------------
        $booking = Booking::create([
            'user_id'        => $data['user_id'],
            'passenger_id'   => $passenger->_id,
            'flight_id'      => $flight->_id,
            'flight_date'    => $data['flight_date'],
            'departure_time' => $dep,
            'arrival_time'   => $arr,
            'total_amount'   => $flight->basePrice,
            'payment_method' => 'AEROPAY',
            'payment_status' => 'pending', // not yet paid
        ]);

        // -----------------------------------------
        // STEP 2: CALL AEROPAY USING REAL BOOKING ID
        // -----------------------------------------
        $transaction = $this->createAeroPayTransaction(
            $data['user_id'],
            $flight->basePrice,
            $booking->_id,   // <-- TRUE booking ID now passed!
            $flight,
            $passenger
        );

        if (!$transaction['success']) {
            return response()->json([
                'error'   => 'AeroPay Payment Failed',
                'details' => $transaction['message']
            ], 500);
        }

        // -----------------------------------------
        // STEP 3: UPDATE BOOKING WITH PAYMENT DETAILS
        // -----------------------------------------
        $booking->update([
            'transaction_code' => $transaction['transaction_code'],
            'payment_status'   => $transaction['status'],
        ]);

        return response()->json([
            'message' => 'Booking created and paid via AeroPay.',
            'data'    => $booking
        ], 201);
    }

    // -----------------------------------------------------------
    // AEROPAY PAYMENT REQUEST
    // -----------------------------------------------------------
    private function createAeroPayTransaction($userId, $amount, $bookingId, $flight, $passenger)
    {
        try {
            $payload = [
                'user_id'              => $userId,
                'transaction_code'     => Str::upper(Str::random(10)),
                'partner'              => 'PSA',
                'partner_reference_id' => $bookingId,   // <-- REAL BOOKING ID
                'amount'               => $amount,
                'currency'             => 'PHP',
                'status'               => 'pending',
                'metadata'             => [
                    'source'      => 'psa-booking',
                    'flight_id'   => $flight->_id,
                    'passenger'   => $passenger->first_name . ' ' . $passenger->last_name,
                ]
            ];

            $response = Http::timeout(5)->post(
                "http://localhost:8001/api/aeropay/charge",
                $payload
            );

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => $response->body()
                ];
            }

            $json = $response->json();

            return [
                'success'          => true,
                'transaction_code' => $json['transaction_code'] ?? null,
                'status'           => $json['status'] ?? 'pending',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // -----------------------------------------------------------
    // GET BOOKINGS BY USER
    // -----------------------------------------------------------
    public function userBookings($user_id)
    {
        return Booking::where('user_id', $user_id)->get();
    }

    public function show($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        return response()->json($booking);
    }

    public function cancel($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $booking->payment_status = 'cancelled';
        $booking->save();

        return response()->json(['message' => 'Booking cancelled', 'data' => $booking]);
    }

    public function updatePassenger(Request $req, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        if (!$req->passenger_id) {
            return response()->json(['error' => 'passenger_id is required'], 422);
        }

        $booking->passenger_id = $req->passenger_id;
        $booking->save();

        return response()->json(['message' => 'Passenger updated', 'data' => $booking]);
    }
}

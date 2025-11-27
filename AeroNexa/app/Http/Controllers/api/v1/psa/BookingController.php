<?php

namespace App\Http\Controllers\api\v1\psa;

use App\Http\Controllers\Controller;
use App\Models\psa\Booking;
use App\Models\psa\Flight;
use App\Models\psa\Passenger;
use Illuminate\Http\Request;
use App\Traits\HandlesAeroPay;

class BookingController extends Controller
{
    use HandlesAeroPay;

    public function store(Request $req)
    {
        $data = $req->validate([
            'user_id'      => 'required|string',
            'passenger_id' => 'required|string',
            'flight_id'    => 'required|string',
            'flight_date'  => 'required|date',
        ]);

        $passenger = Passenger::find($data['passenger_id']);
        $flight = Flight::find($data['flight_id']);

        if (!$passenger) return response()->json(['error' => 'Passenger not found'], 404);
        if (!$flight) return response()->json(['error' => 'Flight not found'], 404);

        $booking = Booking::create([
            'user_id'        => $data['user_id'],
            'passenger_id'   => $passenger->_id,
            'flight_id'      => $flight->_id,
            'flight_date'    => $data['flight_date'],
            'departure_time' => substr($flight->departure_time, 0, 5),
            'arrival_time'   => substr($flight->arrival_time, 0, 5),
            'total_amount'   => $flight->basePrice,
            'payment_method' => 'AEROPAY',
            'payment_status' => 'pending',
        ]);

        // AeroPay
        $tx = $this->createAeroPayPayment(
            $data['user_id'],
            $flight->basePrice,
            $booking->_id,
            'PSA',
            [
                'flight_id'  => $flight->_id,
                'passenger'  => $passenger->first_name . " " . $passenger->last_name
            ]
        );

        if (!$tx['success']) {
            return response()->json(['error' => $tx['message']], 500);
        }

        $booking->update([
            'transaction_code' => $tx['transaction_code'],
            'payment_status'   => $tx['status'],
        ]);

        return ['message' => 'PSA booking created', 'data' => $booking];
    }

    public function userBookings($id)
    {
        return Booking::where('user_id', $id)->get();
    }

    public function show($id)
    {
        $booking = Booking::find($id);
        if (!$booking) return response()->json(['error' => 'Not found'], 404);
        return $booking;
    }

    /** Cancel + sync AeroPay */
    public function cancel($id)
    {
        $booking = Booking::find($id);
        if (!$booking) return response()->json(['error' => 'Not found'], 404);

        $this->updateAeroPayStatus($booking->transaction_code, 'cancelled');

        $booking->payment_status = 'cancelled';
        $booking->save();

        return ['message' => 'Booking cancelled'];
    }

    /** Payment status updater */
    public function updateStatus(Request $req, $id)
    {
        $data = $req->validate([
            'payment_status' => 'required|string|in:pending,paid,failed,cancelled'
        ]);

        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        if (!$booking->transaction_code) {
            return response()->json([
                'error' => 'No AeroPay transaction linked to this booking'
            ], 400);
        }

        // -----------------------------------------
        // 1️⃣ Update Booking Status (Local)
        // -----------------------------------------
        $booking->payment_status = $data['payment_status'];
        $booking->save();

        // -----------------------------------------
        // 2️⃣ Sync Status with AeroPay
        // -----------------------------------------
        $aero = $this->updateAeroPayStatus(
            $booking->transaction_code,
            $data['payment_status']
        );

        if (!$aero['success']) {
            return response()->json([
                'warning' => 'Booking updated, but AeroPay update failed',
                'details' => $aero['message'],
                'booking' => $booking
            ], 202);
        }

        // -----------------------------------------
        // 3️⃣ Return FINAL Response
        // -----------------------------------------
        return response()->json([
            'message' => 'Payment status updated successfully',
            'aeropay' => $aero['data'] ?? null,
            'booking' => $booking
        ]);
    }
}

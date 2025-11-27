<?php

namespace App\Http\Controllers\api\v1\skyroute;

use App\Http\Controllers\Controller;
use App\Models\skyroute\Booking;
use App\Models\skyroute\Trip;
use Illuminate\Http\Request;
use App\Traits\HandlesAeroPay;

class BookingController extends Controller
{
    use HandlesAeroPay;

    public function store(Request $req)
    {
        $data = $req->validate([
            'user_id'     => 'required|string',
            'trip_id'     => 'required|string',
            'travel_date' => 'required|date',
            'passengers'  => 'required|integer|min:1|max:20',
        ]);

        $trip = Trip::find($data['trip_id']);
        if (!$trip) return response()->json(['error' => 'Trip not found'], 404);

        $total = $trip->fare * $data['passengers'];

        // Create booking (pending)
        $booking = Booking::create([
            'user_id'        => $data['user_id'],
            'trip_id'        => $trip->_id,
            'travel_date'    => $data['travel_date'],
            'passengers'     => $data['passengers'],
            'total_amount'   => $total,
            'payment_method' => 'AEROPAY',
            'payment_status' => 'pending'
        ]);

        // AeroPay
        $tx = $this->createAeroPayPayment(
            $data['user_id'],
            $total,
            $booking->_id,
            'SKYROUTE',
            [
                'trip_code' => $trip->trip_code
            ]
        );

        if (!$tx['success']) {
            return response()->json(['error' => $tx['message']], 500);
        }

        $booking->update([
            'transaction_code' => $tx['transaction_code'],
            'payment_status'   => $tx['status']
        ]);

        return ['message' => 'SkyRoute booking created', 'data' => $booking];
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

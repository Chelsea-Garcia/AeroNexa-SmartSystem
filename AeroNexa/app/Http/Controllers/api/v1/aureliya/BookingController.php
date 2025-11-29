<?php

namespace App\Http\Controllers\Api\V1\aureliya;

use App\Http\Controllers\Controller;
use App\Models\aureliya\Booking;
use App\Models\aureliya\Property;
use Illuminate\Http\Request;
use App\Traits\HandlesAeroPay;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    use HandlesAeroPay;

    public function index()
    {
        return Booking::all();
    }

    public function show($id)
    {
        return Booking::findOrFail($id);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'user_id'     => 'required|string',
            'property_id' => 'required|string',
            'check_in'    => 'required|date',
            'check_out'   => 'required|date|after:check_in',
            'transaction_code' => 'nullable|string', // <-- Added for TruTravel
            'payment_method' => 'nullable|string|in:AEROPAY,TRUTRAVEL',
        ]);

        $property = Property::find($data['property_id']);
        if (!$property) return response()->json(['error' => 'Property not found'], 404);

        $nights = (new \DateTime($data['check_in']))->diff(new \DateTime($data['check_out']))->days;
        if ($nights <= 0) return response()->json(['error' => 'Invalid stay duration'], 422);

        $totalPrice = $nights * $property->price_per_night;

        // Create booking (pending)
        $booking = Booking::create([
            '_id'            => Str::uuid()->toString(),
            'user_id'        => $data['user_id'],
            'property_id'    => $property->_id,
            'check_in'       => $data['check_in'],
            'check_out'      => $data['check_out'],
            'total_price'    => $totalPrice,
            'payment_method' => 'AEROPAY',
            'payment_status' => 'pending',
        ]);

        /** ---------------------------------------------
         * 1️⃣ IF TRUTRAVEL SENT A TRANSACTION CODE
         * --------------------------------------------*/
        // After creating booking, check payment method
        if (($data['payment_method'] ?? 'AEROPAY') === 'TRUTRAVEL') {
            return response()->json([
                'message' => 'Booking created via TruTravel',
                'data' => $booking
            ]);
        }

        // Otherwise continue with normal AeroPay flow...

        if (!empty($data['transaction_code'])) {

            $booking->update([
                'transaction_code' => $data['transaction_code'],
                'payment_status'   => 'pending',
            ]);

            return response()->json([
                'message' => 'Aureliya booking created via TruTravel',
                'data'    => $booking
            ]);
        }

        /** ---------------------------------------------
         * 2️⃣ NORMAL BOOKING → Generate AeroPay transaction
         * --------------------------------------------*/
        $tx = $this->createAeroPayPayment(
            $data['user_id'],
            $totalPrice,
            $booking->_id,
            'AURELIYA',
            [
                'property_id' => $property->_id,
                'property_name' => $property->title,
            ]
        );

        if (!$tx['success']) {
            return response()->json(['error' => $tx['message']], 500);
        }

        $booking->update([
            'transaction_code' => $tx['transaction_code'],
            'payment_status'   => $tx['status'],
        ]);

        return response()->json([
            'message' => 'Aureliya booking created',
            'data'    => $booking
        ]);
    }

    /** Update stay only */
    public function update(Request $req, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update($req->only(['check_in', 'check_out']));
        return $booking;
    }

    /** Payment status update (paid/cancelled/failed) */
    public function updateStatus(Request $req, $id)
    {
        $data = $req->validate([
            'payment_status' => 'sometimes|string|in:pending,paid,failed,cancelled',
            'transaction_code' => 'sometimes|string', // For TruTravel to set transaction code
        ]);

        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // -----------------------------------------
        // 1️⃣ If TruTravel is sending transaction_code, save it
        // -----------------------------------------
        if (isset($data['transaction_code'])) {
            $booking->transaction_code = $data['transaction_code'];
            $booking->save();

            return response()->json([
                'message' => 'Transaction code updated',
                'booking' => $booking
            ]);
        }

        // -----------------------------------------
        // 2️⃣ Update payment status (if provided)
        // -----------------------------------------
        if (isset($data['payment_status'])) {
            $booking->payment_status = $data['payment_status'];
            $booking->save();

            // -----------------------------------------
            // 3️⃣ Sync with AeroPay (if transaction exists)
            // -----------------------------------------
            if ($booking->transaction_code) {
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

                return response()->json([
                    'message' => 'Payment status updated successfully',
                    'aeropay' => $aero['data'] ?? null,
                    'booking' => $booking
                ]);
            }

            // If no transaction_code yet, just return updated booking
            return response()->json([
                'message' => 'Payment status updated',
                'booking' => $booking
            ]);
        }

        return response()->json(['error' => 'No valid update data provided'], 400);
    }
}

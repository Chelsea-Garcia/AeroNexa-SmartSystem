<?php

namespace App\Http\Controllers\api\v1\skyroute;

use App\Http\Controllers\Controller;
use App\Models\skyroute\Booking;
use App\Models\skyroute\Location;
use App\Models\skyroute\Vehicle;
use Illuminate\Http\Request;
use App\Traits\HandlesAeroPay;

class BookingController extends Controller
{
    use HandlesAeroPay;

    /** Haversine Distance Calculator */
    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earth = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) ** 2;

        return $earth * (2 * asin(sqrt($a)));
    }


    /** Resolve Location by ID or by city name */
    private function resolveLocation($value)
    {
        // Try as MongoDB _id
        $loc = Location::find($value);
        if ($loc) return $loc;

        // Try partial match
        $loc = Location::where('city', 'like', $value)->first();
        if ($loc) return $loc;

        // Try exact case-insensitive
        return Location::where('city', $value)->first();
    }


    /** ----------------------------
     *  CREATE BOOKING
     * ---------------------------*/
    public function store(Request $req)
    {
        $data = $req->validate([
            'user_id'                 => 'required|string',
            'vehicle_id'              => 'required|string',
            'origin_location_id'      => 'required|string',
            'destination_location_id' => 'required|string',
            'date'                    => 'required|date',
            'time'                    => 'required|string',
            'passenger_name'          => 'required|string',
            'transaction_code'        => 'nullable|string', // <-- added
            'payment_method' => 'nullable|string|in:AEROPAY,TRUTRAVEL',
        ]);

        $vehicle = Vehicle::find($data['vehicle_id']);
        if (!$vehicle) return response()->json(['error' => 'Vehicle not found'], 404);

        $origin = $this->resolveLocation($data['origin_location_id']);
        if (!$origin) return response()->json(['error' => 'Origin location not found'], 404);

        $dest = $this->resolveLocation($data['destination_location_id']);
        if (!$dest) return response()->json(['error' => 'Destination location not found'], 404);

        if ($origin->division !== $dest->division) {
            return response()->json(['error' => "Origin and destination must be in the same division."], 400);
        }

        if ((string)$vehicle->location_id !== (string)$origin->_id) {
            return response()->json(['error' => "Vehicle does not belong to the origin city."], 400);
        }

        $distance = $this->haversine(
            $origin->latitude,
            $origin->longitude,
            $dest->latitude,
            $dest->longitude
        );

        $rate = $vehicle->fare_per_km ?? 12;
        $estimated = round($distance * $rate, 2);

        $booking = Booking::create([
            'user_id'                   => $data['user_id'],
            'vehicle_id'                => $vehicle->_id,
            'origin_location_id'        => $origin->_id,
            'destination_location_id'   => $dest->_id,
            'date'                      => $data['date'],
            'time'                      => $data['time'],
            'passenger_name'            => $data['passenger_name'],
            'estimated_amount'          => $estimated,
            'payment_method'            => 'AEROPAY',
            'payment_status'            => 'pending',
        ]);

        /** ---------------------------------------------
         * 1️⃣ TRUTRAVEL BOOKING
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

            return [
                'message' => 'SkyRoute booking created via TruTravel',
                'data'    => $booking
            ];
        }

        /** ---------------------------------------------
         * 2️⃣ NORMAL BOOKING (Generate AeroPay Tx)
         * --------------------------------------------*/
        $tx = $this->createAeroPayPayment(
            $data['user_id'],
            $estimated,
            $booking->_id,
            'SKYROUTE',
            [
                'origin' => $origin->city,
                'destination' => $dest->city,
                'vehicle' => $vehicle->name
            ]
        );

        if (!$tx['success']) {
            return response()->json(['error' => $tx['message']], 500);
        }

        $booking->update([
            'transaction_code' => $tx['transaction_code'],
            'payment_status'   => $tx['status']
        ]);

        return [
            'message' => 'SkyRoute booking created successfully',
            'data'    => $booking
        ];
    }

    /** USER BOOKINGS */
    public function userBookings($id)
    {
        return Booking::where('user_id', $id)->get();
    }


    /** SHOW BOOKING */
    public function show($id)
    {
        $b = Booking::find($id);
        if (!$b) return response()->json(['error' => 'Not found'], 404);
        return $b;
    }


    /** CANCEL BOOKING */
    public function cancel($id)
    {
        $booking = Booking::find($id);
        if (!$booking) return response()->json(['error' => 'Not found'], 404);

        $this->updateAeroPayStatus($booking->transaction_code, 'cancelled');

        $booking->payment_status = 'cancelled';
        $booking->save();

        return ['message' => 'Booking cancelled'];
    }


    /** UPDATE STATUS */
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

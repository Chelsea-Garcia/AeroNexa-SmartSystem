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

    // --- HELPER: Haversine Distance ---
    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earth = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $earth * (2 * asin(sqrt($a)));
    }

    // --- HELPER: Resolve Location ---
    private function resolveLocation($value)
    {
        $loc = Location::find($value);
        if ($loc) return $loc;
        $loc = Location::where('city', 'like', $value)->first();
        if ($loc) return $loc;
        return Location::where('city', $value)->first();
    }

    // --- MAIN: Create Booking ---
    public function store(Request $req)
    {
        // 1. Validate Input
        $data = $req->validate([
            'user_id'                 => 'required|string',
            'vehicle_id'              => 'required|string',
            'origin_location_id'      => 'required|string',
            'destination_location_id' => 'required|string',
            'date'                    => 'required|date',
            'time'                    => 'required|string',
            'passenger_name'          => 'required|string',
            'passenger_amount'        => 'required|integer|min:1', // <--- NEW VALIDATION
            'transaction_code'        => 'nullable|string',
            'payment_method'          => 'nullable|string|in:AEROPAY,TRUTRAVEL',
        ]);

        // 2. Fetch Entities
        $vehicle = Vehicle::find($data['vehicle_id']);
        if (!$vehicle) return response()->json(['error' => 'Vehicle not found'], 404);

        $origin = $this->resolveLocation($data['origin_location_id']);
        if (!$origin) return response()->json(['error' => 'Origin location not found'], 404);

        $dest = $this->resolveLocation($data['destination_location_id']);
        if (!$dest) return response()->json(['error' => 'Destination location not found'], 404);

        // 3. Logic Checks
        if ($origin->division !== $dest->division) {
            return response()->json(['error' => "Origin and destination must be in the same division."], 400);
        }

        // Check Division availability
        $vehicleLocation = $vehicle->location ?? Location::find($vehicle->location_id);
        if (!$vehicleLocation || $vehicleLocation->division !== $origin->division) {
            return response()->json(['error' => "Vehicle is not available in this division ($origin->division)."], 400);
        }

        // 4. Calculate Distance & Price
        $distance = $this->haversine(
            $origin->latitude,
            $origin->longitude,
            $dest->latitude,
            $dest->longitude
        );

        // --- NEW PRICING LOGIC ---
        $basePrice = $vehicle->base_price ?? 0;
        $farePerKm = $vehicle->fare_per_km ?? 12;
        
        $pax = (int)$data['passenger_amount'];
        
        // Define Percentage Increase per extra passenger (e.g., 0.20 = 20%)
        $extraPerPassenger = 0.005; 

        // If pax > 1, increase the rate. (e.g. 2 pax = 1.2x rate, 3 pax = 1.4x rate)
        $multiplier = 1 + ($extraPerPassenger * ($pax - 1));
        
        $adjustedRate = $farePerKm * $multiplier;
        
        $estimated = round($basePrice + ($distance * $adjustedRate), 2);
        // -------------------------

        // 5. Create Booking Record
        $booking = Booking::create([
            'user_id'                   => $data['user_id'],
            'vehicle_id'                => $vehicle->id, 
            'origin_location_id'        => $origin->id,
            'destination_location_id'   => $dest->id,
            'date'                      => $data['date'],
            'time'                      => $data['time'],
            'passenger_name'            => $data['passenger_name'],
            'passenger_amount'          => $pax, // <--- SAVE AMOUNT
            'estimated_amount'          => $estimated,
            'payment_method'            => 'AEROPAY',
            'payment_status'            => 'pending',
        ]);

        // 6. Handle TruTravel
        if (($data['payment_method'] ?? 'AEROPAY') === 'TRUTRAVEL') {
            return response()->json(['message' => 'Booking created via TruTravel', 'data' => $booking]);
        }

        if (!empty($data['transaction_code'])) {
            $booking->update(['transaction_code' => $data['transaction_code'], 'payment_status' => 'pending']);
            return ['message' => 'SkyRoute booking created via TruTravel', 'data' => $booking];
        }

        // 7. Handle AeroPay
        $tx = $this->createAeroPayPayment(
            $data['user_id'],
            $estimated,
            $booking->id,
            'SKYROUTE',
            [
                'origin' => $origin->city,
                'destination' => $dest->city,
                'vehicle' => $vehicle->name,
                'passengers' => $pax
            ]
        );

        if (!$tx['success']) {
            return response()->json(['error' => $tx['message']], 500);
        }

        $booking->update([
            'transaction_code' => $tx['transaction_code'],
            'payment_status'   => $tx['status']
        ]);

        return ['message' => 'SkyRoute booking created successfully', 'data' => $booking];
    }
    
    // ... (Keep existing methods: userBookings, show, cancel, updateStatus)
    public function userBookings($id) { return Booking::where('user_id', $id)->get(); }
    public function show($id) { $b = Booking::find($id); return $b ? $b : response()->json(['error' => 'Not found'], 404); }
    public function cancel($id) { 
        $booking = Booking::find($id); 
        if (!$booking) return response()->json(['error' => 'Not found'], 404);
        $this->updateAeroPayStatus($booking->transaction_code, 'cancelled');
        $booking->payment_status = 'cancelled';
        $booking->save();
        return ['message' => 'Booking cancelled'];
    }
    public function updateStatus(Request $req, $id) {
        $data = $req->validate(['payment_status' => 'sometimes|in:pending,paid,failed,cancelled', 'transaction_code' => 'sometimes|string']);
        $booking = Booking::find($id);
        if (!$booking) return response()->json(['error' => 'Booking not found'], 404);
        if (isset($data['transaction_code'])) { $booking->transaction_code = $data['transaction_code']; $booking->save(); return response()->json(['message' => 'Transaction code updated', 'booking' => $booking]); }
        if (isset($data['payment_status'])) {
            $booking->payment_status = $data['payment_status']; $booking->save();
            if ($booking->transaction_code) {
                $aero = $this->updateAeroPayStatus($booking->transaction_code, $data['payment_status']);
                if (!$aero['success']) return response()->json(['warning' => 'Booking updated, but AeroPay update failed', 'details' => $aero['message'], 'booking' => $booking], 202);
                return response()->json(['message' => 'Payment status updated successfully', 'aeropay' => $aero['data'] ?? null, 'booking' => $booking]);
            }
            return response()->json(['message' => 'Payment status updated', 'booking' => $booking]);
        }
        return response()->json(['error' => 'No valid update data provided'], 400);
    }
}
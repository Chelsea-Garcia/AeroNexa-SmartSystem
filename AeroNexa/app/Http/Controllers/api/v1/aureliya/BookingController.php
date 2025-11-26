<?php

namespace App\Http\Controllers\Api\V1\aureliya;

use App\Http\Controllers\Controller;
use App\Models\aureliya\Booking;
use App\Models\aureliya\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    // -------------------------------------------------------
    // GET ALL BOOKINGS
    // -------------------------------------------------------
    public function index()
    {
        return Booking::all();
    }

    // -------------------------------------------------------
    // SHOW SINGLE BOOKING
    // -------------------------------------------------------
    public function show($id)
    {
        return Booking::findOrFail($id);
    }

    // -------------------------------------------------------
    // CREATE BOOKING + AEROPAY PAYMENT (LIKE PSA LOGIC)
    // -------------------------------------------------------
    public function store(Request $req)
    {
        // 1️⃣ VALIDATION (total_price removed!)
        $data = $req->validate([
            'user_id'     => 'required|string',
            'property_id' => 'required|string',
            'check_in'    => 'required|date',
            'check_out'   => 'required|date|after:check_in',
        ]);

        // 2️⃣ ENSURE PROPERTY EXISTS
        $property = Property::where('_id', $data['property_id'])->first();

        if (!$property) {
            return response()->json(['error' => 'Property not found'], 404);
        }

        // 3️⃣ CALCULATE NIGHTS
        $checkIn  = new \DateTime($data['check_in']);
        $checkOut = new \DateTime($data['check_out']);

        $nights = $checkIn->diff($checkOut)->days;  // number of days
        if ($nights <= 0) {
            return response()->json(['error' => 'Invalid stay duration'], 422);
        }

        // 4️⃣ COMPUTE TOTAL PRICE
        $totalPrice = $nights * ($property->price_per_night ?? 0);

        // 5️⃣ CREATE BOOKING (unpaid first)
        $booking = Booking::create([
            '_id'           => Str::uuid()->toString(),
            'user_id'        => $data['user_id'],
            'property_id'    => $property->_id,
            'check_in'       => $data['check_in'],
            'check_out'      => $data['check_out'],
            'total_price'    => $totalPrice,
            'payment_method' => 'AEROPAY',
            'payment_status' => 'pending',
        ]);

        // 6️⃣ CALL AEROPAY PAYMENT GATEWAY
        $transaction = $this->createAeroPayTransaction(
            $data['user_id'],
            $totalPrice,
            $booking->_id,
            $property
        );

        if (!$transaction['success']) {
            return response()->json([
                'error'   => 'AeroPay Payment Failed',
                'details' => $transaction['message']
            ], 500);
        }

        // 7️⃣ SAVE PAYMENT DETAILS
        $booking->update([
            'transaction_code' => $transaction['transaction_code'],
            'payment_status'   => $transaction['status'],
        ]);

        return response()->json([
            'message' => 'Aureliya booking created and paid via AeroPay.',
            'data'    => $booking
        ], 201);
    }

    // -------------------------------------------------------
    // AEROPAY PAYMENT FUNCTION
    // -------------------------------------------------------
    private function createAeroPayTransaction($userId, $amount, $bookingId, $property)
    {
        try {
            $payload = [
                'user_id'              => $userId,
                'transaction_code'     => Str::upper(Str::random(10)),
                'partner'              => 'AURELIYA',
                'partner_reference_id' => $bookingId,
                'amount'               => $amount,
                'currency'             => 'PHP',
                'status'               => 'pending',
                'metadata'             => [
                    'source'       => 'aureliya-booking',
                    'property_id'  => $property->_id,
                    'property_name' => $property->title,
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

            // AurePay returns: { "transaction_code": "...", "status": "pending" }
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

    // -------------------------------------------------------
    // UPDATE CHECK-IN / CHECK-OUT ONLY
    // -------------------------------------------------------
    public function update(Request $req, $id)
    {
        $booking = Booking::findOrFail($id);

        $booking->update(
            $req->only(['check_in', 'check_out'])
        );

        return $booking;
    }

    // -------------------------------------------------------
    // DELETE DISABLED
    // -------------------------------------------------------
    public function destroy()
    {
        return response()->json(['error' => 'Forbidden'], 403);
    }
}

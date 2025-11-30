<?php

namespace App\Http\Controllers\Api\V1\trutravel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\trutravel\Booking;
use App\Models\trutravel\Package;
use Carbon\Carbon;
use App\Traits\HandlesAeroPay;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    use HandlesAeroPay;

    /**
     * Create a package booking on TruTravel and reserve partner bookings.
     */
    public function store(Request $req)
    {
        $data = $req->validate([
            'user_id' => 'required|string',
            'package_id' => 'required|string',
            'travel_date' => 'required|date|after_or_equal:today',
            'passenger_name' => 'required|string',
            'passenger_id' => 'required|string',
        ]);

        // Resolve package
        $package = Package::find($data['package_id']);
        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        // Compute dates
        $nights = $package->nights ?? 2;
        $travelDate = Carbon::parse($data['travel_date']);
        $returnDate = $travelDate->copy()->addDays($nights);

        // 1. Create local TruTravel booking (Pending)
        $ttBooking = Booking::create([
            'user_id' => $data['user_id'],
            'package_id' => $data['package_id'],
            'travel_date' => $travelDate->format('Y-m-d'),
            'return_date' => $returnDate->format('Y-m-d'),
            'amount' => $package->final_price,
            'currency' => $package->currency ?? 'PHP',
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $partnerBookings = [];

        // Base URLs
        $psaBase  = 'http://localhost:8000/api/psa';
        $aureBase = 'http://localhost:8002/api/aureliya';
        $srBase   = 'http://localhost:8003/api/skyroute';

        try {
            // -------------------------
            // 2. Reserve Partner Bookings
            // -------------------------

            // A) PSA — Outbound
            try {
                $psaResp = Http::post("{$psaBase}/bookings", [
                    'user_id' => $data['user_id'],
                    'passenger_id' => $data['passenger_id'],
                    'flight_id' => $package->airline_flight_id,
                    'flight_date' => $travelDate->format('Y-m-d'),
                    'payment_origin' => 'TRUTRAVEL',
                    'skip_aeropay' => true,
                ]);
                $psaResp->throw();
                $partnerBookings['psa_outbound_id'] = $this->extractBookingId($psaResp->json());
            } catch (\Exception $e) {
                throw new \Exception("PSA Outbound Booking Failed: " . $e->getMessage());
            }

            // B) PSA — Return (Optional)
            if (!empty($package->airline_return_flight_id)) {
                try {
                    $psaRetResp = Http::post("{$psaBase}/bookings", [
                        'user_id' => $data['user_id'],
                        'passenger_id' => $data['passenger_id'],
                        'flight_id' => $package->airline_return_flight_id,
                        'flight_date' => $returnDate->format('Y-m-d'),
                        'payment_origin' => 'TRUTRAVEL',
                        'skip_aeropay' => true,
                    ]);
                    $psaRetResp->throw();
                    $partnerBookings['psa_return_id'] = $this->extractBookingId($psaRetResp->json());
                } catch (\Exception $e) {
                    throw new \Exception("PSA Return Booking Failed: " . $e->getMessage());
                }
            }

            // C) Aureliya — Accommodation
            try {
                $aureResp = Http::post("{$aureBase}/bookings", [
                    'user_id' => $data['user_id'],
                    'property_id' => $package->aureliya_property_id,
                    'check_in' => $travelDate->format('Y-m-d'),
                    'check_out' => $returnDate->format('Y-m-d'),
                    'payment_origin' => 'TRUTRAVEL',
                    'skip_aeropay' => true,
                ]);
                $aureResp->throw();
                $partnerBookings['aureliya_id'] = $this->extractBookingId($aureResp->json());
            } catch (\Exception $e) {
                throw new \Exception("Aureliya Booking Failed: " . $e->getMessage());
            }

            // D) SkyRoute — Transfers (Optional)
            if (!empty($package->skyroute_vehicle_id)) {
                // Outbound
                try {
                    $srOutResp = Http::post("{$srBase}/bookings", [
                        'user_id' => $data['user_id'],
                        'vehicle_id' => $package->skyroute_vehicle_id,
                        'origin_location_id' => $package->skyroute_origin_id,
                        'destination_location_id' => $package->skyroute_destination_id,
                        'date' => $travelDate->format('Y-m-d'),
                        'time' => '14:00',
                        'passenger_name' => $data['passenger_name'],
                        'payment_origin' => 'TRUTRAVEL',
                        'skip_aeropay' => true,
                    ]);
                    $srOutResp->throw();
                    $partnerBookings['skyroute_outbound_id'] = $this->extractBookingId($srOutResp->json());
                } catch (\Exception $e) {
                    throw new \Exception("SkyRoute Outbound Failed: " . $e->getMessage());
                }

                // Return
                try {
                    $srRetResp = Http::post("{$srBase}/bookings", [
                        'user_id' => $data['user_id'],
                        'vehicle_id' => $package->skyroute_vehicle_id,
                        'origin_location_id' => $package->skyroute_destination_id,
                        'destination_location_id' => $package->skyroute_origin_id,
                        'date' => $returnDate->format('Y-m-d'),
                        'time' => '10:00',
                        'passenger_name' => $data['passenger_name'],
                        'payment_origin' => 'TRUTRAVEL',
                        'skip_aeropay' => true,
                    ]);
                    $srRetResp->throw();
                    $partnerBookings['skyroute_return_id'] = $this->extractBookingId($srRetResp->json());
                } catch (\Exception $e) {
                    throw new \Exception("SkyRoute Return Failed: " . $e->getMessage());
                }
            }

            // Save partner IDs locally
            $ttBooking->update([
                'payment_breakdown' => json_encode($partnerBookings),
            ]);

            // -------------------------
            // 3. Create AeroPay Transaction
            // -------------------------
            $aeropay = $this->createAeroPayPayment(
                $data['user_id'],
                $package->final_price,
                $ttBooking->getKey(),
                'TRUTRAVEL',
                [
                    'package_id' => $package->id ?? $package->getKey(),
                    'package_name' => $package->name,
                    'partner_bookings' => $partnerBookings,
                ]
            );

            // Log response for debugging
            Log::info("AeroPay Response for Booking {$ttBooking->getKey()}", $aeropay);

            if (!$aeropay['success']) {
                throw new \Exception('AeroPay transaction failed: ' . ($aeropay['message'] ?? 'unknown error'));
            }

            // ROBUST EXTRACTION: Check root, then data, then payload
            $transactionCode = $aeropay['transaction_code']
                ?? $aeropay['data']['transaction_code']
                ?? $aeropay['payload']['transaction_code']
                ?? null;

            if (empty($transactionCode)) {
                // If success is true but code is missing, Fail hard so we don't have null codes
                throw new \Exception("AeroPay returned success but Transaction Code is missing. Response: " . json_encode($aeropay));
            }

            // Update local booking
            $ttBooking->update(['transaction_code' => $transactionCode]);

            // -------------------------
            // 4. Sync Partners
            // -------------------------
            $this->updatePartnerTransactions($partnerBookings, $transactionCode, 'pending');

            return response()->json([
                'message' => 'Booking created successfully',
                'data' => [
                    'booking' => $ttBooking->fresh(),
                    'package' => $package,
                    'partner_bookings' => $partnerBookings,
                ],
                'payment' => [
                    'transaction_code' => $transactionCode,
                    'amount' => $package->final_price,
                    'status' => 'pending',
                ]
            ], 201);
        } catch (\Exception $e) {
            // Rollback (Best Effort)
            Log::error("TruTravel Booking Error: " . $e->getMessage());
            $this->cancelPartnerBookings($partnerBookings);

            $ttBooking->update([
                'status' => 'failed',
                'payment_status' => 'failed',
            ]);

            return response()->json([
                'error' => 'Booking failed',
                'message' => $e->getMessage(),
                'details' => $partnerBookings
            ], 500);
        }
    }

    /**
     * Extract booking id from partner response
     */
    private function extractBookingId(array $json)
    {
        if (isset($json['data']['_id'])) return $json['data']['_id'];
        if (isset($json['data']['id'])) return $json['data']['id'];
        if (isset($json['_id'])) return $json['_id'];
        if (isset($json['id'])) return $json['id'];
        return null;
    }

    /**
     * Send transaction code + payment_status to partner bookings
     */
    private function updatePartnerTransactions(array $bookings, ?string $transactionCode, string $paymentStatus = 'pending')
    {
        $psaBase  = 'http://localhost:8000/api/psa';
        $aureBase = 'http://localhost:8002/api/aureliya';
        $srBase   = 'http://localhost:8003/api/skyroute';

        $payload = [
            'payment_status' => $paymentStatus,
            'transaction_code' => $transactionCode,
        ];

        // PSA
        if (!empty($bookings['psa_outbound_id'])) Http::put("{$psaBase}/booking/{$bookings['psa_outbound_id']}/status", $payload);
        if (!empty($bookings['psa_return_id'])) Http::put("{$psaBase}/booking/{$bookings['psa_return_id']}/status", $payload);

        // Aureliya
        if (!empty($bookings['aureliya_id'])) Http::put("{$aureBase}/booking/{$bookings['aureliya_id']}/status", $payload);

        // SkyRoute
        if (!empty($bookings['skyroute_outbound_id'])) Http::put("{$srBase}/booking/{$bookings['skyroute_outbound_id']}/status", $payload);
        if (!empty($bookings['skyroute_return_id'])) Http::put("{$srBase}/booking/{$bookings['skyroute_return_id']}/status", $payload);
    }

    /**
     * Cancel partner bookings
     */
    private function cancelPartnerBookings(array $bookings)
    {
        $psaBase  = 'http://localhost:8000/api/psa';
        $aureBase = 'http://localhost:8002/api/aureliya';
        $srBase   = 'http://localhost:8003/api/skyroute';

        foreach ($bookings as $key => $id) {
            if (empty($id)) continue;
            try {
                if (str_contains($key, 'psa')) Http::post("{$psaBase}/booking/{$id}/cancel");
                elseif (str_contains($key, 'aureliya')) Http::put("{$aureBase}/booking/{$id}/status", ['payment_status' => 'cancelled']);
                elseif (str_contains($key, 'skyroute')) Http::post("{$srBase}/booking/{$id}/cancel");
            } catch (\Exception $e) {
                // Ignore rollback errors
            }
        }
    }

    /**
     * List user bookings
     */
    public function userBookings($id)
    {
        $bookings = Booking::where('user_id', $id)
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $bookings]);
    }

    /**
     * Show booking
     */
    public function show($id)
    {
        $booking = Booking::with('package')->find($id);
        if (!$booking) return response()->json(['error' => 'Booking not found'], 404);
        return response()->json(['data' => $booking]);
    }

    /**
     * Cancel booking
     */
    public function cancel($id)
    {
        $booking = Booking::find($id);
        if (!$booking) return response()->json(['error' => 'Booking not found'], 404);

        $this->updateAeroPayStatus($booking->transaction_code, 'cancelled');
        $partnerBookings = json_decode($booking->payment_breakdown, true) ?? [];
        $this->cancelPartnerBookings($partnerBookings);

        $booking->update(['status' => 'cancelled', 'payment_status' => 'cancelled']);

        return response()->json(['message' => 'Booking cancelled']);
    }

    /**
     * Update status
     */
    public function updateStatus($id, Request $req)
    {
        $booking = Booking::find($id);
        if (!$booking) return response()->json(['error' => 'Not found'], 404);
        $booking->update($req->all());
        return response()->json(['message' => 'Updated', 'data' => $booking]);
    }

    /**
     * Webhook
     */
    public function webhook(Request $req)
    {
        $data = $req->validate([
            'transaction_code' => 'required|string',
            'status' => 'required|string',
        ]);

        $booking = Booking::where('transaction_code', $data['transaction_code'])->first();
        if (!$booking) return response()->json(['error' => 'Not found'], 404);

        $booking->payment_status = $data['status'];
        if (in_array($data['status'], ['completed', 'paid'])) $booking->status = 'confirmed';
        elseif (in_array($data['status'], ['failed', 'cancelled'])) $booking->status = 'failed';

        $booking->save();

        $partnerBookings = json_decode($booking->payment_breakdown, true) ?? [];
        $this->updatePartnerTransactions($partnerBookings, $data['transaction_code'], $data['status']);

        return response()->json(['message' => 'Updated']);
    }
}

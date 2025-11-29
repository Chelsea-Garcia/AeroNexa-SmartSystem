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

class BookingController extends Controller
{
    use HandlesAeroPay;

    /**
     * Create a package booking on TruTravel and reserve partner bookings.
     * - calls partner booking endpoints with payment_origin=TRUTRAVEL + skip_aeropay=true
     * - records partner booking ids
     * - creates a single AeroPay transaction for the package
     * - pushes transaction_code + payment_status=pending to partners
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

        // compute dates (fallback nights to 2)
        $nights = $package->nights ?? 2;
        $travelDate = Carbon::parse($data['travel_date']);
        $returnDate = $travelDate->copy()->addDays($nights);

        // Create local TruTravel booking
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

        // base URLs (use /api prefix — change if your services don't use /api)
        $psaBase  = 'http://localhost:8000/api/psa';
        $aureBase = 'http://localhost:8002/api/aureliya';
        $srBase   = 'http://localhost:8003/api/skyroute';

        try {
            // -------------------------
            // 1) PSA — outbound flight
            // -------------------------
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
                $psaJson = $psaResp->json();
                $partnerBookings['psa_outbound_id'] = $this->extractBookingId($psaJson);
            } catch (RequestException $e) {
                $res = $e->response;
                $body = $res ? $res->body() : $e->getMessage();
                \Log::error('PSA outbound booking failed', ['status' => $res?->status(), 'body' => $body]);
                throw new \Exception("PSA outbound booking failed: HTTP " . ($res?->status() ?? 'N/A') . " — " . $body);
            }

            // -------------------------
            // 2) PSA — return flight (optional)
            // -------------------------
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
                    $psaRetJson = $psaRetResp->json();
                    $partnerBookings['psa_return_id'] = $this->extractBookingId($psaRetJson);
                } catch (RequestException $e) {
                    $res = $e->response;
                    $body = $res ? $res->body() : $e->getMessage();
                    \Log::error('PSA return booking failed', ['status' => $res?->status(), 'body' => $body]);
                    throw new \Exception("PSA return booking failed: HTTP " . ($res?->status() ?? 'N/A') . " — " . $body);
                }
            }

            // -------------------------
            // 3) Aureliya — accommodation
            // -------------------------
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
                $aureJson = $aureResp->json();
                $partnerBookings['aureliya_id'] = $this->extractBookingId($aureJson);
            } catch (RequestException $e) {
                $res = $e->response;
                $body = $res ? $res->body() : $e->getMessage();
                \Log::error('Aureliya booking failed', ['status' => $res?->status(), 'body' => $body]);
                throw new \Exception("Aureliya booking failed: HTTP " . ($res?->status() ?? 'N/A') . " — " . $body);
            }

            // -------------------------
            // 4) SkyRoute — transfers (outbound & return) if vehicle present
            // -------------------------
            if (!empty($package->skyroute_vehicle_id)) {
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
                    $srOutJson = $srOutResp->json();
                    $partnerBookings['skyroute_outbound_id'] = $this->extractBookingId($srOutJson);
                } catch (RequestException $e) {
                    $res = $e->response;
                    $body = $res ? $res->body() : $e->getMessage();
                    \Log::error('SkyRoute outbound failed', ['status' => $res?->status(), 'body' => $body]);
                    throw new \Exception("SkyRoute outbound booking failed: HTTP " . ($res?->status() ?? 'N/A') . " — " . $body);
                }

                // return transfer
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
                    $srRetJson = $srRetResp->json();
                    $partnerBookings['skyroute_return_id'] = $this->extractBookingId($srRetJson);
                } catch (RequestException $e) {
                    $res = $e->response;
                    $body = $res ? $res->body() : $e->getMessage();
                    \Log::error('SkyRoute return failed', ['status' => $res?->status(), 'body' => $body]);
                    throw new \Exception("SkyRoute return booking failed: HTTP " . ($res?->status() ?? 'N/A') . " — " . $body);
                }
            }

            // -------------------------
            // Save partner booking ids
            // -------------------------
            $ttBooking->update([
                'payment_breakdown' => json_encode($partnerBookings),
            ]);

            // -------------------------
            // Create single AeroPay transaction for the package
            // -------------------------
            $aeropay = $this->createAeroPayPayment(
                $data['user_id'],
                $package->final_price,
                $ttBooking->getKey(), // use local PK
                'TRUTRAVEL',
                [
                    'package_id' => $package->id ?? $package->getKey(),
                    'package_name' => $package->name,
                    'partner_bookings' => $partnerBookings,
                ]
            );

            if (!$aeropay['success']) {
                throw new \Exception('AeroPay transaction failed: ' . ($aeropay['message'] ?? 'unknown'));
            }

            $transactionCode = $aeropay['transaction_code'] ?? $aeropay['data']['transaction_code'] ?? null;

            // store transaction code
            $ttBooking->update(['transaction_code' => $transactionCode]);

            // -------------------------
            // Push transaction_code + pending status to partners
            // -------------------------
            $this->updatePartnerTransactions($partnerBookings, $transactionCode, 'pending');

            // -------------------------
            // Return response
            // -------------------------
            return response()->json([
                'message' => 'Booking created successfully',
                'data' => [
                    'booking' => $ttBooking->fresh(),
                    'package' => $package,
                    'itinerary' => [
                        'travel_date' => $travelDate->format('Y-m-d'),
                        'return_date' => $returnDate->format('Y-m-d'),
                        'nights' => $nights,
                    ],
                    'partner_bookings' => $partnerBookings,
                ],
                'payment' => [
                    'transaction_code' => $transactionCode,
                    'amount' => $package->final_price,
                    'status' => 'pending',
                ]
            ], 201);
        } catch (\Exception $e) {
            // rollback partner bookings (best-effort)
            if (!empty($partnerBookings)) {
                $this->cancelPartnerBookings($partnerBookings);
            }

            // mark truTravel booking failed
            $ttBooking->update([
                'status' => 'failed',
                'payment_status' => 'failed',
            ]);

            \Log::error('TruTravel booking failed', ['message' => $e->getMessage(), 'partnerBookings' => $partnerBookings]);

            return response()->json([
                'error' => 'Booking failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract booking id from partner response (handles multiple shapes)
     */
    private function extractBookingId(array $json)
    {
        // common patterns: { "message": "...", "data": { "_id": "..." } }
        if (isset($json['data']) && is_array($json['data'])) {
            if (isset($json['data']['_id'])) return $json['data']['_id'];
            if (isset($json['data']['id'])) return $json['data']['id'];
        }

        // or top-level _id / id
        if (isset($json['_id'])) return $json['_id'];
        if (isset($json['id'])) return $json['id'];

        // some services may nest differently; return null if not found
        return null;
    }

    /**
     * Send transaction code + payment_status to partner bookings (used after charge created)
     */
    private function updatePartnerTransactions(array $bookings, ?string $transactionCode, string $paymentStatus = 'pending')
    {
        // use the same /api prefixes as store
        $psaBase  = 'http://localhost:8000/api/psa';
        $aureBase = 'http://localhost:8002/api/aureliya';
        $srBase   = 'http://localhost:8003/api/skyroute';

        // PSA
        if (!empty($bookings['psa_outbound_id'])) {
            Http::put("{$psaBase}/booking/{$bookings['psa_outbound_id']}/status", [
                'payment_status' => $paymentStatus,
                'transaction_code' => $transactionCode,
            ]);
        }
        if (!empty($bookings['psa_return_id'])) {
            Http::put("{$psaBase}/booking/{$bookings['psa_return_id']}/status", [
                'payment_status' => $paymentStatus,
                'transaction_code' => $transactionCode,
            ]);
        }

        // Aureliya
        if (!empty($bookings['aureliya_id'])) {
            Http::put("{$aureBase}/booking/{$bookings['aureliya_id']}/status", [
                'payment_status' => $paymentStatus,
                'transaction_code' => $transactionCode,
            ]);
        }

        // SkyRoute
        if (!empty($bookings['skyroute_outbound_id'])) {
            Http::put("{$srBase}/booking/{$bookings['skyroute_outbound_id']}/status", [
                'payment_status' => $paymentStatus,
                'transaction_code' => $transactionCode,
            ]);
        }
        if (!empty($bookings['skyroute_return_id'])) {
            Http::put("{$srBase}/booking/{$bookings['skyroute_return_id']}/status", [
                'payment_status' => $paymentStatus,
                'transaction_code' => $transactionCode,
            ]);
        }
    }

    /**
     * Cancel partner bookings (best-effort)
     */
    private function cancelPartnerBookings(array $bookings)
    {
        $psaBase  = 'http://localhost:8000/api/psa';
        $aureBase = 'http://localhost:8002/api/aureliya';
        $srBase   = 'http://localhost:8003/api/skyroute';

        foreach ($bookings as $key => $id) {
            if (empty($id)) continue;
            try {
                if (str_contains($key, 'psa')) {
                    // PSA cancel is POST /psa/booking/{id}/cancel
                    Http::post("{$psaBase}/booking/{$id}/cancel");
                } elseif (str_contains($key, 'aureliya')) {
                    // Aureliya: use update status to cancelled
                    Http::put("{$aureBase}/booking/{$id}/status", [
                        'payment_status' => 'cancelled'
                    ]);
                } elseif (str_contains($key, 'skyroute')) {
                    // SkyRoute cancel is POST /skyroute/booking/{id}/cancel
                    Http::post("{$srBase}/booking/{$id}/cancel");
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to cancel partner booking {$key}={$id}: " . $e->getMessage());
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
     * Cancel truTravel booking and partner bookings
     */
    public function cancel($id)
    {
        $booking = Booking::find($id);
        if (!$booking) return response()->json(['error' => 'Booking not found'], 404);
        if ($booking->status === 'cancelled') return response()->json(['error' => 'Booking already cancelled'], 400);

        $partnerBookings = json_decode($booking->payment_breakdown, true) ?? [];

        // Cancel AeroPay transaction
        if ($booking->transaction_code) {
            // updateAeropayStatus probably exists in trait
            $this->updateAeroPayStatus($booking->transaction_code, 'cancelled');
        }

        // Cancel partners
        $this->cancelPartnerBookings($partnerBookings);

        $booking->update([
            'status' => 'cancelled',
            'payment_status' => 'cancelled'
        ]);

        return response()->json(['message' => 'Booking cancelled successfully', 'data' => $booking]);
    }

    /**
     * Update booking status (admin / webhook friendly)
     */
    public function updateStatus($id, Request $req)
    {
        $booking = Booking::find($id);
        if (!$booking) return response()->json(['error' => 'Booking not found'], 404);

        $data = $req->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed,failed',
            'payment_status' => 'sometimes|in:pending,completed,failed,cancelled'
        ]);

        $booking->update($data);

        return response()->json(['message' => 'Booking status updated', 'data' => $booking]);
    }

    /**
     * Handle AeroPay webhook
     */
    public function webhook(Request $req)
    {
        $data = $req->validate([
            'transaction_code' => 'required|string',
            'status' => 'required|string',
        ]);

        $booking = Booking::where('transaction_code', $data['transaction_code'])->first();
        if (!$booking) return response()->json(['error' => 'Booking not found'], 404);

        $booking->payment_status = $data['status'];

        if (in_array($data['status'], ['completed', 'paid'])) {
            $booking->status = 'confirmed';
            // push paid to partners
            $partnerBookings = json_decode($booking->payment_breakdown, true) ?? [];
            $this->updatePartnerTransactions($partnerBookings, $data['transaction_code'], 'paid');
        } elseif (in_array($data['status'], ['failed', 'cancelled'])) {
            $booking->status = 'failed';
        }

        $booking->save();

        return response()->json(['message' => 'Payment status updated', 'data' => $booking]);
    }
}

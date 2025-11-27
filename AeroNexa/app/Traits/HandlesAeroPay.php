<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait HandlesAeroPay
{
    /**
     * Create an AeroPay transaction
     */
    public function createAeroPayPayment($userId, $amount, $referenceId, $partner, $metadata = [])
    {
        try {
            $payload = [
                'user_id'              => $userId,
                'transaction_code'     => Str::upper(Str::random(10)),
                'partner'              => $partner,   // PSA | Skyroute | Aureliya
                'partner_reference_id' => $referenceId,
                'amount'               => $amount,
                'currency'             => 'PHP',
                'status'               => 'pending',
                'metadata'             => $metadata
            ];

            $res = Http::timeout(5)->post(
                'http://localhost:8001/api/aeropay/charge',
                $payload
            );

            if ($res->failed()) {
                return [
                    'success' => false,
                    'message' => 'AeroPay API error: ' . $res->body(),
                ];
            }

            return [
                'success' => true,
                'transaction_code' => $res['transaction_code'],
                'status'           => $res['status'],
            ];
        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update AeroPay payment status (paid, failed, cancelled)
     */
    public function updateAeroPayStatus($transactionCode, $status)
    {
        try {
            $payload = [
                'status' => $status
            ];

            $res = Http::timeout(5)->put(
                "http://localhost:8001/api/aeropay/transactions/{$transactionCode}/status",
                $payload
            );

            if ($res->failed()) {
                return [
                    'success' => false,
                    'message' => 'AeroPay Update Error: ' . $res->body(),
                ];
            }

            return [
                'success' => true,
                'message' => 'AeroPay status updated successfully',
                'data'    => $res->json()
            ];
        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

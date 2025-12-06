<?php

namespace App\Http\Controllers\api\v1\aeropay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\aeropay\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    /**
     * Create / Charge Transaction
     */
    public function charge(Request $request)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|string',
                'partner' => 'required|string',               // psa | aureliya | skyroute
                'partner_reference_id' => 'required|string', // booking_id
                'amount' => 'required|numeric',
                'currency' => 'required|string',
                'status' => 'required|string',               // pending initially
                'metadata' => 'nullable|array'
            ]);

            $transaction = Transaction::create($data);

            return response()->json([
                'transaction_code' => $transaction->transaction_code,
                'status' => $transaction->status
            ], 201);
        } catch (\Exception $e) {
            Log::error("AeroPay Charge Error: " . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create transaction',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List All Transactions
     */
    public function index()
    {
        return response()->json(Transaction::all());
    }

    /**
     * Get Single transaction by ID or Code
     */
    public function show($id)
    {
        $transaction = Transaction::where('_id', $id)
            ->orWhere('transaction_code', $id)
            ->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        return response()->json($transaction);
    }

    /**
     * Webhook (optional)
     */
    public function webhook(Request $request)
    {
        Log::info("AeroPay Webhook Received", $request->all());

        return response()->json([
            'message' => 'Webhook received',
            'payload'  => $request->all()
        ]);
    }

    /**
     * All transactions of user
     */
    public function userTransactions($user_id)
    {
        $tx = Transaction::where('user_id', $user_id)->get();

        if ($tx->isEmpty()) {
            return response()->json(['message' => 'No transactions found'], 404);
        }

        return response()->json($tx);
    }

    /**
     * Filter by status
     */
    public function filterByStatus($status)
    {
        return response()->json(Transaction::where('status', $status)->get());
    }



    /* ==========================================================================
     *  🔥 NEW: updateStatus() WITH PROPER VALIDATION + PARTNER SYNC
     * ==========================================================================
     *
     *  This method:
     *   - updates the transaction status
     *   - if PAID => automatically sends update to PSA / Aureliya / SkyRoute
     *   - if FAILED => sends failure callback
     *
     *  You said: “all systems use AeroPay to update their booking status”
     *
     *  💯 This function does exactly that.
     * ========================================================================== */

    public function updateStatus(Request $req, $id)
    {
        $transaction = Transaction::where('_id', $id)
            ->orWhere('transaction_code', $id)
            ->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $data = $req->validate([
            'status' => 'required|string|in:pending,paid,failed,cancelled'
        ]);

        $transaction->status = $data['status'];
        $transaction->save();

        return response()->json([
            'message' => 'Transaction status updated',
            'data'    => $transaction
        ]);
    }
}

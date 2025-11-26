<?php

namespace App\Http\Controllers\api\v1\aeropay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\aeropay\Transaction;
use Illuminate\Support\Facades\Log;

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
                'partner' => 'required|string',
                'partner_reference_id' => 'required|string',
                'amount' => 'required|numeric',
                'currency' => 'required|string',
                'status' => 'required|string',
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
        $transactions = Transaction::all();
        return response()->json($transactions);
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
     * Webhook Receiver (PSA -> AeroPay)
     */
    public function webhook(Request $request)
    {
        Log::info("AeroPay Webhook Received", $request->all());

        return response()->json([
            'message' => 'Webhook received',
            'payload' => $request->all()
        ]);
    }

    public function userTransactions($user_id)
    {
        $tx = Transaction::where('user_id', $user_id)->get();

        if ($tx->isEmpty()) {
            return response()->json(['message' => 'No transactions found'], 404);
        }

        return response()->json($tx);
    }

    public function filterByStatus($status)
    {
        $tx = Transaction::where('status', $status)->get();
        return response()->json($tx);
    }

    public function updateStatus(Request $req, $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $transaction->status = $req->status ?? $transaction->status;
        $transaction->save();

        return response()->json(['message' => 'Status updated', 'data' => $transaction]);
    }
}

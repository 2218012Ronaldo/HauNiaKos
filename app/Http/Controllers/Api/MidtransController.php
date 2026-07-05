<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationFeed;
use App\Models\Transaction;
use App\Services\FonnteService;
use Illuminate\Http\Request;

class MidtransController extends Controller
{
    // private FonnteService $fonnteService;

    // public function __construct(FonnteService $fonnteService)
    // {
    //     $this->fonnteService = $fonnteService;
    // }

    public function callback(Request $request)
    {
        $serverKey = config('midtrans.serverKey');
        $hashedKey = hash(
            'sha512',
            $request->order_id.$request->status_code.$request->gross_amount.$serverKey,
        );

        if ($hashedKey !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        $transactionStatus = $request->transaction_status;
        $orderId = $request->order_id;
        $transaction = Transaction::firstWhere('code', $orderId);

        if (! $transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        switch ($transactionStatus) {
            case 'capture':
                if ($request->payment_type == 'credit_card') {
                    if ($request->fraud_status == 'challenge') {
                        $transaction->update(['payment_status' => 'pending']);
                    } else {
                        $transaction->update(['payment_status' => 'paid']);
                    }
                }
                break;
            case 'settlement':
                $transaction->update(['payment_status' => 'paid']);
                break;
            case 'pending':
                $transaction->update(['payment_status' => 'pending']);
                break;
            case 'deny':
                $transaction->update(['payment_status' => 'failed']);
                break;
            case 'expire':
                $transaction->update(['payment_status' => 'expired']);
                break;
            case 'cancel':
                $transaction->update(['payment_status' => 'failed']);
                break;
            default:
                $transaction->update(['payment_status' => 'unknown']);
                break;
        }

        // refresh the model so all attributes (like `name`) are available
        $transaction->refresh();

        if ($transaction->payment_status === 'paid') {
            $transaction->loadMissing('boardingHouse', 'room');
            NotificationFeed::recordPaymentSuccess($transaction);

            // Kirim notifikasi WhatsApp
            // $this->fonnteService->sendPaymentSuccessNotification($transaction);
        }

        return response()->json(['message' => 'Callback received successfully']);
    }
}
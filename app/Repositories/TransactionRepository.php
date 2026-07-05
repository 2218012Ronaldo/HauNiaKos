<?php

namespace App\Repositories;

use App\Interface\TransactionRepositoryInterface;
use App\Models\NotificationFeed;
use App\Models\Room;
use App\Models\Transaction;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getTransactionDataFromSession()
    {
        return session()->get('transaction');
    }

    public function saveTransactionDataToSession($data)
    {
        $transaction = session()->get('transaction', []);

        foreach ($data as $key => $value) {
            $transaction[$key] = $value;
        }

        session()->put('transaction', $transaction);
    }

    public function saveTransaction($data)
    {
        $room = Room::query()
            ->whereKey($data['room_id'])
            ->where('is_available', true)
            ->firstOrFail();

        $data = $this->prepareTransactionData($data, $room);
        $data['user_id'] = auth()->id();

        $transaction = Transaction::create($data);
        $transaction->loadMissing('boardingHouse', 'room');

        NotificationFeed::recordBookingPending($transaction);

        session()->forget('transaction');

        return $transaction;
    }

    public function getTransactionByCode($code)
    {
        return Transaction::firstWhere('code', $code);
    }

    public function getTransactionByCodeEmailPhone($code, $email, $phone)
    {
        return Transaction::query()
            ->where('code', $code)
            ->where('email', $email)
            ->where('phone_number', $phone)
            ->first();
    }

    private function prepareTransactionData($data, $room)
    {
        $data['code'] = $this->generateTransactionCode();
        $data['payment_status'] = 'pending';
        $data['approval_status'] = 'pending_owner';
        $data['transaction_date'] = now();

        // Set default payment_method if not provided
        if (! isset($data['payment_method'])) {
            $data['payment_method'] = 'full_payment';
        }

        // Format start_date ke MySQL
        if (! empty($data['start_date'])) {
            $data['start_date'] = \Carbon\Carbon::parse($data['start_date'])->format('Y-m-d H:i:s');
        }

        // Calculate the full total (subtotal + admin fee)
        $fullTotal = $this->calculateTotalAmount($room->price_per_month, $data['duration']);
        // Store the full total in total_amount (not the payment amount)
        $data['total_amount'] = $fullTotal;

        return $data;
    }

    private function generateTransactionCode()
    {
        return 'TRX'.rand(100000, 999999);
    }

    public function calculateTotalAmount($pricePerMonth, $duration)
    {
        // Price is now in USD, calculate directly
        $subtotal = $pricePerMonth * $duration;
        $adminFee = $subtotal * 0.03;

        return $subtotal + $adminFee;
    }

    public function calculatePaymentAmount($total, $paymentMethod)
    {
        // Calculate payment amount based on payment method
        // Total is now in USD, calculate directly
        return $paymentMethod === 'full_payment' ? $total : $total * 0.3;
    }
}
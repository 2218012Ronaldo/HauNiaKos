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
        $adminFee = $subtotal * 0.02;

        return $subtotal + $adminFee;
    }

    public function calculatePaymentAmount($total, $paymentMethod)
    {
        // Calculate payment amount based on payment method
        // Total is now in USD, calculate directly
        return $paymentMethod === 'full_payment' ? $total : $total * 0.3;
    }

    public function extendBooking($transaction, $additionalDuration, $paymentMethod)
    {
        $room = $transaction->room;
        
        // Use the end date of current transaction as start date for extension
        $extensionStartDate = $transaction->end_date;
        
        // Calculate payment amount only for additional months
        $extensionAmount = $this->calculateTotalAmount($room->price_per_month, $additionalDuration);
        
        // Create new transaction for extension
        $extensionData = [
            'boarding_house_id' => $transaction->boarding_house_id,
            'room_id' => $transaction->room_id,
            'user_id' => $transaction->user_id,
            'name' => $transaction->name,
            'email' => $transaction->email,
            'phone_number' => $transaction->phone_number,
            'gender' => $transaction->gender,
            'payment_method' => $paymentMethod,
            'start_date' => $extensionStartDate, // Use end date of current transaction
            'duration' => $additionalDuration, // Store only extension duration
            'total_amount' => $extensionAmount, // Store only extension amount for payment
            'parent_transaction_id' => $transaction->id, // Mark as extension
            'is_extension' => true, // Mark as extension
        ];
        
        // Prepare extension data
        $extensionData = $this->prepareExtensionTransactionData($extensionData);
        
        // Create extension transaction directly without room availability check
        $extensionTransaction = Transaction::create($extensionData);
        $extensionTransaction->loadMissing('boardingHouse', 'room');
        
        NotificationFeed::recordBookingPending($extensionTransaction);
        
        return $extensionTransaction;
    }
    
    private function prepareExtensionTransactionData($data)
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

        // Ensure parent_transaction_id is not set to self for extensions
        // This prevents extensions from being marked as completion payments
        if (isset($data['parent_transaction_id']) && $data['parent_transaction_id'] === 'self') {
            unset($data['parent_transaction_id']);
        }

        // total_amount is already calculated in extendBooking
        return $data;
    }

    public function createExtensionFromSession($extensionData)
    {
        \Log::info('createExtensionFromSession called', [
            'start_date' => $extensionData['start_date'],
            'duration' => $extensionData['duration'],
            'total_amount' => $extensionData['total_amount'],
            'parent_transaction_id' => $extensionData['parent_transaction_id'],
        ]);

        // Create extension transaction from session data
        $data = [
            'boarding_house_id' => $extensionData['boarding_house_id'],
            'room_id' => $extensionData['room_id'],
            'user_id' => $extensionData['user_id'],
            'name' => $extensionData['name'],
            'email' => $extensionData['email'],
            'phone_number' => $extensionData['phone_number'],
            'gender' => $extensionData['gender'],
            'payment_method' => $extensionData['payment_method'],
            'start_date' => $extensionData['start_date'],
            'duration' => $extensionData['duration'],
            'total_amount' => $extensionData['total_amount'],
            'parent_transaction_id' => $extensionData['parent_transaction_id'],
            'is_extension' => true,
        ];

        // Prepare extension data
        $data = $this->prepareExtensionTransactionData($data);

        \Log::info('Extension data prepared', [
            'start_date' => $data['start_date'],
            'duration' => $data['duration'],
            'total_amount' => $data['total_amount'],
        ]);

        // Create extension transaction
        $extensionTransaction = Transaction::create($data);
        $extensionTransaction->loadMissing('boardingHouse', 'room');

        \Log::info('Extension transaction created', [
            'id' => $extensionTransaction->id,
            'code' => $extensionTransaction->code,
            'start_date' => $extensionTransaction->start_date,
            'duration' => $extensionTransaction->duration,
            'total_amount' => $extensionTransaction->total_amount,
        ]);

        NotificationFeed::recordBookingPending($extensionTransaction);

        return $extensionTransaction;
    }
}
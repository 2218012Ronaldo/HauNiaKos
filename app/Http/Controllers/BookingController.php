<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingShowRequest;
use App\Http\Requests\CustomerInformationStoreRequest;
use App\Interface\BoardingHouseRepositoryInterface;
use App\Interface\TransactionRepositoryInterface;
use App\Models\NotificationFeed;
use App\Services\CurrencyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    private BoardingHouseRepositoryInterface $boardingHouseRepository;

    private TransactionRepositoryInterface $transactionRepository;

    private CurrencyService $currencyService;

    // Dependency Injection: Laravel otomatis inject repository
    public function __construct(
        BoardingHouseRepositoryInterface $boardingHouseRepository,
        TransactionRepositoryInterface $transactionRepository,
        CurrencyService $currencyService,
    ) {
        $this->boardingHouseRepository = $boardingHouseRepository;
        $this->transactionRepository = $transactionRepository;
        $this->currencyService = $currencyService;
    }

    public function booking(Request $request, string $slug): RedirectResponse
    {
        if ($response = $this->ensureBookingAllowed($request, $slug)) {
            return $response;
        }

        if ($response = $this->ensureRoomAvailable($request->integer('room_id'), $slug)) {
            return $response;
        }

        $this->transactionRepository->saveTransactionDataToSession($request->all());

        return redirect()->route('booking.cust-information', $slug);
    }

    public function information(Request $request, string $slug): View|RedirectResponse
    {
        if ($response = $this->ensureBookingAllowed($request, $slug)) {
            return $response;
        }

        $transaction = $this->transactionRepository->getTransactionDataFromSession();

        if (! $transaction) {
            return redirect()->route('kos.rooms', $slug);
        }

        if ($response = $this->ensureRoomAvailable(data_get($transaction, 'room_id'), $slug)) {
            return $response;
        }

        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);
        $room = $this->boardingHouseRepository->getBoardingHouseRoomById($transaction['room_id']);

        if (! $room) {
            return $this->redirectUnavailableRoom($slug);
        }

        return view(
            'pages.booking.cust-information',
            compact('transaction', 'boardingHouse', 'room'),
        );
    }

    public function saveInformation(
        CustomerInformationStoreRequest $request,
        string $slug,
    ): RedirectResponse {
        if ($response = $this->ensureBookingAllowed($request, $slug)) {
            return $response;
        }

        $data = $request->validated();
        $this->transactionRepository->saveTransactionDataToSession($data);

        // Create transaction with pending_owner status
        $transaction = $this->transactionRepository->saveTransaction(
            $this->transactionRepository->getTransactionDataFromSession(),
        );

        // Clear session after creating transaction
        session()->forget('transaction');

        return redirect()->route('booking.waiting-approval', [
            'slug' => $slug,
            'code' => $transaction->code,
        ]);
    }

    public function approveRejectFromNotification(Request $request): JsonResponse
    {
        $transactionCode = $request->input('transaction_code');
        $decision = $request->input('decision'); // 'approve' or 'reject'

        if (! $transactionCode || ! $decision) {
            return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $transaction = $this->transactionRepository->getTransactionByCode($transactionCode);

        if (! $transaction) {
            return response()->json(
                ['success' => false, 'message' => 'Transaction not found'],
                404,
            );
        }

        if (! $transaction->isPendingOwner()) {
            return response()->json(
                ['success' => false, 'message' => 'Transaction is not pending approval'],
                400,
            );
        }

        if ($decision === 'approve') {
            $transaction->approve();
            NotificationFeed::recordBookingApproved($transaction);
        } elseif ($decision === 'reject') {
            $transaction->reject();
            NotificationFeed::recordBookingRejected($transaction);
        }

        return response()->json(['success' => true, 'message' => 'Decision recorded successfully']);
    }

    public function waitingApproval(Request $request, string $slug): View|RedirectResponse
    {
        $code = $request->query('code');
        $transaction = $this->transactionRepository->getTransactionByCode($code);

        if (! $transaction) {
            return redirect()->route('home')->with('error', 'Transaction not found');
        }

        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);

        return view('pages.booking.waiting-approval', compact('transaction', 'boardingHouse'));
    }

    public function checkout(Request $request, string $slug): View|RedirectResponse
    {
        if ($response = $this->ensureBookingAllowed($request, $slug)) {
            return $response;
        }

        $transaction = $this->transactionRepository->getTransactionDataFromSession();

        if (! $transaction) {
            return redirect()->route('kos.rooms', $slug);
        }

        if ($response = $this->ensureRoomAvailable(data_get($transaction, 'room_id'), $slug)) {
            return $response;
        }

        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);
        $room = $this->boardingHouseRepository->getBoardingHouseRoomById($transaction['room_id']);

        if (! $room) {
            return $this->redirectUnavailableRoom($slug);
        }

        return view('pages.booking.checkout', compact('transaction', 'boardingHouse', 'room'));
    }

    public function payment(Request $request, string $slug): RedirectResponse
    {
        if ($response = $this->ensureBookingAllowed($request, $slug)) {
            return $response;
        }

        $transactionData = $this->transactionRepository->getTransactionDataFromSession();

        if (! $transactionData) {
            return redirect()->route('kos.rooms', $slug);
        }

        if ($response = $this->ensureRoomAvailable(data_get($transactionData, 'room_id'), $slug)) {
            return $response;
        }

        $this->transactionRepository->saveTransactionDataToSession($request->all());

        // Check if transaction code exists in session (from payNowFromNotification)
        $existingTransactionCode = session('existing_transaction_code');
        $transaction = null;

        if ($existingTransactionCode) {
            $transaction = $this->transactionRepository->getTransactionByCode(
                $existingTransactionCode,
            );
        }

        // If no existing transaction or not approved, create new transaction
        if (! $transaction || ! $transaction->isApproved()) {
            $transaction = $this->transactionRepository->saveTransaction(
                $this->transactionRepository->getTransactionDataFromSession(),
            );
        }

        // Update payment method if changed (total_amount remains the full total)
        $transactionData = $this->transactionRepository->getTransactionDataFromSession();
        if (
            isset($transactionData['payment_method']) &&
            $transactionData['payment_method'] !== $transaction->payment_method
        ) {
            $transaction->update([
                'payment_method' => $transactionData['payment_method'],
            ]);
        }

        // Check if transaction is approved before allowing payment
        if (! $transaction->isApproved()) {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Your booking is waiting for owner approval. You will be notified when it is approved.',
                );
        }

        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = config('midtrans.serverKey');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = config('midtrans.isProduction');
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = config('midtrans.isSanitized');
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = config('midtrans.is3ds');

        // Convert USD to IDR for Midtrans (Midtrans only supports IDR)
        // Calculate payment amount based on payment method using exact values
        $room = $transaction->room;
        $subtotal = $room->price_per_month * $transaction->duration;
        $adminFee = $subtotal * 0.02;
        $fullTotal = $subtotal + $adminFee;
        $paymentAmount = $transaction->payment_method === 'full_payment'
            ? $fullTotal
            : $fullTotal * 0.3;
        // Round to 2 decimal places before conversion to match frontend display
        $paymentAmount = round($paymentAmount, 2);
        $grossAmountInIdr = $this->currencyService->convertToIdr($paymentAmount);

        $params = [
            'transaction_details' => [
                'order_id' => $transaction->code,
                'gross_amount' => $grossAmountInIdr,
            ],
            'customer_details' => [
                'first_name' => $transaction->name,
                'email' => $transaction->email,
                'phone' => $transaction->phone_number,
            ],
        ];

        $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;

        return redirect($paymentUrl);

        // dd($transaction);
    }

    public function success(Request $request)
    {
        $transaction = $this->transactionRepository->getTransactionByCode($request->order_id);

        if (! $transaction) {
            return redirect()->route('home');
        }

        return view('pages.booking.success', compact('transaction'));
    }

    public function check()
    {
        return view('pages.booking.check-booking');
    }

    public function show(BookingShowRequest $request)
    {
        $transaction = $this->transactionRepository->getTransactionByCodeEmailPhone(
            $request->code,
            $request->email,
            $request->phone_number,
        );

        if (! $transaction) {
            return redirect()->route('check-booking')->with('error', 'Booking tidak ditemukan');
        }

        return view('pages.booking.detail', compact('transaction'));
    }

    public function payNowFromNotification(Request $request, string $slug)
    {
        $transactionCode = $request->query('transaction_code');

        if (! $transactionCode) {
            return redirect()->route('home')->with('error', 'Invalid transaction code');
        }

        $transaction = $this->transactionRepository->getTransactionByCode($transactionCode);

        if (! $transaction) {
            return redirect()->route('home')->with('error', 'Transaction not found');
        }

        if (! $transaction->isApproved()) {
            return redirect()
                ->route('home')
                ->with('error', 'Your booking is waiting for owner approval');
        }

        if ($transaction->payment_status === 'paid') {
            return redirect()->route('booking.success', ['order_id' => $transaction->code]);
        }

        // Prepare transaction data for session
        $transactionData = [
            'boarding_house_id' => $transaction->boarding_house_id,
            'room_id' => $transaction->room_id,
            'name' => $transaction->name,
            'email' => $transaction->email,
            'phone_number' => $transaction->phone_number,
            'gender' => $transaction->gender,
            'payment_method' => $transaction->payment_method,
            'start_date' => $transaction->start_date,
            'duration' => $transaction->duration,
            'total_amount' => $transaction->total_amount,
        ];

        $this->transactionRepository->saveTransactionDataToSession($transactionData);

        // Save existing transaction code to session so payment method can use it
        session()->put('existing_transaction_code', $transaction->code);

        return redirect()->route('booking.checkout', $slug);
    }

    private function ensureBookingAllowed(Request $request, string $slug): ?RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()
                ->route('kos.rooms', $slug)
                ->with('bookingAccessDenied', [
                    'role' => 'guest',
                    'title' => 'Please sign in as a tenant to book this room.',
                    'message' => 'Only tenant accounts are allowed to continue booking. Please use a user account to complete this reservation.',
                    'actionLabel' => 'Close',
                ]);
        }

        if ($user->isUser()) {
            return null;
        }

        $role = $user->role;

        return redirect()
            ->route('kos.rooms', $slug)
            ->with('bookingAccessDenied', [
                'role' => $role,
                'title' => $role === 'admin'
                        ? 'Booking is not available for this admin account.'
                        : 'Booking is not available for this owner account.',
                'message' => $role === 'admin'
                        ? 'This account is reserved for platform administration. Please use the admin dashboard to manage the system instead of creating a booking here.'
                        : 'Owner accounts are reserved for managing boarding house data, booking approvals, and their own transactions from the owner dashboard.',
                'actionLabel' => 'Close',
            ]);
    }

    private function ensureRoomAvailable(int|string|null $roomId, string $slug): ?RedirectResponse
    {
        if (! $roomId) {
            return $this->redirectUnavailableRoom($slug);
        }

        if ($this->boardingHouseRepository->getBoardingHouseRoomById($roomId) === null) {
            return $this->redirectUnavailableRoom($slug);
        }

        return null;
    }

    private function redirectUnavailableRoom(string $slug): RedirectResponse
    {
        return redirect()
            ->route('kos.rooms', $slug)
            ->with('bookingAccessDenied', [
                'role' => 'room_unavailable',
                'title' => 'This room is no longer available.',
                'message' => 'Please choose another available room to continue booking.',
                'actionLabel' => 'Close',
            ]);
    }
}
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
        \Log::info('Checkout: Starting', ['slug' => $slug]);
        
        if ($response = $this->ensureBookingAllowed($request, $slug)) {
            \Log::error('Checkout: Booking not allowed');
            return $response;
        }

        $transaction = $this->transactionRepository->getTransactionDataFromSession();

        if (! $transaction) {
            \Log::error('Checkout: No transaction in session');
            return redirect()->route('kos.rooms', $slug);
        }

        // Skip room availability check for extension transactions
        $isExtension = session('is_extension', false);
        
        \Log::info('Checkout method', [
            'is_extension' => $isExtension,
            'transaction' => $transaction,
            'extension_transaction_code' => session('extension_transaction_code')
        ]);

        if (! $isExtension) {
            if ($response = $this->ensureRoomAvailable(data_get($transaction, 'room_id'), $slug)) {
                \Log::error('Checkout: Room availability check failed');
                return $response;
            }
        }

        \Log::info('Checkout: Fetching boarding house', ['slug' => $slug]);
        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);
        
        \Log::info('Checkout: Boarding house fetched', ['found' => $boardingHouse ? 'yes' : 'no']);
        
        // For extension transactions with extension_data, get room from original transaction
        $extensionData = session('extension_data');
        if ($isExtension && $extensionData) {
            \Log::info('Checkout: Getting room from original transaction for extension');
            $parentTransaction = $this->transactionRepository->getTransactionByCode($extensionData['parent_transaction_code']);
            if ($parentTransaction && $parentTransaction->room) {
                $room = $parentTransaction->room;
                \Log::info('Checkout: Room fetched from parent transaction', ['room_id' => $room->id]);
            }
        } else {
            \Log::info('Checkout: Fetching room', ['room_id' => $transaction['room_id']]);
            $room = $this->boardingHouseRepository->getBoardingHouseRoomById($transaction['room_id']);

            \Log::info('Checkout: Room fetched', [
                'room_found' => $room ? 'yes' : 'no',
                'room_id' => $transaction['room_id']
            ]);

            // For extension transactions, if room not found, try to get it from the extension transaction
            if (! $room && $isExtension) {
                \Log::info('Checkout: Room not found, trying to get from extension transaction');
                $extensionTransactionCode = session('extension_transaction_code');
                if ($extensionTransactionCode) {
                    $extensionTransaction = $this->transactionRepository->getTransactionByCode($extensionTransactionCode);
                    if ($extensionTransaction && $extensionTransaction->room) {
                        $room = $extensionTransaction->room;
                        \Log::info('Checkout: Room fetched from extension transaction', ['room_id' => $room->id]);
                    }
                }
            }
        }

        if (! $room) {
            \Log::error('Checkout: Room not found, redirecting to rooms');
            return $this->redirectUnavailableRoom($slug);
        }

        \Log::info('Checkout: Rendering checkout view');
        return view('pages.booking.checkout', compact('transaction', 'boardingHouse', 'room', 'isExtension'));
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

        // Skip room availability check for extension transactions and completion payments
        $isExtension = session('is_extension', false);
        $skipRoomAvailability = session('skip_room_availability', false);
        $isCompletionPayment = data_get($transactionData, 'is_completion_payment', false);
        $existingTransactionCode = session('existing_transaction_code');
        $extensionData = session('extension_data');
        
        // Check if there's an existing approved transaction
        $skipForExistingTransaction = false;
        if ($existingTransactionCode) {
            $existingTransaction = $this->transactionRepository->getTransactionByCode($existingTransactionCode);
            $skipForExistingTransaction = $existingTransaction && $existingTransaction->isApproved();
        }
        
        // Skip if extension data exists (extension transaction will be created later)
        $skipForExtensionData = $extensionData !== null;
        
        \Log::info('Checkout: Room availability check', [
            'is_extension' => $isExtension,
            'skip_room_availability' => $skipRoomAvailability,
            'is_completion_payment' => $isCompletionPayment,
            'existing_transaction_code' => $existingTransactionCode,
            'skip_for_existing_transaction' => $skipForExistingTransaction,
            'extension_data_exists' => $skipForExtensionData,
            'should_skip' => $isExtension || $skipRoomAvailability || $isCompletionPayment || $skipForExistingTransaction || $skipForExtensionData
        ]);
        
        if (! $isExtension && ! $skipRoomAvailability && ! $isCompletionPayment && ! $skipForExistingTransaction && ! $skipForExtensionData) {
            if ($response = $this->ensureRoomAvailable(data_get($transactionData, 'room_id'), $slug)) {
                return $response;
            }
        }

        $this->transactionRepository->saveTransactionDataToSession($request->all());

        // Check if this is an extension transaction
        $extensionTransactionCode = session('extension_transaction_code');
        $extensionData = session('extension_data');
        $transaction = null;

        if ($isExtension && $extensionData) {
            // Create extension transaction from session data
            $extensionTransaction = $this->transactionRepository->createExtensionFromSession($extensionData);
            $transaction = $extensionTransaction;
            session()->put('extension_transaction_code', $extensionTransaction->code);
            
            \Log::info('Payment: Extension transaction created from session', [
                'extension_transaction_code' => $extensionTransaction->code
            ]);
        } elseif ($isExtension && $extensionTransactionCode) {
            // Use the existing extension transaction
            $transaction = $this->transactionRepository->getTransactionByCode($extensionTransactionCode);
            
            \Log::info('Payment: Extension transaction fetched', [
                'extension_transaction_code' => $extensionTransactionCode,
                'transaction_found' => $transaction ? 'yes' : 'no'
            ]);
            
            // If extension transaction not found, try to create it again
            if (! $transaction) {
                \Log::error('Payment: Extension transaction not found, creating new one');
                $transaction = $this->transactionRepository->saveTransaction(
                    $this->transactionRepository->getTransactionDataFromSession(),
                );
            }
            
            // Update payment method if changed
            if ($transaction && isset($transactionData['payment_method']) && 
                $transactionData['payment_method'] !== $transaction->payment_method) {
                $transaction->update([
                    'payment_method' => $transactionData['payment_method'],
                ]);
            }
        } else {
            // Check if transaction code exists in session (from payNowFromNotification)
            $existingTransactionCode = session('existing_transaction_code');

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
        }

        // For extension transactions, auto-approve since it's the same user extending
        if ($isExtension && $transaction && ! $transaction->isApproved()) {
            $transaction->approve();
            NotificationFeed::recordBookingApproved($transaction);
        }

        // Check if transaction exists before checking approval status
        if (! $transaction) {
            \Log::error('Payment: Transaction is null', [
                'is_extension' => $isExtension,
                'extension_transaction_code' => $extensionTransactionCode,
                'existing_transaction_code' => session('existing_transaction_code'),
                'transaction_data' => $transactionData
            ]);
            return redirect()
                ->route('kos.rooms', $slug)
                ->with(
                    'error',
                    'Transaction not found. Please try booking again.',
                );
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

        // Check if this is a completion payment (remaining 70% of down payment)
        $isCompletionPayment = session('is_completion_payment', false);
        $completionAmount = session('completion_amount', 0);

        // Clear extension session flags after payment processing
        if ($isExtension) {
            session()->forget('is_extension');
            session()->forget('extension_transaction_code');
            session()->forget('extension_data');
        }

        // Clear completion payment session flags after payment processing
        if ($isCompletionPayment) {
            session()->forget('is_completion_payment');
            session()->forget('completion_amount');
            session()->forget('skip_room_availability');
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
        
        // If this is a completion payment, use the remaining amount (70%)
        if ($isCompletionPayment && $completionAmount > 0) {
            $paymentAmount = $completionAmount;
        } else {
            $paymentAmount = $transaction->payment_method === 'full_payment'
                ? $fullTotal
                : $fullTotal * 0.3;
        }
        
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
        $orderId = $request->order_id;
        $transaction = null;

        // Check if this is a completion payment with mapped order_id
        $completionOrderMapping = session('completion_order_mapping');
        if ($completionOrderMapping && $completionOrderMapping['completion_order_id'] === $orderId) {
            $transaction = $this->transactionRepository->getTransactionByCode($completionOrderMapping['original_transaction_code']);
            
            // Update payment method to full payment and mark as completion payment
            if ($transaction && $transaction->payment_method === 'down_payment') {
                $transaction->update([
                    'payment_method' => 'full_payment',
                    'parent_transaction_id' => $transaction->id, // Mark as completion by pointing to itself
                ]);
            }
            
            session()->forget('completion_order_mapping');
        } else {
            $transaction = $this->transactionRepository->getTransactionByCode($orderId);
        }

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

    public function completePayment(Request $request, string $code)
    {
        $transaction = $this->transactionRepository->getTransactionByCode($code);
        
        if (! $transaction) {
            return redirect()->route('check-booking')->with('error', 'Booking tidak ditemukan');
        }

        if (! $transaction->canCompletePayment()) {
            return redirect()->route('check-booking')->with('error', 'Booking ini tidak dapat diselesaikan pembayarannya');
        }

        // Calculate remaining payment amount (70% of total)
        $remainingAmount = $transaction->getRemainingPaymentAmount();

        // Prepare transaction data for session with remaining amount
        $transactionData = [
            'boarding_house_id' => $transaction->boarding_house_id,
            'room_id' => $transaction->room_id,
            'name' => $transaction->name,
            'email' => $transaction->email,
            'phone_number' => $transaction->phone_number,
            'gender' => $transaction->gender,
            'payment_method' => 'full_payment',
            'start_date' => $transaction->start_date,
            'duration' => $transaction->duration,
            'total_amount' => $transaction->total_amount,
            'is_completion_payment' => true,
            'completion_amount' => $remainingAmount,
        ];

        $this->transactionRepository->saveTransactionDataToSession($transactionData);

        // Set existing transaction code so payment method uses the same transaction
        session()->put('existing_transaction_code', $transaction->code);
        session()->put('is_completion_payment', true);
        session()->put('skip_room_availability', true);

        \Log::info('CompletePayment: Session flags set', [
            'existing_transaction_code' => $transaction->code,
            'is_completion_payment' => session('is_completion_payment'),
            'skip_room_availability' => session('skip_room_availability')
        ]);

        // Redirect to process completion payment directly
        return redirect()->route('booking.complete-payment.process', ['code' => $transaction->code]);
    }

    public function processCompletionPayment(Request $request, string $code)
    {
        $transaction = $this->transactionRepository->getTransactionByCode($code);
        
        if (! $transaction) {
            return redirect()->route('check-booking')->with('error', 'Booking tidak ditemukan');
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

        // Get completion amount from session
        $completionAmount = session('completion_amount', 0);

        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = config('midtrans.serverKey');
        \Midtrans\Config::$isProduction = config('midtrans.isProduction');
        \Midtrans\Config::$isSanitized = config('midtrans.isSanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is3ds');

        // Use completion amount for payment
        $paymentAmount = $completionAmount > 0 ? $completionAmount : $transaction->getRemainingPaymentAmount();
        $paymentAmount = round($paymentAmount, 2);
        $grossAmountInIdr = $this->currencyService->convertToIdr($paymentAmount);

        // Generate unique order_id for completion payment to avoid Midtrans duplicate error
        $completionOrderId = $transaction->code . '-COMPLETION-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $completionOrderId,
                'gross_amount' => $grossAmountInIdr,
            ],
            'customer_details' => [
                'first_name' => $transaction->name,
                'email' => $transaction->email,
                'phone' => $transaction->phone_number,
            ],
        ];

        // Store mapping between completion order_id and original transaction code
        session()->put('completion_order_mapping', [
            'completion_order_id' => $completionOrderId,
            'original_transaction_code' => $transaction->code,
        ]);

        $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;

        // Clear session flags
        session()->forget('is_completion_payment');
        session()->forget('completion_amount');
        session()->forget('skip_room_availability');

        return redirect($paymentUrl);
    }

    public function showExtensionForm(Request $request, string $code)
    {
        $transaction = $this->transactionRepository->getTransactionByCode($code);

        if (! $transaction) {
            return redirect()->route('check-booking')->with('error', 'Booking tidak ditemukan');
        }

        if (! $transaction->canBeExtended()) {
            return redirect()->route('check-booking')->with('error', 'Booking ini tidak dapat diperpanjang');
        }

        return view('pages.booking.extend', compact('transaction'));
    }

    public function processExtension(Request $request, string $code)
    {
        \Log::info('processExtension START', [
            'code' => $code,
            'method' => $request->method(),
            'input' => $request->all()
        ]);
        
        $request->validate([
            'duration' => 'required|integer|min:1|max:12',
            'payment_method' => 'required|in:full_payment,down_payment',
        ]);

        \Log::info('Validation passed');

        $transaction = $this->transactionRepository->getTransactionByCode($code);

        \Log::info('Transaction fetched', ['found' => $transaction ? 'yes' : 'no']);

        if (! $transaction) {
            \Log::error('Transaction not found', ['code' => $code]);
            return redirect()->route('check-booking')->with('error', 'Booking tidak ditemukan');
        }

        \Log::info('Checking if can be extended');

        if (! $transaction->canBeExtended()) {
            \Log::error('Cannot extend', ['code' => $code]);
            return redirect()->route('check-booking')->with('error', 'Booking ini tidak dapat diperpanjang');
        }

        // Validate payment method: if original was down payment and not fully paid, extension must also be down payment
        if ($transaction->payment_method === 'down_payment' && $transaction->payment_status !== 'paid') {
            if ($request->payment_method === 'full_payment') {
                \Log::error('Invalid payment method for extension', [
                    'original_payment_method' => $transaction->payment_method,
                    'original_payment_status' => $transaction->payment_status,
                    'requested_payment_method' => $request->payment_method
                ]);
                return redirect()->route('check-booking')->with('error', 'Booking awal menggunakan down payment dan belum lunas. Extension harus menggunakan down payment juga.');
            }
        }

        \Log::info('Preparing extension data for session');

        // Clear any old extension transaction code from previous attempts
        session()->forget('extension_transaction_code');

        // Calculate extension data without creating transaction yet
        $room = $transaction->room;
        $extensionStartDate = $transaction->end_date;
        $extensionAmount = $this->transactionRepository->calculateTotalAmount($room->price_per_month, $request->duration);

        // Always find the ORIGINAL transaction to use as parent for all extensions
        // This creates a flat structure where all extensions point to the original
        $actualParentId = $transaction->id;
        
        if ($transaction->is_extension) {
            // This is an extension, find the original transaction
            $originalTransaction = \App\Models\Transaction::where('id', '!=', $transaction->id)
                ->where('boarding_house_id', $transaction->boarding_house_id)
                ->where('room_id', $transaction->room_id)
                ->where('user_id', $transaction->user_id)
                ->where('is_extension', false)
                ->orderBy('created_at', 'asc')
                ->first();
            
            if ($originalTransaction) {
                $actualParentId = $originalTransaction->id;
                \Log::info('Found original transaction for extension', [
                    'current_transaction_id' => $transaction->id,
                    'original_transaction_id' => $originalTransaction->id,
                ]);
            }
        } elseif ($transaction->parent_transaction_id === $transaction->id) {
            // This is a completion payment, find the original transaction
            $originalTransaction = \App\Models\Transaction::where('id', '!=', $transaction->id)
                ->where('boarding_house_id', $transaction->boarding_house_id)
                ->where('room_id', $transaction->room_id)
                ->where('user_id', $transaction->user_id)
                ->where('is_extension', false)
                ->orderBy('created_at', 'asc')
                ->first();
            
            if ($originalTransaction) {
                $actualParentId = $originalTransaction->id;
                \Log::info('Found original transaction for completion payment', [
                    'completion_transaction_id' => $transaction->id,
                    'original_transaction_id' => $originalTransaction->id,
                ]);
            }
        }

        // Store extension data in session for later creation after payment
        session()->put('extension_data', [
            'parent_transaction_code' => $transaction->code,
            'parent_transaction_id' => $actualParentId,
            'boarding_house_id' => $transaction->boarding_house_id,
            'room_id' => $transaction->room_id,
            'user_id' => $transaction->user_id,
            'name' => $transaction->name,
            'email' => $transaction->email,
            'phone_number' => $transaction->phone_number,
            'gender' => $transaction->gender,
            'payment_method' => $request->payment_method,
            'start_date' => $extensionStartDate,
            'duration' => $request->duration,
            'total_amount' => $extensionAmount,
        ]);

        // Mark session as extension to skip room availability check
        session()->put('is_extension', true);

        // Prepare session for payment
        $transactionData = [
            'boarding_house_id' => $transaction->boarding_house_id,
            'room_id' => $transaction->room_id,
            'name' => $transaction->name,
            'email' => $transaction->email,
            'phone_number' => $transaction->phone_number,
            'gender' => $transaction->gender,
            'payment_method' => $request->payment_method,
            'start_date' => $extensionStartDate,
            'duration' => $request->duration,
            'total_amount' => $extensionAmount,
        ];

        $this->transactionRepository->saveTransactionDataToSession($transactionData);

        \Log::info('Session saved');

        $slug = $transaction->boardingHouse->slug;
        $checkoutUrl = route('booking.checkout', ['slug' => $slug]);
        
        \Log::info('Redirecting', ['slug' => $slug, 'url' => $checkoutUrl]);

        return redirect($checkoutUrl);
    }
}
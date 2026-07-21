@extends('layouts.app')

@section('content')
    <div id="Background"
        class="absolute top-0 h-[230px] w-full rounded-b-[75px] bg-[linear-gradient(180deg,#F2F9E6_0%,#D2EDE4_100%)]"></div>
    <div id="TopNav" class="relative mt-[60px] flex items-center justify-between px-5">
        <a href="#" onclick="goBackOrHome('{{ route('home') }}')"
            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white">
            <img src="assets/images/icons/arrow-left.svg" class="h-[28px] w-[28px]" alt="icon">
        </a>
        <p class="font-semibold">My Booking Details</p>
        <div class="dummy-btn w-12"></div>
    </div>
    <div id="Header" class="relative mt-[18px] flex items-center justify-between gap-2 px-5">
        <div class="flex w-full flex-col gap-4 rounded-[30px] border border-[#F1F2F6] bg-white p-4">
            <div class="flex gap-4">
                <div class="flex h-[132px] w-[120px] shrink-0 overflow-hidden rounded-[30px] bg-[#D9D9D9]">
                    <img src="{{ asset('storage/' . ($transaction->boardingHouse->thumbnail ?? 'assets/images/thumbnails/boarding-house.png')) }}"
                        class="h-full w-full object-cover" alt="icon">
                </div>
                <div class="flex w-full flex-col gap-3">
                    <p class="line-clamp-2 min-h-[54px] text-lg font-semibold leading-[27px]">
                        {{ $transaction->boardingHouse->name }}</p>
                    <hr class="border-[#F1F2F6]">
                    <div class="flex items-center gap-[6px]">
                        <img src="{{ asset('assets/images/icons/location.svg') }}" class="flex h-5 w-5 shrink-0"
                            alt="icon">
                        <p class="text-ngekos-grey text-sm">{{ $transaction->boardingHouse->city->name }}</p>
                    </div>
                    <div class="flex items-center gap-[6px]">
                        <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="flex h-5 w-5 shrink-0"
                            alt="icon">
                        <p class="text-ngekos-grey text-sm">In {{ $transaction->boardingHouse->category->name }}</p>
                    </div>
                </div>
            </div>
            <hr class="border-[#F1F2F6]">
            <div class="flex gap-4">
                <div class="flex h-[156px] w-[120px] shrink-0 overflow-hidden rounded-[30px] bg-[#D9D9D9]">
                    <img src="{{ asset('storage/' . (optional($transaction->room->roomImages->first())->image ?? 'assets/images/thumbnails/room-1.png')) }}"
                        class="h-full w-full object-cover" alt="icon">
                </div>
                <div class="flex w-full flex-col gap-3">
                    <p class="text-lg font-semibold leading-[27px]">{{ $transaction->room->name }}</p>
                    <hr class="border-[#F1F2F6]">
                    <div class="flex items-center gap-[6px]">
                        <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="flex h-5 w-5 shrink-0"
                            alt="icon">
                        <p class="text-ngekos-grey text-sm">{{ $transaction->room->capacity }} People</p>
                    </div>
                    <div class="flex items-center gap-[6px]">
                        <img src="{{ asset('assets/images/icons/3dcube.svg') }}" class="flex h-5 w-5 shrink-0"
                            alt="icon">
                        <p class="text-ngekos-grey text-sm">{{ $transaction->room->square_feet }} sqft flat</p>
                    </div>
                    <hr class="border-[#F1F2F6]">
                    <p class="text-ngekos-orange text-lg font-semibold">
                        {{ formatUsd($transaction->room->price_per_month_usd) }}
                        <span class="text-ngekos-grey text-sm font-normal">/bulan</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div
        class="accordion group mx-5 mt-5 flex flex-col overflow-hidden rounded-[30px] bg-[#F5F6F8] p-5 transition-all duration-300 has-[:checked]:!h-[68px]">
        <label class="relative flex items-center justify-between">
            <p class="text-lg font-semibold">Customer</p>
            <img src="assets/images/icons/arrow-up.svg"
                class="flex h-[28px] w-[28px] shrink-0 transition-all duration-300 group-has-[:checked]:rotate-180"
                alt="icon">
            <input type="checkbox" class="absolute hidden">
        </label>
        <div class="flex flex-col gap-4 pt-[22px]">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="assets/images/icons/profile-2user.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                    <p class="text-ngekos-grey">Name</p>
                </div>
                <p class="font-semibold">{{ $transaction->name }}</p>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="assets/images/icons/sms.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                    <p class="text-ngekos-grey">Email</p>
                </div>
                <p class="font-semibold">{{ $transaction->email }}</p>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="assets/images/icons/call.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                    <p class="text-ngekos-grey">Phone</p>
                </div>
                <p class="font-semibold">{{ $transaction->phone_number }}</p>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/icons/gender.svg') }}" class="flex h-6 w-6 shrink-0" alt="icon">
                    <p class="text-ngekos-grey">Gender</p>
                </div>
                <p class="font-semibold">{{ ucfirst($transaction->gender) }}</p>
            </div>
        </div>
    </div>
    @php
        // Initialize variables
        $originalTransaction = null;
        $extensions = collect();
        $currentExtension = null;

        // Get the original transaction (parent) and all extensions
        if ($transaction->is_extension) {
            // For extension transactions, find the original transaction
            if ($transaction->parent_transaction_id && $transaction->parent_transaction_id !== $transaction->id) {
                $originalTransaction = \App\Models\Transaction::find($transaction->parent_transaction_id);
            }

            // If parent not found or is self, find the original by searching
            if (! $originalTransaction) {
                $originalTransaction = \App\Models\Transaction::where('id', '!=', $transaction->id)
                    ->where('boarding_house_id', $transaction->boarding_house_id)
                    ->where('room_id', $transaction->room_id)
                    ->where('user_id', $transaction->user_id)
                    ->where('is_extension', false)
                    ->orderBy('created_at', 'asc')
                    ->first();
            }

            // Get all extensions for the original transaction
            $extensions = $originalTransaction ?
                \App\Models\Transaction::where('parent_transaction_id', $originalTransaction->id)->where('is_extension', true)->get() :
                collect();

            // Current extension being viewed
            $currentExtension = $transaction;

            \Log::info('Booking detail view - extension transaction', [
                'current_transaction_id' => $transaction->id,
                'current_transaction_code' => $transaction->code,
                'current_start_date' => $transaction->start_date,
                'current_duration' => $transaction->duration,
                'original_transaction_id' => $originalTransaction ? $originalTransaction->id : 'not found',
                'original_transaction_code' => $originalTransaction ? $originalTransaction->code : 'not found',
                'original_start_date' => $originalTransaction ? $originalTransaction->start_date : 'not found',
                'original_duration' => $originalTransaction ? $originalTransaction->duration : 'not found',
            ]);
        } else {
            // For original transactions (including completion payments)
            $originalTransaction = $transaction;
            $extensions = \App\Models\Transaction::where('parent_transaction_id', $transaction->id)->where('is_extension', true)->get();
            $currentExtension = null;

            \Log::info('Booking detail view - original transaction', [
                'transaction_id' => $transaction->id,
                'transaction_code' => $transaction->code,
                'start_date' => $transaction->start_date,
                'duration' => $transaction->duration,
                'extensions_count' => $extensions->count(),
            ]);
        }
    @endphp

    @if ($transaction->is_extension && $originalTransaction)
        <div
            class="accordion group mx-5 mt-5 flex flex-col overflow-hidden rounded-[30px] bg-[#F5F6F8] p-5 transition-all duration-300 has-[:checked]:!h-[68px]">
            <label class="relative flex items-center justify-between">
                <p class="text-lg font-semibold">Original Booking</p>
                <img src="assets/images/icons/arrow-up.svg"
                    class="flex h-[28px] w-[28px] shrink-0 transition-all duration-300 group-has-[:checked]:rotate-180"
                    alt="icon">
                <input type="checkbox" class="absolute hidden">
            </label>
            <div class="flex flex-col gap-4 pt-[22px]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                        <p class="text-ngekos-grey">Booking ID</p>
                    </div>
                    <p class="font-semibold">{{ $originalTransaction->code }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="assets/images/icons/clock.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                        <p class="text-ngekos-grey">Duration</p>
                    </div>
                    <p class="font-semibold">{{ $originalTransaction->duration }} Months</p>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                        <p class="text-ngekos-grey">Started At</p>
                    </div>
                    <p class="font-semibold">{{ \Carbon\Carbon::parse($originalTransaction->start_date)->isoFormat('D MMMM YYYY') }}
                    </p>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                        <p class="text-ngekos-grey">Ended At</p>
                    </div>
                    <p class="font-semibold">
                        {{ \Carbon\Carbon::parse($originalTransaction->start_date)->addMonths(intval($originalTransaction->duration))->isoFormat('D MMMM YYYY') }}
                    </p>
                </div>
                
                @php
                    $subtotal = $originalTransaction->room->price_per_month * $originalTransaction->duration;
                    $adminFee = $subtotal * 0.02;
                    $total = $subtotal + $adminFee;
                    $downPayment = $total * 0.3;

                    $currencyService = app(\App\Services\CurrencyService::class);
                    $subtotalUsd = $currencyService->convertToUsdNormalized($subtotal);
                    $adminFeeUsd = $currencyService->convertToUsdNormalized($adminFee);
                    $totalUsd = $currencyService->convertToUsdNormalized($total);
                    $downPaymentUsd = $currencyService->convertToUsdNormalized($downPayment);
                @endphp
                
                <div class="mt-4 border-t border-gray-300 pt-4">
                    <p class="mb-3 font-semibold">Payment</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/card-tick.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Payment</p>
                        </div>
                        @if ($originalTransaction->payment_method === 'full_payment')
                            <p class="font-semibold">Full Payment 100%</p>
                        @else
                            <p class="font-semibold">Down Payment 30%</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/receipt-2.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Sub Total</p>
                        </div>
                        <p class="font-semibold">{{ formatUsd($subtotalUsd) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/receipt-disscount.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Admin Fee</p>
                        </div>
                        <p class="font-semibold">{{ formatUsd($adminFeeUsd) }}</p>
                    </div>
                    @if ($originalTransaction->payment_method === 'down_payment')
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                <p class="text-ngekos-grey">Down Payment (30%)</p>
                            </div>
                            <p class="font-semibold">{{ formatUsd($downPaymentUsd) }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                <p class="text-ngekos-grey">Remaining Payment (70%)</p>
                            </div>
                            <p class="font-semibold">{{ formatUsd($totalUsd - $downPaymentUsd) }}</p>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Grand total</p>
                        </div>
                        @if ($originalTransaction->payment_method === 'full_payment')
                            <p class="font-semibold">{{ formatUsd($totalUsd) }}</p>
                        @else
                            <p class="font-semibold">{{ formatUsd($downPaymentUsd) }}</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/security-card.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Status</p>
                        </div>
                        @if ($originalTransaction->payment_status === 'pending')
                            <p class="bg-ngekos-orange rounded-full p-[6px_12px] text-xs font-bold leading-[18px]">PENDING</p>
                        @else
                            <p class="rounded-full bg-[#91BF77] p-[6px_12px] text-xs font-bold leading-[18px]">SUCCESSFUL PAID</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($extensions->count() > 0 && $currentExtension)
            @foreach ($extensions as $extension)
                @if ($extension->id !== $currentExtension->id)
                    <div
                        class="accordion group mx-5 mt-5 flex flex-col overflow-hidden rounded-[30px] bg-[#F5F6F8] p-5 transition-all duration-300 has-[:checked]:!h-[68px]">
                        <label class="relative flex items-center justify-between">
                            <p class="text-lg font-semibold">Extension #{{ $loop->iteration }}</p>
                            <img src="assets/images/icons/arrow-up.svg"
                                class="flex h-[28px] w-[28px] shrink-0 transition-all duration-300 group-has-[:checked]:rotate-180"
                                alt="icon">
                            <input type="checkbox" class="absolute hidden">
                        </label>
                        <div class="flex flex-col gap-4 pt-[22px]">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                    <p class="text-ngekos-grey">Booking ID</p>
                                </div>
                                <p class="font-semibold">{{ $extension->code }}</p>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="assets/images/icons/clock.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                    <p class="text-ngekos-grey">Duration</p>
                                </div>
                                <p class="font-semibold">{{ $extension->duration }} Months</p>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                    <p class="text-ngekos-grey">Started At</p>
                                </div>
                                <p class="font-semibold">{{ \Carbon\Carbon::parse($extension->start_date)->isoFormat('D MMMM YYYY') }}
                                </p>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                    <p class="text-ngekos-grey">Ended At</p>
                                </div>
                                <p class="font-semibold">
                                    {{ \Carbon\Carbon::parse($extension->start_date)->addMonths(intval($extension->duration))->isoFormat('D MMMM YYYY') }}
                                </p>
                            </div>
                            
                            @php
                                $subtotal = $extension->room->price_per_month * $extension->duration;
                                $adminFee = $subtotal * 0.02;
                                $total = $subtotal + $adminFee;
                                $downPayment = $total * 0.3;

                                $currencyService = app(\App\Services\CurrencyService::class);
                                $subtotalUsd = $currencyService->convertToUsdNormalized($subtotal);
                                $adminFeeUsd = $currencyService->convertToUsdNormalized($adminFee);
                                $totalUsd = $currencyService->convertToUsdNormalized($total);
                                $downPaymentUsd = $currencyService->convertToUsdNormalized($downPayment);
                            @endphp
                            
                            <div class="mt-4 border-t border-gray-300 pt-4">
                                <p class="mb-3 font-semibold">Payment</p>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="assets/images/icons/card-tick.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                        <p class="text-ngekos-grey">Payment</p>
                                    </div>
                                    @if ($extension->payment_method === 'full_payment')
                                        <p class="font-semibold">Full Payment 100%</p>
                                    @else
                                        <p class="font-semibold">Down Payment 30%</p>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="assets/images/icons/receipt-2.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                        <p class="text-ngekos-grey">Sub Total</p>
                                    </div>
                                    <p class="font-semibold">{{ formatUsd($subtotalUsd) }}</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="assets/images/icons/receipt-disscount.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                        <p class="text-ngekos-grey">Admin Fee</p>
                                    </div>
                                    <p class="font-semibold">{{ formatUsd($adminFeeUsd) }}</p>
                                </div>
                                @if ($extension->payment_method === 'down_payment')
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                            <p class="text-ngekos-grey">Down Payment (30%)</p>
                                        </div>
                                        <p class="font-semibold">{{ formatUsd($downPaymentUsd) }}</p>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                            <p class="text-ngekos-grey">Remaining Payment (70%)</p>
                                        </div>
                                        <p class="font-semibold">{{ formatUsd($totalUsd - $downPaymentUsd) }}</p>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                        <p class="text-ngekos-grey">Grand total</p>
                                    </div>
                                    @if ($extension->payment_method === 'full_payment')
                                        <p class="font-semibold">{{ formatUsd($totalUsd) }}</p>
                                    @else
                                        <p class="font-semibold">{{ formatUsd($downPaymentUsd) }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="assets/images/icons/security-card.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                        <p class="text-ngekos-grey">Status</p>
                                    </div>
                                    @if ($extension->payment_status === 'pending')
                                        <p class="bg-ngekos-orange rounded-full p-[6px_12px] text-xs font-bold leading-[18px]">PENDING</p>
                                    @else
                                        <p class="rounded-full bg-[#91BF77] p-[6px_12px] text-xs font-bold leading-[18px]">SUCCESSFUL PAID</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        @if ($currentExtension)
            <div
                class="accordion group mx-5 mt-5 flex flex-col overflow-hidden rounded-[30px] bg-[#F5F6F8] p-5 transition-all duration-300 has-[:checked]:!h-[68px]">
                <label class="relative flex items-center justify-between">
                    <p class="text-lg font-semibold">Extension Booking (Current)</p>
                    <img src="assets/images/icons/arrow-up.svg"
                        class="flex h-[28px] w-[28px] shrink-0 transition-all duration-300 group-has-[:checked]:rotate-180"
                        alt="icon">
                    <input type="checkbox" class="absolute hidden">
                </label>
                <div class="flex flex-col gap-4 pt-[22px]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Booking ID</p>
                        </div>
                        <p class="font-semibold">{{ $currentExtension->code }}</p>
                    </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="assets/images/icons/clock.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                        <p class="text-ngekos-grey">Duration</p>
                    </div>
                    <p class="font-semibold">{{ $currentExtension->duration }} Months</p>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                        <p class="text-ngekos-grey">Started At</p>
                    </div>
                    <p class="font-semibold">{{ \Carbon\Carbon::parse($currentExtension->start_date)->isoFormat('D MMMM YYYY') }}
                    </p>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                        <p class="text-ngekos-grey">Ended At</p>
                    </div>
                    <p class="font-semibold">
                        {{ \Carbon\Carbon::parse($currentExtension->start_date)->addMonths(intval($currentExtension->duration))->isoFormat('D MMMM YYYY') }}
                    </p>
                </div>
                
                @php
                    $subtotal = $currentExtension->room->price_per_month * $currentExtension->duration;
                    $adminFee = $subtotal * 0.02;
                    $total = $subtotal + $adminFee;
                    $downPayment = $total * 0.3;

                    $currencyService = app(\App\Services\CurrencyService::class);
                    $subtotalUsd = $currencyService->convertToUsdNormalized($subtotal);
                    $adminFeeUsd = $currencyService->convertToUsdNormalized($adminFee);
                    $totalUsd = $currencyService->convertToUsdNormalized($total);
                    $downPaymentUsd = $currencyService->convertToUsdNormalized($downPayment);
                @endphp
                
                <div class="mt-4 border-t border-gray-300 pt-4">
                    <p class="mb-3 font-semibold">Payment</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/card-tick.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Payment</p>
                        </div>
                        @if ($currentExtension->payment_method === 'full_payment')
                            <p class="font-semibold">Full Payment 100%</p>
                        @else
                            <p class="font-semibold">Down Payment 30%</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/receipt-2.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Sub Total</p>
                        </div>
                        <p class="font-semibold">{{ formatUsd($subtotalUsd) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/receipt-disscount.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Admin Fee</p>
                        </div>
                        <p class="font-semibold">{{ formatUsd($adminFeeUsd) }}</p>
                    </div>
                    @if ($currentExtension->payment_method === 'down_payment')
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                <p class="text-ngekos-grey">Down Payment (30%)</p>
                            </div>
                            <p class="font-semibold">{{ formatUsd($downPaymentUsd) }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                <p class="text-ngekos-grey">Remaining Payment (70%)</p>
                            </div>
                            <p class="font-semibold">{{ formatUsd($totalUsd - $downPaymentUsd) }}</p>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Grand total</p>
                        </div>
                        @if ($currentExtension->payment_method === 'full_payment')
                            <p class="font-semibold">{{ formatUsd($totalUsd) }}</p>
                        @else
                            <p class="font-semibold">{{ formatUsd($downPaymentUsd) }}</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/security-card.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Status</p>
                        </div>
                        @if ($currentExtension->payment_status === 'pending')
                            <p class="bg-ngekos-orange rounded-full p-[6px_12px] text-xs font-bold leading-[18px]">PENDING</p>
                        @else
                            <p class="rounded-full bg-[#91BF77] p-[6px_12px] text-xs font-bold leading-[18px]">SUCCESSFUL PAID</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    @else
        <div
            class="accordion group mx-5 mt-5 flex flex-col overflow-hidden rounded-[30px] bg-[#F5F6F8] p-5 transition-all duration-300 has-[:checked]:!h-[68px]">
            <label class="relative flex items-center justify-between">
                <p class="text-lg font-semibold">Booking</p>
                <img src="assets/images/icons/arrow-up.svg"
                    class="flex h-[28px] w-[28px] shrink-0 transition-all duration-300 group-has-[:checked]:rotate-180"
                    alt="icon">
                <input type="checkbox" class="absolute hidden">
            </label>
            <div class="flex flex-col gap-4 pt-[22px]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                        <p class="text-ngekos-grey">Booking ID</p>
                    </div>
                    <p class="font-semibold">{{ $transaction->code }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="assets/images/icons/clock.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                        <p class="text-ngekos-grey">Duration</p>
                    </div>
                    <p class="font-semibold">{{ $transaction->duration }} Months</p>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                        <p class="text-ngekos-grey">Started At</p>
                    </div>
                    <p class="font-semibold">{{ \Carbon\Carbon::parse($transaction->start_date)->isoFormat('D MMMM YYYY') }}
                    </p>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                        <p class="text-ngekos-grey">Ended At</p>
                    </div>
                    <p class="font-semibold">
                        {{ \Carbon\Carbon::parse($transaction->start_date)->addMonths(intval($transaction->duration))->isoFormat('D MMMM YYYY') }}
                    </p>
                </div>
                
                @php
                    $subtotal = $transaction->room->price_per_month * $transaction->duration;
                    $adminFee = $subtotal * 0.02;
                    $total = $subtotal + $adminFee;
                    $downPayment = $total * 0.3;

                    $currencyService = app(\App\Services\CurrencyService::class);
                    $subtotalUsd = $currencyService->convertToUsdNormalized($subtotal);
                    $adminFeeUsd = $currencyService->convertToUsdNormalized($adminFee);
                    $totalUsd = $currencyService->convertToUsdNormalized($total);
                    $downPaymentUsd = $currencyService->convertToUsdNormalized($downPayment);
                @endphp
                
                <div class="mt-4 border-t border-gray-300 pt-4">
                    <p class="mb-3 font-semibold">Payment</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/card-tick.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Payment</p>
                        </div>
                        @if ($transaction->payment_method === 'full_payment')
                            <p class="font-semibold">Full Payment 100%</p>
                        @else
                            <p class="font-semibold">Down Payment 30%</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/receipt-2.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Sub Total</p>
                        </div>
                        <p class="font-semibold">{{ formatUsd($subtotalUsd) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/receipt-disscount.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Admin Fee</p>
                        </div>
                        <p class="font-semibold">{{ formatUsd($adminFeeUsd) }}</p>
                    </div>
                    @if ($transaction->payment_method === 'down_payment')
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                <p class="text-ngekos-grey">Down Payment (30%)</p>
                            </div>
                            <p class="font-semibold">{{ formatUsd($downPaymentUsd) }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                <p class="text-ngekos-grey">Remaining Payment (70%)</p>
                            </div>
                            <p class="font-semibold">{{ formatUsd($totalUsd - $downPaymentUsd) }}</p>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Grand total</p>
                        </div>
                        @if ($transaction->payment_method === 'full_payment')
                            <p class="font-semibold">{{ formatUsd($totalUsd) }}</p>
                        @else
                            <p class="font-semibold">{{ formatUsd($downPaymentUsd) }}</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="assets/images/icons/security-card.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Status</p>
                        </div>
                        @if ($transaction->payment_status === 'pending')
                            <p class="bg-ngekos-orange rounded-full p-[6px_12px] text-xs font-bold leading-[18px]">PENDING</p>
                        @else
                            <p class="rounded-full bg-[#91BF77] p-[6px_12px] text-xs font-bold leading-[18px]">SUCCESSFUL PAID</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($extensions->count() > 0)
            @foreach ($extensions as $extension)
                <div
                    class="accordion group mx-5 mt-5 flex flex-col overflow-hidden rounded-[30px] bg-[#F5F6F8] p-5 transition-all duration-300 has-[:checked]:!h-[68px]">
                    <label class="relative flex items-center justify-between">
                        <p class="text-lg font-semibold">Extension #{{ $loop->iteration }}</p>
                        <img src="assets/images/icons/arrow-up.svg"
                            class="flex h-[28px] w-[28px] shrink-0 transition-all duration-300 group-has-[:checked]:rotate-180"
                            alt="icon">
                        <input type="checkbox" class="absolute hidden">
                    </label>
                    <div class="flex flex-col gap-4 pt-[22px]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                <p class="text-ngekos-grey">Booking ID</p>
                            </div>
                            <p class="font-semibold">{{ $extension->code }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="assets/images/icons/clock.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                <p class="text-ngekos-grey">Duration</p>
                            </div>
                            <p class="font-semibold">{{ $extension->duration }} Months</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                <p class="text-ngekos-grey">Started At</p>
                            </div>
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($extension->start_date)->isoFormat('D MMMM YYYY') }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="assets/images/icons/calendar.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                <p class="text-ngekos-grey">Ended At</p>
                            </div>
                            <p class="font-semibold">
                                {{ \Carbon\Carbon::parse($extension->start_date)->addMonths(intval($extension->duration))->isoFormat('D MMMM YYYY') }}
                            </p>
                        </div>
                        
                        @php
                            $subtotal = $extension->room->price_per_month * $extension->duration;
                            $adminFee = $subtotal * 0.02;
                            $total = $subtotal + $adminFee;
                            $downPayment = $total * 0.3;

                            $currencyService = app(\App\Services\CurrencyService::class);
                            $subtotalUsd = $currencyService->convertToUsdNormalized($subtotal);
                            $adminFeeUsd = $currencyService->convertToUsdNormalized($adminFee);
                            $totalUsd = $currencyService->convertToUsdNormalized($total);
                            $downPaymentUsd = $currencyService->convertToUsdNormalized($downPayment);
                        @endphp
                        
                        <div class="mt-4 border-t border-gray-300 pt-4">
                            <p class="mb-3 font-semibold">Payment</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="assets/images/icons/card-tick.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                    <p class="text-ngekos-grey">Payment</p>
                                </div>
                                @if ($extension->payment_method === 'full_payment')
                                    <p class="font-semibold">Full Payment 100%</p>
                                @else
                                    <p class="font-semibold">Down Payment 30%</p>
                                @endif
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="assets/images/icons/receipt-2.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                    <p class="text-ngekos-grey">Sub Total</p>
                                </div>
                                <p class="font-semibold">{{ formatUsd($subtotalUsd) }}</p>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="assets/images/icons/receipt-disscount.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                    <p class="text-ngekos-grey">Admin Fee</p>
                                </div>
                                <p class="font-semibold">{{ formatUsd($adminFeeUsd) }}</p>
                            </div>
                            @if ($extension->payment_method === 'down_payment')
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                        <p class="text-ngekos-grey">Down Payment (30%)</p>
                                    </div>
                                    <p class="font-semibold">{{ formatUsd($downPaymentUsd) }}</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                        <p class="text-ngekos-grey">Remaining Payment (70%)</p>
                                    </div>
                                    <p class="font-semibold">{{ formatUsd($totalUsd - $downPaymentUsd) }}</p>
                                </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="assets/images/icons/receipt-text.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                    <p class="text-ngekos-grey">Grand total</p>
                                </div>
                                @if ($extension->payment_method === 'full_payment')
                                    <p class="font-semibold">{{ formatUsd($totalUsd) }}</p>
                                @else
                                    <p class="font-semibold">{{ formatUsd($downPaymentUsd) }}</p>
                                @endif
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="assets/images/icons/security-card.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                                    <p class="text-ngekos-grey">Status</p>
                                </div>
                                @if ($extension->payment_status === 'pending')
                                    <p class="bg-ngekos-orange rounded-full p-[6px_12px] text-xs font-bold leading-[18px]">PENDING</p>
                                @else
                                    <p class="rounded-full bg-[#91BF77] p-[6px_12px] text-xs font-bold leading-[18px]">SUCCESSFUL PAID</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endif

    <div class="mx-5 mt-3">
        <div class="mx-auto w-full max-w-[640px]">
            <div class="flex flex-col items-center gap-3 rounded-[30px] p-5">
                @if ($transaction->canCompletePayment())
                    <form action="{{ route('booking.complete-payment', $transaction->code) }}" method="GET" class="w-full max-w-[520px]">
                        <button type="submit"
                            class="cursor-pointer bg-emerald-500 block w-full rounded-full px-6 py-3 text-center font-bold text-white shadow-lg">
                            Complete Payment
                        </button>
                    </form>
                @elseif ($transaction->canBeExtended())
                    <form action="{{ route('booking.extend', $transaction->code) }}" method="GET" class="w-full max-w-[520px]">
                        <button type="submit"
                            class="cursor-pointer bg-emerald-500 block w-full rounded-full px-6 py-3 text-center font-bold text-white shadow-lg">
                            Extend Booking
                        </button>
                    </form>
                @endif
                <a href="#"
                    class="bg-ngekos-orange block w-full max-w-[520px] rounded-full px-6 py-3 text-center font-bold text-white shadow-lg">
                    Contact Customer Service
                </a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/accodion.js') }}"></script>
    <script>
        // Get all tab buttons
        const tabLinks = document.querySelectorAll('.tab-link');

        // Add click event listener to each button
        tabLinks.forEach(button => {
            button.addEventListener('click', () => {
                // Get the target tab id from the data attribute
                const targetTab = button.getAttribute('data-target-tab');
                console.log(targetTab)
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });

                // Show the target tab content
                document.querySelector(targetTab).classList.toggle('hidden');
            });
        });
    </script>
@endsection
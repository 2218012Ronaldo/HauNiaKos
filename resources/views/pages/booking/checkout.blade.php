@extends('layouts.app')

@section('content')
    <div id="Background"
        class="absolute top-0 h-[230px] w-full rounded-b-[75px] bg-[linear-gradient(180deg,#F2F9E6_0%,#D2EDE4_100%)]">
    </div>
    <div id="TopNav" class="relative mt-[60px] flex items-center justify-between px-5">
        <a href="#" onclick="goBackOrHome('{{ route('home') }}')"
            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white">
            <img src="{{ asset('assets/images/icons/arrow-left.svg') }}" class="h-[28px] w-[28px]" alt="icon">
        </a>
        <p class="font-semibold">Checkout Kost</p>
        <div class="dummy-btn w-12"></div>
    </div>
    <div id="Header" class="relative mt-[18px] flex items-center justify-between gap-2 px-5">
        <div class="flex w-full flex-col gap-4 rounded-[30px] border border-[#F1F2F6] bg-white p-4">
            <div class="flex gap-4">
                <div class="flex h-[132px] w-[120px] shrink-0 overflow-hidden rounded-[30px] bg-[#D9D9D9]">
                    <img src="{{ asset('storage/' . ($boardingHouse->thumbnail ?? 'assets/images/thumbnails/boarding-house.png')) }}"
                        class="h-full w-full object-cover" alt="icon">
                </div>
                <div class="flex w-full flex-col gap-3">
                    <p class="line-clamp-2 min-h-[54px] text-lg font-semibold leading-[27px]">{{ $boardingHouse->name }}</p>
                    <hr class="border-[#F1F2F6]">
                    <div class="flex items-center gap-[6px]">
                        <img src="{{ asset('assets/images/icons/location.svg') }}" class="flex h-5 w-5 shrink-0"
                            alt="icon">
                        <p class="text-ngekos-grey text-sm">{{ $boardingHouse->city->name }}</p>
                    </div>
                    <div class="flex items-center gap-[6px]">
                        <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="flex h-5 w-5 shrink-0"
                            alt="icon">
                        <p class="text-ngekos-grey text-sm">In {{ $boardingHouse->category->name }}</p>
                    </div>
                </div>
            </div>
                    <hr class="border-[#F1F2F6]">
                    <div class="flex gap-4">
                <div class="flex h-[156px] w-[120px] shrink-0 overflow-hidden rounded-[30px] bg-[#D9D9D9]">
                    <img src="{{ asset('storage/' . (optional($room->roomImages->first())->image ?? 'assets/images/thumbnails/room-1.png')) }}"
                        class="h-full w-full object-cover" alt="icon">
                </div>
                <div class="flex w-full flex-col gap-3">
                    <p class="text-lg font-semibold leading-[27px]">{{ $room->name }}</p>
                    <hr class="border-[#F1F2F6]">
                    <div class="flex items-center gap-[6px]">
                        <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="flex h-5 w-5 shrink-0"
                            alt="icon">
                        <p class="text-ngekos-grey text-sm">{{ $room->capacity }} People</p>
                    </div>
                    <div class="flex items-center gap-[6px]">
                        <img src="{{ asset('assets/images/icons/3dcube.svg') }}" class="flex h-5 w-5 shrink-0"
                            alt="icon">
                        <p class="text-ngekos-grey text-sm">{{ $room->square_feet }} sqft flat</p>
                    </div>
                    <hr class="border-[#F1F2F6]">
                    <p class="text-ngekos-orange text-lg font-semibold">
                        {{ formatUsd($room->price_per_month_usd) }}
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
            <img src="{{ asset('assets/images/icons/arrow-up.svg') }}"
                class="flex h-[28px] w-[28px] shrink-0 transition-all duration-300 group-has-[:checked]:rotate-180"
                alt="icon">
            <input type="checkbox" class="absolute hidden">
        </label>
        <div class="flex flex-col gap-4 pt-[22px]">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="flex h-6 w-6 shrink-0"
                        alt="icon">
                    <p class="text-ngekos-grey">Name</p>
                </div>
                <p class="font-semibold">{{ $transaction['name'] }}</p>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/icons/sms.svg') }}" class="flex h-6 w-6 shrink-0" alt="icon">
                    <p class="text-ngekos-grey">Email</p>
                </div>
                <p class="font-semibold">{{ $transaction['email'] }}</p>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/icons/call.svg') }}" class="flex h-6 w-6 shrink-0" alt="icon">
                    <p class="text-ngekos-grey">Phone</p>
                </div>
                <p class="font-semibold">{{ $transaction['phone_number'] }}</p>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span
                        class="text-ngekos-grey flex h-6 w-6 shrink-0 items-center justify-center text-xl font-semibold leading-none">
                        ⚥
                    </span>
                    <p class="text-ngekos-grey">Gender</p>
                </div>
                <p class="font-semibold">{{ ucfirst($transaction['gender'] ?? '') }}</p>
            </div>
        </div>
    </div>
    <div
        class="accordion group mx-5 mt-5 flex flex-col overflow-hidden rounded-[30px] bg-[#F5F6F8] p-5 transition-all duration-300 has-[:checked]:!h-[68px]">
        <label class="relative flex items-center justify-between">
            <p class="text-lg font-semibold">Booking</p>
            <img src="{{ asset('assets/images/icons/arrow-up.svg') }}"
                class="flex h-[28px] w-[28px] shrink-0 transition-all duration-300 group-has-[:checked]:rotate-180"
                alt="icon">
            <input type="checkbox" class="absolute hidden">
        </label>
        <div class="flex flex-col gap-4 pt-[22px]">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/icons/clock.svg') }}" class="flex h-6 w-6 shrink-0" alt="icon">
                    <p class="text-ngekos-grey">Duration</p>
                </div>
                <p class="font-semibold">{{ $transaction['duration'] }} Months</p>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/icons/calendar.svg') }}" class="flex h-6 w-6 shrink-0"
                        alt="icon">
                    <p class="text-ngekos-grey">Started At</p>
                </div>
                <p class="font-semibold">{{ \Carbon\Carbon::parse($transaction['start_date'])->isoFormat('D MMMM YYYY') }}
                </p>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/icons/calendar.svg') }}" class="flex h-6 w-6 shrink-0"
                        alt="icon">
                    <p class="text-ngekos-grey">Ended At</p>
                </div>
                <p class="font-semibold">
                    {{ \Carbon\Carbon::parse($transaction['start_date'])->addMonths(intval($transaction['duration']))->isoFormat('D MMMM YYYY') }}
                </p>
            </div>
        </div>
    </div>
    <form action="{{ route('booking.payment', $boardingHouse->slug) }}" class="relative mt-5 flex flex-col gap-6 pt-5"
        method="POST">
        @csrf
        <div id="PaymentOptions" class="mx-5 flex flex-col gap-4 rounded-[30px] border border-[#F1F2F6] p-5">
            <div id="TabButton-Container" class="flex items-center justify-between gap-[18px] border-b border-[#F1F2F6]">
                <label class="tab-link group relative flex flex-col justify-between gap-4"
                    data-target-tab="#DownPayment-Tab">
                    <input type="radio" name="payment_method" value="down_payment"
                        class="absolute left-1/2 top-1/2 -z-10 opacity-0" checked>
                    <div class="mx-auto flex items-center gap-3">
                        <div class="relative h-6 w-6">
                            <img src="{{ asset('assets/images/icons/status-orange.svg') }}"
                                class="absolute flex h-6 w-6 shrink-0 opacity-0 transition-all duration-300 group-has-[:checked]:opacity-100"
                                alt="icon">
                            <img src="{{ asset('assets/images/icons/status.svg') }}"
                                class="absolute flex h-6 w-6 shrink-0 opacity-100 transition-all duration-300 group-has-[:checked]:opacity-0"
                                alt="icon">
                        </div>
                        <p class="font-semibold">Down Payment</p>
                    </div>
                    <div
                        class="mx-auto w-0 transition-all duration-300 group-has-[:checked]:w-[90%] group-has-[:checked]:ring-1 group-has-[:checked]:ring-[#91BF77]">
                    </div>
                </label>
                <div class="mb-auto flex h-6 w-[1px] border border-[#F1F2F6]"></div>
                <label class="tab-link group relative flex flex-col justify-between gap-4"
                    data-target-tab="#FullPayment-Tab">
                    <input type="radio" name="payment_method" value="full_payment"
                        class="absolute left-1/2 top-1/2 -z-10 opacity-0">
                    <div class="mx-auto flex items-center gap-3">
                        <div class="relative h-6 w-6">
                            <img src="{{ asset('assets/images/icons/diamonds-orange.svg') }}"
                                class="absolute flex h-6 w-6 shrink-0 opacity-0 transition-all duration-300 group-has-[:checked]:opacity-100"
                                alt="icon">
                            <img src="{{ asset('assets/images/icons/diamonds.svg') }}"
                                class="absolute flex h-6 w-6 shrink-0 transition-all duration-300 group-has-[:checked]:opacity-0"
                                alt="icon">
                        </div>
                        <p class="font-semibold">Pay in Full</p>
                    </div>
                    <div
                        class="mx-auto w-0 transition-all duration-300 group-has-[:checked]:w-[90%] group-has-[:checked]:ring-1 group-has-[:checked]:ring-[#91BF77]">
                    </div>
                </label>
            </div>
            <div id="TabContent-Container">
                @php
                    $subtotal = $room->price_per_month * $transaction['duration']; // IDR
                    $adminFee = $subtotal * 0.02; // IDR
                    $total = $subtotal + $adminFee; // IDR
                    $downPayment = $total * 0.3; // IDR

                    $currencyService = app(\App\Services\CurrencyService::class);
                    $subtotalUsd = $currencyService->convertToUsdNormalized($subtotal);
                    $adminFeeUsd = $currencyService->convertToUsdNormalized($adminFee);
                    $totalUsd = $currencyService->convertToUsdNormalized($total);
                    $downPaymentUsd = $currencyService->convertToUsdNormalized($downPayment);
                @endphp
                <div id="DownPayment-Tab" class="tab-content flex flex-col gap-4">
                    <p class="text-ngekos-grey text-sm">Anda perlu melunasi pembayaran secara cash setelah melakukan
                        survey koskos</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/card-tick.svg') }}" class="flex h-6 w-6 shrink-0"
                                alt="icon">
                            <p class="text-ngekos-grey">Payment</p>
                        </div>
                        <p class="font-semibold">Down Payment 30%</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-2.svg') }}" class="flex h-6 w-6 shrink-0"
                                alt="icon">
                            <p class="text-ngekos-grey">Sub Total</p>
                        </div>
                        <p class="font-semibold">{{ formatUsd($subtotalUsd) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-disscount.svg') }}"
                                class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Admin Fee</p>
                        </div>
                        <p class="font-semibold">{{ formatUsd($adminFeeUsd) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-text.svg') }}" class="flex h-6 w-6 shrink-0"
                                alt="icon">
                            <p class="text-ngekos-grey">Grand total (30%)</p>
                        </div>
                        <p id="downPaymentPrice" class="font-semibold">{{ formatUsd($downPaymentUsd) }}</p>
                    </div>
                </div>
                <div id="FullPayment-Tab" class="tab-content flex hidden flex-col gap-4">
                    <p class="text-ngekos-grey text-sm">Anda tidak perlu membayar biaya tambahan apapun ketika
                        survey koskos</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/card-tick.svg') }}" class="flex h-6 w-6 shrink-0"
                                alt="icon">
                            <p class="text-ngekos-grey">Payment</p>
                        </div>
                        <p class="font-semibold">Full Payment 100%</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-2.svg') }}" class="flex h-6 w-6 shrink-0"
                                alt="icon">
                            <p class="text-ngekos-grey">Sub Total</p>
                        </div>
                        <p class="font-semibold">{{ formatUsd($subtotalUsd) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-disscount.svg') }}"
                                class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Admin Fee</p>
                        </div>
                        <p class="font-semibold">{{ formatUsd($adminFeeUsd) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-text.svg') }}" class="flex h-6 w-6 shrink-0"
                                alt="icon">
                            <p class="text-ngekos-grey">Grand total</p>
                        </div>
                        <p id="fullPaymentPrice" class="font-semibold">{{ formatUsd($totalUsd) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div id="BottomNav" class="relative flex h-[132px] w-full shrink-0">
            <div class="fixed bottom-5 z-10 w-full max-w-[640px] px-5">
                <div class="bg-ngekos-black flex items-center justify-between rounded-[40px] px-6 py-4">
                    <div class="flex flex-col gap-[2px]">
                        <p id="price" class="text-xl font-bold leading-[30px] text-white">
                            <!-- Price mengikuti pilihan yang dipilih dan diambil dari text grand total -->
                        </p>
                        <span class="text-sm text-white">Grand Total</span>
                    </div>
                    <button type="submit"
                        class="bg-ngekos-orange flex shrink-0 rounded-full px-5 py-[14px] font-bold text-white">Pay
                        Now</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    @vite(['resources/js/booking/checkout.js'])
    @vite(['resources/js/booking/accodion.js'])
@endsection
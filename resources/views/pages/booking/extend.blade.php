@extends('layouts.app')

@section('content')
    <div id="Background"
        class="absolute top-0 h-[180px] w-full rounded-b-[75px] bg-[linear-gradient(180deg,#F2F9E6_0%,#D2EDE4_100%)]"></div>
    <div id="TopNav" class="relative mt-[60px] flex items-center justify-between px-5">
        <a href="#" onclick="goBackOrHome('{{ route('check-booking') }}')"
            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white">
            <img src="{{ asset('assets/images/icons/arrow-left.svg') }}" class="h-[28px] w-[28px]" alt="icon">
        </a>
        <p class="font-semibold">Extend Booking</p>
        <div class="dummy-btn w-12"></div>
    </div>

    <div class="mx-5 mt-20">
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

    <div class="mx-5 mt-6">
        <div class="flex flex-col gap-4 rounded-[30px] bg-[#F5F6F8] p-5">
            <div class="flex items-center justify-between">
                <p class="text-ngekos-grey">Current End Date</p>
                <p class="font-semibold">
                    {{ \Carbon\Carbon::parse($transaction->start_date)->addMonths(intval($transaction->duration))->isoFormat('D MMMM YYYY') }}
                </p>
            </div>
            <div class="flex items-center justify-between">
                <p class="text-ngekos-grey">Room Price</p>
                <p class="font-semibold">{{ formatUsd($transaction->room->price_per_month_usd) }}/bulan</p>
            </div>
        </div>
    </div>

    <form action="/booking/{{ $transaction->code }}/extend" method="POST" class="relative mt-5 flex flex-col gap-6 pt-5" id="extendForm">
        @csrf
        <div id="DurationSection" class="mx-5 flex flex-col gap-4 rounded-[30px] border border-[#F1F2F6] p-5">
            <div class="flex items-center justify-between">
                <p class="text-ngekos-grey">Extension Duration</p>
                <div class="flex items-center gap-3">
                    <button type="button" id="decreaseDuration" class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F5F6F8] text-lg font-bold">-</button>
                    <input type="number" id="duration" name="duration" value="1" min="1" max="12" required
                        class="w-16 rounded-[20px] border border-[#E0E0E0] bg-white p-2 text-center font-semibold">
                    <button type="button" id="increaseDuration" class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F5F6F8] text-lg font-bold">+</button>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <p class="text-ngekos-grey">New End Date</p>
                <p id="newEndDate" class="font-semibold">
                    {{ \Carbon\Carbon::parse($transaction->start_date)->addMonths(intval($transaction->duration) + 1)->isoFormat('D MMMM YYYY') }}
                </p>
            </div>
        </div>

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
                    $roomPrice = $transaction->room->price_per_month; // IDR
                    $currencyService = app(\App\Services\CurrencyService::class);
                    $roomPriceUsd = $currencyService->convertToUsdNormalized($roomPrice);
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
                            <p class="text-ngekos-grey">Kos Price</p>
                        </div>
                        <p id="kosPrice" class="font-semibold">{{ formatUsd($roomPriceUsd) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-2.svg') }}" class="flex h-6 w-6 shrink-0"
                                alt="icon">
                            <p class="text-ngekos-grey">Sub Total</p>
                        </div>
                        <p id="subTotal" class="font-semibold">{{ formatUsd($roomPriceUsd) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-disscount.svg') }}"
                                class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Admin Fee</p>
                        </div>
                        <p id="adminFee" class="font-semibold">{{ formatUsd($roomPriceUsd * 0.02) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-text.svg') }}" class="flex h-6 w-6 shrink-0"
                                alt="icon">
                            <p class="text-ngekos-grey">Grand total (30%)</p>
                        </div>
                        <p id="downPaymentPrice" class="font-semibold">{{ formatUsd($roomPriceUsd * 1.02 * 0.3) }}</p>
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
                            <p class="text-ngekos-grey">Kos Price</p>
                        </div>
                        <p id="kosPriceFull" class="font-semibold">{{ formatUsd($roomPriceUsd) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-2.svg') }}" class="flex h-6 w-6 shrink-0"
                                alt="icon">
                            <p class="text-ngekos-grey">Sub Total</p>
                        </div>
                        <p id="subTotalFull" class="font-semibold">{{ formatUsd($roomPriceUsd) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-disscount.svg') }}"
                                class="flex h-6 w-6 shrink-0" alt="icon">
                            <p class="text-ngekos-grey">Admin Fee</p>
                        </div>
                        <p id="adminFeeFull" class="font-semibold">{{ formatUsd($roomPriceUsd * 0.02) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/icons/receipt-text.svg') }}" class="flex h-6 w-6 shrink-0"
                                alt="icon">
                            <p class="text-ngekos-grey">Grand total</p>
                        </div>
                        <p id="fullPaymentPrice" class="font-semibold">{{ formatUsd($roomPriceUsd * 1.02) }}</p>
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
                        class="cursor-pointer bg-ngekos-orange flex shrink-0 rounded-full px-5 py-[14px] font-bold text-white">Proceed to Payment</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/accodion.js') }}"></script>
    <script>
        // Format USD helper function
        function formatUsd(amount) {
            return '$' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Duration controls
        const durationInput = document.getElementById('duration');
        const decreaseBtn = document.getElementById('decreaseDuration');
        const increaseBtn = document.getElementById('increaseDuration');
        const newEndDateEl = document.getElementById('newEndDate');
        const currentEndDate = new Date('{{ \Carbon\Carbon::parse($transaction->start_date)->addMonths(intval($transaction->duration)) }}');

        decreaseBtn.addEventListener('click', () => {
            const currentValue = parseInt(durationInput.value);
            if (currentValue > 1) {
                durationInput.value = currentValue - 1;
                updateCalculations();
            }
        });

        increaseBtn.addEventListener('click', () => {
            const currentValue = parseInt(durationInput.value);
            if (currentValue < 12) {
                durationInput.value = currentValue + 1;
                updateCalculations();
            }
        });

        durationInput.addEventListener('change', updateCalculations);
        durationInput.addEventListener('input', updateCalculations);

        function updateCalculations() {
            const duration = parseInt(durationInput.value);
            const newEndDate = new Date(currentEndDate);
            newEndDate.setMonth(newEndDate.getMonth() + duration);
            
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            newEndDateEl.textContent = newEndDate.toLocaleDateString('en-US', options);

            // Update prices based on duration
            const kosPrice = parseFloat('{{ $roomPriceUsd }}');
            const subTotal = kosPrice * duration;
            const adminFee = subTotal * 0.02;
            const total = subTotal + adminFee;
            const downPayment = total * 0.3;

            document.getElementById('kosPrice').textContent = formatUsd(kosPrice);
            document.getElementById('subTotal').textContent = formatUsd(subTotal);
            document.getElementById('adminFee').textContent = formatUsd(adminFee);
            document.getElementById('downPaymentPrice').textContent = formatUsd(downPayment);
            
            document.getElementById('kosPriceFull').textContent = formatUsd(kosPrice);
            document.getElementById('subTotalFull').textContent = formatUsd(subTotal);
            document.getElementById('adminFeeFull').textContent = formatUsd(adminFee);
            document.getElementById('fullPaymentPrice').textContent = formatUsd(total);

            updatePrice();
        }

        // Tab functionality
        const tabLinks = document.querySelectorAll('.tab-link');
        tabLinks.forEach((button) => {
            button.addEventListener('click', () => {
                const targetTab = button.getAttribute('data-target-tab');
                document.querySelectorAll('.tab-content').forEach((content) => {
                    content.classList.add('hidden');
                });
                document.querySelector(targetTab).classList.toggle('hidden');
            });
        });

        // Price update
        const downPaymentPriceEl = document.getElementById('downPaymentPrice');
        const fullPaymentPriceEl = document.getElementById('fullPaymentPrice');
        const priceElement = document.getElementById('price');
        const paymentOptions = document.querySelectorAll('input[name="payment_method"]');

        function updatePrice() {
            const selectedPayment = document.querySelector('input[name="payment_method"]:checked').value;
            if (selectedPayment === 'down_payment') {
                priceElement.innerHTML = downPaymentPriceEl.textContent;
            } else if (selectedPayment === 'full_payment') {
                priceElement.innerHTML = fullPaymentPriceEl.textContent;
            }
        }

        paymentOptions.forEach((option) => {
            option.addEventListener('change', updatePrice);
        });

        // Initialize
        updateCalculations();
        updatePrice();
    </script>
@endsection
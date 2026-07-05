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
        <p class="font-semibold">Customer Information</p>
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
                    <p class="text-ngekos-orange text-lg font-semibold">Rp
                        {{ formatUsd($room->price_per_month) }}<span
                            class="text-ngekos-grey text-sm font-normal">/bulan</span></p>
                </div>
            </div>
        </div>
    </div>
    <form action="{{ route('booking.information.save', $boardingHouse->slug) }}"
        class="relative mt-5 flex flex-col gap-6 bg-[#F5F6F8] pt-5" method="POST">
        @csrf
        <div class="flex flex-col gap-[6px] px-5">
            <h1 class="text-lg font-semibold">Your Informations</h1>
            <p class="text-ngekos-grey text-sm">Fill the fields below with your valid data</p>
        </div>
        <div id="InputContainer" class="flex flex-col gap-[18px]">
            <div class="flex w-full flex-col gap-2 px-5">
                <p class="font-semibold">Complete Name</p>
                <label
                    class="@error('name') border border-red-500 focus-within:ring-0 @else focus-within:border focus-within:border-[#91BF77] focus-within:ring-1 focus-within:ring-[#91BF77] @enderror flex w-full items-center gap-3 rounded-full bg-white p-[14px_20px] transition-all transition-colors duration-300">
                    <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="flex h-5 w-5 shrink-0"
                        alt="icon">
                    <input type="text" name="name" id=""
                        class="placeholder:text-ngekos-grey w-full appearance-none font-semibold outline-none placeholder:font-normal"
                        placeholder="Write your name" value="{{ old('name') }}">
                </label>
                @error('name')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex w-full flex-col gap-2 px-5">
                <p class="font-semibold">Email Address</p>
                <label
                    class="@error('email') border border-red-500 focus-within:ring-0 @else focus-within:border focus-within:border-[#91BF77] focus-within:ring-1 focus-within:ring-[#91BF77] @enderror flex w-full items-center gap-3 rounded-full bg-white p-[14px_20px] transition-all transition-colors duration-300">
                    <img src="{{ asset('assets/images/icons/sms.svg') }}" class="flex h-5 w-5 shrink-0" alt="icon">
                    <input type="email" name="email" id=""
                        class="placeholder:text-ngekos-grey w-full appearance-none font-semibold outline-none placeholder:font-normal"
                        placeholder="Write your email" value="{{ old('email') }}">
                </label>
                @error('email')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex w-full flex-col gap-2 px-5">
                <p class="font-semibold">Phone No</p>
                <label
                    class="@error('phone') border border-red-500 focus-within:ring-0 @else focus-within:border focus-within:border-[#91BF77] focus-within:ring-1 focus-within:ring-[#91BF77] @enderror flex w-full items-center gap-3 rounded-full bg-white p-[14px_20px] transition-all transition-colors duration-300">
                    <img src="{{ asset('assets/images/icons/call.svg') }}" class="flex h-5 w-5 shrink-0" alt="icon">
                    <input type="tel" name="phone_number" id=""
                        class="placeholder:text-ngekos-grey w-full appearance-none font-semibold outline-none placeholder:font-normal"
                        placeholder="Write your phone" value="{{ old('phone') }}">
                </label>
                @error('phone')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex w-full flex-col gap-2 px-5">
                <p class="font-semibold">Gender</p>
                <label
                    class="@error('gender') border border-red-500 focus-within:ring-0 @else focus-within:border focus-within:border-[#91BF77] focus-within:ring-1 focus-within:ring-[#91BF77] @enderror flex w-full items-center gap-3 rounded-full bg-white p-[14px_20px] transition-all transition-colors duration-300">
                    <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="flex h-5 w-5 shrink-0"
                        alt="icon">
                    <select name="gender" id=""
                        class="w-full appearance-none bg-transparent font-semibold outline-none">
                        <option value="" disabled @selected(old('gender', data_get($transaction, 'gender')) === null || old('gender', data_get($transaction, 'gender')) === '')>
                            Select your gender
                        </option>
                        <option value="male" @selected(old('gender', data_get($transaction, 'gender')) === 'male')>
                            Male
                        </option>
                        <option value="female" @selected(old('gender', data_get($transaction, 'gender')) === 'female')>
                            Female
                        </option>
                    </select>
                </label>
                @error('gender')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center justify-between px-5">
                <p class="font-semibold">Duration in Month</p>
                <div class="relative flex w-fit items-center gap-[10px]">
                    <button type="button" id="Minus" class="h-12 w-12 flex-shrink-0">
                        <img src="{{ asset('assets/images/icons/minus.svg') }}" alt="icon">
                    </button>
                    <input id="Duration" type="text" value="1" name="duration"
                        class="w-[42px] appearance-none !bg-transparent text-center text-[22px] font-semibold leading-[33px] outline-none"
                        inputmode="numeric" pattern="[0-9]*">
                    <button type="button" id="Plus" class="h-12 w-12 flex-shrink-0">
                        <img src="{{ asset('assets/images/icons/plus.svg') }}" alt="icon">
                    </button>
                </div>
            </div>
            <div class="flex flex-col gap-3">
                <p class="px-5 font-semibold text-gray-800">Moving Date</p>
                <div class="px-5">
                    <div
                        class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-4 shadow-sm transition-all focus-within:border-[#91BF77] focus-within:ring-2 focus-within:ring-[#91BF77]/30">

                        <img src="/assets/images/icons/calendar.svg" class="h-6 w-6 opacity-70">

                        <input id="moving_date" name="start_date" placeholder="Select moving date"
                            class="w-full bg-transparent text-[15px] font-medium text-gray-700 outline-none placeholder:text-gray-400"
                            required readonly>

                    </div>
                </div>
            </div>
        </div>
        <div id="BottomNav" class="relative flex h-[132px] w-full shrink-0 bg-white">
            <div class="fixed bottom-5 z-10 w-full max-w-[640px] px-5">
                <div class="bg-ngekos-black flex items-center justify-between rounded-[40px] px-6 py-4">
                    <div class="flex flex-col gap-[2px]">
                        <p id="price" class="text-xl font-bold leading-[30px] text-white">
                            <!-- price dari js -->
                        </p>
                        <span class="text-sm text-white">Grand Total</span>
                    </div>
                    <button type="submit"
                        class="bg-ngekos-orange flex shrink-0 rounded-full px-5 py-[14px] font-bold text-white">Book
                        Now</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        window.defaultPrice = {{ $room->price_per_month }};
    </script>
    @vite(['resources/js/booking/cust-info.js'])
@endsection
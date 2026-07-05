@extends('layouts.app')

@section('content')
    <div id="Background"
        class="absolute top-0 h-[430px] w-full rounded-b-[75px] bg-[linear-gradient(180deg,#F2F9E6_0%,#D2EDE4_100%)]">
    </div>
    <div class="relative my-[60px] flex flex-col gap-[30px] px-5">
        <h1 class="text-center text-[30px] font-bold leading-[45px]">Check Your<br>Booking Details</h1>
        <form action="{{ route('check-booking.show') }}"
            class="flex flex-col gap-6 rounded-[30px] border border-[#F1F2F6] bg-white p-5" method="POST">
            @csrf
            <div class="flex flex-col gap-[6px]">
                <h1 class="text-lg font-semibold">Your Informations</h1>
                <p class="text-ngekos-grey text-sm">Fill the fields below with your valid data</p>
            </div>
            <div id="InputContainer" class="flex flex-col gap-[18px]">
                <div class="flex w-full flex-col gap-2">
                    <p class="font-semibold">Booking ID</p>
                    <label
                        class="@error('code') border border-red-500 focus-within:ring-0 @else focus-within:border focus-within:border-[#91BF77] focus-within:ring-1 focus-within:ring-[#91BF77] @enderror flex w-full items-center gap-3 rounded-full bg-white p-[14px_20px] ring-1 ring-[#F1F2F6] transition-all duration-300 focus-within:ring-[#91BF77]">
                        <img src="assets/images/icons/note-favorite-grey.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                        <input type="text" name="code" id="" value="{{ old('code', request('code')) }}"
                            class="placeholder:text-ngekos-grey w-full appearance-none font-semibold outline-none placeholder:font-normal"
                            placeholder="Write your booking id">

                    </label>
                    @error('code')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex w-full flex-col gap-2">
                    <p class="font-semibold">Email Address</p>
                    <label
                        class="@error('email') border border-red-500 focus-within:ring-0 @else focus-within:border focus-within:border-[#91BF77] focus-within:ring-1 focus-within:ring-[#91BF77] @enderror flex w-full items-center gap-3 rounded-full bg-white p-[14px_20px] ring-1 ring-[#F1F2F6] transition-all duration-300 focus-within:ring-[#91BF77]">
                        <img src="assets/images/icons/sms.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                        <input type="email" name="email" id="" value="{{ old('email', request('email')) }}"
                            class="placeholder:text-ngekos-grey w-full appearance-none font-semibold outline-none placeholder:font-normal"
                            placeholder="Write your email">
                    </label>
                    @error('email')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex w-full flex-col gap-2">
                    <p class="font-semibold">Phone No</p>
                    <label
                        class="@error('phone_number') border border-red-500 focus-within:ring-0 @else focus-within:border focus-within:border-[#91BF77] focus-within:ring-1 focus-within:ring-[#91BF77] @enderror flex w-full items-center gap-3 rounded-full bg-white p-[14px_20px] ring-1 ring-[#F1F2F6] transition-all duration-300 focus-within:ring-[#91BF77]">
                        <img src="assets/images/icons/call.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                        <input type="tel" name="phone_number" id=""
                            value="{{ old('phone_number', request('phone_number')) }}"
                            class="placeholder:text-ngekos-grey w-full appearance-none font-semibold outline-none placeholder:font-normal"
                            placeholder="Write your phone">
                    </label>
                    @error('phone_number')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="bg-ngekos-orange flex w-full justify-center rounded-full p-[14px_20px] font-bold text-white">View
                    My Booking</button>
            </div>
        </form>
    </div>
    <div id="BottomNav" class="relative flex h-[138px] w-full shrink-0">
        <nav class="fixed bottom-5 z-10 w-full max-w-[640px] px-5">
            <div class="bg-ngekos-black grid h-fit grid-cols-4 justify-between rounded-[40px] px-5 py-4">
                <a href="index.html" class="flex flex-col items-center gap-2 text-center">
                    <img src="assets/images/icons/global.svg" class="flex h-8 w-8 shrink-0" alt="icon">
                    <span class="text-sm font-semibold text-white">Discover</span>
                </a>
                <a href="check-booking.html" class="flex flex-col items-center gap-2 text-center">
                    <img src="assets/images/icons/note-favorite-green.svg" class="flex h-8 w-8 shrink-0" alt="icon">
                    <span class="text-sm font-semibold text-white">Orders</span>
                </a>
                <a href="find-kos.html" class="flex flex-col items-center gap-2 text-center">
                    <img src="assets/images/icons/search-status.svg" class="flex h-8 w-8 shrink-0" alt="icon">
                    <span class="text-sm font-semibold text-white">Find</span>
                </a>
                <a href="#" class="flex flex-col items-center gap-2 text-center">
                    <img src="assets/images/icons/24-support.svg" class="flex h-8 w-8 shrink-0" alt="icon">
                    <span class="text-sm font-semibold text-white">Help</span>
                </a>
            </div>
        </nav>
    </div>
    @include('includes.navigation')
@endsection
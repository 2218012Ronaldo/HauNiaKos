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
        <p class="font-semibold">Booking Status</p>
        <div class="dummy-btn w-12"></div>
    </div>

    <div class="relative mt-[18px] flex flex-col items-center justify-center px-5">
        <div class="flex w-full max-w-md flex-col items-center gap-6 rounded-[30px] border border-[#F1F2F6] bg-white p-8 text-center">
            <!-- Success Icon -->
            <div class="flex h-24 w-24 items-center justify-center rounded-full bg-orange-100">
                <svg class="h-12 w-12 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900">
                Booking Submitted Successfully
            </h1>

            <!-- Message -->
            <p class="text-gray-600">
                Your booking request for <span class="font-semibold">{{ $boardingHouse->name }}</span> has been submitted successfully.
            </p>

            <p class="text-gray-600">
                Your booking is currently <span class="font-semibold text-orange-500">pending approval</span> from the boarding house owner. You will receive a notification once your booking has been approved.
            </p>

            <!-- Transaction Code -->
            <div class="flex flex-col gap-2 rounded-xl bg-gray-50 p-4">
                <p class="text-sm text-gray-500">Transaction Code</p>
                <p class="text-lg font-bold text-gray-900">{{ $transaction->code }}</p>
            </div>

            <!-- Back to Home Button -->
            <a href="{{ route('home') }}"
                class="mt-4 flex w-full items-center justify-center rounded-full bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4 font-semibold text-white shadow-[0px_10px_18px_-12px_rgba(249,115,22,0.9)] transition hover:from-orange-600 hover:to-orange-700">
                Back to Home
            </a>
        </div>

        <!-- Additional Info -->
        <div class="mt-6 max-w-md text-center">
            <p class="text-sm text-gray-500">
                You can check your booking status anytime from your notifications or by using the transaction code.
            </p>
        </div>
    </div>
@endsection
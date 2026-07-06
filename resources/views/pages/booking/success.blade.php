@extends('layouts.app')

@section('content')
    <div id="Background"
        class="absolute top-0 h-[430px] w-full rounded-b-[75px] bg-[linear-gradient(180deg,#F2F9E6_0%,#D2EDE4_100%)]"></div>
    <div class="relative my-[60px] flex flex-col gap-[30px] px-5">
        <h1 class="text-center text-[30px] font-bold leading-[45px]">Booking Successful<br>Congratulations!</h1>
        <div id="Header" class="relative flex items-center justify-between gap-2">
            <div class="flex w-full flex-col gap-4 rounded-[30px] border border-[#F1F2F6] bg-white p-4">
                <div class="flex gap-4">
                    <div class="flex h-[132px] w-[120px] shrink-0 overflow-hidden rounded-[30px] bg-[#D9D9D9]">
                        <img src="{{ asset('storage/' . $transaction->boardingHouse->thumbnail) }}"
                            class="h-full w-full object-cover" alt="icon">
                    </div>
                    <div class="flex w-full flex-col gap-3">
                        <p class="line-clamp-2 min-h-[54px] text-lg font-semibold leading-[27px]">
                            {{ $transaction->boardingHouse->name }}</p>
                        <hr class="border-[#F1F2F6]">
                        <div class="flex items-center gap-[6px]">
                            <img src="assets/images/icons/location.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                            <p class="text-ngekos-grey text-sm">{{ $transaction->boardingHouse->city->name }}</p>
                        </div>
                        <div class="flex items-center gap-[6px]">
                            <img src="assets/images/icons/profile-2user.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                            <p class="text-ngekos-grey text-sm">In {{ $transaction->boardingHouse->category->name }}</p>
                        </div>
                    </div>
                </div>
                <hr class="border-[#F1F2F6]">
                <div class="flex gap-4">
                    <div class="flex h-[138px] w-[120px] shrink-0 overflow-hidden rounded-[30px] bg-[#D9D9D9]">
                        <img src="{{ asset('storage/' . $transaction->room->roomImages->first()->image) }}"
                            class="h-full w-full object-cover" alt="icon">
                    </div>
                    <div class="flex w-full flex-col gap-3">
                        <p class="text-lg font-semibold leading-[27px]">{{ $transaction->room->name }}</p>
                        <hr class="border-[#F1F2F6]">
                        <div class="flex items-center gap-[6px]">
                            <img src="assets/images/icons/profile-2user.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                            <p class="text-ngekos-grey text-sm">{{ $transaction->room->capacity }} People</p>
                        </div>
                        <div class="flex items-center gap-[6px]">
                            <img src="assets/images/icons/3dcube.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                            <p class="text-ngekos-grey text-sm">{{ $transaction->room->square_feet }} sqft flat</p>
                        </div>
                        <div class="flex items-center gap-[6px]">
                            <img src="assets/images/icons/calendar.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                            <p class="text-ngekos-grey text-sm">
                                {{ \Carbon\Carbon::parse($transaction->start_date)->isoFormat('D MMMM YYYY') }} -
                                {{ \Carbon\Carbon::parse($transaction->start_date)->addMonths((int) $transaction->duration)->isoFormat('D MMMM YYYY') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-[18px]">
            <p class="font-semibold">Your Booking ID</p>
            <div class="flex items-center gap-3 rounded-full bg-[#F5F6F8] p-[14px_20px]">
                <img src="assets/images/icons/note-favorite-green.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                <p class="font-semibold">{{ $transaction->code }}</p>
            </div>
        </div>
        <div class="flex flex-col gap-[14px]">
            <a href="{{ route('home') }}"
                class="bg-ngekos-orange w-full rounded-full p-[14px_20px] text-center font-bold text-white">Explore Other
                Kos</a>

            <form action="{{ route('check-booking.show') }}" method="POST">
                @csrf
                <input type="hidden" name="code" value="{{ $transaction->code }}">
                <input type="hidden" name="email" value="{{ $transaction->email }}">
                <input type="hidden" name="phone_number" value="{{ $transaction->phone_number }}">
                <button
                    class="text-ngekos-orange bg-ngekos-black w-full cursor-pointer rounded-full p-[14px_20px] text-center font-bold">
                    View My Booking
                </button>
            </form>

            </form>
        </div>
    </div>
@endsection
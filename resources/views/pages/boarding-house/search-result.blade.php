@extends('layouts.app')

@section('content')
    <div id="Background"
        class="absolute top-0 h-[570px] w-full rounded-b-[75px] bg-[linear-gradient(180deg,#F2F9E6_0%,#D2EDE4_100%)]"></div>
    <div id="TopNav" class="relative mt-[60px] flex items-center justify-between px-5">
        <a href="{{ route('find-kos') }}"
            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white">
            <img src="assets/images/icons/arrow-left.svg" class="h-[28px] w-[28px]" alt="icon">
        </a>
        <p class="font-semibold">Search Results</p>
        <div class="dummy-btn w-12"></div>
    </div>
    <div id="Header" class="relative mt-[18px] flex items-center justify-between gap-2 px-5">
        <div class="flex flex-col gap-[6px]">
            <h1 class="text-[32px] font-bold leading-[48px]">Search Result</h1>
            <p class="text-ngekos-grey">{{ $boardingHouses->count() }} results found</p>
        </div>
    </div>
    <section id="Result" class="relative mb-9 mt-5 flex flex-col gap-4 px-5">
        @foreach ($boardingHouses as $boardingHouse)
            <a href="{{ route('kos.show', ['slug' => $boardingHouse->slug]) }}" class="card">
                <div
                    class="flex gap-4 rounded-[30px] border border-[#F1F2F6] bg-white p-4 transition-all duration-300 hover:border-[#91BF77]">
                    <div class="flex h-[183px] w-[120px] shrink-0 overflow-hidden rounded-[30px] bg-[#D9D9D9]">
                        <img src="{{ asset('storage/' . ($boardingHouse->thumbnail ?? 'assets/images/thumbnails/kos-4.png')) }}"
                            class="h-full w-full object-cover" alt="thumbnail">
                    </div>
                    <div class="flex w-full flex-col gap-3">
                        <h3 class="line-clamp-2 min-h-[54px] text-lg font-semibold leading-[27px]">
                            {{ $boardingHouse->name }}</h3>
                        <hr class="border-[#F1F2F6]">
                        <div class="flex items-center gap-[6px]">
                            <img src="assets/images/icons/location.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                            <p class="text-ngekos-grey text-sm">{{ $boardingHouse->city->name }}</p>
                        </div>
                        <div class="flex items-center gap-[6px]">
                            <img src="assets/images/icons/profile-2user.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                            <p class="text-ngekos-grey text-sm">{{ $boardingHouse->rooms_sum_capacity ?? 0 }} People</p>
                        </div>
                        <hr class="border-[#F1F2F6]">
                        <p class="text-ngekos-orange text-lg font-semibold">
                            {{ formatUsd($boardingHouse->price) }}<span
                                class="text-ngekos-grey text-sm font-normal">/month</span></p>
                    </div>
                </div>
            </a>
        @endforeach
    </section>
@endsection
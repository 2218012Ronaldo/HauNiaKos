@extends('layouts.app')

@section('content')
    <div id="Background"
        class="absolute top-0 h-[570px] w-full rounded-b-[75px] bg-[linear-gradient(180deg,#F2F9E6_0%,#D2EDE4_100%)]">
    </div>
    <div id="TopNav" class="relative mt-[60px] flex items-center justify-between px-5">
        <a href="#" onclick="goBackOrHome('{{ route('home') }}')"
            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white">
            <img src="{{ asset('assets/images/icons/arrow-left.svg') }}" class="h-[28px] w-[28px]" alt="icon">
        </a>
        <p class="font-semibold">Browse Cities</p>
        <div class="dummy-btn w-12"></div>
    </div>
    <div class="relative mt-[18px] px-5">
        <h1 class="text-[32px] font-bold leading-[48px]">All Cities</h1>
        <p class="text-ngekos-grey mt-2">Temukan semua kota yang tersedia untuk pemesanan kost.</p>
    </div>

    <section id="Cities" class="relative mt-6 px-5 pb-10">
        <div class="grid grid-cols-2 gap-4">
            @foreach ($cities as $city)
                <a href="{{ route('city.show', ['slug' => $city->slug]) }}" class="card">
                    <div
                        class="flex items-center gap-3 overflow-hidden rounded-[22px] border border-white bg-white p-2.5 transition-all duration-300 hover:border-[#91BF77]">
                        <div
                            class="h-13.75 w-13.75 flex shrink-0 overflow-hidden rounded-full border-4 border-white ring-1 ring-[#F1F2F6]">
                            <img src="{{ asset('storage/' . $city->image) }}" class="h-full w-full object-cover"
                                alt="{{ $city->name }}">
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <h3 class="font-semibold">{{ $city->name }}</h3>
                            <p class="text-ngekos-grey text-sm">{{ $city->available_boarding_houses_count }} Kos</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endsection

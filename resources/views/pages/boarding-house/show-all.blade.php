@extends('layouts.app')

@section('content')
    @php
        $totalResults = count($boardingHouses);
    @endphp

    <div id="Background"
        class="absolute top-0 h-[570px] w-full rounded-b-[75px] bg-[linear-gradient(180deg,#F2F9E6_0%,#D2EDE4_100%)]">
    </div>
    <div id="TopNav" class="relative mt-[60px] flex items-center justify-between px-5">
        <a href="#" onclick="goBackOrHome('{{ route('home') }}')"
            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white">
            <img src="{{ asset('assets/images/icons/arrow-left.svg') }}" class="h-[28px] w-[28px]" alt="icon">
        </a>
        <p class="font-semibold">All Kost</p>
        <div class="dummy-btn w-12"></div>
    </div>
    <div id="Header" class="relative mt-[18px] flex items-center justify-between gap-2 px-5">
        <div class="flex flex-col gap-[6px]">
            <h1 class="text-[32px] font-bold leading-[48px]">Iben Kost</h1>
            <p class="text-ngekos-grey">Recommended for You</p>
        </div>
        <button class="flex shrink-0 flex-col items-center gap-2 rounded-[22px] bg-white p-[10px_20px] text-center">
            <img src="{{ asset('assets/images/icons/star.svg') }}" class="h-6 w-6" alt="icon">
            <p class="text-sm font-bold">Top</p>
        </button>
    </div>
    <div class="relative mt-4 px-5">
        <div class="flex items-center justify-between rounded-2xl border border-[#E6EAF0] bg-white px-4 py-3">
            <div class="flex items-center gap-2">
                <img src="{{ asset('assets/images/icons/result.svg') }}" class="h-5 w-5" alt="icon">
                <p class="text-sm text-slate-600">Kos Tersedia</p>
            </div>
            <span class="rounded-full bg-orange-50 px-3 py-1 text-sm font-semibold text-orange-500">
                {{ number_format($totalResults) }} Kos
            </span>
        </div>
    </div>
    <section id="Result" class="relative mb-36 mt-5 flex flex-col gap-3 px-3">
        @foreach ($boardingHouses as $index => $boardingHouse)
            <a href="{{ route('kos.show', ['slug' => $boardingHouse->slug]) }}"
                onclick="sessionStorage.setItem('backUrl', '{{ route('boarding-house.show-all') }}')" class="card">
                <div
                    class="relative rounded-3xl border border-[#E7EBF0] bg-white p-3 shadow-[0px_4px_14px_0px_#0000000D] transition-all duration-300 hover:border-[#91BF77]">
                    <div class="flex gap-3">
                        <div
                            class="relative aspect-square h-24 w-24 shrink-0 overflow-hidden rounded-[18px] bg-gradient-to-br from-slate-200 to-slate-100 shadow-sm ring-1 ring-slate-200/50">
                            @if ($index < 3)
                                <div
                                    class="absolute left-2 top-2 z-10 flex h-8 w-8 items-center justify-center rounded-full border-2 border-amber-300 bg-white shadow-md">
                                    <span class="block h-4 w-4 shrink-0"
                                        style="background: linear-gradient(135deg, #8B5E1E 0%, #D4AF37 42%, #FFF4C2 100%); mask-image: url('{{ asset('assets/images/icons/crown.svg') }}'); -webkit-mask-image: url('{{ asset('assets/images/icons/crown.svg') }}'); mask-repeat: no-repeat; -webkit-mask-repeat: no-repeat; mask-position: center; -webkit-mask-position: center; mask-size: contain; -webkit-mask-size: contain;"></span>
                                    <span class="absolute text-[8px] font-bold leading-none text-amber-700"
                                        style="bottom: -4px; right: -4px; background: linear-gradient(135deg, #8B5E1E 0%, #D4AF37 42%, #FFF4C2 100%); color: white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; border: 1px solid #D4AF37;">{{ $index + 1 }}</span>
                                </div>
                            @endif
                            <img src="{{ asset('storage/' . ($boardingHouse->thumbnail ?? 'assets/images/thumbnails/kos-4.png')) }}"
                                class="h-full w-full object-cover" alt="{{ $boardingHouse->name }}" loading="lazy">
                        </div>

                        <div class="flex min-w-0 flex-1 flex-col justify-between gap-1">
                            <div class="flex min-w-0 flex-col">
                                <div class="flex min-w-0 flex-1 items-start gap-2">
                                    <!-- Mobile Version (word-based, max 2 words) -->
                                    <h3 class="line-clamp-1 block text-base font-semibold leading-5 text-slate-900 md:hidden"
                                        title="{{ $boardingHouse->name }}">
                                        {{ \Illuminate\Support\Str::words($boardingHouse->name, 2, '...') }}
                                    </h3>
                                    <!-- Desktop Version (max 25 chars) -->
                                    <h3 class="line-clamp-1 hidden text-base font-semibold leading-5 text-slate-900 md:block"
                                        title="{{ $boardingHouse->name }}">
                                        {{ \Illuminate\Support\Str::limit($boardingHouse->name, 25, '...') }}
                                    </h3>
                                </div>
                            </div>
                            <hr class="my-1.5 border-0 border-t border-slate-200/80">
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                @php
                                    $gender = $boardingHouse['gender_type'] ?? 'mixed';
                                    $badgeColor =
                                        $gender === 'male'
                                            ? 'bg-blue-100 text-blue-700 border-blue-200'
                                            : ($gender === 'female'
                                                ? 'bg-pink-100 text-pink-700 border-pink-200'
                                                : 'bg-green-100 text-green-700 border-green-200');
                                    $icon = $gender === 'male' ? '♂' : ($gender === 'female' ? '♀' : '⚥');
                                    $label = $gender === 'male' ? 'Male' : ($gender === 'female' ? 'Female' : 'Mixed');
                                    $distanceValue = $boardingHouse->computed_distance ?? $boardingHouse->distance;
                                @endphp
                                <span
                                    class="{{ $badgeColor }} inline-flex items-center gap-1 rounded-full border px-2 py-0.5 align-middle text-[11px] font-medium">
                                    <span class="text-[12px]">{{ $icon }}</span> {{ $label }}
                                </span>

                                @if (($boardingHouse->available_rooms ?? 0) > 0)
                                    <span
                                        class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1.5 align-middle text-[11px] font-semibold leading-none tracking-normal text-emerald-700">
                                        {{ $boardingHouse->available_rooms }} Rooms Available
                                    </span>
                                @endif
                                {{-- @if (isset($boardingHouse->ahp_score))
                                    <div class="rounded-xl bg-orange-50 px-2 py-1 text-right">
                                        <p class="text-base font-bold leading-none text-orange-500">
                                            {{ number_format($boardingHouse->ahp_score * 100, 0) }}%</p>
                                        <p class="text-[10px] font-semibold leading-none text-orange-500">Rekomendasi</p>
                                    </div>
                                @endif --}}
                            </div>

                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <img src="{{ asset('assets/images/icons/location.svg') }}" class="h-3.5 w-3.5"
                                    alt="lokasi">
                                <span>{{ $boardingHouse->city->name }}</span>
                                @if (isset($boardingHouse->distance))
                                    <span>· {{ number_format((float) $boardingHouse->distance, 1) }} km</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="h-3.5 w-3.5"
                                    alt="kapasitas">
                                <span>{{ $boardingHouse->available_rooms_sum_capacity ?? 0 }} People</span>
                                @if (!is_null($boardingHouse->testimonials_avg_rating))
                                    <span>· ⭐ {{ number_format((float) $boardingHouse->testimonials_avg_rating, 1) }}
                                        ({{ $boardingHouse->testimonials_count ?? 0 }} Review)
                                    </span>
                                @endif
                            </div>

                            <hr class="my-1.5 border-0 border-t border-slate-200/80">

                            <p class="text-lg font-bold text-orange-500">
                                {{ formatUsd($boardingHouse->price) }}<span
                                    class="text-sm font-normal text-slate-500">/month</span></p>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </section>
@endsection
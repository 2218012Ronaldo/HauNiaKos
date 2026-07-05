@extends('layouts.app')

@section('content')
    @php
        $bookingRestriction = session('bookingAccessDenied');
        $currentRole = auth()->user()?->role ?? 'guest';
        $canBook = auth()->user()?->isUser();
    @endphp

    <div id="Background"
        class="absolute top-0 h-[230px] w-full rounded-b-[75px] bg-[linear-gradient(180deg,#F2F9E6_0%,#D2EDE4_100%)]">
    </div>
    <div id="TopNav" class="relative mt-[60px] flex items-center justify-between px-5">
        <a href="#" onclick="goBackOrHome('{{ route('home') }}')"
            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white">
            <img src="{{ asset('assets/images/icons/arrow-left.svg') }}" class="h-[28px] w-[28px]" alt="icon">
        </a>
        <p class="font-semibold">Choose Available Room</p>
        <div class="dummy-btn w-12"></div>
    </div>
    <div id="Header" class="relative mt-[18px] flex items-center justify-between gap-2 px-5">
        <div class="flex w-full gap-4 rounded-[30px] border border-[#F1F2F6] bg-white p-4">
            <div class="flex h-[132px] w-[120px] shrink-0 overflow-hidden rounded-[30px] bg-[#D9D9D9]">
                <img src="{{ asset('storage/' . $boardingHouse->thumbnail) }}" class="h-full w-full object-cover"
                    alt="icon">
            </div>
            <div class="flex w-full flex-col gap-3">
                <h1 class="line-clamp-2 min-h-[54px] text-lg font-semibold leading-[27px]">{{ $boardingHouse->name }}</h1>
                <hr class="border-[#F1F2F6]">
                <div class="flex items-center gap-[6px]">
                    <img src="{{ asset('assets/images/icons/location.svg') }}" class="flex h-5 w-5 shrink-0" alt="icon">
                    <p class="text-ngekos-grey text-sm">{{ $boardingHouse->city->name }}</p>
                </div>
                <div class="flex items-center gap-[6px]">
                    <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="flex h-5 w-5 shrink-0"
                        alt="icon">
                    <p class="text-ngekos-grey text-sm">In {{ $boardingHouse->category->name }}</p>
                </div>
            </div>
        </div>
    </div>
    <form action="{{ route('booking', $boardingHouse->slug) }}" class="relative mt-5 flex flex-col gap-4">
        <input type="hidden" name="boarding_house_id" value="{{ $boardingHouse->id }}">
        <h2 class="px-5 font-bold">Available Rooms</h2>
        <div id="RoomsContainer" class="flex flex-col gap-4 px-5">
            @foreach ($boardingHouse->rooms as $room)
                <label class="group relative">
                    <input type="radio" name="room_id" class="absolute left-1/2 top-1/2 -z-10 opacity-0"
                        value="{{ $room->id }}" required>
                    <div
                        class="flex gap-4 rounded-[30px] border border-[#F1F2F6] bg-white p-4 transition-all duration-300 hover:border-[#91BF77] group-has-[:checked]:ring-2 group-has-[:checked]:ring-[#91BF77]">
                        <div class="flex h-[156px] w-[120px] shrink-0 overflow-hidden rounded-[30px] bg-[#D9D9D9]">
                            <img src="{{ asset('storage/' . optional($room->roomImages->first())->image ?? 'assets/images/thumbnails/room-1.png') }}"
                                class="h-full w-full object-cover" alt="icon">
                        </div>
                        <div class="flex w-full flex-col gap-3">
                            <h3 class="text-lg font-semibold leading-[27px]">{{ $room->name }}</h3>
                            <hr class="border-[#F1F2F6]">
                            <div class="flex items-center gap-[6px]">
                                <img src="{{ asset('assets/images/icons/profile-2user.svg') }}"
                                    class="flex h-5 w-5 shrink-0" alt="icon">
                                <p class="text-ngekos-grey text-sm"> {{ $room->capacity }} People
                                </p>
                            </div>
                            <div class="flex items-center gap-[6px]">
                                <img src="{{ asset('assets/images/icons/3dcube.svg') }}" class="flex h-5 w-5 shrink-0"
                                    alt="icon">
                                <p class="text-ngekos-grey text-sm">{{ $room->square_feet }} sqft flat</p>
                            </div>
                            <hr class="border-[#F1F2F6]">
                            <p class="text-ngekos-orange text-lg font-semibold">
                                {{ formatUsd($room->price_per_month) }}<span
                                    class="text-ngekos-grey text-sm font-normal">/month</span></p>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
        <div id="BottomButton" class="relative flex h-[98px] w-full shrink-0">
            <div class="fixed bottom-[30px] z-10 w-full max-w-[640px] px-5">
                @if ($canBook)
                    <button class="bg-ngekos-orange w-full rounded-full p-[14px_20px] text-center font-bold text-white">
                        Continue Booking</button>
                @else
                    <button type="button" onclick="openBookingRestrictionModal('{{ $currentRole }}')"
                        class="bg-ngekos-orange w-full rounded-full p-[14px_20px] text-center font-bold text-white">
                        Continue Booking</button>
                @endif
            </div>
        </div>
    </form>

    <div id="bookingRestrictionModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 py-6">
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-[2px]" onclick="closeBookingRestrictionModal()">
        </div>

        <div
            class="bg-white/96 relative z-10 mx-auto max-h-[min(100vh-3rem,90vh)] w-full max-w-[min(92vw,28rem)] overflow-hidden overflow-y-auto rounded-[28px] border border-white/70 p-5 shadow-[0px_28px_70px_-28px_rgba(15,23,42,0.5)] ring-1 ring-white/70 md:p-6">
            <div class="absolute -left-12 -top-14 h-28 w-28 rounded-full bg-orange-200/45 blur-3xl"></div>
            <div class="absolute -right-10 top-4 h-24 w-24 rounded-full bg-sky-200/45 blur-3xl"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div class="space-y-2">
                    <span
                        class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-white">Booking
                        Access</span>
                    <div>
                        <h3 id="bookingRestrictionTitle" class="text-xl font-bold leading-tight text-slate-900 md:text-2xl">
                            Booking is unavailable</h3>
                        <p id="bookingRestrictionMessage"
                            class="mt-2 max-w-xs text-sm leading-6 text-slate-500 md:max-w-sm">
                            Only tenant accounts can continue with booking.</p>
                    </div>
                </div>

                <button type="button" aria-label="Close" onclick="closeBookingRestrictionModal()"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                    ✕
                </button>
            </div>

            <div class="relative mt-6 flex items-center justify-end gap-3">
                <button type="button" onclick="closeBookingRestrictionModal()"
                    class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Close
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const bookingRestrictionModal = document.getElementById('bookingRestrictionModal');
        const bookingRestrictionTitle = document.getElementById('bookingRestrictionTitle');
        const bookingRestrictionMessage = document.getElementById('bookingRestrictionMessage');

        const bookingRestrictionMessages = {
            guest: {
                title: 'Please sign in as a tenant to book this room.',
                message: 'Only tenant accounts are allowed to continue booking. Please use a user account to complete this reservation.',
            },
            admin: {
                title: 'Booking is unavailable for admin accounts.',
                message: 'This account is reserved for platform administration. Please use the admin dashboard to manage the system instead of creating a booking here.',
            },
            owner_kost: {
                title: 'Booking is unavailable for owner accounts.',
                message: 'Owner accounts are reserved for managing boarding house data, booking approvals, and their own transactions from the owner dashboard.',
            },

            room_unavailable: {
                title: 'This room is no longer available.',
                message: 'Please choose another available room to continue booking.',
            },
        };

        function openBookingRestrictionModal(role) {
            if (!bookingRestrictionModal) {
                return;
            }

            const content = bookingRestrictionMessages[role] ?? bookingRestrictionMessages.guest;

            if (bookingRestrictionTitle) {
                bookingRestrictionTitle.textContent = content.title;
            }

            if (bookingRestrictionMessage) {
                bookingRestrictionMessage.textContent = content.message;
            }

            bookingRestrictionModal.classList.remove('hidden');
            bookingRestrictionModal.classList.add('flex');
        }

        function closeBookingRestrictionModal() {
            if (!bookingRestrictionModal) {
                return;
            }

            bookingRestrictionModal.classList.add('hidden');
            bookingRestrictionModal.classList.remove('flex');
        }

        @if ($bookingRestriction)
            document.addEventListener('DOMContentLoaded', function() {
                openBookingRestrictionModal(@js($bookingRestriction['role'] ?? 'guest'));
            });
        @endif
    </script>
@endsection
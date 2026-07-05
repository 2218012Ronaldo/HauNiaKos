@extends('layouts.app')

@section('content')
    @php
        $owner = $boardingHouse->owner;
        $ownerName = $owner?->name ?? 'Owner kos';
        $ownerPhone = $owner?->phone;
        $ownerPhoneDigits = preg_replace('/\D+/', '', $ownerPhone ?? '');
        $ownerPhoneForWhatsapp = $ownerPhoneDigits;

        if (str_starts_with($ownerPhoneForWhatsapp, '0')) {
            $ownerPhoneForWhatsapp = '670' . ltrim($ownerPhoneForWhatsapp, '0');
        }

        $rulesHtml = trim((string) ($boardingHouse->rules ?? ''));
        // Clean any potential corruption from double-encoding
        if (str_starts_with($rulesHtml, '"') && str_ends_with($rulesHtml, '"')) {
            $rulesHtml = substr($rulesHtml, 1, -1);
            $rulesHtml = str_replace('\"', '"', $rulesHtml);
        }
    @endphp

    <div id="Content-Container"
        class="relative mx-auto flex min-h-screen w-full max-w-[640px] flex-col overflow-x-hidden bg-white">
        <div id="ForegroundFade"
            class="absolute top-0 z-10 h-[143px] w-full bg-[linear-gradient(180deg,#070707_0%,rgba(7,7,7,0)_100%)]">
        </div>
        <div id="TopNavAbsolute" class="absolute top-[60px] z-10 flex w-full items-center justify-between px-5">
            <a href="#" onclick="goBackOrHome('{{ route('home') }}')"
                class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white/10 backdrop-blur-sm">
                <img src="{{ asset('assets/images/icons/arrow-left-transparent.svg') }}" class="h-8 w-8" alt="icon">
            </a>
            <p class="font-semibold text-white">Details</p>
            <button
                class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white/10 backdrop-blur-sm">
                <img src="{{ asset('assets/images/icons/like.svg') }}" class="h-[26px] w-[26px]" alt="">
            </button>
        </div>
        <div id="Gallery" class="swiper-gallery -mb-[38px] w-full overflow-x-hidden">
            <div class="swiper-wrapper">
                @foreach ($boardingHouse->rooms ?? [] as $room)
                    @foreach ($room->roomImages ?? [] as $image)
                        <div class="swiper-slide !w-fit">
                            <div class="flex h-[430px] w-[320px] shrink-0 overflow-hidden">
                                <img src="{{ asset('storage/' . $image->image) }}" class="h-full w-full object-cover"
                                    alt="gallery thumbnails">
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
        <main id="Details" class="relative z-10 flex flex-col gap-4 rounded-t-[40px] bg-white py-5 pb-[10px]">
            <div id="Title" class="flex items-center justify-between gap-2 px-5">
                <h1 class="text-[22px] font-bold leading-[33px]">{{ $boardingHouse->name }}</h1>
                <div
                    class="flex shrink-0 flex-col items-center gap-2 rounded-[22px] border border-[#F1F2F6] bg-white p-[10px_20px] text-center">
                    <img src="{{ asset('assets/images/icons/star.svg') }}" class="h-6 w-6" alt="icon">
                    <p class="text-sm font-bold">{{ number_format($boardingHouse->testimonials_avg_rating ?? 0, 1) }}</p>
                </div>
            </div>
            <hr class="mx-5 border-[#F1F2F6]">
            <div id="Features" class="grid grid-cols-2 gap-x-[10px] gap-y-4 px-5">
                <div class="flex items-center gap-[6px]">
                    <img src="{{ asset('assets/images/icons/location.svg') }}" class="flex h-[26px] w-[26px] shrink-0"
                        alt="icon">
                    <p class="text-ngekos-grey">{{ $boardingHouse->city->name }}</p>
                </div>
                <div class="flex items-center gap-[6px]">
                    <img src="{{ asset('assets/images/icons/3dcube.svg') }}" class="flex h-[26px] w-[26px] shrink-0"
                        alt="icon">
                    <p class="text-ngekos-grey">{{ $boardingHouse->category->name }}</p>
                </div>
                <div class="flex items-center gap-[6px]">
                    <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="flex h-[26px] w-[26px] shrink-0"
                        alt="icon">
                    <p class="text-ngekos-grey">{{ $boardingHouse->rooms_sum_capacity ?? 0 }} People</p>
                </div>
                <div class="flex items-center gap-[6px]">
                    <img src="{{ asset('assets/images/icons/shield-tick.svg') }}" class="flex h-[26px] w-[26px] shrink-0"
                        alt="icon">
                    <p class="text-ngekos-grey">Privacy 100%</p>
                </div>
            </div>
            <hr class="mx-5 border-[#F1F2F6]">
            <div id="About" class="flex flex-col gap-[6px] px-5">
                <h2 class="font-bold">About</h2>
                <div class="prose prose-sm leading-7.5 max-w-none">
                    <div class="text-sm">{!! $boardingHouse->description ?? '<p>No description available.</p>' !!}</div>
                </div>
            </div>
            <div id="Tabs" class="flex gap-3 overflow-x-auto pb-2 ps-5"
                style="-webkit-overflow-scrolling: touch; scroll-behavior: smooth;">
                <button
                    class="tab-link hover:bg-ngekos-black !bg-ngekos-black shrink-0 whitespace-nowrap rounded-full border border-[#F1F2F6] p-[8px_14px] text-sm font-semibold !text-white transition-all duration-300 hover:text-white"
                    data-target-tab="#Bonus-Tab">Bonus Kos</button>
                <button
                    class="tab-link hover:bg-ngekos-black shrink-0 whitespace-nowrap rounded-full border border-[#F1F2F6] p-[8px_14px] text-sm font-semibold transition-all duration-300 hover:text-white"
                    data-target-tab="#Facility-Tab">Facility</button>
                <button
                    class="tab-link hover:bg-ngekos-black shrink-0 whitespace-nowrap rounded-full border border-[#F1F2F6] p-[8px_14px] text-sm font-semibold transition-all duration-300 hover:text-white"
                    data-target-tab="#Testimonials-Tab">Testimonials</button>
                <button
                    class="tab-link hover:bg-ngekos-black shrink-0 whitespace-nowrap rounded-full border border-[#F1F2F6] p-[8px_14px] text-sm font-semibold transition-all duration-300 hover:text-white"
                    data-target-tab="#Rules-Tab">Rules</button>
                <button
                    class="tab-link hover:bg-ngekos-black shrink-0 whitespace-nowrap rounded-full border border-[#F1F2F6] p-[8px_14px] text-sm font-semibold transition-all duration-300 hover:text-white"
                    data-target-tab="#Contact-Tab">Contact</button>
                <button
                    class="tab-link hover:bg-ngekos-black shrink-0 whitespace-nowrap rounded-full border border-[#F1F2F6] p-[8px_14px] text-sm font-semibold transition-all duration-300 hover:text-white"
                    data-target-tab="#Location-Tab">Location</button>
            </div>
            <div id="TabsContent" class="px-5">
                <div id="Bonus-Tab" class="tab-content flex flex-col gap-5">
                    <div class="flex flex-col gap-4">
                        @foreach ($boardingHouse->bonuses as $bonus)
                            <div
                                class="bonus-card flex items-center gap-3 rounded-[22px] border border-[#F1F2F6] p-[10px] transition-all duration-300 hover:border-[#91BF77]">
                                <div class="flex h-[90px] w-[120px] shrink-0 overflow-hidden rounded-[18px] bg-[#D9D9D9]">
                                    <img src="{{ asset('storage/' . $bonus->image) }}" class="h-full w-full object-cover"
                                        alt="thumbnails">
                                </div>
                                <div>
                                    <p class="font-semibold">{{ $bonus->name }}</p>
                                    <p class="text-ngekos-grey text-sm">{{ $bonus->description }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div id="Facility-Tab" class="tab-content hidden flex-col gap-5">
                    <div class="rounded-[22px] border border-[#F1F2F6] bg-white p-4">
                        <div class="grid grid-cols-2 gap-x-4 gap-y-5">
                            @foreach ($boardingHouse->facilities as $facility)
                                <div
                                    class="facility-card flex items-center gap-3 rounded-xl px-1 py-2 transition-all duration-300 hover:bg-[#F8FAFC]">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#DCE1E8] bg-white shadow-sm">
                                        <img src="{{ asset('storage/' . $facility->icon) }}" class="h-6 w-6 object-contain"
                                            alt="{{ $facility->name }} icon">
                                    </div>
                                    <p class="truncate text-[15px] font-semibold text-[#3E4146]">
                                        {{ $facility->name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div id="Testimonials-Tab" class="tab-content hidden flex-col gap-5">
                    <div class="flex flex-col gap-4">
                        @foreach ($boardingHouse->testimonials as $testimonial)
                            <div
                                class="testi-card flex flex-col gap-3 rounded-[22px] border border-[#F1F2F6] bg-white p-4 transition-all duration-300 hover:border-[#91BF77]">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-[70px] w-[70px] shrink-0 overflow-hidden rounded-full border-4 border-white ring-1 ring-[#F1F2F6]">
                                        <img src="{{ asset('storage/' . $testimonial->photo) }}"
                                            class="h-full w-full object-cover" alt="icon">
                                    </div>
                                    <div>
                                        <p class="font-semibold">{{ $testimonial->name }}</p>
                                        <p class="text-ngekos-grey mt-[2px] text-sm">{{ $testimonial->created_at }}</p>
                                    </div>
                                </div>
                                <p class="leading-[26px]">{{ $testimonial->content }}</p>
                                <div class="flex">
                                    @for ($i = 0; $i < $testimonial->rating; $i++)
                                        <img src="{{ asset('assets/images/icons/Star 1.svg') }}"
                                            class="flex h-[22px] w-[22px] shrink-0" alt="">
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div id="Rules-Tab" class="tab-content hidden flex-col gap-5">
                    <div class="rounded-[22px] border border-[#F1F2F6] bg-white p-4">
                        <div class="mb-3 flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F7FAFB]">
                                <img src="{{ asset('assets/images/icons/shield-tick.svg') }}" class="h-5 w-5"
                                    alt="rules icon">
                            </div>
                            <div>
                                <p class="text-lg font-semibold">Rules Boarding House</p>
                                <p class="text-sm text-slate-500">Aturan yang berlaku di kost ini</p>
                            </div>
                        </div>

                        @if ($rulesHtml !== '')
                            <div
                                class="prose prose-sm max-w-none text-sm leading-7 text-slate-700 [&_li]:my-1 [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:pl-5 [&_ul]:list-disc [&_ul]:pl-5">
                                {!! $rulesHtml !!}
                            </div>
                        @else
                            <p class="text-sm text-slate-500">Belum ada aturan yang ditambahkan.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div id="Contact-Tab" class="tab-content hidden flex-col gap-5">
                <div class="mx-5 rounded-[22px] border border-[#F1F2F6] bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F7FAFB]">
                            <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="h-5 w-5"
                                alt="owner icon">
                        </div>
                        <div>
                            <p class="text-lg font-semibold">Contact Owner</p>
                            <p class="text-sm text-slate-500">Hubungi owner untuk info lebih lanjut</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 rounded-2xl bg-[#F7FAFB] p-4">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white shadow-sm">
                                <img src="{{ asset('assets/images/icons/profile-2user.svg') }}" class="h-5 w-5"
                                    alt="owner">
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-slate-500">Nama Owner</p>
                                <p class="truncate text-base font-semibold text-slate-900">{{ $ownerName }}</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 rounded-2xl border border-[#E7EBF0] bg-white p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-slate-500">Nomor WhatsApp</p>
                                    <p class="break-all text-base font-semibold text-slate-900">
                                        {{ $ownerPhone ?: 'Nomor belum tersedia' }}
                                    </p>
                                </div>
                                @if ($ownerPhone)
                                    <button type="button" onclick="copyOwnerPhone('{{ $ownerPhone }}')"
                                        class="inline-flex shrink-0 items-center rounded-full border border-[#E7EBF0] bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#91BF77] hover:text-[#5A7C49]">
                                        Salin
                                    </button>
                                @endif
                            </div>

                            @if ($ownerPhoneForWhatsapp)
                                <div class="flex gap-2">
                                    <a href="https://wa.me/{{ $ownerPhoneForWhatsapp }}?text={{ urlencode('Halo ' . $ownerName . ', saya ingin tanya tentang ' . $boardingHouse->name) }}"
                                        target="_blank" rel="noopener"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-green-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-green-600">
                                        <img src="{{ asset('assets/images/icons/whatsapp.svg') }}" class="h-4 w-4"
                                            alt="wa icon">
                                        Chat via WhatsApp
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div id="Location-Tab" class="tab-content hidden flex-col gap-4">
                <div class="space-y-4">
                    <div class="mx-5 flex w-auto items-start gap-4 rounded-2xl border border-[#F1F2F6] bg-white p-4">
                        <div class="flex-shrink-0 rounded-lg bg-[#F7FAFB] p-3">
                            <img src="{{ asset('assets/images/icons/map-pin.svg') }}" class="h-6 w-6" alt="map icon">
                        </div>

                        <div class="flex flex-1 flex-col">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-lg font-semibold">Lokasi</p>
                                    <p class="text-ngekos-grey mt-1 text-sm">Lihat lokasi kost dan arahkan ke Google
                                        Maps</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($boardingHouse->latitude && $boardingHouse->longitude)
                                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $boardingHouse->latitude }},{{ $boardingHouse->longitude }}"
                                            onclick="event.preventDefault(); openDirections('{{ $boardingHouse->latitude }}', '{{ $boardingHouse->longitude }}');"
                                            target="_blank" rel="noopener"
                                            class="bg-ngekos-black inline-flex items-center gap-2 rounded-full px-3 py-2 text-xs font-semibold text-white">
                                            See Location
                                        </a>
                                        <button
                                            onclick="event.preventDefault(); copyDirectionsAndShowQR('{{ $boardingHouse->latitude }}','{{ $boardingHouse->longitude }}')"
                                            class="inline-flex items-center gap-2 rounded-full border border-[#E7EBF0] px-3 py-2 text-xs text-slate-700">
                                            Salin
                                        </button>
                                    @else
                                        <span class="text-ngekos-grey text-sm">Koordinat belum tersedia</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </main>
    <div id="BottomNav" class="relative flex h-[138px] w-full shrink-0">
        <div class="fixed bottom-5 z-10 w-full max-w-[640px] px-5">
            <div class="bg-ngekos-black flex items-center justify-between rounded-[40px] px-6 py-4">
                <p class="text-xl font-bold leading-[30px] text-white">
                    {{ formatUsd($boardingHouse->price) }}
                    <span class="text-sm font-normal">/month</span>
                </p>
                @auth
                    <a href="{{ route('kos.rooms', $boardingHouse->slug) }}"
                        class="bg-ngekos-orange flex shrink-0 rounded-full px-5 py-[14px] font-bold text-white">Book
                        Now</a>
                @else
                    <button type="button" onclick="openLoginModal()"
                        class="bg-ngekos-orange flex shrink-0 rounded-full px-5 py-[14px] font-bold text-white">Book
                        Now</button>
                @endauth
            </div>
        </div>
    </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/details.js') }}"></script>
    <script>
        function copyOwnerPhone(phone) {
            if (!phone) {
                return;
            }

            const text = String(phone).trim();
            const toast = document.getElementById('copyToast');

            if (navigator.clipboard?.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                const input = document.createElement('input');
                input.value = text;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                input.remove();
            }

            if (!toast) {
                return;
            }

            toast.textContent = 'Nomor owner disalin';
            toast.classList.remove('hidden');

            setTimeout(() => {
                toast.classList.add('hidden');
            }, 1600);
        }
    </script>
    <!-- QR modal (created here so it's available on page even if JS hasn't mounted) -->
    <div id="directionsQrModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-[320px] rounded-lg bg-white p-4">
            <div class="mb-2 flex items-center justify-between">
                <h4 class="font-semibold">Scan to open on phone</h4>
                <button onclick="(function(){document.getElementById('directionsQrModal').classList.add('hidden')})()"
                    class="text-sm">Close</button>
            </div>
            <img id="directionsQrImage" src="" alt="QR" class="mx-auto h-48 w-48 object-contain" />
            <div class="mt-3">
                <input id="directionsLinkInput" class="w-full rounded border px-2 py-2 text-sm" readonly />
            </div>
            <div class="mt-3 flex items-center justify-center gap-2">
                <button id="copyDirectionsLinkBtn" type="button" class="rounded-full border px-3 py-2 text-sm">Copy
                    link</button>
            </div>
        </div>
    </div>
    <!-- Transient toast for copy confirmation -->
    <div id="copyToast"
        class="fixed left-1/2 top-6 z-50 hidden -translate-x-1/2 rounded-lg bg-black/80 px-4 py-2 text-sm text-white">
        Link rute disalin ke clipboard
    </div>

    <!-- Login Modal -->
    <div id="loginModal" data-login-url="{{ route('filament.kost.auth.login') }}"
        data-register-url="{{ route('filament.kost.auth.register') }}"
        class="fixed inset-0 z-50 flex hidden items-center justify-center px-4 py-6">
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-[2px]" onclick="closeLoginModal()"></div>

        <div
            class="bg-white/96 relative z-10 mx-auto max-h-[min(100vh-3rem,90vh)] w-full max-w-[min(92vw,28rem)] overflow-hidden overflow-y-auto rounded-[28px] border border-white/70 p-5 shadow-[0px_28px_70px_-28px_rgba(15,23,42,0.5)] ring-1 ring-white/70 md:p-6">
            <div class="absolute -left-12 -top-14 h-28 w-28 rounded-full bg-orange-200/45 blur-3xl"></div>
            <div class="absolute -right-10 top-4 h-24 w-24 rounded-full bg-sky-200/45 blur-3xl"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div class="space-y-2">
                    <span
                        class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-white">Masuk</span>
                    <div>
                        <h3 class="text-xl font-bold leading-tight text-slate-900 md:text-2xl">Masuk ke Kost Iben</h3>
                        <p class="mt-2 max-w-xs text-sm leading-6 text-slate-500 md:max-w-sm">Pilih peran dulu, lalu lanjut
                            ke login atau daftar dengan role yang sama.</p>
                    </div>
                </div>

                <button type="button" aria-label="Close" onclick="closeLoginModal()"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                    ✕
                </button>
            </div>

            <p class="relative mt-6 text-sm font-medium uppercase tracking-[0.2em] text-slate-400">Pilih peran Anda</p>

            <div class="relative mt-4 flex flex-col gap-3" id="roleSelectionPanel">
                <button type="button" onclick="selectRole('user')"
                    class="group flex h-full flex-col rounded-3xl border border-slate-200 bg-white p-4 text-left shadow-[0px_10px_24px_-18px_rgba(15,23,42,0.45)] transition duration-300 hover:-translate-y-1 hover:border-sky-300 hover:shadow-[0px_16px_32px_-18px_rgba(14,165,233,0.45)]">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#DBEAFE_0%,#EFF6FF_100%)] text-sm font-bold text-sky-700 ring-1 ring-sky-100">
                            PK</div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-base font-semibold text-slate-900">Pencari Kos</h4>
                            <p class="mt-1 text-sm leading-6 text-slate-500">Login atau daftar sebagai pencari kos.</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm font-semibold text-sky-700">
                        <span>Pilih pencari kos</span>
                        <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                    </div>
                </button>

                <button type="button" onclick="selectRole('owner_kost')"
                    class="group flex h-full flex-col rounded-3xl border border-slate-200 bg-white p-4 text-left shadow-[0px_10px_24px_-18px_rgba(15,23,42,0.45)] transition duration-300 hover:-translate-y-1 hover:border-orange-300 hover:shadow-[0px_16px_32px_-18px_rgba(249,115,22,0.4)]">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#FFEDD5_0%,#FFF7ED_100%)] text-sm font-bold text-orange-700 ring-1 ring-orange-100">
                            PO</div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-base font-semibold text-slate-900">Pemilik Kos</h4>
                            <p class="mt-1 text-sm leading-6 text-slate-500">Login atau daftar sebagai pemilik kos.</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm font-semibold text-orange-600">
                        <span>Pilih pemilik kos</span>
                        <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                    </div>
                </button>
            </div>

            <div class="relative mt-4 hidden flex-col gap-3" id="authPanel">
                <button type="button" onclick="backToRoleSelection()"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </button>

                <a id="loginLink" href="#"
                    class="group flex h-full flex-col rounded-3xl border border-slate-200 bg-white p-4 text-left shadow-[0px_10px_24px_-18px_rgba(15,23,42,0.45)] transition duration-300 hover:-translate-y-1 hover:border-sky-300 hover:shadow-[0px_16px_32px_-18px_rgba(14,165,233,0.45)]">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#DBEAFE_0%,#EFF6FF_100%)] text-sm font-bold text-sky-700 ring-1 ring-sky-100">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13.8 12H3" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-base font-semibold text-slate-900">Masuk</h4>
                            <p class="mt-1 text-sm leading-6 text-slate-500">Sudah punya akun? Masuk dengan role ini.</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm font-semibold text-sky-700">
                        <span>Masuk</span>
                        <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                    </div>
                </a>

                <a id="registerLink" href="#"
                    class="group flex h-full flex-col rounded-3xl border border-slate-200 bg-white p-4 text-left shadow-[0px_10px_24px_-18px_rgba(15,23,42,0.45)] transition duration-300 hover:-translate-y-1 hover:border-orange-300 hover:shadow-[0px_16px_32px_-18px_rgba(249,115,22,0.4)]">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#FFEDD5_0%,#FFF7ED_100%)] text-sm font-bold text-orange-700 ring-1 ring-orange-100">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-base font-semibold text-slate-900">Daftar</h4>
                            <p class="mt-1 text-sm leading-6 text-slate-500">Belum punya akun? Buat akun baru dengan role
                                ini.</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm font-semibold text-orange-600">
                        <span>Daftar</span>
                        <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                    </div>
                </a>
            </div>

            <div class="relative mt-5 rounded-[22px] bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-500 ring-1 ring-slate-200/70"
                id="infoText">
                Role yang dipilih akan dipakai untuk login dan daftar.
            </div>
        </div>
    </div>

    <script>
        function openLoginModal() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeLoginModal() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function selectRole(role) {
            const rolePanel = document.getElementById('roleSelectionPanel');
            const authPanel = document.getElementById('authPanel');
            const loginLink = document.getElementById('loginLink');
            const registerLink = document.getElementById('registerLink');

            if (rolePanel) rolePanel.classList.add('hidden');
            if (authPanel) authPanel.classList.remove('hidden');

            if (loginLink) {
                loginLink.href = `{{ filament()->getLoginUrl() }}?role=${role}`;
            }

            if (registerLink) {
                registerLink.href = `{{ filament()->getRegistrationUrl() }}?role=${role}`;
            }
        }

        function backToRoleSelection() {
            const rolePanel = document.getElementById('roleSelectionPanel');
            const authPanel = document.getElementById('authPanel');

            if (rolePanel) rolePanel.classList.remove('hidden');
            if (authPanel) authPanel.classList.add('hidden');
        }
    </script>
@endsection
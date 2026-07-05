@extends('layouts.app')

@section('content')
    @php
        $currentUser = auth()->user();
        $currentUserAvatar = $currentUser?->getFilamentAvatarUrl();
        $currentUserInitials = collect(preg_split('/\s+/', trim($currentUser?->name ?? 'User')) ?: [])
            ->filter()
            ->take(2)
            ->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
        $currentUserInitials = $currentUserInitials !== '' ? $currentUserInitials : 'U';
        $currentUserEmail = $currentUser?->email;
        $isOwner = $currentUser?->role === 'owner_kost';
        $isAdmin = $currentUser?->role === 'admin';
        $isUser = $currentUser?->role === 'user';
        $userProfileUrl = \Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage::getUrl(panel: 'kost');
        $ownerProfileUrl = \Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage::getUrl(panel: 'kost');
        $adminProfileUrl = \Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage::getUrl(panel: 'admin');
        $ownerDashboardUrl = \Filament\Pages\Dashboard::getUrl(panel: 'kost');
        $adminDashboardUrl = \Filament\Pages\Dashboard::getUrl(panel: 'admin');
        $priceFloor = 0;
        $priceCeil = 250;
        $isPriceFilterEnabled = request()->boolean('price_enabled');
        $selectedPriceMax = (float) request()->query('price_max', $priceCeil);
        $selectedPriceMax = max($priceFloor, min($selectedPriceMax, $priceCeil));

        $isDistanceFilterEnabled = request()->boolean('distance_enabled');
        $selectedDistanceMax = (float) request()->query('distance_max', 30);
        $selectedDistanceMax = max(0, min($selectedDistanceMax, 30));

        $selectedRatingCategory = request()->query('rating_category', 'all');

        $selectedFacilityIds = collect((array) request()->query('facilities', []))
            ->filter(fn($id) => is_numeric($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $activeFilterCount = 0;
        if ($isPriceFilterEnabled && request()->filled('price_max')) {
            $activeFilterCount++;
        }
        if ($isDistanceFilterEnabled && request()->filled('distance_max')) {
            $activeFilterCount++;
        }
        if ($selectedRatingCategory !== 'all') {
            $activeFilterCount++;
        }
        if ($selectedFacilityIds->isNotEmpty()) {
            $activeFilterCount++;
        }

        $selectedRatingMin = request()->integer('rating_min', 0);
        $selectedRatingMin = max(0, min($selectedRatingMin, 5));

        $seeAllQuery = array_filter(
            [
                'price_enabled' => $isPriceFilterEnabled ? 1 : null,
                'price_max' => $isPriceFilterEnabled ? $selectedPriceMax : null,
                'distance_enabled' => $isDistanceFilterEnabled ? 1 : null,
                'distance_max' => $isDistanceFilterEnabled ? $selectedDistanceMax : null,
                'rating_category' => $selectedRatingCategory !== 'all' ? $selectedRatingCategory : null,
                'facilities' => $selectedFacilityIds->isNotEmpty() ? $selectedFacilityIds->all() : null,
            ],
            fn($value) => !is_null($value) && $value !== '' && (!is_array($value) || $value !== []),
        );
        $seeAllUrl = route('boarding-house.show-all', $seeAllQuery);
    @endphp

    <!-- Success Notification -->
    @php
        $successStatus = session()->pull('status');
        $successType = session()->pull('status_type', 'login');
        $successDisplayMessage = $successStatus;

        if ($successStatus) {
            if ($successType === 'logout') {
                $successDisplayMessage = 'Logged out successfully! See you again.';
            } elseif ($successType === 'login') {
                $successDisplayMessage = 'Login successful! Welcome back.';
            }
        }
    @endphp

    @if ($successStatus)
        <div id="successNotif" data-type="{{ $successType }}" data-message="{{ e($successDisplayMessage) }}"
            class="notif-root fixed left-4 right-4 top-4 mx-auto max-w-sm" aria-hidden="true">
            <div id="successNotifInner" class="notif-inner flex items-center gap-3 rounded-2xl px-4 py-3.5">
                <span id="notifIcon" class="notif-icon flex h-10 w-10 shrink-0 items-center justify-center rounded-full">
                    <!-- icon inserted via blade based on type -->
                    @if ($successType === 'logout')
                        <svg class="h-6 w-6 text-sky-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M15 12H3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M10 7L15 12L10 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M8 5H18C19.1046 5 20 5.89543 20 7V17C20 18.1046 19.1046 19 18 19H8"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    @else
                        <svg class="h-6 w-6 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17Z" fill="currentColor" />
                        </svg>
                    @endif
                </span>
                <div class="flex-1">
                    <p id="notifText" class="notif-text font-semibold">{{ e($successDisplayMessage) }}</p>
                </div>
                <button type="button" onclick="closeSuccessNotif()"
                    class="notif-close text-current transition hover:opacity-90">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <div id="Background"
        class="pointer-events-none absolute top-0 h-80 w-full overflow-hidden rounded-bl-[100px] bg-[linear-gradient(145deg,#FFF9F3_0%,#F3FAF7_48%,#EAF6FF_100%)]">
        <div class="absolute -left-16 -top-14 h-56 w-56 rounded-full bg-orange-200/35 blur-3xl"></div>
        <div class="-right-17.5 absolute -top-5 h-64 w-64 rounded-full bg-sky-200/35 blur-3xl"></div>
        <div class="absolute left-1/3 top-6 h-40 w-40 rounded-full bg-emerald-200/25 blur-3xl"></div>
        <div
            class="absolute inset-0 opacity-30 [background:radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.85),transparent_35%),radial-gradient(circle_at_80%_15%,rgba(255,255,255,0.65),transparent_30%),radial-gradient(circle_at_55%_60%,rgba(255,255,255,0.55),transparent_38%)]">
        </div>
        <div class="bg-linear-to-t absolute inset-x-0 bottom-0 h-24 from-white/70 via-white/25 to-transparent"></div>
    </div>
    <div id="TopNav" class="mt-15 relative flex items-start justify-between gap-4 px-5">
        <div class="flex flex-col gap-1">
            <p>Good day,</p>
            <h1 class="leading-7.5 text-xl font-bold text-slate-950">Explore Cozy Home</h1>
        </div>
        <div class="flex items-center gap-3">
            @auth
                <div class="relative">
                    <button type="button" id="userMenuTrigger" onclick="toggleUserMenu(event)"
                        class="group flex h-11 items-center gap-2.5 rounded-full border border-slate-200/70 bg-white/95 px-3 py-2 text-left shadow-[0px_12px_24px_-18px_rgba(15,23,42,0.4)] backdrop-blur-xl transition duration-200 hover:border-sky-300 hover:shadow-[0px_18px_32px_-22px_rgba(14,165,233,0.3)]"
                        aria-haspopup="menu" aria-expanded="false">
                        <span
                            class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-[linear-gradient(135deg,#FFEDD5_0%,#DBEAFE_100%)] ring-2 ring-white">
                            @if ($currentUserAvatar)
                                <img src="{{ $currentUserAvatar }}" class="h-full w-full object-cover"
                                    alt="{{ $currentUser->name }}">
                            @else
                                <span class="text-xs font-bold tracking-wide text-slate-800">{{ $currentUserInitials }}</span>
                            @endif
                        </span>

                        <span class="hidden max-w-36 flex-col leading-tight sm:flex">
                            <span class="truncate text-sm font-semibold text-slate-900">{{ $currentUser->name }}</span>
                            <span class="truncate text-[11px] text-slate-500">{{ $currentUserEmail }}</span>
                        </span>

                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-500 transition group-hover:bg-sky-50 group-hover:text-sky-700">
                            <svg id="userMenuChevron" class="h-4 w-4 transition duration-200" viewBox="0 0 20 20" fill="none"
                                aria-hidden="true">
                                <path d="M5 8l5 5 5-5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </span>
                    </button>

                    <div id="userMenuPanel"
                        class="bg-white/96 absolute right-0 top-full z-50 mt-2 hidden w-[min(92vw,12rem)] overflow-hidden rounded-3xl border border-white/70 p-1.5 shadow-[0px_20px_50px_-24px_rgba(15,23,42,0.4)] backdrop-blur-xl">

                        <div class="mt-1.5 space-y-0.5">
                            @if ($isUser)
                                <a href="{{ $userProfileUrl }}"
                                    class="group flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-950">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-700 ring-1 ring-sky-100 transition group-hover:bg-sky-100">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor"
                                                stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M4.5 20a7.5 7.5 0 0 1 15 0" stroke="currentColor" stroke-width="1.75"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="flex-1">Profile</span>
                                    <span
                                        class="text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-400">→</span>
                                </a>
                            @endif

                            @if ($isAdmin)
                                <a href="{{ $adminProfileUrl }}"
                                    class="group flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-950">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-700 ring-1 ring-sky-100 transition group-hover:bg-sky-100">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor"
                                                stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M4.5 20a7.5 7.5 0 0 1 15 0" stroke="currentColor" stroke-width="1.75"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="flex-1">Profile</span>
                                    <span
                                        class="text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-400">→</span>
                                </a>
                            @endif

                            @if ($isOwner)
                                <a href="{{ $ownerProfileUrl }}"
                                    class="group flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-orange-50 hover:text-slate-950">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-700 ring-1 ring-sky-100 transition group-hover:bg-sky-100">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor"
                                                stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M4.5 20a7.5 7.5 0 0 1 15 0" stroke="currentColor" stroke-width="1.75"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="flex-1">Profile</span>
                                    <span
                                        class="text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-400">→</span>
                                </a>
                            @endif

                            @if ($isOwner)
                                <a href="{{ $ownerDashboardUrl }}"
                                    class="group flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-orange-50 hover:text-slate-950">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 text-orange-700 ring-1 ring-orange-100 transition group-hover:bg-orange-100">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path
                                                d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9A2.5 2.5 0 0 1 17.5 19h-11A2.5 2.5 0 0 1 4 16.5v-9Z"
                                                stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path d="M8 15h8M8 11h8M8 7h4" stroke="currentColor" stroke-width="1.75"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="flex-1">Dashboard Owner</span>
                                    <span
                                        class="text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-400">→</span>
                                </a>
                            @endif

                            @if ($isAdmin)
                                <a href="{{ $adminDashboardUrl }}"
                                    class="group flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-red-50 hover:text-slate-950">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-700 ring-1 ring-red-100 transition group-hover:bg-red-100">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path
                                                d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9A2.5 2.5 0 0 1 17.5 19h-11A2.5 2.5 0 0 1 4 16.5v-9Z"
                                                stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path d="M8 15h8M8 11h8M8 7h4" stroke="currentColor" stroke-width="1.75"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="flex-1">Dashboard Admin</span>
                                    <span
                                        class="text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-400">→</span>
                                </a>
                            @endif

                            <form method="post" action="{{ route('logout') }}" class="pt-0.5">
                                @csrf
                                <button type="submit"
                                    class="group flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-medium text-rose-600 transition hover:bg-rose-50 hover:text-rose-700">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 ring-1 ring-rose-100 transition group-hover:bg-rose-100">
                                        <img src="{{ asset('assets/images/icons/logout.svg') }}"
                                            class="h-4 w-4 object-contain" alt="logout">
                                    </span>
                                    <span class="flex-1">Logout</span>
                                    <span
                                        class="text-rose-200 transition group-hover:translate-x-0.5 group-hover:text-rose-300">→</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <button type="button" onclick="openLoginModal()"
                    class="group inline-flex h-12 min-w-10 cursor-pointer items-center justify-center gap-2.5 rounded-full border border-sky-200/70 bg-white/95 px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-[0px_14px_28px_-20px_rgba(15,23,42,0.5)] backdrop-blur-sm transition duration-200 hover:border-sky-300">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-50 ring-1 ring-sky-100">
                        <img src="{{ asset('assets/images/icons/login.svg') }}" class="h-4.5 w-4.5 object-contain"
                            alt="login">
                    </span>
                    <span class="tracking-wide text-slate-800">Login</span>
                </button>
            @endauth
            <div class="relative">
                <button id="notifBtn" type="button" onclick="toggleNotifModal()"
                    class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/80 bg-white text-slate-900 shadow-[0px_12px_24px_-18px_rgba(15,23,42,0.5)] transition-transform duration-200">
                    <img src="{{ asset('assets/images/icons/notification-bell.svg') }}" class="h-7 w-7" alt="icon">
                    <span id="notifBadge"
                        class="absolute right-0 top-0 z-10 hidden h-5 w-5 -translate-y-1/3 translate-x-1/3 rounded-full bg-orange-500 text-center text-[10px] font-bold leading-5 text-white shadow-[0px_4px_8px_rgba(249,115,22,0.5)]"></span>
                </button>

                <div id="notificationDropdown"
                    class="w-92 rounded-4xl sm:w-100 pointer-events-none absolute right-0 top-[calc(100%+0.75rem)] z-40 max-w-[calc(100vw-1.5rem)] origin-top-right translate-y-2 scale-[0.98] overflow-hidden border border-white/55 bg-[linear-gradient(180deg,rgba(255,255,255,0.40),rgba(255,255,255,0.18))] p-4 opacity-0 shadow-[0px_32px_80px_-28px_rgba(15,23,42,0.5)] ring-1 ring-white/60 backdrop-blur-3xl transition-all duration-200 ease-out">
                    <div class="bg-linear-to-r absolute inset-x-0 top-0 h-px from-transparent via-white/90 to-transparent">
                    </div>
                    <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-orange-200/25 blur-3xl"></div>
                    <div class="absolute -left-8 -top-6 h-24 w-24 rounded-full bg-sky-200/20 blur-3xl"></div>

                    <div class="relative flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-900/90">Notifications
                            </h4>
                            <p class="text-xs leading-5 text-slate-600/90">Your latest booking updates appear here.</p>
                        </div>
                        <button type="button" id="clearAllBtn" onclick="clearAllNotifs()"
                            class="shrink-0 cursor-pointer rounded-full border border-white/55 bg-white/35 px-3.5 py-1.5 text-xs font-semibold text-slate-800/90 shadow-sm backdrop-blur-md transition hover:bg-white/60 hover:text-slate-950">Clear
                            All</button>
                    </div>

                    <div id="notificationList" class="relative mt-4 max-h-80 space-y-3 overflow-y-auto pr-1.5">
                        <!-- notifications rendered by JS -->
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div id="Location" class="relative mt-4 px-5 py-5">
        <div class="flex items-center justify-between rounded-full bg-white px-4 py-3 shadow-[0px_6px_18px_0px_#0000000D]">
            <div class="flex items-center gap-2">
                <img src="assets/images/icons/location.svg" class="h-5 w-5" alt="location">
                <p class="text-sm text-slate-600">Active Location :
                    <span id="activeLocationLabel" class="font-semibold text-slate-900">Dili (Default)</span>
                </p>
            </div>
            <button type="button" onclick="openLocationModal()"
                class="cursor-pointer text-sm font-medium text-slate-500">Ubah</button>
        </div>
    </div>

    <!-- Location Modal -->
    <div id="locationModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
        <div id="modalOverlay" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

        <div class="relative z-10 w-full max-w-sm rounded-2xl bg-white p-5 shadow-xl">
            <div class="flex items-start justify-between">
                <h3 class="text-lg font-bold">Select Location</h3>
                <button type="button" aria-label="Close" onclick="closeLocationModal()"
                    class="ml-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-600 shadow-sm">
                    ✕
                </button>
            </div>

            <div class="mt-4 space-y-3">
                <button type="button" data-option="dili" onclick="selectLocation('dili')"
                    class="location-option flex w-full cursor-pointer items-center gap-3 rounded-xl border border-orange-200 bg-orange-50/40 p-3 text-left">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100">
                        <img src="assets/images/icons/3dcube.svg" class="h-4 w-4" alt="icon">
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Pusat Kota Dili</span>
                        </div>
                        <p class="text-xs text-slate-500">(Default)</p>
                    </div>
                    <div class="radio-circle flex items-center justify-center">
                        <span
                            class="radio-outer flex h-5 w-5 items-center justify-center rounded-full border-2 border-orange-500 bg-white">
                            <span
                                class="radio-inner h-2.5 w-2.5 scale-100 rounded-full bg-orange-500 opacity-100 transition"></span>
                        </span>
                    </div>
                </button>

                <button type="button" data-option="gps" onclick="selectLocation('gps')"
                    class="location-option flex w-full cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-left">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50">
                        <img src="assets/images/icons/location.svg" class="h-4 w-4" alt="icon">
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Gunakan Lokasi Saya</span>
                        </div>
                        <p class="text-xs text-slate-500">(GPS)</p>
                    </div>
                    <div class="radio-circle flex items-center justify-center">
                        <span
                            class="radio-outer flex h-5 w-5 items-center justify-center rounded-full border-2 border-slate-300 bg-white">
                            <span
                                class="radio-inner h-2.5 w-2.5 scale-0 rounded-full bg-orange-500 opacity-0 transition"></span>
                        </span>
                    </div>
                </button>

                <p class="text-sm text-slate-500">Gunakan lokasi saat ini berdasarkan GPS Anda.</p>
            </div>

            <div class="mt-4">
                <button id="chooseLocationBtn" type="button" onclick="confirmLocation()"
                    class="w-full rounded-full bg-orange-500 py-2.5 font-semibold text-white">Pilih</button>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="loginModal" data-login-url="{{ route('filament.kost.auth.login') }}"
        data-register-url="{{ route('filament.kost.auth.register') }}"
        class="fixed inset-0 z-50 hidden items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-[2px]" onclick="closeLoginModal()"></div>

        <div
            class="bg-white/96 relative z-10 mx-auto max-h-[calc(100vh-2rem)] w-full max-w-[min(92vw,28rem)] overflow-hidden overflow-y-auto rounded-[28px] border border-white/70 p-5 shadow-[0px_28px_70px_-28px_rgba(15,23,42,0.5)] ring-1 ring-white/70 md:p-6">
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

    <section id="Categories" class="relative mt-2 py-4">
        <div class="category-scroll flex gap-5 px-5 pb-2">
            @foreach ($categories as $category)
                <a href="{{ route('category.show', $category->slug) }}" class="card shrink-0">
                    <div
                        class="w-28 rounded-3xl border border-transparent bg-white px-3 py-3 text-center shadow-[0px_8px_20px_0px_#0000000D] transition-all duration-200 hover:border-[#91BF77] hover:shadow-lg active:scale-95 active:border-[#91BF77] active:shadow-md">
                        <div class="mx-auto mb-2 h-14 w-14 overflow-hidden rounded-full">
                            <img src="{{ asset('storage/' . $category->image) }}" class="h-full w-full object-cover"
                                alt="{{ $category->name }}"
                                onerror="this.onerror=null;this.src='assets/images/thumbnails/buildings.png';">
                        </div>
                        <h3 class="line-clamp-2 text-sm font-semibold leading-5">{{ $category->name }}</h3>
                        <p class="text-ngekos-grey text-sm">{{ $category->available_boarding_houses_count }} Kos</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <section id="Recommendation" class="relative mt-6 flex flex-col gap-3 px-3 py-4">
        <div class="flex items-start justify-between gap-3 px-2">
            <h2 class="text-xl font-bold leading-tight">Recommended for You</h2>

            <div class="relative" id="filterWrapper">
                <div class="flex items-center gap-2">
                    <button type="button" id="filterTrigger"
                        class="inline-flex h-10 items-center gap-2 rounded-full border border-orange-200/80 bg-white px-4 text-sm font-semibold text-orange-600 shadow-[0px_10px_22px_-20px_rgba(249,115,22,0.55)] transition hover:border-orange-300 hover:text-orange-700"
                        aria-expanded="false" aria-controls="recommendationFilterPanel">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 6h16M7 12h10M10 18h4" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Filter
                        @if ($activeFilterCount > 0)
                            <span
                                class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-orange-500 px-1.5 text-[11px] font-semibold text-white">{{ $activeFilterCount }}</span>
                        @endif
                    </button>

                    <a href="{{ $seeAllUrl }}"
                        class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <span>See all</span>
                        <img src="assets/images/icons/arrow-right.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                    </a>
                </div>

                <div id="recommendationFilterPanel"
                    class="bg-white/96 absolute right-0 top-[calc(100%+0.7rem)] z-30 hidden w-[min(92vw,24rem)] rounded-3xl border border-orange-100 p-4 shadow-[0px_30px_60px_-30px_rgba(15,23,42,0.5)] backdrop-blur-xl">
                    <form method="get" action="{{ route('home') }}" id="recommendationFilterForm" class="space-y-5">
                        @if (request()->filled('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if (request()->filled('city'))
                            <input type="hidden" name="city" value="{{ request('city') }}">
                        @endif
                        @if (request()->filled('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif

                        <div class="space-y-2">
                            <label for="priceEnabledCheckbox"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 transition hover:border-orange-300 hover:text-orange-600">
                                <input id="priceEnabledCheckbox" name="price_enabled" type="checkbox" value="1"
                                    class="h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400"
                                    @checked($isPriceFilterEnabled)>
                                Aktifkan Filter Harga
                            </label>

                            <div id="priceSliderGroup" class="{{ $isPriceFilterEnabled ? '' : 'hidden' }} space-y-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Harga Maksimal
                                </p>
                                <div
                                    class="rounded-2xl border border-slate-200/80 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                     $ <span id="priceMaxValue">{{ number_format($selectedPriceMax, 2, '.', ',') }}</span>
                                </div>
                                <input id="priceMaxRange" name="price_max" type="range"
                                    class="filter-range-slider h-2 w-full cursor-pointer appearance-none rounded-lg bg-orange-100"
                                    min="{{ $priceFloor }}" max="{{ $priceCeil }}" step="1"
                                    value="{{ $selectedPriceMax }}" @disabled(!$isPriceFilterEnabled)>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="distanceEnabledCheckbox"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 transition hover:border-orange-300 hover:text-orange-600">
                                <input id="distanceEnabledCheckbox" name="distance_enabled" type="checkbox"
                                    value="1"
                                    class="h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400"
                                    @checked($isDistanceFilterEnabled)>
                                Aktifkan Filter Jarak
                            </label>

                            <div id="distanceSliderGroup"
                                class="{{ $isDistanceFilterEnabled ? '' : 'hidden' }} space-y-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Jarak Maksimum
                                </p>
                                <div
                                    class="rounded-2xl border border-slate-200/80 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                    Hingga <span
                                        id="distanceMaxValue">{{ number_format($selectedDistanceMax, 1, ',', '.') }}</span>
                                    km
                                </div>
                                <input id="distanceMaxRange" name="distance_max" type="range"
                                    class="filter-range-slider h-2 w-full cursor-pointer appearance-none rounded-lg bg-orange-100"
                                    min="0" max="30" step="0.5" value="{{ $selectedDistanceMax }}"
                                    @disabled(!$isDistanceFilterEnabled)>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Rating</p>
                            <input id="ratingCategoryInput" type="hidden" name="rating_category"
                                value="{{ $selectedRatingCategory }}">
                            <div class="flex flex-wrap gap-2" id="ratingButtons">
                                <button type="button" data-value="all"
                                    class="rating-star-btn rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:border-orange-300 hover:text-orange-600">Semua</button>
                                <button type="button" data-value="3"
                                    class="rating-star-btn inline-flex items-center gap-1 rounded-full border-2 border-slate-200 bg-white px-3 py-1.5 text-sm font-bold text-slate-600 transition hover:border-orange-300 hover:text-orange-600">
                                    <span>★</span>
                                    <span>3</span>
                                </button>
                                <button type="button" data-value="4"
                                    class="rating-star-btn inline-flex items-center gap-1 rounded-full border-2 border-slate-200 bg-white px-3 py-1.5 text-sm font-bold text-slate-600 transition hover:border-orange-300 hover:text-orange-600">
                                    <span>★</span>
                                    <span>4</span>
                                </button>
                                <button type="button" data-value="5"
                                    class="rating-star-btn inline-flex items-center gap-1 rounded-full border-2 border-slate-200 bg-white px-3 py-1.5 text-sm font-bold text-slate-600 transition hover:border-orange-300 hover:text-orange-600">
                                    <span>★</span>
                                    <span>5</span>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Fasilitas</p>
                            <div class="grid max-h-40 grid-cols-1 gap-2 overflow-y-auto pr-1 sm:grid-cols-2">
                                @foreach ($facilities as $facility)
                                    <label
                                        class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-2.5 py-2 transition hover:border-orange-300 hover:bg-orange-50/40">
                                        <input type="checkbox" name="facilities[]" value="{{ $facility->id }}"
                                            class="h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400"
                                            @checked($selectedFacilityIds->contains($facility->id))>
                                        <span
                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-orange-100 bg-orange-50/60">
                                            <img src="{{ asset('storage/' . $facility->icon) }}"
                                                class="h-4.5 w-4.5 object-contain" alt="{{ $facility->name }}">
                                        </span>
                                        <span
                                            class="truncate text-xs font-medium text-slate-700">{{ $facility->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ route('home') }}"
                                class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300">Reset</a>
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">Terapkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="resultsContainer" class="flex flex-col gap-3">
            @forelse ($recommendedBoardingHouse as $index => $boardingHouse)
                <a href="{{ route('kos.show', ['slug' => $boardingHouse->slug]) }}"
                    onclick="sessionStorage.setItem('backUrl', '{{ route('home') }}')" class="card"
                    data-lat="{{ $boardingHouse->latitude ?? '' }}" data-lng="{{ $boardingHouse->longitude ?? '' }}">
                    <div
                        class="relative rounded-3xl border border-[#E7EBF0] bg-white p-3 shadow-[0px_4px_14px_0px_#0000000D] transition-all duration-300 hover:border-[#91BF77]">
                        <div class="flex items-stretch gap-3">
                            <div
                                class="relative flex w-28 shrink-0 items-center justify-center self-stretch overflow-hidden rounded-[18px] bg-[#D9D9D9]">
                                @if ($index < 3)
                                    <div
                                        class="absolute left-2 top-2 z-10 flex h-8 w-8 items-center justify-center rounded-full border-2 border-amber-300 bg-white shadow-md">
                                        <span class="block h-4 w-4 shrink-0"
                                            style="background: linear-gradient(135deg, #8B5E1E 0%, #D4AF37 42%, #FFF4C2 100%); mask-image: url('{{ asset('assets/images/icons/crown.svg') }}'); -webkit-mask-image: url('{{ asset('assets/images/icons/crown.svg') }}'); mask-repeat: no-repeat; -webkit-mask-repeat: no-repeat; mask-position: center; -webkit-mask-position: center; mask-size: contain; -webkit-mask-size: contain;"></span>
                                        <span class="absolute text-[8px] font-bold leading-none text-amber-700"
                                            style="bottom: -4px; right: -4px; background: linear-gradient(135deg, #8B5E1E 0%, #D4AF37 42%, #FFF4C2 100%); color: white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; border: 1px solid #D4AF37;">{{ $index + 1 }}</span>
                                    </div>
                                @endif
                                <img src="{{ asset('storage/' . $boardingHouse->thumbnail ?? 'assets/images/thumbnails/kos-4.png') }}"
                                    class="h-full w-full object-cover" alt="{{ $boardingHouse->name }}">
                            </div>

                            <div class="flex min-w-0 flex-1 flex-col justify-between gap-1">
                                <div class="flex min-w-0 flex-col">
                                    <div class="flex min-w-0 flex-1 items-start gap-2">
                                        <!-- Mobile Version (word-based, max 2 words) -->
                                        <h3 class="line-clamp-1 block text-lg font-semibold leading-6 text-slate-900 md:hidden"
                                            title="{{ $boardingHouse->name }}">
                                            {{ \Illuminate\Support\Str::words($boardingHouse->name, 2, '...') }}
                                        </h3>
                                        <!-- Desktop Version (max 25 chars) -->
                                        <h3 class="line-clamp-1 hidden text-lg font-semibold leading-6 text-slate-900 md:block"
                                            title="{{ $boardingHouse->name }}">
                                            {{ \Illuminate\Support\Str::limit($boardingHouse->name, 25, '...') }}
                                        </h3>
                                    </div>
                                </div>

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
                                        $label =
                                            $gender === 'male' ? 'Male' : ($gender === 'female' ? 'Female' : 'Mixed');
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
                                </div>

                                <hr class="my-1.5 border-0 border-t border-slate-200/80">

                                <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                    <img src="assets/images/icons/location.svg" class="h-3.5 w-3.5" alt="lokasi">
                                    <span class="js-city-name">{{ $boardingHouse->city->name }}</span>
                                    <span class="js-distance">
                                        @if (is_numeric($distanceValue))
                                            · {{ number_format((float) $distanceValue, 1) }} km
                                        @endif
                                    </span>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                    <img src="assets/images/icons/profile-2user.svg" class="h-3.5 w-3.5" alt="kapasitas">
                                    <span>{{ $boardingHouse->available_rooms_sum_capacity ?? 0 }} People</span>
                                    @if (!is_null($boardingHouse->testimonials_avg_rating))
                                        <span>· ⭐
                                            {{ number_format((float) $boardingHouse->testimonials_avg_rating, 1) }}
                                            ({{ $boardingHouse->testimonials_count ?? 0 }} Review)
                                        </span>
                                    @endif
                                </div>

                                <hr class="my-1.5 border-0 border-t border-slate-200/80">

                                <div class="flex items-center justify-between">
                                    <p class="whitespace-nowrap text-xl font-bold text-orange-500">
                                        {{ formatUsd($boardingHouse->price) }}<span
                                            class="text-sm font-normal text-slate-500">/month</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="rounded-3xl border border-dashed border-orange-200 bg-orange-50/50 px-4 py-6 text-center">
                    <p class="text-sm font-medium text-orange-700">Belum ada kost yang cocok dengan filter saat ini.</p>
                    <p class="mt-1 text-xs text-orange-600">Coba longgarkan harga, jarak, rating, atau fasilitas.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section id="Cities" class="mt-7.5 flex flex-col gap-4 bg-[#F5F6F8] p-5">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold leading-tight">Browse Cities</h2>
            <a href="{{ route('city.show-all') }}">
                <div class="flex items-center gap-2">
                    <span>See all</span>
                    <img src="assets/images/icons/arrow-right.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                </div>
            </a>
        </div>
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

    <section id="Best" class="flex flex-col gap-4">
        <div class="flex items-center justify-between px-5 py-4">
            <h2 class="text-xl font-bold leading-tight">All Great Kos</h2>
            <a href="#">
                <div class="flex items-center gap-2">
                    <span>See all</span>
                    <img src="assets/images/icons/arrow-right.svg" class="flex h-6 w-6 shrink-0" alt="icon">
                </div>
            </a>
        </div>
        <div class="category-scroll flex gap-3 px-5 pb-2">
            @foreach ($bestByRating as $bestHouse)
                <div class="swiper-slide w-fit!">
                    <a href="{{ route('kos.show', $bestHouse->slug) }}"
                        onclick="sessionStorage.setItem('backUrl', '{{ route('home') }}')" class="card"
                        data-lat="{{ $bestHouse->latitude ?? '' }}" data-lng="{{ $bestHouse->longitude ?? '' }}">
                        <div
                            class="w-67.5 flex shrink-0 flex-col gap-2 rounded-[30px] border border-[#F1F2F6] p-3 transition-all duration-300 hover:border-[#91BF77]">
                            <div class="h-40 w-full shrink-0 overflow-hidden rounded-[25px] bg-[#D9D9D9]">
                                <img src="{{ asset('storage/' . $bestHouse->thumbnail ?? 'assets/images/thumbnails/kos-1.png') }}"
                                    class="h-full w-full object-cover" alt="{{ $bestHouse->name }}">
                            </div>
                            <div class="flex flex-col gap-2">
                                <h3 class="line-clamp-2 text-lg font-semibold leading-tight">
                                    {{ $bestHouse->name }}</h3>
                                <hr class="border-[#F1F2F6]">
                                <div class="flex items-center gap-1.5">
                                    <img src="assets/images/icons/location.svg" class="flex h-5 w-5 shrink-0"
                                        alt="icon">
                                    <p class="text-ngekos-grey text-sm leading-tight">
                                        <span class="js-city-name">{{ $bestHouse->city->name ?? 'Kota' }}</span>
                                        <span class="js-distance">
                                            @if (isset($bestHouse->distance))
                                                · {{ number_format((float) $bestHouse->distance, 1) }} km
                                            @endif
                                        </span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <img src="assets/images/icons/3dcube.svg" class="flex h-5 w-5 shrink-0"
                                        alt="icon">
                                    <p class="text-ngekos-grey text-sm leading-tight">
                                        {{ $bestHouse->category->name ?? 'Boarding House' }}</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <img src="assets/images/icons/profile-2user.svg" class="flex h-5 w-5 shrink-0"
                                        alt="icon">
                                    <p class="text-ngekos-grey text-sm leading-tight">
                                        {{ $bestHouse->available_rooms_sum_capacity ?? '0' }} People
                                    </p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <img src="assets/images/icons/star.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                                    <p class="text-ngekos-grey text-sm leading-tight">
                                        {{ number_format((float) $bestHouse->testimonials_avg_rating, 1) }}
                                        ({{ $bestHouse->testimonials_count ?? 0 }} Review)
                                    </p>
                                </div>
                                <hr class="border-[#F1F2F6]">
                                <p class="text-ngekos-orange text-lg font-semibold">
                                    {{ formatUsd($bestHouse->price) }}<span
                                        class="text-ngekos-grey text-sm font-normal">/month</span></p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
    @include('includes.navigation')
@endsection

@section('scripts')
    @php
        $homeNotificationConfig = [
            'initialNotifications' => $notifications ?? [],
            'sessionCheckout' => session('checkout_data') ?? null,
            'notificationRole' => auth()->check() ? ($isOwner ? 'owner' : ($isAdmin ? 'admin' : 'user')) : 'guest',
            'notificationFeedUrl' => route('notifications.feed'),
                'transactionIndexUrl' =>
                $isOwner || $isAdmin
                    ? \App\Filament\Resources\Transactions\TransactionsResource::getUrl(
                        panel: $isAdmin ? 'admin' : 'kost',
                    )
                    : null,
            'checkBookingBaseUrl' => route('check-booking'),
            'checkoutUrl' => url('/kost/booking/{slug}/checkout'),
            'baseKosUrl' => url('/kost'),
            'csrfToken' => csrf_token(),
        ];
    @endphp

    <script>
        window.homeNotificationConfig = @json($homeNotificationConfig);
    </script>
    <style>
        .notif-root {
            position: fixed;
            left: 1rem;
            right: 1rem;
            top: 1rem;
            z-index: 1000;
            display: block;
            max-width: 20rem;
            margin: 0 auto;
        }

        .notif-inner {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            box-shadow: 0 12px 32px -8px rgba(0, 0, 0, 0.08);
        }

        .notif-icon {
            flex: 0 0 40px;
            height: 40px;
            width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .notif-text {
            color: #083344;
        }

        /* Themes */
        .notif-login {
            background: linear-gradient(90deg, #ECFDF5, #D1FAE5);
            border: 1px solid #BBF7D0;
        }

        .notif-login .notif-icon {
            background: #D1FAE5;
            color: #047857;
        }

        .notif-logout {
            background: linear-gradient(90deg, #EFF6FF, #DBEAFE);
            border: 1px solid #BFDBFE;
        }

        .notif-logout .notif-icon {
            background: #DBEAFE;
            color: #0369A1;
        }

        /* Animations */
        .notif-root {
            transform: translateY(-10px);
            opacity: 0;
            transition: transform .35s cubic-bezier(.2, .9, .2, 1), opacity .28s ease;
        }

        .notif-root.notif-show {
            transform: translateY(0);
            opacity: 1;
        }

        .notif-root.notif-hide {
            transform: translateY(-10px);
            opacity: 0;
        }

        .filter-range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            height: 16px;
            width: 16px;
            border-radius: 9999px;
            background: #f97316;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.3);
        }

        .filter-range-slider::-moz-range-thumb {
            height: 16px;
            width: 16px;
            border-radius: 9999px;
            background: #f97316;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.3);
        }

        .rating-star-btn.is-active {
            border-color: #fdba74;
            background: #fff7ed;
            color: #c2410c;
        }

        .notification-action-approve {
            background: #16a34a;
            color: #ffffff;
            box-shadow: 0 10px 18px -12px rgba(22, 163, 74, 0.9);
        }

        .notification-action-approve:hover {
            background: #15803d;
        }

        .notification-action-reject {
            border: 1px solid #fda4af;
            background: rgba(255, 255, 255, 0.82);
            color: #be123c;
        }

        .notification-action-reject:hover {
            border-color: #fb7185;
            background: #fff1f2;
        }

        .notification-action-check {
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 10px 18px -12px rgba(15, 23, 42, 0.8);
        }

        .notification-action-check:hover {
            background: #1e293b;
        }

        .notification-action-reset {
            border: 1px solid rgba(249, 115, 22, 0.2);
            background: linear-gradient(135deg, rgba(255, 247, 237, 0.98), rgba(254, 243, 199, 0.9));
            color: #c2410c;
            box-shadow: 0 10px 18px -14px rgba(194, 65, 12, 0.35);
        }

        .notification-action-reset:hover {
            border-color: rgba(249, 115, 22, 0.35);
            background: linear-gradient(135deg, rgba(255, 237, 213, 0.98), rgba(254, 215, 170, 0.92));
            color: #9a3412;
        }

        .notification-action-view {
            border: 1px solid rgba(13, 148, 136, 0.22);
            background: linear-gradient(135deg, rgba(240, 253, 250, 0.98), rgba(219, 234, 254, 0.92));
            color: #0f766e;
            box-shadow: 0 10px 18px -14px rgba(15, 118, 110, 0.45);
        }

        .notification-action-view:hover {
            border-color: rgba(13, 148, 136, 0.35);
            background: linear-gradient(135deg, rgba(204, 251, 241, 0.98), rgba(191, 219, 254, 0.95));
            color: #115e59;
        }

        .notification-action-dismiss {
            border: 1px solid rgba(148, 163, 184, 0.22);
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.98), rgba(241, 245, 249, 0.94));
            color: #475569;
            box-shadow: 0 10px 18px -14px rgba(71, 85, 105, 0.35);
        }

        .notification-action-dismiss:hover {
            border-color: rgba(148, 163, 184, 0.38);
            background: linear-gradient(135deg, rgba(241, 245, 249, 0.99), rgba(226, 232, 240, 0.95));
            color: #0f172a;
        }
    </style>
@endsection

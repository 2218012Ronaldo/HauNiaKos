    <div id="BottomNav" class="h-34.5 relative flex w-full shrink-0">
        <nav class="max-w-160 fixed bottom-5 z-10 w-full px-5">
            <div class="bg-ngekos-black grid h-fit grid-cols-4 justify-between rounded-[40px] px-5 py-4">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 text-center">
                    <img src="assets/images/icons/{{ request()->routeIs('home') ? 'global-green.svg' : 'global.svg' }}"
                        class="flex h-8 w-8 shrink-0" alt="icon">
                    <span
                        class="{{ request()->routeIs('home') ? 'text-ngekos-green' : 'text-white' }} text-sm font-semibold">Discover</span>
                </a>
                <a href="{{ route('check-booking') }}" class="flex flex-col items-center gap-2 text-center">

                    <img src="assets/images/icons/{{ request()->routeIs('check-booking') ? 'note-favorite-green.svg' : 'note-favorite.svg' }}"
                        class="flex h-8 w-8 shrink-0" alt="icon">
                    <span
                        class="{{ request()->routeIs('check-booking') ? 'text-ngekos-green' : 'text-white' }} text-sm font-semibold">Orders</span>
                </a>
                <a href="{{ route('find-kos') }}" class="flex flex-col items-center gap-2 text-center">

                    <img src="assets/images/icons/{{ request()->routeIs('find-kos') ? 'search-status-green.svg' : 'search-status.svg' }}"
                        class="flex h-8 w-8 shrink-0" alt="icon">
                    <span
                        class="{{ request()->routeIs('find-kos') ? 'text-ngekos-green' : 'text-white' }} text-sm font-semibold">Find</span>
                </a>
                <a href="#" class="flex flex-col items-center gap-2 text-center">
                    <img src="assets/images/icons/24-support.svg" class="flex h-8 w-8 shrink-0" alt="icon">
                    <span class="text-sm font-semibold text-white">Help</span>
                </a>
            </div>
        </nav>
    </div>

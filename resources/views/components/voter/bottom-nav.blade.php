@props(['activeRoute'])

<!-- Mobile Bottom Navigation -->
<div
    class="fixed bottom-0 left-0 right-0 h-16 bg-blue-50 border-t border-blue-50 z-40 lg:hidden pb-safe"
    role="navigation"
    aria-label="Mobile navigation"
>
    <div class="flex h-full">
        <!-- Dashboard -->
        <a
            href="{{ route('voter.dashboard') }}"
            x-data="{ pressed: false }"
            @touchstart="pressed = true"
            @touchend="pressed = false"
            {{-- MODIFICATION: Changed border-gray-200 to border-blue-200 for a blue edge --}}
            class="flex-1 flex flex-col items-center justify-center py-2 px-1 text-xs font-medium transition-all duration-200 ease-out border-b-2 border-r border-blue-100 focus:outline-none active:outline-none ring-0 focus:ring-0 hover:bg-blue-50"
            style="-webkit-tap-highlight-color: transparent;"
            :class="{
                'text-blue-600 border-blue-600 bg-blue-100 shadow-sm': pressed,
                'text-blue-600 border-blue-600 bg-blue-50 shadow-sm': !pressed && $activeRoute === 'voter.dashboard',
                'text-gray-500 border-transparent': !pressed && $activeRoute !== 'voter.dashboard'
            }"
            role="menuitem"
            tabindex="0"
            aria-current="{{ $activeRoute === 'voter.dashboard' ? 'page' : 'false' }}"
            aria-label="Dashboard"
        >
            <svg class="w-6 h-6 mb-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
            </svg>
            Dashboard
        </a>

        <!-- Elections -->
        <a
            href="{{ route('voter.elections') }}"
            x-data="{ pressed: false }"
            @touchstart="pressed = true"
            @touchend="pressed = false"
            {{-- MODIFICATION: Changed border-gray-200 to border-blue-200 for a blue edge --}}
            class="flex-1 flex flex-col items-center justify-center py-2 px-1 text-xs font-medium transition-all duration-200 ease-out border-b-2 border-r border-blue-100 focus:outline-none active:outline-none ring-0 focus:ring-0 hover:bg-blue-50"
            style="-webkit-tap-highlight-color: transparent;"
            :class="{
                'text-blue-600 border-blue-600 bg-blue-100 shadow-sm': pressed,
                'text-blue-600 border-blue-600 bg-blue-50 shadow-sm': !pressed && $activeRoute === 'voter.elections',
                'text-gray-500 border-transparent': !pressed && $activeRoute !== 'voter.elections'
            }"
            role="menuitem"
            tabindex="0"
            aria-current="{{ $activeRoute === 'voter.elections' ? 'page' : 'false' }}"
            aria-label="Elections"
        >
            <svg class="w-6 h-6 mb-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Elections
        </a>

        <!-- History -->
        <a
            href="{{ route('voter.history') }}"
            x-data="{ pressed: false }"
            @touchstart="pressed = true"
            @touchend="pressed = false"
            class="flex-1 flex flex-col items-center justify-center py-2 px-1 text-xs font-medium transition-all duration-200 ease-out border-b-2 focus:outline-none active:outline-none ring-0 focus:ring-0 hover:bg-blue-50"
            style="-webkit-tap-highlight-color: transparent;"
            :class="{
                'text-blue-600 border-blue-600 bg-blue-100 shadow-sm': pressed,
                'text-blue-600 border-blue-600 bg-blue-50 shadow-sm': !pressed && $activeRoute === 'voter.history',
                'text-gray-500 border-transparent': !pressed && $activeRoute !== 'voter.history'
            }"
            role="menuitem"
            tabindex="0"
            aria-current="{{ $activeRoute === 'voter.history' ? 'page' : 'false' }}"
            aria-label="History"
        >
            <svg class="w-6 h-6 mb-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            History
        </a>
    </div>
</div>
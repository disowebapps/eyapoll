{{-- Slide-out Menu Component --}}
<div>
<div x-data="voterMenu()" class="relative">
    {{-- Bottom Navigation Bar (Mobile only) --}}
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-lg lg:hidden safe-area-bottom">
        <div class="flex items-center justify-around px-2 py-2">
            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex flex-col items-center justify-center px-3 py-2 min-h-[60px] min-w-[60px] rounded-xl transition-all duration-200 group relative touch-manipulation"
                   :class="{ 'bg-blue-50 text-blue-700': '{{ $item['active'] }}' === '1', 'text-gray-600 hover:text-gray-900 hover:bg-gray-50': '{{ $item['active'] }}' !== '1' }">

                    {{-- Icon --}}
                    <div class="relative mb-1">
                        <div class="w-6 h-6 flex items-center justify-center">
                            <svg class="w-5 h-5 transition-colors duration-200"
                                 :class="{ 'text-blue-600': '{{ $item['active'] }}' === '1', 'text-gray-600 group-hover:text-gray-900': '{{ $item['active'] }}' !== '1' }"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                            </svg>
                        </div>

                        {{-- Badge --}}
                        @if($item['badge'])
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-sm">
                                {{ is_numeric($item['badge']) ? $item['badge'] : '!' }}
                            </span>
                        @elseif($item['badge'] === 'verify')
                            <span class="absolute -top-1 -right-1 bg-yellow-500 text-white rounded-full w-3 h-3 shadow-sm"></span>
                        @endif
                    </div>

                    {{-- Label --}}
                    <span class="text-xs font-medium text-center leading-tight"
                          :class="{ 'text-blue-600': '{{ $item['active'] }}' === '1', 'text-gray-600 group-hover:text-gray-900': '{{ $item['active'] }}' !== '1' }">
                        {{ $item['label'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </nav>

    {{-- Overlay --}}
    <div x-show="false"
          x-cloak
          @click="closeMenu()"
          class="fixed inset-0 bg-black bg-opacity-50 z-30 transition-opacity duration-300 lg:hidden"
          x-transition:enter="transition-opacity duration-300"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition-opacity duration-300"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"></div>

    {{-- Slide-out Menu Panel --}}
    <div x-show="isOpen"
          x-cloak
          class="fixed top-0 left-0 h-full w-80 max-w-[85vw] bg-white shadow-2xl z-40 transform transition-transform duration-300 ease-out"
          :class="{ '-translate-x-full': !isOpen, 'translate-x-0': isOpen }"
          x-transition:enter="transition-transform duration-300 ease-out"
          x-transition:enter-start="-translate-x-full"
          x-transition:enter-end="translate-x-0"
          x-transition:leave="transition-transform duration-300 ease-out"
          x-transition:leave-start="translate-x-0"
          x-transition:leave-end="-translate-x-full">

        {{-- Menu Header --}}
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Echara Vote</h2>
                    <p class="text-sm text-gray-600">Navigation Menu</p>
                </div>
            </div>
            <button @click="closeMenu()"
                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors duration-200"
                    aria-label="Close menu">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- User Profile Section --}}
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center space-x-4">
                <img src="{{ Auth::user()->profile_image_url }}"
                     alt="Profile"
                     class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->full_name }}</p>
                    <p class="text-xs text-gray-600">Verified Voter</p>
                    <div class="flex items-center mt-1">
                        <div class="w-2 h-2 {{ $userStatus['kyc_verified'] ? 'bg-green-500' : 'bg-yellow-500' }} rounded-full mr-2"></div>
                        <span class="text-xs text-gray-600">
                            {{ $userStatus['kyc_verified'] ? 'Identity Verified' : 'Verification Pending' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation Items --}}
        <nav class="flex-1 overflow-y-auto py-4">
            <div class="px-4 space-y-2">
                {{-- Main Navigation --}}
                <div class="mb-6">
                    <h3 class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Main</h3>
                    @foreach($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           @click="closeMenu()"
                           class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group relative"
                           :class="{ 'bg-blue-50 text-blue-700 border-r-3 border-blue-600': '{{ $item['active'] }}' === '1', 'text-gray-700 hover:bg-gray-50 hover:text-gray-900': '{{ $item['active'] }}' !== '1' }">

                            {{-- Active indicator --}}
                            @if($item['active'])
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-600 rounded-r-full"></div>
                            @endif

                            {{-- Icon --}}
                            <div class="relative flex-shrink-0 mr-4">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center transition-all duration-200"
                                     :class="{ 'bg-blue-100': '{{ $item['active'] }}' === '1', 'bg-gray-100 group-hover:bg-gray-200': '{{ $item['active'] }}' !== '1' }">
                                    <svg class="w-4 h-4 transition-colors duration-200"
                                         :class="{ 'text-blue-600': '{{ $item['active'] }}' === '1', 'text-gray-600 group-hover:text-gray-900': '{{ $item['active'] }}' !== '1' }"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                    </svg>
                                </div>

                                {{-- Badge --}}
                                @if($item['badge'])
                                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-sm">
                                        {{ is_numeric($item['badge']) ? $item['badge'] : '!' }}
                                    </span>
                                @elseif($item['badge'] === 'verify')
                                    <span class="absolute -top-1 -right-1 bg-yellow-500 text-white rounded-full w-3 h-3 shadow-sm"></span>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <span class="font-medium">{{ $item['label'] }}</span>
                                @if($item['route'] === 'voter.elections' && $item['badge'])
                                    <p class="text-xs text-blue-600 mt-1">{{ $item['badge'] }} active election{{ $item['badge'] > 1 ? 's' : '' }}</p>
                                @endif
                            </div>

                            {{-- Active checkmark --}}
                            @if($item['active'])
                                <svg class="w-4 h-4 text-blue-600 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </a>
                    @endforeach
                </div>

                {{-- Additional Actions --}}
                <div class="mb-6">
                    <h3 class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</h3>
                    <div class="space-y-1">
                        {{-- Quick Actions --}}
                        <a href="{{ route('voter.kyc') }}"
                           @click="closeMenu()"
                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-xl transition-colors duration-200">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center mr-4">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span>KYC Verification</span>
                        </a>

                        <a href="{{ route('public.verify.receipt') }}"
                           @click="closeMenu()"
                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-xl transition-colors duration-200">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center mr-4">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span>Verify Receipt</span>
                        </a>

                        <a href="{{ route('public.help') }}"
                           @click="closeMenu()"
                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-xl transition-colors duration-200">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center mr-4">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span>Help & Support</span>
                        </a>
                    </div>
                </div>

                {{-- Status Indicators --}}
                <div class="mb-6">
                    <h3 class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</h3>
                    <div class="space-y-3">
                        {{-- Election Status --}}
                        @if($userStatus['has_upcoming_elections'])
                            <div class="flex items-center px-4 py-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3 animate-pulse"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Elections Available</p>
                                    <p class="text-xs text-gray-600">Ready to vote</p>
                                </div>
                            </div>
                        @endif

                        {{-- Voting History --}}
                        @if($userStatus['has_voting_history'])
                            <div class="flex items-center px-4 py-2">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Voting History</p>
                                    <p class="text-xs text-gray-600">Past elections recorded</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        {{-- Footer --}}
        <div class="border-t border-gray-200 p-6 bg-gray-50">
            <form method="POST" action="{{ route('voter.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center px-4 py-3 bg-red-50 hover:bg-red-100 text-red-700 rounded-xl transition-colors duration-200 font-medium">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Sign Out
                </button>
            </form>
            <p class="text-xs text-gray-500 text-center mt-3">Version 1.0.0</p>
        </div>
    </div>
</div>

<script>
function voterMenu() {
    return {
        isOpen: false,

        toggleMenu() {
            this.isOpen = !this.isOpen;
            this.updateBodyScroll();
        },

        closeMenu() {
            this.isOpen = false;
            this.updateBodyScroll();
        },

        updateBodyScroll() {
            if (this.isOpen) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        },

        // Close menu on escape key
        init() {
            // Set sidebar state based on screen size
            if (window.matchMedia('(min-width: 1024px)').matches) {
                this.isOpen = true; // Open by default on desktop
            } else {
                this.isOpen = false; // Explicitly closed on mobile
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.closeMenu();
                }
            });

            // Close menu when clicking on navigation links
            this.$watch('isOpen', (isOpen) => {
                if (!isOpen) {
                    this.updateBodyScroll();
                }
            });
        }
    }
}
</script>

<style>
/* Custom scrollbar for menu */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Smooth transitions */
[x-cloak] {
    display: none !important;
}

/* Focus management */
.menu-focus:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Safe area support */
@supports (env(safe-area-inset-top)) {
    .fixed.top-4 {
        top: max(1rem, env(safe-area-inset-top) + 0.5rem);
    }
}
/* Safe area support for bottom navigation */
@supports (env(safe-area-inset-bottom)) {
    .safe-area-bottom {
        padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
    }
}

/* Touch-friendly interactions */
.touch-manipulation {
    touch-action: manipulation;
}

/* Ensure minimum touch target size */
@media (max-width: 1023px) {
    .safe-area-bottom {
        padding-bottom: env(safe-area-inset-bottom, 0.5rem);
    }
}
</style>
</div>
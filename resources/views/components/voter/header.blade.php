<header class="h-16 bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-full">

            <!-- Branding/Logo Section -->
            <div class="flex items-center">
                <a href="{{ route('voter.dashboard') }}" class="flex items-center space-x-2">
                    <!-- Full logo for desktop -->
                    <span class="hidden md:block text-xl font-bold text-gray-900 hover:text-blue-600 transition-colors duration-200">
                        AyaPoll
                    </span>
                    <!-- Abbreviated logo for mobile -->
                    <span class="md:hidden text-xl font-bold text-gray-900 hover:text-blue-600 transition-colors duration-200">
                        AP
                    </span>
                </a>
            </div>

            <!-- Right Side: User Menu and Mobile Toggle -->
            <div class="flex items-center space-x-3">

                <!-- User Menu Dropdown -->
                <div
                    x-data="{ open: false }"
                    class="relative"
                    @keydown.escape.window="open = false"
                >
                    <button
                        @click="open = !open"
                        @keydown.enter.prevent="open = !open"
                        @keydown.space.prevent="open = !open"
                        :aria-expanded="open"
                        aria-haspopup="true"
                        aria-label="User menu"
                        class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
                    >
                        <!-- User Avatar -->
                        @if(auth()->user()->profile_photo_path)
                            <img
                                src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}"
                                alt="{{ auth()->user()->name }}"
                                class="w-8 h-8 rounded-full object-cover"
                            >
                        @else
                            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                                <span class="text-white text-sm font-medium">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        <span class="hidden sm:block text-sm font-medium text-gray-700">
                            {{ auth()->user()->name }}
                        </span>
                        <!-- Dropdown arrow -->
                        <svg
                            class="w-4 h-4 text-gray-500 transition-transform duration-200"
                            :class="{ 'rotate-180': open }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        @click.away="open = false"
                        class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
                        role="menu"
                        aria-orientation="vertical"
                    >
                        <div class="py-1">
                            <!-- Profile Link -->
                            <a
                                href="{{ route('voter.profile') }}"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:bg-gray-50 focus:text-gray-900 transition-colors duration-150"
                                role="menuitem"
                                tabindex="-1"
                            >
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Profile
                            </a>

                            <!-- KYC Verification Link -->
                            <a
                                href="{{ route('voter.kyc') }}"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:bg-gray-50 focus:text-gray-900 transition-colors duration-150"
                                role="menuitem"
                                tabindex="-1"
                            >
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                KYC Verification
                            </a>

                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button
                                    type="submit"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:bg-gray-50 focus:text-gray-900 transition-colors duration-150"
                                    role="menuitem"
                                    tabindex="-1"
                                >
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</header>
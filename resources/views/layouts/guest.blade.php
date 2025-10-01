<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Echara Youths - One Voice, One Future...')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center hover:opacity-80 transition-opacity">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-2xl font-bold text-gray-900">Echara Youths</h1>
                        <p class="text-xs text-gray-600">One Voice, One Future</p>
                    </div>
                </a>
                
                <div class="flex items-center space-x-4">
                    <nav class="hidden lg:flex space-x-8">
                        <a href="/" class="text-gray-700 hover:text-blue-600 font-medium transition-colors {{ request()->is('/') ? 'text-blue-600' : '' }}">Home</a>
                        <a href="{{ route('public.about') }}" class="text-gray-700 hover:text-blue-600 font-medium transition-colors {{ request()->routeIs('public.about') ? 'text-blue-600' : '' }}">About</a>
                        <div class="relative group">
                            <button class="text-gray-700 hover:text-blue-600 font-medium transition-colors flex items-center">
                                Community
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <a href="{{ route('public.members') }}" class="block px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors {{ request()->routeIs('public.members') ? 'text-blue-600 bg-blue-50' : '' }}">Members</a>
                                <a href="{{ route('public.executives') }}" class="block px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors {{ request()->routeIs('public.executives') ? 'text-blue-600 bg-blue-50' : '' }}">Executives</a>
                            </div>
                        </div>
                        <div class="relative group">
                            <button class="text-gray-700 hover:text-blue-600 font-medium transition-colors flex items-center">
                                Voting
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <a href="{{ route('public.voter-register') }}" class="block px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors {{ request()->routeIs('public.voter-register') ? 'text-blue-600 bg-blue-50' : '' }}">Voter Register</a>
                                <a href="{{ route('public.verify-vote') }}" class="block px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors {{ request()->routeIs('public.verify-vote') ? 'text-blue-600 bg-blue-50' : '' }}">Verify Vote</a>
                                <a href="{{ route('public.results') }}" class="block px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors {{ request()->routeIs('public.results') ? 'text-blue-600 bg-blue-50' : '' }}">Results</a>
                                <a href="{{ route('public.how-it-works') }}" class="block px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors {{ request()->routeIs('public.how-it-works') ? 'text-blue-600 bg-blue-50' : '' }}">How it Works</a>
                            </div>
                        </div>
                        <a href="{{ route('public.contact') }}" class="text-gray-700 hover:text-blue-600 font-medium transition-colors {{ request()->routeIs('public.contact') ? 'text-blue-600' : '' }}">Contact</a>
                        @guest
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('auth.login') }}" class="text-blue-600 border border-blue-600 hover:bg-blue-600 hover:text-white px-4 py-2 rounded-lg font-medium transition">
                                    Login
                                </a>
                                <a href="{{ route('auth.register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                                    Register
                                </a>
                            </div>
                        @else
                            <a href="{{ route('dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                                Dashboard
                            </a>
                        @endguest
                    </nav>
                    
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden lg:hidden bg-white border-b shadow-sm fixed w-full z-40" style="top: 62px;" x-data="{ communityOpen: false, votingOpen: false }">
        <div class="px-4 py-2 space-y-1">
            <a href="/" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-lg font-medium {{ request()->is('/') ? 'text-blue-600 bg-blue-50' : '' }}">Home</a>
            <a href="{{ route('public.about') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-lg font-medium {{ request()->routeIs('public.about') ? 'text-blue-600 bg-blue-50' : '' }}">About</a>
            <div class="px-3 py-2">
                <button @click="communityOpen = !communityOpen" class="flex items-center justify-between w-full text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                    <span class="flex items-center">
                        Community
                        <svg class="w-4 h-4 ml-1 transition-transform" :class="communityOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </span>
                </button>
                <div x-show="communityOpen" x-collapse>
                    <a href="{{ route('public.members') }}" class="block px-3 py-2 text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg text-sm {{ request()->routeIs('public.members') ? 'text-blue-600 bg-blue-50' : '' }}">Members</a>
                    <a href="{{ route('public.executives') }}" class="block px-3 py-2 text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg text-sm {{ request()->routeIs('public.executives') ? 'text-blue-600 bg-blue-50' : '' }}">Executives</a>
                </div>
            </div>
            <div class="px-3 py-2">
                <button @click="votingOpen = !votingOpen" class="flex items-center justify-between w-full text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                    <span class="flex items-center">
                        Voting
                        <svg class="w-4 h-4 ml-1 transition-transform" :class="votingOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </span>
                </button>
                <div x-show="votingOpen" x-collapse>
                    <a href="{{ route('public.voter-register') }}" class="block px-3 py-2 text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg text-sm {{ request()->routeIs('public.voter-register') ? 'text-blue-600 bg-blue-50' : '' }}">Voter Register</a>
                    <a href="{{ route('public.verify-vote') }}" class="block px-3 py-2 text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg text-sm {{ request()->routeIs('public.verify-vote') ? 'text-blue-600 bg-blue-50' : '' }}">Verify Vote</a>
                    <a href="{{ route('public.results') }}" class="block px-3 py-2 text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg text-sm {{ request()->routeIs('public.results') ? 'text-blue-600 bg-blue-50' : '' }}">Results</a>
                    <a href="{{ route('public.how-it-works') }}" class="block px-3 py-2 text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg text-sm {{ request()->routeIs('public.how-it-works') ? 'text-blue-600 bg-blue-50' : '' }}">How it Works</a>
                </div>
            </div>
            <a href="{{ route('public.contact') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-lg font-medium {{ request()->routeIs('public.contact') ? 'text-blue-600 bg-blue-50' : '' }}">Contact</a>
            @guest
                <div class="flex gap-2 px-3 py-2">
                    <a href="{{ route('auth.login') }}" class="flex-1 text-center px-3 py-2 text-blue-600 border border-blue-600 hover:bg-blue-50 rounded-lg font-medium text-sm">Login</a>
                    <a href="{{ route('auth.register') }}" class="flex-1 text-center px-3 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg font-medium text-sm">Register</a>
                </div>
            @else
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg font-medium">Dashboard</a>
            @endguest
        </div>
    </div>

    <!-- Main Content -->
    <main class="@yield('main-class')">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="col-span-2">
                    <a href="{{ route('home') }}" class="flex items-center mb-4 hover:opacity-80 transition-opacity">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-2xl font-bold">Echara Youths</h3>
                    </a>
                    <p class="text-gray-300 mb-4 max-w-md">
                        The most trusted platform for secure, transparent, and verifiable digital elections.
                    </p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Platform</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="{{ route('public.how-it-works') }}" class="hover:text-white transition">How it Works</a></li>
                        <li><a href="{{ route('public.security') }}" class="hover:text-white transition">Security</a></li>
                        <li><a href="{{ route('public.features') }}" class="hover:text-white transition">Features</a></li>
                        <li><a href="{{ route('public.election-integrity') }}" class="hover:text-white transition">Election Integrity</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="{{ route('public.help') }}" class="hover:text-white transition">Help Center</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-white transition">Contact</a></li>
                        <li><a href="{{ route('public.privacy-policy') }}" class="hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="{{ route('public.terms-of-service') }}" class="hover:text-white transition">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Echara Youths. All rights reserved. Securing democracy, one vote at a time.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>
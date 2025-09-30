<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('ayapoll.platform_name', 'Echara Youths') }} - @yield('title', 'Dashboard')</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Echara Vote">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-TileColor" content="#2563eb">
    <meta name="msapplication-config" content="/browserconfig.xml">

    <!-- PWA Links -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/icon-512.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @livewireStyles
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
@php
    $activeRoute = request()->route()->getName();
@endphp
<body class="bg-slate-50 pb-16 lg:pb-0" x-data="{ sidebarOpen: false }">

<!-- Sidebar Navigation -->
<div class="hidden lg:block">
    <x-sidebar type="voter" :user="auth()->user()" />
</div>

<x-breadcrumb />

<!-- Main Content -->
<div class="w-full lg:pl-64">
    <header class="sticky top-0 z-50 bg-white border-b">
        <div class="flex items-center justify-between h-16 px-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('home') }}" class="flex items-center hover:opacity-80 transition-opacity">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-lg font-bold text-gray-900">Echara Youths</h1>
                        <p class="text-xs text-gray-600">One Voice, One Future</p>
                    </div>
                </a>
            </div>

            <div class="flex items-center space-x-3">
                @yield('header-actions')

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50">
                        @php
                            $currentUser = auth('web')->user() ?? auth('admin')->user();
                            $userType = auth('admin')->check() ? 'Admin' : 'Voter';
                        @endphp
                        <img src="{{ $currentUser->profile_image_url }}"
                              alt="Profile"
                              class="w-8 h-8 rounded-full object-cover border">
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-medium text-gray-900">{{ $currentUser->full_name ?? ($currentUser->first_name . ' ' . $currentUser->last_name) }}</p>
                            <p class="text-xs text-gray-500">{{ $userType }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-1 z-50">
                        <div class="px-4 py-3 border-b">
                            <p class="text-sm font-medium text-gray-900">{{ $currentUser->first_name }} {{ $currentUser->last_name }}</p>
                            <p class="text-xs text-gray-500">{{ $currentUser->email }}</p>
                        </div>
                        <a href="{{ route('voter.profile') }}" @click="open = false" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile
                        </a>
                        <a href="{{ route('voter.kyc') }}" @click="open = false" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            KYC Verification
                        </a>
                        <div class="border-t mt-1">
                            <form method="POST" action="{{ route('voter.logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="pt-8 px-6 pb-6">
        @if (session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-700 font-medium">{!! session('error') !!}</p>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<x-voter.bottom-nav :activeRoute="$activeRoute" />

<!-- Vote Now FAB for ongoing elections -->
@php
    $ongoingElections = \App\Models\Election\Election::select('id', 'title', 'starts_at', 'ends_at', 'status')->get()->filter(function($election) {
        try {
            $timeService = app(\App\Services\Election\ElectionTimeService::class);
            return $timeService->getElectionStatus($election) === \App\Enums\Election\ElectionStatus::ONGOING;
        } catch (\Exception $e) {
            return false;
        }
    });
    $user = auth()->user();
    $isAccredited = $user && $user->status->value === 'active';
@endphp

@if($ongoingElections->count() > 0 && $isAccredited)
    <div class="fixed bottom-20 right-4 z-50 lg:bottom-6">
        <a href="{{ route('voter.elections') }}" 
           class="flex items-center bg-red-600 text-white px-6 py-3 rounded-full shadow-lg hover:bg-red-700 transform hover:scale-105 transition-all duration-200 animate-pulse">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-bold">Vote Now</span>
            @if($ongoingElections->count() > 1)
                <span class="ml-2 bg-red-800 text-xs px-2 py-1 rounded-full">{{ $ongoingElections->count() }}</span>
            @endif
        </a>
    </div>
@endif

@livewireScripts

<!-- PWA Service Worker Registration -->
<script>
    // Register service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('Service Worker registered successfully:', registration.scope);

                    // Handle updates
                    registration.addEventListener('updatefound', function() {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', function() {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // New version available
                                if (confirm('A new version of the app is available. Reload to update?')) {
                                    window.location.reload();
                                }
                            }
                        });
                    });
                })
                .catch(function(error) {
                    console.log('Service Worker registration failed:', error);
                });
        });
    }

    // Install prompt
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;

        // Show install button or banner
        const installBanner = document.createElement('div');
        installBanner.id = 'install-banner';
        installBanner.className = 'fixed bottom-4 left-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg z-50 md:left-auto md:right-4 md:w-96';
        installBanner.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h3 class="font-semibold">Install Echara Vote</h3>
                    <p class="text-sm text-blue-100">Add to your home screen for quick access</p>
                </div>
                <div class="flex space-x-2 ml-4">
                    <button onclick="installApp()" class="bg-white text-blue-600 px-4 py-2 rounded font-medium text-sm hover:bg-blue-50" aria-label="Install the PWA app">
                        Install
                    </button>
                    <button onclick="dismissInstall()" class="text-blue-200 hover:text-white text-sm" aria-label="Dismiss install prompt">
                        ✕
                    </button>
                </div>
            </div>
        `;

        // Only show if not already installed
        if (!window.matchMedia('(display-mode: standalone)').matches) {
            document.body.appendChild(installBanner);

            // Auto-hide after 10 seconds
            setTimeout(() => {
                const banner = document.getElementById('install-banner');
                if (banner) banner.remove();
            }, 10000);
        }
    });

    function installApp() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function(choiceResult) {
                console.log('User choice:', choiceResult.outcome);
                deferredPrompt = null;

                // Hide banner
                const banner = document.getElementById('install-banner');
                if (banner) banner.remove();
            });
        }
    }

    function dismissInstall() {
        const banner = document.getElementById('install-banner');
        if (banner) banner.remove();
    }

    // Handle app installed
    window.addEventListener('appinstalled', function() {
        console.log('App was installed');
        const banner = document.getElementById('install-banner');
        if (banner) banner.remove();
    });
</script>
</body>
</html>
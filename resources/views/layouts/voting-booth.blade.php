<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure Voting Booth - Echara Youth</title>
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans antialiased">
    <!-- Main Content -->
    <div class="w-full">
        <!-- Top Bar -->
        <div class="sticky top-0 z-40 bg-white/95 backdrop-blur-xl shadow-soft border-b border-gray-200/50">
            <div class="flex items-center justify-between h-20 px-6">
                <!-- Page Title -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold gradient-text">@yield('page-title', 'Secure Voting Booth')</h1>
                        <div class="w-5 h-5 bg-green-600 rounded-full flex items-center justify-center ml-2">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-4">
                    @yield('header-actions')
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="p-8">
            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>
</html>
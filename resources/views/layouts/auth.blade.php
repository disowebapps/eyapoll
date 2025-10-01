<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Echara Youths - One Voice, One Future</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <!-- Logo -->
        <div class="mb-0 sticky top-0 z-50">
            <a href="/" class="flex items-center">
                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-2xl font-bold text-gray-900">Echara Youths</h1>
                    <p class="text-sm text-gray-600">One Voice, One Future</p>
                </div>
            </a>
        </div>

        <!-- Main Content -->
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500">
            Powered by 
            <a href="/" class="text-blue-500 hover:underline">
                Echara Youths
            </a> 
            &copy; {{ date('Y') }}
            </p>

            <div class="mt-2 space-x-4">
                <a href="#" class="text-xs text-blue-600 hover:text-blue-800">Privacy Policy</a>
                <a href="#" class="text-xs text-blue-600 hover:text-blue-800">Terms of Service</a>
                <a href="#" class="text-xs text-blue-600 hover:text-blue-800">Help</a>
            </div>
        </div>
    </div>

    @livewireScripts

    <!-- Global JavaScript -->
    <script>
        // reCAPTCHA callback
        function recaptchaCallback(token) {
            if (window.livewire) {
                window.livewire.emit('recaptchaVerified', token);
            }
        }

        // Auto-resize textareas
        document.addEventListener('DOMContentLoaded', function() {
            const textareas = document.querySelectorAll('textarea[data-auto-resize]');
            textareas.forEach(textarea => {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = this.scrollHeight + 'px';
                });
            });
        });

        // Form validation helpers
        window.EcharaHelpers = {
            validateIdNumber: function(idNumber) {
                const cleaned = idNumber.replace(/[^0-9]/g, '');
                return cleaned.length === 11 && /^\d+$/.test(cleaned);
            },
            
            formatPhoneNumber: function(phoneNumber) {
                const cleaned = phoneNumber.replace(/[^0-9+]/g, '');
                // Add formatting logic here if needed
                return cleaned;
            },
            
            showToast: function(message, type = 'info') {
                // Simple toast notification
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                    type === 'success' ? 'bg-green-500 text-white' :
                    type === 'error' ? 'bg-red-500 text-white' :
                    type === 'warning' ? 'bg-yellow-500 text-white' :
                    'bg-blue-500 text-white'
                }`;
                toast.textContent = message;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 5000);
            }
        };
    </script>
</body>
</html>
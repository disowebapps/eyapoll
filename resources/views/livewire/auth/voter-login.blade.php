
<main class="h-auto bg-gradient-to-br from-blue-50 to-indigo-100 px-4 sm:px-6 lg:px-8
            flex items-start justify-center py-8" role="main" aria-labelledby="login-heading">  

    <div class="max-w-md w-full">
        {{-- Header --}}
        <header class="text-center mb-6">
            <h1 id="login-heading" class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h1>
            <p class="text-gray-600">Sign in to your account</p>
        </header>

        {{-- Status Messages --}}
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg" role="alert" aria-live="assertive">
                <h2 class="sr-only">Login Errors</h2>
                <p class="text-sm text-red-800">Please correct the errors below and try again.</p>
            </div>
        @endif

        {{-- Login Form --}}
        <section class="bg-white rounded-2xl shadow-xl p-8" aria-labelledby="login-form-heading">
            <h2 id="login-form-heading" class="sr-only">Login Form</h2>
            <form wire:submit.prevent="login" class="space-y-6" role="form" aria-labelledby="login-heading">
                @csrf
                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input id="email" wire:model="email" type="email" autocomplete="email" required
                           aria-describedby="email-error email-help"
                           aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-gray-900 placeholder-gray-500"
                           placeholder="Enter your email">
                    @error('email') 
                        <p id="email-error" class="mt-2 text-sm text-red-600" role="alert" aria-live="polite">{{ $message }}</p> 
                    @enderror
                    <p id="email-help" class="sr-only">Enter the email address associated with your account</p>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input id="password" wire:model="password" type="password" autocomplete="current-password" required
                           aria-describedby="password-error password-help"
                           aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-gray-900 placeholder-gray-500"
                           placeholder="Enter your password">
                    @error('password') 
                        <p id="password-error" class="mt-2 text-sm text-red-600" role="alert" aria-live="polite">{{ $message }}</p> 
                    @enderror
                    <p id="password-help" class="sr-only">Enter your account password</p>
                </div>

                {{-- Remember / Forgot --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" wire:model="remember" type="checkbox"
                               aria-describedby="remember-help"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">Remember me</label>
                        <span id="remember-help" class="sr-only">Keep me signed in on this device</span>
                    </div>
                    <a href="{{ route('password.request', ['type' => 'voter']) }}" 
                       class="text-sm text-blue-600 hover:text-blue-500 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 rounded"
                       aria-label="Reset your password">Forgot password?</a>
                </div>

                {{-- Submit --}}
                <button type="submit" wire:loading.attr="disabled" wire:target="login"
                        aria-describedby="login-help"
                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <span wire:loading.remove wire:target="login">Sign In</span>
                    <span wire:loading wire:target="login" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing in...
                    </span>
                </button>
                <p id="login-help" class="sr-only">Click to sign in to your account</p>
            </form>

            {{-- Divider --}}
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-center text-sm text-gray-600">
                    Don't have an account?
                    <a href="{{ route('voter.register') }}" 
                       class="font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded"
                       aria-label="Create a new account">Register</a>
                </p>
            </div>
        </section>

        {{-- Help Section --}}
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Need help?
                <button class="text-blue-600 hover:text-blue-500 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 rounded"
                        onclick="alert('Contact support at support@echara.org')"
                        aria-label="Get help with login">
                    Contact Support
                </button>
            </p>
        </div>
    </div>
</main>
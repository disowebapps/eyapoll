<div class="h-auto bg-gradient-to-br from-blue-50 to-indigo-100 px-4 sm:px-6 lg:px-8
            flex items-start justify-center py-8">  

    <div class="max-w-md w-full">
        {{-- Header --}}
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Observer Access</h2>
            <p class="text-gray-600">Sign in to your observer account</p>
        </div>

        {{-- Login Form --}}
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <form wire:submit.prevent="login" class="space-y-6">
                @csrf
                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input id="email" wire:model="email" type="email" autocomplete="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-gray-900 placeholder-gray-500"
                           placeholder="Enter your email">
                    @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input id="password" wire:model="password" type="password" autocomplete="current-password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-gray-900 placeholder-gray-500"
                           placeholder="Enter your password">
                    @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Remember / Forgot --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" wire:model="remember" type="checkbox"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">Remember me</label>
                    </div>
                    <a href="{{ route('password.request', ['type' => 'observer']) }}" class="text-sm text-blue-600 hover:text-blue-500 font-medium">Forgot password?</a>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Sign In
                </button>
            </form>

            {{-- Divider --}}
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-center text-sm text-gray-600">
                    Authorized personnel only
                </p>
            </div>
        </div>
    </div>
</div>
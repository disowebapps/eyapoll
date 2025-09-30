<x-auth-layout>
    <div class="space-y-6">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900">Reset Password</h2>
            <p class="text-gray-600 mt-2">Enter your email to receive reset link</p>
        </div>

        <form class="space-y-5" action="{{ route('password.email') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="{{ $userType }}">
            
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                <input id="email" name="email" type="email" required 
                       class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" 
                       placeholder="Enter your email" value="{{ old('email') }}">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200">
                Send Reset Link
            </button>

            @if ($errors->any())
                <div class="text-red-600 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if (session('status'))
                <div class="text-green-600 text-sm">
                    {{ session('status') }}
                </div>
            @endif
        </form>

        <div class="text-center pt-4 border-t border-gray-200">
            <a href="{{ route($userType . '.login') }}" class="font-semibold text-blue-600 hover:text-blue-500">Back to Login</a>
        </div>
    </div>
</x-auth-layout>
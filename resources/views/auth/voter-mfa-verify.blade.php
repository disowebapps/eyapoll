<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-center text-gray-800">Verify Your Identity</h2>
                <p class="text-center text-gray-600 mt-2">
                    We've sent a verification code to your email address. Please enter it below to continue.
                </p>
            </div>

            @livewire('auth.voter-mfa-verify', ['userId' => $userId])

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Didn't receive the code?
                    <button wire:click="resendCodes" class="text-blue-600 hover:text-blue-800 font-medium" aria-label="Resend verification code">
                        Resend Code
                    </button>
                </p>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('voter.login') }}" class="text-sm text-gray-500 hover:text-gray-700" aria-label="Back to login">
                    ← Back to Login
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
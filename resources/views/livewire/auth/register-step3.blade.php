<main class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 px-4 sm:px-6 lg:px-8 flex items-center justify-center py-8"
     role="main"
     aria-labelledby="step3-heading">

    <div class="max-w-md w-full">
        <!-- Progress Bar -->
        <nav class="mb-4" aria-label="Registration Progress">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white text-sm font-medium" aria-label="Step 1 completed">✓</div>
                    <span class="ml-2 text-xs sm:text-sm font-medium text-green-600">Basic Info</span>
                </div>
                <div class="flex-1 mx-2 sm:mx-4 h-1 bg-green-600 rounded" aria-hidden="true"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white text-sm font-medium" aria-label="Step 2 completed">✓</div>
                    <span class="ml-2 text-xs sm:text-sm font-medium text-green-600">Contact</span>
                </div>
                <div class="flex-1 mx-2 sm:mx-4 h-1 bg-green-600 rounded" aria-hidden="true"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium" aria-current="step">3</div>
                    <span class="ml-2 text-xs sm:text-sm font-medium text-blue-600">Verify</span>
                </div>
            </div>
        </nav>

        <!-- Header -->
        <header class="text-center mb-6">
            <h1 id="step3-heading" class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Verify Your Identity</h1>
            <p class="text-gray-600 text-sm sm:text-base">Step 3 of 3: Final Verification</p>
        </header>

        {{-- Status Messages --}}
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg" role="alert" aria-live="assertive">
                <h2 class="sr-only">Verification Errors</h2>
                <p class="text-sm text-red-800">Please correct the errors below and try again.</p>
            </div>
        @endif

        <!-- Verification Form -->
        <section class="bg-white rounded-2xl shadow-xl p-6 sm:p-8" aria-labelledby="form-heading">
            <h2 id="form-heading" class="sr-only">Registration Form - Step 3: Identity Verification</h2>
            <div class="mb-6 p-4 bg-blue-50 rounded-lg text-center" role="status" aria-live="polite">
                <p class="text-sm text-blue-700">
                    We've sent verification codes to your email and phone.
                </p>
            </div>

            <form wire:submit.prevent="nextStep" role="form" aria-labelledby="step3-heading" class="space-y-6">
                <!-- Email Verification -->
                <div>
                    <label for="email_verification_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Verification Code <span class="text-red-500" aria-label="required">*</span>
                    </label>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0">
                        <input type="text" id="email_verification_code" wire:model="email_verification_code"
                               maxlength="6" placeholder="Enter 6-digit code"
                               aria-describedby="email-code-error email-code-help"
                               aria-invalid="{{ $errors->has('email_verification_code') ? 'true' : 'false' }}"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-lg font-mono">
                        @if(config('app.env') === 'development' || config('app.env') === 'local')
                            @php
                                $devCodes = session('dev_codes');
                                $emailOtp = $devCodes['email_otp'] ?? null;
                            @endphp
                            @if($emailOtp)
                                <div class="bg-yellow-100 border border-yellow-300 px-3 py-2 rounded font-mono text-sm text-center sm:text-left" role="status" aria-label="Development email code">
                                    Email OTP: {{ $emailOtp }}
                                </div>
                            @endif
                        @endif
                    </div>
                    @error('email_verification_code')
                        <p id="email-code-error" class="mt-1 text-sm text-red-600" role="alert" aria-live="polite">{{ $message }}</p>
                    @enderror
                    <p id="email-code-help" class="sr-only">Enter the 6-digit verification code sent to your email</p>
                </div>

                <!-- Phone Verification -->
                <div>
                    <label for="phone_verification_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Verification Code <span class="text-red-500" aria-label="required">*</span>
                    </label>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0">
                        <input type="text" id="phone_verification_code" wire:model="phone_verification_code"
                               maxlength="6" placeholder="Enter 6-digit code"
                               aria-describedby="phone-code-error phone-code-help"
                               aria-invalid="{{ $errors->has('phone_verification_code') ? 'true' : 'false' }}"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-lg font-mono">
                        @if(config('app.env') === 'development' || config('app.env') === 'local')
                            @php
                                $devCodes = session('dev_codes');
                                $phoneOtp = $devCodes['phone_otp'] ?? null;
                            @endphp
                            @if($phoneOtp)
                                <div class="bg-yellow-100 border border-yellow-300 px-3 py-2 rounded font-mono text-sm text-center sm:text-left" role="status" aria-label="Development phone code">
                                    Phone OTP: {{ $phoneOtp }}
                                </div>
                            @endif
                        @endif
                    </div>
                    @error('phone_verification_code')
                        <p id="phone-code-error" class="mt-1 text-sm text-red-600" role="alert" aria-live="polite">{{ $message }}</p>
                    @enderror
                    <p id="phone-code-help" class="sr-only">Enter the 6-digit verification code sent to your phone</p>
                </div>



                <!-- Navigation Buttons -->
                <nav class="flex flex-col-reverse sm:flex-row space-y-2 space-y-reverse sm:space-y-0 sm:space-x-4" aria-label="Registration navigation">
                    <button type="button" wire:click="previousStep"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-gray-500 min-h-[44px]"
                            aria-label="Go back to previous step">
                        ← Back
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="nextStep"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold py-3 px-4 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[44px]"
                            aria-describedby="confirm-help">
                        <span wire:loading.remove wire:target="nextStep">
                            Next Step →
                        </span>
                        <span wire:loading wire:target="nextStep" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verifying...
                        </span>
                    </button>
                    <p id="confirm-help" class="sr-only">Complete your registration and create your account</p>
                </nav>
            </form>
        </section>

        <!-- Help Section -->
        <aside class="mt-6 text-center" aria-label="Help and support">
            <p class="text-sm text-gray-600">
                Need help?
                <a href="{{ route('public.help') }}" class="text-blue-600 hover:text-blue-500 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 rounded"
                   aria-label="Get help with verification">
                    Contact Support
                </a>
            </p>
        </aside>
    </div>
</main>
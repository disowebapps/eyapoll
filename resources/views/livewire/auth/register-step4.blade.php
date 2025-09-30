<main class="h-auto bg-gradient-to-br from-blue-50 to-indigo-100 px-4 sm:px-6 lg:px-8
            flex items-start justify-center py-8"
     role="main"
     aria-labelledby="register-heading">

    <div class="max-w-lg w-full">

        <!-- Header -->
        <header class="text-center mb-6">
            <h1 id="register-heading" class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Create Password</h1>
            <p class="text-gray-600 text-sm sm:text-base">Join Echara Youths - Step 4 of 4</p>
        </header>

        {{-- Status Messages --}}
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg" role="alert" aria-live="assertive">
                <h2 class="sr-only">Registration Errors</h2>
                <p class="text-sm text-red-800">Please correct the errors below and try again.</p>
            </div>
        @endif

        <!-- Registration Form -->
        <section class="bg-white rounded-2xl shadow-xl p-6 sm:p-8" aria-labelledby="form-heading">
            <h2 id="form-heading" class="sr-only">Registration Form - Step 4: Create Password</h2>
            <form wire:submit.prevent="completeRegistration" role="form" class="space-y-6">

                <!-- Password Fields -->
                <fieldset class="space-y-4">
                    <legend class="text-base font-medium text-gray-900 mb-4">Create Your Password</legend>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input type="password" id="password" wire:model.live="password" required
                               aria-describedby="password-error password-help"
                               aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="Enter your password">
                        @if($errors->has('password'))
                            <p id="password-error" class="mt-1 text-sm text-red-600" role="alert" aria-live="polite">{{ $errors->first('password') }}</p>
                        @endif
                        <p id="password-help" class="mt-1 text-xs text-gray-500">Password must be at least 8 characters long</p>
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Password <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input type="password" id="password_confirmation" wire:model.live="password_confirmation" required
                               aria-describedby="password_confirmation-error"
                               aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="Confirm your password">
                        @if($errors->has('password_confirmation'))
                            <p id="password_confirmation-error" class="mt-1 text-sm text-red-600" role="alert" aria-live="polite">{{ $errors->first('password_confirmation') }}</p>
                        @endif
                    </div>
                </fieldset>

                <!-- Terms Agreement -->
                <fieldset class="space-y-4">
                    <legend class="sr-only">Terms and Conditions</legend>
                    <div class="flex items-start space-x-3">
                        <input type="checkbox" id="agree_terms" wire:model.live="agree_terms" required
                               aria-describedby="agree_terms-error"
                               class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="agree_terms" class="text-sm text-gray-700">
                            I agree to the 
                            <a href="{{ route('public.terms-of-service') }}" target="_blank" 
                               class="text-blue-600 hover:text-blue-500 underline focus:outline-none focus:ring-2 focus:ring-blue-500 rounded">
                                Terms of Service
                            </a> 
                            and 
                            <a href="{{ route('public.privacy-policy') }}" target="_blank" 
                               class="text-blue-600 hover:text-blue-500 underline focus:outline-none focus:ring-2 focus:ring-blue-500 rounded">
                                Privacy Policy
                            </a>
                            <span class="text-red-500" aria-label="required">*</span>
                        </label>
                    </div>
                    @if($errors->has('agree_terms'))
                        <p id="agree_terms-error" class="text-sm text-red-600" role="alert" aria-live="polite">{{ $errors->first('agree_terms') }}</p>
                    @endif
                </fieldset>

                <!-- Navigation Buttons -->
                <nav class="flex flex-col-reverse sm:flex-row space-y-2 space-y-reverse sm:space-y-0 sm:space-x-4" aria-label="Registration navigation">
                    <button type="button" wire:click="previousStep"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition text-center focus:outline-none focus:ring-2 focus:ring-gray-500 min-h-[44px]"
                            aria-label="Go back to step 3">
                        ← Back
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="completeRegistration"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold py-3 px-4 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[44px]">
                        <span wire:loading.remove wire:target="completeRegistration">
                            Complete Registration
                        </span>
                        <span wire:loading wire:target="completeRegistration" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating Account...
                        </span>
                    </button>
                </nav>
            </form>
        </section>

        {{-- Help Section --}}
        <aside class="mt-6 text-center" aria-label="Help and support">
            <p class="text-sm text-gray-600">
                Need help?
                <a href="{{ route('public.help') }}" class="text-blue-600 hover:text-blue-500 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 rounded"
                   aria-label="Get help with registration">
                    Contact Support
                </a>
            </p>
        </aside>
    </div>
</main>
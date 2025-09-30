<main class="h-auto bg-gradient-to-br from-blue-50 to-indigo-100 px-4 sm:px-6 lg:px-8
            flex items-start justify-center py-8"
     role="main"
     aria-labelledby="step2-heading">

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
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium" aria-current="step">2</div>
                    <span class="ml-2 text-xs sm:text-sm font-medium text-blue-600">Contact</span>
                </div>
                <div class="flex-1 mx-2 sm:mx-4 h-1 bg-gray-200 rounded" aria-hidden="true"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-gray-500 text-sm font-medium">3</div>
                    <span class="ml-2 text-xs sm:text-sm font-medium text-gray-500">Verify</span>
                </div>
            </div>
        </nav>

        <!-- Header -->
        <header class="text-center mb-6">
            <h1 id="step2-heading" class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Contact Information</h1>
            <p class="text-gray-600 text-sm sm:text-base">Step 2 of 3: Email & Phone</p>
        </header>

        {{-- Status Messages --}}
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg" role="alert" aria-live="assertive">
                <h2 class="sr-only">Contact Information Errors</h2>
                <p class="text-sm text-red-800">Please correct the errors below and try again.</p>
            </div>
        @endif

        <!-- Contact Form -->
        <section class="bg-white rounded-2xl shadow-xl p-6 sm:p-8" aria-labelledby="form-heading">
            <h2 id="form-heading" class="sr-only">Registration Form - Step 2: Contact Information</h2>
            <form wire:submit.prevent="nextStep" role="form" aria-labelledby="step2-heading" class="space-y-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address <span class="text-red-500" aria-label="required">*</span>
                    </label>
                    <input type="email" id="email" wire:model.blur="email" required
                           aria-describedby="email-error email-help"
                           aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                           autocomplete="email"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                           placeholder="Enter your email address">
                    @error('email')
                        <p id="email-error" class="mt-1 text-sm text-red-600" role="alert" aria-live="polite">{{ $message }}</p>
                    @enderror
                    <p id="email-help" class="mt-1 text-xs text-gray-500"></p>
                </div>

                <!-- Phone Number -->
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number <span class="text-red-500" aria-label="required">*</span>
                    </label>
                    <input type="tel" id="phone_number" wire:model="phone_number" required
                           aria-describedby="phone-error phone-help"
                           aria-invalid="{{ $errors->has('phone_number') ? 'true' : 'false' }}"
                           autocomplete="tel"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                           placeholder="+234 XXX XXX XXXX">
                    @error('phone_number')
                        <p id="phone-error" class="mt-1 text-sm text-red-600" role="alert" aria-live="polite">{{ $message }}</p>
                    @enderror
                    <p id="phone-help" class="mt-1 text-xs text-gray-500"></p>
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
                            aria-describedby="continue-help">
                        <span wire:loading.remove wire:target="nextStep">Continue →</span>
                        <span wire:loading wire:target="nextStep" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                    <p id="continue-help" class="sr-only">Continue to step 3 of registration for verification</p>
                </nav>
            </form>
        </section>

        {{-- Help Section --}}
        <aside class="mt-6 text-center" aria-label="Help and support">
            <p class="text-sm text-gray-600">
                Need help?
                <a href="{{ route('public.help') }}" class="text-blue-600 hover:text-blue-500 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 rounded"
                   aria-label="Get help with registration step 2">
                    Contact Support
                </a>
            </p>
        </aside>
    </div>
</main>
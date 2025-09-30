<main class="h-auto bg-gradient-to-br from-blue-50 to-indigo-100 px-4 sm:px-6 lg:px-8
            flex items-start justify-center py-8"
     role="main"
     aria-labelledby="register-heading">

    <div class="max-w-lg w-full">

        <!-- Header -->
        <header class="text-center mb-6">
            <h1 id="register-heading" class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Create Account</h1>
            <p class="text-gray-600 text-sm sm:text-base">Join Echara Youths - Step 1 of 3</p>
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
            <h2 id="form-heading" class="sr-only">Registration Form - Step 1: Personal Information</h2>
            <form wire:submit.prevent="completeRegistration" role="form" aria-labelledby="register-heading" class="space-y-6">

                <!-- Name Fields -->
                <fieldset class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <legend class="text-base font-medium text-gray-900 mb-4">Personal Information</legend>
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                            First Name <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input type="text" id="first_name" wire:model.live="first_name" required
                               aria-describedby="first_name-error first_name-help"
                               aria-invalid="{{ $errors->has('first_name') ? 'true' : 'false' }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="Enter your first name">
                        @if($errors->has('first_name'))
                            <p id="first_name-error" class="mt-1 text-sm text-red-600" role="alert" aria-live="polite">{{ $errors->first('first_name') }}</p>
                        @endif
                        <p id="first_name-help" class="sr-only">Enter your legal first name as it appears on your ID</p>
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Last Name <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input type="text" id="last_name" wire:model.live="last_name" required
                               aria-describedby="last_name-error last_name-help"
                               aria-invalid="{{ $errors->has('last_name') ? 'true' : 'false' }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="Enter your last name">
                        @if($errors->has('last_name'))
                            <p id="last_name-error" class="mt-1 text-sm text-red-600" role="alert" aria-live="polite">{{ $errors->first('last_name') }}</p>
                        @endif
                        <p id="last_name-help" class="sr-only">Enter your legal last name as it appears on your ID</p>
                    </div>
                </fieldset>

                <!-- Role Selection (Voter Only) -->
                <input type="hidden" wire:model="role" value="voter" aria-hidden="true">

                <!-- Navigation Buttons -->
                <nav class="flex flex-col-reverse sm:flex-row space-y-2 space-y-reverse sm:space-y-0 sm:space-x-4" aria-label="Registration navigation">
                    <a href="{{ route('voter.login') }}"
                       class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition text-center focus:outline-none focus:ring-2 focus:ring-gray-500 min-h-[44px]"
                       aria-label="Go back to login page">
                        ← Back to Login
                    </a>
                    <button type="submit" wire:loading.attr="disabled" wire:target="completeRegistration"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold py-3 px-4 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[44px]"
                            aria-describedby="next-help">
                        <span wire:loading.remove wire:target="completeRegistration">
                            Next Step →
                        </span>
                        <span wire:loading wire:target="completeRegistration" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                    <p id="next-help" class="sr-only">Proceed to step 2 of registration to enter contact information</p>
                </nav>
            </form>

            <!-- Login Link -->
            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-600">
                    Already have an account?
                    <a href="{{ route('voter.login') }}" class="font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded"
                       aria-label="Sign in to your existing account">
                        Sign In
                    </a>
                </p>
            </div>
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
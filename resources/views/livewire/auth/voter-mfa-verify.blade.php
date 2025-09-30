<form wire:submit.prevent="verifyMfa" class="space-y-4">
    <div>
        <label for="email_code" class="block text-sm font-medium text-gray-700 mb-2">
            Email Verification Code
        </label>
        <input
            type="text"
            id="email_code"
            wire:model="email_code"
            maxlength="6"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('email_code') border-red-500 @enderror"
            placeholder="Enter 6-digit code"
            required
        >
        @error('email_code')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone_code" class="block text-sm font-medium text-gray-700 mb-2">
            Phone Verification Code (Optional)
        </label>
        <input
            type="text"
            id="phone_code"
            wire:model="phone_code"
            maxlength="6"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            placeholder="Enter 6-digit code"
        >
        <p class="mt-1 text-xs text-gray-500">Only required if you have a verified phone number</p>
    </div>

    <div class="flex items-center justify-between">
        <button
            type="submit"
            wire:loading.attr="disabled"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            aria-label="Verify and continue to dashboard"
        >
            <span wire:loading.remove>Verify & Continue</span>
            <span wire:loading>Verifying...</span>
        </button>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif
</form>

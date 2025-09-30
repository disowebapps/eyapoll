@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Candidate</h1>
                <p class="text-gray-600">{{ $candidate->getDisplayName() }}</p>
            </div>
            <a href="{{ route('admin.candidates.show', $candidate) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                Cancel
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.candidates.update', $candidate) }}">
            @csrf
            @method('PUT')
            
            <!-- Manifesto -->
            <div class="mb-6">
                <label for="manifesto" class="block text-sm font-medium text-gray-700 mb-2">
                    Manifesto <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="manifesto" 
                    name="manifesto" 
                    rows="8" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('manifesto') border-red-500 @enderror"
                    placeholder="Enter candidate manifesto..."
                    required
                >{{ old('manifesto', $candidate->manifesto) }}</textarea>
                @error('manifesto')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Minimum 10 characters, maximum 5000 characters</p>
            </div>

            <!-- Application Fee -->
            <div class="mb-6">
                <label for="application_fee" class="block text-sm font-medium text-gray-700 mb-2">
                    Application Fee <span class="text-red-500">*</span>
                </label>
                <div class="flex">
                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                        ₦
                    </span>
                    <input 
                        type="number" 
                        id="application_fee" 
                        name="application_fee" 
                        step="0.01" 
                        min="0" 
                        max="1000000"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('application_fee') border-red-500 @enderror"
                        value="{{ old('application_fee', $candidate->application_fee) }}"
                        required
                    >
                </div>
                @error('application_fee')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Maximum ₦1,000,000.00</p>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.candidates.show', $candidate) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Update Candidate
                </button>
            </div>
        </form>
    </div>

    <!-- Warning Notice -->
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Super Admin Action</h3>
                <p class="mt-1 text-sm text-yellow-700">
                    Only super administrators can edit candidate information. Changes will be logged for audit purposes.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
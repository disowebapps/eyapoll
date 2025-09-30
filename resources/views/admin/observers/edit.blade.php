@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Observer</h1>
                <p class="text-gray-600">{{ $observer->first_name }} {{ $observer->last_name }}</p>
            </div>
            <a href="{{ route('admin.observers.show', $observer) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                Cancel
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.observers.update', $observer) }}">
            @csrf
            @method('PUT')
            
            <!-- Access Level -->
            <div class="mb-6">
                <label for="access_level" class="block text-sm font-medium text-gray-700 mb-2">
                    Access Level <span class="text-red-500">*</span>
                </label>
                <select 
                    id="access_level" 
                    name="access_level" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('access_level') border-red-500 @enderror"
                    required
                >
                    <option value="pending" {{ old('status', $observer->status) === 'pending' ? 'selected' : '' }}>
                        Pending - Awaiting approval
                    </option>
                    <option value="approved" {{ old('status', $observer->status) === 'approved' ? 'selected' : '' }}>
                        Approved - Full observer access
                    </option>
                </select>
                @error('access_level')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-6">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Status <span class="text-red-500">*</span>
                </label>
                <select 
                    id="status" 
                    name="status" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-500 @enderror"
                    required
                >
                    <option value="active" {{ old('status', $observer->status) === 'active' ? 'selected' : '' }}>
                        Active - Can access observer dashboard
                    </option>
                    <option value="suspended" {{ old('status', $observer->status) === 'suspended' ? 'selected' : '' }}>
                        Suspended - Temporarily blocked access
                    </option>
                    <option value="inactive" {{ old('status', $observer->status) === 'inactive' ? 'selected' : '' }}>
                        Inactive - No access to system
                    </option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.observers.show', $observer) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Update Observer
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
                <h3 class="text-sm font-medium text-yellow-800">Admin Action</h3>
                <p class="mt-1 text-sm text-yellow-700">
                    Changes to observer permissions will be logged for audit purposes and take effect immediately.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
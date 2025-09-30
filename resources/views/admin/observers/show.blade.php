@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $observer->first_name }} {{ $observer->last_name }}</h1>
                <p class="text-gray-600">Observer Details</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.observers.edit', $observer) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Edit Observer
                </a>
                <a href="{{ route('admin.observers.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-6">
        <span class="px-3 py-1 text-sm font-medium rounded-full 
            {{ $observer->status === 'active' ? 'bg-green-100 text-green-800' : 
               ($observer->status === 'suspended' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800') }}">
            {{ ucfirst(is_string($observer->status) ? $observer->status : $observer->status->value) }}
        </span>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Personal Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Full Name</label>
                        <p class="text-gray-900">{{ $observer->first_name }} {{ $observer->last_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Email</label>
                        <p class="text-gray-900">{{ Str::mask($observer->email, '*', 3, -10) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-gray-900">
                            @if($observer->phone_number)
                                {{ Str::mask($observer->phone_number, '*', 3, -4) }}
                            @else
                                <span class="text-gray-400">Not provided</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Registration Date</label>
                        <p class="text-gray-900">{{ $observer->created_at->format('M j, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Observer Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Observer Access</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Organization</label>
                        <p class="text-gray-900">{{ $observer->organization_name ?: 'Not specified' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        <span class="px-2 py-1 text-xs rounded-full 
                            {{ $observer->status === 'active' ? 'bg-green-100 text-green-800' : 
                               ($observer->status === 'suspended' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst(is_string($observer->status) ? $observer->status : $observer->status->value) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Assigned Elections -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Assigned Elections</h2>
                @if($observer->assignedElections && $observer->assignedElections->count() > 0)
                    <div class="space-y-3">
                        @foreach($observer->assignedElections as $election)
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div>
                                <p class="font-medium">{{ $election->title }}</p>
                                <p class="text-sm text-gray-500">{{ $election->starts_at->format('M j, Y') }} - {{ $election->ends_at->format('M j, Y') }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $election->status->value === 'active' ? 'bg-green-100 text-green-800' : 
                                   ($election->status->value === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($election->status->value) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No elections assigned</p>
                @endif
            </div>
        </div>

        <!-- Right Column - Stats & Actions -->
        <div class="space-y-6">
            <!-- Activity Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Activity Statistics</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Elections Observed</span>
                        <span class="text-2xl font-bold text-indigo-600">{{ $observer->assignedElections?->count() ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Status</span>
                        <span class="text-lg font-semibold">{{ ucfirst(is_string($observer->status) ? $observer->status : $observer->status->value) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Type</span>
                        <span class="text-sm">{{ ucfirst($observer->type ?: 'Standard') }}</span>
                    </div>
                </div>
            </div>

            <!-- Approval Info -->
            @if($observer->approvedBy)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Approval Details</h2>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm text-gray-600">Approved by:</span>
                        <p class="font-medium">{{ $observer->approvedBy->first_name }} {{ $observer->approvedBy->last_name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Approved on:</span>
                        <p class="font-medium">{{ $observer->approved_at?->format('M j, Y g:i A') ?? 'Not available' }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('title', 'KYC Document Review')
@section('page-title', 'KYC Document Review')

@section('navigation')
    <div class="space-y-1">
        <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-md">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/>
            </svg>
            Dashboard
        </a>
        <a href="{{ route('admin.users.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-md">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
            </svg>
            User Management
        </a>
        <a href="{{ route('admin.kyc.review') }}" class="group flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md">
            <svg class="w-5 h-5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            KYC Review
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    @livewire('admin.kyc-review')
</div>
@endsection
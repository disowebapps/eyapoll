@extends('layouts.guest')

@section('title', 'Executive Committee - Echara Youths')

@section('main-class', 'pt-20')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Executive Committee</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">Meet our executive leadership driving progress and innovation</p>
        </div>

        <!-- Quick Links -->
        <div class="flex justify-center mb-8">
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <div class="flex space-x-4">
                    <a href="{{ route('public.members') }}" 
                       class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        All Members
                    </a>
                    <a href="{{ route('public.executives') }}" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg transition-colors">
                        Executives
                    </a>
                </div>
            </div>
        </div>

        <!-- Executives Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
            @forelse($executives as $executive)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300 group">
                <!-- Profile Header -->
                <div class="relative bg-gradient-to-r from-blue-500 to-indigo-600 p-6 text-white">
                
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <img src="{{ $executive->profile_image_url }}" 
                                 alt="{{ $executive->full_name }}" 
                                 class="w-16 h-16 rounded-full border-3 border-white shadow-lg object-cover">
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-400 rounded-full border-2 border-white flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold">{{ $executive->full_name }}</h3>
                            <div class="mt-1 space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Verified
                                    </span>
                                    <span class="text-blue-100 text-xs">{{ $executive->current_position }}</span>
                                </div>
                                <div class="text-blue-100 text-xs">
                                    Member since {{ $executive->created_at->format('M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="p-6">

                    <!-- About Me -->
                    @if($executive->bio)
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-3">
                            {{ Str::limit($executive->bio, 120) }}
                        </p>
                    </div>
                    @endif

                    <div class="border-b border-gray-100 mb-4"></div>

                    <!-- Term Information -->
                    @if($executive->term_start || $executive->term_end)
                    <div class="mb-4">
                        <div class="flex items-center text-sm text-gray-600 mb-2">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V6a2 2 0 012-2h4a2 2 0 012 2v1m-6 0h8m-8 0H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-2"></path>
                            </svg>
                            <span class="font-medium">Term:</span>
                            <span class="ml-1">
                                {{ $executive->term_start ? $executive->term_start->format('M Y') : 'Current' }} - 
                                {{ $executive->term_end ? $executive->term_end->format('M Y') : 'Present' }}
                            </span>
                        </div>
                    </div>
                    @endif

                    <!-- Action Button -->
                    <div class="mt-4">
                        <a href="{{ route('public.member.profile', $executive->id) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-600 text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                            View Profile
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No executives found</h3>
                <p class="text-gray-500">Executive positions will be displayed here once members are sworn in.</p>
            </div>
            @endforelse
        </div>
    </div>
@endsection
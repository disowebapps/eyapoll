@extends('layouts.app')

@section('title', 'Voter Dashboard')
@section('page-title', 'Voter Dashboard')




@section('header-actions')
    @php
        $isVerified = Auth::user()->status->value === 'active';
        $hasUpcomingElections = true;
    @endphp

    @if($isVerified && $hasUpcomingElections)
        <a href="{{ route('candidate.apply', ['election' => 1]) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-medium">
            Register as Candidate
        </a>
    @endif
@endsection

@section('content')
    <div class="pt-12">
    @php
        $kycStatus = Auth::user()->getKycStatus();
    @endphp

    @if($kycStatus['status'] === 'required')
        <div class="max-w-2xl mx-auto">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-yellow-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-lg font-medium text-yellow-800 mb-2">{{ $kycStatus['text'] }}</h3>
                <p class="text-yellow-700 mb-4">
                    {{ $kycStatus['subtext'] }}
                </p>
                <a href="{{ route('voter.kyc') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Upload KYC Documents
                </a>
            </div>
        </div>
    @else
        <livewire:voter.dashboard />

        @php
            $ongoingElections = \App\Models\Election\Election::ongoing()->limit(3)->get();
        @endphp

        @if($ongoingElections->count() > 0)
            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Ongoing Elections</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($ongoingElections as $election)
                        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $election->title }}</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Ends: {{ $election->ends_at->format('M j, Y g:i A') }}
                            </p>
                            <a href="{{ route('voter.elections') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Election →
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 text-center">
                    <a href="{{ route('voter.elections') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium">
                        View All Elections
                    </a>
                </div>
            </div>
        @endif
    @endif
    </div>
@endsection
@extends('layouts.app')

@section('title', 'Candidates - ' . $election->title)

@section('content')
<div class="max-w-6xl mx-auto p-4 md:p-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border p-4 md:p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $election->title }}</h1>
        <p class="text-gray-600 mb-4">{{ $election->description }}</p>
        <div class="flex items-center text-sm text-gray-500">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                ✅ Candidate List Published
            </span>
            <span class="ml-4">Published: {{ $election->candidate_list_published->format('M d, Y H:i') }}</span>
        </div>
    </div>

    <!-- Candidates by Position -->
    @forelse($candidates as $positionTitle => $positionCandidates)
        <div class="bg-white rounded-lg shadow-sm border p-4 md:p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $positionTitle }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($positionCandidates as $candidate)
                    <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                        <h3 class="font-medium text-lg">{{ $candidate->user->first_name }} {{ $candidate->user->last_name }}</h3>
                        <p class="text-sm text-gray-600 mb-2">{{ $candidate->user->email }}</p>
                        @if($candidate->user->phone)
                            <p class="text-sm text-gray-600 mb-2">{{ $candidate->user->phone }}</p>
                        @endif
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Approved Candidate
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow-sm border p-4 md:p-6 text-center">
            <p class="text-gray-500">No approved candidates found for this election.</p>
        </div>
    @endforelse
</div>
@endsection
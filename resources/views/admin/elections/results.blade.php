@extends('layouts.admin')

@section('title', 'Election Results - Admin')

@section('page-title', 'Election Results')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Election Results</h1>
                <p class="text-gray-600 mt-1">Admin view of election results and analytics</p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                <a href="{{ route('admin.elections.show', $electionId) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                    ← Back to Election
                </a>
            </div>
        </div>
    </div>

    <!-- Livewire Component -->
    @livewire('admin.election-results', ['electionId' => $electionId])
</div>
@endsection
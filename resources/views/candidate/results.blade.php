@extends('layouts.app')

@section('title', 'Election Results')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <livewire:candidate.results :electionId="$electionId ?? null" />
    </div>
</div>
@endsection
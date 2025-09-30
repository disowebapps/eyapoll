@extends('layouts.admin')

@section('title', 'Emergency Controls')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">🛑 Emergency Election Controls</h1>
        <p class="text-gray-600">Critical system controls - Super Admin access only</p>
    </div>

    @if(session('emergency_success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('emergency_success') }}
        </div>
    @endif

    <livewire:admin.emergency-controls />
</div>
@endsection
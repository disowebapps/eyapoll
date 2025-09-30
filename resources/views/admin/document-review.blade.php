@extends('layouts.admin')

@section('title', 'Document Review')

@section('content')
@php
    \Illuminate\Support\Facades\Log::info('View admin.document-review rendering');
@endphp
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <livewire:admin.document-review />
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'KYC Required')
@section('page-title', 'Identity Verification Required')

@section('navigation')
    <div class="space-y-1">
        <a href="#" class="group flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md">
            <svg class="w-5 h-5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            KYC Required
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Warning Icon -->
    <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
    </div>

    <!-- KYC Required Message -->
    <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Identity Verification Required</h1>
        <p class="text-gray-600 mb-6">
            To access voting features and participate in elections, you must complete your identity verification (KYC).
        </p>

        <!-- Requirements -->
        <div class="bg-blue-50 rounded-lg p-4 mb-6 text-left">
            <h3 class="font-semibold text-blue-900 mb-2">What you'll need:</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Valid government-issued ID</li>
                <li>• Clear photo of ID front and back</li>
                <li>• Recent selfie photo</li>
                <li>• Phone number for SMS verification</li>
            </ul>
        </div>

        <!-- Actions -->
        <div class="space-y-3">
            <a href="{{ route('auth.register.step2') }}" 
               class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition inline-block">
                Complete KYC Verification
            </a>
        </div>

        <!-- Help -->
        <div class="mt-6 pt-4 border-t border-gray-200">
            <p class="text-sm text-gray-600">
                Need help? <a href="#" class="text-blue-600 hover:text-blue-500">Contact Support</a>
            </p>
        </div>
    </div>
</div>
@endsection
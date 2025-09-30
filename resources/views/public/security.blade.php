@extends('layouts.guest')

@section('title', 'Security - AYApoll')

@section('main-class', 'pt-20')

@section('content')
<x-public.hero-section class="pb-16">
    <x-slot name="title">
        Democratic <span class="text-blue-600">Integrity</span>
    </x-slot>
    <x-slot name="subtitle">
        Every civic action is protected by multiple layers of cryptographic security, ensuring the integrity of democratic processes.
    </x-slot>
</x-public.hero-section>

<!-- Security Features -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Civic Tech Security Principles</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Our security architecture implements immutable democratic principles with cryptographic guarantees.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Encryption -->
            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Ballot Secrecy Protection</h3>
                <p class="text-gray-600">
                    All civic participation is encrypted using AES-256, ensuring complete separation of identity from ballot choices.
                </p>
            </div>

            <!-- Blockchain -->
            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Immutable Audit Trail</h3>
                <p class="text-gray-600">
                    Every civic action creates an immutable blockchain entry. Democratic integrity is mathematically guaranteed.
                </p>
            </div>

            <!-- Multi-Factor -->
            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">One Person, One Vote</h3>
                <p class="text-gray-600">
                    Multi-factor identity verification ensures voter uniqueness while maintaining complete privacy protection.
                </p>
            </div>

            <!-- Audit Logging -->
            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                <div class="w-16 h-16 bg-orange-600 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Complete Audit Trail</h3>
                <p class="text-gray-600">
                    Every action is logged with cryptographic signatures. All logs are immutable and publicly verifiable.
                </p>
            </div>

            <!-- DDoS Protection -->
            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                <div class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">DDoS Protection</h3>
                <p class="text-gray-600">
                    Enterprise-grade DDoS protection ensures the platform remains available during peak voting periods.
                </p>
            </div>

            <!-- Penetration Testing -->
            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                <div class="w-16 h-16 bg-indigo-600 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Regular Security Audits</h3>
                <p class="text-gray-600">
                    Independent security firms conduct regular penetration testing and vulnerability assessments.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Compliance Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Regulatory Compliance</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                We adhere to the highest standards of data protection and election security.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-12">
            <div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-6">Data Protection</h3>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <strong class="text-gray-900">GDPR Compliant</strong>
                            <p class="text-gray-600">Full compliance with EU General Data Protection Regulation</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <strong class="text-gray-900">CCPA Ready</strong>
                            <p class="text-gray-600">California Consumer Privacy Act compliance</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <strong class="text-gray-900">ISO 27001 Certified</strong>
                            <p class="text-gray-600">Information security management system certification</p>
                        </div>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-6">Election Standards</h3>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <strong class="text-gray-900">EAC Certified</strong>
                            <p class="text-gray-600">Election Assistance Commission voting system standards</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <strong class="text-gray-900">NIST Compliant</strong>
                            <p class="text-gray-600">National Institute of Standards and Technology guidelines</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <strong class="text-gray-900">FIPS 140-2 Validated</strong>
                            <p class="text-gray-600">Federal Information Processing Standards cryptography</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Trust Indicators -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Trusted by Organizations Worldwide</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Our security measures have been verified by independent auditors and trusted by governments and organizations globally.
            </p>
        </div>

        <div class="grid md:grid-cols-4 gap-8 text-center">
            <div class="p-6">
                <div class="text-4xl font-bold text-blue-600 mb-2">99.99%</div>
                <div class="text-gray-600">Uptime SLA</div>
            </div>
            <div class="p-6">
                <div class="text-4xl font-bold text-green-600 mb-2">256-bit</div>
                <div class="text-gray-600">Encryption Standard</div>
            </div>
            <div class="p-6">
                <div class="text-4xl font-bold text-purple-600 mb-2">24/7</div>
                <div class="text-gray-600">Security Monitoring</div>
            </div>
            <div class="p-6">
                <div class="text-4xl font-bold text-orange-600 mb-2">0</div>
                <div class="text-gray-600">Security Breaches</div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-10 bg-gradient-to-r from-blue-600 to-blue-900">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-white mb-6">
            Democracy You Can Trust
        </h2>
        <p class="text-xl text-blue-100 mb-8">
            Experience the most secure civic engagement platform available. Democratic integrity is our foundation.
        </p>
        <a href="{{ route('public.verify.receipt') }}" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-4 rounded-lg text-lg font-semibold transition inline-block mr-4">
            Verify a Vote
        </a>
        <a href="{{ route('public.election-integrity') }}" class="border-2 border-white text-white hover:bg-white hover:text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold transition inline-block">
            Election Integrity
        </a>
    </div>
</section>
@endsection
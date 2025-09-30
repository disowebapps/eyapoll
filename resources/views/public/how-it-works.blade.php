@extends('layouts.guest')

@section('title', 'How It Works - AYApoll')

@section('main-class', 'pt-20')

@section('content')
<x-public.hero-section class="pb-16">
    <x-slot name="title">
        How <span class="text-blue-600">Civic Engagement</span> Works
    </x-slot>
    <x-slot name="subtitle">
        Participate in transparent, secure democratic processes designed for the digital generation.
    </x-slot>
</x-public.hero-section>

<!-- Process Steps -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Your Democratic Journey</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                From joining the assembly to making your voice heard, civic participation made simple and secure.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Step 1 -->
            <div class="text-center">
                <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-white">1</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Join the Assembly</h3>
                <p class="text-gray-600">
                    Register as a member with identity verification. Join a community of young leaders committed to democratic change.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="text-center">
                <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-white">2</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Get Verified</h3>
                <p class="text-gray-600">
                    Complete civic identity verification and receive your democratic participation token. One person, one voice guaranteed.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="text-center">
                <div class="w-20 h-20 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-white">3</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Participate</h3>
                <p class="text-gray-600">
                    Engage in elections, policy votes, and civic initiatives. Your voice shapes the future through secure digital democracy.
                </p>
            </div>

            <!-- Step 4 -->
            <div class="text-center">
                <div class="w-20 h-20 bg-orange-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-white">4</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Verify Impact</h3>
                <p class="text-gray-600">
                    Receive cryptographic proof and verify your participation was recorded. Complete transparency in democratic processes.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Technology Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Civic Tech Innovation</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Democratic principles meet cutting-edge technology to ensure every voice is heard and counted.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-xl shadow-sm">
                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">End-to-End Encryption</h3>
                <p class="text-gray-600">
                    Every vote is encrypted from the moment it's cast until the final tally. Only authorized parties can decrypt results.
                </p>
            </div>

            <div class="bg-white p-8 rounded-xl shadow-sm">
                <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Blockchain Verification</h3>
                <p class="text-gray-600">
                    Each vote creates an immutable blockchain entry. The entire chain can be publicly verified for integrity.
                </p>
            </div>

            <div class="bg-white p-8 rounded-xl shadow-sm">
                <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Zero-Knowledge Proofs</h3>
                <p class="text-gray-600">
                    Verify vote validity without revealing voter choices. Mathematical proofs ensure election integrity.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
            <p class="text-xl text-gray-600">
                Get answers to common questions about our voting platform.
            </p>
        </div>

        <div class="space-y-6">
            <div class="border border-gray-200 rounded-lg">
                <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50" onclick="toggleFAQ(this)">
                    <span class="font-semibold text-gray-900">Is my vote really anonymous?</span>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="px-6 pb-4 hidden">
                    <p class="text-gray-600">
                        Yes, your vote is completely anonymous. We use cryptographic techniques that separate voter identity from vote content. Even system administrators cannot link votes to specific voters.
                    </p>
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg">
                <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50" onclick="toggleFAQ(this)">
                    <span class="font-semibold text-gray-900">How do I know my vote was counted?</span>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="px-6 pb-4 hidden">
                    <p class="text-gray-600">
                        After voting, you receive a unique cryptographic receipt. You can verify this receipt on our public verification page to confirm your vote was recorded exactly as cast.
                    </p>
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg">
                <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50" onclick="toggleFAQ(this)">
                    <span class="font-semibold text-gray-900">What happens if there's a technical issue?</span>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="px-6 pb-4 hidden">
                    <p class="text-gray-600">
                        Our platform includes multiple redundancy systems and real-time monitoring. In case of issues, voting can be paused and resumed. All votes cast before any interruption remain secure and valid.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-10 bg-gradient-to-r from-blue-600 to-blue-900">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-white mb-6">
            Ready to Shape Your Future?
        </h2>
        <p class="text-xl text-blue-100 mb-8">
            Join young leaders worldwide who are building the future through democratic participation.
        </p>
        <a href="{{ route('auth.register') }}" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-4 rounded-lg text-lg font-semibold transition inline-block">
            Get Started Today
        </a>
    </div>
</section>

<script>
function toggleFAQ(button) {
    const content = button.nextElementSibling;
    const icon = button.querySelector('svg');

    content.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}
</script>
@endsection
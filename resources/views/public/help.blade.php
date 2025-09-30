@extends('layouts.guest')

@section('title', 'Help Center - Echara Youth Assembly')

@section('main-class', 'pt-16')

@section('content')
<div class="bg-white">
    <x-public.hero-section 
        title="Help Center" 
        subtitle="Find answers to your questions and get support for your democratic participation journey." 
    />

    <!-- Quick Help Categories -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">How can we help you?</h2>
                <p class="text-lg text-gray-600">Choose a category to find quick answers</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border hover:shadow-md transition-shadow cursor-pointer">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Account Setup</h3>
                    <p class="text-gray-600 text-sm">Registration, verification, and profile management</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border hover:shadow-md transition-shadow cursor-pointer">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Voting Process</h3>
                    <p class="text-gray-600 text-sm">How to vote, verify receipts, and check results</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border hover:shadow-md transition-shadow cursor-pointer">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Security & Privacy</h3>
                    <p class="text-gray-600 text-sm">Data protection, encryption, and privacy settings</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border hover:shadow-md transition-shadow cursor-pointer">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Technical Support</h3>
                    <p class="text-gray-600 text-sm">Troubleshooting, browser issues, and platform errors</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
                <p class="text-lg text-gray-600">Quick answers to common questions</p>
            </div>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50" onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900">How do I register to vote?</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="px-6 pb-4 hidden">
                        <p class="text-gray-600">Click "Register" on the homepage, fill out the form with your personal information, upload required documents for verification, and wait for approval from our admin team.</p>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50" onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900">Is my vote really secret?</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="px-6 pb-4 hidden">
                        <p class="text-gray-600">Yes, absolutely. We use advanced cryptographic techniques to ensure your vote choices are completely anonymous. Even system administrators cannot link your identity to your specific votes.</p>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50" onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900">How do I verify my vote was counted?</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="px-6 pb-4 hidden">
                        <p class="text-gray-600">After voting, you'll receive a unique cryptographic receipt. Visit our "Verify Vote" page, enter your receipt code, and the system will confirm your vote was recorded correctly.</p>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50" onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900">What if I forget my password?</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="px-6 pb-4 hidden">
                        <p class="text-gray-600">Click "Forgot Password" on the login page, enter your email address, and we'll send you a secure link to reset your password. The link expires after 1 hour for security.</p>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50" onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900">Can I change my vote after submitting?</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="px-6 pb-4 hidden">
                        <p class="text-gray-600">No, once you submit your vote, it cannot be changed. This ensures election integrity. Please review your choices carefully before clicking "Submit Vote".</p>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50" onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900">What browsers are supported?</span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="px-6 pb-4 hidden">
                        <p class="text-gray-600">We support Chrome 90+, Firefox 88+, Safari 14+, and Edge 90+. For the best experience, please use the latest version of your preferred browser and enable JavaScript.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Support -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Still Need Help?</h2>
            <p class="text-lg text-gray-600 mb-8">Our support team is here to assist you</p>
            
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Email Support</h3>
                    <p class="text-gray-600 text-sm mb-4">Get detailed help via email</p>
                    <a href="mailto:support@echarayouth.org" class="text-blue-600 hover:text-blue-700 font-medium">support@echarayouth.org</a>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Phone Support</h3>
                    <p class="text-gray-600 text-sm mb-4">Speak directly with our team</p>
                    <a href="tel:+2348012345678" class="text-green-600 hover:text-green-700 font-medium">+234 801 234 5678</a>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Live Chat</h3>
                    <p class="text-gray-600 text-sm mb-4">Instant help during elections</p>
                    <button class="text-purple-600 hover:text-purple-700 font-medium">Start Chat</button>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function toggleFAQ(button) {
    const content = button.nextElementSibling;
    const icon = button.querySelector('svg');

    content.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}
</script>
@endsection
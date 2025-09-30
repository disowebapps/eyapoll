@extends('layouts.guest')

@section('title', 'Echara Youth Assembly - One Voice, One Future')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
#particles-js {
    position: absolute;
    width: 100%;
    height: 90%;
    top: 0;
    left: 0;
    z-index: 1;
}
</style>
@endpush

@section('main-class', '')

@section('content')
<!-- Hero Section -->
<section class="pt-28 pb-8 bg-gradient-to-r from-blue-50 to-blue-200 relative overflow-hidden">
    <div id="particles-js"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6 relative z-10">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-1 items-center min-h-[500px]">
            <div class="text-center lg:text-left">
                <h1 class="text-3xl md:text-5xl font-bold text-gray-900 py-6">
                    Secure Digital <span class="text-blue-600">Voting</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    The most trusted platform for transparent, verifiable, and secure digital elections. 
                    Empowering democracy in the digital age.
                </p>
                <div class="flex flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('voter.register') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg text-lg font-semibold transition">
                        Register
                    </a>
                    <a href="{{ route('voter.login') }}"
                       class="border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white
                              px-8 py-3 rounded-lg text-lg font-semibold transition">
                        Login
                    </a>
                </div>
                
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <p class="text-sm text-gray-600 mb-3">Already registered? Check your voter status:</p>
                    <a href="{{ route('public.voter-register') }}"
                       class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Check Voter Register
                    </a>
                </div>
            </div>
            <div class="flex justify-center lg:justify-end">
                <img src="{{ asset('storage/asset/image/home/vote.png') }}" alt="Digital Voting" class="w-full max-w-md rounded-lg shadow-lg" loading="eager" style="min-height: 300px; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);">
            </div>
        </div>
    </div>
</section>

<!-- Who We Are Section -->
<section class="py-10 bg-blue-50" x-data="{ activeCard: null }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-20">
            <div class="inline-flex items-center bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-semibold mb-6">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Who We Are
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-6 leading-tight">
                Echara Youth <span class="text-blue-600">Assembly</span>
            </h2>
            <p class="text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                A dynamic Community Youth Association dedicated to empowering young voices and fostering 
                democratic participation in Echara community through innovation, transparency, and inclusive leadership.
            </p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid lg:grid-cols-12 gap-12 items-start mb-10">
            <!-- Mission Statement -->
            <div class="lg:col-span-7">
                <div class="bg-gradient-to-r from-blue-50 to-blue-200 rounded-3xl shadow-xl p-8 lg:p-10 border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">Our Mission</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed text-lg mb-8">
                        To create an inclusive platform where young people in Echara community can actively participate in democratic processes, 
                        develop leadership skills, and contribute meaningfully to community development through transparent and accountable governance.
                    </p>
                    
                    <!-- Key Focus Areas -->
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="flex items-center p-3 bg-blue-50 rounded-xl">
                            <div class="w-2 h-2 bg-blue-600 rounded-full mr-3"></div>
                            <span class="text-gray-800 font-medium">Youth Empowerment</span>
                        </div>
                        <div class="flex items-center p-3 bg-green-50 rounded-xl">
                            <div class="w-2 h-2 bg-green-600 rounded-full mr-3"></div>
                            <span class="text-gray-800 font-medium">Democratic Participation</span>
                        </div>
                        <div class="flex items-center p-3 bg-purple-50 rounded-xl">
                            <div class="w-2 h-2 bg-purple-600 rounded-full mr-3"></div>
                            <span class="text-gray-800 font-medium">Community Development</span>
                        </div>
                        <div class="flex items-center p-3 bg-orange-50 rounded-xl">
                            <div class="w-2 h-2 bg-orange-600 rounded-full mr-3"></div>
                            <span class="text-gray-800 font-medium">Leadership Training</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Impact Stats -->
            <div class="lg:col-span-5">
                <div class="bg-blue-600 rounded-2xl p-8 text-white shadow-lg">
                    <h3 class="text-2xl font-bold mb-8 text-center">Our Impact</h3>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <span class="text-2xl font-bold">500+</span>
                            </div>
                            <p class="text-blue-100 text-sm font-medium">Active Members</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <span class="text-2xl font-bold">50+</span>
                            </div>
                            <p class="text-blue-100 text-sm font-medium">Community Projects</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <span class="text-2xl font-bold">10+</span>
                            </div>
                            <p class="text-blue-100 text-sm font-medium">Years of Service</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <span class="text-xl font-bold">100%</span>
                            </div>
                            <p class="text-blue-100 text-sm font-medium">Transparency</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Core Values -->
        <div class="grid md:grid-cols-3 gap-8 mb-16">
            <div class="group">
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl hover:border-blue-200 transition-all duration-300 h-full">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3 text-center">Youth Leadership</h4>
                    <p class="text-gray-600 text-center leading-relaxed">Developing the next generation of community leaders through mentorship and practical experience.</p>
                </div>
            </div>
            
            <div class="group">
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl hover:border-green-200 transition-all duration-300 h-full">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3 text-center">Transparent Governance</h4>
                    <p class="text-gray-600 text-center leading-relaxed">Ensuring all decisions are made openly with full accountability to our community members.</p>
                </div>
            </div>
            
            <div class="group">
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl hover:border-purple-200 transition-all duration-300 h-full">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3 text-center">Community Impact</h4>
                    <p class="text-gray-600 text-center leading-relaxed">Creating lasting positive change through collaborative projects and community engagement.</p>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="text-center">
            <a href="{{ route('public.about') }}" class="group inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-semibold text-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                Learn More About Us
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-10 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="text-blue-600 font-semibold text-sm uppercase tracking-wide">Platform Features</span>
            <h2 class="text-4xl font-bold text-gray-900 mt-2 mb-4">Enterprise-Level Digital Voting</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                Suitable for secure, transparent, and accessible elections
            </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-8">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Bank-Level Security</h3>
                        <p class="text-gray-600 leading-relaxed">End-to-end encryption with cryptographic verification ensures complete vote integrity and voter privacy protection.</p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Complete Transparency</h3>
                        <p class="text-gray-600 leading-relaxed">Real-time audit trails and public verification while maintaining ballot secrecy through advanced cryptographic methods.</p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Universal Accessibility</h3>
                        <p class="text-gray-600 leading-relaxed">Mobile-first design optimized for all devices, screen readers, and low-bandwidth environments.</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-blue-50 to-blue-200 rounded-2xl shadow-xl p-8 border border-gray-100">
                <div class="grid grid-cols-2 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600 mb-1">99.9%</div>
                        <div class="text-sm text-gray-600">Uptime SLA</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600 mb-1">256-bit</div>
                        <div class="text-sm text-gray-600">Encryption</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600 mb-1">24/7</div>
                        <div class="text-sm text-gray-600">Support</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-orange-600 mb-1">WCAG</div>
                        <div class="text-sm text-gray-600">Compliant</div>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <h4 class="font-semibold text-gray-900 mb-3">Compliance & Certifications</h4>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">ISO 27001</span>
                        <span class="px-3 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-full">SOC 2</span>
                        <span class="px-3 py-1 bg-purple-50 text-purple-700 text-xs font-medium rounded-full">GDPR</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-20 bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold mb-4">Trusted Worldwide</h2>
            <p class="text-xl text-gray-300">Join thousands of organizations using AYApoll for secure elections</p>
        </div>
        
        <div class="grid md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl font-bold text-blue-400 mb-2">10K+</div>
                <div class="text-gray-300">Elections Conducted</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-green-400 mb-2">500K+</div>
                <div class="text-gray-300">Votes Cast</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-purple-400 mb-2">99.9%</div>
                <div class="text-gray-300">Uptime</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-orange-400 mb-2">24/7</div>
                <div class="text-gray-300">Support</div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-center mb-12 lg:hidden">
            <img src="{{ asset('storage/asset/image/home/security.png') }}" alt="Echara Collaboration" class="w-full max-w-lg rounded-lg" loading="lazy" style="min-height: 250px; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);">
        </div>
        
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
            <p class="text-lg text-gray-600">Everything you need to know about secure digital voting</p>
        </div>
        
        <div class="grid lg:grid-cols-2 gap-12 items-start">
            <div class="space-y-4" x-data="{ open: null }">
                <div class="border border-gray-200 rounded-lg">
                    <button @click="open = open === 1 ? null : 1" class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                        <span class="font-semibold text-gray-900">How secure is digital voting?</span>
                        <svg class="w-5 h-5 transform transition-transform" :class="open === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse class="px-6 pb-4">
                        <p class="text-gray-600">Our platform uses bank-level 256-bit encryption, cryptographic verification, and blockchain technology to ensure complete vote integrity and voter privacy.</p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg">
                    <button @click="open = open === 2 ? null : 2" class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                        <span class="font-semibold text-gray-900">Can I verify my vote was counted?</span>
                        <svg class="w-5 h-5 transform transition-transform" :class="open === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse class="px-6 pb-4">
                        <p class="text-gray-600">Yes, you receive a unique receipt hash that allows you to verify your vote was included in the final tally while maintaining ballot secrecy.</p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg">
                    <button @click="open = open === 3 ? null : 3" class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                        <span class="font-semibold text-gray-900">What devices can I use to vote?</span>
                        <svg class="w-5 h-5 transform transition-transform" :class="open === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse class="px-6 pb-4">
                        <p class="text-gray-600">Our platform works on any device - smartphones, tablets, laptops, or desktop computers. It's optimized for low bandwidth and accessible to screen readers.</p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg">
                    <button @click="open = open === 4 ? null : 4" class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                        <span class="font-semibold text-gray-900">How do you prevent duplicate voting?</span>
                        <svg class="w-5 h-5 transform transition-transform" :class="open === 4 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse class="px-6 pb-4">
                        <p class="text-gray-600">We enforce "one person, one vote" through rigorous ID verification, unique voter tokens, and real-time duplicate detection systems.</p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg">
                    <button @click="open = open === 5 ? null : 5" class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50">
                        <span class="font-semibold text-gray-900">Is technical support available during elections?</span>
                        <svg class="w-5 h-5 transform transition-transform" :class="open === 5 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open === 5" x-collapse class="px-6 pb-4">
                        <p class="text-gray-600">Yes, we provide 24/7 technical support during all elections through live chat, phone, and email to ensure smooth voting for all participants.</p>
                    </div>
                </div>
            </div>
            
            <div class="hidden lg:flex justify-end">
                <img src="{{ asset('storage/asset/image/home/security.png') }}" alt="Echara Collaboration" class="w-full max-w-lg rounded-lg" loading="lazy" style="min-height: 250px; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);">
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-10 bg-gradient-to-r from-blue-600 to-blue-900">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-white mb-6">
            Ready to Experience Secure Digital Voting?
        </h2>
        <p class="text-xl text-blue-100 mb-8">
            Join the future of democracy. Create your account and participate in transparent, verifiable elections.
        </p>
        <a href="{{ route('voter.register') }}" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-4 rounded-lg text-lg font-semibold transition inline-block">
            Get Started Free
        </a>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
particlesJS('particles-js', {
    particles: {
        number: { value: 80, density: { enable: true, value_area: 800 } },
        color: { value: "#3b82f6" },
        shape: { type: "circle" },
        opacity: { value: 0.5, random: false },
        size: { value: 3, random: true },
        line_linked: { enable: true, distance: 150, color: "#3b82f6", opacity: 0.4, width: 1 },
        move: { enable: true, speed: 2, direction: "none", random: false, straight: false, out_mode: "out", bounce: false }
    },
    interactivity: {
        detect_on: "canvas",
        events: { onhover: { enable: true, mode: "repulse" }, onclick: { enable: true, mode: "push" }, resize: true },
        modes: { repulse: { distance: 200, duration: 0.4 }, push: { particles_nb: 4 } }
    },
    retina_detect: true
});
</script>
@endpush
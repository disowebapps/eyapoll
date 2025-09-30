@extends('layouts.guest')

@section('title', 'Privacy Policy - Echara Youth Assembly')

@section('main-class', 'pt-16')

@section('content')
<div class="bg-white">
    <x-public.hero-section 
        title="Privacy Policy" 
        subtitle="Your privacy is our priority. Learn how we protect your personal information and democratic participation." 
    />

    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8">
                <p class="text-blue-800"><strong>Last updated:</strong> December 2024</p>
            </div>

            <div class="space-y-8">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Information We Collect</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Personal Information</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li>Name, email address, phone number</li>
                                <li>Date of birth and identity verification documents</li>
                                <li>Profile information and preferences</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Voting Data</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li>Encrypted voting choices (anonymized)</li>
                                <li>Participation timestamps</li>
                                <li>Cryptographic receipts</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">2. How We Use Your Information</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Service Provision</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li>Account creation and management</li>
                                <li>Election participation</li>
                                <li>Identity verification</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Communication</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li>Election notifications</li>
                                <li>Platform updates</li>
                                <li>Support responses</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Data Protection & Security</h2>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">End-to-End Encryption</h3>
                                <p class="text-gray-600">All sensitive data is encrypted using AES-256 encryption standards.</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Vote Anonymization</h3>
                                <p class="text-gray-600">Voting choices are completely separated from voter identity.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Your Rights</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Access & Control</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li>View your personal data</li>
                                <li>Update your information</li>
                                <li>Delete your account</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Data Portability</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li>Export your data</li>
                                <li>Transfer to other platforms</li>
                                <li>Receive data in standard formats</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Contact Us</h2>
                    <div class="space-y-4">
                        <p class="text-gray-600">If you have questions about this Privacy Policy, contact us:</p>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Privacy Officer</h3>
                                <p class="text-gray-600">Email: privacy@echarayouth.org</p>
                                <p class="text-gray-600">Phone: +234 801 234 5678</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Mailing Address</h3>
                                <p class="text-gray-600">Echara Youth Assembly<br>Ndegu Echara Village<br>Ebonyi State, Nigeria</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
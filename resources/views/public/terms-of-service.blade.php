@extends('layouts.guest')

@section('title', 'Terms of Service - Echara Youth Assembly')

@section('main-class', 'pt-16')

@section('content')
<div class="bg-white">
    <x-public.hero-section 
        title="Terms of Service" 
        subtitle="Understanding your rights and responsibilities as a member of our democratic community." 
    />

    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8">
                <p class="text-blue-800"><strong>Last updated:</strong> December 2024</p>
                <p class="text-blue-700 text-sm mt-1">By using our platform, you agree to these terms.</p>
            </div>

            <div class="space-y-8">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Acceptance of Terms</h2>
                    <p class="text-gray-600 mb-4">By accessing and using the Echara Youth Assembly platform, you accept and agree to be bound by the terms and provision of this agreement.</p>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700"><strong>Important:</strong> If you do not agree to abide by the above, please do not use this service.</p>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Eligibility & Membership</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Age Requirements</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li>Must be 18 years or older to participate in elections</li>
                                <li>Must be a resident of Echara community</li>
                                <li>Must provide valid identification for verification</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Account Responsibilities</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li>Maintain accurate and current information</li>
                                <li>Keep login credentials secure</li>
                                <li>One account per person policy</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Democratic Participation Rules</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Voting Conduct</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li>Vote independently and freely</li>
                                <li>No vote buying or coercion</li>
                                <li>Respect election schedules</li>
                                <li>Report irregularities promptly</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Candidate Guidelines</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li>Truthful campaign information</li>
                                <li>Respectful discourse</li>
                                <li>Comply with election timelines</li>
                                <li>Accept results gracefully</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Prohibited Activities</h2>
                    <div class="space-y-4">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-red-800 mb-2">Strictly Forbidden</h3>
                            <ul class="list-disc list-inside text-red-700 space-y-1">
                                <li>Attempting to manipulate election results</li>
                                <li>Creating multiple accounts</li>
                                <li>Sharing login credentials</li>
                                <li>Harassment or intimidation of other users</li>
                                <li>Spreading false information about elections</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Platform Availability</h2>
                    <div class="space-y-4">
                        <p class="text-gray-600">We strive to maintain 99.9% uptime but cannot guarantee uninterrupted service.</p>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Maintenance</h3>
                                <ul class="list-disc list-inside text-gray-600 space-y-1">
                                    <li>Scheduled maintenance notifications</li>
                                    <li>Emergency maintenance as needed</li>
                                    <li>Service restoration priorities</li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Support</h3>
                                <ul class="list-disc list-inside text-gray-600 space-y-1">
                                    <li>24/7 technical support during elections</li>
                                    <li>Help desk for general inquiries</li>
                                    <li>Community forums for peer support</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Limitation of Liability</h2>
                    <div class="space-y-4">
                        <p class="text-gray-600">The Echara Youth Assembly platform is provided "as is" without warranties of any kind.</p>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-yellow-800 text-sm"><strong>Disclaimer:</strong> We are not liable for any indirect, incidental, or consequential damages arising from platform use.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Changes to Terms</h2>
                    <div class="space-y-4">
                        <p class="text-gray-600">We reserve the right to modify these terms at any time. Changes will be communicated through:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-1">
                            <li>Email notifications to all members</li>
                            <li>Platform announcements</li>
                            <li>Updated terms posted on this page</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Contact Information</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Legal Department</h3>
                            <p class="text-gray-600">Email: legal@echarayouth.org</p>
                            <p class="text-gray-600">Phone: +234 801 234 5678</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">General Support</h3>
                            <p class="text-gray-600">Email: support@echarayouth.org</p>
                            <p class="text-gray-600">Hours: 24/7 during elections</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
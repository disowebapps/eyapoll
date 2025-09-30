@extends('layouts.guest')

@section('title', 'About Us - Echara Youth Assembly')



@section('main-class', 'pt-16')

@section('content')
<div class="bg-white">
    <x-public.hero-section 
        title="About Echara Youth Assembly" 
        subtitle="Empowering young voices through democratic participation and transparent governance" 
    />

    <!-- Introduction -->
    <section class="py-10 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Who We Are</h2>
                <p class="text-gray-600 leading-relaxed">
                    Echara Youth Assembly is a dynamic Community Youth Association established to serve as the voice of young people 
                    in Echara community. We are a non-partisan organization committed to promoting democratic values, youth empowerment, 
                    and community development through transparent and accountable governance.
                </p>
            </div>
            
            <div class="grid lg:grid-cols-2 gap-8 items-center">
                <div>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        As a registered community-based organization, we bring together young people from diverse backgrounds within 
                        Echara community to participate actively in decision-making processes that affect their lives and future. 
                        Our assembly serves as a bridge between the youth and traditional community structures, ensuring that young 
                        voices are heard and valued in all aspects of community development.
                    </p>
                    <p class="text-gray-600 leading-relaxed">
                        Through innovative programs, democratic processes, and community engagement initiatives, we are building 
                        a generation of informed, engaged, and empowered young leaders who will drive positive change in our community 
                        and beyond.
                    </p>
                </div>
                <div class="bg-blue-50 rounded-xl p-6">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-blue-600 mb-1">500+</div>
                            <div class="text-xs text-gray-600">Active Members</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600 mb-1">50+</div>
                            <div class="text-xs text-gray-600">Community Projects</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-purple-600 mb-1">10+</div>
                            <div class="text-xs text-gray-600">Years of Service</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-orange-600 mb-1">100%</div>
                            <div class="text-xs text-gray-600">Transparency</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission, Vision, Values -->
    <section class="py-8 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-8 mb-12">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        To create an inclusive platform where young people in Echara community can actively participate in democratic processes, 
                        develop leadership skills, and contribute meaningfully to community development through transparent and accountable governance.
                    </p>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-600 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Promote youth participation in governance
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-600 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Foster leadership development programs
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-600 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Ensure transparent decision-making processes
                        </li>
                    </ul>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Our Vision</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        A thriving Echara community where every young person has a voice, opportunities for growth, 
                        and the power to shape their future through active civic engagement and democratic participation.
                    </p>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-600 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Empowered and engaged youth population
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-600 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Sustainable community development
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-600 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Democratic culture and good governance
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Core Values -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Our Core Values</h3>
                <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Integrity</h4>
                        <p class="text-gray-600 text-xs">Upholding honesty, transparency, and ethical conduct in all our actions</p>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Inclusivity</h4>
                        <p class="text-gray-600 text-xs">Embracing diversity and ensuring equal participation for all</p>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Innovation</h4>
                        <p class="text-gray-600 text-xs">Embracing new ideas and creative solutions to challenges</p>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Service</h4>
                        <p class="text-gray-600 text-xs">Dedicated to serving our community with passion and commitment</p>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Accountability</h4>
                        <p class="text-gray-600 text-xs">Taking responsibility and being answerable to our community</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- What We Do -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">What We Do</h2>
                <p class="text-gray-600">Our key focus areas and activities</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-blue-50 rounded-xl p-6 text-center hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Civic Education</h3>
                    <p class="text-gray-600 text-sm">Educating young people about their rights, responsibilities, and democratic participation.</p>
                </div>
                
                <div class="bg-blue-50 rounded-xl p-6 text-center hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Leadership Development</h3>
                    <p class="text-gray-600 text-sm">Training programs and mentorship to develop the next generation of leaders.</p>
                </div>
                
                <div class="bg-blue-50 rounded-xl p-6 text-center hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Community Projects</h3>
                    <p class="text-gray-600 text-sm">Implementing sustainable development projects that improve quality of life.</p>
                </div>
                
                <div class="bg-blue-50 rounded-xl p-6 text-center hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Advocacy</h3>
                    <p class="text-gray-600 text-sm">Representing youth interests and advocating for policies that benefit young people.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Executive Members -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Executive Members</h2>
                <p class="text-gray-600">Meet the dedicated leaders driving our mission forward</p>
            </div>
            
            @php
                $executives = \App\Models\User::where('is_executive', true)
                    ->whereIn('status', ['approved', 'accredited'])
                    ->orderBy('executive_order')
                    ->take(6)
                    ->get();
            @endphp
            
            @if($executives->count() > 0)
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($executives as $executive)
                    <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 text-center hover:shadow-lg transition-all duration-300">
                        <div class="relative mb-6 inline-block">
                            <img src="{{ $executive->profile_image ? asset('storage/' . $executive->profile_image) : asset('storage/default-avatar.png') }}" 
                                 alt="{{ $executive->full_name }}" 
                                 class="w-20 h-20 rounded-full object-cover border-2 border-blue-100">
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $executive->full_name }}</h3>
                        <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold mb-4 inline-block">
                            {{ $executive->current_position }}
                        </div>
                        @if($executive->bio)
                            <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ Str::limit($executive->bio, 100) }}</p>
                        @endif
                        <div class="flex justify-center space-x-3 pt-4 border-t border-gray-100">
                            @if($executive->linkedin_handle)
                                <a href="{{ $executive->linkedin_handle }}" target="_blank" class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            @endif
                            @if($executive->twitter_handle)
                                <a href="https://twitter.com/{{ ltrim($executive->twitter_handle, '@') }}" target="_blank" class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 hover:text-blue-400 hover:bg-blue-50 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-lg">Executive team information will be available soon.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Historical Timeline -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Historical Timeline</h2>
                <p class="text-gray-600">Key milestones in our mission to empower youth democracy</p>
            </div>
            
            <div class="relative max-w-4xl mx-auto">
                <div class="absolute left-1/2 transform -translate-x-px h-full w-1 bg-blue-200"></div>
                
                <div class="space-y-12">
                    <div class="relative flex items-center">
                        <div class="flex-1 pr-12 text-right">
                            <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                                <div class="text-blue-600 font-semibold text-sm mb-2">December 2024</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3">Platform Launch</h3>
                                <p class="text-gray-600 leading-relaxed">Launched the digital voting platform with advanced security features, end-to-end encryption, and user-friendly interface for transparent elections.</p>
                            </div>
                        </div>
                        <div class="relative flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full shadow-lg z-10">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 pl-12"></div>
                    </div>
                    
                    <div class="relative flex items-center">
                        <div class="flex-1 pr-12"></div>
                        <div class="relative flex items-center justify-center w-16 h-16 bg-green-600 rounded-full shadow-lg z-10">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 pl-12">
                            <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                                <div class="text-green-600 font-semibold text-sm mb-2">January 2025</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3">First Digital Elections</h3>
                                <p class="text-gray-600 leading-relaxed">Successfully conducted our inaugural digital elections with full transparency, security verification, and unprecedented voter participation rates.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative flex items-center">
                        <div class="flex-1 pr-12 text-right">
                            <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                                <div class="text-purple-600 font-semibold text-sm mb-2">March 2025</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3">Community Expansion</h3>
                                <p class="text-gray-600 leading-relaxed">Reached significant milestones in member registration, community engagement, and established partnerships with local organizations.</p>
                            </div>
                        </div>
                        <div class="relative flex items-center justify-center w-16 h-16 bg-purple-600 rounded-full shadow-lg z-10">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 pl-12"></div>
                    </div>
                    
                    <div class="relative flex items-center">
                        <div class="flex-1 pr-12"></div>
                        <div class="relative flex items-center justify-center w-16 h-16 bg-orange-600 rounded-full shadow-lg z-10">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 pl-12">
                            <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                                <div class="text-orange-600 font-semibold text-sm mb-2">Future Goals</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3">Continued Growth</h3>
                                <p class="text-gray-600 leading-relaxed">Expanding our impact through innovative programs, enhanced technology, and deeper community engagement initiatives.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-10 bg-gradient-to-r from-blue-600 to-blue-900">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white mb-6">Join Our Mission</h2>
            <p class="text-xl text-blue-100 mb-8">Be part of the movement to strengthen youth democracy and transparent governance.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('voter.register') }}" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-3 rounded-lg font-semibold transition">
                    Become a Member
                </a>
                <a href="{{ route('public.contact') }}" class="border-2 border-white text-white hover:bg-white hover:text-blue-600 px-8 py-3 rounded-lg font-semibold transition">
                    Contact Us
                </a>
            </div>
        </div>
    </section>
</div>
@endsection


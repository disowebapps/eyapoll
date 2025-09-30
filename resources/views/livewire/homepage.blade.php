<div x-data="{ scrollY: 0, activeCard: @entangle('activeCard') }" @scroll.window="scrollY = window.pageYOffset" class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-700 text-white h-[75vh] flex items-center pt-16 md:pt-0" x-data="{ 
        show: false,
        initParticles() {
            particlesJS('particles-js', {
                particles: {
                    number: { value: 80, density: { enable: true, value_area: 800 } },
                    color: { value: '#ffffff' },
                    shape: { type: 'circle' },
                    opacity: { value: 0.5, random: false },
                    size: { value: 3, random: true },
                    line_linked: { enable: true, distance: 150, color: '#ffffff', opacity: 0.4, width: 1 },
                    move: { enable: true, speed: 2, direction: 'none', random: false, straight: false, out_mode: 'out', bounce: false }
                },
                interactivity: {
                    detect_on: 'canvas',
                    events: { onhover: { enable: true, mode: 'repulse' }, onclick: { enable: true, mode: 'push' }, resize: true },
                    modes: { grab: { distance: 400, line_linked: { opacity: 1 } }, bubble: { distance: 400, size: 40, duration: 2, opacity: 8, speed: 3 }, repulse: { distance: 200, duration: 0.4 }, push: { particles_nb: 4 }, remove: { particles_nb: 2 } }
                },
                retina_detect: true
            });
        }
    }" x-init="setTimeout(() => { show = true; initParticles(); }, 200)">
        <div id="particles-js"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6 text-white transform transition-all duration-1000"
                    :class="show ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                    Secure Digital Democracy
                </h1>
                <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto text-white transform transition-all duration-1000 delay-300"
                   :class="show ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                    Transparent, verifiable, and accessible voting for the digital age
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center transform transition-all duration-1000 delay-500"
                     :class="show ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'">
                    <button @click="window.location.href='{{ route('auth.register') }}'" 
                            @mouseenter="$el.style.transform = 'scale(1.05) translateY(-2px)'" 
                            @mouseleave="$el.style.transform = 'scale(1) translateY(0)'"
                            class="bg-white text-blue-600 px-8 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-blue-200">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Register to Vote
                        </span>
                    </button>
                    <button @click="window.location.href='{{ route('public.how-it-works') }}'" 
                            @mouseenter="$el.style.transform = 'scale(1.05) translateY(-2px)'" 
                            @mouseleave="$el.style.transform = 'scale(1) translateY(0)'"
                            class="border-2 border-white text-white px-8 py-4 rounded-xl font-semibold hover:bg-white hover:text-blue-600 transition-all duration-300 backdrop-blur-sm bg-white bg-opacity-10">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Learn More
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach([
                    ['label' => 'Total Votes Cast', 'value' => number_format($stats['total_votes']), 'icon' => '🗳️'],
                    ['label' => 'Active Elections', 'value' => $stats['active_elections'], 'icon' => '📊'],
                    ['label' => 'Verified Voters', 'value' => number_format($stats['verified_voters']), 'icon' => '✅'],
                    ['label' => 'Transparency Score', 'value' => $stats['transparency_score'] . '%', 'icon' => '🔒']
                ] as $index => $stat)
                <div class="text-center p-4 bg-gray-50 rounded-lg transform transition-all duration-700 hover:scale-105">
                    <div class="text-4xl mb-2">{{ $stat['icon'] }}</div>
                    <div class="text-3xl font-bold text-gray-900 mb-1">{{ $stat['value'] }}</div>
                    <div class="text-gray-600 font-medium">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Features Grid --}}
    <section class="bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose Our Platform?</h2>
                <p class="text-xl text-gray-700 max-w-2xl mx-auto font-medium">Built with security, transparency, and accessibility at its core</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach([
                    ['id' => 'security', 'title' => 'Bank-Level Security', 'description' => 'End-to-end encryption with cryptographic verification ensures your vote is secure and private.', 'icon' => '🔐', 'features' => ['256-bit encryption', 'Blockchain verification', 'Zero-knowledge proofs']],
                    ['id' => 'transparency', 'title' => 'Complete Transparency', 'description' => 'Every vote is verifiable while maintaining ballot secrecy. Real-time audit trails for full accountability.', 'icon' => '👁️', 'features' => ['Public audit logs', 'Real-time monitoring', 'Verifiable receipts']],
                    ['id' => 'accessibility', 'title' => 'Universal Access', 'description' => 'Mobile-first design works on any device, optimized for low bandwidth and screen readers.', 'icon' => '📱', 'features' => ['Mobile responsive', 'Screen reader support', 'Low bandwidth mode']],
                    ['id' => 'integrity', 'title' => 'Election Integrity', 'description' => 'One person, one vote enforced through rigorous identity verification and duplicate prevention.', 'icon' => '⚖️', 'features' => ['ID verification', 'Duplicate detection', 'Audit compliance']],
                    ['id' => 'realtime', 'title' => 'Real-time Results', 'description' => 'Live election monitoring with instant result tabulation and transparent counting process.', 'icon' => '⚡', 'features' => ['Live updates', 'Instant tabulation', 'Observer dashboard']],
                    ['id' => 'support', 'title' => '24/7 Support', 'description' => 'Dedicated support team ensures smooth elections with comprehensive voter assistance.', 'icon' => '🤝', 'features' => ['Live chat support', 'Phone assistance', 'Email help desk']]
                ] as $feature)
                <div class="group cursor-pointer" 
                     @click="$wire.selectCard('{{ $feature['id'] }}')" 
                     @mouseenter="$el.style.transform = 'translateY(-8px)'" 
                     @mouseleave="$el.style.transform = 'translateY(0)'">
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-500 h-full border border-gray-100 hover:border-blue-200">
                        <div class="p-8">
                            <div class="text-5xl mb-4 transform group-hover:scale-110 transition-transform duration-300">{{ $feature['icon'] }}</div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $feature['title'] }}</h3>
                            <p class="text-gray-600 mb-4">{{ $feature['description'] }}</p>
                            <div class="overflow-hidden transition-all duration-500" :class="activeCard === '{{ $feature['id'] }}' ? 'max-h-40 opacity-100' : 'max-h-0 opacity-0'">
                                <div class="border-t pt-4 mt-4">
                                    <ul class="space-y-2">
                                        @foreach($feature['features'] as $item)
                                        <li class="flex items-center text-sm text-gray-700">
                                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-3"></span>{{ $item }}
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <button class="mt-4 text-blue-600 font-semibold group-hover:text-blue-700 transition-colors flex items-center gap-2 hover:gap-3">
                                <span x-text="activeCard === '{{ $feature['id'] }}' ? 'Click to collapse' : 'Click to learn more'"></span>
                                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white">
        <div class="max-w-4xl mx-auto text-center px-4">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-white">
                    Ready to Participate in Secure Democracy?
                </h2>
                <p class="text-xl text-white">
                    Join thousands of voters who trust our platform for transparent, secure elections
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button @click="window.location.href='{{ route('auth.register') }}'" 
                            @mouseenter="$el.style.transform = 'scale(1.05) translateY(-2px)'" 
                            @mouseleave="$el.style.transform = 'scale(1) translateY(0)'"
                            class="bg-white text-blue-600 px-8 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Start Voting Today
                        </span>
                    </button>
                    <button @click="window.location.href='{{ route('public.verify-receipt') }}'" 
                            @mouseenter="$el.style.transform = 'scale(1.05) translateY(-2px)'" 
                            @mouseleave="$el.style.transform = 'scale(1) translateY(0)'"
                            class="border-2 border-white text-white px-8 py-4 rounded-xl font-semibold hover:bg-white hover:text-blue-600 transition-all duration-300 backdrop-blur-sm bg-white bg-opacity-10">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Verify Your Vote
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>
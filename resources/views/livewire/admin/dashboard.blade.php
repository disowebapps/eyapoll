<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50" wire:poll.30s>
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-slate-200">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Election Dashboard</h1>
                    <p class="text-slate-600 mt-1">Real-time monitoring and analytics</p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Time Period Selector -->
                    <div class="flex bg-slate-100 rounded-lg p-1">
                        @foreach(['1h' => '1H', '24h' => '24H', '7d' => '7D', '30d' => '30D'] as $period => $label)
                            <button 
                                wire:click="setPeriod('{{ $period }}')"
                                class="px-3 py-1.5 text-sm font-medium rounded-md transition-all {{ $this->selectedPeriod === $period ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                    
                    <!-- Real-time Toggle -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-slate-600">Real-time</span>
                        <button 
                            wire:click="toggleRealTimeUpdates"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $this->realTimeUpdates ? 'bg-blue-600' : 'bg-slate-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $this->realTimeUpdates ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-6 py-6">
        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Elections Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Active Elections</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">{{ $electionMetrics['active'] }}</p>
                        <p class="text-sm text-emerald-600 mt-1">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $electionMetrics['participation_rate'] }}% participation
                            </span>
                        </p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Voters Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Registered Voters</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">{{ number_format($voterMetrics['total_registered']) }}</p>
                        <p class="text-sm text-emerald-600 mt-1">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $voterMetrics['verification_rate'] }}% verified
                            </span>
                        </p>
                    </div>
                    <div class="p-3 bg-emerald-100 rounded-lg">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Security Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Security Status</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">{{ $overviewMetrics['security']['alerts_today'] }}</p>
                        <p class="text-sm mt-1">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $overviewMetrics['security']['threat_level'] === 'low' ? 'bg-emerald-100 text-emerald-800' : 
                                   ($overviewMetrics['security']['threat_level'] === 'medium' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($overviewMetrics['security']['threat_level'] === 'high' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')) }}">
                                {{ ucfirst($overviewMetrics['security']['threat_level']) }} threat
                            </span>
                        </p>
                    </div>
                    <div class="p-3 bg-amber-100 rounded-lg">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Performance Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">System Health</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">{{ $overviewMetrics['performance']['system_uptime'] }}%</p>
                        <p class="text-sm text-emerald-600 mt-1">
                            <span class="inline-flex items-center">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></div>
                                {{ $overviewMetrics['performance']['active_sessions'] }} active sessions
                            </span>
                        </p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Voter Registration Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-slate-900">Voter Registration Trend</h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-sm text-slate-600">New Registrations</span>
                    </div>
                </div>
                <div class="h-64 flex items-center justify-center bg-slate-50 rounded-lg">
                    <div class="text-center">
                        <svg class="w-12 h-12 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p class="text-slate-500">Chart will render here</p>
                    </div>
                </div>
            </div>

            <!-- Election Activity Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-slate-900">Election Activity</h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-emerald-500 rounded-full"></div>
                        <span class="text-sm text-slate-600">Votes Cast</span>
                    </div>
                </div>
                <div class="h-64 flex items-center justify-center bg-slate-50 rounded-lg">
                    <div class="text-center">
                        <svg class="w-12 h-12 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-slate-500">Chart will render here</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Elections -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Recent Elections</h3>
                <div class="space-y-4">
                    @forelse($electionMetrics['recent_activity'] as $election)
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                            <div>
                                <p class="font-medium text-slate-900">{{ Str::limit($election->title, 25) }}</p>
                                <p class="text-sm text-slate-600">{{ $election->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $election->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 
                                   ($election->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800') }}">
                                {{ ucfirst($election->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="text-slate-500">No recent elections</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- System Status -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">System Status</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Database</span>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></div>
                            <span class="text-sm font-medium text-emerald-600">Healthy</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">API Response</span>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></div>
                            <span class="text-sm font-medium text-emerald-600">50ms</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Active Sessions</span>
                        <span class="text-sm font-medium text-slate-900">{{ $overviewMetrics['performance']['active_sessions'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Uptime</span>
                        <span class="text-sm font-medium text-slate-900">{{ $overviewMetrics['performance']['system_uptime'] }}%</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.elections.create') }}" 
                       class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3 group-hover:bg-blue-200">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <span class="font-medium text-slate-900">Create Election</span>
                    </a>
                    
                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center p-3 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors group">
                        <div class="p-2 bg-emerald-100 rounded-lg mr-3 group-hover:bg-emerald-200">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <span class="font-medium text-slate-900">Manage Users</span>
                    </a>
                    
                    <a href="{{ route('admin.analytics.elections') }}" 
                       class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors group">
                        <div class="p-2 bg-purple-100 rounded-lg mr-3 group-hover:bg-purple-200">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <span class="font-medium text-slate-900">View Analytics</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div wire:loading.flex class="fixed inset-0 bg-black bg-opacity-25 items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-slate-700 font-medium">Updating dashboard...</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:load', function () {
    // Auto-refresh functionality
    @if($this->realTimeUpdates)
        setInterval(() => {
            @this.call('$refresh');
        }, 30000);
    @endif
    
    // Smooth transitions
    Livewire.on('periodUpdated', () => {
        // Add loading animation
        document.querySelectorAll('.chart-container').forEach(el => {
            el.classList.add('opacity-50');
        });
        
        setTimeout(() => {
            document.querySelectorAll('.chart-container').forEach(el => {
                el.classList.remove('opacity-50');
            });
        }, 1000);
    });
});
</script>
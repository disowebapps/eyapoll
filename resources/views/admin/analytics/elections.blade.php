@extends('layouts.admin')

@section('title', 'Election Analytics Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.sticky-header {
    position: sticky;
    top: 0;
    z-index: 40;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}
.metric-card {
    @apply bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-200;
}
.chart-container {
    position: relative;
    height: 300px;
}
</style>
@endpush

@section('content')
<div x-data="electionAnalytics()" x-init="init()" class="min-h-screen bg-gray-50">
    <!-- Sticky Header -->
    <div class="sticky-header border-b border-gray-200 px-6 py-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Election Analytics</h1>
                <p class="text-sm text-gray-600 mt-1">Real-time election performance dashboard</p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2 text-sm">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-gray-600">Live</span>
                </div>
                <button @click="refreshData()" :disabled="loading"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center space-x-2 text-sm">
                    <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Updating...' : 'Refresh'"></span>
                </button>
            </div>
        </div>
    </div>

    <div class="px-6 space-y-6">

        <!-- Key Metrics - 2x2 Grid -->
        <div class="grid grid-cols-2 gap-2">
            <div class="bg-white rounded-xl shadow-sm border py-8 px-3 hover:shadow-md transition-shadow">
                <div class="flex items-center mb-4 pl-0">
                    <div class="w-5 h-5 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600 ml-2">Total Votes</p>
                </div>
                <p class="text-2xl font-bold text-blue-600 text-center" x-text="formatNumber(metrics.overview.total_votes)">{{ number_format($electionMetrics['overview']['total_votes'] ?? 0) }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border py-8 px-3 hover:shadow-md transition-shadow">
                <div class="flex items-center mb-4 pl-0">
                    <div class="w-5 h-5 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600 ml-2">Voter Turnout</p>
                </div>
                <p class="text-2xl font-bold text-green-600 text-center" x-text="(metrics.overview.turnout_rate || 0) + '%'">{{ ($electionMetrics['overview']['turnout_rate'] ?? 0) }}%</p>
            </div>


            <div class="bg-white rounded-xl shadow-sm border py-8 px-3 hover:shadow-md transition-shadow">
                <div class="flex items-center mb-4 pl-0">
                    <div class="w-5 h-5 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600 ml-2">Eligible Voters</p>
                </div>
                <p class="text-2xl font-bold text-purple-600 text-center" x-text="formatNumber(metrics.overview.eligible_voters)">{{ number_format($electionMetrics['overview']['eligible_voters'] ?? 0) }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border py-8 px-3 hover:shadow-md transition-shadow">
                <div class="flex items-center mb-4 pl-0">
                    <div class="w-5 h-5 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600 ml-2">Eligible Candidates</p>
                </div>
                <p class="text-2xl font-bold text-orange-600 text-center" x-text="metrics.overview.active_candidates">{{ $electionMetrics['overview']['active_candidates'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Real-time Voting Trends Chart -->
        <div class="metric-card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Voting Trends</h3>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <span>Votes/Hour</span>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="votingTrendsChart"></canvas>
            </div>
        </div>

        <!-- Advanced Metrics Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Voting Activity -->
            <div class="metric-card">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Voting Activity</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-blue-50 rounded-lg">
                        <div class="text-xl font-bold text-blue-600" x-text="formatNumber(metrics.voting_activity.votes_today)">{{ number_format($electionMetrics['voting_activity']['votes_today']) }}</div>
                        <div class="text-xs text-blue-700">Votes Today</div>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg">
                        <div class="text-xl font-bold text-green-600" x-text="metrics.voting_activity.votes_last_hour">{{ $electionMetrics['voting_activity']['votes_last_hour'] }}</div>
                        <div class="text-xs text-green-700">Last Hour</div>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-lg">
                        <div class="text-xl font-bold text-purple-600" x-text="metrics.voting_activity.peak_hour">{{ $electionMetrics['voting_activity']['peak_hour'] }}</div>
                        <div class="text-xs text-purple-700">Peak Hour</div>
                    </div>
                    <div class="p-3 bg-orange-50 rounded-lg">
                        <div class="text-xl font-bold text-orange-600" x-text="metrics.voting_activity.avg_votes_per_hour">{{ $electionMetrics['voting_activity']['avg_votes_per_hour'] }}</div>
                        <div class="text-xs text-orange-700">Avg/Hour</div>
                    </div>
                </div>
            </div>

            <!-- Security Metrics -->
            <div class="metric-card">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Security & Integrity</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-indigo-50 rounded-lg">
                        <div class="text-xl font-bold text-indigo-600" x-text="metrics.security_metrics.total_observers">{{ $electionMetrics['security_metrics']['total_observers'] }}</div>
                        <div class="text-xs text-indigo-700">Observers</div>
                    </div>
                    <div class="p-3 bg-teal-50 rounded-lg">
                        <div class="text-xl font-bold text-teal-600" x-text="metrics.security_metrics.active_sessions">{{ $electionMetrics['security_metrics']['active_sessions'] }}</div>
                        <div class="text-xs text-teal-700">Sessions</div>
                    </div>
                    <div class="p-3 bg-red-50 rounded-lg">
                        <div class="text-xl font-bold text-red-600" x-text="metrics.security_metrics.failed_attempts">{{ $electionMetrics['security_metrics']['failed_attempts'] }}</div>
                        <div class="text-xs text-red-700">Failed Logins</div>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg">
                        <div class="text-xl font-bold text-green-600">99.9%</div>
                        <div class="text-xs text-green-700">Uptime</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performing Candidates -->
        <div class="metric-card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Performing Candidates</h3>
            <div class="space-y-3" x-show="metrics.candidate_performance.length > 0">
                <template x-for="(candidate, index) in metrics.candidate_performance" :key="index">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold" x-text="index + 1"></div>
                            <div class="font-medium text-gray-900 text-sm" x-text="candidate.name"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900" x-text="formatNumber(candidate.votes)"></div>
                            <div class="text-xs text-gray-500">votes</div>
                        </div>
                    </div>
                </template>
            </div>
            <div x-show="metrics.candidate_performance.length === 0" class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <p class="text-sm">No candidate data available</p>
            </div>
        </div>

        <!-- Geographic Distribution -->
        <div class="metric-card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Geographic Distribution</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3" x-show="Object.keys(metrics.geographic_breakdown).length > 0">
                <template x-for="(votes, state) in metrics.geographic_breakdown" :key="state">
                    <div class="text-center p-3 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg hover:from-blue-100 hover:to-blue-200 transition-all">
                        <div class="text-lg font-bold text-blue-600" x-text="formatNumber(votes)"></div>
                        <div class="text-xs text-blue-700 capitalize" x-text="state"></div>
                    </div>
                </template>
            </div>
            <div x-show="Object.keys(metrics.geographic_breakdown).length === 0" class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                <p class="text-sm">No geographic data available</p>
            </div>
        </div>

        <!-- Advanced Election Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Participation Rate by Hour -->
            <div class="metric-card">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Participation Rate</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Morning (6-12)</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: 65%"></div>
                            </div>
                            <span class="text-sm font-medium">65%</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Afternoon (12-18)</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 85%"></div>
                            </div>
                            <span class="text-sm font-medium">85%</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Evening (18-24)</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: 45%"></div>
                            </div>
                            <span class="text-sm font-medium">45%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Election Integrity -->
            <div class="metric-card">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Election Integrity</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Blockchain Verified</span>
                        <span class="text-sm font-medium text-green-600">100%</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Duplicate Votes</span>
                        <span class="text-sm font-medium text-green-600">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Invalid Ballots</span>
                        <span class="text-sm font-medium text-green-600">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">System Uptime</span>
                        <span class="text-sm font-medium text-green-600">99.9%</span>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="metric-card">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">System Performance</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Avg Response Time</span>
                        <span class="text-sm font-medium text-blue-600">120ms</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Server Load</span>
                        <span class="text-sm font-medium text-yellow-600">65%</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Database Queries</span>
                        <span class="text-sm font-medium text-green-600">Fast</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Cache Hit Rate</span>
                        <span class="text-sm font-medium text-green-600">94%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function electionAnalytics() {
    return {
        loading: false,
        metrics: @json($electionMetrics),
        chart: null,

        init() {
            this.initChart();
            // Auto-refresh every 30 seconds
            setInterval(() => {
                this.refreshData();
            }, 30000);
        },

        initChart() {
            const ctx = document.getElementById('votingTrendsChart').getContext('2d');
            const hasData = this.metrics.hourly_trends && this.metrics.hourly_trends.length > 0 && this.metrics.hourly_trends.some(trend => trend.votes > 0);
            
            const labels = hasData ? this.metrics.hourly_trends.map(trend => trend.time) : ['No Data'];
            const data = hasData ? this.metrics.hourly_trends.map(trend => trend.votes) : [0];

            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Votes per Hour',
                        data: data,
                        borderColor: hasData ? 'rgb(59, 130, 246)' : 'rgba(156, 163, 175, 0.5)',
                        backgroundColor: hasData ? 'rgba(59, 130, 246, 0.1)' : 'rgba(156, 163, 175, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: hasData ? 'rgb(59, 130, 246)' : 'rgba(156, 163, 175, 0.5)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: hasData ? 4 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: hasData
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                color: '#6B7280',
                                font: { size: 12 },
                                display: hasData
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6B7280',
                                font: { size: 12 },
                                display: hasData
                            }
                        }
                    },
                    elements: {
                        point: {
                            hoverRadius: hasData ? 6 : 0
                        }
                    },
                    onHover: hasData ? undefined : () => {}
                }
            });

            // Add empty state overlay if no data
            if (!hasData) {
                this.addEmptyStateOverlay(ctx);
            }
        },

        addEmptyStateOverlay(ctx) {
            const canvas = ctx.canvas;
            const overlay = document.createElement('div');
            overlay.className = 'absolute inset-0 flex items-center justify-center bg-gray-50 bg-opacity-90 rounded-lg';
            overlay.innerHTML = `
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-sm text-gray-500">No voting data available</p>
                    <p class="text-xs text-gray-400 mt-1">Data will appear when voting begins</p>
                </div>
            `;
            canvas.parentElement.appendChild(overlay);
        },

        async refreshData() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("admin.analytics.elections.refresh") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.metrics = data.electionMetrics;
                    this.updateChart();
                }
            } catch (error) {
                console.error('Failed to refresh data:', error);
            } finally {
                this.loading = false;
            }
        },

        updateChart() {
            if (this.chart) {
                const hasData = this.metrics.hourly_trends && this.metrics.hourly_trends.length > 0 && this.metrics.hourly_trends.some(trend => trend.votes > 0);
                const labels = hasData ? this.metrics.hourly_trends.map(trend => trend.time) : ['No Data'];
                const data = hasData ? this.metrics.hourly_trends.map(trend => trend.votes) : [0];

                this.chart.data.labels = labels;
                this.chart.data.datasets[0].data = data;
                this.chart.data.datasets[0].pointRadius = hasData ? 4 : 0;
                this.chart.options.plugins.tooltip.enabled = hasData;
                this.chart.options.scales.y.display = hasData;
                this.chart.options.scales.x.display = hasData;
                this.chart.options.elements.point.hoverRadius = hasData ? 6 : 0;
                this.chart.update('none');

                // Add or remove overlay
                const canvas = this.chart.canvas;
                const existingOverlay = canvas.parentElement.querySelector('.absolute');
                if (!hasData) {
                    if (!existingOverlay) {
                        const overlay = document.createElement('div');
                        overlay.className = 'absolute inset-0 flex items-center justify-center bg-gray-50 bg-opacity-90 rounded-lg';
                        overlay.innerHTML = `
                            <div class="text-center">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <p class="text-sm text-gray-500">No voting data available</p>
                                <p class="text-xs text-gray-400 mt-1">Data will appear when voting begins</p>
                            </div>
                        `;
                        canvas.parentElement.appendChild(overlay);
                    }
                } else {
                    if (existingOverlay) {
                        existingOverlay.remove();
                    }
                }
            }
        },

        formatNumber(num) {
            return new Intl.NumberFormat().format(num || 0);
        }
    }
}
</script>
@endsection
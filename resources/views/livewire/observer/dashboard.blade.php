<div class="space-y-6" 
     x-data="observerDashboard()" 
     x-init="init()" 
     @stats-refreshed.window="showNotification('Dashboard updated', 'success')"
     wire:poll.30s="refreshData">

<!-- Welcome Section -->
<div class="bg-gradient-to-r from-green-600 to-teal-700 rounded-xl shadow-sm p-6 text-white">
    <h1 class="text-2xl font-bold mb-2">Welcome back, {{ auth('observer')->user()->first_name }}!</h1>
    <p class="text-green-100">Monitor elections and track system activity in real-time</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
  <!-- Elections -->
  <div x-data="{ loading: false }"
       @click="loading = true; setTimeout(() => window.location.href = '{{ route('observer.elections') }}', 150)"
       :class="loading ? 'scale-95 opacity-75' : 'hover:scale-105'"
       class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 hover:shadow-lg transition-all duration-200 cursor-pointer hover:border-purple-200 group flex flex-col items-center text-center transform">
    <div class="flex items-center justify-center gap-3 sm:gap-4">
      <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-sm group-hover:from-purple-600 group-hover:to-purple-700 transition-all">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
      </div>

      <h3 class="text-xl sm:text-2xl font-bold leading-none text-gray-900 tabular-nums group-hover:text-purple-700 transition-colors">
        {{ number_format($stats['total_elections']) }}
      </h3>
    </div>

    <p class="mt-2 text-xs sm:text-sm font-medium text-gray-600 group-hover:text-purple-600 transition-colors">
      Total Elections
    </p>
  </div>

  <!-- Active Elections -->
  <div x-data="{ loading: false }"
       @click="loading = true; setTimeout(() => window.location.href = '{{ route('observer.elections', ['status' => 'active']) }}', 150)"
       :class="loading ? 'scale-95 opacity-75' : 'hover:scale-105'"
       class="bg-white overflow-hidden shadow rounded-lg cursor-pointer hover:shadow-lg transition-all duration-200 hover:border-green-200 border border-gray-100 group flex flex-col items-center text-center transform">
    <div class="p-4 sm:p-6 w-full">
      <div class="flex items-center justify-center gap-3 sm:gap-4">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-500 rounded-xl flex items-center justify-center shadow-sm group-hover:bg-green-600 transition-colors">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>

        <h3 class="text-base sm:text-lg font-semibold leading-none text-gray-900 tabular-nums group-hover:text-green-700 transition-colors">
          {{ number_format($stats['active_elections']) }}
        </h3>
      </div>

      <p class="mt-2 text-xs sm:text-sm text-gray-500 group-hover:text-green-600 transition-colors">
        Active Elections
      </p>
    </div>
  </div>

  <!-- Total Votes -->
  <div x-data="{ loading: false }"
       @click="loading = true; setTimeout(() => window.location.href = '{{ route('observer.elections') }}', 150)"
       :class="loading ? 'scale-95 opacity-75' : 'hover:scale-105'"
       class="bg-white overflow-hidden shadow rounded-lg cursor-pointer hover:shadow-lg transition-all duration-200 hover:border-blue-200 border border-gray-100 group flex flex-col items-center text-center transform">
    <div class="p-4 sm:p-6 w-full">
      <div class="flex items-center justify-center gap-3 sm:gap-4">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-sm group-hover:bg-blue-600 transition-colors">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>

        <h3 class="text-base sm:text-lg font-semibold leading-none text-gray-900 tabular-nums group-hover:text-blue-700 transition-colors">
          {{ number_format($stats['total_votes']) }}
        </h3>
      </div>

      <p class="mt-2 text-xs sm:text-sm text-gray-500 group-hover:text-blue-600 transition-colors">
        Total Votes Cast
      </p>
    </div>
  </div>

  <!-- Recent Activity -->
  <div x-data="{ loading: false }"
       @click="loading = true; setTimeout(() => window.location.href = '{{ route('observer.audit-logs') }}', 150)"
       :class="loading ? 'scale-95 opacity-75' : 'hover:scale-105'"
       class="bg-white overflow-hidden shadow rounded-lg cursor-pointer hover:shadow-lg transition-all duration-200 hover:border-indigo-200 border border-gray-100 group flex flex-col items-center text-center transform">
    <div class="p-4 sm:p-6 w-full">
      <div class="flex items-center justify-center gap-3 sm:gap-4">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-indigo-500 rounded-xl flex items-center justify-center shadow-sm group-hover:bg-indigo-600 transition-colors">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>

        <h3 class="text-base sm:text-lg font-semibold leading-none text-gray-900 tabular-nums group-hover:text-indigo-700 transition-colors">
          {{ number_format($stats['recent_logs']) }}
        </h3>
      </div>

      <p class="mt-2 text-xs sm:text-sm text-gray-500 group-hover:text-indigo-600 transition-colors">
        Recent Logs (24h)
      </p>
    </div>
  </div>
</div>

    <!-- Recent Activity -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                    <button @click="window.location.href = '{{ route('observer.audit-logs') }}'" class="flex items-center px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors whitespace-nowrap ml-auto">
                        View All Logs
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center">
                    <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 border border-gray-200">
                        <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                        </svg>
                        <select wire:model.live="selectedSeverity" wire:change="filterBySeverity($event.target.value)" class="text-sm bg-transparent border-0 focus:ring-0 text-gray-700 font-medium">
                            <option value="all">All Severity</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4">
            @if(count($recentActivity) > 0)
            <div class="divide-y divide-gray-100">
                @foreach($recentActivity as $activity)
                <div class="group relative py-4 hover:bg-gradient-to-r hover:from-gray-50 hover:to-white transition-all duration-200"
                     x-data="{ expanded: false }">
                    
                    <!-- Severity Indicator -->
                    <div class="absolute left-0 top-0 bottom-0 w-1 rounded-r"
                         :class="{
                             'bg-gradient-to-b from-red-500 to-red-600': '{{ $activity['severity'] ?? 'low' }}' === 'high',
                             'bg-gradient-to-b from-amber-500 to-orange-500': '{{ $activity['severity'] ?? 'low' }}' === 'medium',
                             'bg-gradient-to-b from-emerald-500 to-green-600': '{{ $activity['severity'] ?? 'low' }}' === 'low'
                         }"></div>
                    
                    <div class="flex items-start space-x-4 pl-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-1">
                            <div class="w-5 h-5 rounded-xl flex items-center justify-center shadow-sm border"
                                 :class="{
                                     'bg-red-50 border-red-200': '{{ $activity['severity'] ?? 'low' }}' === 'high',
                                     'bg-amber-50 border-amber-200': '{{ $activity['severity'] ?? 'low' }}' === 'medium',
                                     'bg-emerald-50 border-emerald-200': '{{ $activity['severity'] ?? 'low' }}' === 'low'
                                 }">
                                @if(str_contains($activity['action'], 'Approved'))
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @elseif(str_contains($activity['action'], 'Rejected'))
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @elseif(str_contains($activity['action'], 'Created'))
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <!-- Header -->
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="text-sm font-semibold text-gray-900">
                                            {{ $activity['action'] }}
                                        </h4>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-red-100 text-red-800': '{{ $activity['severity'] ?? 'low' }}' === 'high',
                                                  'bg-amber-100 text-amber-800': '{{ $activity['severity'] ?? 'low' }}' === 'medium',
                                                  'bg-emerald-100 text-emerald-800': '{{ $activity['severity'] ?? 'low' }}' === 'low'
                                              }">
                                            {{ ucfirst($activity['severity'] ?? 'low') }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $activity['description'] }}
                                    </p>
                                </div>
                                
                                <!-- Expand Button -->
                                <button @click="expanded = !expanded" 
                                        class="ml-4 p-1 rounded-md hover:bg-gray-100 transition-colors">
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" 
                                         :class="{ 'rotate-180': expanded }" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Metadata -->
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mt-3 text-xs text-gray-500">
                                <div class="flex items-center space-x-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="font-medium">{{ $activity['user_name'] }}</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <time datetime="{{ $activity['created_at']->toISOString() }}">
                                        {{ $activity['created_at']->diffForHumans() }}
                                    </time>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    <span class="px-2 py-0.5 bg-blue-100 rounded-md font-medium">
                                        {{ $activity['entity_type'] }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Expanded Details -->
                            <div x-show="expanded" x-collapse class="mt-4 p-3 sm:p-4 bg-gray-50 rounded-lg border">
                                @if(isset($activity['metadata']) && !empty($activity['metadata']))
                                    <h5 class="text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Details</h5>
                                    <dl class="grid grid-cols-1 lg:grid-cols-2 gap-2 text-xs">
                                        @foreach($activity['metadata'] as $key => $value)
                                            <div class="flex flex-col sm:flex-row sm:justify-between py-1">
                                                <dt class="font-medium text-gray-600 mb-1 sm:mb-0">{{ ucwords(str_replace('_', ' ', $key)) }}:</dt>
                                                <dd class="text-gray-900 font-semibold break-words">{{ $value }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                @else
                                    <p class="text-xs text-gray-500 italic">No additional details available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-6 sm:py-8 px-4 sm:px-6">
                <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="mt-2 text-sm sm:text-base font-medium text-gray-900">No recent activity</h3>
                <p class="mt-1 text-sm text-gray-500">System activity logs will appear here.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Elections -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Elections</h2>
                    <button @click="window.location.href = '{{ route('observer.elections') }}'" class="flex items-center px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors whitespace-nowrap ml-auto">
                        View All
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center">
                    <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 border border-gray-200">
                        <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                        </svg>
                        <select wire:model.live="electionStatusFilter" wire:change="filterElectionsByStatus($event.target.value)" class="text-sm bg-transparent border-0 focus:ring-0 text-gray-700 font-medium">
                            <option value="all">All Status</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="active">Active</option>
                            <option value="ended">Ended</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-4 sm:px-6 py-4">
            @if(count($recentElections) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($recentElections as $election)
                <div class="border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="p-4">
                        <!-- Election info -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-base font-medium text-gray-900 truncate">{{ $election['title'] }}</h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $election['status_color'] }}-bg text-{{ $election['status_color'] }}-800 flex-shrink-0">
                                        {{ $election['status'] }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $election['positions_count'] }} positions • {{ $election['votes_count'] }} votes</p>
                                <p class="text-xs text-gray-500 mt-1">Created {{ $election['created_at']->diffForHumans() }}</p>
                            </div>
                        </div>

                        <!-- Action button -->
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            @if(in_array($election['status'], ['Active', 'Ended']))
                            <button
                                wire:click="viewElectionResults({{ $election['id'] }})"
                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                View Results
                            </button>
                            @else
                            <button
                                disabled
                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-400 bg-gray-200 cursor-not-allowed"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                View Results
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No elections found</h3>
                <p class="mt-1 text-sm text-gray-500">Election data will appear here once elections are created.</p>
            </div>
            @endif
        </div>
    </div>



    <!-- System Health -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">System Health</h2>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($systemHealth['database'])
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Database</p>
                        <p class="text-xs text-gray-500">{{ $systemHealth['database'] ? 'Connected' : 'Disconnected' }}</p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($systemHealth['cache'])
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Cache</p>
                        <p class="text-xs text-gray-500">{{ $systemHealth['cache'] ? 'Operational' : 'Failed' }}</p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($systemHealth['storage'])
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Storage</p>
                        <p class="text-xs text-gray-500">{{ $systemHealth['storage'] ? 'Writable' : 'Read-only' }}</p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($systemHealth['overall'])
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Overall</p>
                        <p class="text-xs text-gray-500">{{ $systemHealth['overall'] ? 'Healthy' : 'Issues Detected' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
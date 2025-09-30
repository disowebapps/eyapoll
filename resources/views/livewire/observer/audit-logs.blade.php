<div x-data="auditLogsManager()" 
     x-init="init()" 
     @logs-refreshed.window="showNotification('Logs refreshed successfully', 'success')"
     @filters-cleared.window="showNotification('Filters cleared', 'info')"
     wire:poll.visible.30s="refreshLogs"
     class="space-y-6"
     x-intersect.once="$wire.loadMore()">
     
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
                    <p class="text-gray-600 mt-1">Real-time system activity monitoring</p>
                </div>

            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">

                
                <div class="flex gap-3">
                    <!-- Export Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export
                            <svg class="w-4 h-4 ml-2 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                            <div class="py-1">
                                <button onclick="exportLogs('csv')"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Export as CSV
                                </button>
                                <button onclick="exportLogs('excel')"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Export as Excel
                                </button>
                                <button onclick="exportLogs('json')"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    Export as JSON
                                </button>
                            </div>
                        </div>
                    </div>

                    <button wire:click="refreshLogs"
                            x-data="{ loading: false }"
                            @click="loading = true; setTimeout(() => loading = false, 1000)"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all duration-200 flex items-center">
                        <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <svg x-show="loading" x-cloak class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'Refreshing...' : 'Refresh'" x-cloak></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="space-y-3 mb-6">
        <!-- Search and Clear -->
        <div class="flex gap-3">
            <div class="relative flex-1">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Search logs..." 
                       class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <button wire:click="clearFilters" class="flex items-center px-3 py-2 bg-gray-50 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span class="text-sm font-medium">Clear</span>
            </button>
        </div>
        
        <!-- Event Type and Severity -->
        <div class="flex gap-3">
            <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 border border-gray-200 flex-1">
                <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <select wire:model.live="eventType" class="text-sm bg-transparent border-0 focus:ring-0 text-gray-700 font-medium w-full">
                    <option value="">All Events</option>
                    @foreach($this->eventTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 border border-gray-200 flex-1">
                <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <select wire:model.live="severity" class="text-sm bg-transparent border-0 focus:ring-0 text-gray-700 font-medium w-full">
                    <option value="">All Severity</option>
                    @foreach($this->severityLevels as $level)
                        <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <!-- Date Range -->
        <div class="flex gap-3">
            <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 border border-gray-200 flex-1">
                <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <input type="date" wire:model.live="dateFrom" class="text-sm bg-transparent border-0 focus:ring-0 text-gray-700 font-medium w-full">
            </div>
            
            <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 border border-gray-200 flex-1">
                <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <input type="date" wire:model.live="dateTo" class="text-sm bg-transparent border-0 focus:ring-0 text-gray-700 font-medium w-full">
            </div>
        </div>
    </div>

    <!-- Audit Logs -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Audit Logs</h2>
                <div class="flex items-center space-x-3">
                    <div class="text-sm text-gray-500">
                        {{ $this->auditLogs->total() }} total logs
                    </div>
                    <div wire:loading class="flex items-center text-sm text-blue-600">
                        <svg class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading...
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4">
            @if(count($this->auditLogs) > 0)
            <div class="divide-y divide-gray-100">
                @foreach($this->auditLogs as $log)
                <div class="group relative py-4 hover:bg-gradient-to-r hover:from-gray-50 hover:to-white transition-all duration-200"
                     x-data="{ expanded: false }">
                    
                    @php
                        $severity = $this->getSeverity($log->action);
                    @endphp
                    
                    <!-- Severity Indicator -->
                    <div class="absolute left-0 top-0 bottom-0 w-1 rounded-r"
                         :class="{
                             'bg-gradient-to-b from-red-500 to-red-600': '{{ $severity }}' === 'high',
                             'bg-gradient-to-b from-amber-500 to-orange-500': '{{ $severity }}' === 'medium',
                             'bg-gradient-to-b from-emerald-500 to-green-600': '{{ $severity }}' === 'low',
                             'bg-gradient-to-b from-purple-500 to-purple-600': '{{ $severity }}' === 'critical'
                         }"></div>
                    
                    <div class="flex items-start space-x-4 pl-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-1">
                            <div class="w-5 h-5 rounded-xl flex items-center justify-center shadow-sm border"
                                 :class="{
                                     'bg-red-50 border-red-200': '{{ $severity }}' === 'high',
                                     'bg-amber-50 border-amber-200': '{{ $severity }}' === 'medium',
                                     'bg-emerald-50 border-emerald-200': '{{ $severity }}' === 'low',
                                     'bg-purple-50 border-purple-200': '{{ $severity }}' === 'critical'
                                 }">
                                @if(str_contains($log->action, 'approved'))
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @elseif(str_contains($log->action, 'rejected'))
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @elseif(str_contains($log->action, 'created'))
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
                                            {{ $log->getActionLabel() }}
                                        </h4>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-red-100 text-red-800': '{{ $severity }}' === 'high',
                                                  'bg-amber-100 text-amber-800': '{{ $severity }}' === 'medium',
                                                  'bg-emerald-100 text-emerald-800': '{{ $severity }}' === 'low',
                                                  'bg-purple-100 text-purple-800': '{{ $severity }}' === 'critical'
                                              }">
                                            {{ ucfirst($severity) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $log->getDescription() }}
                                    </p>
                                </div>
                                
                                <!-- Expand Button -->
                                @if($log->old_values || $log->new_values)
                                <button @click="expanded = !expanded" 
                                        class="ml-4 p-1 rounded-md hover:bg-gray-100 transition-colors">
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" 
                                         :class="{ 'rotate-180': expanded }" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                @endif
                            </div>
                            
                            <!-- Metadata -->
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mt-3 text-xs text-gray-500">
                                <div class="flex items-center space-x-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="font-medium">{{ $log->getUserName() }}</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <time datetime="{{ $log->created_at->toISOString() }}">
                                        {{ $log->created_at->diffForHumans() }}
                                    </time>
                                </div>
                                @if($log->entity_type)
                                <div class="flex items-center space-x-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    <span class="px-2 py-0.5 bg-blue-100 rounded-md font-medium">
                                        {{ class_basename($log->entity_type) }}
                                    </span>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Expanded Details -->
                            @if($log->old_values || $log->new_values)
                            <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 max-h-96" x-transition:leave-end="opacity-0 max-h-0" x-cloak class="mt-4 p-3 sm:p-4 bg-gray-50 rounded-lg border overflow-hidden">
                                <h5 class="text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Change Details</h5>
                                <dl class="grid grid-cols-1 lg:grid-cols-2 gap-2 text-xs">
                                    @php
                                        $changes = $log->getChangeSummary();
                                    @endphp
                                    @foreach($changes as $field => $change)
                                        <div class="flex flex-col sm:flex-row sm:justify-between py-1 border-b border-gray-200 last:border-b-0">
                                            <dt class="font-medium text-gray-600 mb-1 sm:mb-0">{{ ucwords(str_replace('_', ' ', $field)) }}:</dt>
                                            <dd class="text-gray-900 font-semibold break-words">
                                                @if($change['old'])
                                                    <span class="text-red-600">{{ $change['old'] }}</span> → 
                                                @endif
                                                <span class="text-green-600">{{ $change['new'] }}</span>
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No audit logs found</h3>
                <p class="mt-1 text-sm text-gray-500">No logs match your current filter criteria.</p>
            </div>
            @endif
        </div>
        
        @if($this->auditLogs->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="text-sm text-gray-500 text-center sm:text-left">
                        Showing {{ $this->auditLogs->firstItem() }} to {{ $this->auditLogs->lastItem() }} of {{ $this->auditLogs->total() }} results
                    </div>
                    <div class="flex items-center justify-center space-x-2">
                        @if($this->auditLogs->onFirstPage())
                            <span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed text-sm">Previous</span>
                        @else
                            <button wire:click="previousPage" class="px-3 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">Previous</button>
                        @endif
                        
                        <div class="hidden sm:flex items-center space-x-1">
                            @foreach($this->auditLogs->getUrlRange(max(1, $this->auditLogs->currentPage() - 2), min($this->auditLogs->lastPage(), $this->auditLogs->currentPage() + 2)) as $page => $url)
                                @if($page == $this->auditLogs->currentPage())
                                    <span class="px-3 py-2 text-white bg-indigo-600 rounded-lg font-medium text-sm">{{ $page }}</span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" class="px-3 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">{{ $page }}</button>
                                @endif
                            @endforeach
                        </div>
                        
                        <div class="sm:hidden px-3 py-2 text-sm text-gray-700">
                            Page {{ $this->auditLogs->currentPage() }} of {{ $this->auditLogs->lastPage() }}
                        </div>
                        
                        @if($this->auditLogs->hasMorePages())
                            <button wire:click="nextPage" class="px-3 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">Next</button>
                        @else
                            <span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed text-sm">Next</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
    

</div>

<script>
// Global export function
function exportLogs(format) {
    // Build query parameters from current filters
    const params = new URLSearchParams();

    // Add current filter values
    if (window.livewire && window.livewire.find) {
        const component = window.livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
        if (component) {
            if (component.search) params.append('search', component.search);
            if (component.eventType) params.append('action', component.eventType);
            if (component.severity) {
                // Convert severity to actions
                const severityActions = getActionsBySeverity(component.severity);
                if (severityActions.length > 0) {
                    params.append('actions', severityActions.join(','));
                }
            }
            if (component.dateFrom) params.append('date_from', component.dateFrom);
            if (component.dateTo) params.append('date_to', component.dateTo);
        }
    }

    params.append('format', format);

    // Create download link
    const url = `/observer/audit-logs/export?${params.toString()}`;
    const link = document.createElement('a');
    link.href = url;
    link.download = `audit-logs.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Show success notification
    showExportNotification(format);
}

function getActionsBySeverity(severity) {
    const severityMap = {
        'high': ['candidate_rejected', 'user_rejected', 'election_cancelled'],
        'medium': ['candidate_approved', 'user_approved', 'election_created'],
        'low': ['user_login', 'user_logout', 'vote_cast'],
        'critical': ['election_started', 'election_ended', 'system_error']
    };
    return severityMap[severity] || [];
}

function showExportNotification(format) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 transition-all duration-300 shadow-lg transform translate-x-0 bg-green-500';
    notification.innerHTML = `
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>Exporting audit logs as ${format.toUpperCase()}...</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 hover:bg-black hover:bg-opacity-20 rounded p-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    `;
    document.body.appendChild(notification);

    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }
    }, 3000);
}

function auditLogsManager() {
    return {
        showFilters: true,

        hasActiveFilters() {
            return this.$wire.search || this.$wire.eventType || this.$wire.severity || this.$wire.dateFrom || this.$wire.dateTo;
        },

        getActiveFilterCount() {
            let count = 0;
            if (this.$wire.search) count++;
            if (this.$wire.eventType) count++;
            if (this.$wire.severity) count++;
            if (this.$wire.dateFrom) count++;
            if (this.$wire.dateTo) count++;
            return count;
        },

        init() {
            // Auto-hide filters on mobile
            if (window.innerWidth < 768) {
                this.showFilters = false;
            }

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey || e.metaKey) {
                    if (e.key === 'k') {
                        e.preventDefault();
                        document.querySelector('input[wire\\:model\\.live\\.debounce\\.300ms="search"]')?.focus();
                    }
                    if (e.key === 'r') {
                        e.preventDefault();
                        this.$wire.refreshLogs();
                    }
                }
            });
        },

        showNotification(message, type = 'success') {
            // Use Alpine's $dispatch for better integration
            this.$dispatch('notify', { message, type });

            // Fallback notification system
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 transition-all duration-300 shadow-lg transform translate-x-0 ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                type === 'info' ? 'bg-blue-500' : 'bg-gray-500'
            }`;
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 hover:bg-black hover:bg-opacity-20 rounded p-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            `;
            document.body.appendChild(notification);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }
    }
}
</script>
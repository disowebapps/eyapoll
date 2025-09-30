<div x-data="alertPanel()" x-init="init()" class="relative">
    <!-- Alert Bell Icon -->
    <button @click="togglePanel()" class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"></span>
    </button>

    <!-- Alert Panel -->
    <div x-show="showPanel" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" @click.away="showPanel = false" class="absolute left-1/2 transform -translate-x-1/2 sm:left-auto sm:right-0 sm:transform-none mt-2 w-80 sm:w-96 max-w-[calc(100vw-2rem)] bg-white rounded-lg shadow-lg border z-50">
        
        <!-- Header -->
        <div class="p-3 sm:p-4 border-b flex justify-between items-center">
            <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Notifications</h3>
            <button @click="markAllRead()" x-show="unreadCount > 0" class="text-xs sm:text-sm text-blue-600 hover:text-blue-800 whitespace-nowrap">Mark all read</button>
        </div>

        <!-- Alerts List -->
        <div class="max-h-80 sm:max-h-96 overflow-y-auto">
            <template x-for="alert in alerts" :key="alert.id">
                <div @click="markAsRead(alert.id)" :class="{'bg-blue-50': !alert.is_read}" class="p-3 sm:p-4 border-b hover:bg-gray-50 cursor-pointer">
                    <div class="flex items-start space-x-2 sm:space-x-3">
                        <div :class="getAlertColor(alert.type)" class="w-2 h-2 rounded-full mt-1.5 sm:mt-2 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-900 break-words" x-text="alert.title"></p>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1 break-words leading-relaxed" x-text="alert.message"></p>
                            <p class="text-xs text-gray-400 mt-1.5 sm:mt-2" x-text="formatTime(alert.created_at)"></p>
                        </div>
                    </div>
                </div>
            </template>
            
            <div x-show="alerts.length === 0" class="p-6 sm:p-8 text-center text-gray-500">
                <svg class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-3 sm:mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-sm sm:text-base">No notifications</p>
            </div>
        </div>
    </div>
</div>

<script>
function alertPanel() {
    return {
        showPanel: false,
        alerts: [],
        unreadCount: 0,
        
        init() {
            this.fetchAlerts();
            // Poll for new alerts every 30 seconds
            setInterval(() => this.fetchAlerts(), 30000);
        },
        
        async fetchAlerts() {
            try {
                const response = await fetch('/admin/alerts');
                const data = await response.json();
                this.alerts = data.alerts;
                this.unreadCount = data.unread_count;
            } catch (error) {
                console.error('Failed to fetch alerts:', error);
            }
        },
        
        togglePanel() {
            this.showPanel = !this.showPanel;
        },
        
        async markAsRead(alertId) {
            try {
                await fetch(`/admin/alerts/${alertId}/read`, { 
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const alert = this.alerts.find(a => a.id === alertId);
                if (alert && !alert.is_read) {
                    alert.is_read = true;
                    this.unreadCount--;
                }
            } catch (error) {
                console.error('Failed to mark alert as read:', error);
            }
        },
        
        async markAllRead() {
            try {
                await fetch('/admin/alerts/mark-all-read', { 
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                this.alerts.forEach(alert => alert.is_read = true);
                this.unreadCount = 0;
            } catch (error) {
                console.error('Failed to mark all alerts as read:', error);
            }
        },
        
        getAlertColor(type) {
            const colors = {
                'kyc_submission': 'bg-blue-500',
                'candidate_application': 'bg-green-500',
                'security_alert': 'bg-red-500',
                'electoral_integrity': 'bg-orange-500',
                'observer_alert': 'bg-purple-500',
                'system_notification': 'bg-gray-500'
            };
            return colors[type] || 'bg-gray-500';
        },
        
        formatTime(timestamp) {
            return new Date(timestamp).toLocaleString();
        }
    }
}
</script>
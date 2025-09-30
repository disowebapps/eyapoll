function observerDashboard() {
    return {
        refreshing: false,
        notification: {
            show: false,
            message: '',
            type: 'success'
        },
        
        init() {
            // Real-time notifications
            Echo.channel('audit-logs')
                .listen('AuditLogCreated', (e) => {
                    this.showNotification(`New ${e.severity} activity: ${e.action}`, 'info');
                });
        },
        
        showNotification(message, type = 'success') {
            this.notification = { show: true, message, type };
            setTimeout(() => {
                this.notification.show = false;
            }, 4000);
        },
        
        timeAgo(date) {
            const now = new Date();
            const past = new Date(date);
            const diffInSeconds = Math.floor((now - past) / 1000);
            
            if (diffInSeconds < 60) return `${diffInSeconds}s ago`;
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
            return `${Math.floor(diffInSeconds / 86400)}d ago`;
        }
    }
}
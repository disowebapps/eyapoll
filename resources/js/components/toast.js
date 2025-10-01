// Enhanced toast notification system with performance optimizations
class ToastManager {
    constructor() {
        this.container = null;
        this.toasts = new Set();
        this.maxToasts = 5;
        this.init();
    }
    
    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'fixed top-4 right-4 z-50 space-y-2';
            this.container.setAttribute('aria-live', 'polite');
            this.container.setAttribute('aria-label', 'Notifications');
            document.body.appendChild(this.container);
        }
    }
    
    create(message, type = 'info', duration = 5000) {
        // Limit number of toasts
        if (this.toasts.size >= this.maxToasts) {
            const oldestToast = this.toasts.values().next().value;
            this.remove(oldestToast);
        }
        
        const toast = document.createElement('div');
        const toastId = `toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
        
        toast.id = toastId;
        toast.className = `transform translate-x-full opacity-0 transition-all duration-300 ease-out p-4 rounded-xl shadow-lg max-w-sm ${this.getToastClasses(type)}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-atomic', 'true');
        
        // Create toast content
        const content = document.createElement('div');
        content.className = 'flex items-center justify-between';
        
        const messageEl = document.createElement('span');
        messageEl.textContent = message;
        messageEl.className = 'flex-1 text-sm font-medium';
        
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.className = 'ml-3 text-lg font-bold opacity-70 hover:opacity-100 transition-opacity';
        closeBtn.setAttribute('aria-label', 'Close notification');
        closeBtn.onclick = () => this.remove(toast);
        
        content.appendChild(messageEl);
        content.appendChild(closeBtn);
        toast.appendChild(content);
        
        this.container.appendChild(toast);
        this.toasts.add(toast);
        
        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        });
        
        // Auto remove
        if (duration > 0) {
            setTimeout(() => this.remove(toast), duration);
        }
        
        return toast;
    }
    
    remove(toast) {
        if (!toast || !this.toasts.has(toast)) return;
        
        toast.classList.add('translate-x-full', 'opacity-0');
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            this.toasts.delete(toast);
        }, 300);
    }
    
    getToastClasses(type) {
        const classes = {
            success: 'bg-success-500 text-white border border-success-600',
            error: 'bg-danger-500 text-white border border-danger-600',
            warning: 'bg-warning-500 text-white border border-warning-600',
            info: 'bg-primary-500 text-white border border-primary-600'
        };
        return classes[type] || classes.info;
    }
    
    clear() {
        this.toasts.forEach(toast => this.remove(toast));
    }
}

// Initialize toast manager
const toastManager = new ToastManager();

// Export functions
export const createToast = (message, type, duration) => toastManager.create(message, type, duration);
export const clearToasts = () => toastManager.clear();

// Make available globally
window.toast = createToast;
window.clearToasts = clearToasts;
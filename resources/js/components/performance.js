// Performance monitoring and optimization utilities
export class PerformanceMonitor {
    constructor() {
        this.metrics = new Map();
        this.observers = new Map();
        this.init();
    }
    
    init() {
        // Monitor Core Web Vitals
        this.observeCLS();
        this.observeFID();
        this.observeLCP();
        
        // Monitor resource loading
        this.observeResourceTiming();
        
        // Monitor navigation timing
        this.observeNavigationTiming();
    }
    
    // Cumulative Layout Shift
    observeCLS() {
        if ('LayoutShiftObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                let clsValue = 0;
                for (const entry of list.getEntries()) {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                    }
                }
                this.metrics.set('CLS', clsValue);
            });
            observer.observe({ type: 'layout-shift', buffered: true });
            this.observers.set('CLS', observer);
        }
    }
    
    // First Input Delay
    observeFID() {
        if ('PerformanceEventTiming' in window) {
            const observer = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (entry.name === 'first-input') {
                        const fid = entry.processingStart - entry.startTime;
                        this.metrics.set('FID', fid);
                        observer.disconnect();
                        break;
                    }
                }
            });
            observer.observe({ type: 'first-input', buffered: true });
            this.observers.set('FID', observer);
        }
    }
    
    // Largest Contentful Paint
    observeLCP() {
        if ('LargestContentfulPaint' in window) {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1];
                this.metrics.set('LCP', lastEntry.startTime);
            });
            observer.observe({ type: 'largest-contentful-paint', buffered: true });
            this.observers.set('LCP', observer);
        }
    }
    
    // Resource timing
    observeResourceTiming() {
        if ('PerformanceResourceTiming' in window) {
            const observer = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (entry.transferSize > 0) {
                        const resourceMetrics = {
                            name: entry.name,
                            duration: entry.duration,
                            transferSize: entry.transferSize,
                            encodedBodySize: entry.encodedBodySize,
                            decodedBodySize: entry.decodedBodySize
                        };
                        
                        // Track slow resources
                        if (entry.duration > 1000) {
                            console.warn('Slow resource detected:', resourceMetrics);
                        }
                    }
                }
            });
            observer.observe({ type: 'resource', buffered: true });
            this.observers.set('resource', observer);
        }
    }
    
    // Navigation timing
    observeNavigationTiming() {
        window.addEventListener('load', () => {
            const navigation = performance.getEntriesByType('navigation')[0];
            if (navigation) {
                this.metrics.set('TTFB', navigation.responseStart - navigation.requestStart);
                this.metrics.set('DOMContentLoaded', navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart);
                this.metrics.set('LoadComplete', navigation.loadEventEnd - navigation.loadEventStart);
            }
        });
    }
    
    // Get all metrics
    getMetrics() {
        return Object.fromEntries(this.metrics);
    }
    
    // Report metrics (can be sent to analytics)
    reportMetrics() {
        const metrics = this.getMetrics();
        console.log('Performance Metrics:', metrics);
        
        // Send to analytics service if available
        if (window.gtag) {
            Object.entries(metrics).forEach(([name, value]) => {
                window.gtag('event', 'performance_metric', {
                    metric_name: name,
                    metric_value: Math.round(value),
                    custom_parameter: 'ayapoll_performance'
                });
            });
        }
        
        return metrics;
    }
    
    // Cleanup observers
    disconnect() {
        this.observers.forEach(observer => observer.disconnect());
        this.observers.clear();
    }
}

// Performance optimization utilities
export const performanceUtils = {
    // Debounce function calls
    debounce(func, wait, immediate = false) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    },
    
    // Throttle function calls
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },
    
    // Optimize scroll events
    optimizeScroll(callback, delay = 16) {
        let ticking = false;
        return this.throttle(() => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    callback();
                    ticking = false;
                });
                ticking = true;
            }
        }, delay);
    },
    
    // Preload resources
    preloadResource(href, as = 'script', crossorigin = null) {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.href = href;
        link.as = as;
        if (crossorigin) link.crossOrigin = crossorigin;
        document.head.appendChild(link);
    },
    
    // Critical resource hints
    addResourceHints(resources) {
        resources.forEach(({ href, rel = 'prefetch', as = null }) => {
            const link = document.createElement('link');
            link.rel = rel;
            link.href = href;
            if (as) link.as = as;
            document.head.appendChild(link);
        });
    }
};

// Initialize performance monitoring
const performanceMonitor = new PerformanceMonitor();

// Report metrics after page load
window.addEventListener('load', () => {
    setTimeout(() => performanceMonitor.reportMetrics(), 5000);
});

export default performanceMonitor;
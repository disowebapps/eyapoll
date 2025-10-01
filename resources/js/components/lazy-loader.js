// Lazy loading utilities for performance optimization
export class LazyLoader {
    constructor(options = {}) {
        this.options = {
            threshold: 0.1,
            rootMargin: '50px',
            ...options
        };
        this.observer = null;
        this.init();
    }
    
    init() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver(
                this.handleIntersection.bind(this),
                this.options
            );
        }
    }
    
    handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                this.loadElement(entry.target);
                this.observer.unobserve(entry.target);
            }
        });
    }
    
    loadElement(element) {
        // Load images
        if (element.dataset.src) {
            element.src = element.dataset.src;
            element.removeAttribute('data-src');
            element.classList.add('loaded');
        }
        
        // Load background images
        if (element.dataset.bg) {
            element.style.backgroundImage = `url(${element.dataset.bg})`;
            element.removeAttribute('data-bg');
        }
        
        // Execute lazy scripts
        if (element.dataset.script) {
            const script = document.createElement('script');
            script.src = element.dataset.script;
            document.head.appendChild(script);
            element.removeAttribute('data-script');
        }
        
        // Trigger custom load event
        element.dispatchEvent(new CustomEvent('lazyloaded'));
    }
    
    observe(element) {
        if (this.observer) {
            this.observer.observe(element);
        } else {
            // Fallback for browsers without IntersectionObserver
            this.loadElement(element);
        }
    }
    
    observeAll(selector = '[data-src], [data-bg], [data-script]') {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => this.observe(el));
    }
    
    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }
}

// Initialize global lazy loader
const lazyLoader = new LazyLoader();

// Auto-observe on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => lazyLoader.observeAll());
} else {
    lazyLoader.observeAll();
}

// Export for manual use
export default lazyLoader;
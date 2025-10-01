import './bootstrap';
import Alpine from 'alpinejs';
import './components/toast';
import './components/lazy-loader';
import './components/performance';

// Alpine.js setup with performance optimizations
window.Alpine = Alpine;

// Configure Alpine for better performance
Alpine.plugin((Alpine) => {
    Alpine.directive('lazy', (el, { expression }, { evaluate }) => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    evaluate(expression);
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.1 });
        observer.observe(el);
    });
});

Alpine.start();

// Enhanced performance optimizations
if ('serviceWorker' in navigator && 'production' === import.meta.env.MODE) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered:', registration);
            })
            .catch(err => console.log('SW registration failed:', err));
    });
}

// Resource optimization
const optimizeResources = () => {
    // Preload critical resources
    const preloadLinks = document.querySelectorAll('link[rel="preload"]');
    preloadLinks.forEach(link => {
        if (!link.href) return;
        const resource = document.createElement('link');
        resource.rel = 'prefetch';
        resource.href = link.href;
        document.head.appendChild(resource);
    });
    
    // Lazy load images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
    
    // Optimize form interactions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
            }
        });
    });
};

// Initialize optimizations when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', optimizeResources);
} else {
    optimizeResources();
}

// Memory management
window.addEventListener('beforeunload', () => {
    // Clean up any observers or intervals
    if (window.imageObserver) {
        window.imageObserver.disconnect();
    }
});

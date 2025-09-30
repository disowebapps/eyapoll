{{-- Floating Action Button Component --}}
@if($showFab && $fabAction)
    <div class="fixed bottom-20 right-6 z-40 lg:bottom-8 lg:right-8">
        {{-- FAB Container --}}
        <div class="relative group">
            {{-- Main FAB Button --}}
            <button wire:click="fabAction"
                    class="fab-button w-14 h-14 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-full shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 ease-out transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-blue-500/30"
                    aria-label="{{ $fabAction['label'] }}"
                    title="{{ $fabAction['label'] }}">

                {{-- Icon --}}
                <svg class="w-6 h-6 mx-auto transition-transform duration-300 group-hover:rotate-12"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24"
                     aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fabAction['icon'] }}"></path>
                </svg>

                {{-- Pulse animation for attention --}}
                <div class="absolute inset-0 rounded-full bg-blue-400 animate-ping opacity-20"></div>
            </button>

            {{-- Tooltip --}}
            <div class="fab-tooltip absolute bottom-full right-0 mb-3 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none whitespace-nowrap">
                {{ $fabAction['label'] }}
                {{-- Arrow --}}
                <div class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
            </div>

            {{-- Ripple effect on click --}}
            <div class="fab-ripple absolute inset-0 rounded-full bg-white opacity-0 scale-0 transition-all duration-300"></div>
        </div>

        {{-- Contextual badge --}}
        @if(isset($fabAction['badge']) && $fabAction['badge'])
            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full min-w-[20px] h-5 flex items-center justify-center px-1 shadow-lg animate-bounce">
                {{ $fabAction['badge'] }}
            </div>
        @endif
    </div>

    {{-- FAB Styles --}}
    <style>
    .fab-button {
        /* Ensure proper touch target */
        min-width: 44px;
        min-height: 44px;

        /* Position for thumb accessibility on mobile */
        /* Bottom right corner with safe area consideration */
        bottom: max(5rem, env(safe-area-inset-bottom) + 5rem);
        right: max(1.5rem, env(safe-area-inset-right) + 1.5rem);
    }

    /* Enhanced shadow for FAB */
    .fab-button {
        box-shadow:
            0 10px 25px -5px rgba(59, 130, 246, 0.4),
            0 4px 6px -2px rgba(59, 130, 246, 0.2),
            0 0 0 1px rgba(255, 255, 255, 0.1);
    }

    .fab-button:hover {
        box-shadow:
            0 20px 40px -10px rgba(59, 130, 246, 0.6),
            0 8px 16px -4px rgba(59, 130, 246, 0.3),
            0 0 0 1px rgba(255, 255, 255, 0.2);
    }

    /* Ripple effect */
    .fab-ripple {
        animation: fab-ripple 0.6s ease-out;
    }

    @keyframes fab-ripple {
        0% {
            opacity: 0.6;
            transform: scale(0);
        }
        100% {
            opacity: 0;
            transform: scale(2);
        }
    }

    /* FAB entrance animation */
    @keyframes fab-enter {
        0% {
            opacity: 0;
            transform: scale(0.8) translateY(20px);
        }
        50% {
            opacity: 1;
            transform: scale(1.05) translateY(-5px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .fab-button {
        animation: fab-enter 0.5s ease-out;
    }

    /* Pulse animation for attention */
    @keyframes fab-pulse {
        0%, 100% {
            transform: scale(1);
            box-shadow:
                0 10px 25px -5px rgba(59, 130, 246, 0.4),
                0 4px 6px -2px rgba(59, 130, 246, 0.2);
        }
        50% {
            transform: scale(1.05);
            box-shadow:
                0 15px 35px -5px rgba(59, 130, 246, 0.6),
                0 6px 12px -2px rgba(59, 130, 246, 0.3);
        }
    }

    /* Apply pulse animation periodically */
    .fab-button {
        animation: fab-enter 0.5s ease-out, fab-pulse 3s ease-in-out 2s infinite;
    }

    /* Desktop adjustments */
    @media (min-width: 1024px) {
        .fab-button {
            width: 16; /* 4rem */
            height: 16; /* 4rem */
            bottom: 2rem;
            right: 2rem;
        }

        .fab-tooltip {
            display: none; /* Hide tooltip on desktop, show on hover */
        }

        .group:hover .fab-tooltip {
            display: block;
        }
    }

    /* Safe area adjustments for devices with notches */
    @supports (env(safe-area-inset-bottom)) {
        .fab-button {
            bottom: max(5rem, calc(env(safe-area-inset-bottom) + 1rem));
            right: max(1.5rem, calc(env(safe-area-inset-right) + 1rem));
        }
    }

    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .fab-button {
            border: 2px solid white;
        }
    }

    /* Reduced motion support */
    @media (prefers-reduced-motion: reduce) {
        .fab-button,
        .fab-ripple,
        .fab-tooltip {
            animation: none;
            transition: none;
        }

        .fab-button:hover {
            transform: none;
        }
    }
    </style>

    {{-- FAB JavaScript for enhanced interactions --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fabButton = document.querySelector('.fab-button');
        const fabRipple = document.querySelector('.fab-ripple');

        if (fabButton && fabRipple) {
            // Add click ripple effect
            fabButton.addEventListener('click', function(e) {
                // Reset animation
                fabRipple.style.animation = 'none';
                fabRipple.offsetHeight; // Trigger reflow
                fabRipple.style.animation = 'fab-ripple 0.6s ease-out';

                // Add haptic feedback if available
                if (navigator.vibrate) {
                    navigator.vibrate(50);
                }
            });

            // Add keyboard support
            fabButton.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    fabButton.click();
                }
            });

            // Focus management
            fabButton.addEventListener('focus', function() {
                fabButton.style.transform = 'scale(1.05)';
            });

            fabButton.addEventListener('blur', function() {
                fabButton.style.transform = 'scale(1)';
            });
        }
    });
    </script>
@endif
@props(['title', 'subtitle' => null, 'class' => ''])

<div class="bg-gradient-to-r from-blue-50 to-blue-200 relative overflow-hidden {{ $class }}">
    <div id="particles-js"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $subtitle }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
</div>

@once
@push('styles')
<style>
#particles-js {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: 1;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof particlesJS !== 'undefined') {
        particlesJS('particles-js', {
            particles: {
                number: { value: 60, density: { enable: true, value_area: 800 } },
                color: { value: "#3b82f6" },
                shape: { type: "circle" },
                opacity: { value: 0.3, random: false },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: "#3b82f6", opacity: 0.2, width: 1 },
                move: { enable: true, speed: 1, direction: "none", random: false, straight: false, out_mode: "out", bounce: false }
            },
            interactivity: {
                detect_on: "canvas",
                events: { onhover: { enable: true, mode: "repulse" }, onclick: { enable: true, mode: "push" }, resize: true },
                modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 2 } }
            },
            retina_detect: true
        });
    }
});
</script>
@endpush
@endonce
@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'badge' => null,
    'badgeType' => 'info',
    'interactive' => false,
    'hover' => true,
    'padding' => 'normal'
])

@php
    $classes = 'card';
    if ($hover) $classes .= ' card-hover';
    if ($interactive) $classes .= ' card-interactive';
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($title || $subtitle || $icon || $badge)
        <div class="card-header">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    @if($icon)
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center shadow-soft">
                            {!! $icon !!}
                        </div>
                    @endif
                    <div>
                        @if($title)
                            <h3 class="text-lg font-bold text-gray-900">{{ $title }}</h3>
                        @endif
                        @if($subtitle)
                            <p class="text-sm text-gray-600">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>
                @if($badge)
                    <span class="badge-{{ $badgeType }}">
                        {{ $badge }}
                    </span>
                @endif
            </div>
        </div>
    @endif
    
    <div class="{{ $padding === 'none' ? '' : 'card-body' }}">
        {{ $slot }}
    </div>
</div>
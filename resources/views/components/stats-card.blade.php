@props([
    'title',
    'value',
    'change' => null,
    'changeType' => 'positive',
    'icon' => null,
    'color' => 'primary'
])

@php
    $colorClasses = [
        'primary' => 'from-primary-500 to-primary-700',
        'success' => 'from-success-500 to-success-700',
        'warning' => 'from-warning-500 to-warning-700',
        'danger' => 'from-danger-500 to-danger-700',
        'civic' => 'from-civic-500 to-civic-700'
    ];
    
    $changeClasses = [
        'positive' => 'text-success-600 bg-success-100',
        'negative' => 'text-danger-600 bg-danger-100',
        'neutral' => 'text-gray-600 bg-gray-100'
    ];
@endphp

<div {{ $attributes->merge(['class' => 'card-hover']) }}>
    <div class="card-body">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">{{ $title }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $value }}</p>
                @if($change)
                    <div class="flex items-center mt-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $changeClasses[$changeType] }}">
                            @if($changeType === 'positive')
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7"></path>
                                </svg>
                            @elseif($changeType === 'negative')
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7l-9.2 9.2M7 7v10h10"></path>
                                </svg>
                            @endif
                            {{ $change }}
                        </span>
                        <span class="text-xs text-gray-500 ml-2">vs last period</span>
                    </div>
                @endif
            </div>
            @if($icon)
                <div class="w-16 h-16 bg-gradient-to-br {{ $colorClasses[$color] }} rounded-2xl flex items-center justify-center shadow-soft">
                    {!! $icon !!}
                </div>
            @endif
        </div>
    </div>
</div>
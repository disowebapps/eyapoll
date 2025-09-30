@props(['election', 'class' => 'w-full'])

@php
    $votingStatus = app(\App\Services\Voting\ConsolidatedEligibilityService::class)
        ->getVotingButtonState(Auth::user(), $election);
@endphp

@if($votingStatus['can_vote'])
    <a href="{{ route('voter.vote', $election) }}" 
       class="{{ $class }} inline-flex items-center justify-center px-4 py-2 {{ $votingStatus['button_class'] }} font-medium rounded-lg transition-colors">
        <x-icon name="{{ $votingStatus['icon'] }}" class="w-4 h-4 mr-2" />
        {{ $votingStatus['button_text'] }}
    </a>
@else
    <button class="{{ $class }} inline-flex items-center justify-center px-4 py-2 {{ $votingStatus['button_class'] }} font-medium rounded-lg" disabled>
        <x-icon name="{{ $votingStatus['icon'] }}" class="w-4 h-4 mr-2" />
        {{ $votingStatus['button_text'] }}
    </button>
@endif
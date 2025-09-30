

@extends('layouts.voting-booth')

@section('page-title', $election->title . ' ')

<div id="voting-booth-root" x-data="votingBooth"></div>


@section('header-actions')
    <div class="flex items-center space-x-2" x-data="{ showExitModal: false }">
        <button @click="showExitModal = true"
                class="text-red-600 hover:text-red-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 rounded px-2 py-1 min-h-[44px]"
                aria-label="Exit voting booth">
            Exit
        </button>

        <!-- Exit Confirmation Modal -->
        <div x-show="showExitModal"
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 z-[70] flex items-center justify-center p-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="exit-modal-title"
             aria-describedby="exit-modal-description">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <header class="px-6 py-4 border-b border-gray-200">
                    <h3 id="exit-modal-title" class="text-lg font-semibold text-gray-900">Exit Voting</h3>
                    <button @click="showExitModal = false"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 rounded min-h-[44px] min-w-[44px] flex items-center justify-center"
                            aria-label="Close exit confirmation">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </header>
                <div class="px-6 py-4">
                    <p id="exit-modal-description" class="text-gray-900 mb-4">Are you sure you want to exit the voting booth?</p>
                    <p class="text-sm text-gray-600">Your selections will be saved and you can return to complete your vote later.</p>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <button @click="showExitModal = false"
                            class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 min-h-[44px]">
                        Stay
                    </button>
                    <a href="{{ route('voter.dashboard') }}"
                       class="flex-1 px-4 py-3 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 text-center focus:outline-none focus:ring-2 focus:ring-red-500 min-h-[44px] inline-flex items-center justify-center"
                       aria-describedby="exit-help">
                        Exit
                    </a>
                    <div id="exit-help" class="sr-only">Exit the voting booth and return to dashboard - your progress will be saved</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @livewire('voter.voting-booth', ['election' => $election])
@endsection
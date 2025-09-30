@php
    $routeName = request()->route()->getName();
    $pageTitles = [
        'voter.dashboard' => 'Dashboard',
        'voter.profile' => 'Profile',
        'voter.kyc' => 'KYC Verification',
        'voter.elections' => 'Elections',
        'voter.vote' => 'Vote',
        'voter.receipt' => 'Receipt',
        'voter.history' => 'Voting History',
        'voter.appeals.index' => 'Appeals',
        'voter.appeals.create' => 'Create Appeal',
        'voter.appeals.show' => 'Appeal Details',
        'voter.appeals.edit' => 'Edit Appeal',
        'voter.document.view' => 'Document',
    ];
    $currentTitle = $pageTitles[$routeName] ?? ucfirst(str_replace(['voter.', '.'], ['', ' '], $routeName));
@endphp

<nav class="bg-white px-6 py-3 border-b border-gray-200 fixed top-16 left-0 lg:left-64 right-0 z-10 block">
    <ol class="flex items-center space-x-2 text-sm">
        <li>
            <a href="{{ route('voter.dashboard') }}" class="text-gray-600 hover:text-blue-600 transition-colors duration-200">
                Home
            </a>
        </li>
        <li>
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">
            {{ $currentTitle }}
        </li>
    </ol>
</nav>
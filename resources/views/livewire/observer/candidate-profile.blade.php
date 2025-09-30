<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('observer.elections') }}" 
               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Elections
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-8">
            <div class="flex items-center space-x-6">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold text-2xl">
                        {{ substr($candidate->user->first_name, 0, 1) }}{{ substr($candidate->user->last_name, 0, 1) }}
                    </span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $candidate->user->full_name }}</h1>
                    <p class="text-indigo-100">{{ $candidate->position->title ?? 'Candidate' }}</p>
                    @if($candidate->party_affiliation)
                        <p class="text-indigo-200 text-sm">{{ $candidate->party_affiliation }}</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-gray-900">{{ $candidate->user->email }}</p>
                        </div>
                        @if($candidate->user->phone)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Phone</label>
                            <p class="text-gray-900">{{ $candidate->user->phone }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Campaign Details</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $candidate->status->value === 'approved' ? 'bg-green-100 text-green-800 ring-1 ring-green-600/20' : '' }}
                                {{ $candidate->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800 ring-1 ring-yellow-600/20' : '' }}
                                {{ $candidate->status->value === 'rejected' ? 'bg-red-100 text-red-800 ring-1 ring-red-600/20' : '' }}">
                                @if($candidate->status->value === 'approved')
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @elseif($candidate->status->value === 'pending')
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                @elseif($candidate->status->value === 'rejected')
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                                {{ $candidate->status->label() }}
                            </span>
                        </div>
                        @if($candidate->bio)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Biography</label>
                            <p class="text-gray-900">{{ $candidate->bio }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
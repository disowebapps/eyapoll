<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto p-4">
        <!-- Header -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Eligible Voters</h1>
                    <p class="text-gray-600 mt-1 md:mt-2 text-sm md:text-base">{{ $election->title }}</p>
                </div>
                <a href="{{ route('admin.elections.results', $electionId) }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm md:text-base text-center">
                    Back to Results
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-gray-900">{{ count($voters) }}</div>
                <div class="text-sm text-gray-600">Total Eligible</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ collect($voters)->where('has_voted', true)->count() }}</div>
                <div class="text-sm text-gray-600">Have Voted</div>
            </div>
        </div>

        <!-- Voters List -->
        <div class="bg-white rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Voter List</h3>
            </div>
            
            @if(count($voters) > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($voters as $voter)
                    <div class="px-4 py-3 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900">{{ $voter['name'] }}</div>
                            <div class="text-sm text-gray-500">{{ $voter['email'] }}</div>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($voter['has_voted'])
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Voted
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Not Voted
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="px-4 py-8 text-center text-gray-500">
                    <div class="text-lg font-medium">No eligible voters found</div>
                    <div class="text-sm mt-1">Voters will appear once they are registered for this election</div>
                </div>
            @endif
        </div>
    </div>
</div>
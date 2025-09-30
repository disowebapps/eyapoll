<div class="max-w-2xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold text-gray-900">Observer Voter Register</h2>
            <p class="text-sm text-gray-600 mt-1">Verify voter registration status for election monitoring</p>
        </div>

        <div class="p-6">
            <form wire:submit="checkRegistration" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" wire:model="email" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter voter email address">
                    @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                    Verify Registration
                </button>
            </form>

            @if($searchResult)
                <div class="mt-6 p-4 rounded-lg {{ $searchResult['status'] === 'found' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    @if($searchResult['status'] === 'found')
                        <div class="flex items-center mb-3">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <h3 class="text-green-800 font-medium">Registration Verified</h3>
                        </div>
                        
                        <p class="text-green-700 mb-4">
                            <strong>{{ $searchResult['user']['first_name'] }} {{ $searchResult['user']['last_name'] }}</strong>
                        </p>

                        <div class="space-y-2">
                            @foreach($searchResult['registrations'] as $reg)
                                <div class="bg-white p-3 rounded border">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $reg['election_title'] }}</p>
                                            <p class="text-sm text-gray-600">{{ $reg['election_date'] }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $reg['status'] === 'voted' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $reg['status'] === 'voted' ? 'Voted' : 'Eligible' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
                            </svg>
                            <p class="text-red-800">No registration found for this email address.</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Verified Voters List -->
    @if($elections->count() > 0)
        <div class="mt-6 bg-white rounded-lg shadow-sm border">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Verified Voters Register</h3>
                <div class="mt-4">
                    <select wire:model.live="selectedElection" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @foreach($elections as $election)
                            <option value="{{ $election->id }}">{{ $election->title }} ({{ $election->starts_at->format('M d, Y') }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="p-6">
                @if(count($verifiedVoters) > 0)
                    <div class="mb-4 text-sm text-gray-600">
                        Total Verified Voters: <strong>{{ count($verifiedVoters) }}</strong>
                    </div>
                    
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($verifiedVoters as $voter)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $voter['name'] }}</p>
                                    <p class="text-sm text-gray-600">Registered: {{ $voter['registered_at'] }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full {{ $voter['status'] === 'Voted' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $voter['status'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 italic">No verified voters for selected election.</p>
                @endif
            </div>
        </div>
    @endif
</div>
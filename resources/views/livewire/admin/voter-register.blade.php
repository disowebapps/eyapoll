<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold text-gray-900">Admin Voter Register</h2>
            <p class="text-sm text-gray-600 mt-1">Search and verify voter registration status across all elections</p>
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
                    Search Voter Registration
                </button>
            </form>

            @if($searchResult)
                <div class="mt-6 p-4 rounded-lg {{ $searchResult['status'] === 'found' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    @if($searchResult['status'] === 'found')
                        <div class="flex items-center mb-3">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <h3 class="text-green-800 font-medium">Voter Found</h3>
                        </div>
                        
                        <div class="bg-white p-4 rounded border mb-4">
                            <p class="font-medium text-gray-900">{{ $searchResult['user']['first_name'] }} {{ $searchResult['user']['last_name'] }}</p>
                            <p class="text-sm text-gray-600">{{ $searchResult['user']['email'] }}</p>
                        </div>

                        <div class="space-y-2">
                            <h4 class="font-medium text-gray-900">Election Registrations:</h4>
                            @foreach($searchResult['registrations'] as $reg)
                                <div class="bg-white p-3 rounded border">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $reg['election_title'] }}</p>
                                            <p class="text-sm text-gray-600">{{ $reg['election_date'] }}</p>
                                            <p class="text-xs text-gray-500">Token: {{ $reg['token_id'] }}</p>
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
                            <p class="text-red-800">No voter found with this email address.</p>
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
                <h3 class="text-lg font-semibold text-gray-900">Accredited Users Register</h3>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Election</label>
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
                        Total Accredited Users: <strong>{{ count($verifiedVoters) }}</strong>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($verifiedVoters as $voter)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $voter['name'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $voter['email'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $voter['status'] === 'Voted' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $voter['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $voter['registered_at'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 italic">No accredited users for selected election.</p>
                @endif
            </div>
        </div>
    @endif
</div>
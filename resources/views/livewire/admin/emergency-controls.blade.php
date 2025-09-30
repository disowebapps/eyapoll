<div class="space-y-6">
    <!-- Warning Banner -->
    <div class="bg-red-50 border-l-4 border-red-400 p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Emergency Controls</h3>
                <p class="text-sm text-red-700">Use only in critical situations. All actions are permanently logged.</p>
            </div>
        </div>
    </div>

    <!-- Active Elections -->
    @if($elections->count() > 0)
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Active Elections</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($elections as $election)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $election->title }}</h4>
                            <p class="text-sm text-gray-500">Status: {{ ucfirst($election->status) }}</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Spacer to prevent accidental clicks -->
                            <div class="w-32"></div>
                            <button wire:click="openHaltModal({{ $election->id }})"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Emergency Halt
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            No active elections requiring emergency controls
        </div>
    @endif

    <!-- Confirmation Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-red-600">Emergency Halt Confirmation</h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Step 1: Reason -->
                    @if($step === 1)
                        <div class="space-y-4">
                            <div class="bg-red-50 p-4 rounded-lg">
                                <p class="text-sm text-red-800">
                                    <strong>Election:</strong> {{ $selectedElection->title }}<br>
                                    <strong>Current Status:</strong> {{ ucfirst($selectedElection->status) }}<br>
                                    <strong>Audit Hash:</strong> <code class="text-xs">{{ substr($auditHash, 0, 16) }}...</code>
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Detailed Reason for Emergency Halt *
                                </label>
                                <textarea wire:model="reason" rows="4" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    placeholder="Provide detailed justification (minimum 20 characters)..."></textarea>
                                @error('reason') 
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button wire:click="closeModal" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button wire:click="nextStep" 
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    Continue
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Step 2: Password & Confirmation -->
                    @if($step === 2)
                        <div class="space-y-4">
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <p class="text-sm text-yellow-800">
                                    <strong>Warning:</strong> This action will immediately halt the election and cannot be undone.
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Your Admin Password *
                                </label>
                                <input wire:model="adminPassword" type="password" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    placeholder="Enter your password">
                                @error('adminPassword') 
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Type "EMERGENCY HALT CONFIRMED" to proceed *
                                </label>
                                <input wire:model="confirmText" type="text" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    placeholder="EMERGENCY HALT CONFIRMED">
                                @error('confirmText') 
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button wire:click="closeModal" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button wire:click="nextStep" 
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    Verify & Continue
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Step 3: Final Confirmation -->
                    @if($step === 3)
                        <div class="space-y-4">
                            <div class="bg-red-100 border border-red-300 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-sm font-medium text-red-800">FINAL CONFIRMATION REQUIRED</p>
                                </div>
                            </div>
                            
                            <div class="space-y-2 text-sm">
                                <p><strong>Election:</strong> {{ $selectedElection->title }}</p>
                                <p><strong>Action:</strong> Emergency Halt</p>
                                <p><strong>Reason:</strong> {{ $reason }}</p>
                                <p><strong>Admin:</strong> {{ auth('admin')->user()->email }}</p>
                                <p><strong>Timestamp:</strong> {{ now()->format('Y-m-d H:i:s T') }}</p>
                                <p><strong>Audit Hash:</strong> <code class="text-xs">{{ $auditHash }}</code></p>
                            </div>
                            
                            <div class="bg-gray-50 p-3 rounded text-xs text-gray-600">
                                This action will be permanently logged with tamper-evident audit trail including IP address, timestamp, and cryptographic verification.
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button wire:click="closeModal" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button wire:click="executeHalt" 
                                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                                    EXECUTE EMERGENCY HALT
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
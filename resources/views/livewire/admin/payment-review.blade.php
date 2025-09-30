<div>
    <!-- Payment Proof Review -->
    @if($candidate->paymentProofs->count() > 0)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Payment Proof</h2>
        <div class="space-y-4">
            @foreach($candidate->paymentProofs as $proof)
            <div class="border rounded-lg p-4 bg-gray-50" x-data="{ showPreview: false }">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <p class="font-medium">{{ $proof->original_filename }}</p>
                        <div class="grid grid-cols-2 gap-4 mt-2 text-sm text-gray-600">
                            <div>
                                <span class="font-medium">Reference:</span> {{ $proof->reference_number }}
                            </div>
                            <div>
                                <span class="font-medium">Amount:</span> ${{ number_format($proof->amount_paid, 2) }}
                            </div>
                            <div>
                                <span class="font-medium">Date:</span> {{ $proof->payment_date->format('M j, Y') }}
                            </div>
                            <div>
                                <span class="font-medium">Method:</span> {{ ucfirst($proof->payment_method) }}
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button @click="showPreview = !showPreview" 
                                class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                            <span x-text="showPreview ? 'Hide' : 'Preview'"></span>
                        </button>
                        <a href="{{ route('admin.candidates.payment.proof.download', [$candidate, $proof]) }}" 
                           class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                            Download
                        </a>
                    </div>
                </div>
                
                <!-- Inline Preview -->
                <div x-show="showPreview" x-collapse class="mt-4 border-t pt-4">
                    @if(in_array($proof->mime_type, ['image/jpeg', 'image/png', 'image/gif']))
                        <img src="{{ route('admin.candidates.payment.proof.download', [$candidate, $proof]) }}" 
                             class="max-w-full h-auto max-h-96 rounded border">
                    @elseif($proof->mime_type === 'application/pdf')
                        <iframe src="{{ route('admin.candidates.payment.proof.download', [$candidate, $proof]) }}" 
                                class="w-full h-96 border rounded"></iframe>
                    @else
                        <p class="text-gray-500 italic">Preview not available for this file type</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Payment History -->
    @if($candidate->paymentHistory->count() > 0)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Payment History</h2>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @foreach($candidate->paymentHistory as $payment)
            <div class="flex items-start justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap
                            {{ $payment->action === 'confirmed' ? 'bg-green-100 text-green-800' : 
                               ($payment->action === 'waived' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800') }}">
                            {{ ucfirst($payment->action) }}
                        </span>
                        <span class="text-sm text-gray-600">by {{ $payment->admin->first_name }} {{ $payment->admin->last_name }}</span>
                        <span class="text-sm font-medium">${{ number_format($payment->amount, 2) }}</span>
                    </div>
                    @if($payment->reason)
                    <p class="text-sm text-gray-700 mt-1 break-words">{{ $payment->reason }}</p>
                    @endif
                </div>
                <div class="text-xs text-gray-500 ml-4 whitespace-nowrap">
                    {{ $payment->created_at->format('M j, Y g:i A') }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Payment Actions -->
    @if($candidate->payment_status && in_array($candidate->payment_status->value, ['pending', 'failed']))
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 text-yellow-800">Payment Actions</h2>
        
        @if($candidate->paymentProofs->count() === 0)
        <div class="mb-4 p-3 bg-orange-100 border border-orange-300 rounded">
            <p class="text-sm text-orange-800">⚠️ No payment proof uploaded. Candidate must upload payment evidence before confirmation.</p>
        </div>
        @endif
        
        <div class="space-y-3">
            <button wire:click="openModal('confirm')" 
                    class="w-full px-4 py-2 rounded-lg text-white transition-colors"
                    @class([
                        'bg-gray-400 cursor-not-allowed' => $candidate->paymentProofs->count() === 0,
                        'bg-green-600 hover:bg-green-700' => $candidate->paymentProofs->count() > 0
                    ])
                    @disabled($candidate->paymentProofs->count() === 0)>
                Confirm Payment Received
            </button>
            
            <button wire:click="openModal('waive')" 
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Waive Payment
            </button>
            
            @if($candidate->payment_status->value === 'failed')
            <button wire:click="openModal('reset')" 
                    class="w-full px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                Reset Payment Status
            </button>
            @endif
            
            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800"><strong>Fee:</strong> ${{ number_format($candidate->application_fee, 2) }}</p>
                <p class="text-sm text-blue-600 mt-1">Current Status: {{ ucfirst($candidate->payment_status->value) }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Modal -->
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md" @click.away="$wire.closeModal()">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">
                    @if($action === 'confirm') Confirm Payment
                    @elseif($action === 'waive') Waive Payment  
                    @else Reset Payment Status
                    @endif
                </h3>
                
                <p class="text-gray-600 mb-4">
                    @if($action === 'confirm') Please provide confirmation details for the payment received.
                    @elseif($action === 'waive') Please provide a reason for waiving the payment.
                    @else Please provide a reason for resetting the payment status.
                    @endif
                </p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason *</label>
                    <textarea wire:model="reason" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                              placeholder="Enter reason..."></textarea>
                    @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                    <button wire:click="closeModal" 
                            class="w-full sm:w-auto px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button wire:click="{{ $action === 'confirm' ? 'confirmPayment' : ($action === 'waive' ? 'waivePayment' : 'resetPayment') }}" 
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-4 py-2 rounded-lg text-white
                                   {{ $action === 'confirm' ? 'bg-green-600 hover:bg-green-700' : 
                                      ($action === 'waive' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-orange-600 hover:bg-orange-700') }}">
                        <span wire:loading.remove>Confirm</span>
                        <span wire:loading>Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
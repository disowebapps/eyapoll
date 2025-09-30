<div class="max-w-3xl mx-auto px-4 sm:px-6 space-y-8 pb-20 sm:pb-8">
    <!-- Header with Status -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="px-6 py-1 text-center">
            <div class="inline-flex items-center justify-center w-10 h-10 bg-green-100 rounded-full mb-4">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
                <h1 class="text-xl font-bold text-gray-900 mb-2">
                You Voted <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-600 text-white text-xs italic">in</span>
                </h1>



            <p class="text-lg text-gray-600 mb-1">{{ $election->title }}</p>
            @if($receiptData)
            <!--p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($receiptData['cast_at'])->format('F j, Y \a\t g:i A') }}</p-->
            @endif
        </div>
    </div>

    @if($loading)
        <!-- Loading State -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-8">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-gray-600">Loading your receipt...</p>
            </div>
        </div>
    @elseif($receiptData)
        <!-- Receipt Content -->
        <div class="bg-white border-2 border-dashed border-gray-300 rounded-lg shadow-sm max-w-md mx-auto">
            <!-- Receipt Header -->
            <div class="px-6 py-4 text-center border-b-2 border-dashed border-gray-300">
                <div class="mb-3">
                    <h1 class="text-sm font-bold text-blue-600 mb-1">ECHARA YOUTHS ASSEMBLY</h1>
                    <p class="text-xs text-gray-500 italic mb-2">One Voice, One Future</p>
                    <div class="w-8 h-0.5 bg-blue-600 mx-auto mb-2"></div>
                </div>
                <h2 class="text-lg font-bold text-gray-900 mb-1">VOTE RECEIPT</h2>
                <p class="text-xs text-gray-500 uppercase tracking-base">{{ $election->title }}</p>
                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($receiptData['cast_at'])->format('M j, Y g:i A') }}</p>
            </div>
            
            <!-- Receipt Details -->
            <div class="p-6 space-y-4 font-mono text-sm">
                <!-- Receipt Items -->
                <div class="flex justify-between items-center py-2 border-b border-dotted border-gray-300">
                    <span class="text-gray-600">RECEIPT ID:</span>
                    <div class="flex items-center">
                        <span class="font-bold">{{ $receiptData['verification_code'] }}</span>
                        <button onclick="copyToClipboard('{{ $receiptData['verification_code'] }}', this)"
                                class="ml-2 text-gray-400 hover:text-gray-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="flex justify-between items-center py-2 border-b border-dotted border-gray-300">
                    <span class="text-gray-600">BLOCK HEIGHT:</span>
                    <span class="font-bold">{{ $receiptData['chain_position'] }}</span>
                </div>
                
                <div class="flex justify-between items-center py-2 border-b border-dotted border-gray-300">
                    <span class="text-gray-600">VOTE STATUS:</span>
                    <span class="font-bold text-green-600">COUNTED ✓ </span>
                </div>
                
                <!-- Voting ID Section -->
                <div class="py-3 border-b border-dotted border-gray-300">
                    <div class="flex justify-between items-start">
                        <span class="text-gray-600 text-xs">VOTING ID:</span>
                        <button onclick="copyToClipboard('{{ $receiptData['receipt_hash'] }}', this)"
                                class="text-gray-400 hover:text-gray-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs break-all mt-1 text-gray-800">{{ $receiptData['receipt_hash'] }}</p>
                </div>

                <!-- Security Features -->
                <div class="py-4 border-b border-dotted border-gray-300 bg-slate-50 -mx-6 px-6">
                    <div class="text-center mb-3">
                        <svg class="w-7 h-7 text-green-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="text-xs font-medium text-slate-600 tracking-tighter">SECURED</span>
                    </div>
                    <div class="grid grid-cols-3 gap-3 text-xs">
                        <div class="text-center">
                            <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <span class="text-slate-600 font-medium">Anonymous</span>
                        </div>
                        <div class="text-center">
                            <div class="w-7 h-7 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-1.5">
                                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <span class="text-slate-600 font-medium">Immutable</span>
                        </div>
                        <div class="text-center">
                            <div class="w-7 h-7 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-1.5">
                                <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-slate-600 font-medium">Verifiable</span>
                        </div>
                    </div>
                </div>

                <!-- Verification Link -->
                <div class="pt-4 text-center">
                    <p class="text-xs text-gray-500 mb-3">Verify this vote publicly:</p>
                    <a href="{{ route('public.verify-vote', ['hash' => $receiptData['receipt_hash']]) }}"
                       target="_blank"
                       class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors">
                        Verify Vote
                    </a>
                </div>
            </div>
            
            <!-- Receipt Footer -->
            <div class="px-6 py-4 text-center border-t-2 border-dashed border-gray-300">
                <p class="text-xs text-blue-600">Keep this receipt for your records</p>
                <p class="text-xs text-gray-400">Thank you for voting!</p>
            </div>
        </div>
        
        <script>
        function copyToClipboard(text, button) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showCopyFeedback(button, 'Copied!');
                }).catch(() => {
                    fallbackCopy(text, button);
                });
            } else {
                fallbackCopy(text, button);
            }
        }
        
        function fallbackCopy(text, button) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                showCopyFeedback(button, 'Copied!');
            } catch (err) {
                console.error('Copy failed:', err);
                showCopyFeedback(button, 'Failed');
            }
            
            document.body.removeChild(textArea);
        }
        
        function showCopyFeedback(button, message) {
            const originalHTML = button.innerHTML;
            button.innerHTML = `<span class="text-xs font-medium">${message}</span>`;
            button.classList.add('bg-green-100', 'text-green-700');
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('bg-green-100', 'text-green-700');
            }, 1500);
        }
        </script>
    @else
        <!-- No Receipt Found -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-8">
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Receipt Found</h3>
                <p class="text-gray-600 mb-6">You haven't voted in this election yet, or your vote is still being processed.</p>
                <a href="{{ route('voter.dashboard') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    Return to Dashboard
                </a>
            </div>
        </div>
    @endif
</div>
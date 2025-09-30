<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Services\Voting\VotingService;
use App\Models\Voting\VoteRecord;
use Illuminate\Support\Facades\Log;

class ReceiptVerification extends Component
{
    public $receiptHash = '';
    public $verificationResult = null;
    public $isVerifying = false;
    public $errorMessage = '';

    protected $rules = [
        'receiptHash' => 'required|string|min:32|max:128|regex:/^[a-f0-9]+$/i',
    ];

    protected $messages = [
        'receiptHash.required' => 'Please enter a receipt hash.',
        'receiptHash.regex' => 'Receipt hash must contain only hexadecimal characters (0-9, A-F).',
        'receiptHash.min' => 'Receipt hash must be at least 32 characters long.',
        'receiptHash.max' => 'Receipt hash is too long.',
    ];

    public function mount($hash = null)
    {
        if ($hash) {
            $this->receiptHash = $hash;
            $this->verifyReceipt();
        }
    }

    public function updatedReceiptHash()
    {
        // Clear previous results when hash changes
        $this->verificationResult = null;
        $this->errorMessage = '';
    }

    public function verifyReceipt()
    {
        $this->validate();

        $this->isVerifying = true;
        $this->verificationResult = null;
        $this->errorMessage = '';

        try {
            $votingService = app(VotingService::class);
            $result = $votingService->verifyReceipt($this->receiptHash);

            $this->verificationResult = $result;

            // Log verification attempt for transparency
            Log::info('Receipt verification performed', [
                'receipt_hash' => substr($this->receiptHash, 0, 8) . '...',
                'result' => $result['valid'] ? 'valid' : 'invalid',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

        } catch (\Exception $e) {
            $this->errorMessage = 'This reciept is not part of the vote chain for this election, pls make sure the hash is correct or contact Eleco.';
            Log::error('Receipt verification error', [
                'receipt_hash' => substr($this->receiptHash, 0, 8) . '...',
                'error' => $e->getMessage(),
                'ip_address' => request()->ip(),
            ]);
        } finally {
            $this->isVerifying = false;
        }
    }

    public function clearResults()
    {
        $this->verificationResult = null;
        $this->errorMessage = '';
        $this->receiptHash = '';
    }

    public function getStatusColor()
    {
        if (!$this->verificationResult) {
            return 'gray';
        }

        return $this->verificationResult['valid'] ? 'green' : 'red';
    }

    public function getStatusIcon()
    {
        if (!$this->verificationResult) {
            return 'question-mark-circle';
        }

        return $this->verificationResult['valid'] ? 'check-circle' : 'x-circle';
    }

    public function getStatusText()
    {
        if (!$this->verificationResult) {
            return 'Not Verified';
        }

        return $this->verificationResult['valid'] ? 'Valid Receipt' : 'Invalid Receipt';
    }

    public function render()
    {
        return view('livewire.public.receipt-verification');
    }
}
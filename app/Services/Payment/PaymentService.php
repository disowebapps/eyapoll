<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Process a mock payment for candidate application fees
     *
     * @param array $paymentData
     * @return array
     */
    public function processMockPayment(array $paymentData): array
    {
        try {
            // Simulate payment processing delay
            sleep(1);

            // Generate mock payment reference
            $reference = 'PAY-' . strtoupper(Str::random(12));

            // Mock payment success (90% success rate for testing)
            $isSuccess = rand(1, 10) <= 9;

            if ($isSuccess) {
                Log::info('Mock payment processed successfully', [
                    'reference' => $reference,
                    'amount' => $paymentData['amount'],
                    'candidate_id' => $paymentData['candidate_id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'reference' => $reference,
                    'amount' => $paymentData['amount'],
                    'processed_at' => now(),
                    'message' => 'Payment processed successfully',
                ];
            } else {
                Log::warning('Mock payment failed', [
                    'reference' => $reference,
                    'amount' => $paymentData['amount'],
                    'candidate_id' => $paymentData['candidate_id'] ?? null,
                ]);

                return [
                    'success' => false,
                    'reference' => $reference,
                    'amount' => $paymentData['amount'],
                    'error' => 'Payment declined by mock processor',
                    'message' => 'Payment was declined. Please try again.',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Mock payment processing error', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData,
            ]);

            return [
                'success' => false,
                'error' => 'Payment processing failed',
                'message' => 'An error occurred while processing payment.',
            ];
        }
    }

    /**
     * Verify a payment reference
     *
     * @param string $reference
     * @return array|null
     */
    public function verifyPayment(string $reference): ?array
    {
        // In a real implementation, this would query the payment gateway
        // For mock purposes, we'll just check if the reference exists in payment history

        $paymentHistory = \App\Models\Candidate\PaymentHistory::where('reference', $reference)->first();

        if ($paymentHistory) {
            return [
                'reference' => $paymentHistory->reference,
                'amount' => $paymentHistory->amount,
                'status' => $paymentHistory->status,
                'processed_at' => $paymentHistory->created_at,
                'candidate_id' => $paymentHistory->candidate_id,
            ];
        }

        return null;
    }

    /**
     * Get payment methods available
     *
     * @return array
     */
    public function getAvailablePaymentMethods(): array
    {
        return [
            'card' => 'Credit/Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'wallet' => 'Digital Wallet',
        ];
    }

    /**
     * Calculate fees for a payment amount
     *
     * @param float $amount
     * @param string $method
     * @return array
     */
    public function calculateFees(float $amount, string $method = 'card'): array
    {
        $feeRates = [
            'card' => 0.025, // 2.5%
            'bank_transfer' => 0.01, // 1%
            'mobile_money' => 0.02, // 2%
            'wallet' => 0.015, // 1.5%
        ];

        $feeRate = $feeRates[$method] ?? 0.025;
        $fee = round($amount * $feeRate, 2);
        $total = $amount + $fee;

        return [
            'amount' => $amount,
            'fee' => $fee,
            'fee_rate' => $feeRate,
            'total' => $total,
            'method' => $method,
        ];
    }
}
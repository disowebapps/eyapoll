<?php

namespace App\Services\Verification;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class RegistrationSessionService
{
    public static function getSessionId(): string
    {
        $sessionId = session('registration_session_id');
        
        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
            session(['registration_session_id' => $sessionId]);
        }
        
        return $sessionId;
    }
    
    public static function saveStep(int $step, array $data): void
    {
        try {
            $sessionId = self::getSessionId();
            
            DB::table('registration_sessions')->upsert(
                [
                    'session_id' => $sessionId,
                    "step{$step}_data" => json_encode($data),
                    'current_step' => $step,
                    'expires_at' => now()->addHours(24),
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
                ['session_id'],
                ["step{$step}_data", 'current_step', 'expires_at', 'updated_at']
            );
        } catch (Exception $e) {
            Log::error('Failed to save registration step', [
                'step' => $step,
                'session_id' => $sessionId ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public static function getStepData(int $step): ?array
    {
        try {
            $sessionId = self::getSessionId();
            
            $record = DB::table('registration_sessions')
                ->where('session_id', $sessionId)
                ->where('expires_at', '>', now())
                ->first();
                
            if (!$record) {
                return null;
            }
            
            $data = $record->{"step{$step}_data"};
            return $data ? json_decode($data, true) : null;
        } catch (Exception $e) {
            Log::error('Failed to get registration step data', [
                'step' => $step,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    public static function getCurrentStep(): int
    {
        try {
            $sessionId = self::getSessionId();
            
            $record = DB::table('registration_sessions')
                ->where('session_id', $sessionId)
                ->where('expires_at', '>', now())
                ->first();
                
            return $record ? $record->current_step : 1;
        } catch (Exception $e) {
            Log::error('Failed to get current registration step', [
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }
    
    public static function clearSession(): void
    {
        try {
            $sessionId = session('registration_session_id');
            
            if ($sessionId) {
                DB::table('registration_sessions')
                    ->where('session_id', $sessionId)
                    ->delete();
                    
                session()->forget('registration_session_id');
            }
        } catch (Exception $e) {
            Log::error('Failed to clear registration session', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
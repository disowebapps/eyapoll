<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationStep
{
    public function handle(Request $request, Closure $next, string $requiredStep): Response
    {
        $currentStep = $this->getCurrentStep($request);
        
        if ($currentStep < (int) $requiredStep) {
            return redirect()->route($this->getRedirectRoute($currentStep));
        }

        return $next($request);
    }

    private function getCurrentStep(Request $request): int
    {
        if (!session('registration_step1')) {
            return 1;
        }
        
        if (!session('registration_step2')) {
            return 2;
        }
        
        return 3;
    }

    private function getRedirectRoute(int $step): string
    {
        return match($step) {
            1 => 'auth.register',
            2 => 'auth.register.step2',
            default => 'auth.register.step3',
        };
    }
}
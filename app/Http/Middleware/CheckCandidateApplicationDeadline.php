<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Election\Election;
use Symfony\Component\HttpFoundation\Response;

class CheckCandidateApplicationDeadline
{
    public function handle(Request $request, Closure $next): Response
    {
        $election = $request->route('election');
        
        if ($election && $election instanceof Election && !$request->user()?->can('apply', $election)) {
            $message = app('App\Policies\ElectionPolicy')->getApplicationMessage($election);
            
            if ($request->expectsJson()) {
                return response()->json(['error' => $message], 403);
            }
            
            return redirect()->back()->with('error', $message);
        }
        
        return $next($request);
    }
}
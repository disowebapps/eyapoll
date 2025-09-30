<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election\Election;
use App\Models\User;
use App\Models\Voting\VoteToken;
use App\Models\System\SecurityEvent;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = Cache::remember('admin_dashboard_metrics', 300, function() {
            return [
                'elections' => [
                    'active' => Election::where('status', 'active')->count(),
                    'total' => Election::count(),
                ],
                'users' => [
                    'total' => User::count(),
                    'verified' => User::whereNotNull('email_verified_at')->count(),
                ],
                'security' => [
                    'alerts_today' => SecurityEvent::whereDate('created_at', today())->count(),
                ],
                'tokens' => [
                    'active' => VoteToken::where('is_used', false)->where('is_revoked', false)->count(),
                ]
            ];
        });

        return view('admin.dashboard', compact('metrics'));
    }
}
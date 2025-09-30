<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicMemberController extends Controller
{
    /**
     * Display the members directory
     */
    public function index(Request $request)
    {
        $query = User::whereIn('status', ['approved', 'accredited'])
                    ->where('is_public', true)
                    ->whereNotNull('city')
                    ->whereNotNull('occupation')
                    ->whereNotNull('about_me');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('occupation', 'like', "%{$search}%");
            });
        }

        // Remove position filter since current_position doesn't exist

        // Filter by status
        // Remove status filter since is_executive doesn't exist

        $members = $query->orderBy('first_name')
                        ->paginate(10);

        // Get unique positions for filter dropdown  
        $positions = collect(); // Empty collection since current_position doesn't exist

        return view('public.members', compact('members', 'positions'))
               ->with('search', $request->get('search'));
    }

    /**
     * Display the executives page
     */
    public function executives()
    {
        $executives = User::whereIn('status', ['approved', 'accredited'])
                         ->where('is_executive', true)
                         ->orderBy('executive_order')
                         ->orderBy('first_name')
                         ->get();

        return view('public.executives', compact('executives'));
    }

    /**
     * Display a specific member's profile
     */
    public function show($id)
    {
        $member = User::where('id', $id)
                     ->where('status', 'approved')
                     ->firstOrFail();

        return view('public.member-profile', compact('member'));
    }

    /**
     * Search members via AJAX
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $members = User::where('status', 'approved')
                      ->where(function ($q) use ($query) {
                          $q->where('first_name', 'like', '%' . $query . '%')
                            ->orWhere('last_name', 'like', '%' . $query . '%')
                            ->orWhere('city', 'like', '%' . $query . '%')
                            ->orWhere('occupation', 'like', '%' . $query . '%');
                      })
                      ->select('id', 'first_name', 'last_name', 'city', 'occupation')
                      ->limit(10)
                      ->get();

        return response()->json($members->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->first_name . ' ' . $member->last_name,
                'position' => $member->email,
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=50&h=50&fit=crop&crop=face',
                'is_executive' => false,
                'url' => route('public.member.profile', $member->id)
            ];
        }));
    }
}
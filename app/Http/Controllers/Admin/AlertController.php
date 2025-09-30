<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }
    
    public function index()
    {
        $alerts = Alert::latest()->take(20)->get();
        $unreadCount = Alert::where('is_read', false)->count();
        
        return response()->json([
            'alerts' => $alerts,
            'unread_count' => $unreadCount
        ]);
    }
    
    public function markAsRead(Alert $alert)
    {
        $alert->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['success' => true]);
    }
    
    public function markAllRead()
    {
        Alert::where('is_read', false)->update([
            'is_read' => true, 
            'read_at' => now()
        ]);
        return response()->json(['success' => true]);
    }
}
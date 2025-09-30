<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Observer;
use App\Services\Observer\ObserverService;
use App\Http\Requests\Admin\UpdateObserverRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ObserverController extends Controller
{
    public function __construct(
        private ObserverService $observerService
    ) {}

    public function show(Observer $observer)
    {
        $observer->loadMissing([
            'assignedElections:id,title,type,status,starts_at,ends_at',
            'approvedBy:id,first_name,last_name'
        ]);

        return view('admin.observers.show', compact('observer'));
    }

    public function edit(Observer $observer)
    {
        return view('admin.observers.edit', compact('observer'));
    }

    public function update(UpdateObserverRequest $request, Observer $observer)
    {
        try {
            $admin = Auth::guard('admin')->user();
            $this->observerService->updateObserver($observer, $admin, $request->validated());
            
            // Clear cache
            Cache::forget("observer_data_{$observer->id}");
            
            return redirect()->route('admin.observers.show', $observer)
                ->with('success', 'Observer updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
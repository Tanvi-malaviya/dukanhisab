<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class AdvertisementController extends Controller
{
    public function index()
    {
        $ads = Advertisement::latest()->paginate(10);
        return view('admin.ads.index', compact('ads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:banner,interstitial,native,announcement',
            'target_url' => 'nullable|url',
            'image_url' => 'nullable|url',
            'script_code' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $ad = Advertisement::create($validated);

        AuditLog::log("Created advertisement campaign: {$ad->title}", $validated);

        return back()->with('success', 'Advertisement campaign created successfully.');
    }

    public function update(Request $request, $id)
    {
        $ad = Advertisement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:banner,interstitial,native,announcement',
            'target_url' => 'nullable|url',
            'image_url' => 'nullable|url',
            'script_code' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $ad->update($validated);

        AuditLog::log("Updated advertisement campaign: {$ad->title}", $validated);

        return back()->with('success', 'Advertisement campaign updated successfully.');
    }

    public function toggleStatus($id)
    {
        $ad = Advertisement::findOrFail($id);
        $newStatus = $ad->status === 'active' ? 'inactive' : 'active';
        $ad->update(['status' => $newStatus]);

        AuditLog::log("Toggled ad campaign #{$ad->id} to {$newStatus}");

        return back()->with('success', "Campaign status changed to {$newStatus}.");
    }

    public function destroy($id)
    {
        $ad = Advertisement::findOrFail($id);
        $title = $ad->title;
        $ad->delete();

        AuditLog::log("Deleted ad campaign: {$title}");

        return back()->with('success', 'Advertisement campaign deleted.');
    }
}

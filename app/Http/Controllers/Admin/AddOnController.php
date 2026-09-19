<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\UserAddOn;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class AddOnController extends Controller
{
    public function index(Request $request)
    {
        $addOns = AddOn::orderBy('price', 'asc')->get();

        // One row per purchase: shop add-ons stack, so a user can hold several.
        $historyQuery = UserAddOn::with(['user.shops', 'shop', 'addOn']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $historyQuery->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('mobile', 'like', "%{$search}%");
                })->orWhereHas('shop', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                });
            });
        }
        $history = $historyQuery->latest()->paginate(10)->withQueryString();
        $users = User::with('shops')->orderBy('name')->get();

        return view('admin.addons.index', compact('addOns', 'history', 'users'));
    }

    public function assignToUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'add_on_id' => 'required|exists:add_ons,id',
            'shop_id' => 'nullable|exists:shops,id',
            'quantity' => 'required|integer|min:1|max:20',
            'days' => 'required|integer|min:1',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $addOn = AddOn::findOrFail($validated['add_on_id']);
        $shopId = !empty($validated['shop_id']) ? $validated['shop_id'] : $user->shops()->first()?->id;

        $userAddOn = UserAddOn::create([
            'user_id' => $user->id,
            'shop_id' => $shopId,
            'add_on_id' => $addOn->id,
            'quantity' => $validated['quantity'],
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays((int) $validated['days']),
            'auto_renew' => true,
        ]);

        $shopName = $userAddOn->shop?->name ?? 'N/A';
        AuditLog::log("Manually granted {$addOn->title} (Qty: {$validated['quantity']}) to user '{$user->name}' (Shop: {$shopName}) for {$validated['days']} days");

        return back()->with('success', "{$addOn->title} add-on assigned to user {$user->name} successfully.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:shop,website',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $validated['slug'] = $validated['type'];
        $validated['billing_period'] = 'yearly';
        $validated['status'] = 'active';

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('addons', 'public');
        }

        $addOn = AddOn::updateOrCreate(['type' => $validated['type']], $validated);

        AuditLog::log("Created/updated add-on {$addOn->title}", $validated);

        return back()->with('success', 'Add-on saved successfully.');
    }

    public function update(Request $request, $id)
    {
        $addOn = AddOn::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('addons', 'public');
        }

        $addOn->update($validated);

        AuditLog::log("Updated add-on {$addOn->title}", $validated);

        return back()->with('success', 'Add-on updated successfully.');
    }

    public function expire($id)
    {
        $userAddOn = UserAddOn::with('user')->findOrFail($id);
        $userAddOn->update([
            'status' => 'expired',
            'auto_renew' => false,
            'ends_at' => now(),
        ]);

        $userName = $userAddOn->user ? $userAddOn->user->name : 'User';
        AuditLog::log("Expired add-on #{$userAddOn->id} ({$userAddOn->addOn->title}) for user '{$userName}'");

        return back()->with('success', 'Add-on has been expired.');
    }

    public function extend(Request $request, $id)
    {
        $userAddOn = UserAddOn::with('user')->findOrFail($id);

        $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        $days = (int) $request->input('days');

        if ($userAddOn->status === 'active' && $userAddOn->ends_at && $userAddOn->ends_at->isFuture()) {
            $newEndsAt = $userAddOn->ends_at->addDays($days);
        } else {
            $newEndsAt = now()->addDays($days);
        }

        $userAddOn->update([
            'ends_at' => $newEndsAt,
            'status' => 'active',
        ]);

        AuditLog::log("Extended add-on #{$userAddOn->id} by {$days} days", ['new_ends_at' => $newEndsAt->toDateString()]);

        return back()->with('success', "Add-on extended/reactivated successfully by {$days} days.");
    }
}

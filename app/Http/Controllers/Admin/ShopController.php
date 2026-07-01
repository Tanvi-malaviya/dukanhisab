<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Shop::with(['owner', 'activePlan', 'currentSubscription']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhereHas('owner', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('plan')) {
            $query->where('active_plan_id', $request->input('plan'));
        }

        $shops = $query->latest()->paginate(10)->withQueryString();
        $plans = SubscriptionPlan::where('status', 'active')->get();
        $users = User::orderBy('name')->get();

        return view('admin.shops.index', compact('shops', 'plans', 'users'));
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'owner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|digits:10',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:15',
            'status' => 'required|in:active,suspended',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'mobile.digits' => 'The mobile number must be exactly 10 digits.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal_open', 'add_shop');
        }

        $validated = $validator->validated();

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $shop = Shop::create($validated);

        // Sync owner status to match shop status
        if ($shop->owner) {
            $shop->owner->update(['status' => $shop->status]);
        }

        AuditLog::log("Manually created shop #{$shop->id} ({$shop->name})", $validated);

        return back()->with('success', 'Shop created successfully.');
    }

    public function update(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'owner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|digits:10',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:15',
            'status' => 'required|in:active,suspended',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'mobile.digits' => 'The mobile number must be exactly 10 digits.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal_open', 'edit_shop')
                ->with('edit_shop_data', $shop);
        }

        $validated = $validator->validated();

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($shop->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($shop->logo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($shop->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $shop->update($validated);

        // Sync owner status to match shop status
        if ($shop->owner) {
            $shop->owner->update(['status' => $shop->status]);
            AuditLog::log("Automatically synchronized status of owner #{$shop->owner->id} to {$shop->status} due to manually updated shop status.");
        }

        AuditLog::log("Manually updated shop #{$shop->id} ({$shop->name})", $validated);

        return back()->with('success', 'Shop updated successfully.');
    }

    public function show($id)
    {
        $shop = Shop::with(['owner', 'activePlan', 'subscriptions.plan', 'payments.plan'])->findOrFail($id);
        return response()->json($shop);
    }

    public function toggleStatus($id)
    {
        $shop = Shop::with('owner')->findOrFail($id);
        $newStatus = $shop->status === 'suspended' ? 'active' : 'suspended';
        $shop->update(['status' => $newStatus]);

        // Automatically sync status to the owner user
        if ($shop->owner) {
            $shop->owner->update(['status' => $newStatus]);
            AuditLog::log("Automatically synchronized status of owner #{$shop->owner->id} to {$newStatus} due to shop status change.");
        }

        AuditLog::log("Toggled status of shop #{$shop->id} to {$newStatus}", ['status' => $newStatus]);

        return back()->with('success', "Shop status and owner account have been updated to {$newStatus}.");
    }

    public function updateSubscription(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'duration_days' => 'required|integer|min:1',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->input('plan_id'));
        $days = (int) $request->input('duration_days');

        $startsAt = now();
        $endsAt = now()->addDays($days);

        // Deactivate past active subscriptions
        Subscription::where('shop_id', $shop->id)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        // Create new subscription record
        $subscription = Subscription::create([
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        // Create manual payment record if it is a paid plan
        if ($plan->price > 0) {
            \App\Models\Payment::create([
                'user_id' => $shop->owner_id,
                'shop_id' => $shop->id,
                'plan_id' => $plan->id,
                'amount' => $plan->price,
                'payment_gateway' => 'manual',
                'transaction_id' => 'tx_manual_' . strtoupper(\Illuminate\Support\Str::random(10)),
                'status' => 'successful',
                'payment_date' => now(),
            ]);
        }

        // Link on shop table
        $shop->update([
            'active_plan_id' => $plan->id,
        ]);

        AuditLog::log("Manually updated subscription for shop #{$shop->id} to plan {$plan->name}", [
            'plan_id' => $plan->id,
            'ends_at' => $endsAt->toDateString()
        ]);

        return back()->with('success', "Subscription for shop '{$shop->name}' successfully updated to {$plan->name} for {$days} days.");
    }
}

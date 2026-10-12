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
        return redirect()->route('admin.users.index', $request->query());
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

        // The admin panel is never limited by a user's plan/add-on shop quota: past it, this
        // silently grants one more Shop Add-on slot (no payment, never expires) so the extra
        // shop stays usable instead of getting locked by ShopScopeMiddleware the same way an
        // unpaid one would.
        $owner = User::find($validated['owner_id']);
        $addedByAdmin = false;
        if ($owner && !$owner->canAddShop()) {
            $shopAddOn = \App\Models\AddOn::where('type', 'shop')->first();
            if ($shopAddOn) {
                \App\Models\UserAddOn::create([
                    'user_id' => $owner->id,
                    'add_on_id' => $shopAddOn->id,
                    'quantity' => 1,
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => null,
                    'auto_renew' => false,
                    'granted_by_admin' => true,
                    'admin_note' => 'Auto-granted to cover a shop added directly from the admin panel.',
                ]);
                $addedByAdmin = true;
            }
        }
        $validated['added_by_admin'] = $addedByAdmin;

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $shop = Shop::create($validated);

        // Link to an unlinked active shop add-on if one exists
        if ($owner) {
            $unlinkedAddOn = \App\Models\UserAddOn::where('user_id', $owner->id)
                ->whereNull('shop_id')
                ->where('status', 'active')
                ->first();
            if ($unlinkedAddOn) {
                $unlinkedAddOn->update(['shop_id' => $shop->id]);
            }
        }

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

    /** Switch an optional module (Shop::FEATURES) on or off for one shop — free, admin-only. */
    public function updateFeature(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        $request->validate([
            'feature' => 'required|string|in:' . implode(',', array_keys(Shop::FEATURES)),
            'enabled' => 'required|boolean',
        ]);

        $feature = $request->input('feature');
        $features = array_values(array_diff($shop->features ?? [], [$feature]));
        if ($request->boolean('enabled')) {
            $features[] = $feature;
        }
        // Turning a module off only hides it — its data stays and comes back if switched on again.
        $shop->forceFill(['features' => $features])->save();

        $label = Shop::FEATURES[$feature];
        $state = $request->boolean('enabled') ? 'enabled' : 'disabled';
        AuditLog::log("{$label} {$state} for shop #{$shop->id} ({$shop->name})", ['feature' => $feature, 'enabled' => $request->boolean('enabled')]);

        return back()->with('success', "{$label} {$state} for {$shop->name}.");
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

        if ($shop->owner) {
            // Deactivate past active subscriptions for owner
            Subscription::where('user_id', $shop->owner_id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            // Create new subscription record
            Subscription::create([
                'user_id' => $shop->owner_id,
                'shop_id' => $shop->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);

            $shop->owner->update([
                'active_plan_id' => $plan->id,
            ]);
        }

        // Create manual payment record if it is a paid plan
        if ($plan->price > 0 && $shop->owner_id) {
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

        AuditLog::log("Manually updated subscription for shop #{$shop->id} owner to plan {$plan->name}", [
            'plan_id' => $plan->id,
            'ends_at' => $endsAt->toDateString()
        ]);

        return back()->with('success', "Subscription for user '{$shop->owner->name}' successfully updated to {$plan->name} for {$days} days.");
    }
}

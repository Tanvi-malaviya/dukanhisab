<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\AuditLog;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::latest()->get();
        
        $allSubscriptions = Subscription::select('id', 'user_id', 'status')
            ->whereNotNull('user_id')
            ->orderBy('id', 'desc')
            ->get();
            
        $grouped = $allSubscriptions->groupBy('user_id');
        $subscriptionIds = [];
        foreach ($grouped as $userId => $subs) {
            $subToShow = $subs->firstWhere('status', 'active') ?: $subs->first();
            if ($subToShow) {
                $subscriptionIds[] = $subToShow->id;
            }
        }

        $historyQuery = Subscription::with(['user', 'plan'])
            ->whereIn('id', $subscriptionIds);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $historyQuery->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }
        $history = $historyQuery->latest()->paginate(10)->withQueryString();

        return view('admin.subscriptions.index', compact('plans', 'history'));
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:free,monthly,yearly',
            'features' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        // Ensure features structure has defaults
        $features = $validated['features'] ?? [];
        $features['max_shops'] = isset($features['max_shops']) ? (int)$features['max_shops'] : 1;
        $features['max_devices'] = isset($features['max_devices']) ? (int)$features['max_devices'] : 1;
        $features['advanced_reports'] = isset($features['advanced_reports']) ? true : false;
        $features['backup'] = isset($features['backup']) ? true : false;
        $validated['features'] = $features;

        $plan = SubscriptionPlan::create($validated);

        AuditLog::log("Created subscription plan {$plan->name}", $validated);

        return back()->with('success', 'Subscription plan created successfully.');
    }

    public function updatePlan(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:free,monthly,yearly',
            'features' => 'nullable|array',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $features = $validated['features'] ?? [];
        $features['max_shops'] = isset($features['max_shops']) ? (int)$features['max_shops'] : 1;
        $features['max_devices'] = isset($features['max_devices']) ? (int)$features['max_devices'] : 1;
        $features['advanced_reports'] = isset($features['advanced_reports']) ? true : false;
        $features['backup'] = isset($features['backup']) ? true : false;
        $validated['features'] = $features;

        $plan->update($validated);

        AuditLog::log("Updated subscription plan {$plan->name}", $validated);

        return back()->with('success', 'Subscription plan updated successfully.');
    }

    public function expireSubscription($id)
    {
        $subscription = Subscription::with('user')->findOrFail($id);
        $subscription->update([
            'status' => 'expired',
            'ends_at' => now(),
        ]);

        // Reset User's active plan to null or Free plan
        $freePlan = SubscriptionPlan::where('slug', 'free')->first();
        if ($subscription->user) {
            $subscription->user->update([
                'active_plan_id' => $freePlan ? $freePlan->id : null,
            ]);
        }

        $userName = $subscription->user ? $subscription->user->name : 'User';
        AuditLog::log("Expired subscription #{$subscription->id} for user '{$userName}'");

        return back()->with('success', 'Subscription has been expired.');
    }

    public function extendSubscription(Request $request, $id)
    {
        $subscription = Subscription::with('user')->findOrFail($id);
        
        $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        $days = (int) $request->input('days');
        
        // If active and ends in the future, extend from ends_at. Otherwise, start from now.
        if ($subscription->status === 'active' && $subscription->ends_at && $subscription->ends_at->isFuture()) {
            $newEndsAt = $subscription->ends_at->addDays($days);
        } else {
            $newEndsAt = now()->addDays($days);
        }

        $subscription->update([
            'ends_at' => $newEndsAt,
            'status' => 'active',
        ]);

        // Create manual payment record if it is a paid plan
        $plan = $subscription->plan;
        $user = $subscription->user;
        if ($plan && $plan->price > 0 && $user) {
            $amount = 0.00;
            if ($plan->billing_period === 'monthly') {
                $amount = round(($plan->price / 30) * $days, 2);
            } elseif ($plan->billing_period === 'yearly') {
                $amount = round(($plan->price / 365) * $days, 2);
            } else {
                $amount = $plan->price;
            }

            $firstShop = $user->shops()->first();

            \App\Models\Payment::create([
                'user_id' => $user->id,
                'shop_id' => $firstShop ? $firstShop->id : null,
                'plan_id' => $subscription->plan_id,
                'amount' => $amount,
                'payment_gateway' => 'manual',
                'transaction_id' => 'tx_manual_' . strtoupper(\Illuminate\Support\Str::random(10)),
                'status' => 'successful',
                'payment_date' => now(),
            ]);
        }

        // Restore active_plan_id on the user if it's currently null or different
        if ($user && $user->active_plan_id !== $subscription->plan_id) {
            $user->update([
                'active_plan_id' => $subscription->plan_id,
            ]);
        }

        AuditLog::log("Extended subscription #{$subscription->id} by {$days} days", ['new_ends_at' => $newEndsAt->toDateString()]);

        return back()->with('success', "Subscription extended/reactivated successfully by {$days} days.");
    }

    public function reactivateSubscription($id)
    {
        $subscription = Subscription::with('user')->findOrFail($id);
        $plan = $subscription->plan;
        $user = $subscription->user;

        if ($subscription->ends_at && $subscription->ends_at->isFuture()) {
            $newEndsAt = $subscription->ends_at;
            $msg = "Subscription reactivated successfully using the remaining coverage days.";
        } else {
            // Otherwise, calculate new ends_at based on the plan's billing period
            $days = 30; // Default monthly
            if ($plan && $plan->billing_period === 'yearly') {
                $days = 365;
            }
            $newEndsAt = now()->addDays($days);
            $msg = "Subscription reactivated successfully. New expiry: {$newEndsAt->format('Y-m-d')}.";

            // If it is a paid plan, record a manual payment
            if ($plan && $plan->price > 0 && $user) {
                $firstShop = $user->shops()->first();
                \App\Models\Payment::create([
                    'user_id' => $user->id,
                    'shop_id' => $firstShop ? $firstShop->id : null,
                    'plan_id' => $subscription->plan_id,
                    'amount' => $plan->price,
                    'payment_gateway' => 'manual',
                    'transaction_id' => 'tx_manual_' . strtoupper(\Illuminate\Support\Str::random(10)),
                    'status' => 'successful',
                    'payment_date' => now(),
                ]);
            }
        }

        $subscription->update([
            'ends_at' => $newEndsAt,
            'status' => 'active',
        ]);

        if ($user && $user->active_plan_id !== $subscription->plan_id) {
            $user->update([
                'active_plan_id' => $subscription->plan_id,
            ]);
        }

        $userName = $user ? $user->name : 'User';
        AuditLog::log("Reactivated subscription #{$subscription->id} for user '{$userName}' until {$newEndsAt->toDateString()}");

        return back()->with('success', $msg);
    }

    public function destroyPlan($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        // Check if there are active subscriptions using this plan
        $activeSubCount = Subscription::where('plan_id', $id)->where('status', 'active')->count();
        if ($activeSubCount > 0) {
            return back()->with('error', 'Cannot delete this plan because it has active subscribers.');
        }

        // Check if it's the default free plan
        if ($plan->slug === 'free') {
            return back()->with('error', 'Cannot delete the default Free subscription plan.');
        }

        // Delete associated subscriptions and nullify user references
        Subscription::where('plan_id', $id)->delete();
        User::where('active_plan_id', $id)->update(['active_plan_id' => null]);

        $plan->delete();

        AuditLog::log("Deleted subscription plan {$plan->name}");

        return back()->with('success', "Subscription plan deleted successfully.");
    }
}

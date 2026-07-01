<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Revenue Report aggregates
        $dailyRevenue = Payment::where('status', 'successful')
            ->whereDate('payment_date', Carbon::today())
            ->sum('amount');
            
        $weeklyRevenue = Payment::where('status', 'successful')
            ->where('payment_date', '>=', Carbon::now()->startOfWeek())
            ->sum('amount');
            
        $monthlyRevenue = Payment::where('status', 'successful')
            ->whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount');
            
        $yearlyRevenue = Payment::where('status', 'successful')
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount');

        // 2. User Growth metrics
        $newUsersThisMonth = User::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
            
        $activeUsers = User::where('status', 'active')->count();

        // 3. Premium Conversion analytics
        $totalShops = Shop::count();
        $freeShopsCount = Shop::where(function($q) {
            $q->whereNull('active_plan_id')
              ->orWhereIn('active_plan_id', function($sub) {
                  $sub->select('id')->from('subscription_plans')->where('price', 0);
              });
        })->count();
        
        $premiumShopsCount = Shop::whereHas('activePlan', function($q) {
            $q->where('price', '>', 0);
        })->count();
        
        $conversionRate = $totalShops > 0 ? round(($premiumShopsCount / $totalShops) * 100, 2) : 0;

        // 4. Plan performance table
        $plansPerformance = SubscriptionPlan::all()->map(function($plan) {
            $shopsCount = Shop::where('active_plan_id', $plan->id)->count();
            $totalRevenue = Payment::where('status', 'successful')
                ->where('plan_id', $plan->id)
                ->sum('amount');
            return [
                'name' => $plan->name,
                'price' => $plan->price,
                'billing_period' => $plan->billing_period,
                'shops_count' => $shopsCount,
                'total_revenue' => $totalRevenue,
            ];
        });

        // 5. Recent payments list
        $recentPayments = Payment::with(['shop', 'plan'])
            ->where('status', 'successful')
            ->latest()
            ->paginate(5);

        return view('admin.reports.index', compact(
            'dailyRevenue',
            'weeklyRevenue',
            'monthlyRevenue',
            'yearlyRevenue',
            'newUsersThisMonth',
            'activeUsers',
            'totalShops',
            'freeShopsCount',
            'premiumShopsCount',
            'conversionRate',
            'plansPerformance',
            'recentPayments'
        ));
    }
}

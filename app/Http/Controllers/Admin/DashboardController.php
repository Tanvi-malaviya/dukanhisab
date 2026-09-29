<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. KPI Counts
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        
        // "Premium" = any paid plan (Monthly, Yearly, ...), not a specific slug
        $paidPlanIds = SubscriptionPlan::where('slug', '!=', 'free')->pluck('id');

        $premiumUsers = User::whereIn('active_plan_id', $paidPlanIds)->count();
        $totalShops = Shop::count();
        
        $todayRevenue = Payment::where('status', 'successful')
            ->whereDate('payment_date', Carbon::today())
            ->sum('amount');
            
        $monthlyRevenue = Payment::where('status', 'successful')
            ->whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount');

        // Active Devices from Sanctum personal access tokens + standard web sessions
        $activeDevices = DB::table('personal_access_tokens')->count() + DB::table('sessions')->count();

        // Real week-over-week signup growth (replaces a previously hardcoded "+12.3%").
        $signupsThisWeek = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $signupsPriorWeek = User::whereBetween('created_at', [Carbon::now()->subDays(14), Carbon::now()->subDays(7)])->count();
        $userGrowthPct = $signupsPriorWeek > 0
            ? round((($signupsThisWeek - $signupsPriorWeek) / $signupsPriorWeek) * 100, 1)
            : ($signupsThisWeek > 0 ? 100.0 : 0.0);

        // "Needs attention" panel — the whole point of a dashboard someone actually uses: it should
        // surface what needs a click today, not just totals.
        $openTicketsCount = SupportTicket::whereIn('status', ['open', 'pending', 'inProgress'])->count();
        $pendingRefundsCount = Refund::where('status', 'pending')->count();
        $expiringSoonCount = Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->count();
        $suspendedUsersCount = User::where('status', 'suspended')->count();

        // 2. Charts Data (Past 7 Days Daily Registrations)
        $dailyRegsData = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
            
        $dailyRegsLabels = [];
        $dailyRegsValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dailyRegsLabels[] = Carbon::now()->subDays($i)->format('D, M d');
            $match = $dailyRegsData->firstWhere('date', $date);
            $dailyRegsValues[] = $match ? $match->count : 0;
        }

        // 3. Subscription Sales Trend (Past 7 Days Premium Sales)
        $dailySalesData = Payment::where('status', 'successful')
            ->whereIn('plan_id', $paidPlanIds)
            ->where('payment_date', '>=', Carbon::now()->subDays(7))
            ->select(DB::raw('DATE(payment_date) as date'), DB::raw('sum(amount) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
            
        $dailySalesValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $match = $dailySalesData->firstWhere('date', $date);
            $dailySalesValues[] = $match ? (float)$match->total : 0.0;
        }

        // 4. Monthly Revenue Graph (Past 6 Months)
        // Grouped in PHP (not with MySQL-only YEAR()/MONTH()) so it works on any database driver.
        $monthlyRevData = Payment::where('status', 'successful')
            ->where('payment_date', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->get(['amount', 'payment_date'])
            ->groupBy(fn ($p) => Carbon::parse($p->payment_date)->format('Y-m'))
            ->map(fn ($rows) => (float) $rows->sum('amount'));

        $monthlyRevLabels = [];
        $monthlyRevValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $monthlyRevLabels[] = $dt->format('M Y');
            $monthlyRevValues[] = (float) ($monthlyRevData[$dt->format('Y-m')] ?? 0.0);
        }

        // 5. Active Users Analytics (Daily active count from audit logs or logins)
        $activeAnalyticsValues = [];
        for ($i = 6; $i >= 0; $i--) {
            // Count unique users who had audit logs or last login on that day
            $dt = Carbon::now()->subDays($i);
            $count = DB::table('audit_logs')
                ->whereDate('created_at', $dt->toDateString())
                ->distinct('user_id')
                ->count('user_id');
            $activeAnalyticsValues[] = $count;
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'premiumUsers',
            'totalShops',
            'todayRevenue',
            'monthlyRevenue',
            'activeDevices',
            'userGrowthPct',
            'openTicketsCount',
            'pendingRefundsCount',
            'expiringSoonCount',
            'suspendedUsersCount',
            'dailyRegsLabels',
            'dailyRegsValues',
            'dailySalesValues',
            'monthlyRevLabels',
            'monthlyRevValues',
            'activeAnalyticsValues'
        ));
    }
}

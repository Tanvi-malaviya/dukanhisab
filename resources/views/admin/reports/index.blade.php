@extends('layouts.admin')

@section('title', 'Platform Reports')
@section('page_title', 'Analytics & Reports')


@section('content')
    <div class="space-y-2">

        <!-- Revenue Period Metrics -->
        <h2 class="text-lg font-bold text-white mb-2">Revenue</h2>
        <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
            <!-- Daily -->
            <div class="p-4 rounded-2xl shadow-sm border relative overflow-hidden"
                style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(209, 250, 229, 0.25) 100%); border-color: rgba(16, 185, 129, 0.2);">
                <p class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Daily
                </p>
                <h3 class="text-xl font-black text-emerald-950 mt-1">₹{{ number_format($dailyRevenue, 2) }}</h3>
                <div class="mt-1 text-[10px] text-emerald-800/80 font-medium">Collected today</div>
            </div>

            <!-- Weekly -->
            <div class="p-4 rounded-2xl shadow-sm border relative overflow-hidden"
                style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(219, 234, 254, 0.25) 100%); border-color: rgba(59, 130, 246, 0.2);">
                <p class="text-[10px] font-bold text-blue-800 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Weekly
                </p>
                <h3 class="text-xl font-black text-blue-950 mt-1">₹{{ number_format($weeklyRevenue, 2) }}</h3>
                <div class="mt-1 text-[10px] text-blue-800/80 font-medium">Current week (Mon-Sun)</div>
            </div>

            <!-- Monthly -->
            <div class="p-4 rounded-2xl shadow-sm border relative overflow-hidden"
                style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(237, 233, 254, 0.25) 100%); border-color: rgba(139, 92, 246, 0.2);">
                <p class="text-[10px] font-bold text-violet-800 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                    Monthly
                </p>
                <h3 class="text-xl font-black text-violet-950 mt-1">₹{{ number_format($monthlyRevenue, 2) }}</h3>
                <div class="mt-1 text-[10px] text-violet-800/80 font-medium">Current calendar month</div>
            </div>

            <!-- Yearly -->
            <div class="p-4 rounded-2xl shadow-sm border relative overflow-hidden"
                style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(254, 243, 199, 0.25) 100%); border-color: rgba(245, 158, 11, 0.2);">
                <p class="text-[10px] font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Yearly
                </p>
                <h3 class="text-xl font-black text-amber-950 mt-1">₹{{ number_format($yearlyRevenue, 2) }}</h3>
                <div class="mt-1 text-[10px] text-amber-800/80 font-medium">Accumulated this year</div>
            </div>
        </div>

        <!-- Growth & Conversion Metrics -->
        <div class="grid grid-cols-1 gap-2 lg:grid-cols-3">
            <!-- Growth -->
            <div class="bg-card-dark border border-border-dark p-6 rounded-2xl flex flex-col justify-between">
                <h3 class="text-sm font-semibold text-white">Platform Growth</h3>
                <div class="space-y-4 my-6">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">New Signups (This Month):</span>
                        <span class="text-white font-semibold font-mono">{{ $newUsersThisMonth }} Users</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Total Registered Tenants:</span>
                        <span class="text-white font-semibold font-mono">{{ $totalShops }} Shops</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Active Node Users:</span>
                        <span class="text-white font-semibold font-mono">{{ $activeUsers }} Active</span>
                    </div>
                </div>
                <div class="text-[10px] text-slate-500 border-t border-border-dark pt-3">Calculated via daily cron metrics.
                </div>
            </div>

            <!-- Conversion -->
            <div class="bg-card-dark border border-border-dark p-6 rounded-2xl lg:col-span-2 flex flex-col justify-between">
                <h3 class="text-sm font-semibold text-white">Premium Conversion Rate</h3>

                <div class="flex flex-col md:flex-row items-center gap-8 my-6">
                    <!-- Circular gauge simulation -->
                    <div
                        class="relative w-32 h-32 flex items-center justify-center rounded-full border-4 border-secondary/40">
                        <div
                            class="absolute inset-2 rounded-full border border-primary/20 flex flex-col items-center justify-center">
                            <span class="text-2xl font-extrabold text-white">{{ $conversionRate }}%</span>
                            <span class="text-[9px] text-slate-500 uppercase tracking-wider font-semibold">Premium</span>
                        </div>
                    </div>

                    <div class="flex-1 space-y-3.5 w-full text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 flex items-center"><span
                                    class="w-2 h-2 rounded-full bg-warning mr-2"></span>Premium Shops:</span>
                            <span class="text-white font-bold font-mono">{{ $premiumShopsCount }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 flex items-center"><span
                                    class="w-2 h-2 rounded-full bg-slate-500 mr-2"></span>Free/Trial Shops:</span>
                            <span class="text-white font-bold font-mono">{{ $freeShopsCount }}</span>
                        </div>
                        <div class="w-full bg-secondary/40 h-2 rounded-full overflow-hidden mt-3">
                            <div class="bg-primary h-full rounded-full" style="width: {{ $conversionRate }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="text-[10px] text-slate-500 border-t border-border-dark pt-3">Premium Conversion = (Premium Shops
                    / Total Shops) * 100.</div>
            </div>
        </div>

        <!-- Plan Performance list -->
        <div class="space-y-2">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                </svg>
                Subscription Plan Reports
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                @foreach($plansPerformance as $perf)
                    @php
                        $isPaid = $perf['price'] > 0;
                        if (!$isPaid) {
                            $cardStyle = "background: linear-gradient(135deg, rgba(241, 245, 249, 0.5) 0%, rgba(226, 232, 240, 0.3) 100%); border-color: rgba(148, 163, 184, 0.15);";
                            $badgeStyle = "bg-slate-100 text-slate-700 border border-slate-200/50";
                            $dotColor = "bg-slate-400";
                            $textHeading = "text-slate-800";
                            $revenueText = "text-slate-700";
                            $priceText = "text-slate-800";
                        } elseif ($perf['price'] > 500) {
                            $cardStyle = "background: linear-gradient(135deg, rgba(139, 92, 246, 0.08) 0%, rgba(237, 233, 254, 0.35) 100%); border-color: rgba(139, 92, 246, 0.2);";
                            $badgeStyle = "bg-violet-100/60 text-violet-700 border border-violet-200/50";
                            $dotColor = "bg-violet-500";
                            $textHeading = "text-violet-950";
                            $revenueText = "text-violet-700";
                            $priceText = "text-violet-900";
                        } else {
                            $cardStyle = "background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(209, 250, 229, 0.35) 100%); border-color: rgba(16, 185, 129, 0.2);";
                            $badgeStyle = "bg-emerald-100/60 text-emerald-700 border border-emerald-200/50";
                            $dotColor = "bg-emerald-500";
                            $textHeading = "text-emerald-950";
                            $revenueText = "text-emerald-700";
                            $priceText = "text-emerald-900";
                        }
                    @endphp
                    <div class="border rounded-xl p-3 shadow-sm flex flex-col justify-between transition-all hover:shadow-md hover:scale-[1.01]"
                        style="{{ $cardStyle }}">
                        <div>
                            <!-- Card Header -->
                            <div class="flex justify-between items-start">
                                <div>
                                    <!-- <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $badgeStyle }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                        {{ $isPaid ? 'Paid Plan' : 'Free Entry' }}
                                    </span> -->
                                    <h3 class="text-xs font-black mt-1 {{ $textHeading }}">{{ $perf['name'] }}</h3>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="text-sm font-black {{ $priceText }}">₹{{ number_format($perf['price'], 2) }}</span>
                                    <span
                                        class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">{{ $perf['billing_period'] }}</span>
                                </div>
                            </div>

                            <!-- Metrics Details -->
                            <div
                                class="flex justify-between items-center text-xs py-1.5 border-t border-slate-500/10 mt-2 mb-1.5">
                                <span class="text-slate-500 font-medium">Licenses:</span>
                                <span
                                    class="px-1.5 py-0.5 rounded bg-white/60 border border-slate-200/40 font-bold font-mono text-slate-800">{{ $perf['shops_count'] }}
                                    {{ Str::plural('Shop', $perf['shops_count']) }}</span>
                            </div>
                        </div>

                        <!-- Cumulative Revenue Footer -->
                        <div class="pt-1.5 border-t border-slate-500/10 flex justify-between items-end">
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Revenue</span>
                            <span class="text-sm font-black {{ $revenueText }}">
                                ₹{{ number_format($perf['total_revenue'], 2) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Payments List -->
        <div class="space-y-2">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Recent Subscriptions Transactions
            </h2>

            <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Tenant Shop</th>
                                <th class="px-6 py-4">Sub Plan</th>
                                <th class="px-6 py-4 text-right">Amount Collected</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                            @forelse($recentPayments as $payment)
                                <tr class="hover:bg-secondary/10 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs text-slate-500">
                                        {{ $payment->payment_date->format('Y-m-d ') }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-white">
                                        {{ $payment->shop->name }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-400">
                                        {{ $payment->plan->name }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-white">
                                        ₹{{ number_format($payment->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500 italic">No payments collected
                                        recently.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($recentPayments->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $recentPayments->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
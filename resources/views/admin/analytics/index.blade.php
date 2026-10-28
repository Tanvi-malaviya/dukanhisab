@extends('layouts.admin')

@section('title', 'Product Analytics & Insights')
@section('page_title', 'Product Analytics & Insights')

@section('content')
<div class="space-y-4">

    @php
        $ranges = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            '7d' => 'Last 7D',
            '30d' => 'Last 30D',
            'this_month' => 'This Month',
        ];
    @endphp

    <!-- Compact Top Control Bar: Active Filter Period, Switcher & Export -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white border border-slate-200 px-4 py-2.5 rounded-xl shadow-2xs">
        <!-- Active Filter Badge -->
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-primary animate-pulse shrink-0"></span>
            <span class="text-xs font-semibold text-slate-500">Period:</span>
            <span class="text-xs font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200 flex items-center gap-1.5">
                <span class="text-primary">{{ $ranges[$range] ?? $range }}</span>
                <span class="text-slate-300">|</span>
                <span class="text-slate-600 font-medium">{{ $metrics['startDate'] }} – {{ $metrics['endDate'] }}</span>
            </span>
        </div>

        <!-- Range Switcher & Export -->
        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex rounded-lg p-0.5 bg-slate-100 border border-slate-200">
                @foreach ($ranges as $key => $lbl)
                    <a href="{{ route('admin.analytics.index', ['tab' => $tab, 'range' => $key]) }}"
                        class="px-2.5 py-1 text-xs font-bold rounded-md transition-all {{ $range === $key ? 'bg-primary text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}">
                        {{ $lbl }}
                    </a>
                @endforeach
            </div>

            <a href="{{ route('admin.analytics.export', ['range' => $range, 'from' => $from, 'to' => $to]) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-lg bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 transition shadow-2xs">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Analytics Navigation Tabs (No horizontal scrollbar track) -->
    <div class="border-b border-slate-200 flex overflow-x-auto gap-1 pb-px text-xs font-semibold [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
        @php
            $tabs = [
                'overview' => ['Overview & KPIs', 'M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z'],
                'users' => ['Users & Retention', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                'features' => ['Feature Usage', 'M13 10V3L4 14h7v7l9-11h-7z'],
                'funnels' => ['Funnels & Onboarding', 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z'],
                'subscriptions' => ['Subscriptions & Revenue', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                'whatsapp' => ['WhatsApp Analytics', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            ];
        @endphp

        @foreach ($tabs as $key => [$label, $iconSvg])
            <a href="{{ route('admin.analytics.index', ['tab' => $key, 'range' => $range]) }}"
                class="flex items-center gap-1.5 px-3 py-2 border-b-2 font-bold text-xs transition whitespace-nowrap {{ $tab === $key ? 'border-primary text-primary bg-primary/10 rounded-t-lg' : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300' }}">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconSvg }}"></path>
                </svg>
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: OVERVIEW & KEY METRICS             -->
    <!-- ========================================== -->
    @if ($tab === 'overview')
    <div class="space-y-4">

        <!-- 4 Primary Product Analytics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Active Users in Selected Range (Hero Card) -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        @if ($range === 'today')
                            DAU (Today's Active)
                        @elseif ($range === 'yesterday')
                            Active Users (Yesterday)
                        @elseif ($range === '7d')
                            WAU (Last 7D)
                        @elseif ($range === '30d')
                            MAU (Last 30D)
                        @else
                            Active Users ({{ $ranges[$range] ?? $range }})
                        @endif
                    </span>
                    <span class="w-2 h-2 rounded-full {{ $range === 'today' ? 'bg-emerald-500 animate-pulse' : 'bg-primary' }}"></span>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <h2 class="text-2xl font-black text-slate-900">{{ number_format($metrics['periodActiveCount']) }}</h2>
                    <span class="text-[11px] font-bold {{ $metrics['periodActiveChangePct'] >= 0 ? 'text-emerald-700 bg-emerald-50' : 'text-rose-700 bg-rose-50' }} px-1.5 py-0.5 rounded">
                        {{ $metrics['periodActiveChangePct'] >= 0 ? '+' : '' }}{{ $metrics['periodActiveChangePct'] }}% vs prev
                    </span>
                </div>
            </div>

            <!-- WAU -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">WAU (7 Days)</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <h2 class="text-2xl font-black text-slate-900">{{ number_format($metrics['wau']) }}</h2>
                    <span class="text-[11px] text-slate-500 font-medium">weekly active</span>
                </div>
            </div>

            <!-- MAU -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">MAU (30 Days)</span>
                    <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <h2 class="text-2xl font-black text-slate-900">{{ number_format($metrics['mau']) }}</h2>
                    <span class="text-[11px] text-slate-500 font-medium">monthly active</span>
                </div>
            </div>

            <!-- Stickiness Ratio -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Stickiness (DAU/MAU)</span>
                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">Goal &gt; 20%</span>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <h2 class="text-2xl font-black text-slate-900">{{ $metrics['stickiness'] }}%</h2>
                    <span class="text-[11px] text-slate-500 font-medium">daily return</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-2.5 overflow-hidden border border-slate-200">
                    <div class="bg-gradient-to-r from-amber-500 to-emerald-500 h-1 rounded-full" style="width: {{ min(100, $metrics['stickiness'] * 2.5) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Secondary KPIs Grid -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="bg-white border border-slate-200 p-3 rounded-xl shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Users</div>
                <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($metrics['totalUsers']) }}</div>
                <div class="text-[10px] text-emerald-700 font-semibold mt-0.5">+{{ $metrics['newUsersInPeriod'] }} in range</div>
            </div>

            <div class="bg-white border border-slate-200 p-3 rounded-xl shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 flex items-center justify-between">
                    <span>
                        @if ($range === 'today')
                            Logins Today
                        @elseif ($range === 'yesterday')
                            Logins Yesterday
                        @else
                            Logins ({{ $ranges[$range] ?? $range }})
                        @endif
                    </span>
                    @if ($metrics['periodNewLoginsCount'] > 0)
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    @endif
                </div>
                <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($metrics['periodLoginsCount']) }}</div>
                <div class="text-[10px] text-emerald-700 font-semibold mt-0.5">
                    @if ($range === 'today')
                        +{{ $metrics['periodNewLoginsCount'] }} new logins
                    @else
                        +{{ $metrics['periodNewLoginsCount'] }} new signups
                    @endif
                </div>
            </div>

            <div class="bg-white border border-slate-200 p-3 rounded-xl shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Shops</div>
                <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($metrics['totalShops']) }}</div>
                <div class="text-[10px] text-slate-500 font-medium mt-0.5">+{{ $metrics['newShopsInPeriod'] }} new shops</div>
            </div>

            <div class="bg-white border border-slate-200 p-3 rounded-xl shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Paid Conversion</div>
                <div class="text-xl font-black text-primary mt-1">{{ $metrics['paidConversionRate'] }}%</div>
                <div class="text-[10px] text-slate-500 font-medium mt-0.5">{{ $metrics['paidUsersCount'] }} paid / {{ $metrics['freeUsersCount'] }} free</div>
            </div>

            <div class="bg-white border border-slate-200 p-3 rounded-xl shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Period Revenue</div>
                <div class="text-xl font-black text-emerald-600 mt-1">₹{{ number_format($metrics['periodRevenue'], 2) }}</div>
                <div class="text-[10px] text-slate-500 font-medium mt-0.5">Gross: ₹{{ number_format($metrics['totalGrossRevenue'], 2) }}</div>
            </div>
        </div>

        <!-- Main Analytics Trend Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Daily Active Users & Signups Trend (14 Days) -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <div class="flex items-center justify-between mb-2 pb-1.5 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-800">Daily Active Users & New Signups</h3>
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-primary/10 text-primary border border-primary/20 rounded-full">14 Days</span>
                </div>
                <div id="chart-dau-signups" style="min-height: 240px;"></div>
            </div>

            <!-- Sales Invoices & Revenue Trend -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <div class="flex items-center justify-between mb-2 pb-1.5 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-800">Daily Invoices Created</h3>
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">Sales Trend</span>
                </div>
                <div id="chart-sales-revenue" style="min-height: 240px;"></div>
            </div>
        </div>

        <!-- Recent User Logins & Live Sessions -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="px-4 py-2.5 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-2 bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                        Recent User Logins & Activity
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span> Live
                        </span>
                    </h3>
                </div>
                <div class="text-xs text-slate-500 font-medium">
                    <span class="font-bold text-slate-900 font-mono">{{ $metrics['periodLoginsCount'] }}</span>
                    @if ($range === 'today')
                        logins recorded today
                    @elseif ($range === 'yesterday')
                        logins recorded yesterday
                    @else
                        logins in selected range
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-600 font-bold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2.5 whitespace-nowrap">User</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Shop</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Login Status</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Login Time</th>
                            <th class="px-3 py-2.5 text-right whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($metrics['recentLogins'] as $login)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0 border border-primary/20">
                                        {{ strtoupper(substr($login['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                            <span>{{ $login['name'] }}</span>
                                            @if ($login['is_first_login'])
                                                <span class="inline-flex items-center px-1.5 py-0.5 text-[9px] font-black rounded bg-emerald-100 text-emerald-800 border border-emerald-300 leading-none whitespace-nowrap">NEW</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $login['contact'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2.5 text-slate-700 font-medium whitespace-nowrap">
                                {{ $login['shop'] }}
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                @if ($login['is_first_login'])
                                    <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                        ✨ New Person Login
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full font-medium bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap">
                                        Returning Login
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="text-xs font-semibold text-slate-800 flex items-center gap-1">
                                    @if ($login['is_today'])
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    @endif
                                    {{ $login['logged_at'] }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $login['logged_at_exact'] }}</div>
                            </td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                <a href="{{ route('admin.users.show', $login['id']) }}" class="text-xs font-bold text-primary hover:underline">
                                    View &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500 font-medium">No recent logins recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <!-- User Status Breakdown -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <h4 class="text-xs font-bold text-slate-800 mb-2.5">User Health & Retention</h4>
                <div class="space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="flex items-center gap-1.5 text-slate-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active (Last 7D)
                        </span>
                        <span class="font-bold text-slate-900 font-mono">{{ $metrics['activeUsersCount'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="flex items-center gap-1.5 text-slate-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Dormant (8-30D)
                        </span>
                        <span class="font-bold text-slate-900 font-mono">{{ $metrics['dormantUsersCount'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="flex items-center gap-1.5 text-slate-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span> Inactive (&gt;30D)
                        </span>
                        <span class="font-bold text-slate-900 font-mono">{{ $metrics['inactiveUsersCount'] }}</span>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Health -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <h4 class="text-xs font-bold text-slate-800 mb-2.5">WhatsApp Notification Delivery</h4>
                <div class="space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-600 font-medium">Total Dispatched:</span>
                        <span class="font-bold text-slate-900 font-mono">{{ number_format($metrics['waSent']) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-600 font-medium">Delivered Rate:</span>
                        <span class="font-bold text-emerald-600 font-mono">{{ $metrics['waDeliveryRate'] }}%</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-600 font-medium">Failed Messages:</span>
                        <span class="font-bold text-rose-600 font-mono">{{ $metrics['waFailed'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Feature Usage Snapshot -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <h4 class="text-xs font-bold text-slate-800 mb-2.5">Feature Usage in Period</h4>
                <div class="space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-600 font-medium">Sales Invoices:</span>
                        <span class="font-bold text-slate-900 font-mono">{{ number_format($metrics['salesCount']) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-600 font-medium">Purchases Recorded:</span>
                        <span class="font-bold text-slate-900 font-mono">{{ number_format($metrics['purchasesCount']) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-600 font-medium">Cashbook Entries:</span>
                        <span class="font-bold text-slate-900 font-mono">{{ number_format($metrics['cashBookCount']) }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 2: USERS & RETENTION                  -->
    <!-- ========================================== -->
    @if ($tab === 'users')
    <div class="space-y-4">

        <!-- Retention Benchmarks Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Day 1 (D1) Retention</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $metrics['d1Retention'] }}%</div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2.5 overflow-hidden border border-slate-200">
                    <div class="bg-primary h-1.5 rounded-full" style="width: {{ $metrics['d1Retention'] }}%"></div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Day 7 (D7) Retention</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $metrics['d7Retention'] }}%</div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2.5 overflow-hidden border border-slate-200">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $metrics['d7Retention'] }}%"></div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Day 30 (D30) Retention</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $metrics['d30Retention'] }}%</div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2.5 overflow-hidden border border-slate-200">
                    <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $metrics['d30Retention'] }}%"></div>
                </div>
            </div>
        </div>

        <!-- User Activity Explorer Table -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="px-4 py-2.5 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-2 bg-slate-50/50">
                <h3 class="text-xs font-bold text-slate-800">User Activity Explorer</h3>
                <div class="text-xs text-slate-500 font-medium">
                    Latest {{ count($metrics['usersExplorer']) }} accounts
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[980px] w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-600 font-bold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2.5 whitespace-nowrap">User</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Primary Shop</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Plan</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Sales</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Purchases</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Products</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Status</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Last Login</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Last Active</th>
                            <th class="px-3 py-2.5 text-right whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($metrics['usersExplorer'] as $u)
                        @php
                            $isFreePlan = stripos($u['plan'], 'free') !== false;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0 border border-primary/20">
                                        {{ strtoupper(substr($u['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                            <span>{{ $u['name'] }}</span>
                                            @if ($u['is_new_user'])
                                                <span class="inline-flex items-center px-1.5 py-0.5 text-[9px] font-black rounded bg-emerald-100 text-emerald-800 border border-emerald-300 leading-none whitespace-nowrap">NEW</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $u['phone'] ?: $u['email'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-semibold text-slate-700">{{ $u['primary_shop'] }}</span>
                                    @if ($u['shops_count'] > 1)
                                        <span class="inline-flex items-center text-[10px] bg-slate-100 text-slate-600 font-bold px-1.5 py-0.5 rounded border border-slate-200 shrink-0">+{{ $u['shops_count'] - 1 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                @if (!$isFreePlan)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-teal-50 text-teal-800 border border-teal-200 whitespace-nowrap shadow-2xs">
                                        {{ $u['plan'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium text-[11px] bg-slate-100 text-slate-600 border border-slate-200 whitespace-nowrap">
                                        {{ $u['plan'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <span class="inline-block min-w-[24px] px-1.5 py-0.5 font-mono font-bold text-xs rounded {{ $u['sales_count'] > 0 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'text-slate-400' }}">
                                    {{ $u['sales_count'] }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <span class="inline-block min-w-[24px] px-1.5 py-0.5 font-mono font-bold text-xs rounded {{ $u['purchases_count'] > 0 ? 'bg-blue-50 text-blue-800 border border-blue-200' : 'text-slate-400' }}">
                                    {{ $u['purchases_count'] }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <span class="inline-block min-w-[24px] px-1.5 py-0.5 font-mono font-bold text-xs rounded {{ $u['products_count'] > 0 ? 'bg-slate-100 text-slate-800 border border-slate-200' : 'text-slate-400' }}">
                                    {{ $u['products_count'] }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                @if ($u['status'] === 'Active')
                                    <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-bold bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200 whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                @elseif ($u['status'] === 'Dormant')
                                    <span class="inline-flex items-center gap-1 text-[11px] text-amber-700 font-bold bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200 whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Dormant
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-600 font-semibold bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200 whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="text-xs font-semibold text-slate-800 flex items-center gap-1">
                                    @if ($u['logged_in_today'])
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    @endif
                                    {{ $u['last_login_at'] }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $u['last_login_exact'] }}</div>
                            </td>
                            <td class="px-3 py-2.5 text-xs text-slate-600 font-medium whitespace-nowrap">{{ $u['last_active_at'] }}</td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                <a href="{{ route('admin.users.show', $u['id']) }}" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:text-primary-dark transition hover:underline">
                                    View &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-4 py-6 text-center text-slate-500 font-medium">No users found for this filter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 3: FEATURE USAGE                      -->
    <!-- ========================================== -->
    @if ($tab === 'features')
    <div class="space-y-4">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Sales -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <h4 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sales Invoices</h4>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($metrics['salesCount']) }}</div>
                <div class="text-xs text-emerald-700 font-semibold mt-0.5">₹{{ number_format($metrics['salesVolume'], 2) }} volume</div>
                <div class="text-[10px] text-slate-500 mt-1">Avg ticket: ₹{{ number_format($metrics['avgSaleTicket'], 2) }}</div>
            </div>

            <!-- Purchases -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <h4 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Purchase Entries</h4>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($metrics['purchasesCount']) }}</div>
                <div class="text-xs text-blue-700 font-semibold mt-0.5">₹{{ number_format($metrics['purchasesVolume'], 2) }} cost</div>
                <div class="text-[10px] text-slate-500 mt-1">Supplier bills & restocks</div>
            </div>

            <!-- Cashbook -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <h4 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Cashbook & Expenses</h4>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($metrics['cashBookCount']) }}</div>
                <div class="text-xs text-violet-700 font-semibold mt-0.5">Cash In & Out entries</div>
                <div class="text-[10px] text-slate-500 mt-1">Daily register ledger</div>
            </div>

            <!-- Products -->
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <h4 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Products & Stock</h4>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($metrics['totalProducts']) }}</div>
                <div class="text-xs text-amber-700 font-semibold mt-0.5">+{{ $metrics['productsCreated'] }} added in period</div>
                <div class="text-[10px] text-slate-500 mt-1">Active catalog items</div>
            </div>
        </div>

        <!-- WhatsApp Integration Stats -->
        <div class="bg-white border border-slate-200 p-4 rounded-xl shadow-2xs">
            <h3 class="text-xs font-bold text-slate-800 mb-3">WhatsApp Engine Utilization</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Sent</div>
                    <div class="text-xl font-black text-slate-900 mt-0.5">{{ number_format($metrics['waSent']) }}</div>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Delivered Rate</div>
                    <div class="text-xl font-black text-emerald-700 mt-0.5">{{ number_format($metrics['waDelivered']) }} ({{ $metrics['waDeliveryRate'] }}%)</div>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Failed / Rejected</div>
                    <div class="text-xl font-black text-rose-700 mt-0.5">{{ number_format($metrics['waFailed']) }}</div>
                </div>
            </div>
        </div>

    </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 4: FUNNELS & ONBOARDING               -->
    <!-- ========================================== -->
    @if ($tab === 'funnels')
    <div class="space-y-4">

        <div class="bg-white border border-slate-200 p-4 rounded-xl shadow-2xs">
            <h3 class="text-xs font-bold text-slate-800 mb-3 pb-2 border-b border-slate-100">Onboarding & Activation Funnel</h3>

            <div class="space-y-3">
                @foreach ($metrics['funnel'] as $step)
                <div class="bg-slate-50/80 border border-slate-200 p-3 rounded-lg relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1.5">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-md bg-primary/10 text-primary font-bold text-xs flex items-center justify-center border border-primary/20 shrink-0">
                                {{ $step['step'] }}
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">{{ $step['name'] }}</h4>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 text-right">
                            <div>
                                <span class="text-sm font-black text-slate-900 font-mono">{{ number_format($step['count']) }}</span>
                                <span class="text-[11px] text-slate-500">users</span>
                            </div>
                            <div class="min-w-[70px]">
                                <span class="text-xs font-bold text-primary font-mono">{{ $step['conversion_pct'] }}%</span>
                            </div>
                            @if ($step['drop_off_pct'] > 0)
                                <div class="text-xs text-rose-700 font-bold min-w-[65px]">
                                    -{{ $step['drop_off_pct'] }}%
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Visual Progress Bar -->
                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-primary to-emerald-500 h-1.5 rounded-full transition-all duration-500"
                            style="width: {{ $step['conversion_pct'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 5: SUBSCRIPTIONS & REVENUE            -->
    <!-- ========================================== -->
    @if ($tab === 'subscriptions')
    <div class="space-y-4">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Free vs Paid Distribution</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $metrics['paidUsersCount'] }} / {{ $metrics['totalUsers'] }}</div>
                <div class="text-xs text-primary font-bold mt-0.5">{{ $metrics['paidConversionRate'] }}% conversion</div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2.5 overflow-hidden border border-slate-200">
                    <div class="bg-primary h-1.5 rounded-full" style="width: {{ $metrics['paidConversionRate'] }}%"></div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Period Collections</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">₹{{ number_format($metrics['periodRevenue'], 2) }}</div>
                <div class="text-xs text-slate-600 font-semibold mt-0.5">{{ $metrics['revenueChangePct'] >= 0 ? '+' : '' }}{{ $metrics['revenueChangePct'] }}% vs prev</div>
            </div>

            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Lifetime Gross Revenue</span>
                <div class="text-2xl font-black text-slate-900 mt-1">₹{{ number_format($metrics['totalGrossRevenue'], 2) }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Verified payments</div>
            </div>
        </div>

        <!-- Plan Breakdown -->
        <div class="bg-white border border-slate-200 p-4 rounded-xl shadow-2xs">
            <h3 class="text-xs font-bold text-slate-800 mb-3 pb-2 border-b border-slate-100">Subscription Plan Distribution</h3>
            <div class="space-y-3">
                @foreach ($metrics['plansDistribution'] as $plan)
                @php
                    $pct = $metrics['totalUsers'] > 0 ? round(($plan['users'] / $metrics['totalUsers']) * 100, 1) : 0;
                @endphp
                <div>
                    <div class="flex justify-between items-center text-xs font-semibold mb-1">
                        <span class="text-slate-800 font-bold">{{ $plan['name'] }} (₹{{ number_format($plan['price']) }})</span>
                        <span class="text-slate-600 font-mono">{{ $plan['users'] }} users ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                        <div class="bg-primary h-2 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
    @endif

    <!-- ========================================== -->
    <!-- TAB 6: WHATSAPP ANALYTICS                 -->
    <!-- ========================================== -->
    @if ($tab === 'whatsapp')
    <div class="space-y-4">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Messages Sent</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($metrics['waSent']) }}</div>
            </div>

            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Delivered</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($metrics['waDelivered']) }}</div>
                <div class="text-[11px] text-emerald-700 font-semibold mt-0.5">{{ $metrics['waDeliveryRate'] }}% rate</div>
            </div>

            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Failed</span>
                <div class="text-2xl font-black text-rose-600 mt-1">{{ number_format($metrics['waFailed']) }}</div>
            </div>

            <div class="bg-white border border-slate-200 p-3.5 rounded-xl shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Delivery Rate</span>
                <div class="text-2xl font-black text-primary mt-1">{{ $metrics['waDeliveryRate'] }}%</div>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-2 overflow-hidden border border-slate-200">
                    <div class="bg-emerald-500 h-1 rounded-full" style="width: {{ $metrics['waDeliveryRate'] }}%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 p-4 rounded-xl shadow-2xs">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-800">WhatsApp Integration Management</h4>
                <a href="{{ route('admin.whatsapp.usage') }}" class="text-xs text-primary font-bold hover:underline">
                    View Ledgers & Packs &rarr;
                </a>
            </div>
        </div>

    </div>
    @endif

</div>

<!-- ApexCharts Script for Overview Charts (Light Theme Configured) -->
@if ($tab === 'overview')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const dates = @json($metrics['trendDates']);
    const dau = @json($metrics['dauTrend']);
    const signups = @json($metrics['signupsTrend']);
    const sales = @json($metrics['salesTrend']);

    const commonOptions = {
        theme: { mode: 'light' },
        chart: {
            background: 'transparent',
            toolbar: { show: false },
            zoom: { enabled: false },
            fontFamily: 'Plus Jakarta Sans, Outfit, sans-serif'
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4
        },
        tooltip: {
            theme: 'light'
        }
    };

    // Chart 1: DAU & Signups
    const dauChartEl = document.querySelector("#chart-dau-signups");
    if (dauChartEl) {
        new ApexCharts(dauChartEl, {
            ...commonOptions,
            chart: { ...commonOptions.chart, type: 'area', height: 280 },
            series: [
                { name: 'Active Users (DAU)', data: dau },
                { name: 'New Signups', data: signups }
            ],
            colors: ['#0F766E', '#6366F1'],
            stroke: { curve: 'smooth', width: 2.5 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 95, 100]
                }
            },
            xaxis: {
                categories: dates,
                labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } }
            }
        }).render();
    }

    // Chart 2: Sales & Invoices
    const salesChartEl = document.querySelector("#chart-sales-revenue");
    if (salesChartEl) {
        new ApexCharts(salesChartEl, {
            ...commonOptions,
            chart: { ...commonOptions.chart, type: 'bar', height: 280 },
            series: [
                { name: 'Invoices Created', data: sales }
            ],
            colors: ['#0284C7'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '45%'
                }
            },
            xaxis: {
                categories: dates,
                labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } }
            }
        }).render();
    }
});
</script>
@endif

@endsection

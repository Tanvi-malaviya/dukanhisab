@extends('layouts.admin')

@section('title', 'Platform Dashboard')
@section('page_title', 'Dashboard ')

@section('content')
<div class="space-y-2">
    
    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
        
        <!-- Total Users -->
        <div class="card-purchase border p-6 rounded-2xl flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Users</p>
                <h3 class="text-3xl font-bold text-white mt-2">{{ number_format($totalUsers) }}</h3>
                <span class="inline-flex items-center text-[10px] text-primary font-medium mt-2 bg-primary/10 px-2 py-0.5 rounded">
                    +12.3% this week
                </span>
            </div>
            <span class="p-3 card-icon rounded-xl border border-blue-200 shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </span>
        </div>

        <!-- Active Users -->
        <div class="card-sales border p-6 rounded-2xl flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Active Users</p>
                <h3 class="text-3xl font-bold text-white mt-2">{{ number_format($activeUsers) }}</h3>
                <span class="inline-flex items-center text-[10px] text-info font-medium mt-2 bg-info/10 px-2 py-0.5 rounded">
                    {{ round(($activeUsers / max(1, $totalUsers)) * 100) }}% Active Ratio
                </span>
            </div>
            <span class="p-3 card-icon rounded-xl border border-green-200 shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </span>
        </div>

        <!-- Premium Users -->
        <div class="card-customer-due border p-6 rounded-2xl flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Premium Users</p>
                <h3 class="text-3xl font-bold text-white mt-2">{{ number_format($premiumUsers) }}</h3>
                <span class="inline-flex items-center text-[10px] text-warning font-medium mt-2 bg-warning/10 px-2 py-0.5 rounded">
                    SaaS MRR Growth
                </span>
            </div>
            <span class="p-3 card-icon rounded-xl border border-amber-200 shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </span>
        </div>

        <!-- Total Shops -->
        <div class="card-supplier-due border p-6 rounded-2xl flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Shops</p>
                <h3 class="text-3xl font-bold text-white mt-2">{{ number_format($totalShops) }}</h3>
                <span class="inline-flex items-center text-[10px] text-success font-medium mt-2 bg-success/10 px-2 py-0.5 rounded">
                    Platform Tenants
                </span>
            </div>
            <span class="p-3 card-icon rounded-xl border border-red-200 shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </span>
        </div>

    </div>

    <!-- Revenue Grid Cards -->
    <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
        <!-- Today's Revenue -->
        <div class="card-cash border p-6 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Revenue</p>
                <h2 class="text-3xl font-bold text-white mt-2">₹{{ number_format($todayRevenue, 2) }}</h2>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                    Real-time transactions
                </div>
            </div>
            <span class="p-3 card-icon rounded-xl border border-teal-200 shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </span>
        </div>

        <!-- Monthly Revenue -->
        <div class="card-bank border p-6 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Monthly Revenue (MRR)</p>
                <h2 class="text-3xl font-bold text-white mt-2">₹{{ number_format($monthlyRevenue, 2) }}</h2>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-info"></span>
                    Current billing cycle
                </div>
            </div>
            <span class="p-3 card-icon rounded-xl border border-sky-200 shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </span>
        </div>

        <!-- Active Devices -->
        <div class="card-purchase border p-6 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Device Sessions</p>
                <h2 class="text-3xl font-bold text-white mt-2">{{ number_format($activeDevices) }}</h2>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-warning"></span>
                    Mobile apps & browser nodes
                </div>
            </div>
            <span class="p-3 card-icon rounded-xl border border-blue-200 shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </span>
        </div>
    </div>

    <!-- Charts Grid Section -->
    <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
        
        <!-- Chart 1: Monthly Revenue Graph -->
        <div class="bg-card-dark border border-border-dark p-6 rounded-2xl shadow-sm">
            <h3 class="text-sm font-semibold text-white mb-4">Monthly Revenue Graph (INR)</h3>
            <div id="monthly-revenue-chart"></div>
        </div>

        <!-- Chart 2: Premium Subscription Sales -->
        <div class="bg-card-dark border border-border-dark p-6 rounded-2xl shadow-sm">
            <h3 class="text-sm font-semibold text-white mb-4">Premium Subscription Sales (INR)</h3>
            <div id="premium-sales-chart"></div>
        </div>

        <!-- Chart 3: Daily User Registrations -->
        <div class="bg-card-dark border border-border-dark p-6 rounded-2xl shadow-sm">
            <h3 class="text-sm font-semibold text-white mb-4">Daily User Registrations</h3>
            <div id="daily-registrations-chart"></div>
        </div>

        <!-- Chart 4: Active Users Analytics -->
        <div class="bg-card-dark border border-border-dark p-6 rounded-2xl shadow-sm">
            <h3 class="text-sm font-semibold text-white mb-4">Active Users Hourly Analytics</h3>
            <div id="active-users-chart"></div>
        </div>

    </div>

</div>

<!-- Interactive ApexCharts Render Engine -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        
        // General Chart styles overriding for ApexCharts Light theme integration
        const chartThemeOptions = {
            theme: {
                mode: 'light',
                palette: 'palette1'
            },
            grid: {
                borderColor: '#e2e8f0',
                strokeDashArray: 4
            },
            legend: {
                labels: {
                    colors: '#475569'
                }
            }
        };

        // 1. Monthly Revenue Chart (Bar)
        var monthlyRevOptions = {
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                background: 'transparent'
            },
            series: [{
                name: 'Monthly Revenue',
                data: @json($monthlyRevValues)
            }],
            colors: ['#10b981'], // Emerald
            xaxis: {
                categories: @json($monthlyRevLabels),
                labels: { style: { colors: '#475569' } }
            },
            yaxis: {
                labels: { style: { colors: '#475569' } }
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '40%'
                }
            },
            dataLabels: { enabled: false },
            ...chartThemeOptions
        };
        new ApexCharts(document.querySelector("#monthly-revenue-chart"), monthlyRevOptions).render();

        // 2. Premium Sales Chart (Area)
        var premiumSalesOptions = {
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: false },
                background: 'transparent'
            },
            series: [{
                name: 'Sales Revenue',
                data: @json($dailySalesValues)
            }],
            colors: ['#3b82f6'], // Info Blue
            xaxis: {
                categories: @json($dailyRegsLabels),
                labels: { style: { colors: '#475569' } }
            },
            yaxis: {
                labels: { style: { colors: '#475569' } }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 100]
                }
            },
            ...chartThemeOptions
        };
        new ApexCharts(document.querySelector("#premium-sales-chart"), premiumSalesOptions).render();

        // 3. Daily Registrations (Line)
        var dailyRegOptions = {
            chart: {
                type: 'line',
                height: 300,
                toolbar: { show: false },
                background: 'transparent'
            },
            series: [{
                name: 'Signups',
                data: @json($dailyRegsValues)
            }],
            colors: ['#f59e0b'], // Warning Amber
            xaxis: {
                categories: @json($dailyRegsLabels),
                labels: { style: { colors: '#475569' } }
            },
            yaxis: {
                labels: { style: { colors: '#475569' } }
            },
            stroke: { width: 3, curve: 'smooth' },
            dataLabels: { enabled: false },
            markers: { size: 4, colors: ['#f59e0b'], strokeColors: '#111827', strokeWidth: 2 },
            ...chartThemeOptions
        };
        new ApexCharts(document.querySelector("#daily-registrations-chart"), dailyRegOptions).render();

        // 4. Active Users Analytics (Area)
        var activeUsersOptions = {
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: false },
                background: 'transparent'
            },
            series: [{
                name: 'Active Users',
                data: @json($activeAnalyticsValues)
            }],
            colors: ['#10b981'],
            xaxis: {
                categories: @json($dailyRegsLabels),
                labels: { style: { colors: '#475569' } }
            },
            yaxis: {
                labels: { style: { colors: '#475569' } }
            },
            stroke: { curve: 'straight', width: 2 },
            dataLabels: { enabled: false },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.02,
                }
            },
            ...chartThemeOptions
        };
        new ApexCharts(document.querySelector("#active-users-chart"), activeUsersOptions).render();

    });
</script>
@endsection

@extends('layouts.admin')

@section('title', 'User Details - ' . $user->name)
@section('page_title', 'User Profile')

@section('content')
    <div class="space-y-3">

        <!-- Breadcrumbs -->
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
            <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <a href="{{ route('admin.users.index') }}" class="hover:text-primary transition-colors">Users & Shops</a>
            <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-slate-700 font-semibold">{{ $user->name }}</span>
        </div>

        <!-- Page Header / Profile info -->
        <div
            class="bg-card-dark border border-border-dark p-4 sm:p-5 rounded-2xl shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left: Avatar, Details & Badges -->
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="relative shrink-0 flex-shrink-0" style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px;">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"
                            class="w-14 h-14 rounded-full object-cover border-2 border-primary/20 shadow-xs shrink-0 flex-shrink-0"
                            style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; border-radius: 50%; object-fit: cover;">
                    @else
                        <span
                            class="w-14 h-14 rounded-full bg-primary/10 text-primary border-2 border-primary/20 flex items-center justify-center font-extrabold text-lg uppercase shadow-xs shrink-0 flex-shrink-0"
                            style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; border-radius: 50%;">
                            {{ substr($user->name, 0, 2) }}
                        </span>
                    @endif
                    <span
                        class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full border-2 border-white {{ $user->status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"
                        style="width: 14px; height: 14px; border-radius: 50%;"
                        title="Status: {{ ucfirst($user->status) }}"></span>
                </div>

                <div class="space-y-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl font-bold tracking-tight text-slate-800 truncate" title="{{ $user->name }}">
                            {{ $user->name }}</h1>
                        <span
                            class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                        @if($user->activePlan)
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $user->activePlan->slug === 'premium' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                    </path>
                                </svg>
                                <span>{{ $user->activePlan->name }} Plan</span>
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-x-3.5 gap-y-0.5 text-xs text-slate-500 font-medium">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            {{ $user->email }}
                        </span>
                        <span class="flex items-center gap-1 font-mono">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            {{ $user->mobile ?? 'No Mobile' }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            Joined {{ $user->created_at->format('M d, Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Compact Profile Quick Actions -->
            <div class="flex flex-wrap items-center justify-start lg:justify-end gap-1.5 shrink-0 max-w-full lg:max-w-xl">
                <!-- Manage Plan -->
                <button type="button" onclick="openUserPlanModal()"
                    class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg text-xs font-bold transition-all flex items-center gap-1 cursor-pointer shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                        </path>
                    </svg>
                    <span>Manage Plan</span>
                </button>

                <!-- Login as User -->
                @if($user->status === 'active')
                    <a href="{{ route('admin.users.login_as', $user->id) }}"
                        class="px-2.5 py-1 bg-primary text-white hover:bg-primary/90 rounded-lg text-xs font-bold transition-all flex items-center gap-1 cursor-pointer shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"></path>
                        </svg>
                        <span>Login as User</span>
                    </a>
                @endif

                <!-- Edit User -->
                <button type="button"
                    onclick="openEditModal({{ json_encode($user->only(['id', 'name', 'email', 'mobile', 'status', 'avatar'])) }})"
                    class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer"
                    title="Edit User Details">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                    </svg>
                    <span>Edit</span>
                </button>

                <!-- Backup -->
                <a href="{{ route('admin.users.backup', $user->id) }}" download
                    class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer"
                    title="Download Backup JSON">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    <span>Backup</span>
                </a>

                <!-- Restore -->
                <button type="button" onclick="openRestoreModal()"
                    class="px-2 py-1 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer"
                    title="Restore JSON">
                    <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span>Restore</span>
                </button>

                <!-- Reset Password -->
                <button type="button" onclick="openPasswordModal({{ $user->id }}, '{{ $user->name }}')"
                    class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer"
                    title="Reset Password">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m-5 4v5m0 0l-2-2m2 2l2-2M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path>
                    </svg>
                    <span>Password</span>
                </button>

                <!-- Delete -->
                <button type="button"
                    onclick="confirmDelete('{{ route('admin.users.destroy', $user->id) }}', '{{ $user->name }}')"
                    class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer"
                    title="Delete User">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    <span>Delete</span>
                </button>
            </div>
        </div>

        <!-- COMPACT OVERALL USAGE TELEMETRY BAR -->
        <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-xs space-y-2.5">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 012 2h2a2 2 0 012-2z">
                        </path>
                    </svg>
                    Overall Usage Telemetry
                </h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
                <!-- Total Sales -->
                <div class="card-sales border p-2.5 rounded-xl flex items-center justify-between shadow-2xs">
                    <div>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Total Sales</p>
                        <p class="text-xs font-black text-slate-800 mt-0.5">₹{{ number_format($overallStats->total_sales) }}
                        </p>
                    </div>
                    <span class="p-1 card-icon rounded-lg shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </span>
                </div>

                <!-- Transactions -->
                <div class="card-purchase border p-2.5 rounded-xl flex items-center justify-between shadow-2xs">
                    <div>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Transactions</p>
                        <p class="text-xs font-black text-slate-800 mt-0.5">
                            {{ number_format($overallStats->total_transactions) }}
                        </p>
                    </div>
                    <span class="p-1 card-icon rounded-lg shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                    </span>
                </div>

                <!-- Cash Balance -->
                <div class="card-cash border p-2.5 rounded-xl flex items-center justify-between shadow-2xs">
                    <div>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Cash Balance</p>
                        <p class="text-xs font-black text-slate-800 mt-0.5">
                            ₹{{ number_format($overallStats->cash_balance) }}</p>
                    </div>
                    <span class="p-1 card-icon rounded-lg shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </span>
                </div>

                <!-- Bank Balance -->
                <div class="card-bank border p-2.5 rounded-xl flex items-center justify-between shadow-2xs">
                    <div>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Bank Balance</p>
                        <p class="text-xs font-black text-slate-800 mt-0.5">
                            ₹{{ number_format($overallStats->bank_balance) }}</p>
                    </div>
                    <span class="p-1 card-icon rounded-lg shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                            </path>
                        </svg>
                    </span>
                </div>

                <!-- Customer Due -->
                <div class="card-customer-due border p-2.5 rounded-xl flex items-center justify-between shadow-2xs">
                    <div>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Customer Due</p>
                        <p class="text-xs font-black text-slate-800 mt-0.5">
                            ₹{{ number_format($overallStats->customer_due) }}</p>
                    </div>
                    <span class="p-1 card-icon rounded-lg shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </span>
                </div>

                <!-- Supplier Due -->
                <div class="card-supplier-due border p-2.5 rounded-xl flex items-center justify-between shadow-2xs">
                    <div>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Supplier Due</p>
                        <p class="text-xs font-black text-slate-800 mt-0.5">
                            ₹{{ number_format($overallStats->supplier_due) }}</p>
                    </div>
                    <span class="p-1 card-icon rounded-lg shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </span>
                </div>
            </div>
        </div>
        <div class="bg-card-dark border border-border-dark rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                        Shops Directory
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Manage shops owned by {{ $user->name }}.
                    </p>
                </div>
                <button onclick="openAddShopModal(true)"
                    class="px-3.5 py-2 bg-primary text-white hover:bg-secondary-hover rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Shop for User
                </button>
            </div>

            @if($user->shops->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    @foreach($user->shops as $shop)
                        <div
                            class="bg-teal-50 border-teal-200/80 p-3.5 rounded-2xl space-y-2.5 relative hover:bg-teal-100/60 transition-colors flex flex-col justify-between">
                            <!-- Shop Header with Action Buttons at Top Right -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    @if($shop->logo)
                                        <img src="{{ asset('storage/' . $shop->logo) }}" alt="{{ $shop->name }}"
                                            class="w-8 h-8 rounded-xl object-cover border-teal-200 shrink-0 aspect-square">
                                    @else
                                        <span
                                            class="w-8 h-8 rounded-xl bg-primary/20 text-primary border-primary/30 flex items-center justify-center font-bold text-xs uppercase shrink-0 aspect-square">
                                            {{ substr($shop->name, 0, 2) }}
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-slate-800 text-xs truncate" title="{{ $shop->name }}">
                                            {{ $shop->name }}
                                        </h3>
                                        <p class="text-[9px] text-slate-500 font-mono truncate">GSTIN:
                                            {{ $shop->gst_number ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Buttons & Status at Top Right -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <span
                                        class="inline-flex px-1.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider shrink-0 {{ $shop->status === 'active' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                        {{ $shop->status }}
                                    </span>

                                    <!-- Edit Shop -->
                                    <button onclick="openEditShopModal(this)"
                                        data-shop="{{ json_encode($shop->only(['id', 'owner_id', 'name', 'email', 'mobile', 'address', 'gst_number', 'status', 'logo'])) }}"
                                        class="p-1 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-600 hover:text-white transition-colors cursor-pointer"
                                        title="Edit Shop">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>

                                    <!-- Toggle Status -->
                                    <form action="{{ route('admin.shops.toggle', $shop->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="p-1 rounded-lg {{ $shop->status === 'suspended' ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-600' : 'bg-rose-100 text-rose-700 hover:bg-rose-600' }} hover:text-white transition-colors cursor-pointer"
                                            title="{{ $shop->status === 'suspended' ? 'Activate Shop' : 'Suspend Shop' }}">
                                            @if($shop->status === 'suspended')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                                    </path>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Shop Contact Info -->
                            <div class="pt-2 text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold">Contact</span>
                                    <span class="text-slate-700 font-mono text-[11px] truncate">{{ $shop->mobile ?? $shop->email ?? 'N/A' }}</span>
                                </div>
                                @if($shop->address)
                                    <p class="text-[10px] text-slate-600 bg-teal-100/60 p-2 rounded-xl truncate"
                                        title="{{ $shop->address }}">
                                        📍 {{ $shop->address }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 border border-dashed border-border-dark rounded-xl bg-secondary/10 space-y-2">
                    <p class="text-slate-400 text-xs">This user does not have any active shop yet.</p>
                    <button onclick="openAddShopModal()"
                        class="px-3 py-1.5 bg-primary text-white hover:bg-secondary-hover rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 cursor-pointer shadow-sm">
                        + Add First Shop for User
                    </button>
                </div>
            @endif
        </div>



    </div>

    <!-- USER MODAL 1: Edit Details Dialog -->
    <div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
            <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Edit Client Details</h3>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="editForm" method="POST" enctype="multipart/form-data" class="p-5 space-y-3">
                @csrf

                @if($errors->any() && session('modal_open') === 'edit')
                    <div class="p-3.5 bg-danger/10 border border-danger/30 text-danger rounded-xl text-xs space-y-1">
                        <p class="font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            Failed to update user details:
                        </p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Name</label>
                        <input type="text" name="name" id="edit_name" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" id="edit_email" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Mobile Number</label>
                        <input type="text" name="mobile" id="edit_mobile" pattern="[0-9]{10}" maxlength="10"
                            title="Mobile number must be exactly 10 digits" placeholder="e.g. 9876543210"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Account Status</label>
                        <select name="status" id="edit_status" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Profile Image (Avatar)</label>
                    <div
                        class="flex items-center gap-4 p-3 bg-secondary/20 border border-dashed border-primary/40 hover:border-primary/80 rounded-xl transition-all">
                        <div id="edit_avatar_preview_container" class="flex-shrink-0">
                            <img id="edit_avatar_preview" src="" alt="Avatar"
                                class="w-12 h-12 rounded-full object-cover border border-border-dark hidden">
                            <span id="edit_avatar_placeholder"
                                class="w-12 h-12 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-sm uppercase">
                                ??
                            </span>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="avatar" id="edit_avatar" accept="image/*"
                                class="block w-full text-xs text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none"
                                onchange="previewEditAvatar(this)">
                            <p class="text-[10px] text-slate-500 mt-1">Accepts JPEG, PNG, JPG, GIF, WEBP. Max 2MB.</p>
                            <p id="edit_avatar_error" class="text-[10px] text-danger mt-1 hidden"></p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                    <x-button type="button" onclick="closeEditModal()" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="primary">Save Changes</x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- USER MODAL 2: Reset Password Dialog -->
    <div id="passwordModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closePasswordModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Reset Account Password</h3>
                <button onclick="closePasswordModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="passwordForm" method="POST" class="p-6 space-y-4">
                @csrf
                <p class="text-xs text-slate-400">Set a new password for <span class="text-white font-semibold"
                        id="pwd_user_name">User</span>.</p>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">New Password</label>
                    <input type="password" name="password" required placeholder="Minimum 8 characters"
                        class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required placeholder="Re-enter password"
                        class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                    <x-button type="button" onclick="closePasswordModal()" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="primary">Reset Password</x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- SHOP MODAL 1: Add New Shop -->
    <div id="addShopModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAddShopModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
            <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Add New Shop for {{ $user->name }}</h3>
                <button onclick="closeAddShopModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.shops.store') }}" method="POST" enctype="multipart/form-data"
                class="p-5 space-y-3">
                @csrf
                <input type="hidden" name="owner_id" value="{{ $user->id }}">

                @if($errors->any() && session('modal_open') === 'add_shop')
                    <div class="p-3.5 bg-danger/10 border border-danger/30 text-danger rounded-xl text-xs space-y-1">
                        <p class="font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            Failed to create shop:
                        </p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="e.g. DukanHisab Store"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Status</label>
                        <select name="status" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                            <option value="active" {{ old('status') === 'active' || !old('status') ? 'selected' : '' }}>Active
                            </option>
                            <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ $user->email }}"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Mobile Number</label>
                        <input type="text" name="mobile" value="{{ old('mobile') }}" pattern="[0-9]{10}" maxlength="10"
                            title="Mobile number must be exactly 10 digits"
                            placeholder="{{ $user->mobile ?? 'e.g. 9876543210' }}"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">GSTIN (GST Number)</label>
                    <input type="text" name="gst_number" value="{{ old('gst_number') }}" placeholder="e.g. 24AAAAA1121A1Z1"
                        class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Address</label>
                        <textarea name="address" rows="2"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white h-[58px] resize-none">{{ old('address') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Logo</label>
                        <div
                            class="flex items-center gap-3 p-2 bg-secondary/20 border border-dashed border-primary/40 hover:border-primary/80 rounded-xl transition-all h-[58px]">
                            <div class="flex-shrink-0">
                                <img id="add_logo_preview" src="" alt="Logo Preview"
                                    class="w-8 h-8 rounded-lg object-cover border border-border-dark hidden">
                                <span id="add_logo_placeholder"
                                    class="w-8 h-8 rounded-lg bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-xs">+</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <input type="file" name="logo" id="add_logo" accept="image/*"
                                    class="block w-full text-[11px] text-slate-400 file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none"
                                    onchange="previewAddLogo(this)">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                    <x-button type="button" onclick="closeAddShopModal()" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="primary">Create Shop</x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- SHOP MODAL 2: Edit Shop -->
    <div id="editShopModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditShopModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
            <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Edit Shop</h3>
                <button onclick="closeEditShopModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="editShopForm" method="POST" enctype="multipart/form-data" class="p-5 space-y-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="owner_id" value="{{ $user->id }}">

                @if($errors->any() && session('modal_open') === 'edit_shop')
                    <div class="p-3.5 bg-danger/10 border border-danger/30 text-danger rounded-xl text-xs space-y-1">
                        <p class="font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            Failed to update shop:
                        </p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Name</label>
                        <input type="text" name="name" id="edit_shop_name" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Status</label>
                        <select name="status" id="edit_shop_status" required
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" id="edit_shop_email"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Mobile Number</label>
                        <input type="text" name="mobile" id="edit_shop_mobile" pattern="[0-9]{10}" maxlength="10"
                            title="Mobile number must be exactly 10 digits" placeholder="e.g. 9876543210"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">GSTIN (GST Number)</label>
                    <input type="text" name="gst_number" id="edit_shop_gst_number" placeholder="e.g. 24AAAAA1121A1Z1"
                        class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Address</label>
                        <textarea name="address" id="edit_shop_address" rows="2"
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white h-[58px] resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Shop Logo</label>
                        <div
                            class="flex items-center gap-3 p-2 bg-secondary/20 border border-dashed border-primary/40 hover:border-primary/80 rounded-xl transition-all h-[58px]">
                            <div id="edit_logo_preview_container" class="flex-shrink-0">
                                <img id="edit_logo_preview" src="" alt="Logo"
                                    class="w-8 h-8 rounded-lg object-cover border border-border-dark hidden">
                                <span id="edit_logo_placeholder"
                                    class="w-8 h-8 rounded-lg bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-xs uppercase">??</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <input type="file" name="logo" id="edit_logo" accept="image/*"
                                    class="block w-full text-[11px] text-slate-400 file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none"
                                    onchange="previewEditLogo(this)">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                    <x-button type="button" onclick="closeEditShopModal()" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="primary">Update Shop</x-button>
                </div>
            </form>
        </div>
    </div>



    <!-- USER MODAL 4: Restore Data Dialog -->
    <div id="restoreModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeRestoreModal()"></div>

        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Restore User Profile & Data</h3>
                <button onclick="closeRestoreModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" enctype="multipart/form-data"
                class="p-6 space-y-4">
                @csrf

                <p class="text-xs text-slate-400">Select a previously exported <span class="text-white font-semibold">User
                        Backup (.json)</span> file to restore user profile and shop details.</p>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Upload Backup File
                        (.json)</label>
                    <input type="file" name="backup_file" required accept=".json,application/json"
                        class="block w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 file:cursor-pointer cursor-pointer focus:outline-none bg-secondary/40 border border-border-dark p-2 rounded-xl">
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                    <x-button type="button" onclick="closeRestoreModal()" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="primary">Restore Data</x-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.openRestoreModal = function () {
            const modal = document.getElementById('restoreModal');
            if (modal) modal.classList.remove('hidden');
        };

        window.closeRestoreModal = function () {
            const modal = document.getElementById('restoreModal');
            if (modal) modal.classList.add('hidden');
        };

        window.openEditModal = function (user) {
            const form = document.getElementById('editForm');
            if (form) form.action = "{{ route('admin.users.update', ['id' => ':id']) }}".replace(':id', user.id);

            if (document.getElementById('edit_name')) document.getElementById('edit_name').value = user.name || '';
            if (document.getElementById('edit_email')) document.getElementById('edit_email').value = user.email || '';
            if (document.getElementById('edit_mobile')) document.getElementById('edit_mobile').value = user.mobile || '';
            if (document.getElementById('edit_status')) document.getElementById('edit_status').value = user.status || 'active';

            const previewImg = document.getElementById('edit_avatar_preview');
            const placeholder = document.getElementById('edit_avatar_placeholder');
            if (previewImg && placeholder) {
                if (user.avatar) {
                    previewImg.src = "/storage/" + user.avatar;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                } else {
                    previewImg.src = "";
                    previewImg.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                    placeholder.innerText = (user.name || 'US').substring(0, 2).toUpperCase();
                }
            }

            const modal = document.getElementById('editModal');
            if (modal) modal.classList.remove('hidden');
        };

        window.closeEditModal = function () {
            const modal = document.getElementById('editModal');
            if (modal) {
                modal.classList.add('hidden');
                const form = modal.querySelector('form');
                if (form) form.reset();
                const errAlerts = modal.querySelectorAll('.bg-danger\\/10');
                errAlerts.forEach(el => el.remove());
            }
        };

        window.openPasswordModal = function (id, name) {
            const form = document.getElementById('passwordForm');
            if (form) {
                form.reset();
                form.action = "{{ route('admin.users.reset_password', ['id' => ':id']) }}".replace(':id', id);
            }

            const nameSpan = document.getElementById('pwd_user_name');
            if (nameSpan) nameSpan.innerText = name || 'User';

            const modal = document.getElementById('passwordModal');
            if (modal) modal.classList.remove('hidden');
        };

        window.closePasswordModal = function () {
            const modal = document.getElementById('passwordModal');
            if (modal) {
                modal.classList.add('hidden');
                const form = modal.querySelector('form');
                if (form) form.reset();
            }
        };

        function clearModalFields(modal) {
            if (!modal) return;
            const form = modal.querySelector('form');
            if (form) {
                form.querySelectorAll('input:not([type="hidden"]), textarea').forEach(input => {
                    input.value = '';
                });
                form.querySelectorAll('select').forEach(select => {
                    select.selectedIndex = 0;
                });
            }
            const errAlerts = modal.querySelectorAll('.bg-danger\\/10');
            errAlerts.forEach(el => el.remove());
            const previewImg = modal.querySelector('#add_logo_preview, #edit_logo_preview');
            const placeholder = modal.querySelector('#add_logo_placeholder, #edit_logo_placeholder');
            if (previewImg) { previewImg.src = ''; previewImg.classList.add('hidden'); }
            if (placeholder) { placeholder.classList.remove('hidden'); }
        }

        window.openAddShopModal = function (clearForm = false) {
            const modal = document.getElementById('addShopModal');
            if (modal) {
                if (clearForm) {
                    clearModalFields(modal);
                }
                modal.classList.remove('hidden');
            }
        };

        window.closeAddShopModal = function () {
            const modal = document.getElementById('addShopModal');
            if (modal) {
                modal.classList.add('hidden');
                clearModalFields(modal);
            }
        };

        window.openEditShopModal = function (elementOrShop) {
            let shop;
            if (elementOrShop instanceof HTMLElement) {
                shop = JSON.parse(elementOrShop.getAttribute('data-shop'));
            } else {
                shop = elementOrShop;
            }

            const form = document.getElementById('editShopForm');
            if (form) form.action = "/admin/shops/" + shop.id;

            if (document.getElementById('edit_shop_name')) document.getElementById('edit_shop_name').value = shop.name || '';
            if (document.getElementById('edit_shop_email')) document.getElementById('edit_shop_email').value = shop.email || '';
            if (document.getElementById('edit_shop_mobile')) document.getElementById('edit_shop_mobile').value = shop.mobile || '';
            if (document.getElementById('edit_shop_gst_number')) document.getElementById('edit_shop_gst_number').value = shop.gst_number || '';
            if (document.getElementById('edit_shop_address')) document.getElementById('edit_shop_address').value = shop.address || '';
            if (document.getElementById('edit_shop_status')) document.getElementById('edit_shop_status').value = shop.status || 'active';

            const previewImg = document.getElementById('edit_logo_preview');
            const placeholder = document.getElementById('edit_logo_placeholder');
            if (previewImg && placeholder) {
                if (shop.logo) {
                    previewImg.src = "/storage/" + shop.logo;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                } else {
                    previewImg.src = "";
                    previewImg.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                    placeholder.innerText = (shop.name || 'SH').substring(0, 2).toUpperCase();
                }
            }

            const modal = document.getElementById('editShopModal');
            if (modal) modal.classList.remove('hidden');
        };

        window.closeEditShopModal = function () {
            const modal = document.getElementById('editShopModal');
            if (modal) {
                modal.classList.add('hidden');
                const form = modal.querySelector('form');
                if (form) form.reset();
                const errAlerts = modal.querySelectorAll('.bg-danger\\/10');
                errAlerts.forEach(el => el.remove());
            }
        };



        window.previewAddAvatar = function (input) {
            const previewImg = document.getElementById('add_avatar_preview');
            const placeholder = document.getElementById('add_avatar_placeholder');
            if (input.files && input.files[0] && previewImg && placeholder) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.previewEditAvatar = function (input) {
            const previewImg = document.getElementById('edit_avatar_preview');
            const placeholder = document.getElementById('edit_avatar_placeholder');
            if (input.files && input.files[0] && previewImg && placeholder) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.previewAddLogo = function (input) {
            const previewImg = document.getElementById('add_logo_preview');
            const placeholder = document.getElementById('add_logo_placeholder');
            if (input.files && input.files[0] && previewImg && placeholder) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.previewEditLogo = function (input) {
            const previewImg = document.getElementById('edit_logo_preview');
            const placeholder = document.getElementById('edit_logo_placeholder');
            if (input.files && input.files[0] && previewImg && placeholder) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.openUserPlanModal = function () {
            document.getElementById('userPlanModal').classList.remove('hidden');
        };
        window.closeUserPlanModal = function () {
            document.getElementById('userPlanModal').classList.add('hidden');
        };

        document.addEventListener('DOMContentLoaded', function () {
            @if(session('modal_open') === 'edit' && session('edit_user_data'))
                openEditModal({!! json_encode(session('edit_user_data')) !!});
            @elseif(session('modal_open') === 'add_shop')
                openAddShopModal();
            @elseif(session('modal_open') === 'edit_shop' && session('edit_shop_data'))
                openEditShopModal({!! json_encode(session('edit_shop_data')) !!});
            @endif
                });
    </script>

    <!-- User Subscription Plan Override Modal -->
    <div id="userPlanModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeUserPlanModal()"></div>
        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Manage User Subscription Tier</h3>
                <button onclick="closeUserPlanModal()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('admin.users.subscription', $user->id) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <p class="text-xs text-slate-400">Configure manually active plan for <span
                        class="text-white font-semibold">{{ $user->name }}</span>.</p>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Select Subscription
                        Plan</label>
                    <select name="plan_id" required
                        class="block w-full px-3 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ $user->active_plan_id == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} (₹{{ $plan->price }}/{{ $plan->billing_period }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Duration (Days)</label>
                    <input type="number" name="duration_days" value="30" min="1" required
                        class="block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                    <x-button type="button" onclick="closeUserPlanModal()" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="primary">Activate Subscription</x-button>
                </div>
            </form>
        </div>
    </div>
@endsection
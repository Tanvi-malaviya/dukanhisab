<!DOCTYPE html>
<html lang="en" class="h-full bg-bg-dark text-slate-800">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DukanHisab') - Super Admin Panel</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS (compiled via Vite) -->
    @vite(['resources/css/app.css'])

    <!-- ApexCharts CDN for interactive analytics -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
        }
    </style>
</head>

<body class="h-full flex overflow-hidden">

    <!-- Sidebar Overlay for mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm hidden md:hidden"
        onclick="toggleSidebar()"></div>

    <!-- Sidebar Navigation -->
    <div id="sidebar"
        class="fixed inset-y-0 left-0 z-40 w-64 transform -translate-x-full md:translate-x-0 md:static md:flex md:flex-shrink-0 transition-transform duration-300 ease-in-out flex overflow-hidden">
        <div class="flex flex-col w-full border-r border-border-dark bg-card-dark text-slate-300 relative h-full">
            <!-- Brand Logo -->
            <div class="flex items-center h-16 px-4 md:px-6 border-b border-border-dark justify-between gap-2 md:gap-3">
                <div class="flex items-center gap-2 md:gap-3">
                    <span class="p-1.5 rounded-lg bg-primary/10 text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </span>
                    <span class="text-lg font-bold tracking-tight text-white">Dukan<span
                            class="text-primary">Hisab</span></span>
                    <span
                        class="text-[10px] uppercase font-semibold bg-primary/20 text-primary px-1.5 py-0.5 rounded">Super</span>
                </div>
                <!-- Close button for mobile -->
                <button onclick="toggleSidebar()"
                    class="md:hidden p-1 rounded-xl text-slate-400 hover:text-white hover:bg-secondary cursor-pointer shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 flex flex-col overflow-y-auto px-4 py-6 space-y-1">
                @php
                    $route = Request::route()->getName();
                @endphp
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ $route === 'admin.dashboard' ? 'bg-primary text-white font-semibold' : 'hover:bg-secondary hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z">
                        </path>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ str_contains($route, 'admin.users') || str_contains($route, 'admin.shops') ? 'bg-primary text-white font-semibold' : 'hover:bg-secondary hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    Users & Shops
                </a>

                <a href="{{ route('admin.subscriptions.index') }}"
                    class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ str_contains($route, 'admin.subscriptions') ? 'bg-primary text-white font-semibold' : 'hover:bg-secondary hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
                        </path>
                    </svg>
                    Subscription Plans
                </a>

                <a href="{{ route('admin.payments.index') }}"
                    class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ str_contains($route, 'admin.payments') ? 'bg-primary text-white font-semibold' : 'hover:bg-secondary hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    Payments Ledger
                </a>

                <a href="{{ route('admin.reports.index') }}"
                    class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ str_contains($route, 'admin.reports') ? 'bg-primary text-white font-semibold' : 'hover:bg-secondary hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    Analytics & Reports
                </a>

                <div class="pt-4 pb-2 text-[10px] font-semibold text-slate-500 uppercase tracking-wider">System Settings
                </div>

                <a href="{{ route('admin.settings.app') }}"
                    class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ str_contains($route, 'settings.app') ? 'bg-primary text-white font-semibold' : 'hover:bg-secondary hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    App Config
                </a>

                <a href="{{ route('admin.settings.invoice') }}"
                    class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ str_contains($route, 'settings.invoice') ? 'bg-primary text-white font-semibold' : 'hover:bg-secondary hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Invoice Layout
                </a>



                <a href="{{ route('admin.notifications.index') }}"
                    class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ str_contains($route, 'admin.notifications') ? 'bg-primary text-white font-semibold' : 'hover:bg-secondary hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                    Broadcast Center
                </a>

                <a href="{{ route('admin.support.index') }}"
                    class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ str_contains($route, 'admin.support') ? 'bg-primary text-white font-semibold' : 'hover:bg-secondary hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    Support Desk
                </a>

                <a href="{{ route('admin.backups.index') }}"
                    class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ str_contains($route, 'admin.backups') ? 'bg-primary text-white font-semibold' : 'hover:bg-secondary hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                        </path>
                    </svg>
                    Database Backups
                </a>

                <a href="{{ route('admin.logs.index') }}"
                    class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ str_contains($route, 'admin.logs') ? 'bg-primary text-white font-semibold' : 'hover:bg-secondary hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Audit Trails
                </a>
            </div>

            <!-- Authenticated Admin Profile Card -->
            @if(auth('admin')->check())
                <div class="p-4 border-t border-border-dark flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-sm uppercase">
                            {{ substr(auth('admin')->user()->name, 0, 2) }}
                        </span>
                        <div class="truncate">
                            <p class="text-xs font-semibold text-white truncate">{{ auth('admin')->user()->name }}</p>
                            <p class="text-[10px] text-slate-500 uppercase">{{ auth('admin')->user()->role }}</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="p-1 rounded-md hover:bg-secondary hover:text-danger text-slate-500 transition-colors"
                            title="Log Out">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Workspace Container -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <header
            class="flex items-center justify-between h-16 px-6 border-b border-border-dark bg-card-dark text-slate-300">
            <!-- Left Side: Mobile Menu & Page Title -->
            <div class="flex items-center gap-3">
                <!-- Mobile Menu Toggle Button -->
                <button onclick="toggleSidebar()"
                    class="md:hidden p-1 rounded-md text-slate-400 hover:text-white hover:bg-secondary cursor-pointer shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16">
                        </path>
                    </svg>
                </button>

                <!-- Page Title -->
                <div class="flex flex-col">
                    <h1 class="text-lg md:text-2xl font-bold text-slate-900 tracking-tight leading-tight">
                        @hasSection('page_title')
                            @yield('page_title')
                        @else
                            @yield('title')
                        @endif
                    </h1>
                    @hasSection('page_subtitle')
                        <p class="block text-slate-400 font-medium leading-none mt-1 text-xs">
                            @yield('page_subtitle')
                        </p>
                    @endif
                </div>
            </div>

            <!-- Global Action Icons -->
            <div class="flex items-center gap-4">
                <!-- Notifications Bell -->
                <!-- <button
                    class="p-1.5 rounded-full hover:bg-secondary hover:text-white relative text-slate-400 transition-colors">
                    <span class="absolute top-0 right-0 w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                </button> -->

                <div class="w-px h-5 bg-border-dark"></div>

                <!-- Clock / Date indicator -->
                <span class="text-xs text-slate-500 font-medium hidden sm:inline">
                    {{ now()->format('l, M d, Y') }}
                </span>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main class="flex-1 overflow-y-auto bg-bg-dark p-3 md:p-3">

            <!-- Notification Success/Error banners -->
            @if(session('success'))
                <div
                    class="flash-alert mb-6 p-4 rounded-lg bg-success/10 border border-success/30 text-success text-sm flex items-center justify-between gap-3 animate-fade-in transition-all duration-500">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button"
                        class="alert-close text-success/60 hover:text-success transition-colors cursor-pointer"
                        title="Dismiss">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div
                    class="flash-alert mb-6 p-4 rounded-lg bg-danger/10 border border-danger/30 text-danger text-sm flex items-center justify-between gap-3 animate-fade-in transition-all duration-500">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button"
                        class="alert-close text-danger/60 hover:text-danger transition-colors cursor-pointer"
                        title="Dismiss">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            @endif

            @if($errors->any() && !session('modal_open'))
                <div
                    class="flash-alert mb-6 p-4 rounded-lg bg-danger/10 border border-danger/30 text-danger text-sm animate-fade-in transition-all duration-500 flex justify-between items-start gap-3">
                    <div>
                        <div class="flex items-center gap-3 mb-2 font-semibold">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            <span>Please fix the following validation errors:</span>
                        </div>
                        <ul class="list-disc pl-8 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button"
                        class="alert-close text-danger/60 hover:text-danger transition-colors cursor-pointer"
                        title="Dismiss">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            @endif

            @yield('content')
        </main>
        <script>
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                if (sidebar && overlay) {
                    if (sidebar.classList.contains('-translate-x-full')) {
                        sidebar.classList.remove('-translate-x-full');
                        overlay.classList.remove('hidden');
                        overlay.style.display = 'block';
                    } else {
                        sidebar.classList.add('-translate-x-full');
                        overlay.classList.add('hidden');
                        overlay.style.display = 'none';
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Auto-dismiss flash alerts
                const alerts = document.querySelectorAll('.flash-alert');
                alerts.forEach(function (alert) {
                    // Fade out and hide after 3 seconds
                    setTimeout(function () {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(function () {
                            alert.style.display = 'none';
                        }, 500);
                    }, 3000);
                });

                // Close button functionality
                const closeButtons = document.querySelectorAll('.alert-close');
                closeButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        const alert = button.closest('.flash-alert');
                        if (alert) {
                            alert.style.opacity = '0';
                            alert.style.transform = 'translateY(-10px)';
                            setTimeout(function () {
                                alert.style.display = 'none';
                            }, 500);
                        }
                    });
                });
            });

            // Global Action Confirmation helpers
            function confirmAction(options) {
                const form = document.getElementById('globalConfirmForm');
                const titleEl = document.getElementById('globalConfirmTitle');
                const messageEl = document.getElementById('globalConfirmMessage');
                const submitBtn = document.getElementById('globalConfirmSubmitBtn');
                const methodInput = document.getElementById('globalConfirmMethod');
                const iconContainer = document.getElementById('globalConfirmIcon');
                const modal = document.getElementById('globalConfirmModal');

                if (form && titleEl && messageEl && submitBtn && modal) {
                    // Set action
                    form.action = options.actionUrl;

                    // Set title & message
                    titleEl.innerText = options.title || 'Confirm Action';
                    messageEl.innerText = options.message || 'Are you sure you want to proceed?';

                    // Set button text
                    submitBtn.innerText = options.buttonText || 'Confirm';

                    // Set button classes/variant
                    submitBtn.className = "px-4 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-white";
                    if (options.variant === 'danger') {
                        submitBtn.classList.add('bg-danger', 'hover:bg-danger/80');
                    } else if (options.variant === 'warning') {
                        submitBtn.classList.add('bg-warning', 'hover:bg-warning/80');
                    } else if (options.variant === 'info') {
                        submitBtn.classList.add('bg-info', 'hover:bg-info/80');
                    } else {
                        submitBtn.classList.add('bg-primary', 'hover:bg-primary/80');
                    }

                    // Set icon styling
                    if (iconContainer) {
                        iconContainer.className = "mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4";
                        if (options.variant === 'danger') {
                            iconContainer.classList.add('bg-danger/10', 'text-danger');
                        } else if (options.variant === 'warning') {
                            iconContainer.classList.add('bg-warning/10', 'text-warning');
                        } else if (options.variant === 'info') {
                            iconContainer.classList.add('bg-info/10', 'text-info');
                        } else {
                            iconContainer.classList.add('bg-primary/10', 'text-primary');
                        }
                    }

                    // Set method (PUT, DELETE, POST)
                    if (methodInput) {
                        methodInput.value = options.method || 'POST';
                    }

                    // Open modal
                    modal.classList.remove('hidden');
                }
            }

            function closeConfirmModal() {
                const modal = document.getElementById('globalConfirmModal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            }

            // Backward compatibility wrapper for delete confirmation
            function confirmDelete(actionUrl, name) {
                confirmAction({
                    actionUrl: actionUrl,
                    title: 'Delete Confirmation',
                    message: `Are you sure you want to delete "${name}"? This action cannot be undone and all associated records will be permanently removed.`,
                    buttonText: 'Delete Permanently',
                    variant: 'danger',
                    method: 'DELETE'
                });
            }
        </script>

        <!-- Global Action Confirmation Modal -->
        <div id="globalConfirmModal"
            class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeConfirmModal()"></div>

            <div
                class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-sm shadow-2xl relative z-10 overflow-hidden">
                <div class="p-6 text-center">
                    <!-- Icon Container -->
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-warning/10 text-warning mb-4"
                        id="globalConfirmIcon">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <h3 class="text-base font-semibold text-white mb-2" id="globalConfirmTitle">Confirm Action</h3>
                    <p class="text-xs text-slate-400 mb-6" id="globalConfirmMessage">Are you sure you want to proceed?
                    </p>

                    <form id="globalConfirmForm" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="globalConfirmMethod" value="POST">

                        <div class="flex justify-center gap-3">
                            <button type="button" onclick="closeConfirmModal()"
                                class="px-4 py-2.5 bg-secondary hover:bg-secondary/80 text-slate-300 border border-border-dark rounded-xl text-xs font-semibold transition-all cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" id="globalConfirmSubmitBtn"
                                class="px-4 py-2.5 bg-primary hover:bg-primary/80 text-white rounded-xl text-xs font-semibold transition-all cursor-pointer">
                                Confirm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- REUSABLE COMMON LOADER COMPONENT (OVERLAY) -->
        <x-loader id="global-page-loader" type="overlay" text="Processing request..." />

        <script>
            let loaderTimeout = null;

            window.showGlobalLoader = function(autoHideDelay = 0) {
                const loader = document.getElementById('global-page-loader');
                if (loader) {
                    loader.classList.remove('opacity-0', 'pointer-events-none');
                    loader.classList.add('opacity-100');
                }
                if (autoHideDelay > 0) {
                    clearTimeout(loaderTimeout);
                    loaderTimeout = setTimeout(hideGlobalLoader, autoHideDelay);
                }
            };

            window.hideGlobalLoader = function() {
                clearTimeout(loaderTimeout);
                const loader = document.getElementById('global-page-loader');
                if (loader) {
                    loader.classList.remove('opacity-100');
                    loader.classList.add('opacity-0', 'pointer-events-none');
                }
            };

            // Intercept Form Submissions
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (form && (form.hasAttribute('download') || form.getAttribute('action')?.includes('download') || form.getAttribute('action')?.includes('backup'))) {
                    showGlobalLoader(1500);
                } else {
                    showGlobalLoader();
                }
            });

            // Intercept Link Clicks for File Downloads
            document.addEventListener('click', function (e) {
                const link = e.target.closest('a');
                if (link) {
                    const href = link.getAttribute('href') || '';
                    if (link.hasAttribute('download') || href.includes('/download') || href.includes('/backup') || href.endsWith('.sql') || href.endsWith('.json') || href.endsWith('.csv') || href.endsWith('.pdf')) {
                        setTimeout(hideGlobalLoader, 1000);
                    }
                }
            });

            // Intercept Page Transitions with auto-hide fallback for file downloads
            window.addEventListener('beforeunload', function () {
                showGlobalLoader();
                // If page response is a file download attachment, the page won't unload.
                // Auto hide loader after 2 seconds as fallback.
                setTimeout(hideGlobalLoader, 2000);
            });

            window.addEventListener('pageshow', hideGlobalLoader);
            window.addEventListener('focus', function() {
                setTimeout(hideGlobalLoader, 500);
            });

            // Intercept Native Fetch API Calls globally
            const originalFetch = window.fetch;
            if (originalFetch) {
                window.fetch = async function(...args) {
                    showGlobalLoader();
                    try {
                        const response = await originalFetch.apply(this, args);
                        return response;
                    } finally {
                        hideGlobalLoader();
                    }
                };
            }

            // Intercept XMLHttpRequest (XHR) API Calls globally
            const originalOpen = XMLHttpRequest.prototype.open;
            const originalSend = XMLHttpRequest.prototype.send;
            XMLHttpRequest.prototype.open = function(...args) {
                this.addEventListener('loadstart', function() {
                    showGlobalLoader();
                });
                this.addEventListener('loadend', function() {
                    hideGlobalLoader();
                });
                return originalOpen.apply(this, args);
            };
        </script>
</body>

</html>
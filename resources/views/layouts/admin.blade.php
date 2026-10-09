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
            <div class="flex-1 flex flex-col overflow-y-auto px-3 py-5 space-y-4">
                @php
                    $route = Request::route()?->getName() ?? '';
                    // Section, item: [route, icon path, label, active-when]. One data-driven list instead of
                    // 20 hand-written <a> tags — the thing every admin screen has in common is this nav, so
                    // it's the highest-leverage place to make the panel consistent and easy to scan.
                    $navSections = [
                        null => [
                            ['admin.dashboard', 'M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z', 'Dashboard', 'admin.dashboard'],
                        ],
                        'Customers' => [
                            ['admin.users.index', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'Users & Shops', 'admin.users|admin.shops'],
                            ['admin.support.index', 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z', 'Support Desk', 'admin.support'],
                        ],
                        'Billing' => [
                            ['admin.subscriptions.index', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z', 'Subscription Plans', 'admin.subscriptions'],
                            ['admin.addons.index', 'M12 4v16m8-8H4', 'Add-Ons', 'admin.addons'],
                            ['admin.payments.index', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'Payments Ledger', 'admin.payments'],
                            ['admin.whatsapp.usage', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'WhatsApp Usage', 'admin.whatsapp'],
                        ],
                        'Insights & Marketing' => [
                            ['admin.reports.index', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'Analytics & Reports', 'admin.reports'],
                            ['admin.notifications.index', 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'Broadcast Center', 'admin.notifications'],
                            ['admin.ads.index', 'M7 4v16l6-4 6 4V4a1 1 0 00-1-1H8a1 1 0 00-1 1z', 'Advertisements', 'admin.ads'],
                        ],
                        'System' => [
                            ['admin.settings.app', 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z', 'App Config', 'settings.app'],
                            ['admin.settings.invoice', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'Invoice Layout', 'settings.invoice'],
                            ['admin.settings.payment', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'Payment Gateway', 'settings.payment'],
                            ['admin.settings.whatsapp', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'WhatsApp', 'settings.whatsapp'],
                            ['admin.settings.translations', 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129', 'Translations', 'settings.translations'],
                            ['admin.backups.index', 'M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4', 'Database Backups', 'admin.backups'],
                            ['admin.logs.index', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'Audit Trails', 'admin.logs'],
                        ],
                    ];
                @endphp

                @foreach ($navSections as $section => $items)
                    <div>
                        @if ($section)
                            <div class="px-3 pb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $section }}</div>
                        @endif
                        <div class="space-y-0.5">
                            @foreach ($items as [$routeName, $iconPath, $label, $activeWhen])
                                @php
                                    $isActive = collect(explode('|', $activeWhen))->contains(fn ($needle) => str_contains($route, $needle));
                                @endphp
                                <a href="{{ route($routeName) }}"
                                    class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ $isActive ? 'bg-primary text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-secondary/60 hover:text-white' }}">
                                    @if ($isActive)
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 h-5 w-1 rounded-r bg-white/80"></span>
                                    @endif
                                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
                                    </svg>
                                    <span class="truncate">{{ $label }}</span>
                                    @if ($routeName === 'admin.support.index' && ($openTicketsBadge ?? 0) > 0)
                                        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $isActive ? 'bg-white/20 text-white' : 'bg-warning/20 text-warning' }}">{{ $openTicketsBadge }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
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
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-xs">
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
            let lastClickedBtn = null;

            // Track last clicked button globally across the page
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('button, input[type="submit"], a.btn, [role="button"]');
                if (btn) {
                    lastClickedBtn = btn;
                }
            }, true);

            window.showButtonLoader = function(btn) {
                if (!btn || btn.dataset.loading === 'true') return;
                btn.dataset.loading = 'true';
                btn.dataset.origContent = btn.innerHTML;
                btn.dataset.origPointer = btn.style.pointerEvents || '';
                btn.dataset.origOpacity = btn.style.opacity || '';

                btn.disabled = true;
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.75';

                if (!btn.querySelector('.animate-spin')) {
                    const spinner = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block text-current shrink-0 align-text-bottom" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
                    btn.innerHTML = spinner + btn.innerHTML;
                }
            };

            window.hideButtonLoader = function(btn) {
                if (!btn) return;
                if (btn.dataset.loading === 'true') {
                    btn.innerHTML = btn.dataset.origContent || btn.innerHTML;
                    btn.style.pointerEvents = btn.dataset.origPointer || '';
                    btn.style.opacity = btn.dataset.origOpacity || '';
                    btn.disabled = false;
                    delete btn.dataset.loading;
                    delete btn.dataset.origContent;
                    delete btn.dataset.origPointer;
                    delete btn.dataset.origOpacity;
                }
            };

            window.showGlobalLoader = function(autoHideDelay = 0) {
                // Kept for backward compatibility if explicitly invoked
            };

            window.hideGlobalLoader = function() {
                // Kept for backward compatibility if explicitly invoked
            };

            // Intercept Form Submissions -> Show loader on submit button inside the form
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]') || lastClickedBtn;
                    if (submitBtn) {
                        showButtonLoader(submitBtn);
                    }
                }
            });

            // Intercept Native Fetch API Calls globally -> Spin loader inside the triggered button
            const originalFetch = window.fetch;
            if (originalFetch) {
                window.fetch = async function(...args) {
                    const btn = lastClickedBtn;
                    if (btn) showButtonLoader(btn);
                    try {
                        const response = await originalFetch.apply(this, args);
                        return response;
                    } finally {
                        if (btn) {
                            setTimeout(() => hideButtonLoader(btn), 300);
                        }
                    }
                };
            }

            // Intercept XMLHttpRequest (XHR) API Calls globally -> Spin loader inside the triggered button
            const originalOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(...args) {
                const btn = lastClickedBtn;
                this.addEventListener('loadstart', function() {
                    if (btn) showButtonLoader(btn);
                });
                this.addEventListener('loadend', function() {
                    if (btn) setTimeout(() => hideButtonLoader(btn), 300);
                });
                return originalOpen.apply(this, args);
            };
        </script>
</body>

</html>
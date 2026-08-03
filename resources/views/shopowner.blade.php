<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DukanHisab - ShopOwner Portal</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- AlpineJS -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#0F766E', hover: '#115E59', light: '#CCFBF1' },
                        secondary: { DEFAULT: '#14B8A6', hover: '#0D9488' }
                    },
                    fontFamily: { sans: ['"Quicksand"', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>

    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Custom and TailwindCSS -->
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Quicksand', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
    </style>
</head>
<body class="min-h-screen text-slate-900" x-data="authApp()" style="background: linear-gradient(135deg, #0F766E 0%, #115E59 55%, #021b18 100%);">

    <!-- Toast Notification System -->
    <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 max-w-sm w-full">
        <template x-for="toast in toasts" :key="toast.id">
            <div :class="{
                'bg-emerald-50 border-emerald-200 text-emerald-800': toast.type === 'success',
                'bg-rose-50 border-rose-200 text-rose-800': toast.type === 'error',
                'bg-amber-50 border-amber-200 text-amber-800': toast.type === 'warning'
            }" class="p-4 rounded-xl border shadow-lg flex items-start gap-3 transform transition-all duration-300 translate-y-0 opacity-100">
                
                <!-- Icon -->
                <span class="mt-0.5">
                    <template x-if="toast.type === 'success'">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </template>
                </span>

                <div class="flex-1">
                    <p class="text-sm font-semibold" x-text="toast.message"></p>
                </div>

                <button @click="removeToast(toast.id)" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Main Container -->
    <div class="shopowner-auth-container relative min-h-screen w-full overflow-hidden flex flex-col">

        <!-- Organic blob shapes (full page, teal palette) -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-16 -left-16 w-[40rem] h-[40rem] bg-emerald-400/40"
                 style="border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; filter: blur(6px);"></div>
            <div class="absolute top-[8%] -right-32 w-[36rem] h-[36rem] bg-teal-300/30"
                 style="border-radius: 40% 60% 70% 30% / 50% 60% 40% 50%; filter: blur(6px);"></div>
            <div class="absolute -bottom-32 left-[12%] w-[38rem] h-[38rem] bg-black/25"
                 style="border-radius: 50% 50% 40% 60% / 40% 50% 60% 50%; filter: blur(6px);"></div>
            <div class="absolute bottom-[6%] -right-10 w-[26rem] h-[26rem] bg-emerald-300/30"
                 style="border-radius: 45% 55% 65% 35% / 55% 45% 55% 45%; filter: blur(6px);"></div>
            <div class="absolute top-[35%] left-[8%] w-[18rem] h-[18rem] bg-cyan-300/20"
                 style="border-radius: 55% 45% 35% 65% / 45% 55% 65% 35%; filter: blur(6px);"></div>
        </div>

        <div class="relative z-10 flex-1 w-full max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-center lg:justify-between gap-10 px-6 lg:px-12 py-10">

        <!-- Left Side: Premium Branding & Showcase -->
        <div class="auth-branding-container hidden lg:flex flex-col justify-center max-w-xl">

            <!-- Branding Header (Glassmorphic Pill style) -->
            <div class="flex items-center gap-3 z-10 mb-6">
                <div class="inline-flex items-center justify-center px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full shadow-lg font-bold text-white">
                    <svg class="w-4 h-4 text-emerald-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="text-sm font-extrabold tracking-tight">Dukan<span class="text-emerald-400">Hisab</span></span>
                </div>
            </div>

            <!-- Content Middle -->
            <div class="w-full z-10 space-y-6">
                <div class="space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-300 text-[10px] font-bold uppercase tracking-wider backdrop-blur-md border border-emerald-500/20">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Simplify Your Retail Business
                    </div>
                    <div class="text-3xl xl:text-4xl font-extrabold text-white leading-tight tracking-tight" style="color: #ffffff !important;">
                        One Platform. <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-200 to-cyan-300">Complete Control</span> of Your Shop.
                    </div>
                    <p class="text-teal-100/70 text-xs xl:text-sm leading-relaxed max-w-lg">
                        Manage your inventory, generate invoices, track customer ledger payments, and keep suppliers in check. Experience automated reminders via WhatsApp to recover dues faster.
                    </p>
                </div>

                <!-- Features Grid (6 features) -->
                <div class="grid grid-cols-2 gap-3.5">
                    <!-- POS & Billing -->
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/[0.08] backdrop-blur-md hover:bg-white/[0.08] hover:border-emerald-500/30 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-950/40 transition-all duration-300 group">
                        <span class="p-2 bg-gradient-to-br from-emerald-400/20 to-teal-500/20 text-emerald-300 rounded-lg shadow-md border border-emerald-500/10 group-hover:scale-105 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </span>
                        <div class="flex flex-col">
                            <span class="font-bold text-xs xl:text-sm text-white group-hover:text-emerald-300 transition-colors" style="color: #ffffff !important;">POS &amp; Billing</span>
                            <span class="text-[10px] text-teal-100/50 mt-0.5 leading-tight">Fast barcode checkout</span>
                        </div>
                    </div>

                    <!-- Inventory Tracker -->
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/[0.08] backdrop-blur-md hover:bg-white/[0.08] hover:border-emerald-500/30 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-950/40 transition-all duration-300 group">
                        <span class="p-2 bg-gradient-to-br from-emerald-400/20 to-teal-500/20 text-emerald-300 rounded-lg shadow-md border border-emerald-500/10 group-hover:scale-105 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </span>
                        <div class="flex flex-col">
                            <span class="font-bold text-xs xl:text-sm text-white group-hover:text-emerald-300 transition-colors" style="color: #ffffff !important;">Stock Control</span>
                            <span class="text-[10px] text-teal-100/50 mt-0.5 leading-tight">Real-time alerts</span>
                        </div>
                    </div>

                    <!-- Cash & Bank Ledger -->
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/[0.08] backdrop-blur-md hover:bg-white/[0.08] hover:border-emerald-500/30 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-950/40 transition-all duration-300 group">
                        <span class="p-2 bg-gradient-to-br from-emerald-400/20 to-teal-500/20 text-emerald-300 rounded-lg shadow-md border border-emerald-500/10 group-hover:scale-105 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </span>
                        <div class="flex flex-col">
                            <span class="font-bold text-xs xl:text-sm text-white group-hover:text-emerald-300 transition-colors" style="color: #ffffff !important;">Cash &amp; Bank</span>
                            <span class="text-[10px] text-teal-100/50 mt-0.5 leading-tight">Instant ledger tracking</span>
                        </div>
                    </div>

                    <!-- WhatsApp reminders -->
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/[0.08] backdrop-blur-md hover:bg-white/[0.08] hover:border-emerald-500/30 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-950/40 transition-all duration-300 group">
                        <span class="p-2 bg-gradient-to-br from-emerald-400/20 to-teal-500/20 text-emerald-300 rounded-lg shadow-md border border-emerald-500/10 group-hover:scale-105 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </span>
                        <div class="flex flex-col">
                            <span class="font-bold text-xs xl:text-sm text-white group-hover:text-emerald-300 transition-colors" style="color: #ffffff !important;">Smart Reminders</span>
                            <span class="text-[10px] text-teal-100/50 mt-0.5 leading-tight">Auto WhatsApp alerts</span>
                        </div>
                    </div>

                    <!-- Expense Tracker -->
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/[0.08] backdrop-blur-md hover:bg-white/[0.08] hover:border-emerald-500/30 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-950/40 transition-all duration-300 group">
                        <span class="p-2 bg-gradient-to-br from-emerald-400/20 to-teal-500/20 text-emerald-300 rounded-lg shadow-md border border-emerald-500/10 group-hover:scale-105 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </span>
                        <div class="flex flex-col">
                            <span class="font-bold text-xs xl:text-sm text-white group-hover:text-emerald-300 transition-colors" style="color: #ffffff !important;">Expense Tracker</span>
                            <span class="text-[10px] text-teal-100/50 mt-0.5 leading-tight">Analyze &amp; save money</span>
                        </div>
                    </div>

                    <!-- Reports & GST -->
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/[0.08] backdrop-blur-md hover:bg-white/[0.08] hover:border-emerald-500/30 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-950/40 transition-all duration-300 group">
                        <span class="p-2 bg-gradient-to-br from-emerald-400/20 to-teal-500/20 text-emerald-300 rounded-lg shadow-md border border-emerald-500/10 group-hover:scale-105 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path>
                            </svg>
                        </span>
                        <div class="flex flex-col">
                            <span class="font-bold text-xs xl:text-sm text-white group-hover:text-emerald-300 transition-colors" style="color: #ffffff !important;">Reports &amp; GST</span>
                            <span class="text-[10px] text-teal-100/50 mt-0.5 leading-tight">One-click tax summaries</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer copyright inside left panel -->
            <div class="text-[10px] text-teal-100/60 z-10 mt-8">
                &copy; {{ date('Y') }} DukanHisab. One Platform. Complete Control.
            </div>
        </div>

        <!-- Right Side: Forms Container -->
        <div class="auth-forms-container w-full max-w-md relative z-10">

            <div class="w-full relative z-10">
                <!-- Branding Header for Mobile Only -->
                <div class="flex md:hidden items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex p-2 rounded-xl bg-primary/10 text-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </span>
                        <span class="text-xl font-extrabold tracking-tight text-slate-900">Dukan<span class="text-primary">Hisab</span></span>
                    </div>
                    <span class="text-[10px] font-semibold px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full" x-text="subtitleText()"></span>
                </div>

                <div class="glass-card py-4 px-4 sm:px-5 shadow-md border border-slate-200/50 rounded-xl">

                    <!-- Page Header (inside card, top center) -->
                    <div class="text-center mb-4">
                        <div class="text-2xl font-extrabold text-slate-800 tracking-tight" x-text="view === 'login' ? 'Login' : (view === 'register' ? 'Create Account' : (view === 'forgot-password' ? 'Forgot Password' : (view === 'reset-password' ? 'Reset Password' : (view === 'verify-otp' ? 'Verify OTP' : (view === 'shop-setup' ? 'Shop Setup' : 'Dashboard')))))"></div>
                        <p class="mt-1 text-xs text-slate-400 font-medium" x-text="subtitleText()"></p>
                    </div>


                    <!-- 1. LOGIN SCREEN -->
                    <div x-show="view === 'login'">
                        <form @submit.prevent="handleLogin()" class="space-y-4">
                            <div>
                                <label for="login-email" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Email Address</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </span>
                                    <input id="login-email" type="email" required placeholder="name@company.com" x-model="loginForm.email"
                                        class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                </div>
                            </div>

                            <div>
                                <label for="login-password" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </span>
                                    <input id="login-password" :type="showPassword ? 'text' : 'password'" required placeholder="••••••••" x-model="loginForm.password"
                                        class="block w-full pl-9 pr-9 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-slate-400 hover:text-slate-600">
                                        <template x-if="!showPassword">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </template>
                                        <template x-if="showPassword">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                                        </template>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs pt-0.5">
                                <div class="flex items-center">
                                    <input id="remember-me" type="checkbox" x-model="loginForm.remember" class="h-3.5 w-3.5 accent-[#0F766E] text-[#0F766E] border-slate-300 rounded focus:ring-[#0F766E] cursor-pointer">
                                    <label for="remember-me" class="ml-1.5 block text-[11px] font-semibold text-slate-600 cursor-pointer">Remember Me</label>
                                </div>
                                <a href="#" @click.prevent="setView('forgot-password')" class="text-[11px] font-semibold text-primary hover:text-primary-hover transition-colors">Forgot Password?</a>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full flex justify-center py-2.5 px-4 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-lg transition-all shadow-md shadow-primary/10 cursor-pointer disabled:opacity-50">
                                <span x-show="!loading">Sign In</span>
                                <span x-show="loading" class="flex items-center gap-1.5">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Signing in...
                                </span>
                            </button>
                        </form>

                        <div class="mt-4 text-center">
                            <p class="text-xs text-slate-600">Don't have an account? <a href="#" @click.prevent="setView('register')" class="font-semibold text-primary hover:text-primary-hover transition-colors">Sign up</a></p>
                        </div>
                    </div>

                    <!-- 2. REGISTER SCREEN -->
                    <div x-show="view === 'register'">
                        <form @submit.prevent="handleRegister()" class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="register-first-name" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">First Name</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </span>
                                        <input id="register-first-name" type="text" required placeholder="John" x-model="registerForm.first_name"
                                            class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                    </div>
                                </div>
                                <div>
                                    <label for="register-last-name" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Last Name</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </span>
                                        <input id="register-last-name" type="text" required placeholder="Doe" x-model="registerForm.last_name"
                                            class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="register-mobile" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Mobile Number</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </span>
                                    <input id="register-mobile" type="text" required placeholder="9876543210" x-model="registerForm.mobile" maxlength="10" x-on:input="registerForm.mobile = registerForm.mobile.replace(/\D/g, '').slice(0, 10)"
                                        class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                </div>
                            </div>

                            <div>
                                <label for="register-email" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Email Address</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </span>
                                    <input id="register-email" type="email" required placeholder="john@example.com" x-model="registerForm.email"
                                        class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                </div>
                            </div>

                            <div>
                                <label for="register-password" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </span>
                                    <input id="register-password" type="password" required placeholder="••••••••" x-model="registerForm.password"
                                        class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                </div>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full flex justify-center py-2.5 px-4 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-lg transition-all shadow-md shadow-primary/10 cursor-pointer disabled:opacity-50">
                                <span x-show="!loading">Create Account</span>
                                <span x-show="loading" class="flex items-center gap-1.5">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Creating Account...
                                </span>
                            </button>
                        </form>

                        <div class="mt-4 text-center">
                            <p class="text-xs text-slate-600">Already have an account? <a href="#" @click.prevent="setView('login')" class="font-semibold text-primary hover:text-primary-hover transition-colors">Sign in</a></p>
                        </div>
                    </div>

                    <!-- 3. OTP VERIFICATION SCREEN -->
                    <div x-show="view === 'verify-otp'">
                        <p class="text-xs text-slate-600 mb-4 text-center">We've sent a 6-digit verification code to <strong x-text="verificationEmail"></strong>. Please enter it below to verify your account.</p>
                        
                        <form @submit.prevent="handleVerifyOtp()" class="space-y-4">
                            <!-- Single numeric-friendly OTP input -->
                            <div>
                                <label for="otp-code" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1 text-center">6-Digit Verification Code</label>
                                <input id="otp-code" type="text" required pattern="[0-9]{6}" maxlength="6" placeholder="000000" x-model="otpForm.otp_code"
                                    class="block w-full text-center tracking-[1em] text-xl font-bold px-4 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg placeholder-slate-300 focus:outline-none transition-all">
                            </div>

                            <!-- Dev OTP Hint for easier local testing -->
                            <div x-show="devOtp" class="p-2.5 bg-primary/5 border border-primary/20 rounded-lg text-xs text-primary text-center">
                                <strong>Local Dev OTP Code:</strong> <span class="font-mono text-sm font-bold" x-text="devOtp"></span>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full flex justify-center py-2.5 px-4 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-lg transition-all shadow-md shadow-primary/10 cursor-pointer disabled:opacity-50">
                                <span x-show="!loading">Verify & Sign In</span>
                                <span x-show="loading" class="flex items-center gap-1.5">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Verifying...
                                </span>
                            </button>
                        </form>

                        <div class="mt-4 text-center space-y-2">
                            <p class="text-xs text-slate-600">Didn't receive the code? <a href="#" @click.prevent="handleResendOtp()" class="font-semibold text-primary hover:text-primary-hover transition-colors">Resend Code</a></p>
                            <p class="text-[11px]"><a href="#" @click.prevent="setView('login')" class="text-slate-500 hover:text-slate-800 transition-colors">Back to Sign In</a></p>
                        </div>
                    </div>

                    <!-- 4. FORGOT PASSWORD SCREEN -->
                    <div x-show="view === 'forgot-password'">
                        <p class="text-xs text-slate-600 mb-4 text-center">Enter your email address and we'll send you a verification code to reset your password.</p>
                        
                        <form @submit.prevent="handleForgotPassword()" class="space-y-4">
                            <div>
                                <label for="forgot-email" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Email Address</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002-2v-10a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </span>
                                    <input id="forgot-email" type="email" required placeholder="john@example.com" x-model="forgotForm.email"
                                        class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                </div>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full flex justify-center py-2.5 px-4 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-lg transition-all shadow-md shadow-primary/10 cursor-pointer disabled:opacity-50">
                                <span x-show="!loading">Send Reset Code</span>
                                <span x-show="loading" class="flex items-center gap-1.5">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Sending...
                                </span>
                            </button>
                        </form>

                        <div class="mt-4 text-center">
                            <a href="#" @click.prevent="setView('login')" class="text-xs font-semibold text-primary hover:text-primary-hover transition-colors">Back to Sign In</a>
                        </div>
                    </div>

                    <!-- 5. RESET PASSWORD SCREEN -->
                    <div x-show="view === 'reset-password'">
                        <p class="text-xs text-slate-600 mb-4 text-center">Enter the reset code sent to your email and your new password below.</p>
                        
                        <form @submit.prevent="handleResetPassword()" class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Email Address</label>
                                <input type="email" readonly x-model="resetForm.email" class="block w-full px-3 py-2 bg-slate-100 border border-slate-200 text-slate-500 rounded-lg text-xs outline-none">
                            </div>

                            <div>
                                <label for="reset-otp" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">6-Digit Reset Code</label>
                                <input id="reset-otp" type="text" required placeholder="000000" maxlength="6" x-model="resetForm.otp_code"
                                    class="block w-full px-3 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                            </div>

                            <div x-show="devOtp" class="p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800 text-center">
                                <strong>Local Reset OTP Code:</strong> <span class="font-mono text-sm font-bold" x-text="devOtp"></span>
                            </div>

                            <div>
                                <label for="reset-password" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">New Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </span>
                                    <input id="reset-password" type="password" required placeholder="••••••••" x-model="resetForm.password"
                                        class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                </div>
                            </div>

                            <div>
                                <label for="reset-password-conf" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Confirm New Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </span>
                                    <input id="reset-password-conf" type="password" required placeholder="••••••••" x-model="resetForm.password_confirmation"
                                        class="block w-full pl-9 pr-4 py-2 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                </div>
                            </div>

                            <button type="submit" :disabled="loading" class="w-full flex justify-center py-2.5 px-4 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-lg transition-all shadow-md shadow-primary/10 cursor-pointer disabled:opacity-50">
                                <span x-show="!loading">Reset Password</span>
                                <span x-show="loading" class="flex items-center gap-1.5">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Resetting...
                                </span>
                            </button>
                        </form>

                        <div class="mt-4 text-center">
                            <a href="#" @click.prevent="setView('login')" class="text-xs font-semibold text-primary hover:text-primary-hover transition-colors">Back to Sign In</a>
                        </div>
                    </div>

                    <!-- 4. SHOP SETUP SCREEN -->
                    <div x-show="view === 'shop-setup'">
                        <p class="text-xs text-slate-600 mb-4 text-center">Please fill in your shop details to complete your account setup and access your dashboard.</p>
                        
                        <form @submit.prevent="handleShopSetup()" class="space-y-3">
                            <!-- Logo Upload + Shop Name inline -->
                            <div class="flex gap-4 items-center mb-1">
                                <!-- Logo Upload -->
                                <div class="flex flex-col items-center space-y-0.5 flex-shrink-0">
                                    <label class="block text-[9px] font-bold uppercase tracking-wider text-slate-500">Shop Logo</label>
                                    <div class="relative group cursor-pointer">
                                        <input type="file" id="logo-input" accept="image/*" @change="handleLogoChange" class="hidden">
                                        <div @click="document.getElementById('logo-input').click()" 
                                             class="w-12 h-12 rounded-full border border-dashed border-slate-300 hover:border-primary flex items-center justify-center overflow-hidden bg-slate-50 transition-all relative">
                                            
                                            <!-- Preview Image -->
                                            <template x-if="logoPreviewUrl">
                                                <img :src="logoPreviewUrl" class="w-full h-full object-cover">
                                            </template>
                                            
                                            <!-- Default Icon -->
                                            <template x-if="!logoPreviewUrl">
                                                <div class="text-center p-0.5 text-slate-400 group-hover:text-primary transition-colors">
                                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="text-[7px] font-bold mt-0.5 block">Upload</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Shop Name -->
                                <div class="flex-1">
                                    <label for="setup-shop-name" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Shop Name</label>
                                    <input id="setup-shop-name" type="text" required placeholder="e.g. Super Mart" x-model="shopSetupForm.name"
                                        class="block w-full px-3 py-1.5 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <!-- Owner Name -->
                                <div>
                                    <label for="setup-owner-name" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Owner Name</label>
                                    <input id="setup-owner-name" type="text" required placeholder="John Doe" x-model="shopSetupForm.owner_name"
                                        class="block w-full px-3 py-1.5 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                </div>

                                <!-- Mobile Number -->
                                <div>
                                    <label for="setup-mobile" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Mobile Number</label>
                                    <input id="setup-mobile" type="text" required placeholder="e.g. 98765 43210" x-model="shopSetupForm.mobile"
                                        class="block w-full px-3 py-1.5 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                                </div>
                            </div>

                            <!-- GST Number -->
                            <div>
                                <label for="setup-gst" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">GST Number (Optional)</label>
                                <input id="setup-gst" type="text" placeholder="e.g. 22AAAAA1111A1Z1" x-model="shopSetupForm.gst_number"
                                    class="block w-full px-3 py-1.5 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs placeholder-slate-400 focus:outline-none transition-all">
                            </div>

                            <button type="submit" :disabled="loading" class="w-full flex justify-center py-2.5 px-4 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-lg transition-all shadow-md shadow-primary/10 cursor-pointer disabled:opacity-50">
                                <span x-show="!loading">Complete Setup</span>
                                <span x-show="loading" class="flex items-center gap-1.5">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Setting up...
                                </span>
                            </button>
                        </form>
                    </div>

                    <!-- 6. DASHBOARD & PROFILE (LOGGED IN) -->
                    <div x-show="view === 'dashboard'">
                        <div class="text-center space-y-3 mb-4">
                            <!-- Shop Logo / Initial -->
                            <div class="relative inline-block">
                                <template x-if="shop && shop.logo">
                                    <img :src="'/Dukanhisab/public/storage/' + shop.logo" class="h-16 w-16 rounded-full object-cover border-2 border-primary/20 shadow-md">
                                </template>
                                <template x-if="!shop || !shop.logo">
                                    <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary text-xl font-extrabold shadow-inner">
                                        <span x-text="shop ? shop.name.substring(0,2).toUpperCase() : 'DH'"></span>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-slate-800" x-text="shop ? shop.name : 'My Shop'"></div>
                                <p class="text-[10px] text-slate-500 font-medium" x-text="'Owner: ' + (user ? user.name : '')"></p>
                            </div>
                        </div>

                        <!-- Shop Details Card -->
                        <div class="bg-slate-50/50 rounded-xl p-3 border border-slate-100 mb-4 space-y-2 text-xs">
                            <div class="flex justify-between py-0.5 border-b border-slate-100">
                                <span class="text-slate-500">Email Address</span>
                                <span class="font-medium text-slate-800" x-text="user ? user.email : ''"></span>
                            </div>
                            <div class="flex justify-between py-0.5 border-b border-slate-100">
                                <span class="text-slate-500">Mobile Number</span>
                                <span class="font-medium text-slate-800" x-text="shop ? shop.mobile : ''"></span>
                            </div>
                            <div class="flex justify-between py-0.5">
                                <span class="text-slate-500">GST Number</span>
                                <span class="font-medium text-slate-800" x-text="shop && shop.gst_number ? shop.gst_number : 'Not Provided'"></span>
                            </div>
                        </div>

                        <!-- Change Password Accordion -->
                        <div class="border border-slate-200 rounded-xl overflow-hidden mb-4 bg-slate-50/50" x-data="{ open: false }">
                            <button type="button" @click="open = !open" class="w-full flex items-center justify-between p-3 text-xs font-semibold text-slate-800 hover:bg-slate-50 transition-colors">
                                <span>Change Password</span>
                                <svg class="w-3.5 h-3.5 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            
                            <div x-show="open" x-transition class="p-3 border-t border-slate-200 space-y-3 bg-white">
                                <form @submit.prevent="handleChangePassword()" class="space-y-3">
                                    <div>
                                        <label for="current-password" class="block text-[10px] font-semibold text-slate-500 mb-0.5">Current Password</label>
                                        <input id="current-password" type="password" required x-model="changePasswordForm.current_password"
                                            class="block w-full px-2.5 py-1.5 border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs outline-none transition-all">
                                    </div>
                                    <div>
                                        <label for="new-password" class="block text-[10px] font-semibold text-slate-500 mb-0.5">New Password</label>
                                        <input id="new-password" type="password" required x-model="changePasswordForm.new_password"
                                            class="block w-full px-2.5 py-1.5 border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs outline-none transition-all">
                                    </div>
                                    <div>
                                        <label for="new-password-conf" class="block text-[10px] font-semibold text-slate-500 mb-0.5">Confirm New Password</label>
                                        <input id="new-password-conf" type="password" required x-model="changePasswordForm.new_password_confirmation"
                                            class="block w-full px-2.5 py-1.5 border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg text-xs outline-none transition-all">
                                    </div>
                                    <button type="submit" :disabled="loading" class="w-full py-1.5 px-3 bg-primary hover:bg-primary-hover text-white text-[11px] font-semibold rounded-lg transition-colors cursor-pointer disabled:opacity-50">
                                        Update Password
                                    </button>
                                </form>
                            </div>
                        </div>

                        <button @click="handleLogout()" :disabled="loading" class="w-full flex justify-center py-2.5 px-4 border border-rose-200 text-rose-700 hover:bg-rose-50 text-xs font-semibold rounded-lg transition-all cursor-pointer disabled:opacity-50">
                            <span x-show="!loading">Logout</span>
                            <span x-show="loading">Logging out...</span>
                        </button>
                    </div>

                </div>
            </div>

            <!-- Footer copyright inside right panel -->
            <div class="w-full mx-auto mt-6 pt-4 text-center text-[10px] text-white/70 relative z-10 border-t border-white/10">
                &copy; {{ date('Y') }} DukanHisab Shop Panel. All rights reserved.
            </div>
        </div>

        </div>
    </div>

    <!-- SPA logic -->
    <script>
        function authApp() {
            return {
                view: 'login', // login, register, verify-otp, forgot-password, reset-password, shop-setup, dashboard
                loading: false,
                showPassword: false,
                token: localStorage.getItem('shopowner_token'),
                user: JSON.parse(localStorage.getItem('shopowner_user')),
                shop: JSON.parse(localStorage.getItem('shopowner_shop')),
                hasShop: localStorage.getItem('shopowner_has_shop') === 'true',
                toasts: [],
                devOtp: null,

                // Forms
                loginForm: { email: '', password: '', remember: false },
                registerForm: { first_name: '', last_name: '', mobile: '', email: '', password: '', password_confirmation: '' },
                otpForm: { email: '', otp_code: '' },
                forgotForm: { email: '' },
                resetForm: { email: '', otp_code: '', password: '', password_confirmation: '' },
                changePasswordForm: { current_password: '', new_password: '', new_password_confirmation: '' },
                shopSetupForm: { name: '', owner_name: '', mobile: '', gst_number: '' },
                logoFile: null,
                logoPreviewUrl: null,

                verificationEmail: '',

                init() {
                    // Check if token and shop exist, if so redirect to dashboard
                    if (this.token && this.hasShop) {
                        const redirectUrl = window.location.pathname.replace(/\/shopowner(\/.*)?$/, '/dukanhisab/');
                        window.location.href = redirectUrl;
                        return;
                    }
                    // Check if token exists, if so fetch latest status
                    if (this.token) {
                        this.fetchProfile();
                    }
                    
                    // Pre-fill email and password from Remember Me
                    const rememberedEmail = localStorage.getItem('shopowner_remember_email');
                    const rememberedPassword = localStorage.getItem('shopowner_remember_password');
                    if (rememberedEmail) {
                        this.loginForm.email = rememberedEmail;
                        this.loginForm.password = rememberedPassword || '';
                        this.loginForm.remember = true;
                    }
                    
                    // Simple URL state router
                    const path = window.location.pathname;
                    if (path.includes('/register') && !this.token) {
                        this.view = 'register';
                    } else if (path.includes('/forgot-password') && !this.token) {
                        this.view = 'forgot-password';
                    }
                },

                setView(newView) {
                    this.view = newView;
                    this.devOtp = null;
                    // Update URL state without reloading
                    const base = '/shopowner/';
                    const suffix = (newView === 'dashboard' || newView === 'shop-setup') ? '' : newView;
                    window.history.pushState(null, '', base + suffix);
                },

                subtitleText() {
                    switch(this.view) {
                        case 'login': return 'Sign in to access your shop dashboard';
                        case 'register': return 'Create a new account for your shop';
                        case 'verify-otp': return 'Verify your email address';
                        case 'forgot-password': return 'Recover your account password';
                        case 'reset-password': return 'Setup a new secure password';
                        case 'shop-setup': return 'Set up your shop details';
                        case 'dashboard': return 'Shop Owner Control Center';
                        default: return 'Shop Portal';
                    }
                },

                addToast(message, type = 'success') {
                    const id = Date.now();
                    this.toasts.push({ id, message, type });
                    setTimeout(() => this.removeToast(id), 5000);
                },

                removeToast(id) {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                },

                async handleRegister() {
                    if (!this.registerForm.password || this.registerForm.password.length < 8) {
                        this.addToast('Password must be at least 8 characters long.', 'error');
                        return;
                    }
                    this.registerForm.password_confirmation = this.registerForm.password;

                    this.loading = true;
                    try {
                        const response = await fetch('/api/v1/shopowner/register', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(this.registerForm)
                        });
                        const data = await response.json();

                        if (response.ok) {
                            this.addToast(data.message, 'success');
                            this.verificationEmail = this.registerForm.email;
                            this.otpForm.email = this.registerForm.email;
                            if (data.dev_otp) this.devOtp = data.dev_otp;
                            this.setView('verify-otp');
                            this.registerForm = { first_name: '', last_name: '', mobile: '', email: '', password: '', password_confirmation: '' };
                        } else {
                            if (data.errors) {
                                Object.values(data.errors).forEach(errs => {
                                    errs.forEach(e => this.addToast(e, 'error'));
                                });
                            } else {
                                this.addToast(data.message || 'Registration failed.', 'error');
                            }
                        }
                    } catch (e) {
                        this.addToast('Connection error. Please try again.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async handleVerifyOtp() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/v1/shopowner/verify-otp', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(this.otpForm)
                        });
                        const data = await response.json();

                        if (response.ok) {
                            this.addToast(data.message, 'success');
                            this.token = data.token;
                            this.user = data.user;
                            this.shop = data.shop;
                            this.hasShop = data.has_shop;
                            localStorage.setItem('shopowner_token', data.token);
                            localStorage.setItem('shopowner_user', JSON.stringify(data.user));
                            localStorage.setItem('shopowner_has_shop', data.has_shop ? 'true' : 'false');
                            if (data.shop) {
                                localStorage.setItem('shopowner_shop', JSON.stringify(data.shop));
                            } else {
                                localStorage.removeItem('shopowner_shop');
                            }

                            // Prepopulate shopSetupForm from current user details
                            this.shopSetupForm.owner_name = this.user.name;
                            this.shopSetupForm.mobile = this.user.mobile || '';

                            if (this.hasShop) {
                                localStorage.setItem('token', data.token);
                                const redirectUrl = window.location.pathname.replace(/\/shopowner(\/.*)?$/, '/dukanhisab/');
                                window.location.href = redirectUrl;
                            } else {
                                this.setView('shop-setup');
                            }
                            this.otpForm = { email: '', otp_code: '' };
                        } else {
                            this.addToast(data.message || 'OTP verification failed.', 'error');
                        }
                    } catch (e) {
                        this.addToast('Connection error. Please try again.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async handleResendOtp() {
                    try {
                        const response = await fetch('/api/v1/shopowner/resend-otp', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ email: this.verificationEmail })
                        });
                        const data = await response.json();

                        if (response.ok) {
                            this.addToast(data.message, 'success');
                            if (data.dev_otp) this.devOtp = data.dev_otp;
                        } else {
                            this.addToast(data.message || 'Failed to resend OTP.', 'error');
                        }
                    } catch (e) {
                        this.addToast('Connection error. Please try again.', 'error');
                    }
                },

                async handleLogin() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/v1/shopowner/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(this.loginForm)
                        });
                        const data = await response.json();

                        if (response.ok) {
                            this.addToast(data.message, 'success');
                            
                            // Save or remove remembered email and password
                            if (this.loginForm.remember) {
                                localStorage.setItem('shopowner_remember_email', this.loginForm.email);
                                localStorage.setItem('shopowner_remember_password', this.loginForm.password);
                            } else {
                                localStorage.removeItem('shopowner_remember_email');
                                localStorage.removeItem('shopowner_remember_password');
                            }

                            this.token = data.token;
                            this.user = data.user;
                            this.shop = data.shop;
                            this.hasShop = data.has_shop;
                            localStorage.setItem('shopowner_token', data.token);
                            localStorage.setItem('shopowner_user', JSON.stringify(data.user));
                            localStorage.setItem('shopowner_has_shop', data.has_shop ? 'true' : 'false');
                            if (data.shop) {
                                localStorage.setItem('shopowner_shop', JSON.stringify(data.shop));
                            } else {
                                localStorage.removeItem('shopowner_shop');
                            }

                            // Prepopulate shopSetupForm from current user details
                            this.shopSetupForm.owner_name = this.user.name;
                            this.shopSetupForm.mobile = this.user.mobile || '';

                            if (this.hasShop) {
                                localStorage.setItem('token', data.token);
                                const redirectUrl = window.location.pathname.replace(/\/shopowner(\/.*)?$/, '/dukanhisab/');
                                window.location.href = redirectUrl;
                            } else {
                                this.setView('shop-setup');
                            }
                            
                            const rememberedEmail = localStorage.getItem('shopowner_remember_email');
                            const rememberedPassword = localStorage.getItem('shopowner_remember_password');
                            this.loginForm = { 
                                email: rememberedEmail || '', 
                                password: rememberedPassword || '', 
                                remember: !!rememberedEmail 
                            };
                        } else {
                            if (data.email_unverified) {
                                this.addToast(data.message, 'warning');
                                this.verificationEmail = data.email;
                                this.otpForm.email = data.email;
                                // Get dev OTP by triggering a resend
                                await this.handleResendOtp();
                                this.setView('verify-otp');
                            } else {
                                this.addToast(data.message || 'Invalid credentials.', 'error');
                            }
                        }
                    } catch (e) {
                        this.addToast('Connection error. Please try again.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async handleForgotPassword() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/v1/shopowner/forgot-password', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(this.forgotForm)
                        });
                        const data = await response.json();

                        if (response.ok) {
                            this.addToast(data.message, 'success');
                            this.resetForm.email = this.forgotForm.email;
                            if (data.dev_otp) this.devOtp = data.dev_otp;
                            this.setView('reset-password');
                            this.forgotForm = { email: '' };
                        } else {
                            this.addToast(data.message || 'Failed to request reset.', 'error');
                        }
                    } catch (e) {
                        this.addToast('Connection error. Please try again.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async handleResetPassword() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/v1/shopowner/reset-password', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(this.resetForm)
                        });
                        const data = await response.json();

                        if (response.ok) {
                            this.addToast(data.message, 'success');
                            this.setView('login');
                            this.resetForm = { email: '', otp_code: '', password: '', password_confirmation: '' };
                        } else {
                            if (data.errors) {
                                Object.values(data.errors).forEach(errs => {
                                    errs.forEach(e => this.addToast(e, 'error'));
                                });
                            } else {
                                this.addToast(data.message || 'Password reset failed.', 'error');
                            }
                        }
                    } catch (e) {
                        this.addToast('Connection error. Please try again.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async handleChangePassword() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/v1/shopowner/change-password', {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json', 
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            },
                            body: JSON.stringify(this.changePasswordForm)
                        });
                        const data = await response.json();

                        if (response.ok) {
                            this.addToast(data.message, 'success');
                            this.changePasswordForm = { current_password: '', new_password: '', new_password_confirmation: '' };
                        } else {
                            if (data.errors) {
                                Object.values(data.errors).forEach(errs => {
                                    errs.forEach(e => this.addToast(e, 'error'));
                                });
                            } else {
                                this.addToast(data.message || 'Password update failed.', 'error');
                            }
                        }
                    } catch (e) {
                        this.addToast('Connection error. Please try again.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async handleLogout() {
                    this.loading = true;
                    try {
                        await fetch('/api/v1/shopowner/logout', {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json', 
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            }
                        });
                    } catch (e) {
                        // Suppress logout API issues and clear local state anyway
                    } finally {
                        this.handleLogoutDirectly();
                        this.loading = false;
                        this.addToast('Logged out successfully.', 'success');
                    }
                },

                handleLogoutDirectly() {
                    localStorage.removeItem('shopowner_token');
                    localStorage.removeItem('shopowner_user');
                    localStorage.removeItem('shopowner_shop');
                    localStorage.removeItem('shopowner_has_shop');
                    localStorage.removeItem('token');
                    this.token = null;
                    this.user = null;
                    this.shop = null;
                    this.hasShop = false;
                    
                    // Reset loginForm and restore remembered email & password
                    const rememberedEmail = localStorage.getItem('shopowner_remember_email');
                    const rememberedPassword = localStorage.getItem('shopowner_remember_password');
                    this.loginForm = { 
                        email: rememberedEmail || '', 
                        password: rememberedPassword || '', 
                        remember: !!rememberedEmail 
                    };
                    
                    this.setView('login');
                },

                async fetchProfile() {
                    try {
                        const response = await fetch('/api/v1/shopowner/profile', {
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            }
                        });
                        const data = await response.json();
                        if (response.ok) {
                            this.user = data.user;
                            this.shop = data.shop;
                            this.hasShop = data.has_shop;
                            localStorage.setItem('shopowner_user', JSON.stringify(data.user));
                            localStorage.setItem('shopowner_has_shop', data.has_shop ? 'true' : 'false');
                            if (data.shop) {
                                localStorage.setItem('shopowner_shop', JSON.stringify(data.shop));
                            } else {
                                localStorage.removeItem('shopowner_shop');
                            }

                            // Prepopulate shopSetupForm from current user details
                            this.shopSetupForm.owner_name = this.user.name;
                            this.shopSetupForm.mobile = this.user.mobile || '';

                            if (this.hasShop) {
                                localStorage.setItem('token', this.token);
                                const redirectUrl = window.location.pathname.replace(/\/shopowner(\/.*)?$/, '/dukanhisab/');
                                window.location.href = redirectUrl;
                            } else {
                                this.setView('shop-setup');
                            }
                        } else {
                            this.handleLogoutDirectly();
                        }
                    } catch (e) {
                        if (this.hasShop) {
                            localStorage.setItem('token', this.token);
                            const redirectUrl = window.location.pathname.replace(/\/shopowner(\/.*)?$/, '/dukanhisab/');
                            window.location.href = redirectUrl;
                        } else {
                            this.setView('shop-setup');
                        }
                    }
                },

                handleLogoChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.logoFile = file;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.logoPreviewUrl = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                async handleShopSetup() {
                    this.loading = true;
                    try {
                        const formData = new FormData();
                        formData.append('name', this.shopSetupForm.name);
                        formData.append('owner_name', this.shopSetupForm.owner_name);
                        formData.append('mobile', this.shopSetupForm.mobile);
                        formData.append('gst_number', this.shopSetupForm.gst_number || '');
                        if (this.logoFile) {
                            formData.append('logo', this.logoFile);
                        }

                        const response = await fetch('/api/v1/shopowner/shop-setup', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            },
                            body: formData
                        });
                        const data = await response.json();

                        if (response.ok) {
                            this.addToast(data.message, 'success');
                            this.user = data.user;
                            this.shop = data.shop;
                            this.hasShop = data.has_shop;
                            localStorage.setItem('shopowner_user', JSON.stringify(data.user));
                            localStorage.setItem('shopowner_has_shop', 'true');
                            localStorage.setItem('shopowner_shop', JSON.stringify(data.shop));
                            localStorage.setItem('token', this.token);
                            
                            const redirectUrl = window.location.pathname.replace(/\/shopowner(\/.*)?$/, '/dukanhisab/');
                            window.location.href = redirectUrl;
                        } else {
                            if (data.errors) {
                                Object.values(data.errors).forEach(errs => {
                                    errs.forEach(e => this.addToast(e, 'error'));
                                });
                            } else {
                                this.addToast(data.message || 'Shop setup failed.', 'error');
                            }
                        }
                    } catch (e) {
                        this.addToast('Connection error. Please try again.', 'error');
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
</body>
</html>

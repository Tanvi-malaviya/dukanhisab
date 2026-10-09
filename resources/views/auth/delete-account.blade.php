<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account & Data Deletion - DukanHisab</title>
    <meta name="description" content="Request permanent account and business data deletion for DukanHisab Android App and Web." />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#0F766E', hover: '#115E59', light: '#CCFBF1' },
                        secondary: { DEFAULT: '#14B8A6', hover: '#0D9488' }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Outfit"', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between bg-slate-50 text-slate-800 antialiased">

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5">
                <span class="p-1.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </span>
                <span class="text-xl font-bold tracking-tight text-slate-900">Dukan<span class="text-teal-700">Hisab</span></span>
            </a>
            <div class="flex items-center gap-3">
                <a href="/shop/login" class="text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">Sign In</a>
                <a href="/shop" class="px-3.5 py-1.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs sm:text-sm font-bold shadow-xs transition-all">Open Web App</a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12 flex-1 w-full space-y-8">

        <!-- Page Header -->
        <div class="text-center space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold uppercase tracking-wider">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                <span>Data Safety & Account Management</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Request Account & Data Deletion
            </h1>
            <p class="text-sm text-slate-600 max-w-2xl mx-auto leading-relaxed">
                Learn how account deletion works for the DukanHisab mobile app (Google Play) and web platform, or permanently delete your account using the form below.
            </p>
        </div>

        @if(session('success_deleted'))
            <!-- Success Alert Banner -->
            <div class="p-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-center space-y-3 shadow-xs">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto text-2xl">
                    ✓
                </div>
                <h3 class="text-base font-bold text-emerald-900">Account Deleted Successfully</h3>
                <p class="text-xs text-emerald-700 max-w-md mx-auto">
                    {{ session('success_deleted') }}
                </p>
                <div class="pt-2">
                    <a href="/shop/register" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                        Create New Account
                    </a>
                </div>
            </div>
        @endif

        <!-- Card 1: Policy & Data Retention Explanation -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center text-xs font-bold">1</span>
                <span>What Happens When You Delete Your Account?</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-rose-50/70 border border-rose-100 space-y-2">
                    <h4 class="text-xs font-bold text-rose-800 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Data Permanently Purged:</span>
                    </h4>
                    <ul class="text-xs text-slate-700 space-y-1.5 list-disc pl-4 leading-relaxed">
                        <li><strong>Personal Profile:</strong> Name, mobile number, email, and password.</li>
                        <li><strong>All Registered Shops:</strong> Shop profiles, GSTIN, UPI settings.</li>
                        <li><strong>Transactions & Ledgers:</strong> All sales invoices, purchases, customer dues, supplier dues, and cashbook logs.</li>
                        <li><strong>Inventory & Products:</strong> Products, categories, and stock movement records.</li>
                        <li><strong>Cloud Media:</strong> Uploaded shop logos, signatures, and backups.</li>
                        <li><strong>WhatsApp Balance:</strong> Message history and remaining message credits.</li>
                    </ul>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Data Retention & Timeframe:</span>
                    </h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Deletion takes effect <strong>immediately</strong> upon submission. All active sessions, authentication tokens, and personal data records are wiped from our active databases.
                    </p>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Routine database server backups may retain encrypted snapshots for up to 30 days solely for disaster recovery, after which they expire automatically.
                    </p>
                </div>
            </div>

            <!-- In-App Deletion Instructions -->
            <div class="p-4 rounded-2xl bg-teal-50/70 border border-teal-200/80 space-y-2">
                <h4 class="text-xs font-bold text-teal-900 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <span>How to Delete Account In-App (Mobile App & Web Panel):</span>
                </h4>
                <ol class="text-xs text-teal-950 space-y-1 list-decimal pl-5 leading-relaxed">
                    <li>Open the <strong>DukanHisab App</strong> or sign in at <strong>dukanhisab.in/shop</strong>.</li>
                    <li>Navigate to <strong>Settings</strong> ➔ <strong>Security</strong> tab.</li>
                    <li>Scroll down to the <strong>Danger Zone: Delete Account</strong> section.</li>
                    <li>Click <strong>Delete Account</strong>, enter your password, type <strong>DELETE</strong> to confirm, and click Permanently Delete.</li>
                </ol>
            </div>
        </div>

        <!-- Card 2: Self-Service Web Deletion Form -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
            <div class="flex items-start gap-3">
                <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">2</span>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Direct Web Account Deletion</h2>
                    <p class="text-xs text-slate-500 mt-0.5">If you uninstalled the mobile app or cannot log in, enter your credentials below to immediately purge your account.</p>
                </div>
            </div>

            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Please correct the errors below:</span>
                    </div>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('account.delete_process') }}" method="POST" class="space-y-4" onsubmit="return confirm('WARNING: Are you absolutely sure? All your shops, invoices, and data will be permanently wiped. This action cannot be reversed.')">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                        Registered Mobile Number or Email Address
                    </label>
                    <input type="text" name="identity" required value="{{ old('identity') }}"
                        placeholder="e.g. 9876543210 or yourname@example.com"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-rose-500 focus:bg-white focus:outline-none rounded-xl text-sm text-slate-900 shadow-2xs">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                        Account Password
                    </label>
                    <input type="password" name="password" required
                        placeholder="Enter your current password"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-rose-500 focus:bg-white focus:outline-none rounded-xl text-sm text-slate-900 shadow-2xs">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                        Reason for leaving <span class="text-slate-400 font-normal lowercase">(optional)</span>
                    </label>
                    <select name="reason" class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-rose-500 focus:bg-white focus:outline-none rounded-xl text-sm text-slate-700 shadow-2xs">
                        <option value="">Select a reason...</option>
                        <option value="closing_business">Closing business / No longer running shop</option>
                        <option value="switching_app">Switching to another accounting software</option>
                        <option value="privacy_concerns">Privacy or data concerns</option>
                        <option value="testing_only">Was only testing the app</option>
                        <option value="other">Other reason</option>
                    </select>
                </div>

                <div class="pt-2">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="confirm_understanding" value="1" required class="mt-1 w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                        <span class="text-xs text-slate-700 select-none leading-relaxed">
                            I understand that this action is <strong>permanent and irreversible</strong>. All my shop databases, ledger history, sales records, and purchased WhatsApp credits will be permanently deleted and cannot be recovered.
                        </span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400">Need help? Email <a href="mailto:support@dukanhisab.in" class="text-teal-700 hover:underline">support@dukanhisab.in</a></span>
                    <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        <span>Permanently Delete My Account</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Assisted Deletion Card -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600">Assisted Support Request</h4>
                <p class="text-xs text-slate-500">
                    If you don't remember your password or encounter issues, our support team will delete your account manually upon identity verification.
                </p>
            </div>
            <a href="mailto:support@dukanhisab.in?subject=Account%20Deletion%20Request"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition-all whitespace-nowrap text-center">
                Contact Support Desk
            </a>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} DukanHisab. All rights reserved. Complies with Google Play Data Safety policies.</p>
    </footer>

</body>
</html>

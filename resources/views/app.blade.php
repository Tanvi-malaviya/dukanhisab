<!DOCTYPE html>
<html lang="en" :class="dark ? 'dark' : ''" x-data="appState()">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DukanHisab - Shop Owner Panel</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
                    fontFamily: { sans: ['"Poppins"', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>

    <!-- AlpineJS -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- App CSS -->
    @vite(['resources/css/app.css'])

    <style>
        body { font-family: 'Poppins', sans-serif; }
        /* Hide elements with x-cloak until Alpine.js fully initializes */
        [x-cloak] { display: none !important; }
        .glass-card {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .dark .glass-card {
            background: rgba(17,24,39,0.85);
            border-color: rgba(31,41,55,0.5);
        }
        /* Invoice Themed Header Contrast Overrides */
        .invoice-theme-header.text-white,
        .invoice-theme-header.text-white * {
            color: #ffffff !important;
        }
        .invoice-theme-header.text-slate-900,
        .invoice-theme-header.text-slate-900 * {
            color: #0f172a !important;
        }
        /* Test Print: while active, print only the invoice preview area */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            /* Force light backgrounds and dark text for all invoice areas when printing, regardless of dark mode */
            .dark #print-area,
            .dark #purchase-print-area,
            .dark #invoicePreviewPrintArea {
                background-color: #ffffff !important;
                color: #0f172a !important;
            }
            .dark #print-area *,
            .dark #purchase-print-area *,
            .dark #invoicePreviewPrintArea * {
                border-color: #e2e8f0 !important;
            }
            .dark #print-area td, .dark #print-area th, .dark #print-area p, .dark #print-area span,
            .dark #purchase-print-area td, .dark #purchase-print-area th, .dark #purchase-print-area p, .dark #purchase-print-area span,
            .dark #invoicePreviewPrintArea td, .dark #invoicePreviewPrintArea th, .dark #invoicePreviewPrintArea p, .dark #invoicePreviewPrintArea span {
                color: #1e293b !important;
            }
            /* Preserve text colors inside dynamic themed header boxes */
            .dark #print-area [style*="background-color"] *,
            .dark #purchase-print-area [style*="background-color"] *,
            .dark #invoicePreviewPrintArea [style*="background-color"] * {
                color: currentColor !important;
            }

            body.printing-invoice-preview * { visibility: hidden; }
            body.printing-invoice-preview #invoicePreviewPrintArea,
            body.printing-invoice-preview #invoicePreviewPrintArea * { visibility: visible; }
            body.printing-invoice-preview #invoicePreviewPrintArea {
                position: fixed;
                inset: 0;
                margin: auto;
                z-index: 99999;
                background: white !important;
            }

            body.printing-sale-invoice * { visibility: hidden; }
            body.printing-sale-invoice #print-area,
            body.printing-sale-invoice #print-area * { visibility: visible; }
            body.printing-sale-invoice #print-area {
                position: fixed;
                inset: 0;
                margin: auto;
                z-index: 99999;
                background: white !important;
                color: black !important;
                padding: 10px;
            }

            body.printing-purchase-invoice * { visibility: hidden; }
            body.printing-purchase-invoice #purchase-print-area,
            body.printing-purchase-invoice #purchase-print-area * { visibility: visible; }
            body.printing-purchase-invoice #purchase-print-area {
                position: fixed;
                inset: 0;
                margin: auto;
                z-index: 99999;
                background: white !important;
                color: black !important;
                padding: 10px;
            }
        }
    </style>
</head>
<body class="h-full bg-slate-50 dark:bg-gray-900 text-slate-900 dark:text-slate-100 transition-colors duration-200">

    {{-- Global Loading Overlay --}}
    <div x-show="loading" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="flex flex-col items-center p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-xl">
            <svg class="animate-spin h-10 w-10 text-primary" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Processing...</span>
        </div>
    </div>

    {{-- Toast Notification --}}
    <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 max-w-sm w-full">
        <template x-if="toast.show">
            <div :class="{
                'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300': toast.type === 'success',
                'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-300': toast.type === 'error',
                'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-300': toast.type === 'warning'
            }" class="p-4 rounded-xl border shadow-lg flex items-start gap-3 transform transition-all duration-300">
                <span class="mt-0.5">
                    <template x-if="toast.type === 'success'">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </template>
                </span>
                <div class="flex-1">
                    <p class="text-sm font-semibold" x-text="toast.message"></p>
                </div>
                <button @click="toast.show = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </template>
    </div>

    {{-- ===== AUTHENTICATION FLOW ===== --}}
    <template x-if="!token">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-50 dark:bg-gray-900">
            <div class="flex flex-col items-center">
                <svg class="animate-spin h-10 w-10 text-primary" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="mt-4 text-xs font-semibold text-slate-600 dark:text-slate-400">Redirecting to login...</span>
            </div>
        </div>
    </template>

    {{-- ===== SHOP SETUP FLOW ===== --}}
    <template x-if="token && !hasShop">
        @include('shopowner.partials.shop-setup')
    </template>

    {{-- ===== MAIN DASHBOARD LAYOUT ===== --}}
    <template x-if="token && hasShop">
        <div class="flex h-screen overflow-hidden">

            {{-- Sidebar --}}
            @include('shopowner.partials.sidebar')

            {{-- Main Workspace --}}
            <div class="flex flex-col flex-1 overflow-hidden">

                {{-- Top Header --}}
                @include('shopowner.partials.header')

                {{-- Page Content --}}
                <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 md:p-4 bg-slate-50 dark:bg-gray-900">

                    @include('shopowner.partials.dashboard')
                    @include('shopowner.partials.sales-pos')
                    @include('shopowner.partials.sales-history')
                    @include('shopowner.partials.sales-returned')
                    @include('shopowner.partials.products')
                    @include('shopowner.partials.customers')
                    @include('shopowner.partials.suppliers')
                    @include('shopowner.partials.expenses')
                    @include('shopowner.partials.purchases')
                    @include('shopowner.partials.purchase-returned')
                    @include('shopowner.partials.inventory')
                    @include('shopowner.partials.cashbook')
                    @include('shopowner.partials.bank-accounts')
                    @include('shopowner.partials.transactions')
                    @include('shopowner.partials.reports')
                    @include('shopowner.partials.reminders')
                    @include('shopowner.partials.settings')

                </main>
            </div>
        </div>
    </template>

    {{-- ===== ALL MODALS (outside x-if — always in DOM, Alpine reactivity works correctly) ===== --}}
    @include('shopowner.partials.modals')

    {{-- Alpine JS Controller --}}
    @include('shopowner.partials.scripts')

</body>
</html>

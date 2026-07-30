{{-- SIDEBAR --}}
<div class="hidden md:flex md:flex-shrink-0">
    <div class="flex flex-col w-64 bg-white dark:bg-gray-800 border-r border-slate-200 dark:border-gray-700">
        <div class="flex items-center justify-between h-16 px-4 border-b border-slate-200 dark:border-gray-700">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold overflow-hidden">
                    <template x-if="shop && shop.logo">
                        <img :src="'/storage/' + shop.logo" class="w-full h-full object-cover rounded-lg">
                    </template>
                    <template x-if="!shop || !shop.logo">
                        <span>DH</span>
                    </template>
                </div>
                <span class="font-bold text-slate-900 dark:text-white truncate max-w-[140px]" x-text="shop ? shop.name : 'DukanHisab'"></span>
            </div>
        </div>

        <div class="flex flex-col flex-1 overflow-y-auto pt-5 pb-4">
            <nav class="flex-1 px-3 space-y-1">

                {{-- Dashboard --}}
                <a href="/dukanhisab/"
                    @click.prevent="navigateTo('dashboard')"
                    :class="page === 'dashboard' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                    class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
                    Dashboard
                </a>

                {{-- Sales Group --}}
                <div x-data="{ openSales: false }" x-effect="if (['sales', 'sales-history', 'sales-returned', 'customers'].includes(page)) openSales = true" class="space-y-1">
                    <button @click="openSales = !openSales"
                        :class="['sales', 'sales-history', 'sales-returned', 'customers'].includes(page) ? 'text-primary font-bold bg-slate-50 dark:bg-gray-700/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                        class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-all focus:outline-none cursor-pointer">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span>Sales</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openSales ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="openSales" x-cloak class="pl-4 space-y-1 border-l border-slate-100 dark:border-gray-700 ml-5 transition-all">
                        {{-- Sales POS --}}
                        <a href="/dukanhisab/sales"
                            @click.prevent="navigateTo('sales')"
                            :class="page === 'sales' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Sales (POS)
                        </a>

                        {{-- Sales History --}}
                        <a href="/dukanhisab/sales-history"
                            @click.prevent="navigateTo('sales-history')"
                            :class="page === 'sales-history' ? 'text-primary font-bold' : 'text-primary dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Sales History
                        </a>

                        {{-- Sales Returned --}}
                        <a href="/dukanhisab/sales-returned"
                            @click.prevent="navigateTo('sales-returned')"
                            :class="page === 'sales-returned' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Sales Returned
                        </a>

                        {{-- Customers --}}
                        <a href="/dukanhisab/customers"
                            @click.prevent="navigateTo('customers')"
                            :class="page === 'customers' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Customers
                        </a>
                    </div>
                </div>

                {{-- Purchases Group --}}
                <div x-data="{ openPurchases: false }" x-effect="if (['purchases', 'purchase-history', 'purchase-returned', 'suppliers'].includes(page)) openPurchases = true" class="space-y-1">
                    <button @click="openPurchases = !openPurchases"
                        :class="['purchases', 'purchase-history', 'purchase-returned', 'suppliers'].includes(page) ? 'text-primary font-bold bg-slate-50 dark:bg-gray-700/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                        class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-all focus:outline-none cursor-pointer">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <span>Purchases</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openPurchases ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
 
                    <div x-show="openPurchases" x-cloak class="pl-4 space-y-1 border-l border-slate-100 dark:border-gray-700 ml-5 transition-all">
                        {{-- Purchase --}}
                        <a href="/dukanhisab/purchases"
                            @click.prevent="navigateTo('purchases')"
                            :class="page === 'purchases' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Purchase
                        </a>
 
                        {{-- Purchase History --}}
                        <a href="/dukanhisab/purchase-history"
                            @click.prevent="navigateTo('purchase-history')"
                            :class="page === 'purchase-history' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Purchase History
                        </a>

                        {{-- Purchase Returned --}}
                        <a href="/dukanhisab/purchase-returned"
                            @click.prevent="navigateTo('purchase-returned')"
                            :class="page === 'purchase-returned' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Purchases Returned
                        </a>
 
                        {{-- Suppliers --}}
                        <a href="/dukanhisab/suppliers"
                            @click.prevent="navigateTo('suppliers')"
                            :class="page === 'suppliers' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Suppliers
                        </a>
                    </div>
                </div>

                {{-- Expenses --}}
                <a href="/dukanhisab/expenses"
                    @click.prevent="navigateTo('expenses')"
                    :class="page === 'expenses' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                    class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Expenses
                </a>

                {{-- Inventory --}}
                <a href="/dukanhisab/inventory"
                    @click.prevent="navigateTo('inventory')"
                    :class="page === 'inventory' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                    class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Inventory
                </a>

                {{-- Ledger Group --}}
                <div x-data="{ openLedger: false }" x-effect="if (['cashbook', 'bank-accounts'].includes(page)) openLedger = true" class="space-y-1">
                    <button @click="openLedger = !openLedger"
                        :class="['cashbook', 'bank-accounts'].includes(page) ? 'text-primary font-bold bg-slate-50 dark:bg-gray-700/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                        class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-all focus:outline-none cursor-pointer">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Ledger</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openLedger ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="openLedger" x-cloak class="pl-4 space-y-1 border-l border-slate-100 dark:border-gray-700 ml-5 transition-all">
                        {{-- Cashbook --}}
                        <a href="/dukanhisab/cashbook"
                            @click.prevent="navigateTo('cashbook')"
                            :class="page === 'cashbook' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Cashbook
                        </a>

                        {{-- BankAccount --}}
                        <a href="/dukanhisab/bank-accounts"
                            @click.prevent="navigateTo('bank-accounts')"
                            :class="page === 'bank-accounts' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Bank Accounts
                        </a>
                    </div>
                </div>

                {{-- Reports Group --}}
                <div x-data="{ openReports: false }" x-effect="if (['transactions', 'reports'].includes(page)) openReports = true" class="space-y-1">
                    <button @click="openReports = !openReports"
                        :class="['transactions', 'reports'].includes(page) ? 'text-primary font-bold bg-slate-50 dark:bg-gray-700/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                        class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-all focus:outline-none cursor-pointer">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span>Reports</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openReports ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="openReports" x-cloak class="pl-4 space-y-1 border-l border-slate-100 dark:border-gray-700 ml-5 transition-all">
                        {{-- Transactions --}}
                        <a href="/dukanhisab/transactions"
                            @click.prevent="navigateTo('transactions')"
                            :class="page === 'transactions' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Transactions
                        </a>

                        {{-- Reports --}}
                        <a href="/dukanhisab/reports"
                            @click.prevent="navigateTo('reports')"
                            :class="page === 'reports' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                            class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Reports
                        </a>
                    </div>
                </div>

                {{-- Reminders --}}
                <a href="/dukanhisab/reminders"
                    @click.prevent="navigateTo('reminders')"
                    :class="page === 'reminders' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                    class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    Reminders
                </a>

                {{-- Settings --}}
                <a href="/dukanhisab/settings"
                    @click.prevent="navigateTo('settings')"
                    :class="page === 'settings' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                    class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Settings
                </a>

            </nav>
        </div>

    </div>
</div>

<!-- Mobile sidebar drawer -->
<div x-show="mobileSidebarOpen" class="relative z-40 md:hidden" role="dialog" aria-modal="true" x-cloak>
    <!-- Off-canvas menu backdrop -->
    <div x-show="mobileSidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileSidebarOpen = false"
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    <div class="fixed inset-0 z-40 flex">
        <!-- Off-canvas menu body -->
        <div x-show="mobileSidebarOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="relative flex w-full max-w-xs flex-1 flex-col bg-white dark:bg-gray-800 pt-5 pb-4 border-r border-slate-200 dark:border-gray-700">
             
             <!-- Logo/Shop Name & Close Button -->
             <div class="flex flex-shrink-0 items-center justify-between px-4 mb-5">
                 <div class="flex items-center gap-2.5">
                     <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold overflow-hidden">
                         <template x-if="shop && shop.logo">
                             <img :src="'/storage/' + shop.logo" class="w-full h-full object-cover rounded-lg">
                         </template>
                         <template x-if="!shop || !shop.logo">
                             <span>DH</span>
                         </template>
                     </div>
                     <span class="font-bold text-slate-900 dark:text-white truncate max-w-[155px]" x-text="shop ? shop.name : 'DukanHisab'"></span>
                 </div>
                 
                 <!-- Close Button -->
                 <button type="button" @click="mobileSidebarOpen = false" class="p-1 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none">
                     <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                     </svg>
                 </button>
             </div>

             <!-- Navigation -->
             <div class="flex-1 h-0 overflow-y-auto">
                 <nav class="space-y-1 px-3">
                     <!-- Dashboard -->
                     <a href="/dukanhisab/"
                         @click.prevent="navigateTo('dashboard'); mobileSidebarOpen = false"
                         :class="page === 'dashboard' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                         class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all gap-3">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
                         Dashboard
                     </a>
                     
                      <!-- Sales Group -->
                      <div x-data="{ openSales: false }" x-effect="if (['sales', 'sales-history', 'sales-returned', 'customers'].includes(page)) openSales = true" class="space-y-1">
                          <button @click="openSales = !openSales"
                              :class="['sales', 'sales-history', 'sales-returned', 'customers'].includes(page) ? 'text-primary font-bold bg-slate-50 dark:bg-gray-700/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                              class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-all focus:outline-none cursor-pointer">
                              <div class="flex items-center gap-3">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                  <span>Sales</span>
                              </div>
                              <svg class="w-4 h-4 transition-transform duration-200" :class="openSales ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                              </svg>
                          </button>

                          <div x-show="openSales" x-cloak class="pl-4 space-y-1 border-l border-slate-100 dark:border-gray-700 ml-5 transition-all">
                              <!-- Sales POS -->
                              <a href="/dukanhisab/sales"
                                  @click.prevent="navigateTo('sales'); mobileSidebarOpen = false"
                                  :class="page === 'sales' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Sales (POS)
                              </a>

                              <!-- Sales History -->
                              <a href="/dukanhisab/sales-history"
                                  @click.prevent="navigateTo('sales-history'); mobileSidebarOpen = false"
                                  :class="page === 'sales-history' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Sales History
                              </a>

                              <!-- Sales Returned -->
                              <a href="/dukanhisab/sales-returned"
                                  @click.prevent="navigateTo('sales-returned'); mobileSidebarOpen = false"
                                  :class="page === 'sales-returned' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Sales Returned
                              </a>

                              <!-- Customers -->
                              <a href="/dukanhisab/customers"
                                  @click.prevent="navigateTo('customers'); mobileSidebarOpen = false"
                                  :class="page === 'customers' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Customers
                              </a>
                          </div>
                      </div>

                     <!-- Products -->
                     <a href="/dukanhisab/products"
                         @click.prevent="navigateTo('products'); mobileSidebarOpen = false"
                         :class="page === 'products' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                         class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all gap-3">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                         Products
                     </a>

                      <!-- Purchases Group -->
                      <div x-data="{ openPurchases: false }" x-effect="if (['purchases', 'purchase-history', 'purchase-returned', 'suppliers'].includes(page)) openPurchases = true" class="space-y-1">
                          <button @click="openPurchases = !openPurchases"
                              :class="['purchases', 'purchase-history', 'purchase-returned', 'suppliers'].includes(page) ? 'text-primary font-bold bg-slate-50 dark:bg-gray-700/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                              class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-all focus:outline-none cursor-pointer">
                              <div class="flex items-center gap-3">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                  <span>Purchases</span>
                              </div>
                              <svg class="w-4 h-4 transition-transform duration-200" :class="openPurchases ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                              </svg>
                          </button>

                          <div x-show="openPurchases" x-cloak class="pl-4 space-y-1 border-l border-slate-100 dark:border-gray-700 ml-5 transition-all">
                              <!-- Purchase -->
                              <a href="/dukanhisab/purchases"
                                  @click.prevent="navigateTo('purchases'); mobileSidebarOpen = false"
                                  :class="page === 'purchases' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Purchase
                              </a>

                              <!-- Purchase History -->
                              <a href="/dukanhisab/purchase-history"
                                  @click.prevent="navigateTo('purchase-history'); mobileSidebarOpen = false"
                                  :class="page === 'purchase-history' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Purchase History
                              </a>

                              <!-- Purchases Returned -->
                              <a href="/dukanhisab/purchase-returned"
                                  @click.prevent="navigateTo('purchase-returned'); mobileSidebarOpen = false"
                                  :class="page === 'purchase-returned' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Purchases Returned
                              </a>

                              <!-- Suppliers -->
                              <a href="/dukanhisab/suppliers"
                                  @click.prevent="navigateTo('suppliers'); mobileSidebarOpen = false"
                                  :class="page === 'suppliers' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Suppliers
                              </a>
                          </div>
                      </div>

                      <!-- Expenses -->
                      <a href="/dukanhisab/expenses"
                          @click.prevent="navigateTo('expenses'); mobileSidebarOpen = false"
                          :class="page === 'expenses' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                          class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all gap-3">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                          Expenses
                      </a>

                      <!-- Inventory -->
                      <a href="/dukanhisab/inventory"
                          @click.prevent="navigateTo('inventory'); mobileSidebarOpen = false"
                          :class="page === 'inventory' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                          class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all gap-3">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                          Inventory
                      </a>

                      <!-- Ledger Group -->
                      <div x-data="{ openLedger: false }" x-effect="if (['cashbook', 'bank-accounts'].includes(page)) openLedger = true" class="space-y-1">
                          <button @click="openLedger = !openLedger"
                              :class="['cashbook', 'bank-accounts'].includes(page) ? 'text-primary font-bold bg-slate-50 dark:bg-gray-700/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                              class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-all focus:outline-none cursor-pointer">
                              <div class="flex items-center gap-3">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                  <span>Ledger</span>
                              </div>
                              <svg class="w-4 h-4 transition-transform duration-200" :class="openLedger ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                              </svg>
                          </button>

                          <div x-show="openLedger" x-cloak class="pl-4 space-y-1 border-l border-slate-100 dark:border-gray-700 ml-5 transition-all">
                              <!-- Cashbook -->
                              <a href="/dukanhisab/cashbook"
                                  @click.prevent="navigateTo('cashbook'); mobileSidebarOpen = false"
                                  :class="page === 'cashbook' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Cashbook
                              </a>

                              <!-- Bank Accounts -->
                              <a href="/dukanhisab/bank-accounts"
                                  @click.prevent="navigateTo('bank-accounts'); mobileSidebarOpen = false"
                                  :class="page === 'bank-accounts' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Bank Accounts
                              </a>
                          </div>
                      </div>

                      <!-- Reports Group -->
                      <div x-data="{ openReports: false }" x-effect="if (['transactions', 'reports'].includes(page)) openReports = true" class="space-y-1">
                          <button @click="openReports = !openReports"
                              :class="['transactions', 'reports'].includes(page) ? 'text-primary font-bold bg-slate-50 dark:bg-gray-700/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                              class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-all focus:outline-none cursor-pointer">
                              <div class="flex items-center gap-3">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                  <span>Reports</span>
                              </div>
                              <svg class="w-4 h-4 transition-transform duration-200" :class="openReports ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                              </svg>
                          </button>

                          <div x-show="openReports" x-cloak class="pl-4 space-y-1 border-l border-slate-100 dark:border-gray-700 ml-5 transition-all">
                              <!-- Transactions -->
                              <a href="/dukanhisab/transactions"
                                  @click.prevent="navigateTo('transactions'); mobileSidebarOpen = false"
                                  :class="page === 'transactions' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Transactions
                              </a>

                              <!-- Reports -->
                              <a href="/dukanhisab/reports"
                                  @click.prevent="navigateTo('reports'); mobileSidebarOpen = false"
                                  :class="page === 'reports' ? 'text-primary font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                                  class="flex items-center px-3 py-2 text-xs rounded-lg transition-all gap-2">
                                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                  Reports
                              </a>
                          </div>
                      </div>

                      <!-- Reminders -->
                      <a href="/dukanhisab/reminders"
                          @click.prevent="navigateTo('reminders'); mobileSidebarOpen = false"
                          :class="page === 'reminders' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                          class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all gap-3">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                          Reminders
                      </a>

                      <!-- Settings -->
                      <a href="/dukanhisab/settings"
                          @click.prevent="navigateTo('settings'); mobileSidebarOpen = false"
                          :class="page === 'settings' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700'"
                          class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all gap-3">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                          Settings
                      </a>
                  </nav>
             </div>

        </div>
    </div>
</div>

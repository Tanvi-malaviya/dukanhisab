{{-- DASHBOARD PANEL --}}
<div x-show="page === 'dashboard'" class="space-y-2">

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
        <div @click="navigateTo('sales-history')" class="card-sales p-3 border border-emerald-200 rounded-2xl flex flex-col justify-between shadow-sm bg-emerald-50 dark:bg-emerald-950/20 dark:border-emerald-800 cursor-pointer hover:scale-[1.02] hover:shadow-md transition-all">
            <div class="flex justify-between items-start gap-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 leading-tight">Today Sales</span>
                <div class="card-icon p-1 rounded-lg bg-white dark:bg-gray-800 text-emerald-600 shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></div>
            </div>
            <div class="mt-2"><span class="text-xl font-extrabold text-emerald-900 dark:text-emerald-200">₹<span x-text="dashboardStats.today_sales"></span></span></div>
        </div>

        <div @click="navigateTo('purchase-history')" class="card-purchase p-3 border border-blue-200 rounded-2xl flex flex-col justify-between shadow-sm bg-blue-50 dark:bg-blue-950/20 dark:border-blue-800 cursor-pointer hover:scale-[1.02] hover:shadow-md transition-all">
            <div class="flex justify-between items-start gap-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-800 dark:text-blue-300 leading-tight">Today Purchase</span>
                <div class="card-icon p-1 rounded-lg bg-white dark:bg-gray-800 text-blue-600 shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg></div>
            </div>
            <div class="mt-2"><span class="text-xl font-extrabold text-blue-900 dark:text-blue-200">₹<span x-text="dashboardStats.today_purchases"></span></span></div>
        </div>

        <div @click="navigateTo('cashbook')" class="card-cash p-3 border border-teal-200 rounded-2xl flex flex-col justify-between shadow-sm bg-teal-50 dark:bg-teal-950/20 dark:border-teal-800 cursor-pointer hover:scale-[1.02] hover:shadow-md transition-all">
            <div class="flex justify-between items-start gap-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-teal-800 dark:text-teal-300 leading-tight">Cash Balance</span>
                <div class="card-icon p-1 rounded-lg bg-white dark:bg-gray-800 text-teal-600 shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            </div>
            <div class="mt-2"><span class="text-xl font-extrabold text-teal-900 dark:text-teal-200">₹<span x-text="dashboardStats.cash_balance"></span></span></div>
        </div>

        <div @click="navigateTo('bank-accounts')" class="card-bank p-3 border border-sky-200 rounded-2xl flex flex-col justify-between shadow-sm bg-sky-50 dark:bg-sky-950/20 dark:border-sky-800 cursor-pointer hover:scale-[1.02] hover:shadow-md transition-all">
            <div class="flex justify-between items-start gap-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300 leading-tight">Bank Balance</span>
                <div class="card-icon p-1 rounded-lg bg-white dark:bg-gray-800 text-sky-600 shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg></div>
            </div>
            <div class="mt-2"><span class="text-xl font-extrabold text-sky-900 dark:text-sky-200">₹<span x-text="dashboardStats.bank_balance"></span></span></div>
        </div>

        <div @click="navigateTo('reminders')" class="card-customer-due p-3 border border-amber-200 rounded-2xl flex flex-col justify-between shadow-sm bg-amber-50 dark:bg-amber-950/20 dark:border-amber-800 cursor-pointer hover:scale-[1.02] hover:shadow-md transition-all">
            <div class="flex justify-between items-start gap-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 leading-tight">Customer Due</span>
                <div class="card-icon p-1 rounded-lg bg-white dark:bg-gray-800 text-amber-600 shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
            </div>
            <div class="mt-2"><span class="text-xl font-extrabold text-amber-900 dark:text-amber-200">₹<span x-text="dashboardStats.customer_due"></span></span></div>
        </div>

        <div @click="navigateTo('reminders')" class="card-supplier-due p-3 border border-rose-200 rounded-2xl flex flex-col justify-between shadow-sm bg-rose-50 dark:bg-rose-950/20 dark:border-rose-800 cursor-pointer hover:scale-[1.02] hover:shadow-md transition-all">
            <div class="flex justify-between items-start gap-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-800 dark:text-rose-300 leading-tight">Supplier Due</span>
                <div class="card-icon p-1 rounded-lg bg-white dark:bg-gray-800 text-rose-600 shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
            </div>
            <div class="mt-2"><span class="text-xl font-extrabold text-rose-900 dark:text-rose-200">₹<span x-text="dashboardStats.supplier_due"></span></span></div>
        </div>
    </div>

    {{-- QUICK ACTIONS --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
        <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 mb-3" x-text="t('quick_actions')">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <button @click="navigateTo('sales')" class="p-3 bg-primary/5 hover:bg-primary/10 text-primary rounded-xl flex flex-col items-center gap-2 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span class="text-xs font-semibold" x-text="t('new_sale') || 'New Sale'">New Sale</span>
            </button>
            <button @click="navigateTo('purchases')" class="p-3 bg-blue-500/5 hover:bg-blue-500/10 text-blue-600 rounded-xl flex flex-col items-center gap-2 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <span class="text-xs font-semibold" x-text="t('new_purchase') || 'New Purchase'">New Purchase</span>
            </button>
            <button @click="openNewProductModal()" class="p-3 bg-emerald-500/5 hover:bg-emerald-500/10 text-emerald-600 rounded-xl flex flex-col items-center gap-2 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-semibold" x-text="t('add_product')">Add Product</span>
            </button>
            <button @click="openNewCustomerModal()" class="p-3 bg-amber-500/5 hover:bg-amber-500/10 text-amber-600 rounded-xl flex flex-col items-center gap-2 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span class="text-xs font-semibold" x-text="t('add_customer')">Add Customer</span>
            </button>
            <button @click="openNewSupplierModal()" class="p-3 bg-indigo-500/5 hover:bg-indigo-500/10 text-indigo-600 rounded-xl flex flex-col items-center gap-2 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 17h1a2 2 0 002-2v-3a2 2 0 00-2.25-2H18M9 17h6M4 17h-1a1 1 0 01-1-1V5a1 1 0 011-1h10a1 1 0 011 1v12a1 1 0 01-1 1" />
                </svg>
                <span class="text-xs font-semibold" x-text="t('add_supplier')">Add Supplier</span>
            </button>
            <button @click="openNewExpenseModal()" class="p-3 bg-rose-500/5 hover:bg-rose-500/10 text-rose-600 rounded-xl flex flex-col items-center gap-2 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-semibold" x-text="t('add_expense')">Add Expense</span>
            </button>
        </div>
    </div>

    {{-- LOW STOCK & RECENT SALES --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200">Inventory Alert (Low Stock)</h3>
                <span class="px-2 py-0.5 bg-rose-100 text-rose-700 rounded-full text-xs font-bold" x-text="dashboardStats.low_stock_count"></span>
            </div>
            <div class="flex-1 overflow-y-auto space-y-3 max-h-[300px]">
                <template x-for="prod in dashboardStats.low_stock_products" :key="prod.id">
                    <div class="flex justify-between items-center p-3 bg-slate-50 dark:bg-gray-700/50 rounded-xl">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-white" x-text="prod.name"></p>
                            <p class="text-xs text-slate-400">Barcode: <span x-text="prod.barcode"></span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-rose-600">Stock: <span x-text="prod.stock"></span></p>
                            <p class="text-[10px] text-slate-400">Limit: <span x-text="prod.low_stock_threshold"></span></p>
                        </div>
                    </div>
                </template>
                <template x-if="dashboardStats.low_stock_products.length === 0">
                    <p class="text-sm text-slate-400 text-center py-8">All items are sufficiently stocked.</p>
                </template>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm lg:col-span-2 flex flex-col">
            <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 mb-3">Recent Sales</h3>
            <div class="flex-1 overflow-y-auto max-h-[300px]">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-bold text-slate-400 uppercase">Sale No</th>
                            <th class="px-3 py-2 text-left text-xs font-bold text-slate-400 uppercase">Customer</th>
                            <th class="px-3 py-2 text-left text-xs font-bold text-slate-400 uppercase">Total</th>
                            <th class="px-3 py-2 text-left text-xs font-bold text-slate-400 uppercase">Status</th>
                            <th class="px-3 py-2 text-left text-xs font-bold text-slate-400 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                        <template x-if="dashboardLoading">
                            <tr>
                                <td colspan="5" class="text-center py-8">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                                    <p class="text-xs text-slate-400 mt-2 font-medium">Loading dashboard...</p>
                                </td>
                            </tr>
                        </template>
                        <template x-for="sale in (dashboardLoading ? [] : dashboardStats.recent_sales)" :key="sale.id">
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50 cursor-pointer" @click="viewInvoice(sale.id)">
                                <td class="px-3 py-2.5 text-sm font-semibold text-primary" x-text="sale.sale_number"></td>
                                <td class="px-3 py-2.5 text-sm text-slate-700 dark:text-slate-300" x-text="sale.customer ? sale.customer.name : 'Walk-In'"></td>
                                <td class="px-3 py-2.5 text-sm font-bold text-slate-800 dark:text-white">₹<span x-text="sale.grand_total"></span></td>
                                <td class="px-3 py-2.5 text-sm">
                                    <span :class="sale.status === 'Returned' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'" class="px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap" x-text="sale.status"></span>
                                </td>
                                <td class="px-3 py-2.5 text-xs text-slate-500" x-text="new Date(sale.sale_date).toLocaleDateString()"></td>
                            </tr>
                        </template>
                        <template x-if="!dashboardLoading && dashboardStats.recent_sales.length === 0">
                            <tr><td colspan="5" class="text-sm text-slate-400 text-center py-8">No recent sales records found.</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

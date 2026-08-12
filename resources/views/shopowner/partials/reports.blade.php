{{-- REPORTS PANEL --}}
<div x-show="page === 'reports'" class="space-y-2" x-data="{ reportDates: { start: '', end: '' } }">

    {{-- Filter section --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row md:justify-between md:items-end gap-3">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('from_date')">From Date</label>
                <input type="date" x-model="reportDates.start" onclick="this.showPicker()" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white cursor-pointer">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('to_date')">To Date</label>
                <input type="date" x-model="reportDates.end" onclick="this.showPicker()" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white cursor-pointer">
            </div>
            <div class="flex gap-2">
                <button @click="loadReports(reportDates.start, reportDates.end)" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl transition-all" x-text="t('generate')">Generate</button>
                <button @click="reportDates.start = ''; reportDates.end = ''; loadReports('', '')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl transition-all" x-text="t('reset')">Reset</button>
            </div>
        </div>

        <div class="flex gap-2 w-full md:w-auto justify-end">
            <button @click="printReport()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span x-text="t('print_statement')">Print Statement</span>
            </button>
        </div>
    </div>

    <div id="report-print-area" class="space-y-2 relative" :class="reportsLoading ? 'min-h-[300px]' : ''">
        {{-- Loader overlay --}}
        <div x-show="reportsLoading" class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm z-30 flex items-center justify-center rounded-2xl">
            <div class="flex flex-col items-center gap-2">
                <svg class="animate-spin h-10 w-10 text-primary" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs font-semibold text-slate-500" x-text="t('generating_analytics')">Generating analytics...</span>
            </div>
        </div>

        {{-- KPI Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400" x-text="t('total_sales')">Total Sales</span>
                <p class="text-2xl font-extrabold text-slate-800 dark:text-white mt-1">₹<span x-text="parseFloat(reportsData.total_sales || 0).toFixed(2)"></span></p>
                <p class="text-xs text-slate-400 mt-2" x-text="`${reportsData.sales_count || 0} ${t('transactions_count') || 'Transactions'}`"></p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400" x-text="t('total_purchases')">Total Purchases</span>
                <p class="text-2xl font-extrabold text-slate-800 dark:text-white mt-1">₹<span x-text="parseFloat(reportsData.total_purchases || 0).toFixed(2)"></span></p>
                <p class="text-xs text-slate-400 mt-2" x-text="`${reportsData.purchases_count || 0} ${t('transactions_count') || 'Transactions'}`"></p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400" x-text="t('total_expenses')">Total Expenses</span>
                <p class="text-2xl font-extrabold text-slate-800 dark:text-white mt-1">₹<span x-text="parseFloat(reportsData.total_expenses || 0).toFixed(2)"></span></p>
                <p class="text-xs text-slate-400 mt-2" x-text="`${reportsData.expenses_count || 0} ${t('transactions_count') || 'Transactions'}`"></p>
            </div>
            <div class="p-4 rounded-2xl border shadow-sm" :class="(reportsData.net_profit || 0) >= 0 ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-800'">
                <span class="text-xs font-bold uppercase tracking-wider" :class="(reportsData.net_profit || 0) >= 0 ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300'" x-text="t('net_margin_profit')">Net Margin / Profit</span>
                <p class="text-2xl font-extrabold mt-1" :class="(reportsData.net_profit || 0) >= 0 ? 'text-emerald-900 dark:text-emerald-200' : 'text-rose-900 dark:text-rose-200'">₹<span x-text="parseFloat(reportsData.net_profit || 0).toFixed(2)"></span></p>
                <p class="text-xs text-slate-400 mt-2" x-text="t('estimated_margin')">Estimated Margin</p>
            </div>
        </div>

        {{-- Breakdown Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
            {{-- Sales by Payment Type --}}
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-2">
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200" x-text="t('revenue_by_payment_type')">Revenue Contribution by Payment Type</h4>
                <div class="space-y-4">
                    <template x-for="item in reportsData.sales_by_payment_type" :key="item.payment_type">
                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="uppercase text-slate-500" x-text="t(item.payment_type ? item.payment_type.toLowerCase() : '') || item.payment_type"></span>
                                <span class="text-slate-800 dark:text-white" x-text="'₹' + parseFloat(item.total).toFixed(2)"></span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                <div class="bg-primary h-full rounded-full" :style="`width: ${Math.min(100, (item.total / (reportsData.total_sales || 1)) * 100)}%`"></div>
                            </div>
                        </div>
                    </template>
                    <template x-if="!reportsData.sales_by_payment_type || reportsData.sales_by_payment_type.length === 0">
                        <p class="text-xs text-slate-400 py-4 text-center" x-text="t('no_payment_distributions')">No payment distributions recorded for this period.</p>
                    </template>
                </div>
            </div>

            {{-- Expenses details --}}
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-2">
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200" x-text="t('profit_loss_summary')">Profit & Loss Summary</h4>
                <div class="divide-y divide-slate-100 dark:divide-gray-700 text-xs">
                    <div class="flex justify-between py-2.5">
                        <span class="text-slate-500 font-semibold" x-text="t('gross_sales_revenue')">Gross Sales Revenue:</span>
                        <span class="font-bold text-emerald-600" x-text="'₹' + parseFloat(reportsData.total_sales || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <span class="text-slate-500 font-semibold" x-text="t('cost_of_goods_inward')">Cost of Goods Inward (Purchases):</span>
                        <span class="font-bold text-rose-600" x-text="'-₹' + parseFloat(reportsData.total_purchases || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <span class="text-slate-500 font-semibold" x-text="t('operating_expenses')">Operating Expenses:</span>
                        <span class="font-bold text-rose-600" x-text="'-₹' + parseFloat(reportsData.total_expenses || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between py-3 text-sm font-bold border-t border-slate-200 dark:border-gray-700">
                        <span class="text-slate-800 dark:text-white" x-text="t('net_estimated_income')">Net Estimated Income:</span>
                        <span :class="(reportsData.net_profit || 0) >= 0 ? 'text-emerald-600' : 'text-rose-600'" x-text="'₹' + parseFloat(reportsData.net_profit || 0).toFixed(2)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

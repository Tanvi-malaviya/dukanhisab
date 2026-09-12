{{-- CASHBOOK PANEL --}}
<div x-show="page === 'cashbook'" class="space-y-3" x-data="{ 
    showEntryModal: false, 
    entryForm: { type: 'cash_in', amount: '', payment_method: 'cash', description: '' },
    showDenominationModal: false,
    selectedClosure: null
}">

    {{-- STATS CARDS FOR CASHBOOK --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        {{-- Total Cash In --}}
        <div class="p-4 border border-emerald-200/80 dark:border-emerald-800/60 bg-gradient-to-br from-emerald-50 to-emerald-100/30 dark:from-emerald-950/30 dark:to-emerald-900/10 rounded-2xl flex items-center justify-between shadow-sm hover:shadow transition-all">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300" x-text="t('total_cash_in') || 'Total Cash In'">Total Cash In</span>
                <div class="mt-1 text-2xl font-black text-emerald-700 dark:text-emerald-300">₹<span x-text="calculateCashBookTotals().totalIn.toFixed(2)"></span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            </div>
        </div>

        {{-- Total Cash Out --}}
        <div class="p-4 border border-rose-200/80 dark:border-rose-800/60 bg-gradient-to-br from-rose-50 to-rose-100/30 dark:from-rose-950/30 dark:to-rose-900/10 rounded-2xl flex items-center justify-between shadow-sm hover:shadow transition-all">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-800 dark:text-rose-300" x-text="t('total_cash_out') || 'Total Cash Out'">Total Cash Out</span>
                <div class="mt-1 text-2xl font-black text-rose-700 dark:text-rose-300">₹<span x-text="calculateCashBookTotals().totalOut.toFixed(2)"></span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
            </div>
        </div>

        {{-- Net Balance --}}
        <div class="p-4 border border-teal-200/80 dark:border-teal-800/60 bg-gradient-to-br from-teal-50 to-teal-100/30 dark:from-teal-950/30 dark:to-teal-900/10 rounded-2xl flex items-center justify-between shadow-sm hover:shadow transition-all">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-teal-800 dark:text-teal-300" x-text="t('net_balance') || 'Net Balance'">Net Balance</span>
                <div class="mt-1 text-2xl font-black text-teal-800 dark:text-teal-200">₹<span x-text="calculateCashBookTotals().netBalance.toFixed(2)"></span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
            </div>
        </div>
    </div>

    {{-- TOOLBAR & ACTIONS BAR --}}
    <div class="bg-white dark:bg-gray-800 p-3.5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3" x-data="{ filterType: '', filterMethod: '' }">
        {{-- Left: Tab-specific Controls --}}
        <div class="flex-1">
            {{-- When on Ledger Tab --}}
            <template x-if="cashbookTab === 'ledger'">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="w-36 sm:w-40">
                        <select x-model="filterType" class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-xl text-xs dark:bg-gray-700 dark:text-white font-medium focus:ring-1 focus:ring-primary">
                            <option value="" x-text="t('all_types') || 'All Types'">All Types</option>
                            <option value="cash_in" x-text="t('cash_in_plus') || 'Cash In (+)'">Cash In (+)</option>
                            <option value="cash_out" x-text="t('cash_out_minus') || 'Cash Out (-)'">Cash Out (-)</option>
                        </select>
                    </div>
                    <div class="w-36 sm:w-40">
                        <select x-model="filterMethod" class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-xl text-xs dark:bg-gray-700 dark:text-white font-medium focus:ring-1 focus:ring-primary">
                            <option value="" x-text="t('all_methods') || 'All Methods'">All Methods</option>
                            <option value="cash" x-text="t('cash') || 'Cash'">Cash</option>
                            <option value="upi" x-text="t('upi') || 'UPI'">UPI</option>
                            <option value="bank" x-text="t('bank') || 'Bank Transfer'">Bank Transfer</option>
                        </select>
                    </div>
                    <button @click="loadCashBook(filterType, filterMethod)" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition-all shadow-sm cursor-pointer" x-text="t('apply') || 'Apply'">Apply</button>
                    <button @click="filterType = ''; filterMethod = ''; loadCashBook('', '')" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition-all cursor-pointer" x-text="t('reset') || 'Reset'">Reset</button>
                </div>
            </template>

            {{-- When on Closures Tab --}}
            <template x-if="cashbookTab === 'closures'">
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/30 font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-text="t('register_closure_notice') || 'Daily End-of-Day register drawer physical count & reconciliation history.'">Daily End-of-Day register drawer physical count & reconciliation history.</span>
                    </span>
                </div>
            </template>
        </div>

        {{-- Right: Primary Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2 shrink-0 justify-end">
            <button @click="showEntryModal = true; entryForm = { type: 'cash_in', amount: '', payment_method: 'cash', description: '' }"
                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span x-text="t('cash_in_plus') || 'Cash In (+)'">Cash In (+)</span>
            </button>
            <button @click="showEntryModal = true; entryForm = { type: 'cash_out', amount: '', payment_method: 'cash', description: '' }"
                class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
                <span x-text="t('cash_out_minus') || 'Cash Out (-)'">Cash Out (-)</span>
            </button>
            <button @click="openRegisterClosureModal()"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-text="t('close_register') || 'Close Register'">Close Register</span>
            </button>
        </div>
    </div>

    {{-- TABS: LEDGER VS REGISTER CLOSURES --}}
    <div class="flex items-center gap-1 border-b border-slate-200 dark:border-gray-700 px-1 pt-1">
        <button @click="cashbookTab = 'ledger'"
            :class="cashbookTab === 'ledger'
                ? 'border-b-2 border-primary text-primary font-bold bg-primary/5 dark:bg-primary/10'
                : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-medium hover:bg-slate-50 dark:hover:bg-gray-700/30'"
            class="px-4 py-2.5 text-xs sm:text-sm rounded-t-xl flex items-center gap-2 transition-all cursor-pointer">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <span x-text="t('ledger') || 'Transactions Ledger'">Transactions Ledger</span>
        </button>
        <button @click="cashbookTab = 'closures'; loadRegisterClosures()"
            :class="cashbookTab === 'closures'
                ? 'border-b-2 border-primary text-primary font-bold bg-primary/5 dark:bg-primary/10'
                : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-medium hover:bg-slate-50 dark:hover:bg-gray-700/30'"
            class="px-4 py-2.5 text-xs sm:text-sm rounded-t-xl flex items-center gap-2 transition-all cursor-pointer">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span x-text="t('register_closures_history') || 'Register Closures History'">Register Closures History</span>
            <span x-show="registerClosures.length > 0"
                class="px-2 py-0.5 text-xs font-bold rounded-full bg-primary/15 text-primary dark:bg-primary/25"
                x-text="registerClosures.length"></span>
        </button>
    </div>

    {{-- CENTRAL TRANSACTIONS LEDGER TAB --}}
    <div x-show="cashbookTab === 'ledger'" class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto">
        <table class="w-full divide-y divide-slate-200 dark:divide-gray-700 min-w-[750px]">
            <thead class="bg-slate-50/75 dark:bg-gray-800/80">
                <tr>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('type') || 'Type'">Type</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('payment_type') || 'Payment Method'">Payment Method</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('description') || 'Description'">Description</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('amount') || 'Amount'">Amount</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('date') || 'Date'">Date</th>
                    <th class="px-4 py-3 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('actions') || 'Actions'">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-700/60">
                <template x-if="cashbookLoading">
                    <tr>
                        <td colspan="6" class="text-center py-10">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                            <p class="text-xs text-slate-400 mt-2 font-medium" x-text="t('loading_ledger') || 'Loading ledger entries...'">Loading ledger entries...</p>
                        </td>
                    </tr>
                </template>
                <template x-for="entry in (cashbookLoading ? [] : cashbook.slice((cashbookPage - 1) * cashbookPerPage, cashbookPage * cashbookPerPage))" :key="entry.id">
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-gray-700/40 transition-colors">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span :class="entry.type === 'cash_in'
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                                : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'"
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border">
                                <span x-text="entry.type === 'cash_in' ? '▲ ' : '▼ '"></span>
                                <span x-text="entry.type === 'cash_in' ? (t('cash_in_plus') || 'Cash In (+)') : (t('cash_out_minus') || 'Cash Out (-)')"></span>
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs font-semibold uppercase text-slate-500 dark:text-slate-400" x-text="t(entry.payment_method ? entry.payment_method.toLowerCase() : '') || entry.payment_method"></td>
                        <td class="px-4 py-3.5 text-xs font-medium text-slate-700 dark:text-slate-200 max-w-xs truncate" :title="entry.description" x-text="entry.description"></td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-sm font-black" :class="entry.type === 'cash_in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                            <span x-text="(entry.type === 'cash_in' ? '+' : '-') + '₹' + parseFloat(entry.amount || 0).toFixed(2)"></span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs font-medium text-slate-500 dark:text-slate-400" x-text="formatDateTime(entry.transaction_date)"></td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-right text-xs">
                            <template x-if="entry.reference_type === null">
                                <button @click="deleteCashBookEntry(entry.id)" class="px-2.5 py-1 text-rose-600 hover:text-white hover:bg-rose-600 border border-rose-200 dark:border-rose-800 rounded-lg font-semibold transition-all cursor-pointer" x-text="t('delete') || 'Delete'">Delete</button>
                            </template>
                            <template x-if="entry.reference_type !== null">
                                <span class="inline-flex items-center px-2.5 py-0.5 text-[11px] rounded-full font-bold uppercase tracking-wider border"
                                    :class="{
                                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200/60': entry.reference_type === 'sale',
                                        'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-200/60': entry.reference_type === 'purchase',
                                        'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200/60': entry.reference_type === 'sale_cancel' || entry.reference_type === 'purchase_cancel',
                                        'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border-purple-200/60': entry.reference_type === 'expense',
                                        'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 border-teal-200/60': entry.reference_type === 'customer_payment',
                                        'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200/60': entry.reference_type === 'supplier_payment',
                                        'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border-indigo-200/60': entry.reference_type === 'contra',
                                        'bg-slate-100 text-slate-600 dark:bg-gray-700 dark:text-slate-300 border-slate-200/60': !['sale','purchase','sale_cancel','purchase_cancel','expense','customer_payment','supplier_payment','contra'].includes(entry.reference_type)
                                    }"
                                    x-text="t(entry.reference_type ? entry.reference_type.toLowerCase() : '') || (entry.reference_type ? entry.reference_type.replace(/_/g, ' ').toUpperCase() : '')">
                                </span>
                            </template>
                        </td>
                    </tr>
                </template>
                <template x-if="!cashbookLoading && cashbook.length === 0">
                    <tr>
                        <td colspan="6" class="text-center text-slate-400 py-10 text-sm font-medium" x-text="t('no_cashbook_found') || 'No cashbook transactions found.'">No cashbook transactions found.</td>
                    </tr>
                </template>
            </tbody>
        </table>
        <x-pagination currentPage="cashbookPage" totalItems="cashbook.length" perPage="cashbookPerPage" loading="cashbookLoading" />
    </div>

    {{-- REGISTER CLOSURES HISTORY TAB --}}
    <div x-show="cashbookTab === 'closures'" class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto">
        <table class="w-full divide-y divide-slate-200 dark:divide-gray-700 min-w-[950px]">
            <thead class="bg-slate-50/75 dark:bg-gray-800/80">
                <tr>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('date') || 'Date'">Date</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('opening_cash') || 'Opening Cash'">Opening Cash</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('total_cash_in') || 'Cash In'">Cash In</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('total_cash_out') || 'Cash Out'">Cash Out</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('expected_cash') || 'Expected Cash'">Expected Cash</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('physical_cash_count') || 'Physical Count'">Physical Count</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap" x-text="t('difference') || 'Difference / Status'">Difference / Status</th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Closed By & Notes</th>
                    <th class="px-4 py-3 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Breakdown</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-700/60">
                <template x-if="registerClosuresLoading">
                    <tr>
                        <td colspan="9" class="text-center py-10">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                            <p class="text-xs text-slate-400 mt-2 font-medium">Loading register closures...</p>
                        </td>
                    </tr>
                </template>
                <template x-for="closure in (registerClosuresLoading ? [] : registerClosures.slice((registerClosuresPage - 1) * registerClosuresPerPage, registerClosuresPage * registerClosuresPerPage))" :key="closure.id">
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-gray-700/40 transition-colors">
                        {{-- Date with weekday --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-gray-700/60 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-slate-800 dark:text-white block whitespace-nowrap" x-text="formatDate(closure.closing_date)"></span>
                                    <span class="text-[11px] text-slate-400 font-medium block whitespace-nowrap" x-text="new Date(closure.closing_date).toLocaleDateString(undefined, { weekday: 'short' })"></span>
                                </div>
                            </div>
                        </td>

                        {{-- Opening Cash --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-sm font-semibold text-slate-600 dark:text-slate-300">
                            ₹<span x-text="parseFloat(closure.opening_balance || 0).toFixed(2)"></span>
                        </td>

                        {{-- Cash In --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-lg border border-emerald-200/60 dark:border-emerald-800/40">
                                +₹<span x-text="parseFloat(closure.cash_in || 0).toFixed(2)"></span>
                            </span>
                        </td>

                        {{-- Cash Out --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-0.5 text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded-lg border border-rose-200/60 dark:border-rose-800/40">
                                -₹<span x-text="parseFloat(closure.cash_out || 0).toFixed(2)"></span>
                            </span>
                        </td>

                        {{-- Expected Cash --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-sm font-extrabold text-slate-800 dark:text-white">
                            ₹<span x-text="parseFloat(closure.expected_cash || 0).toFixed(2)"></span>
                        </td>

                        {{-- Physical Count --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center text-xs font-black text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/40 px-2.5 py-1 rounded-lg border border-indigo-200/60 dark:border-indigo-800/40">
                                ₹<span x-text="parseFloat(closure.actual_cash || 0).toFixed(2)"></span>
                            </span>
                        </td>

                        {{-- Difference / Reconciliation Status --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            {{-- Balanced --}}
                            <template x-if="parseFloat(closure.difference || 0) === 0">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    <span x-text="t('balanced') || 'Balanced'">Balanced (₹0.00)</span>
                                </span>
                            </template>

                            {{-- Surplus --}}
                            <template x-if="parseFloat(closure.difference || 0) > 0">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                                    <span>Surplus (+₹<span x-text="parseFloat(closure.difference).toFixed(2)"></span>)</span>
                                </span>
                            </template>

                            {{-- Shortage --}}
                            <template x-if="parseFloat(closure.difference || 0) < 0">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                    <span>Shortage (-₹<span x-text="Math.abs(parseFloat(closure.difference)).toFixed(2)"></span>)</span>
                                </span>
                            </template>
                        </td>

                        {{-- Closed By & Notes --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm"
                                    x-text="((closure.closed_by_user && closure.closed_by_user.name) ? closure.closed_by_user.name : (closure.closed_by ? closure.closed_by.first_name : 'Owner')).charAt(0).toUpperCase()">
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-white"
                                        x-text="(closure.closed_by_user && closure.closed_by_user.name) ? closure.closed_by_user.name : (closure.closed_by ? (((closure.closed_by.first_name || '') + ' ' + (closure.closed_by.last_name || '')).trim()) : 'Owner')">
                                    </div>
                                    <div class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-1 mt-0.5"
                                        :class="closure.note ? 'italic text-slate-600 dark:text-slate-300' : 'text-slate-400'">
                                        <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                        <span class="max-w-[160px] truncate" :title="closure.note" x-text="closure.note || 'No notes'"></span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Breakdown Action --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-right">
                            <button @click="selectedClosure = closure; showDenominationModal = true"
                                class="p-1.5 text-indigo-600 hover:text-white hover:bg-indigo-600 border border-indigo-200 dark:border-indigo-800 rounded-lg transition-all cursor-pointer"
                                title="View Denomination Details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                        </td>
                    </tr>
                </template>
                <template x-if="!registerClosuresLoading && registerClosures.length === 0">
                    <tr>
                        <td colspan="9" class="text-center text-slate-400 py-12 text-sm font-medium">
                            <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p>No daily register closures recorded yet.</p>
                            <p class="text-xs text-slate-400 mt-1">Click <span class="font-bold text-indigo-600">"Close Register"</span> above to count and record today's cash drawer closing.</p>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <x-pagination currentPage="registerClosuresPage" totalItems="registerClosures.length" perPage="registerClosuresPerPage" loading="registerClosuresLoading" />
    </div>

    {{-- ADD CASHBOOK ENTRY MODAL --}}
    <template x-teleport="body">
        <div x-show="showEntryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="showEntryModal = false">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 dark:text-white" x-text="entryForm.type === 'cash_in' ? (t('record_cash_in') || 'Record Cash In') : (t('record_cash_out') || 'Record Cash Out')"></h3>
                    <button @click="showEntryModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <form @submit.prevent="submitCashBookEntry(); showEntryModal = false" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('amount_rs') || 'Amount (₹)'">Amount (₹)</label>
                        <input type="number" step="0.01" required placeholder="0.00" x-model.number="entryForm.amount" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('payment_type') || 'Payment Method'">Payment Method</label>
                        <select x-model="entryForm.payment_method" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white font-medium">
                            <option value="cash" x-text="t('cash') || 'Cash'">Cash</option>
                            <option value="upi" x-text="(t('upi') || 'UPI') + ' / Digital Wallet'">UPI / Digital Wallet</option>
                            <option value="bank" x-text="t('bank') || 'Bank Transfer'">Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('description_remarks') || 'Description / Remarks'">Description / Remarks</label>
                        <input type="text" required :placeholder="t('reason_for_transaction') || 'Reason for transaction...'" x-model="entryForm.description" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    </div>
                    <button type="submit" class="w-full py-2.5 text-white text-sm font-semibold rounded-xl shadow-md transition-all cursor-pointer" :class="entryForm.type === 'cash_in' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'" x-text="t('save_entry') || 'Save Entry'">Save Entry</button>
                </form>
            </div>
        </div>
    </template>

    {{-- DAILY CASH REGISTER CLOSING & PHYSICAL CASH COUNT MODAL --}}
    <template x-teleport="body">
        <div x-show="showRegisterClosureModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-hidden">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden border border-slate-100 dark:border-gray-700" @click.outside="showRegisterClosureModal = false">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-700/30 shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white text-base" x-text="t('daily_register_closing') || 'Daily Cash Register Closing'">Daily Cash Register Closing</h3>
                            <p class="text-xs text-slate-400" x-text="'Date: ' + formatDate(registerStatus.closing_date || new Date().toISOString().split('T')[0])"></p>
                        </div>
                    </div>
                    <button @click="showRegisterClosureModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                    {{-- Status Banner if already closed --}}
                    <template x-if="registerStatus.is_closed_today">
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span x-text="t('today_register_closed') || 'Today\'s register has already been closed. Submitting again will update the closing record.'">Today's register has already been closed. Submitting again will update the closing record.</span>
                        </div>
                    </template>

                    {{-- Summary: Drawer Flow --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                        <div class="p-2.5 bg-slate-50 dark:bg-gray-700/40 rounded-xl border border-slate-100 dark:border-gray-700">
                            <span class="text-[10px] font-semibold uppercase text-slate-400 block" x-text="t('opening_cash') || 'Opening'">Opening</span>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">₹<span x-text="parseFloat(registerStatus.opening_balance || 0).toFixed(2)"></span></span>
                        </div>
                        <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 dark:border-emerald-900/30">
                            <span class="text-[10px] font-semibold uppercase text-emerald-600 block" x-text="t('total_cash_in') || 'Cash In'">Cash In</span>
                            <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300">+₹<span x-text="parseFloat(registerStatus.cash_in || 0).toFixed(2)"></span></span>
                        </div>
                        <div class="p-2.5 bg-rose-50 dark:bg-rose-950/20 rounded-xl border border-rose-100 dark:border-rose-900/30">
                            <span class="text-[10px] font-semibold uppercase text-rose-600 block" x-text="t('total_cash_out') || 'Cash Out'">Cash Out</span>
                            <span class="text-sm font-bold text-rose-700 dark:text-rose-300">-₹<span x-text="parseFloat(registerStatus.cash_out || 0).toFixed(2)"></span></span>
                        </div>
                        <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl border border-indigo-200 dark:border-indigo-800">
                            <span class="text-[10px] font-bold uppercase text-indigo-700 dark:text-indigo-300 block" x-text="t('expected_cash') || 'Expected Cash'">Expected Cash</span>
                            <span class="text-sm font-extrabold text-indigo-900 dark:text-indigo-200">₹<span x-text="parseFloat(registerStatus.expected_cash || 0).toFixed(2)"></span></span>
                        </div>
                    </div>

                    {{-- Physical Cash Denominations --}}
                    <div class="bg-slate-50 dark:bg-gray-700/30 rounded-2xl p-4 border border-slate-200 dark:border-gray-700 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span x-text="t('denominations') || 'Cash Denominations Count'">Cash Denominations Count</span>
                            </h4>
                            <span class="text-[11px] text-slate-400 font-medium">Count physical notes in cash drawer</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            {{-- ₹500 --}}
                            <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-2 rounded-xl border border-slate-200 dark:border-gray-700">
                                <span class="font-bold text-slate-700 dark:text-slate-300 w-12">₹500 ×</span>
                                <input type="number" min="0" placeholder="0" x-model.number="registerClosureForm.d500" @input="calculateDenominationsTotal()" class="w-16 px-2 py-1 text-center border border-slate-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white font-semibold">
                                <span class="text-right font-bold text-slate-600 dark:text-slate-300 w-16">₹<span x-text="((registerClosureForm.d500 || 0) * 500).toFixed(0)"></span></span>
                            </div>
                            {{-- ₹200 --}}
                            <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-2 rounded-xl border border-slate-200 dark:border-gray-700">
                                <span class="font-bold text-slate-700 dark:text-slate-300 w-12">₹200 ×</span>
                                <input type="number" min="0" placeholder="0" x-model.number="registerClosureForm.d200" @input="calculateDenominationsTotal()" class="w-16 px-2 py-1 text-center border border-slate-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white font-semibold">
                                <span class="text-right font-bold text-slate-600 dark:text-slate-300 w-16">₹<span x-text="((registerClosureForm.d200 || 0) * 200).toFixed(0)"></span></span>
                            </div>
                            {{-- ₹100 --}}
                            <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-2 rounded-xl border border-slate-200 dark:border-gray-700">
                                <span class="font-bold text-slate-700 dark:text-slate-300 w-12">₹100 ×</span>
                                <input type="number" min="0" placeholder="0" x-model.number="registerClosureForm.d100" @input="calculateDenominationsTotal()" class="w-16 px-2 py-1 text-center border border-slate-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white font-semibold">
                                <span class="text-right font-bold text-slate-600 dark:text-slate-300 w-16">₹<span x-text="((registerClosureForm.d100 || 0) * 100).toFixed(0)"></span></span>
                            </div>
                            {{-- ₹50 --}}
                            <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-2 rounded-xl border border-slate-200 dark:border-gray-700">
                                <span class="font-bold text-slate-700 dark:text-slate-300 w-12">₹50 ×</span>
                                <input type="number" min="0" placeholder="0" x-model.number="registerClosureForm.d50" @input="calculateDenominationsTotal()" class="w-16 px-2 py-1 text-center border border-slate-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white font-semibold">
                                <span class="text-right font-bold text-slate-600 dark:text-slate-300 w-16">₹<span x-text="((registerClosureForm.d50 || 0) * 50).toFixed(0)"></span></span>
                            </div>
                            {{-- ₹20 --}}
                            <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-2 rounded-xl border border-slate-200 dark:border-gray-700">
                                <span class="font-bold text-slate-700 dark:text-slate-300 w-12">₹20 ×</span>
                                <input type="number" min="0" placeholder="0" x-model.number="registerClosureForm.d20" @input="calculateDenominationsTotal()" class="w-16 px-2 py-1 text-center border border-slate-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white font-semibold">
                                <span class="text-right font-bold text-slate-600 dark:text-slate-300 w-16">₹<span x-text="((registerClosureForm.d20 || 0) * 20).toFixed(0)"></span></span>
                            </div>
                            {{-- ₹10 --}}
                            <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-2 rounded-xl border border-slate-200 dark:border-gray-700">
                                <span class="font-bold text-slate-700 dark:text-slate-300 w-12">₹10 ×</span>
                                <input type="number" min="0" placeholder="0" x-model.number="registerClosureForm.d10" @input="calculateDenominationsTotal()" class="w-16 px-2 py-1 text-center border border-slate-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white font-semibold">
                                <span class="text-right font-bold text-slate-600 dark:text-slate-300 w-16">₹<span x-text="((registerClosureForm.d10 || 0) * 10).toFixed(0)"></span></span>
                            </div>
                        </div>

                        {{-- Coins / Change --}}
                        <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-2.5 rounded-xl border border-slate-200 dark:border-gray-700 text-xs">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Coins & Other Change (₹)</span>
                            <input type="number" step="0.01" min="0" placeholder="0.00" x-model.number="registerClosureForm.coins" @input="calculateDenominationsTotal()" class="w-28 px-2 py-1 text-right border border-slate-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white font-bold text-indigo-600">
                        </div>
                    </div>

                    {{-- Reconciliation Outcome --}}
                    <div class="p-4 rounded-2xl border transition-all"
                         :class="(registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)) === 0 
                                  ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800' 
                                  : ((registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)) > 0 
                                      ? 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800' 
                                      : 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800')">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block" x-text="t('physical_cash_count') || 'Total Physical Cash Counted'">Total Physical Cash Counted</span>
                                <span class="text-2xl font-black text-slate-800 dark:text-white">₹<span x-text="parseFloat(registerClosureForm.actual_cash || 0).toFixed(2)"></span></span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block" x-text="t('difference') || 'Difference (Actual - Expected)'">Difference (Actual - Expected)</span>
                                <div class="text-lg font-black"
                                     :class="(registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)) === 0 
                                              ? 'text-emerald-600 dark:text-emerald-400' 
                                              : ((registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)) > 0 
                                                  ? 'text-blue-600 dark:text-blue-400' 
                                                  : 'text-rose-600 dark:text-rose-400')">
                                    <span x-text="((registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)) > 0 ? '+' : '') + '₹' + (registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)).toFixed(2)"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2 text-xs font-bold flex items-center gap-1.5"
                             :class="(registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)) === 0 
                                      ? 'text-emerald-700 dark:text-emerald-300' 
                                      : ((registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)) > 0 
                                          ? 'text-blue-700 dark:text-blue-300' 
                                          : 'text-rose-700 dark:text-rose-300')">
                            <template x-if="(registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)) === 0">
                                <span>✓ Register is perfectly balanced! Physical cash matches expected cash.</span>
                            </template>
                            <template x-if="(registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)) > 0">
                                <span>▲ Surplus cash (+₹<span x-text="(registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)).toFixed(2)"></span>) detected in drawer.</span>
                            </template>
                            <template x-if="(registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)) < 0">
                                <span>▼ Cash shortage (-₹<span x-text="Math.abs(registerClosureForm.actual_cash - (registerStatus.expected_cash || 0)).toFixed(2)"></span>) detected in drawer.</span>
                            </template>
                        </div>
                    </div>

                    {{-- Optional Note --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1" x-text="t('closing_note') || 'Closing Note / Remarks'">Closing Note / Remarks</label>
                        <input type="text" placeholder="Remarks regarding difference, handover note, etc." x-model="registerClosureForm.note" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="p-6 pt-3 border-t border-slate-200 dark:border-gray-700 flex gap-3 shrink-0">
                    <button type="button" @click="showRegisterClosureModal = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-all cursor-pointer" x-text="t('cancel') || 'Cancel'">Cancel</button>
                    <button type="button" @click="submitRegisterClosure()" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span x-text="t('close_register') || 'Confirm & Close Register'">Confirm & Close Register</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- VIEW DENOMINATIONS BREAKDOWN MODAL --}}
    <template x-teleport="body">
        <div x-show="showDenominationModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-hidden">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden border border-slate-100 dark:border-gray-700" @click.outside="showDenominationModal = false">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-700/30 shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white text-base">Closure Denomination Count</h3>
                            <p class="text-xs text-slate-400" x-text="selectedClosure ? formatDate(selectedClosure.closing_date) : ''"></p>
                        </div>
                    </div>
                    <button @click="showDenominationModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 overflow-y-auto flex-1" x-if="selectedClosure">
                    {{-- Summary pills --}}
                    <div class="grid grid-cols-2 gap-2 text-center text-xs">
                        <div class="p-2.5 bg-slate-50 dark:bg-gray-700/40 rounded-xl border border-slate-100 dark:border-gray-700">
                            <span class="text-[10px] font-semibold text-slate-400 block uppercase">Expected</span>
                            <span class="font-bold text-slate-800 dark:text-white text-sm">₹<span x-text="parseFloat(selectedClosure.expected_cash || 0).toFixed(2)"></span></span>
                        </div>
                        <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/40 rounded-xl border border-indigo-100 dark:border-indigo-900/30">
                            <span class="text-[10px] font-semibold text-indigo-500 block uppercase">Actual Counted</span>
                            <span class="font-black text-indigo-700 dark:text-indigo-300 text-sm">₹<span x-text="parseFloat(selectedClosure.actual_cash || 0).toFixed(2)"></span></span>
                        </div>
                    </div>

                    {{-- Denominations Table --}}
                    <div class="bg-slate-50 dark:bg-gray-700/30 rounded-2xl p-3.5 border border-slate-200 dark:border-gray-700 space-y-2">
                        <h4 class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Physical Notes & Coins</h4>
                        <div class="divide-y divide-slate-100 dark:divide-gray-700 text-xs">
                            <div class="flex justify-between py-1.5">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">₹500 Notes</span>
                                <span class="text-slate-500 font-medium" x-text="(selectedClosure.denominations && selectedClosure.denominations['500']) ? (selectedClosure.denominations['500'] + ' × ₹500 = ₹' + (selectedClosure.denominations['500'] * 500)) : '0 pcs (₹0)'"></span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">₹200 Notes</span>
                                <span class="text-slate-500 font-medium" x-text="(selectedClosure.denominations && selectedClosure.denominations['200']) ? (selectedClosure.denominations['200'] + ' × ₹200 = ₹' + (selectedClosure.denominations['200'] * 200)) : '0 pcs (₹0)'"></span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">₹100 Notes</span>
                                <span class="text-slate-500 font-medium" x-text="(selectedClosure.denominations && selectedClosure.denominations['100']) ? (selectedClosure.denominations['100'] + ' × ₹100 = ₹' + (selectedClosure.denominations['100'] * 100)) : '0 pcs (₹0)'"></span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">₹50 Notes</span>
                                <span class="text-slate-500 font-medium" x-text="(selectedClosure.denominations && selectedClosure.denominations['50']) ? (selectedClosure.denominations['50'] + ' × ₹50 = ₹' + (selectedClosure.denominations['50'] * 50)) : '0 pcs (₹0)'"></span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">₹20 Notes</span>
                                <span class="text-slate-500 font-medium" x-text="(selectedClosure.denominations && selectedClosure.denominations['20']) ? (selectedClosure.denominations['20'] + ' × ₹20 = ₹' + (selectedClosure.denominations['20'] * 20)) : '0 pcs (₹0)'"></span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">₹10 Notes</span>
                                <span class="text-slate-500 font-medium" x-text="(selectedClosure.denominations && selectedClosure.denominations['10']) ? (selectedClosure.denominations['10'] + ' × ₹10 = ₹' + (selectedClosure.denominations['10'] * 10)) : '0 pcs (₹0)'"></span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">Coins & Change</span>
                                <span class="text-slate-500 font-medium">₹<span x-text="parseFloat((selectedClosure.denominations && selectedClosure.denominations['coins']) || 0).toFixed(2)"></span></span>
                            </div>
                        </div>
                    </div>

                    {{-- Remarks if any --}}
                    <template x-if="selectedClosure.note">
                        <div class="p-3 bg-slate-50 dark:bg-gray-700/30 rounded-xl border border-slate-200 dark:border-gray-700 text-xs">
                            <span class="font-bold text-slate-500 block mb-0.5">Note:</span>
                            <p class="text-slate-700 dark:text-slate-300 italic" x-text="selectedClosure.note"></p>
                        </div>
                    </template>

                    <button type="button" @click="showDenominationModal = false" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition-all cursor-pointer">Close</button>
                </div>
            </div>
        </div>
    </template>
</div>

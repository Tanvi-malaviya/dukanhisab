{{-- CASHBOOK PANEL --}}
<div x-show="page === 'cashbook'" class="space-y-2" x-data="{ showEntryModal: false, entryForm: { type: 'cash_in', amount: '', payment_method: 'cash', description: '' } }">

    {{-- STATS CARDS FOR CASHBOOK --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
        <div class="p-4 border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl flex flex-col justify-between shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-300" x-text="t('total_cash_in')">Total Cash In</span>
            <div class="mt-2 text-2xl font-extrabold text-emerald-900 dark:text-emerald-200">₹<span x-text="calculateCashBookTotals().totalIn.toFixed(2)"></span></div>
        </div>
        <div class="p-4 border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/20 rounded-2xl flex flex-col justify-between shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-rose-800 dark:text-rose-300" x-text="t('total_cash_out')">Total Cash Out</span>
            <div class="mt-2 text-2xl font-extrabold text-rose-900 dark:text-rose-200">₹<span x-text="calculateCashBookTotals().totalOut.toFixed(2)"></span></div>
        </div>
        <div class="p-4 border border-primary/20 dark:border-primary/40 bg-teal-50 dark:bg-teal-950/20 rounded-2xl flex flex-col justify-between shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-primary dark:text-primary-light" x-text="t('net_balance')">Net Balance</span>
            <div class="mt-2 text-2xl font-extrabold text-slate-800 dark:text-white">₹<span x-text="calculateCashBookTotals().netBalance.toFixed(2)"></span></div>
        </div>
    </div>

    {{-- Filter section --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row md:justify-between md:items-end gap-3" x-data="{ filterType: '', filterMethod: '' }">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('transaction_type')">Transaction Type</label>
                <select x-model="filterType" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    <option value="" x-text="t('all_types')">All Types</option>
                    <option value="cash_in" x-text="t('cash_in_plus')">Cash In (+)</option>
                    <option value="cash_out" x-text="t('cash_out_minus')">Cash Out (-)</option>
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('payment_type')">Payment Method</label>
                <select x-model="filterMethod" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    <option value="" x-text="t('all_methods')">All Methods</option>
                    <option value="cash" x-text="t('cash')">Cash</option>
                    <option value="upi" x-text="t('upi')">UPI</option>
                    <option value="bank" x-text="t('bank')">Bank Transfer</option>
                </select>
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button @click="loadCashBook(filterType, filterMethod)" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-all flex-1 sm:flex-none" x-text="t('apply')">Apply</button>
                <button @click="filterType = ''; filterMethod = ''; loadCashBook('', '')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl transition-all" x-text="t('reset')">Reset</button>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 w-full md:w-auto justify-end">
            <button @click="showEntryModal = true; entryForm = { type: 'cash_in', amount: '', payment_method: 'cash', description: '' }" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span x-text="t('cash_in_plus')">Cash In (+)</span>
            </button>
            <button @click="showEntryModal = true; entryForm = { type: 'cash_out', amount: '', payment_method: 'cash', description: '' }" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                <span x-text="t('cash_out_minus')">Cash Out (-)</span>
            </button>
            <button @click="openRegisterClosureModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-text="t('close_register')">Close Register</span>
            </button>
        </div>
    </div>

    {{-- TABS: LEDGER VS REGISTER CLOSURES --}}
    <div class="flex border-b border-slate-200 dark:border-gray-700 gap-4 px-2 pt-1">
        <button @click="cashbookTab = 'ledger'" :class="cashbookTab === 'ledger' ? 'border-b-2 border-primary text-primary font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'" class="pb-2.5 text-sm flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <span x-text="t('ledger')">Transactions Ledger</span>
        </button>
        <button @click="cashbookTab = 'closures'; loadRegisterClosures()" :class="cashbookTab === 'closures' ? 'border-b-2 border-primary text-primary font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'" class="pb-2.5 text-sm flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span x-text="t('register_closures_history')">Register Closures History</span>
        </button>
    </div>

    {{-- Central Transactions Ledger --}}
    <div x-show="cashbookTab === 'ledger'" class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-hidden">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('type')">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('payment_type')">Payment Method</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('description')">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('amount')">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('date')">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-slate-400 uppercase" x-text="t('actions')">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                <template x-if="cashbookLoading">
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                            <p class="text-xs text-slate-400 mt-2 font-medium" x-text="t('loading_ledger')">Loading ledger entries...</p>
                        </td>
                    </tr>
                </template>
                <template x-for="entry in (cashbookLoading ? [] : cashbook.slice((cashbookPage - 1) * cashbookPerPage, cashbookPage * cashbookPerPage))" :key="entry.id">
                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 text-sm font-bold">
                            <span :class="entry.type === 'cash_in' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300'" class="px-2.5 py-0.5 rounded-full text-xs font-bold whitespace-nowrap" x-text="entry.type === 'cash_in' ? t('cash_in_plus') : t('cash_out_minus')"></span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold uppercase text-slate-500" x-text="t(entry.payment_method ? entry.payment_method.toLowerCase() : '') || entry.payment_method"></td>
                        <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300" x-text="entry.description"></td>
                        <td class="px-6 py-4 text-sm font-extrabold" :class="entry.type === 'cash_in' ? 'text-emerald-600' : 'text-rose-600'">
                            ₹<span x-text="parseFloat(entry.amount).toFixed(2)"></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400" x-text="formatDateTime(entry.transaction_date)"></td>
                        <td class="px-6 py-4 text-right text-sm">
                            <template x-if="entry.reference_type === null">
                                <button @click="deleteCashBookEntry(entry.id)" class="text-rose-600 hover:text-rose-700 font-semibold" x-text="t('delete')">Delete</button>
                            </template>
                            <template x-if="entry.reference_type !== null">
                                <span class="text-xs text-slate-400 font-semibold italic" x-text="entry.reference_type"></span>
                            </template>
                        </td>
                    </tr>
                </template>
                <template x-if="!cashbookLoading && cashbook.length === 0">
                    <tr>
                        <td colspan="6" class="text-center text-slate-400 py-8 text-sm" x-text="t('no_cashbook_found')">No cashbook transactions found.</td>
                    </tr>
                </template>
            </tbody>
        </table>
        <x-pagination currentPage="cashbookPage" totalItems="cashbook.length" perPage="cashbookPerPage" loading="cashbookLoading" />
    </div>

    {{-- REGISTER CLOSURES HISTORY TAB --}}
    <div x-show="cashbookTab === 'closures'" class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-hidden">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('date')">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('opening_cash')">Opening Cash</th>
                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('total_cash_in')">Cash In</th>
                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('total_cash_out')">Cash Out</th>
                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('expected_cash')">Expected Cash</th>
                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('physical_cash_count')">Counted Cash</th>
                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('difference')">Difference</th>
                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase">Closed By & Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                <template x-if="registerClosuresLoading">
                    <tr>
                        <td colspan="8" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                            <p class="text-xs text-slate-400 mt-2 font-medium">Loading register closures...</p>
                        </td>
                    </tr>
                </template>
                <template x-for="closure in (registerClosuresLoading ? [] : registerClosures.slice((registerClosuresPage - 1) * registerClosuresPerPage, registerClosuresPage * registerClosuresPerPage))" :key="closure.id">
                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                        <td class="px-5 py-4 text-sm font-bold text-slate-800 dark:text-white" x-text="closure.closing_date"></td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">₹<span x-text="parseFloat(closure.opening_balance).toFixed(2)"></span></td>
                        <td class="px-5 py-4 text-sm font-semibold text-emerald-600">+₹<span x-text="parseFloat(closure.cash_in).toFixed(2)"></span></td>
                        <td class="px-5 py-4 text-sm font-semibold text-rose-600">-₹<span x-text="parseFloat(closure.cash_out).toFixed(2)"></span></td>
                        <td class="px-5 py-4 text-sm font-extrabold text-slate-800 dark:text-white">₹<span x-text="parseFloat(closure.expected_cash).toFixed(2)"></span></td>
                        <td class="px-5 py-4 text-sm font-extrabold text-indigo-600 dark:text-indigo-400">₹<span x-text="parseFloat(closure.actual_cash).toFixed(2)"></span></td>
                        <td class="px-5 py-4 text-sm font-bold">
                            <template x-if="parseFloat(closure.difference) === 0">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300" x-text="t('balanced')">Balanced</span>
                            </template>
                            <template x-if="parseFloat(closure.difference) > 0">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300" x-text="t('surplus') + ' (+₹' + parseFloat(closure.difference).toFixed(2) + ')'">Surplus</span>
                            </template>
                            <template x-if="parseFloat(closure.difference) < 0">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300" x-text="t('shortage') + ' (-₹' + Math.abs(parseFloat(closure.difference)).toFixed(2) + ')'">Shortage</span>
                            </template>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-500 dark:text-slate-400">
                            <div class="font-medium text-slate-700 dark:text-slate-300" x-text="closure.closed_by ? (closure.closed_by.first_name + ' ' + (closure.closed_by.last_name || '')) : 'Owner'"></div>
                            <div class="italic text-slate-400 mt-0.5" x-text="closure.note || 'No notes'"></div>
                        </td>
                    </tr>
                </template>
                <template x-if="!registerClosuresLoading && registerClosures.length === 0">
                    <tr>
                        <td colspan="8" class="text-center text-slate-400 py-8 text-sm">No daily register closures recorded yet. Click "Close Register" to record today's closing cash count.</td>
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
                    <h3 class="font-bold text-slate-800 dark:text-white" x-text="entryForm.type === 'cash_in' ? t('record_cash_in') : t('record_cash_out')"></h3>
                    <button @click="showEntryModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <form @submit.prevent="submitCashBookEntry(); showEntryModal = false" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('amount_rs')">Amount (₹)</label>
                        <input type="number" step="0.01" required placeholder="0.00" x-model.number="entryForm.amount" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('payment_type')">Payment Method</label>
                        <select x-model="entryForm.payment_method" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                            <option value="cash" x-text="t('cash')">Cash</option>
                            <option value="upi" x-text="t('upi') + ' / Digital Wallet'">UPI / Digital Wallet</option>
                            <option value="bank" x-text="t('bank')">Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('description_remarks')">Description / Remarks</label>
                        <input type="text" required :placeholder="t('reason_for_transaction') || 'Reason for transaction...'" x-model="entryForm.description" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    </div>
                    <button type="submit" class="w-full py-2.5 text-white text-sm font-semibold rounded-xl shadow-md transition-all" :class="entryForm.type === 'cash_in' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'" x-text="t('save_entry')">Save Entry</button>
                </form>
            </div>
        </div>
    </template>

    {{-- DAILY CASH REGISTER CLOSING & PHYSICAL CASH COUNT MODAL --}}
    <template x-teleport="body">
        <div x-show="showRegisterClosureModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 dark:border-gray-700 my-8" @click.outside="showRegisterClosureModal = false">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-700/30">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white text-base" x-text="t('daily_register_closing')">Daily Cash Register Closing</h3>
                            <p class="text-xs text-slate-400" x-text="'Date: ' + (registerStatus.closing_date || new Date().toISOString().split('T')[0])"></p>
                        </div>
                    </div>
                    <button @click="showRegisterClosureModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                    {{-- Status Banner if already closed --}}
                    <template x-if="registerStatus.is_closed_today">
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span x-text="t('today_register_closed')">Today's register has already been closed. Submitting again will update the closing record.</span>
                        </div>
                    </template>

                    {{-- Summary: Drawer Flow --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                        <div class="p-2.5 bg-slate-50 dark:bg-gray-700/40 rounded-xl border border-slate-100 dark:border-gray-700">
                            <span class="text-[10px] font-semibold uppercase text-slate-400 block" x-text="t('opening_cash')">Opening</span>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">₹<span x-text="parseFloat(registerStatus.opening_balance || 0).toFixed(2)"></span></span>
                        </div>
                        <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 dark:border-emerald-900/30">
                            <span class="text-[10px] font-semibold uppercase text-emerald-600 block" x-text="t('total_cash_in')">Cash In</span>
                            <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300">+₹<span x-text="parseFloat(registerStatus.cash_in || 0).toFixed(2)"></span></span>
                        </div>
                        <div class="p-2.5 bg-rose-50 dark:bg-rose-950/20 rounded-xl border border-rose-100 dark:border-rose-900/30">
                            <span class="text-[10px] font-semibold uppercase text-rose-600 block" x-text="t('total_cash_out')">Cash Out</span>
                            <span class="text-sm font-bold text-rose-700 dark:text-rose-300">-₹<span x-text="parseFloat(registerStatus.cash_out || 0).toFixed(2)"></span></span>
                        </div>
                        <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl border border-indigo-200 dark:border-indigo-800">
                            <span class="text-[10px] font-bold uppercase text-indigo-700 dark:text-indigo-300 block" x-text="t('expected_cash')">Expected Cash</span>
                            <span class="text-sm font-extrabold text-indigo-900 dark:text-indigo-200">₹<span x-text="parseFloat(registerStatus.expected_cash || 0).toFixed(2)"></span></span>
                        </div>
                    </div>

                    {{-- Physical Cash Denominations --}}
                    <div class="bg-slate-50 dark:bg-gray-700/30 rounded-2xl p-4 border border-slate-200 dark:border-gray-700 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span x-text="t('denominations')">Cash Denominations Count</span>
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
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block" x-text="t('physical_cash_count')">Total Physical Cash Counted</span>
                                <span class="text-2xl font-black text-slate-800 dark:text-white">₹<span x-text="parseFloat(registerClosureForm.actual_cash || 0).toFixed(2)"></span></span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block" x-text="t('difference')">Difference (Actual - Expected)</span>
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
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1" x-text="t('closing_note')">Closing Note / Remarks</label>
                        <input type="text" placeholder="Remarks regarding difference, handover note, etc." x-model="registerClosureForm.note" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2 flex gap-3">
                        <button type="button" @click="showRegisterClosureModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-all" x-text="t('cancel') || 'Cancel'">Cancel</button>
                        <button type="button" @click="submitRegisterClosure()" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('close_register')">Confirm & Close Register</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

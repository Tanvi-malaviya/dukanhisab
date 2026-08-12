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

        <div class="flex gap-2 w-full md:w-auto justify-end">
            <button @click="showEntryModal = true; entryForm = { type: 'cash_in', amount: '', payment_method: 'cash', description: '' }" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span x-text="t('cash_in_plus')">Cash In (+)</span>
            </button>
            <button @click="showEntryModal = true; entryForm = { type: 'cash_out', amount: '', payment_method: 'cash', description: '' }" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                <span x-text="t('cash_out_minus')">Cash Out (-)</span>
            </button>
        </div>
    </div>

    {{-- Central Transactions Ledger --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-hidden">
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
                        <td class="px-6 py-4 text-sm text-slate-400" x-text="new Date(entry.transaction_date).toLocaleString()"></td>
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
</div>

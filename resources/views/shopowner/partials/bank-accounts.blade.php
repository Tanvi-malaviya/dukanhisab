{{-- BANK ACCOUNTS PANEL --}}
<div x-show="page === 'bank-accounts'" class="space-y-4" x-data="{ 
    showTransferModal: false, 
    showReconciliationModal: false,
    transferForm: { type: 'deposit', amount: '', description: '' },
    
    // Filter cashbook entries for Bank and UPI payment methods
    getBankTransactions() {
        return this.cashbook.filter(e => e.payment_method === 'bank' || e.payment_method === 'upi');
    }
}">
    {{-- Bank Accounts list (Primary account card) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Card Loading Placeholder when empty --}}
        <template x-if="bankAccountsLoading && bankAccounts.length === 0">
            <div class="bg-slate-50 dark:bg-gray-800/40 relative overflow-hidden p-4 rounded-3xl border border-slate-200 dark:border-gray-700 flex flex-col justify-center items-center min-h-[160px] shadow-sm">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="text-xs text-slate-400 mt-2 font-medium">Loading bank account details...</p>
            </div>
        </template>

        {{-- Card Empty State when not loading and empty --}}
        <template x-if="!bankAccountsLoading && bankAccounts.length === 0">
            <div class="bg-slate-50 dark:bg-gray-800/40 relative overflow-hidden p-4 rounded-3xl border border-slate-200 dark:border-gray-700 flex flex-col justify-center items-center min-h-[160px] shadow-sm">
                <svg class="w-8 h-8 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <p class="text-xs text-slate-400 font-medium">No bank accounts linked.</p>
            </div>
        </template>

        <template x-for="acc in bankAccounts" :key="acc.id">
            <div class="bg-primary relative overflow-hidden p-4 text-white rounded-3xl shadow-lg border border-teal-700/30 flex flex-col justify-between min-h-[160px]"
                style="background: linear-gradient(135deg, #0F766E 0%, #14B8A6 100%) !important; color: #FFFFFF !important;">
                {{-- Card Loading Overlay --}}
                <div x-show="bankAccountsLoading" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm z-20 flex items-center justify-center rounded-3xl">
                    <svg class="animate-spin h-8 w-8 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                {{-- Decorative background shapes --}}
                <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-xl pointer-events-none">
                </div>
                <div class="absolute left-1/3 top-4 w-12 h-12 bg-white/5 rounded-full pointer-events-none"></div>

                <div class="flex justify-between items-start z-10">
                    <div>
                        <h4 class="text-base font-bold tracking-wide" style="color: #FFFFFF !important;"
                            x-text="acc.name"></h4>
                    </div>
                    <!-- <span
                        class="px-2.5 py-0.5 bg-white/20 dark:bg-black/20 rounded-full text-[10px] font-bold uppercase tracking-wider backdrop-blur-md"
                        style="color: #FFFFFF !important;">Active</span> -->
                </div>

                {{-- Card Chip & Wave graphic --}}
                <div class="flex items-center gap-3 my-2 opacity-80 z-10">
                    <div class="w-8 h-6 bg-amber-400/80 rounded border border-amber-300/40 relative">
                        <div class="absolute inset-1.5 border-r border-b border-amber-600/30"></div>
                    </div>
                    <svg class="w-5 h-5" style="color: rgba(255,255,255,0.8) !important;" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>

                <div class="z-10">
                    <p class="text-[10px] uppercase tracking-wider font-semibold"
                        style="color: rgba(255,255,255,0.85) !important;">Available Bank Balance</p>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <p class="text-2xl font-extrabold tracking-tight" style="color: #FFFFFF !important;">₹<span
                                x-text="parseFloat(acc.balance).toFixed(2)"></span></p>
                        <template x-if="parseFloat(acc.balance) < 0">
                            <span class="px-1.5 py-0.5 bg-rose-500/30 text-[10px] font-bold rounded border"
                                style="color: #FECACA !important; border-color: rgba(239, 68, 68, 0.4) !important;">Overdrawn</span>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        {{-- Account Actions Card --}}
        <div class="p-4 bg-slate-50 dark:bg-gray-800/40 rounded-3xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col justify-between min-h-[160px]">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-primary animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    Quick Actions
                </h4>
            </div>
            <div class="grid grid-cols-3 gap-2 flex-grow">
                <!-- Deposit Cash Button -->
                <button
                    @click="showTransferModal = true; transferForm = { type: 'deposit', amount: '', description: 'Bank Cash Deposit' }"
                    class="flex flex-col items-center justify-center p-2 bg-emerald-50 hover:bg-emerald-100/80 dark:bg-emerald-950/20 dark:hover:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-900/40 rounded-xl transition-all duration-200 hover:scale-[1.03] group shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center mb-1.5 group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 text-center leading-tight">Deposit Cash</span>
                </button>

                <!-- Withdraw Cash Button -->
                <button
                    @click="showTransferModal = true; transferForm = { type: 'withdraw', amount: '', description: 'Bank Cash Withdrawal' }"
                    class="flex flex-col items-center justify-center p-2 bg-rose-50 hover:bg-rose-100/80 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 border border-rose-100 dark:border-rose-900/40 rounded-xl transition-all duration-200 hover:scale-[1.03] group shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center mb-1.5 group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-rose-700 dark:text-rose-300 text-center leading-tight">Withdraw Cash</span>
                </button>

                <!-- Info Button -->
                <button
                    @click="showReconciliationModal = true"
                    class="flex flex-col items-center justify-center p-2 bg-blue-50 hover:bg-blue-100/80 dark:bg-blue-950/20 dark:hover:bg-blue-900/30 border border-blue-100 dark:border-blue-900/40 rounded-xl transition-all duration-200 hover:scale-[1.03] group shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center mb-1.5 group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-blue-700 dark:text-blue-300 text-center leading-tight">Reconciliation Info</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Recent Bank / UPI Transactions --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div
            class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50/50 dark:bg-gray-700/10">
            <h4 class="font-bold text-slate-800 dark:text-white text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                    </path>
                </svg>
                Recent Bank &amp; UPI Activity
            </h4>
            <span class="text-xs text-slate-400" x-text="getBankTransactions().length + ' Total records'"></span>
        </div>

        <div class="overflow-x-auto overflow-y-hidden">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-slate-50 dark:bg-gray-700/30">
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Description</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Method
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Type
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                    <template x-if="cashbookLoading">
                        <tr>
                            <td colspan="5" class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                                <p class="text-xs text-slate-400 mt-2 font-medium">Loading ledger entries...</p>
                            </td>
                        </tr>
                    </template>
                    <template x-for="tr in (cashbookLoading ? [] : getBankTransactions().slice((bankPage - 1) * bankPerPage, bankPage * bankPerPage))" :key="tr.id">
                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-3.5 text-xs text-slate-400"
                                x-text="new Date(tr.transaction_date || tr.created_at).toLocaleDateString() + ' ' + new Date(tr.transaction_date || tr.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})">
                            </td>
                            <td class="px-6 py-3.5 text-sm font-semibold text-slate-800 dark:text-white"
                                x-text="tr.description"></td>
                            <td class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wide"
                                x-text="tr.payment_method"></td>
                            <td class="px-6 py-3.5 text-xs">
                                <span
                                    :class="tr.type === 'cash_in' ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900' : 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900'"
                                    class="inline-flex items-center justify-center px-2 py-0.5 rounded-md font-bold"
                                    x-text="tr.type === 'cash_in' ? 'Deposit / In' : 'Withdrawal / Out'"></span>
                            </td>
                            <td class="px-6 py-3.5 text-sm font-bold text-right"
                                :class="tr.type === 'cash_in' ? 'text-emerald-600' : 'text-rose-600'"
                                x-text="(tr.type === 'cash_in' ? '+' : '-') + ' ₹' + parseFloat(tr.amount).toFixed(2)">
                            </td>
                        </tr>
                    </template>
                    <template x-if="!cashbookLoading && getBankTransactions().length === 0">
                        <tr>
                            <td colspan="5" class="text-center text-slate-400 py-10 text-sm">
                                No recent UPI or Bank transactions recorded.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <x-pagination currentPage="bankPage" totalItems="getBankTransactions().length" perPage="bankPerPage" loading="cashbookLoading" />
    </div>

    {{-- BANK TRANSFER MODAL --}}
    <div x-show="showTransferModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100 dark:border-gray-700">
            <div
                class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-700/20">
                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    <span
                        x-text="transferForm.type === 'deposit' ? 'Deposit Cash to Bank' : 'Withdraw Cash from Bank'"></span>
                </h3>
                <button @click="showTransferModal = false"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <form @submit.prevent="submitBankTransfer(); showTransferModal = false" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Transfer Amount
                        (₹)</label>
                    <input type="number" step="0.01" required placeholder="0.00" x-model.number="transferForm.amount"
                        class="block w-full px-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Transfer Remarks /
                        Description</label>
                    <input type="text" required placeholder="Remarks..." x-model="transferForm.description"
                        class="block w-full px-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                <button type="submit"
                    class="w-full py-3 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all">Submit
                    Transfer</button>
            </form>
        </div>
    </div>

    {{-- RECONCILIATION INFO MODAL --}}
    <div x-show="showReconciliationModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100 dark:border-gray-700 transition-all transform scale-100">
            <div
                class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-700/20">
                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Automatic Reconciliation</span>
                </h3>
                <button @click="showReconciliationModal = false"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-4 bg-blue-50/50 dark:bg-blue-950/10 rounded-2xl border border-blue-100/50 dark:border-blue-900/30">
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Bank balances are automatically updated and aggregated from <strong>Cash Book</strong> transactions marked with payment method <strong>'bank'</strong> or <strong>'upi'</strong> (such as Sales, Sales Returns, Purchases, or Expenses).
                    </p>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-gray-700">
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Use the <strong>Deposit</strong> and <strong>Withdraw</strong> buttons to shift funds between physical cash-in-hand and your bank account.
                    </p>
                </div>
                <button @click="showReconciliationModal = false"
                    class="w-full py-3 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
<div x-show="page === 'transactions'" class="space-y-2" x-data="{ filterType: '', searchQuery: '', startDate: '', endDate: '', transactionsPage: 1, transactionsPerPage: 10 }" x-effect="filterType || searchQuery || startDate || endDate ? transactionsPage = 1 : null">
  

    {{-- Filter section --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-wrap gap-3 items-end">
        <div class="w-full sm:w-auto flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('search_description_ref')">Search description/reference</label>
            <input type="text" :placeholder="(t('search') || 'Search') + '...'" x-model="searchQuery" 
                @input.debounce.300ms="loadCashBook('', '', searchQuery, startDate, endDate)" 
                class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('source_module')">Source Module</label>
            <select x-model="filterType" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                <option value="" x-text="t('all_transactions')">All Transactions</option>
                <option value="sale" x-text="t('sales')">Sales</option>
                <option value="purchase" x-text="t('purchases')">Purchases</option>
                <option value="manual" x-text="t('manual_entries')">Manual entries</option>
                <option value="expense" x-text="t('expenses')">Expenses</option>
            </select>
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('start_date')">Start Date</label>
            <input type="date" x-model="startDate" 
                onclick="this.showPicker()"
                @change="loadCashBook('', '', searchQuery, startDate, endDate)" 
                class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white cursor-pointer">
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('end_date')">End Date</label>
            <input type="date" x-model="endDate" 
                onclick="this.showPicker()"
                @change="loadCashBook('', '', searchQuery, startDate, endDate)" 
                class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white cursor-pointer">
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button @click="filterType = ''; searchQuery = ''; startDate = ''; endDate = ''; loadCashBook('', '', '', '', '');" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl transition-all" x-text="t('clear')">Clear</button>
        </div>
    </div>

    {{-- Consolidated Ledger Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-hidden">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('module')">Module</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('description')">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('method')">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('flow')">Flow</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('amount')">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('date')">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                <template x-if="cashbookLoading">
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                            <p class="text-xs text-slate-400 mt-2 font-medium" x-text="t('loading_transactions')">Loading transactions...</p>
                        </td>
                    </tr>
                </template>
                <template x-for="t_item in (cashbookLoading ? [] : getConsolidatedTransactions(filterType, searchQuery, startDate, endDate).slice((transactionsPage - 1) * transactionsPerPage, transactionsPage * transactionsPerPage))" :key="t_item.uid">
                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 text-sm font-bold uppercase">
                            <span :class="{
                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300': t_item.module === 'sale',
                                'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300': t_item.module === 'purchase',
                                'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300': t_item.module === 'manual',
                                'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300': t_item.module === 'expense'
                            }" class="px-2.5 py-0.5 rounded-full text-xs font-bold whitespace-nowrap" x-text="t(t_item.module === 'sale' ? 'sales' : (t_item.module === 'purchase' ? 'purchases' : (t_item.module === 'manual' ? 'manual_entries' : 'expenses'))) || t_item.module"></span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-800 dark:text-white" x-text="t_item.description"></td>
                        <td class="px-6 py-4 text-sm uppercase text-slate-500 font-semibold" x-text="t(t_item.method ? t_item.method.toLowerCase() : '') || t_item.method"></td>
                        <td class="px-6 py-4 text-sm font-bold">
                            <span :class="t_item.flow === 'IN' ? 'text-emerald-600' : 'text-rose-600'" x-text="t_item.flow"></span>
                        </td>
                        <td class="px-6 py-4 text-sm font-extrabold" :class="t_item.flow === 'IN' ? 'text-emerald-600' : 'text-rose-600'">
                            ₹<span x-text="parseFloat(t_item.amount).toFixed(2)"></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400" x-text="new Date(t_item.date).toLocaleString()"></td>
                    </tr>
                </template>
                <template x-if="!cashbookLoading && getConsolidatedTransactions(filterType, searchQuery, startDate, endDate).length === 0">
                    <tr>
                        <td colspan="6" class="text-center text-slate-400 py-8 text-sm" x-text="t('no_transactions_found')">No transaction records found.</td>
                    </tr>
                </template>
            </tbody>
        </table>
        <x-pagination currentPage="transactionsPage" totalItems="getConsolidatedTransactions(filterType, searchQuery, startDate, endDate).length" perPage="transactionsPerPage" loading="cashbookLoading" />
    </div>
</div>

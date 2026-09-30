{{-- EXPENSES PANEL --}}
<div x-show="page === 'expenses'" class="space-y-4">
    {{-- Top Section: Total Summary & Action Buttons --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400" x-text="selectedExpenseMonth ? formatExpenseMonthDisplay(selectedExpenseMonth) + ' Expenses' : t('total_expenses')">Total Expenses</span>
                <p class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1">₹<span x-text="parseFloat(expenses.reduce((sum, e) => sum + (parseFloat(e.amount) || 0), 0)).toFixed(2)"></span></p>
                <p class="text-xs text-slate-400 mt-1" x-text="`${expenses.length} ${t('transactions_count') || 'Entries'}`"></p>
            </div>
            <div class="p-3 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-2xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
        <div class="sm:col-span-2 flex flex-wrap justify-end items-center gap-2.5">
            <button @click="openManageExpenseCategoriesModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-xl transition-all flex items-center gap-2 cursor-pointer border border-slate-200 dark:border-gray-600">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                <span>Manage Categories</span>
            </button>
            <button @click="openNewExpenseModal()" class="px-5 py-2.5 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span x-text="t('add_expense')">Add Expense</span>
            </button>
        </div>
    </div>

    {{-- Month Filter Bar --}}
    <div class="bg-white dark:bg-gray-800 p-3 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-300">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Filter Month:</span>
            </div>
            <div class="relative">
                <input type="month" x-model="selectedExpenseMonth" onclick="this.showPicker()" @change="loadExpenses()"
                    class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 dark:bg-gray-700 dark:hover:bg-gray-600 border border-slate-200 dark:border-gray-600 rounded-xl text-xs font-semibold text-slate-700 dark:text-white cursor-pointer transition-all focus:ring-1 focus:ring-primary focus:border-primary">
            </div>
            <button type="button" @click="setExpenseCurrentMonth()"
                :class="selectedExpenseMonth === new Date().toISOString().slice(0, 7) ? 'bg-primary text-white shadow-xs' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-gray-600'"
                class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all cursor-pointer">
                This Month
            </button>
            <template x-if="selectedExpenseMonth">
                <button type="button" @click="clearExpenseMonthFilter()" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center gap-1">
                    <span>Clear Filter</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </template>
        </div>

        <div class="text-xs text-slate-400">
            <template x-if="selectedExpenseMonth">
                <span>Filtered by: <strong class="text-slate-800 dark:text-slate-200 font-sans" x-text="formatExpenseMonthDisplay(selectedExpenseMonth)"></strong></span>
            </template>
            <template x-if="!selectedExpenseMonth">
                <span>Period: <strong class="text-slate-600 dark:text-slate-300">All Time</strong></span>
            </template>
        </div>
    </div>

    {{-- Category Filter Chips --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <button @click="filterExpensesByCategory('')" 
            :class="!selectedExpenseCategoryFilter ? 'bg-primary text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700'"
            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer flex items-center gap-1.5">
            <span>All Expenses</span>
            <span class="text-[10px] opacity-75 font-mono" x-text="`(${expenses.length})`"></span>
        </button>
        <template x-for="cat in expenseCategories" :key="cat.id">
            <button @click="filterExpensesByCategory(cat.id)" 
                :class="selectedExpenseCategoryFilter == cat.id ? 'bg-primary text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700'"
                class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer flex items-center gap-1.5">
                <span x-text="cat.name"></span>
                <span class="text-[10px] opacity-75 font-mono" x-text="cat.cash_books_count !== undefined ? `(${cat.cash_books_count})` : ''"></span>
            </button>
        </template>
        <template x-if="expenseCategories.length === 0">
            <span class="text-xs text-slate-400 italic">No categories yet. Click "Manage Categories" to add.</span>
        </template>
    </div>

    {{-- Expenses Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-hidden">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('description')">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('amount')">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('payment_type')">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('date')">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-slate-400 uppercase" x-text="t('action') || 'Action'">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                <template x-if="expensesLoading">
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                            <p class="text-xs text-slate-400 mt-2 font-medium" x-text="t('loading')">Loading expenses...</p>
                        </td>
                    </tr>
                </template>
                <template x-for="exp in (expensesLoading ? [] : expenses.slice((expensesPage - 1) * expensesPerPage, expensesPage * expensesPerPage))" :key="exp.id">
                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-white" x-text="exp.description"></td>
                        <td class="px-6 py-4 text-sm">
                            <span :class="exp.expense_category ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-800/40' : 'bg-slate-100 text-slate-500 border border-slate-200 dark:bg-gray-700 dark:text-slate-400 dark:border-gray-600'" class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold inline-block" x-text="exp.expense_category ? exp.expense_category.name : 'General / Unassigned'"></span>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-rose-600 dark:text-rose-400">₹<span x-text="parseFloat(exp.amount).toFixed(2)"></span></td>
                        <td class="px-6 py-4 text-sm text-slate-500 uppercase" x-text="t(exp.payment_method ? exp.payment_method.toLowerCase() : 'cash') || exp.payment_method"></td>
                        <td class="px-6 py-4 text-sm text-slate-500" x-text="formatDate(exp.transaction_date)"></td>
                        <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" @click="openEditExpenseModal(exp)" title="Edit Expense" class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <button type="button" @click="deleteExpense(exp)" title="Delete Expense" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <template x-if="!expensesLoading && expenses.length === 0">
                    <tr>
                        <td colspan="6" class="text-center text-slate-400 py-8 text-sm" x-text="t('no_data_found')">No expenses found.</td>
                    </tr>
                </template>
            </tbody>
        </table>
        <x-pagination currentPage="expensesPage" totalItems="expenses.length" perPage="expensesPerPage" loading="expensesLoading" />
    </div>
</div>


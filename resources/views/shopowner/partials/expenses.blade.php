{{-- EXPENSES PANEL --}}
<div x-show="page === 'expenses'" class="space-y-2">
    <div class="flex justify-end items-center">
        
        <button @click="openNewExpenseModal()" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl transition-all">
            Add Expense
        </button>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-hidden">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                <template x-if="expensesLoading">
                    <tr>
                        <td colspan="4" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                            <p class="text-xs text-slate-400 mt-2 font-medium">Loading expenses...</p>
                        </td>
                    </tr>
                </template>
                <template x-for="exp in (expensesLoading ? [] : expenses.slice((expensesPage - 1) * expensesPerPage, expensesPage * expensesPerPage))" :key="exp.id">
                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-white" x-text="exp.description"></td>
                        <td class="px-6 py-4 text-sm font-bold text-rose-600">₹<span x-text="exp.amount"></span></td>
                        <td class="px-6 py-4 text-sm text-slate-500 uppercase" x-text="exp.payment_method"></td>
                        <td class="px-6 py-4 text-sm text-slate-500" x-text="new Date(exp.transaction_date).toLocaleDateString()"></td>
                    </tr>
                </template>
                <template x-if="!expensesLoading && expenses.length === 0">
                    <tr>
                        <td colspan="4" class="text-center text-slate-400 py-8 text-sm">No expenses found.</td>
                    </tr>
                </template>
            </tbody>
        </table>
        <x-pagination currentPage="expensesPage" totalItems="expenses.length" perPage="expensesPerPage" loading="expensesLoading" />
    </div>
</div>

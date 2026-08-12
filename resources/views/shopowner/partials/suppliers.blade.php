{{-- SUPPLIERS PANEL --}}
<div x-show="page === 'suppliers'" class="space-y-2">
    <div class="flex justify-end items-center">
       
        <button @click="openNewSupplierModal()" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl transition-all">
            <span x-text="t('add_supplier')">Add Supplier</span>
        </button>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-hidden">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('supplier_name')">Supplier Name</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('mobile')">Mobile</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('email')">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('due_balance')">Due Balance</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-slate-400 uppercase" x-text="t('actions')">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                <template x-if="suppliersLoading">
                    <tr>
                        <td colspan="5" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                            <p class="text-xs text-slate-400 mt-2 font-medium" x-text="t('loading')">Loading suppliers...</p>
                        </td>
                    </tr>
                </template>
                <template x-for="sup in (suppliersLoading ? [] : suppliers.slice((suppliersPage - 1) * suppliersPerPage, suppliersPage * suppliersPerPage))" :key="sup.id">
                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-white" x-text="sup.name"></td>
                        <td class="px-6 py-4 text-sm text-slate-500" x-text="sup.mobile || 'N/A'"></td>
                        <td class="px-6 py-4 text-sm text-slate-500" x-text="sup.email || 'N/A'"></td>
                        <td class="px-6 py-4 text-sm font-bold text-rose-600">₹<span x-text="sup.due_amount"></span></td>
                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap space-x-2">
                            <template x-if="parseFloat(sup.due_amount) > 0">
                                <button @click="openPaySupplierDueModal(sup)"
                                    class="px-2.5 py-1 bg-amber-100 hover:bg-amber-600 text-amber-900 hover:text-white rounded-lg text-xs font-bold transition-all shadow-2xs mr-1"
                                    x-text="t('pay_supplier')">
                                    Pay Due
                                </button>
                            </template>
                            <button @click="openEditSupplierModal(sup)" class="text-xs font-bold text-primary hover:text-primary-hover" x-text="t('edit')">Edit</button>
                            <span class="text-slate-300 dark:text-slate-600">|</span>
                            <button @click="deleteSupplier(sup.id)" class="text-xs font-bold text-rose-600 hover:text-rose-700" x-text="t('delete')">Delete</button>
                        </td>
                    </tr>
                </template>
                <template x-if="!suppliersLoading && suppliers.length === 0">
                    <tr>
                        <td colspan="5" class="text-center text-slate-400 py-8 text-sm" x-text="t('no_suppliers_found')">No suppliers found.</td>
                    </tr>
                </template>
            </tbody>
        </table>
        <x-pagination currentPage="suppliersPage" totalItems="suppliers.length" perPage="suppliersPerPage" loading="suppliersLoading" />
    </div>
</div>

{{-- CUSTOMERS PANEL --}}
<div x-show="page === 'customers'" class="space-y-2">
    <div class="flex justify-end items-center">
       
        <button @click="openNewCustomerModal()" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl transition-all">
            Add Customer
        </button>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-hidden">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Customer Name</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Mobile</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Due Balance</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-slate-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                <template x-if="customersLoading">
                    <tr>
                        <td colspan="5" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                            <p class="text-xs text-slate-400 mt-2 font-medium">Loading customers...</p>
                        </td>
                    </tr>
                </template>
                <template x-for="cust in (customersLoading ? [] : customers)" :key="cust.id">
                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-white" x-text="cust.name"></td>
                        <td class="px-6 py-4 text-sm text-slate-500" x-text="cust.mobile || 'N/A'"></td>
                        <td class="px-6 py-4 text-sm text-slate-500" x-text="cust.email || 'N/A'"></td>
                        <td class="px-6 py-4 text-sm font-bold text-rose-600">₹<span x-text="cust.due_amount"></span></td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <template x-if="parseFloat(cust.due_amount) > 0">
                                <button @click="openCollectCustomerPaymentModal(cust)"
                                    class="px-2.5 py-1 bg-emerald-100 hover:bg-emerald-600 text-emerald-800 hover:text-white rounded-lg text-xs font-bold transition-all shadow-2xs mr-1">
                                    Collect Payment (જમા કરો)
                                </button>
                            </template>
                            <button @click="openEditCustomerModal(cust)" class="text-xs font-bold text-primary hover:text-primary-hover">Edit</button>
                            <span class="text-slate-300 dark:text-slate-600">|</span>
                            <button @click="deleteCustomer(cust.id)" class="text-xs font-bold text-rose-600 hover:text-rose-700">Delete</button>
                        </td>
                    </tr>
                </template>
                <template x-if="!customersLoading && customers.length === 0">
                    <tr>
                        <td colspan="5" class="text-center text-slate-400 py-8 text-sm">No customers found.</td>
                    </tr>
                </template>
            </tbody>
        </table>
        <x-pagination currentPage="customersPage" totalItems="customersTotal" perPage="customersPerPage" loading="customersLoading" />
    </div>
</div>

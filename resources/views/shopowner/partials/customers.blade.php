{{-- CUSTOMERS PANEL --}}
<div x-show="page === 'customers'" class="space-y-2">
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
        {{-- Search Bar --}}
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text"
                x-model="customerSearchQuery"
                @input="customersPage = 1"
                :placeholder="t('search_customer_placeholder') || 'Search customer name or mobile...'"
                class="w-full pl-10 pr-9 py-2 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-sm transition-all">
            <template x-if="customerSearchQuery">
                <button type="button" @click="customerSearchQuery = ''; customersPage = 1"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </template>
        </div>

        {{-- Add Customer Button --}}
        <button @click="openNewCustomerModal()" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl transition-all shadow-sm flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span x-text="t('add_customer')">Add Customer</span>
        </button>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto">
        <table class="w-full divide-y divide-slate-200 dark:divide-gray-700 min-w-[700px]">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('customer_name')">Customer Name</th>
                    <th class="px-3 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('mobile')">Mobile</th>
                    <th class="px-3 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('email')">Email</th>
                    <th class="px-3 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('balance') || 'Balance'">Balance</th>
                    <th class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase" x-text="t('actions')">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                <template x-if="customersLoading">
                    <tr>
                        <td colspan="5" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                            <p class="text-xs text-slate-400 mt-2 font-medium" x-text="t('loading')">Loading customers...</p>
                        </td>
                    </tr>
                </template>
                <template x-for="cust in (customersLoading ? [] : filteredCustomersList().slice((customersPage - 1) * customersPerPage, customersPage * customersPerPage))" :key="cust.id">
                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 text-sm font-bold text-slate-800 dark:text-white" x-text="cust.name"></td>
                        <td class="px-3 py-3 text-sm text-slate-500 whitespace-nowrap" x-text="cust.mobile || 'N/A'"></td>
                        <td class="px-3 py-3 text-sm text-slate-500 max-w-[200px] truncate" :title="cust.email" x-text="cust.email || 'N/A'"></td>
                        <td class="px-3 py-3 text-sm whitespace-nowrap">
                            <template x-if="parseFloat(cust.due_amount || 0) > 0 && parseFloat(cust.credit_balance || 0) > 0">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-rose-600 font-bold text-xs">₹<span x-text="parseFloat(cust.due_amount).toFixed(2)"></span> <span class="text-[10px] font-medium text-rose-500">(Due)</span></span>
                                    <span class="text-emerald-600 font-bold text-xs">₹<span x-text="parseFloat(cust.credit_balance).toFixed(2)"></span> <span class="text-[10px] font-medium text-emerald-500">(Credit)</span></span>
                                </div>
                            </template>
                            <template x-if="parseFloat(cust.due_amount || 0) > 0 && !(parseFloat(cust.credit_balance || 0) > 0)">
                                <span class="text-rose-600 font-bold">
                                    ₹<span x-text="parseFloat(cust.due_amount).toFixed(2)"></span>
                                    <span class="text-[11px] font-medium text-rose-500 ml-1">(Due)</span>
                                </span>
                            </template>
                            <template x-if="parseFloat(cust.credit_balance || 0) > 0 && !(parseFloat(cust.due_amount || 0) > 0)">
                                <span class="text-emerald-600 font-bold">
                                    ₹<span x-text="parseFloat(cust.credit_balance).toFixed(2)"></span>
                                    <span class="text-[11px] font-medium text-emerald-500 ml-1">(Credit)</span>
                                </span>
                            </template>
                            <template x-if="!(parseFloat(cust.due_amount || 0) > 0) && !(parseFloat(cust.credit_balance || 0) > 0)">
                                <span class="text-slate-400 font-medium">₹0.00</span>
                            </template>
                        </td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap space-x-1.5">
                            <button @click="openCustomerPricingModal(cust)" 
                                class="px-2 py-1 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white dark:bg-indigo-900/30 dark:hover:bg-indigo-600 dark:text-indigo-300 rounded-lg text-xs font-semibold transition-all inline-flex items-center gap-1"
                                title="Customer Product Pricing (ઇન્વેન્ટરી ભાવ)">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span>Pricing</span>
                            </button>
                            <template x-if="parseFloat(cust.due_amount) > 0">
                                <button @click="openCollectCustomerPaymentModal(cust)"
                                    class="px-2 py-1 bg-emerald-100 hover:bg-emerald-600 text-emerald-900 hover:text-white rounded-lg text-xs font-semibold transition-all shadow-2xs mr-1"
                                    x-text="t('collect_payment')">
                                    Collect Payment
                                </button>
                            </template>
                            <button @click="openEditCustomerModal(cust)" class="text-xs font-bold text-primary hover:text-primary-hover" x-text="t('edit')">Edit</button>
                            <span class="text-slate-300 dark:text-slate-600">|</span>
                            <button @click="deleteCustomer(cust.id)" class="text-xs font-bold text-rose-600 hover:text-rose-700" x-text="t('delete')">Delete</button>
                        </td>
                    </tr>
                </template>
                <template x-if="!customersLoading && filteredCustomersList().length === 0">
                    <tr>
                        <td colspan="5" class="text-center text-slate-400 py-8 text-sm" x-text="t('no_customers_found')">No customers found.</td>
                    </tr>
                </template>
            </tbody>
        </table>
        <x-pagination currentPage="customersPage" totalItems="filteredCustomersList().length" perPage="customersPerPage" loading="customersLoading" />
    </div>
</div>

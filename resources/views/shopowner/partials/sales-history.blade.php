{{-- SALES HISTORY PANEL --}}
<div x-show="page === 'sales-history'" class="space-y-2">

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row gap-4">
        <div class="flex-1 flex flex-col md:flex-row gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('search')">Search</label>
                <input type="text" :placeholder="t('search_placeholder')" x-model="salesFilter.search" @input.debounce.300ms="loadSales(); salesPage = 1" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div class="w-full md:w-44 shrink-0">
                <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('date')">Date</label>
                <input type="date" x-model="salesFilter.date" onclick="this.showPicker()" @change="loadSales()" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white cursor-pointer">
            </div>
            <div class="w-full md:w-52 shrink-0 relative" x-data="{ open: false }" @click.away="open = false">
                <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('customers')">Customer</label>
                <div class="relative">
                    <!-- Dropdown Trigger Button -->
                    <button type="button" @click="open = !open" 
                        class="w-full flex items-center justify-between px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white bg-white text-left focus:outline-none">
                        <span class="truncate pr-2" x-text="getSelectedSalesCustomerName()"></span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-show="open" x-cloak
                        class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto p-2 space-y-2">
                        <!-- Search Input inside Dropdown -->
                        <input type="text" :placeholder="t('search_customer_placeholder')" 
                            x-model="salesCustomerSearchQuery" 
                            @input.debounce.300ms="searchSalesCustomers()" 
                            @click.stop
                            class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg text-xs dark:bg-gray-900 dark:text-white bg-slate-50 focus:outline-none focus:border-primary">
                        
                        <!-- Customer List Options -->
                        <div class="space-y-1">
                            <button type="button" @click="selectSalesCustomer(null); open = false;"
                                class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-slate-50 dark:hover:bg-gray-700/50 font-medium text-slate-500 dark:text-slate-400"
                                x-text="t('all_customers')">
                                All Customers
                            </button>
                            
                            <template x-for="cust in salesFilteredCustomers" :key="cust.id">
                                <button type="button" @click="selectSalesCustomer(cust); open = false;"
                                    class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary transition-all font-medium text-slate-700 dark:text-slate-300 flex justify-between items-center">
                                    <span x-text="cust.name"></span>
                                    <span class="text-[10px] text-slate-400 font-mono" x-text="cust.mobile || 'No Mobile'"></span>
                                </button>
                            </template>

                            <template x-if="salesFilteredCustomers.length === 0">
                                <div class="text-center py-4 text-xs text-slate-400" x-text="t('no_customers_found')">No customers found.</div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-44 shrink-0">
                <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('status')">Status</label>
                <select x-model="salesFilter.status" @change="loadSales()" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    <option value="" x-text="t('all_statuses')">All Statuses</option>
                    <option value="Completed" x-text="t('completed')">Completed</option>
                    <option value="Partially Paid" x-text="t('partially_paid')">Partially Paid</option>
                    <option value="Unpaid" x-text="t('unpaid')">Unpaid</option>
                    <option value="Partially Returned" x-text="t('partially_returned')">Partially Returned</option>
                </select>
            </div>
        </div>
        <div class="flex items-end gap-2">
            <button @click="clearSalesFilter()" class="px-4 py-2.5 border border-slate-300 dark:border-gray-600 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl transition-all" x-text="t('clear')">Clear</button>
        </div>
    </div>

    {{-- Sales Cards Grid --}}
    <div class="space-y-4">
        <template x-if="salesLoading">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="text-xs text-slate-400 mt-2 font-medium">Loading sales history...</p>
            </div>
        </template>

        <template x-if="!salesLoading && filteredSales().length === 0">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm text-center text-slate-400 text-sm" x-text="t('no_sales_found')">
                No sales found.
            </div>
        </template>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            <template x-for="sale in (salesLoading ? [] : filteredSales().slice((salesPage - 1) * salesPerPage, salesPage * salesPerPage))" :key="sale.id">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-gray-700 shadow-2xs p-2.5 sm:p-3 hover:shadow-md transition-all flex flex-col justify-between space-y-2">
                    {{-- Card Header --}}
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] font-extrabold text-primary tracking-tight" x-text="sale.sale_number"></span>
                        <span :class="sale.status === 'Returned' ? 'bg-rose-100 text-rose-800' : (sale.status === 'Partially Returned' ? 'bg-amber-100 text-amber-800' : (sale.status === 'Partially Paid' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : (sale.status === 'Unpaid' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-emerald-100 text-emerald-800')))" class="px-1.5 py-0.5 rounded-full text-[9px] font-bold whitespace-nowrap inline-block" x-text="t(sale.status.toLowerCase().replace(/ /g, '_')) || sale.status"></span>
                    </div>

                    {{-- Card Body --}}
                    <div class="space-y-0.5 text-[10px] py-1 border-y border-slate-100 dark:border-gray-700/50">
                        <div class="flex justify-between">
                            <span class="text-slate-400" x-text="t('customer_name') + ':'">Customer:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold truncate max-w-[110px]" x-text="sale.customer ? sale.customer.name : t('walk_in_customer')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400" x-text="t('date') + ':'">Date:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-medium" x-text="new Date(sale.sale_date).toLocaleDateString()"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400" x-text="t('payment_type') + ':'">Payment:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-medium" x-text="t(sale.payment_type.toLowerCase()) || sale.payment_type"></span>
                        </div>
                    </div>

                    {{-- Total & Paid/Due Amounts --}}
                    <div class="space-y-0.5">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-semibold text-slate-500" x-text="t('total') + ':'">Total:</span>
                            <span class="text-sm font-extrabold text-slate-900 dark:text-white">₹<span x-text="parseFloat(sale.grand_total).toFixed(2)"></span></span>
                        </div>
                        <template x-if="sale.payment_type === 'Credit' || sale.status === 'Partially Paid' || sale.status === 'Unpaid'">
                            <div class="flex justify-between items-center text-[10px] pt-1 border-t border-dashed border-slate-200 dark:border-gray-700">
                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold"><span x-text="t('paid')">Paid</span>: ₹<span x-text="parseFloat(sale.paid_amount || 0).toFixed(2)"></span></span>
                                <span class="text-rose-600 dark:text-rose-400 font-semibold"><span x-text="t('due')">Due</span>: ₹<span x-text="Math.max(0, parseFloat(sale.grand_total) - parseFloat(sale.paid_amount || 0)).toFixed(2)"></span></span>
                            </div>
                        </template>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-1.5 border-t border-slate-100 dark:border-gray-700/50 gap-1.5 w-full">
                        <!-- Edit Button -->
                        <button @click="openEditSaleModal(sale)"
                            :title="t('edit')"
                            :disabled="sale.status !== 'Completed'"
                            :class="sale.status !== 'Completed' ? 'opacity-40 cursor-not-allowed' : 'hover:bg-primary hover:text-white dark:hover:bg-primary dark:hover:text-white cursor-pointer'"
                            class="flex-1 flex justify-center items-center py-1 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 rounded-md transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>

                        <!-- Invoice Button -->
                        <button @click="viewInvoice(sale.id)" 
                            :title="t('sale_invoice')"
                            class="flex-1 flex justify-center items-center py-1 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 hover:bg-primary hover:text-white dark:hover:bg-primary dark:hover:text-white rounded-md transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </button>

                        <!-- Return Button -->
                        <button @click="returnSale(sale.id)" 
                            :title="t('return_items')"
                            :disabled="sale.status === 'Returned'"
                            :class="sale.status === 'Returned' ? 'opacity-40 cursor-not-allowed' : 'hover:bg-amber-500 hover:text-white dark:hover:bg-amber-500'"
                            class="flex-1 flex justify-center items-center py-1 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 rounded-md transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                        </button>

                        <!-- Delete Button -->
                        <button @click="deleteSale(sale.id)" 
                            :title="t('delete')"
                            class="flex-1 flex justify-center items-center py-1 bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white rounded-md transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <x-pagination currentPage="salesPage" totalItems="filteredSales().length" perPage="salesPerPage" loading="salesLoading" />
    </div>

</div>

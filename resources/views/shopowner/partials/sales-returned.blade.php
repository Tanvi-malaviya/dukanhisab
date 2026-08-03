{{-- SALES RETURNED PANEL --}}
<div x-show="page === 'sales-returned'" class="space-y-2" x-cloak>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row gap-4">
        <div class="flex-1 flex flex-col md:flex-row gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-slate-400 mb-1">Search</label>
                <input type="text" placeholder="Search sale number, customer, status..." x-model="returnedFilter.search" @input.debounce.300ms="returnedSalesPage = 1" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div class="w-full md:w-44 shrink-0">
                <label class="block text-xs font-semibold text-slate-400 mb-1">Date</label>
                <input type="date" x-model="returnedFilter.date" onclick="this.showPicker()" @change="returnedSalesPage = 1" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white cursor-pointer">
            </div>
            <div class="w-full md:w-52 shrink-0 relative" x-data="{ open: false }" @click.away="open = false">
                <label class="block text-xs font-semibold text-slate-400 mb-1">Customer</label>
                <div class="relative">
                    <!-- Dropdown Trigger Button -->
                    <button type="button" @click="open = !open" 
                        class="w-full flex items-center justify-between px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white bg-white text-left focus:outline-none">
                        <span class="truncate pr-2" x-text="getSelectedReturnedCustomerName()"></span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-show="open" x-cloak
                        class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto p-2 space-y-2">
                        <!-- Search Input inside Dropdown -->
                        <input type="text" placeholder="Search customer..." 
                            x-model="returnedCustomerSearchQuery" 
                            @input.debounce.300ms="searchReturnedCustomers()" 
                            @click.stop
                            class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg text-xs dark:bg-gray-900 dark:text-white bg-slate-50 focus:outline-none focus:border-primary">
                        
                        <!-- Customer List Options -->
                        <div class="space-y-1">
                            <button type="button" @click="selectReturnedCustomer(null); open = false;"
                                class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-slate-50 dark:hover:bg-gray-700/50 font-medium text-slate-500 dark:text-slate-400">
                                All Customers
                            </button>
                            
                            <template x-for="cust in returnedFilteredCustomers" :key="cust.id">
                                <button type="button" @click="selectReturnedCustomer(cust); open = false;"
                                    class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary transition-all font-medium text-slate-700 dark:text-slate-300 flex justify-between items-center">
                                    <span x-text="cust.name"></span>
                                    <span class="text-[10px] text-slate-400 font-mono" x-text="cust.mobile || 'No Mobile'"></span>
                                </button>
                            </template>

                            <template x-if="returnedFilteredCustomers.length === 0">
                                <div class="text-center py-4 text-xs text-slate-400">No customers found.</div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-44 shrink-0">
                <label class="block text-xs font-semibold text-slate-400 mb-1">Status</label>
                <select x-model="returnedFilter.status" @change="returnedSalesPage = 1" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="Returned">Returned</option>
                    <option value="Partially Returned">Partially Returned</option>
                </select>
            </div>
        </div>
        <div class="flex items-end gap-2">
            <button @click="clearReturnedFilter()" class="px-4 py-2.5 border border-slate-300 dark:border-gray-600 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl transition-all">Clear</button>
        </div>
    </div>

    {{-- Returned Sales Cards Grid --}}
    <div class="space-y-4">
        <template x-if="salesLoading">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="text-xs text-slate-400 mt-2 font-medium">Loading sales history...</p>
            </div>
        </template>

        <template x-if="!salesLoading && filteredReturnedSales().length === 0">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm text-center text-slate-400 text-sm">
                No returned sales found.
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
            <template x-for="sale in (salesLoading ? [] : filteredReturnedSales().slice((returnedSalesPage - 1) * returnedSalesPerPage, returnedSalesPage * returnedSalesPerPage))" :key="sale.id">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-gray-700 shadow-sm p-3.5 hover:shadow-md transition-all flex flex-col justify-between space-y-3">
                    {{-- Card Header --}}
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-xs font-bold text-primary" x-text="sale.sale_number"></span>
                        </div>
                        <span :class="sale.status === 'Returned' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400'" class="px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap inline-block" x-text="sale.status"></span>
                    </div>

                    {{-- Card Body --}}
                    <div class="space-y-1 text-[11px] py-1.5 border-t border-slate-100 dark:border-gray-700/50">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans">Customer:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold font-sans" x-text="sale.customer ? sale.customer.name : 'Walk-In'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans">Date:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-mono" x-text="new Date(sale.sale_date).toLocaleDateString()"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans">Payment:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-medium font-sans" x-text="sale.payment_type"></span>
                        </div>
                    </div>

                    {{-- Returned Items List & Returned Value --}}
                    <div class="flex flex-col space-y-1.5 bg-rose-50/50 dark:bg-rose-950/10 p-2.5 rounded-xl border border-rose-100 dark:border-rose-900/30">
                        <span class="text-[10px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider font-sans">Returned Items</span>
                        <div class="space-y-1 max-h-24 overflow-y-auto pr-1">
                            <template x-for="item in sale.items" :key="item.id">
                                <template x-if="item.returned_quantity > 0 || (!sale.items.some(i => i.returned_quantity > 0) && sale.status === 'Returned')">
                                    <div class="flex justify-between items-center text-[11px] text-slate-700 dark:text-slate-300">
                                        <span class="truncate font-medium pr-2 font-sans" x-text="item.product ? item.product.name : 'Unknown Product'"></span>
                                        <span class="font-bold text-rose-600 dark:text-rose-400 shrink-0 font-mono" x-text="(item.returned_quantity > 0 ? item.returned_quantity : item.quantity) + ' Qty'"></span>
                                    </div>
                                </template>
                            </template>
                        </div>
                        
                        <div class="flex justify-between items-center pt-2 border-t border-rose-100 dark:border-rose-900/20 mt-1">
                            <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400 font-sans">Returned Value:</span>
                            <span class="text-sm font-extrabold text-rose-700 dark:text-rose-300 font-mono">
                                ₹<span x-text="parseFloat(
                                    sale.items.some(i => i.returned_quantity > 0)
                                        ? sale.items.reduce((sum, item) => sum + (parseFloat(item.returned_quantity) * parseFloat(item.selling_price)), 0)
                                        : (sale.status === 'Returned' ? sale.items.reduce((sum, item) => sum + (parseFloat(item.quantity) * parseFloat(item.selling_price)), 0) : 0)
                                ).toFixed(2)"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-gray-700/50 gap-2 w-full">
                        <!-- Invoice Button -->
                        <button @click="viewInvoice(sale.id)" 
                            title="View Invoice"
                            class="flex-1 flex justify-center items-center py-1.5 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 hover:bg-primary hover:text-white dark:hover:bg-primary dark:hover:text-white rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </button>

                        <!-- Return Button (Only for Partially Returned to allow further returns) -->
                        <button @click="returnSale(sale.id)" 
                            title="Return Sale"
                            :disabled="sale.status === 'Returned'"
                            :class="sale.status === 'Returned' ? 'opacity-40 cursor-not-allowed' : 'hover:bg-amber-500 hover:text-white dark:hover:bg-amber-500'"
                            class="flex-1 flex justify-center items-center py-1.5 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                        </button>

                        <!-- Delete Button -->
                        <button @click="deleteSale(sale.id)" 
                            title="Delete Sale"
                            class="flex-1 flex justify-center items-center py-1.5 bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <x-pagination currentPage="returnedSalesPage" totalItems="filteredReturnedSales().length" perPage="returnedSalesPerPage" loading="salesLoading" />
    </div>

</div>

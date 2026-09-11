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
                    <option value="Cancelled" x-text="t('cancelled') || 'Cancelled'">Cancelled</option>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <template x-for="sale in (salesLoading ? [] : filteredSales().slice((salesPage - 1) * salesPerPage, salesPage * salesPerPage))" :key="sale.id">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-gray-700 shadow-sm p-3.5 hover:shadow-md transition-all flex flex-col justify-between space-y-3">
                    {{-- Card Header --}}
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-primary font-sans" x-text="sale.sale_number"></span>
                        <span :class="sale.status === 'Cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : (sale.status === 'Returned' ? 'bg-rose-100 text-rose-800' : (sale.status === 'Partially Returned' ? 'bg-amber-100 text-amber-800' : (sale.status === 'Partially Paid' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : (sale.status === 'Unpaid' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-emerald-100 text-emerald-800'))))" class="px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap inline-block font-sans" x-text="t(sale.status.toLowerCase().replace(/ /g, '_')) || sale.status"></span>
                    </div>

                    {{-- Card Body --}}
                    <div class="space-y-1 text-[11px] py-1.5 border-y border-slate-100 dark:border-gray-700/50">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans" x-text="t('customer_name') + ':'">Customer:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold font-sans truncate max-w-[150px]" x-text="sale.customer ? sale.customer.name : t('walk_in_customer')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans" x-text="t('date') + ':'">Date:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-sans" x-text="new Date(sale.sale_date).toLocaleDateString()"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans" x-text="t('payment_type') + ':'">Payment:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-medium font-sans" x-text="t(sale.payment_type.toLowerCase()) || sale.payment_type"></span>
                        </div>
                    </div>

                    <template x-if="sale.status === 'Cancelled' && sale.cancellation_reason">
                        <div class="text-[10px] text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-1.5 rounded-lg border border-red-200 dark:border-red-800/40">
                            <span class="font-bold">Reason:</span> <span x-text="sale.cancellation_reason"></span>
                        </div>
                    </template>

                    {{-- Total & Paid/Due Amounts --}}
                    <div class="space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold text-slate-500 font-sans" x-text="t('total') + ':'">Total:</span>
                            <span class="text-base font-extrabold text-slate-900 dark:text-white font-sans">₹<span x-text="parseFloat(sale.grand_total).toFixed(2)"></span></span>
                        </div>
                        <template x-if="sale.payment_type === 'Credit' || sale.status === 'Partially Paid' || sale.status === 'Unpaid'">
                            <div class="flex justify-between items-center text-xs pt-1.5 border-t border-dashed border-slate-200 dark:border-gray-700">
                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold"><span x-text="t('paid')">Paid</span>: ₹<span x-text="parseFloat(sale.paid_amount || 0).toFixed(2)"></span></span>
                                <span class="text-rose-600 dark:text-rose-400 font-semibold"><span x-text="t('due')">Due</span>: ₹<span x-text="Math.max(0, parseFloat(sale.grand_total) - parseFloat(sale.paid_amount || 0)).toFixed(2)"></span></span>
                            </div>
                        </template>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-gray-700/50 gap-2 w-full">
                        <!-- Edit Button -->
                        <button @click="openEditSaleModal(sale)"
                            :title="t('edit')"
                            :disabled="sale.status !== 'Completed'"
                            :class="sale.status !== 'Completed' ? 'opacity-40 cursor-not-allowed' : 'hover:bg-primary hover:text-white dark:hover:bg-primary dark:hover:text-white cursor-pointer'"
                            class="flex-1 flex justify-center items-center py-1.5 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>

                        <!-- Invoice Button -->
                        <button @click="viewInvoice(sale.id)"
                            :title="t('sale_invoice')"
                            class="flex-1 flex justify-center items-center py-1.5 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 hover:bg-primary hover:text-white dark:hover:bg-primary dark:hover:text-white rounded-lg transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </button>

                        <!-- Return Button -->
                        <button @click="returnSale(sale.id)"
                            :title="t('return_items')"
                            :disabled="sale.status === 'Returned' || sale.status === 'Cancelled'"
                            :class="(sale.status === 'Returned' || sale.status === 'Cancelled') ? 'opacity-40 cursor-not-allowed' : 'hover:bg-amber-500 hover:text-white dark:hover:bg-amber-500 cursor-pointer'"
                            class="flex-1 flex justify-center items-center py-1.5 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                        </button>

                        <!-- Cancel Button -->
                        <button @click="openCancelSaleModal(sale)"
                            :title="t('cancel_invoice') || 'Cancel Invoice'"
                            :disabled="sale.status === 'Cancelled' || sale.status === 'Returned'"
                            :class="(sale.status === 'Cancelled' || sale.status === 'Returned') ? 'opacity-30 cursor-not-allowed' : 'hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white cursor-pointer'"
                            class="flex-1 flex justify-center items-center py-1.5 bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <x-pagination currentPage="salesPage" totalItems="filteredSales().length" perPage="salesPerPage" loading="salesLoading" />
    </div>

    {{-- Cancel Sale Modal --}}
    <div x-show="cancelSaleModalOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="cancelSaleModalOpen = false"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4 border border-slate-100 dark:border-gray-700 animate-in fade-in zoom-in duration-150">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-gray-700 pb-3">
                <div class="flex items-center gap-2 text-rose-600 dark:text-rose-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <h3 class="text-base font-bold text-slate-800 dark:text-white" x-text="t('cancel_sale') || 'Cancel Invoice'">Cancel Invoice</h3>
                </div>
                <button @click="cancelSaleModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <template x-if="saleToCancel">
                <div class="space-y-3">
                    <div class="p-3 bg-slate-50 dark:bg-gray-700/50 rounded-xl space-y-1 text-xs">
                        <div class="flex justify-between font-semibold">
                            <span class="text-slate-500">Invoice:</span>
                            <span class="text-primary font-mono" x-text="saleToCancel.sale_number"></span>
                        </div>
                        <div class="flex justify-between font-semibold">
                            <span class="text-slate-500">Total Amount:</span>
                            <span class="text-slate-800 dark:text-white">₹<span x-text="parseFloat(saleToCancel.grand_total).toFixed(2)"></span></span>
                        </div>
                    </div>

                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 rounded-xl text-xs text-amber-800 dark:text-amber-300">
                        ⚠️ <strong>Reversal Notice:</strong> Cancelling will restore product inventory, adjust customer due/store credit, post a CashBook reversal entry, and keep this invoice marked as <strong>Cancelled</strong>.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            Cancellation Reason <span class="text-rose-500">*</span>
                        </label>
                        <textarea x-model="cancelSaleReason" rows="3"
                            placeholder="Enter the reason for cancelling this bill (e.g. Customer cancelled order, wrong items billed)..."
                            class="w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-xs dark:bg-gray-700 dark:text-white focus:outline-none focus:border-rose-500"></textarea>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="cancelSaleModalOpen = false"
                            class="flex-1 py-2.5 bg-slate-100 dark:bg-gray-700 hover:bg-slate-200 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-all cursor-pointer" x-text="t('close')">Close</button>
                        <button type="button" @click="submitCancelSale()"
                            class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer">
                            Confirm Cancellation
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

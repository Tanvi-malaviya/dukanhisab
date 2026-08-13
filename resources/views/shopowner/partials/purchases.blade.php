{{-- PURCHASES PANEL --}}
<div x-show="page === 'purchases'" class="flex flex-col lg:flex-row gap-4 h-auto lg:h-full pb-10 lg:pb-0">

    {{-- Product Selection Side --}}
    <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-4 overflow-hidden min-h-[480px] lg:min-h-0">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
            <div class="relative">
                <input type="text" :placeholder="t('search_product_placeholder')" x-model="pos.searchQuery"
                    @input.debounce.300ms="loadProducts(pos.searchQuery)"
                    class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
            </div>
            <div class="relative">
                <input type="text" id="purchase-barcode" :placeholder="t('scan_barcode_placeholder')" x-model="pos.barcodeInput"
                    @keydown.enter.prevent="handlePurchaseBarcodeScan()"
                    class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-4V8m0 8l-4-4m4 4l4-4"></path></svg>
                </span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto grid grid-cols-2 md:grid-cols-3 gap-3 pr-1 max-h-[500px] content-start">
            <template x-for="prod in filteredProducts()" :key="prod.id">
                <div @click="addPurchaseItemById(prod.id)"
                    class="p-3 border border-slate-200 dark:border-gray-700 rounded-xl transition-all flex flex-col justify-between bg-slate-50 dark:bg-gray-700/50 hover:bg-primary/5 cursor-pointer hover:border-primary">
                    <div>
                        <p class="font-bold text-sm text-slate-800 dark:text-white truncate" x-text="prod.name"></p>
                        <p class="text-[10px] text-slate-400"><span x-text="t('barcode')">Barcode</span>: <span x-text="prod.barcode"></span></p>
                    </div>
                    <div class="flex justify-between items-center mt-3">
                        <span class="text-sm font-extrabold text-primary">₹<span x-text="prod.purchase_price"></span></span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-slate-200 text-slate-700"
                            x-text="t('stock') + ': ' + prod.stock"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Purchase Billing Side --}}
    <div class="w-full lg:w-96 flex flex-col bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-4 overflow-hidden min-h-[400px] lg:min-h-0">
        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3 flex items-center justify-between">
            <span x-text="t('purchase_cart')">Purchase Cart</span>
            <button @click="resetNewPurchase()" class="text-xs text-rose-500 hover:underline" x-text="t('clear_all')">Clear All</button>
        </h3>

        {{-- Supplier Selector --}}
        <div class="flex items-center gap-2 mb-4 relative" x-data="{ open: false }" @click.away="open = false">
            <div class="flex-1 relative">
                <!-- Dropdown Trigger Button -->
                <button type="button" @click="open = !open" 
                    class="w-full flex items-center justify-between px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white bg-white text-left focus:outline-none">
                    <span class="truncate pr-2" x-text="getSelectedPurchaseSupplierName()"></span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Panel -->
                <div x-show="open" x-cloak
                    class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto p-2 space-y-2">
                    <!-- Search Input inside Dropdown -->
                    <input type="text" :placeholder="t('search_supplier_placeholder')" 
                        x-model="purchaseSupplierSearchQuery" 
                        @input.debounce.300ms="searchPurchaseSuppliers()" 
                        @click.stop
                        class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg text-xs dark:bg-gray-900 dark:text-white bg-slate-50 focus:outline-none focus:border-primary">
                    
                    <!-- Supplier List Options -->
                    <div class="space-y-1">
                        <button type="button" @click="selectPurchaseSupplier(null); open = false;"
                            class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-slate-50 dark:hover:bg-gray-700/50 font-medium text-slate-500 dark:text-slate-400"
                            x-text="t('walk_in_supplier')">
                            Walk-In Supplier
                        </button>
                        
                        <template x-for="sup in purchaseFilteredSuppliers" :key="sup.id">
                            <button type="button" @click="selectPurchaseSupplier(sup); open = false;"
                                class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary transition-all font-medium text-slate-700 dark:text-slate-300 flex justify-between items-center">
                                <span x-text="sup.name"></span>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="sup.mobile || 'No Mobile'"></span>
                            </button>
                        </template>

                        <template x-if="purchaseFilteredSuppliers.length === 0">
                            <div class="text-center py-4 text-xs text-slate-400" x-text="t('no_suppliers_found')">No suppliers found.</div>
                        </template>
                    </div>
                </div>
            </div>
            <!-- Add supplier button -->
            <button @click="showSupplierModal = true" class="p-2 border border-slate-300 dark:border-gray-600 hover:border-primary rounded-xl text-slate-500 hover:text-primary transition-all shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </button>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto space-y-2 pr-1 mb-4 max-h-[300px]">
            <template x-for="(item, idx) in newPurchase.items" :key="idx">
                <div class="p-2 bg-slate-50 dark:bg-gray-700/50 rounded-xl border border-slate-100 dark:border-gray-700 flex flex-col gap-1.5">
                    <div class="flex justify-between items-start">
                        <span class="text-xs font-bold text-slate-800 dark:text-white truncate max-w-[180px]" x-text="item.name"></span>
                        <button @click="newPurchase.items.splice(idx, 1)" class="text-slate-400 hover:text-rose-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                    <div class="flex justify-between items-center gap-2">
                        <div class="flex items-center border border-slate-200 dark:border-gray-600 rounded-lg overflow-hidden bg-white dark:bg-gray-800">
                            <button @click="if (item.quantity > 1) item.quantity--" class="px-2 py-0.5 bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300">-</button>
                            <span class="px-2.5 text-xs font-bold" x-text="item.quantity"></span>
                            <button @click="item.quantity++" class="px-2 py-0.5 bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300">+</button>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-slate-400">₹</span>
                            <input type="number" step="0.01" x-model.number="item.purchase_price" class="w-16 px-1.5 py-0.5 border border-slate-200 dark:border-gray-600 rounded text-center text-xs dark:bg-gray-700 dark:text-white">
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">₹<span x-text="(item.purchase_price * item.quantity).toFixed(2)"></span></span>
                    </div>
                </div>
            </template>
            <template x-if="newPurchase.items.length === 0">
                <div class="text-center py-10 text-slate-400 text-sm" x-text="t('purchase_cart_empty')">Cart is empty. Click products or scan barcodes to begin.</div>
            </template>
        </div>

        {{-- Totals & Payment --}}
        <div class="border-t border-slate-200 dark:border-gray-700 pt-3 space-y-2">
            <div class="flex justify-between text-sm font-bold border-t border-dashed border-slate-200 dark:border-gray-700 pt-2">
                <span x-text="t('grand_total')">Grand Total</span>
                <span class="text-primary">₹<span x-text="calculatePurchaseTotal().toFixed(2)"></span></span>
            </div>

            <div class="pt-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1" x-text="t('payment_type')">Payment Type</label>
                <div class="grid grid-cols-4 gap-1.5">
                    <button @click="newPurchase.payment_type = 'Cash'" :class="newPurchase.payment_type === 'Cash' ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all" x-text="t('cash')">Cash</button>
                    <button @click="newPurchase.payment_type = 'UPI'"  :class="newPurchase.payment_type === 'UPI'  ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all" x-text="t('upi')">UPI</button>
                    <button @click="newPurchase.payment_type = 'Bank'" :class="newPurchase.payment_type === 'Bank' ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all" x-text="t('bank')">Bank</button>
                    <button @click="newPurchase.payment_type = 'Credit'" :class="newPurchase.payment_type === 'Credit' ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all" x-text="t('credit')">Credit</button>
                </div>
            </div>

            <button @click="savePurchase()" :disabled="newPurchase.items.length === 0"
                class="w-full mt-3 py-3 bg-primary hover:bg-primary-hover text-white text-sm font-bold rounded-xl shadow-md transition-all disabled:opacity-50 font-sans cursor-pointer"
                x-text="t('save_purchase')">
                Save Purchase
            </button>
        </div>
    </div>

</div>

{{-- PURCHASES HISTORY --}}
<div x-show="page === 'purchase-history'" class="space-y-2">
    <!-- <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white font-sans">Purchases History</h3>
            <p class="text-xs text-slate-500 font-sans">Log incoming inventory and supplier invoices</p>
        </div>
        <button @click="navigateTo('purchases')"
            class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl transition-all shadow-md flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Record Purchase
        </button>
    </div> -->

    {{-- Filter + Search section --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-wrap gap-3 items-end">
        {{-- Search Bar --}}
        <div class="w-full sm:flex-1 min-w-[200px] relative">
            <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('search')">Search</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" x-model="purchaseFilter.search" @input.debounce.300ms="loadPurchases()"
                    :placeholder="t('search_purchase_placeholder')"
                    class="block w-full pl-9 pr-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all">
            </div>
        </div>
        {{-- Month --}}
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('month')">Month</label>
            <input type="month" x-model="purchaseFilter.month" onclick="this.showPicker()" @change="loadPurchases()"
                class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white cursor-pointer">
        </div>
        {{-- Supplier --}}
        <div class="w-full sm:w-auto min-w-[200px] relative" x-data="{ open: false }" @click.away="open = false">
            <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('supplier_name')">Supplier</label>
            <div class="relative">
                <!-- Dropdown Trigger Button -->
                <button type="button" @click="open = !open" 
                    class="w-full flex items-center justify-between px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white bg-white text-left focus:outline-none">
                    <span class="truncate pr-2" x-text="getSelectedPurchaseHistorySupplierName()"></span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Panel -->
                <div x-show="open" x-cloak
                    class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto p-2 space-y-2">
                    <!-- Search Input inside Dropdown -->
                    <input type="text" :placeholder="t('search_supplier_placeholder')" 
                        x-model="purchaseHistorySupplierSearchQuery" 
                        @input.debounce.300ms="searchPurchaseHistorySuppliers()" 
                        @click.stop
                        class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg text-xs dark:bg-gray-900 dark:text-white bg-slate-50 focus:outline-none focus:border-primary">
                    
                    <!-- Supplier List Options -->
                    <div class="space-y-1">
                        <button type="button" @click="selectPurchaseHistorySupplier(null); open = false;"
                            class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-slate-50 dark:hover:bg-gray-700/50 font-medium text-slate-500 dark:text-slate-400"
                            x-text="t('all_suppliers')">
                            All Suppliers
                        </button>
                        
                        <template x-for="sup in purchaseHistoryFilteredSuppliers" :key="sup.id">
                            <button type="button" @click="selectPurchaseHistorySupplier(sup); open = false;"
                                class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary transition-all font-medium text-slate-700 dark:text-slate-300 flex justify-between items-center">
                                <span x-text="sup.name"></span>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="sup.mobile || 'No Mobile'"></span>
                            </button>
                        </template>

                        <template x-if="purchaseHistoryFilteredSuppliers.length === 0">
                            <div class="text-center py-4 text-xs text-slate-400" x-text="t('no_suppliers_found')">No suppliers found.</div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button @click="clearPurchaseFilter()"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl transition-all cursor-pointer" x-text="t('clear')">Reset</button>
        </div>
    </div>

    {{-- Purchase List Cards --}}
    <div class="space-y-4">
        {{-- Loading State --}}
        <template x-if="purchasesLoading">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="text-xs text-slate-400 mt-2 font-medium" x-text="t('loading')">Loading purchases...</p>
            </div>
        </template>

        {{-- Empty State --}}
        <template x-if="!purchasesLoading && filteredPurchases().length === 0">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm text-center text-slate-400 text-sm" x-text="t('no_purchases_found')">
                No purchases found.
            </div>
        </template>

        {{-- Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
            <template x-for="pur in (purchasesLoading ? [] : filteredPurchases())" :key="pur.id">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-gray-700 shadow-sm p-3.5 hover:shadow-md transition-all flex flex-col justify-between space-y-3">
                    {{-- Card Header --}}
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-xs font-bold text-primary font-sans" x-text="pur.purchase_number"></span>
                        </div>
                        <span :class="pur.status === 'Returned' ? 'bg-rose-100 text-rose-800' : (pur.status === 'Partially Returned' ? 'bg-amber-100 text-amber-800' : (pur.status === 'Unpaid' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-emerald-100 text-emerald-800'))" class="px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap inline-block font-sans" x-text="t(pur.status ? pur.status.toLowerCase().replace(/ /g, '_') : 'completed') || pur.status"></span>
                    </div>

                    {{-- Card Body --}}
                    <div class="space-y-1 text-[11px] py-1.5 border-y border-slate-100 dark:border-gray-700/50">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans" x-text="t('supplier_name') + ':'">Supplier:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold font-sans truncate max-w-[150px]" x-text="pur.supplier ? pur.supplier.name : t('walk_in_supplier')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans" x-text="t('date') + ':'">Date:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-sans" x-text="new Date(pur.purchase_date).toLocaleDateString()"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans" x-text="t('payment_type') + ':'">Payment:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-medium font-sans" x-text="t(pur.payment_type.toLowerCase()) || pur.payment_type"></span>
                        </div>
                    </div>

                    {{-- Total Amount --}}
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-semibold text-slate-500 font-sans" x-text="t('total') + ':'">Total:</span>
                        <span class="text-base font-extrabold text-slate-900 dark:text-white font-sans">₹<span x-text="parseFloat(pur.total_amount).toFixed(2)"></span></span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-gray-700/50 gap-2 w-full">
                        <!-- Edit Button -->
                        <button @click="openEditPurchaseModal(pur)"
                            :title="t('edit')"
                            :disabled="pur.status && pur.status !== 'Completed'"
                            :class="(pur.status && pur.status !== 'Completed') ? 'opacity-40 cursor-not-allowed' : 'hover:bg-primary hover:text-white dark:hover:bg-primary dark:hover:text-white cursor-pointer'"
                            class="flex-1 flex justify-center items-center py-1.5 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>

                        <!-- Invoice Button -->
                        <button @click="viewPurchase(pur.id)"
                            :title="t('purchase_invoice')"
                            class="flex-1 flex justify-center items-center py-1.5 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 hover:bg-primary hover:text-white dark:hover:bg-primary dark:hover:text-white rounded-lg transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </button>

                        <!-- Return Button -->
                        <button @click="returnPurchase(pur.id)"
                            :title="t('return_purchase_items')"
                            :disabled="pur.status === 'Returned'"
                            :class="pur.status === 'Returned' ? 'opacity-40 cursor-not-allowed' : 'hover:bg-amber-500 hover:text-white dark:hover:bg-amber-500 cursor-pointer'"
                            class="flex-1 flex justify-center items-center py-1.5 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                        </button>

                        <!-- Delete Button -->
                        <button @click="deletePurchase(pur.id)"
                            :title="t('delete')"
                            class="flex-1 flex justify-center items-center py-1.5 bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white rounded-lg transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <x-pagination currentPage="purchasesPage" totalItems="purchasesTotal" perPage="purchasesPerPage" loading="purchasesLoading" />
    </div>
</div>
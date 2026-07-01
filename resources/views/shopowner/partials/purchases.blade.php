{{-- PURCHASES PANEL --}}
<div x-show="page === 'purchases'" class="flex flex-col lg:flex-row gap-4 h-auto lg:h-full pb-10 lg:pb-0">

    {{-- Product Selection Side --}}
    <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-4 overflow-hidden min-h-[480px] lg:min-h-0">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
            <div class="relative">
                <input type="text" placeholder="Search product name..." x-model="pos.searchQuery"
                    @input.debounce.300ms="loadProducts(pos.searchQuery)"
                    class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
            </div>
            <div class="relative">
                <input type="text" id="purchase-barcode" placeholder="Scan barcode..." x-model="pos.barcodeInput"
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
                        <p class="text-[10px] text-slate-400">Barcode: <span x-text="prod.barcode"></span></p>
                    </div>
                    <div class="flex justify-between items-center mt-3">
                        <span class="text-sm font-extrabold text-primary">₹<span x-text="prod.purchase_price"></span></span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-slate-200 text-slate-700"
                            x-text="'Stock: ' + prod.stock"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Purchase Billing Side --}}
    <div class="w-full lg:w-96 flex flex-col bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-4 overflow-hidden min-h-[400px] lg:min-h-0">
        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3 flex items-center justify-between">
            Purchase Cart
            <button @click="resetNewPurchase()" class="text-xs text-rose-500 hover:underline">Clear All</button>
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
                    <input type="text" placeholder="Search supplier..." 
                        x-model="purchaseSupplierSearchQuery" 
                        @input.debounce.300ms="searchPurchaseSuppliers()" 
                        @click.stop
                        class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg text-xs dark:bg-gray-900 dark:text-white bg-slate-50 focus:outline-none focus:border-primary">
                    
                    <!-- Supplier List Options -->
                    <div class="space-y-1">
                        <button type="button" @click="selectPurchaseSupplier(null); open = false;"
                            class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-slate-50 dark:hover:bg-gray-700/50 font-medium text-slate-500 dark:text-slate-400">
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
                            <div class="text-center py-4 text-xs text-slate-400">No suppliers found.</div>
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
                <div class="text-center py-10 text-slate-400 text-sm">Cart is empty. Click products or scan barcodes to begin.</div>
            </template>
        </div>

        {{-- Totals & Payment --}}
        <div class="border-t border-slate-200 dark:border-gray-700 pt-3 space-y-2">
            <div class="flex justify-between text-sm font-bold border-t border-dashed border-slate-200 dark:border-gray-700 pt-2">
                <span>Grand Total</span>
                <span class="text-primary">₹<span x-text="calculatePurchaseTotal().toFixed(2)"></span></span>
            </div>

            <div class="pt-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Payment Type</label>
                <div class="grid grid-cols-4 gap-1.5">
                    <button @click="newPurchase.payment_type = 'Cash'" :class="newPurchase.payment_type === 'Cash' ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Cash</button>
                    <button @click="newPurchase.payment_type = 'UPI'"  :class="newPurchase.payment_type === 'UPI'  ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">UPI</button>
                    <button @click="newPurchase.payment_type = 'Bank'" :class="newPurchase.payment_type === 'Bank' ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Bank</button>
                    <button @click="newPurchase.payment_type = 'Credit'" :class="newPurchase.payment_type === 'Credit' ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Credit</button>
                </div>
            </div>

            <button @click="savePurchase()" :disabled="newPurchase.items.length === 0"
                class="w-full mt-3 py-3 bg-primary hover:bg-primary-hover text-white text-sm font-bold rounded-xl shadow-md transition-all disabled:opacity-50 font-sans cursor-pointer">
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
            <label class="block text-xs font-semibold text-slate-400 mb-1">Search</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" x-model="purchaseFilter.search" @input.debounce.300ms="loadPurchases()"
                    placeholder="Search by Purchase No or Supplier..."
                    class="block w-full pl-9 pr-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all">
            </div>
        </div>
        {{-- Month --}}
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-semibold text-slate-400 mb-1">Month</label>
            <input type="month" x-model="purchaseFilter.month" @change="loadPurchases()"
                class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
        </div>
        {{-- Supplier --}}
        <div class="w-full sm:w-auto min-w-[200px] relative" x-data="{ open: false }" @click.away="open = false">
            <label class="block text-xs font-semibold text-slate-400 mb-1">Supplier</label>
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
                    <input type="text" placeholder="Search supplier..." 
                        x-model="purchaseHistorySupplierSearchQuery" 
                        @input.debounce.300ms="searchPurchaseHistorySuppliers()" 
                        @click.stop
                        class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg text-xs dark:bg-gray-900 dark:text-white bg-slate-50 focus:outline-none focus:border-primary">
                    
                    <!-- Supplier List Options -->
                    <div class="space-y-1">
                        <button type="button" @click="selectPurchaseHistorySupplier(null); open = false;"
                            class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-slate-50 dark:hover:bg-gray-700/50 font-medium text-slate-500 dark:text-slate-400">
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
                            <div class="text-center py-4 text-xs text-slate-400">No suppliers found.</div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button @click="clearPurchaseFilter()"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl transition-all cursor-pointer">Reset</button>
        </div>
    </div>

    {{-- Purchase List Cards --}}
    <div class="space-y-4">
        {{-- Loading State --}}
        <template x-if="purchasesLoading">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="text-xs text-slate-400 mt-2 font-medium">Loading purchases...</p>
            </div>
        </template>

        {{-- Empty State --}}
        <template x-if="!purchasesLoading && filteredPurchases().length === 0">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm text-center text-slate-400 text-sm">
                <template x-if="purchaseFilter.search">
                    <span>No purchases found matching "<span class="font-bold text-slate-600 dark:text-slate-300" x-text="purchaseFilter.search"></span>"</span>
                </template>
                <template x-if="!purchaseFilter.search">
                    <span>No purchases recorded. Click "Record Purchase" to start.</span>
                </template>
            </div>
        </template>

        {{-- Cards Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            <template x-for="pur in (purchasesLoading ? [] : filteredPurchases())" :key="pur.id">
                <div @click="viewPurchase(pur.id)"
                    class="p-3 border border-slate-200 dark:border-gray-700 rounded-xl transition-all flex flex-col justify-between bg-slate-50 dark:bg-gray-700/50 hover:bg-primary/5 cursor-pointer hover:border-primary relative group">
                    
                    {{-- Delete Button (Hover state/top-right) --}}
                    <button @click.stop="deletePurchase(pur.id)" 
                        title="Delete Purchase"
                        class="absolute top-2 right-2 p-1 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all opacity-60 group-hover:opacity-100 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>

                    <div>
                        <p class="font-bold text-sm text-slate-800 dark:text-white truncate pr-6 font-sans" x-text="pur.purchase_number"></p>
                        <p class="text-[10px] text-slate-400 mt-0.5 truncate font-sans" x-text="'Supplier: ' + (pur.supplier ? pur.supplier.name : 'Walk-In Supplier')"></p>
                        <p class="text-[10px] text-slate-400 font-sans" x-text="'Date: ' + new Date(pur.purchase_date).toLocaleDateString()"></p>
                    </div>
                    <div class="flex justify-between items-center mt-3">
                        <span class="text-sm font-extrabold text-primary font-sans">₹<span x-text="parseFloat(pur.total_amount).toFixed(2)"></span></span>
                        <div class="flex items-center gap-1.5">
                            <template x-if="pur.status === 'Partially Returned'">
                                <span class="text-[9px] px-1.5 py-0.5 rounded-full font-bold font-sans bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">Partial Return</span>
                            </template>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold font-sans"
                                :class="{
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300': pur.payment_type === 'Cash',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300': pur.payment_type === 'Bank' || pur.payment_type === 'UPI',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300': pur.payment_type === 'Credit'
                                }"
                                x-text="pur.payment_type"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <x-pagination currentPage="purchasesPage" totalItems="purchasesTotal" perPage="purchasesPerPage" loading="purchasesLoading" />
    </div>
</div>
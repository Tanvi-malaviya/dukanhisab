{{-- PURCHASES RETURNED --}}
<div x-show="page === 'purchase-returned'" class="space-y-2" x-cloak>
    <!-- <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white font-sans">Purchases Returned</h3>
            <p class="text-xs text-slate-500 font-sans">View history of returned purchases and stock adjustments</p>
        </div>
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
                <input type="text" x-model="purchaseReturnedFilter.search" @input.debounce.300ms="loadPurchases()"
                    placeholder="Search by Purchase No or Supplier..."
                    class="block w-full pl-9 pr-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all">
            </div>
        </div>
        {{-- Month --}}
        <div class="w-full sm:w-auto">
            <label class="block text-xs font-semibold text-slate-400 mb-1">Month</label>
            <input type="month" x-model="purchaseReturnedFilter.month" @change="loadPurchases()"
                class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
        </div>
        {{-- Supplier --}}
        <div class="w-full sm:w-auto min-w-[200px] relative" x-data="{ open: false }" @click.away="open = false">
            <label class="block text-xs font-semibold text-slate-400 mb-1">Supplier</label>
            <div class="relative">
                <!-- Dropdown Trigger Button -->
                <button type="button" @click="open = !open" 
                    class="w-full flex items-center justify-between px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white bg-white text-left focus:outline-none">
                    <span class="truncate pr-2" x-text="getSelectedPurchaseReturnedSupplierName()"></span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Panel -->
                <div x-show="open" x-cloak
                    class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto p-2 space-y-2">
                    <!-- Search Input inside Dropdown -->
                    <input type="text" placeholder="Search supplier..." 
                        x-model="purchaseReturnedSupplierSearchQuery" 
                        @input.debounce.300ms="searchPurchaseReturnedSuppliers()" 
                        @click.stop
                        class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg text-xs dark:bg-gray-900 dark:text-white bg-slate-50 focus:outline-none focus:border-primary">
                    
                    <!-- Supplier List Options -->
                    <div class="space-y-1">
                        <button type="button" @click="selectPurchaseReturnedSupplier(null); open = false;"
                            class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-slate-50 dark:hover:bg-gray-700/50 font-medium text-slate-500 dark:text-slate-400">
                            All Suppliers
                        </button>
                        
                        <template x-for="sup in purchaseReturnedFilteredSuppliers" :key="sup.id">
                            <button type="button" @click="selectPurchaseReturnedSupplier(sup); open = false;"
                                class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary transition-all font-medium text-slate-700 dark:text-slate-300 flex justify-between items-center">
                                <span x-text="sup.name"></span>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="sup.mobile || 'No Mobile'"></span>
                            </button>
                        </template>

                        <template x-if="purchaseReturnedFilteredSuppliers.length === 0">
                            <div class="text-center py-4 text-xs text-slate-400">No suppliers found.</div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button @click="clearPurchaseReturnedFilter()"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl transition-all cursor-pointer">Reset</button>
        </div>
    </div>

    {{-- Purchase List Cards --}}
    <div class="space-y-4">
        {{-- Loading State --}}
        <template x-if="purchasesLoading">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="text-xs text-slate-400 mt-2 font-medium">Loading returned purchases...</p>
            </div>
        </template>

        {{-- Empty State --}}
        <template x-if="!purchasesLoading && filteredPurchases().length === 0">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm text-center text-slate-400 text-sm">
                <template x-if="purchaseReturnedFilter.search">
                    <span>No returned purchases found matching "<span class="font-bold text-slate-600 dark:text-slate-300" x-text="purchaseReturnedFilter.search"></span>"</span>
                </template>
                <template x-if="!purchaseReturnedFilter.search">
                    <span>No returned purchases recorded.</span>
                </template>
            </div>
        </template>

        {{-- Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
            <template x-for="pur in (purchasesLoading ? [] : filteredPurchases())" :key="pur.id">
                <div @click="viewPurchase(pur.id)"
                    class="p-3 border border-slate-200 dark:border-gray-700 rounded-xl transition-all flex flex-col justify-between bg-white dark:bg-gray-800 hover:shadow-md cursor-pointer hover:border-primary relative group space-y-3">
                    
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-sm text-slate-800 dark:text-white truncate font-sans" x-text="pur.purchase_number"></span>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap inline-block font-sans"
                                    :class="pur.status === 'Returned' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400'"
                                    x-text="pur.status"></span>
                                <button @click.stop="deletePurchase(pur.id)" 
                                    title="Delete Record"
                                    class="p-1 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="space-y-1 text-[11px] py-1.5 border-t border-slate-100 dark:border-gray-700/50">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans">Supplier:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold font-sans truncate max-w-[150px]" x-text="pur.supplier ? pur.supplier.name : 'Walk-In Supplier'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans">Date:</span>
                            <span class="text-slate-700 dark:text-slate-300 font-mono" x-text="new Date(pur.purchase_date).toLocaleDateString()"></span>
                        </div>
                    </div>

                    {{-- Returned Items List & Returned Value --}}
                    <div class="flex flex-col space-y-1.5 bg-rose-50/50 dark:bg-rose-950/10 p-2.5 rounded-xl border border-rose-100 dark:border-rose-900/30">
                        <span class="text-[10px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider font-sans">Returned Items</span>
                        <div class="space-y-1 max-h-24 overflow-y-auto pr-1">
                            <template x-for="item in pur.items" :key="item.id">
                                <template x-if="item.returned_quantity > 0 || (!pur.items.some(i => i.returned_quantity > 0) && pur.status === 'Returned')">
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
                                    pur.items && pur.items.some(i => i.returned_quantity > 0)
                                        ? pur.items.reduce((sum, item) => sum + (parseFloat(item.returned_quantity) * parseFloat(item.purchase_price)), 0)
                                        : (pur.status === 'Returned' && pur.items ? pur.items.reduce((sum, item) => sum + (parseFloat(item.quantity) * parseFloat(item.purchase_price)), 0) : 0)
                                ).toFixed(2)"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <x-pagination currentPage="returnedPurchasesPage" totalItems="returnedPurchasesTotal" perPage="purchasesPerPage" loading="purchasesLoading" />
    </div>
</div>

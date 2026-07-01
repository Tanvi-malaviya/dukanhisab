{{-- SALES POS MODULE --}}
<div x-show="page === 'sales'" class="flex flex-col lg:flex-row gap-4 h-auto lg:h-full pb-10 lg:pb-0">

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
                <input type="text" id="pos-barcode" placeholder="Scan barcode..." x-model="pos.barcodeInput"
                    @keydown.enter.prevent="handleBarcodeScan()"
                    class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-4V8m0 8l-4-4m4 4l4-4"></path></svg>
                </span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto grid grid-cols-2 md:grid-cols-3 gap-3 pr-1 max-h-[500px] content-start">
            <template x-for="prod in filteredProducts()" :key="prod.id">
                <div @click="prod.stock > 0 ? addToBill(prod) : showToast('Product is Out of Stock!', 'error')"
                    :class="prod.stock <= 0 ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-gray-800' : 'bg-slate-50 dark:bg-gray-700/50 hover:bg-primary/5 cursor-pointer hover:border-primary'"
                    class="p-3 border border-slate-200 dark:border-gray-700 rounded-xl transition-all flex flex-col justify-between">
                    <div>
                        <p class="font-bold text-sm text-slate-800 dark:text-white truncate" x-text="prod.name"></p>
                        <p class="text-[10px] text-slate-400">Barcode: <span x-text="prod.barcode"></span></p>
                    </div>
                    <div class="flex justify-between items-center mt-3">
                        <span class="text-sm font-extrabold text-primary">₹<span x-text="prod.selling_price"></span></span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                            :class="prod.stock <= 0 ? 'bg-rose-100 text-rose-700' : (prod.stock <= prod.low_stock_threshold ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-700')"
                            x-text="prod.stock <= 0 ? 'Out of Stock' : 'Qty: ' + prod.stock"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Billing Side --}}
    <div class="w-full lg:w-96 flex flex-col bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-4 overflow-hidden min-h-[400px] lg:min-h-0">
        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3 flex items-center justify-between">
            Billing Cart
            <button @click="resetPOS()" class="text-xs text-rose-500 hover:underline">Clear All</button>
        </h3>

        {{-- Customer Selector --}}
        <div class="flex items-center gap-2 mb-4 relative" x-data="{ open: false }" @click.away="open = false">
            <div class="flex-1 relative">
                <!-- Dropdown Trigger Button -->
                <button type="button" @click="open = !open" 
                    class="w-full flex items-center justify-between px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white bg-white text-left focus:outline-none">
                    <span class="truncate pr-2" x-text="getSelectedPosCustomerName()"></span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Panel -->
                <div x-show="open" x-cloak
                    class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto p-2 space-y-2">
                    <!-- Search Input inside Dropdown -->
                    <input type="text" placeholder="Search customer name or mobile..." 
                        x-model="posCustomerSearchQuery" 
                        @input.debounce.300ms="searchPosCustomers()" 
                        @click.stop
                        class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg text-xs dark:bg-gray-900 dark:text-white bg-slate-50 focus:outline-none focus:border-primary">
                    
                    <!-- Customer List Options -->
                    <div class="space-y-1">
                        <!-- Option: Walk-in -->
                        <button type="button" @click="selectPosCustomer(null); open = false;"
                            class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-slate-50 dark:hover:bg-gray-700/50 font-medium text-slate-500 dark:text-slate-400">
                            Walk-In Customer
                        </button>
                        
                        <!-- Filtered list of Customers -->
                        <template x-for="cust in posFilteredCustomers" :key="cust.id">
                            <button type="button" @click="selectPosCustomer(cust); open = false;"
                                class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary transition-all font-medium text-slate-700 dark:text-slate-300 flex justify-between items-center">
                                <span x-text="cust.name"></span>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="cust.mobile || 'No Mobile'"></span>
                            </button>
                        </template>

                        <!-- No customers found message -->
                        <template x-if="posFilteredCustomers.length === 0">
                            <div class="text-center py-4 text-xs text-slate-400">No customers found.</div>
                        </template>
                    </div>
                </div>
            </div>
            <!-- Add customer button -->
            <button @click="showCustomerModal = true" class="p-2 border border-slate-300 dark:border-gray-600 hover:border-primary rounded-xl text-slate-500 hover:text-primary transition-all shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </button>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto space-y-2 pr-1 mb-4 max-h-[300px]">
            <template x-for="(item, idx) in pos.items" :key="idx">
                <div class="p-2 bg-slate-50 dark:bg-gray-700/50 rounded-xl border border-slate-100 dark:border-gray-700 flex flex-col gap-1.5">
                    <div class="flex justify-between items-start">
                        <span class="text-xs font-bold text-slate-800 dark:text-white truncate max-w-[180px]" x-text="item.name"></span>
                        <button @click="removeFromBill(idx)" class="text-slate-400 hover:text-rose-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                    <div class="flex justify-between items-center gap-2">
                        <div class="flex items-center border border-slate-200 dark:border-gray-600 rounded-lg overflow-hidden">
                            <button @click="decreaseQty(idx)" class="px-2 py-0.5 bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300">-</button>
                            <span class="px-2.5 text-xs font-bold" x-text="item.quantity"></span>
                            <button @click="increaseQty(idx)" class="px-2 py-0.5 bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300">+</button>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-slate-400">₹</span>
                            <input type="number" step="0.01" x-model.number="item.selling_price" class="w-16 px-1.5 py-0.5 border border-slate-200 dark:border-gray-600 rounded text-center text-xs dark:bg-gray-700 dark:text-white">
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">₹<span x-text="(item.selling_price * item.quantity).toFixed(2)"></span></span>
                    </div>
                </div>
            </template>
            <template x-if="pos.items.length === 0">
                <div class="text-center py-10 text-slate-400 text-sm">Cart is empty. Click products or scan barcodes to begin.</div>
            </template>
        </div>

        {{-- Totals & Payment --}}
        <div class="border-t border-slate-200 dark:border-gray-700 pt-3 space-y-2">
            <div class="flex justify-between text-xs text-slate-600 dark:text-slate-400">
                <span>Subtotal</span>
                <span>₹<span x-text="calculateSubtotal().toFixed(2)"></span></span>
            </div>
            <div class="flex justify-between items-center text-xs text-slate-600 dark:text-slate-400">
                <span>Discount (₹)</span>
                <input type="number" x-model.number="pos.discount" class="w-20 px-2 py-1 border border-slate-300 dark:border-gray-600 rounded-lg text-right text-xs dark:bg-gray-700 dark:text-white">
            </div>
            <div class="flex justify-between text-sm font-bold border-t border-dashed border-slate-200 dark:border-gray-700 pt-2">
                <span>Grand Total</span>
                <span class="text-primary">₹<span x-text="calculateGrandTotal().toFixed(2)"></span></span>
            </div>

            <div class="pt-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Payment Type</label>
                <div class="grid grid-cols-4 gap-1.5">
                    <button @click="pos.paymentType = 'Cash'" :class="pos.paymentType === 'Cash' ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Cash</button>
                    <button @click="pos.paymentType = 'UPI'"  :class="pos.paymentType === 'UPI'  ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">UPI</button>
                    <button @click="pos.paymentType = 'Bank'" :class="pos.paymentType === 'Bank' ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Bank</button>
                    <button @click="pos.paymentType = 'Credit'" :class="pos.paymentType === 'Credit' ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Credit</button>
                </div>
            </div>

            <button @click="saveSale()" :disabled="pos.items.length === 0"
                class="w-full mt-3 py-3 bg-primary hover:bg-primary-hover text-white text-sm font-bold rounded-xl shadow-md transition-all disabled:opacity-50">
                Save & Print Bill
            </button>
        </div>
    </div>

</div>

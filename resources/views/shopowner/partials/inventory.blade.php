{{-- INVENTORY PANEL --}}
<div x-show="page === 'inventory'" class="space-y-2" x-data="{ 
    showAdjustModal: false, 
    adjustForm: { product_id: '', type: 'addition', quantity: 0, reason: '' }, 
    subTab: 'stock', 
    stockSearch: '', 
    stockFilter: 'all',
    
    filteredStockProducts() {
        return this.products.filter(prod => {
            const isOutOfStock = prod.stock <= 0;
            const isLowStock = prod.stock > 0 && prod.stock <= prod.low_stock_threshold;
            const isInStock = prod.stock > prod.low_stock_threshold;
            
            let matchesStatus = true;
            if (this.stockFilter === 'out') matchesStatus = isOutOfStock;
            else if (this.stockFilter === 'low') matchesStatus = isLowStock;
            else if (this.stockFilter === 'in') matchesStatus = isInStock;
            
            return matchesStatus;
        });
    }
}">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      
      
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
        {{-- Card 1: Total SKUs --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-slate-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400">Total Products</p>
                <h4 class="text-lg font-bold text-slate-800 dark:text-white" x-text="products.length"></h4>
            </div>
        </div>

        {{-- Card 2: Total Items Qty --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-slate-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400">Total Stock Qty</p>
                <h4 class="text-lg font-bold text-slate-800 dark:text-white" x-text="products.reduce((sum, p) => sum + (parseInt(p.stock) || 0), 0)"></h4>
            </div>
        </div>

        {{-- Card 3: Low Stock --}}
        <div @click="stockFilter = 'low'; subTab = 'stock'" class="cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-xl border border-slate-200 dark:border-gray-700 shadow-sm hover:border-amber-400 transition-all flex items-center gap-4">
            <div class="p-3 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400">Low Stock Alert</p>
                <h4 class="text-lg font-bold text-amber-600 dark:text-amber-400" x-text="products.filter(p => p.stock > 0 && p.stock <= p.low_stock_threshold).length"></h4>
            </div>
        </div>

        {{-- Card 4: Out of Stock --}}
        <div @click="stockFilter = 'out'; subTab = 'stock'" class="cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-xl border border-slate-200 dark:border-gray-700 shadow-sm hover:border-rose-400 transition-all flex items-center gap-4">
            <div class="p-3 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400">Out of Stock</p>
                <h4 class="text-lg font-bold text-rose-600 dark:text-rose-400" x-text="products.filter(p => p.stock <= 0).length"></h4>
            </div>
        </div>
    </div>

    {{-- Tabs within Inventory --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="flex justify-between items-center border-b border-slate-200 dark:border-gray-700 px-4">
            <div class="flex">
                <button @click="subTab = 'stock'" :class="subTab === 'stock' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'" class="px-4 py-3 border-b-2 text-sm transition-all focus:outline-none">
                    Stock Status
                </button>
                <button @click="subTab = 'history'; loadStockHistory()" :class="subTab === 'history' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'" class="px-4 py-3 border-b-2 text-sm transition-all focus:outline-none">
                    Adjustment History
                </button>
            </div>
            
            <button @click="openNewProductModal()" class="px-5 py-2.5 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-lg transition-all shadow-md flex items-center justify-center gap-2 my-1.5 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Product
            </button>
        </div>

        {{-- Tab 1: Stock Status --}}
        <div class="p-4 space-y-4" x-show="subTab === 'stock'">
            {{-- Filtering row --}}
            <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                {{-- Search Bar --}}
                <div class="w-full sm:max-w-md relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" x-model="stockSearch" @input.debounce.300ms="loadProducts(stockSearch)" placeholder="Search product name or barcode..." class="block w-full pl-9 pr-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                </div>
                
                {{-- Status Pills --}}
                <div class="flex gap-1.5 p-1 bg-slate-100 dark:bg-gray-700 rounded-xl w-full sm:w-auto">
                    <button @click="stockFilter = 'all'" :class="stockFilter === 'all' ? 'bg-white dark:bg-gray-600 text-slate-800 dark:text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-300'" class="flex-1 sm:flex-none px-3 py-1.5 rounded-lg text-xs transition-all">All</button>
                    <button @click="stockFilter = 'in'" :class="stockFilter === 'in' ? 'bg-emerald-500 text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-800'" class="flex-1 sm:flex-none px-3 py-1.5 rounded-lg text-xs transition-all">In Stock</button>
                    <button @click="stockFilter = 'low'" :class="stockFilter === 'low' ? 'bg-amber-500 text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-800'" class="flex-1 sm:flex-none px-3 py-1.5 rounded-lg text-xs transition-all">Low Stock</button>
                    <button @click="stockFilter = 'out'" :class="stockFilter === 'out' ? 'bg-rose-500 text-white shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-800'" class="flex-1 sm:flex-none px-3 py-1.5 rounded-lg text-xs transition-all">Out of Stock</button>
                </div>
            </div>

            {{-- Table --}}
            <div class="border border-slate-100 dark:border-gray-700 rounded-xl overflow-x-auto overflow-y-hidden">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-gray-700/30">
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Product Name</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Barcode</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Selling Price</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Stock Level</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                        <template x-if="productsLoading">
                            <tr>
                                <td colspan="6" class="text-center py-8">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                                    <p class="text-xs text-slate-400 mt-2 font-medium">Loading inventory...</p>
                                </td>
                            </tr>
                        </template>
                        <template x-for="prod in (productsLoading ? [] : filteredStockProducts().slice((stockStatusPage - 1) * stockStatusPerPage, stockStatusPage * stockStatusPerPage))" :key="prod.id">
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-white" x-text="prod.name"></td>
                                <td class="px-6 py-4 text-sm text-slate-500" x-text="prod.barcode || '—'"></td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">₹<span x-text="prod.selling_price"></span></td>
                                <td class="px-6 py-4 text-sm font-bold">
                                    <div class="flex items-center gap-1.5">
                                        <span :class="prod.stock <= prod.low_stock_threshold ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-white'" x-text="prod.stock"></span>
                                        <button @click="showAdjustModal = true; adjustForm.product_id = prod.id; adjustForm.type = 'addition'; adjustForm.quantity = 0; adjustForm.reason = 'Manual update'" 
                                            title="Adjust Stock"
                                            class="ml-1 p-1 bg-primary/5 hover:bg-primary/20 dark:bg-primary/10 dark:hover:bg-primary/25 rounded-md text-primary transition-all cursor-pointer border border-primary/10">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span :class="{
                                        'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300': prod.stock <= 0,
                                        'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300': prod.stock > 0 && prod.stock <= prod.low_stock_threshold,
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300': prod.stock > prod.low_stock_threshold
                                    }" class="px-2.5 py-0.5 rounded-full text-xs font-bold whitespace-nowrap" x-text="prod.stock <= 0 ? 'Out of Stock' : (prod.stock <= prod.low_stock_threshold ? 'Low Stock' : 'In Stock')"></span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm space-x-2 whitespace-nowrap">
                                    <button @click="openEditProductModal(prod)" class="text-xs font-bold text-primary hover:text-primary-hover">Edit</button>
                                    <span class="text-slate-300 dark:text-slate-600">|</span>
                                    <button @click="deleteProduct(prod.id)" class="text-xs font-bold text-rose-600 hover:text-rose-700">Delete</button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="!productsLoading && filteredStockProducts().length === 0">
                            <tr>
                                <td colspan="6" class="text-center text-slate-400 py-10 text-sm">
                                    No products found matching filters.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <x-pagination currentPage="stockStatusPage" totalItems="filteredStockProducts().length" perPage="stockStatusPerPage" loading="productsLoading" />
        </div>

        {{-- Tab 2: Adjustment History --}}
        <div class="p-4 space-y-4" x-show="subTab === 'history'">
            <div class="border border-slate-100 dark:border-gray-700 rounded-xl overflow-x-auto overflow-y-hidden">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-gray-700/30">
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Quantity Change</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Before</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">After</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Reason/Reference</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                        <template x-for="hist in stockHistory.slice((stockHistoryPage - 1) * stockHistoryPerPage, stockHistoryPage * stockHistoryPerPage)" :key="hist.id">
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-semibold text-slate-800 dark:text-white" x-text="hist.product_name"></td>
                                <td class="px-6 py-4 text-sm">
                                    <span :class="hist.change_qty > 0 ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800'" class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap" x-text="hist.change_qty > 0 ? 'Stock In' : 'Stock Out'"></span>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold" :class="hist.change_qty > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'" x-text="(hist.change_qty > 0 ? '+' : '') + hist.change_qty"></td>
                                <td class="px-6 py-4 text-sm text-slate-500" x-text="hist.old_stock"></td>
                                <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300" x-text="hist.new_stock"></td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400" x-text="hist.reason"></td>
                                <td class="px-6 py-4 text-sm text-slate-400" x-text="new Date(hist.created_at).toLocaleString()"></td>
                            </tr>
                        </template>
                        <template x-if="stockHistory.length === 0">
                            <tr>
                                <td colspan="7" class="text-center text-slate-400 py-10 text-sm">No inventory movement history found.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <x-pagination currentPage="stockHistoryPage" totalItems="stockHistory.length" perPage="stockHistoryPerPage" />
        </div>




        
    </div>

    {{-- STOCK ADJUSTMENT MODAL --}}
    <div x-show="showAdjustModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all border border-slate-100 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-700/20">
                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Adjust Stock Level
                </h3>
                <button @click="showAdjustModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form @submit.prevent="submitStockAdjustment(); showAdjustModal = false" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Product</label>
                    <select required x-model="adjustForm.product_id" class="block w-full px-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">Select Product</option>
                        <template x-for="p in products" :key="p.id">
                            <option :value="p.id" x-text="p.name + ' (Current: ' + p.stock + ')'"></option>
                        </template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Adjustment Type</label>
                        <select x-model="adjustForm.type" class="block w-full px-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="addition">Add Stock (+)</option>
                            <option value="subtraction">Subtract Stock (-)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Quantity</label>
                        <input type="number" min="1" required x-model.number="adjustForm.quantity" class="block w-full px-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Reason / Note</label>
                    <input type="text" required placeholder="Damaged, Stock Count, Inward..." x-model="adjustForm.reason" class="block w-full px-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                <button type="submit" class="w-full py-3 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all mt-2">Submit Adjustment</button>
            </form>
        </div>
    </div>
</div>

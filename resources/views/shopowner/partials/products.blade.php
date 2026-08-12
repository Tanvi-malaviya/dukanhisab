{{-- PRODUCTS PANEL --}}
<div x-show="page === 'products'" class="space-y-2">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white" x-text="t('stock_catalogue') || 'Stock Catalogue'">Stock Catalogue</h3>
        <button @click="openNewProductModal()"
            class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl transition-all">
            <span x-text="t('add_product')">Add Product</span>
        </button>
    </div>

    <div
        class="bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-hidden">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('product_name')">Product Name</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('barcode')">Barcode</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('purchase_price')">Purchase Price</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('selling_price')">Selling Price</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase" x-text="t('stock')">Stock</th>
                    <!-- <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Alert Limit</th> -->
                    <th class="px-6 py-3 text-right text-xs font-bold text-slate-400 uppercase" x-text="t('actions')">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                <template x-if="productsLoading">
                    <tr>
                        <td colspan="7" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                            <p class="text-xs text-slate-400 mt-2 font-medium" x-text="t('loading')">Loading products...</p>
                        </td>
                    </tr>
                </template>
                <template x-for="prod in (productsLoading ? [] : products)" :key="prod.id">
                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-white" x-text="prod.name"></td>
                        <td class="px-6 py-4 text-sm text-slate-500 font-mono" x-text="prod.barcode"></td>
                        <td class="px-6 py-4 text-sm text-slate-500">₹<span x-text="prod.purchase_price"></span></td>
                        <td class="px-6 py-4 text-sm font-bold text-primary">₹<span x-text="prod.selling_price"></span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span
                                :class="prod.stock <= prod.low_stock_threshold ? 'text-rose-600 font-bold' : 'text-slate-700 dark:text-slate-300'"
                                x-text="prod.stock"></span>
                        </td>
                        <!-- <td class="px-6 py-4 text-sm text-slate-400" x-text="prod.low_stock_threshold"></td> -->
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <button @click="openEditProductModal(prod)"
                                class="text-xs font-bold text-primary hover:text-primary-hover" x-text="t('edit')">Edit</button>
                            <span class="text-slate-300 dark:text-slate-600">|</span>
                            <button @click="deleteProduct(prod.id)"
                                class="text-xs font-bold text-rose-600 hover:text-rose-700" x-text="t('delete')">Delete</button>
                        </td>
                    </tr>
                </template>
                <template x-if="!productsLoading && products.length === 0">
                    <tr>
                        <td colspan="7" class="text-center text-slate-400 py-8 text-sm" x-text="t('no_products_found')">No products found.</td>
                    </tr>
                </template>
            </tbody>
        </table>
        <x-pagination currentPage="productsPage" totalItems="productsTotal" perPage="productsPerPage" loading="productsLoading" />
    </div>
</div>
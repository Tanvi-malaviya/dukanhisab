{{-- ALL MODALS — x-cloak prevents invisible overlay flash before Alpine init --}}

{{-- 1. INVOICE MODAL --}}
<div x-show="showInvoiceModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div
            class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900">
            <h3 class="font-bold text-slate-800 dark:text-white">Sale Invoice</h3>
            <button @click="showInvoiceModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <div id="print-area" class="flex-1 overflow-y-auto p-6 space-y-6 text-slate-800 dark:text-slate-200">
            <template x-if="selectedSale">
                <div>
                    <div
                        class="flex justify-between items-start border-b border-dashed border-slate-200 dark:border-gray-700 pb-4">
                        <div>
                            <h4 class="text-xl font-extrabold text-primary" x-text="shop ? shop.name : 'DukanHisab'">
                            </h4>
                            <p class="text-xs text-slate-400 mt-1" x-text="shop ? shop.address : ''"></p>
                            <p class="text-xs text-slate-400" x-text="'Mobile: ' + (shop ? (shop.mobile || '') : '')">
                            </p>
                            <p class="text-xs text-slate-400"
                                x-text="shop && shop.gst_number ? 'GSTIN: ' + shop.gst_number : ''"></p>
                        </div>
                        <div class="text-right">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center justify-end gap-2">
                                <span>INVOICE</span>
                                <template x-if="selectedSale.status === 'Returned' || selectedSale.status === 'Partially Returned'">
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded-full border"
                                        :class="selectedSale.status === 'Partially Returned' 
                                            ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-800/50' 
                                            : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/30 dark:text-rose-300 dark:border-rose-800/50'"
                                        x-text="selectedSale.status"></span>
                                </template>
                            </h3>
                            <p class="text-xs font-semibold text-slate-500" x-text="selectedSale.sale_number"></p>
                            <p class="text-xs text-slate-400 mt-1"
                                x-text="new Date(selectedSale.sale_date).toLocaleString()"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 py-4 text-xs">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Bill To</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white"
                                x-text="selectedSale.customer ? selectedSale.customer.name : 'Walk-In Customer'"></p>
                            <p class="text-slate-500"
                                x-text="selectedSale.customer && selectedSale.customer.mobile ? 'Mobile: ' + selectedSale.customer.mobile : ''">
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Payment Details</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white"
                                x-text="selectedSale.payment_type"></p>
                            <p class="text-slate-500" x-text="selectedSale.status"></p>
                        </div>
                    </div>
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700 text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-700">
                                <th class="px-4 py-2 text-left font-bold text-slate-500">Item</th>
                                <th class="px-4 py-2 text-right font-bold text-slate-500">Price</th>
                                <th class="px-4 py-2 text-center font-bold text-slate-500">Qty</th>
                                <th class="px-4 py-2 text-right font-bold text-slate-500">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in selectedSale.items" :key="item.id">
                                <tr>
                                    <td class="px-4 py-2.5 font-semibold" x-text="item.product.name"></td>
                                    <td class="px-4 py-2.5 text-right" x-text="'₹' + item.selling_price"></td>
                                    <td class="px-4 py-2.5 text-center" x-text="item.quantity"></td>
                                    <td class="px-4 py-2.5 text-right font-bold"
                                        x-text="'₹' + (item.selling_price * item.quantity).toFixed(2)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div
                        class="border-t border-slate-200 dark:border-gray-700 pt-4 flex flex-col items-end gap-1 text-xs">
                        <div class="flex justify-between w-48"><span class="text-slate-500">Subtotal:</span><span
                                class="font-semibold">₹<span x-text="selectedSale.subtotal"></span></span></div>
                        <div class="flex justify-between w-48"><span class="text-slate-500">Discount:</span><span
                                class="font-semibold">-₹<span x-text="selectedSale.discount"></span></span></div>
                        <div
                            class="flex justify-between w-48 text-sm font-bold border-t border-dashed border-slate-200 dark:border-gray-700 pt-2">
                            <span>Grand Total:</span><span class="text-primary">₹<span
                                    x-text="selectedSale.grand_total"></span></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div
            class="px-6 py-4 border-t border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 flex justify-between gap-3">
            <div class="flex gap-2">
                <button @click="printInvoice()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg">Print</button>
                <button @click="downloadPDF()"
                    class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-lg">Download
                    PDF</button>
            </div>
            <div class="flex gap-2">
                <a :href="whatsappLink()" target="_blank"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">Share
                    WhatsApp</a>
                <a :href="emailShareLink()" target="_blank"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg">Share
                    Email</a>
            </div>
        </div>
    </div>
</div>

{{-- 2. ADD CUSTOMER MODAL --}}
<div x-show="showCustomerModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 dark:text-white"
                x-text="newCustomer.id ? 'Edit Customer' : 'Add Customer'"></h3>
            <button @click="showCustomerModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <form @submit.prevent="saveCustomer()" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Customer Name</label>
                <input type="text" required x-model="newCustomer.name" placeholder="Enter Customer Name"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Mobile Number</label>
                <input type="text" x-model="newCustomer.mobile" maxlength="10"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                    placeholder="Enter 10-digit mobile"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Email Address</label>
                <input type="email" x-model="newCustomer.email" placeholder="Enter Email Address"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all"
                x-text="newCustomer.id ? 'Update Customer' : 'Save Customer'"></button>
        </form>
    </div>
</div>

{{-- 3. ADD/EDIT PRODUCT MODAL --}}
<div x-show="showProductModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 dark:text-white"
                x-text="newProduct.id ? 'Edit Product' : 'Add Product'"></h3>
            <button @click="showProductModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <form @submit.prevent="saveProduct()" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Product Name</label>
                <input type="text" required x-model="newProduct.name" placeholder="Enter Product Name"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Selling Price</label>
                    <input type="number" step="0.01" required x-model.number="newProduct.selling_price"
                        placeholder="Enter Selling Price"
                        class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Purchase Price</label>
                    <input type="number" step="0.01" x-model.number="newProduct.purchase_price"
                        placeholder="Enter Purchase Price"
                        class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">Barcode (Leave blank to
                    auto-generate)</label>
                <input type="text" x-model="newProduct.barcode" placeholder="Enter Barcode" class="block w-full px-3 py-2 border border-slate-300
                     dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1"
                        x-text="newProduct.id ? 'Current Stock' : 'Initial Stock'"></label>
                    <input type="number" required x-model.number="newProduct.stock" placeholder="Enter Stock"
                        class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Alert Limit</label>
                    <input type="number" required x-model.number="newProduct.low_stock_threshold"
                        placeholder="Enter Alert Limit"
                        class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all"
                x-text="newProduct.id ? 'Update Product' : 'Save Product'"></button>
        </form>
    </div>
</div>

{{-- 4. ADD EXPENSE MODAL --}}
<div x-show="showExpenseModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 dark:text-white">Add Expense</h3>
            <button @click="showExpenseModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <form @submit.prevent="saveExpense()" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Expense Description</label>
                <input type="text" required placeholder="Electricity, Tea, Rent..." x-model="newExpense.description"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Amount (₹)</label>
                <input type="number" step="0.01" required x-model.number="newExpense.amount"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Payment Method</label>
                <select x-model="newExpense.payment_method"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    <option value="cash">Cash</option>
                    <option value="upi">UPI / Wallet</option>
                    <option value="bank">Bank Transfer</option>
                </select>
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all">Save
                Expense</button>
        </form>
    </div>
</div>

{{-- 5. ADD SUPPLIER MODAL --}}
<div x-show="showSupplierModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 dark:text-white"
                x-text="newSupplier.id ? 'Edit Supplier' : 'Add Supplier'"></h3>
            <button @click="showSupplierModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <form @submit.prevent="saveSupplier()" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Supplier Name</label>
                <input type="text" required x-model="newSupplier.name" placeholder="Enter Supplier Name"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Mobile Number</label>
                <input type="text" x-model="newSupplier.mobile" maxlength="10"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                    placeholder="Enter Supplier Mobile"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Email Address</label>
                <input type="email" x-model="newSupplier.email" placeholder="Enter Supplier Email"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all"
                x-text="newSupplier.id ? 'Update Supplier' : 'Save Supplier'"></button>
        </form>
    </div>
</div>

{{-- 8. PURCHASE DETAILS MODAL --}}
<div x-show="showPurchaseDetailsModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div
            class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900">
            <h3 class="font-bold text-slate-800 dark:text-white">Purchase Details</h3>
            <button @click="showPurchaseDetailsModal = false" class="text-slate-400 hover:text-slate-600"><svg
                    class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <div id="purchase-print-area" class="flex-1 overflow-y-auto p-6 space-y-6 text-slate-800 dark:text-slate-200">
            <template x-if="selectedPurchase">
                <div>
                    <div
                        class="flex justify-between items-start border-b border-dashed border-slate-200 dark:border-gray-700 pb-4">
                        <div>
                            <h4 class="text-xl font-extrabold text-primary" x-text="shop ? shop.name : 'DukanHisab'">
                            </h4>
                            <p class="text-xs text-slate-400 mt-1" x-text="shop ? shop.address : ''"></p>
                            <p class="text-xs text-slate-400" x-text="'Mobile: ' + (shop ? (shop.mobile || '') : '')">
                            </p>
                        </div>
                        <div class="text-right">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center justify-end gap-2">
                                <span>PURCHASE INVOICE</span>
                                <template x-if="selectedPurchase.status === 'Returned' || selectedPurchase.status === 'Partially Returned'">
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded-full border"
                                        :class="selectedPurchase.status === 'Partially Returned' 
                                            ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-800/50' 
                                            : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/30 dark:text-rose-300 dark:border-rose-800/50'"
                                        x-text="selectedPurchase.status"></span>
                                </template>
                            </h3>
                            <p class="text-xs font-semibold text-slate-500" x-text="selectedPurchase.purchase_number">
                            </p>
                            <p class="text-xs text-slate-400 mt-1"
                                x-text="new Date(selectedPurchase.purchase_date).toLocaleString()"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 py-4 text-xs">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Supplier Details
                            </p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white"
                                x-text="selectedPurchase.supplier ? selectedPurchase.supplier.name : 'Walk-In Supplier'">
                            </p>
                            <p class="text-slate-500"
                                x-text="selectedPurchase.supplier && selectedPurchase.supplier.mobile ? 'Mobile: ' + selectedPurchase.supplier.mobile : ''">
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Payment Mode</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white"
                                x-text="selectedPurchase.payment_type"></p>
                        </div>
                    </div>
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700 text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-700">
                                <th class="px-4 py-2 text-left font-bold text-slate-500">Product</th>
                                <th class="px-4 py-2 text-right font-bold text-slate-500">Unit Price</th>
                                <th class="px-4 py-2 text-center font-bold text-slate-500">Qty</th>
                                <template x-if="selectedPurchase.status === 'Returned' || selectedPurchase.status === 'Partially Returned'">
                                    <th class="px-4 py-2 text-center font-bold text-slate-500">Returned</th>
                                </template>
                                <template x-if="selectedPurchase.status === 'Returned' || selectedPurchase.status === 'Partially Returned'">
                                    <th class="px-4 py-2 text-center font-bold text-slate-500">Net Qty</th>
                                </template>
                                <th class="px-4 py-2 text-right font-bold text-slate-500">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in selectedPurchase.items" :key="item.id">
                                <tr>
                                    <td class="px-4 py-2.5 font-semibold"
                                        x-text="item.product ? item.product.name : 'Deleted Product'"></td>
                                    <td class="px-4 py-2.5 text-right"
                                        x-text="'₹' + parseFloat(item.purchase_price).toFixed(2)"></td>
                                    <td class="px-4 py-2.5 text-center" x-text="item.quantity"></td>
                                    <template x-if="selectedPurchase.status === 'Returned' || selectedPurchase.status === 'Partially Returned'">
                                        <td class="px-4 py-2.5 text-center text-rose-600 font-bold" x-text="item.returned_quantity || 0"></td>
                                    </template>
                                    <template x-if="selectedPurchase.status === 'Returned' || selectedPurchase.status === 'Partially Returned'">
                                        <td class="px-4 py-2.5 text-center font-semibold text-slate-600 dark:text-slate-400" x-text="item.quantity - (item.returned_quantity || 0)"></td>
                                    </template>
                                    <td class="px-4 py-2.5 text-right font-bold"
                                        x-text="'₹' + (item.purchase_price * (item.quantity - (item.returned_quantity || 0))).toFixed(2)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div
                        class="border-t border-slate-200 dark:border-gray-700 pt-4 flex flex-col items-end gap-1 text-xs">
                        <div
                            class="flex justify-between w-48 text-sm font-bold border-t border-dashed border-slate-200 dark:border-gray-700 pt-2">
                            <span>Total Amount:</span><span class="text-primary font-extrabold">₹<span
                                    x-text="parseFloat(selectedPurchase.total_amount).toFixed(2)"></span></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div
            class="px-6 py-4 border-t border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 flex justify-between gap-3">
            <div class="flex gap-2">
                <button @click="printPurchase()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg">Print</button>
                <button @click="downloadPurchasePDF()"
                    class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-lg">Download
                    PDF</button>
                <button @click="returnPurchase(selectedPurchase.id); showPurchaseDetailsModal = false"
                    x-show="selectedPurchase.status !== 'Returned'"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    Return
                </button>
            </div>
            <div class="flex gap-2">
                <a :href="whatsappPurchaseLink()" target="_blank"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">Share
                    WhatsApp</a>
                <a :href="emailPurchaseShareLink()" target="_blank"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg">Share
                    Email</a>
            </div>
        </div>
    </div>
</div>

{{-- 7. CONFIRMATION MODAL --}}
<div x-show="confirmModal.show" x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden p-6 space-y-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-amber-50 dark:bg-amber-900/20 text-amber-500 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
            </div>
            <h3 class="font-bold text-lg text-slate-800 dark:text-white" x-text="confirmModal.title"></h3>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400" x-text="confirmModal.message"></p>
        <div class="flex justify-end gap-3 pt-2">
            <button @click="confirmModal.show = false"
                class="px-4 py-2 border border-slate-300 dark:border-gray-600 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition-all">Cancel</button>
            <button @click="triggerConfirm()"
                class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-xl transition-all">Confirm</button>
        </div>
    </div>
</div>

{{-- 8. RETURN ITEMS MODAL --}}
<div x-show="showReturnModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white">Return Items</h3>
                <p class="text-xs text-slate-400 mt-0.5" x-text="'Sale Number: ' + returnForm.sale_number"></p>
            </div>
            <button @click="showReturnModal = false" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-gray-700">
                        <th class="py-2 text-xs font-semibold text-slate-400">Product</th>
                        <th class="py-2 text-xs font-semibold text-slate-400 text-center">Price</th>
                        <th class="py-2 text-xs font-semibold text-slate-400 text-center">Purchased</th>
                        <th class="py-2 text-xs font-semibold text-slate-400 text-right w-24">Return Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in returnForm.items" :key="item.product_id">
                        <tr class="border-b border-slate-50 dark:border-gray-700/50">
                            <td class="py-3 text-sm font-semibold text-slate-700 dark:text-white" x-text="item.name">
                            </td>
                            <td class="py-3 text-sm text-slate-500 text-center">₹<span
                                    x-text="item.selling_price"></span></td>
                            <td class="py-3 text-sm text-slate-500 text-center" x-text="item.purchasedQty"></td>
                            <td class="py-3 text-right">
                                <input type="number" min="0" :max="item.purchasedQty" x-model.number="item.returnedQty"
                                    class="w-20 px-2 py-1 text-center border border-slate-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:outline-none focus:border-primary">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div class="bg-slate-50 dark:bg-gray-900/50 rounded-xl p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Refund Method:</span>
                    <span class="font-semibold text-slate-800 dark:text-white"
                        x-text="returnForm.payment_type === 'Credit' ? 'Reduce Customer Due' : 'Refund (' + returnForm.payment_type + ')'"></span>
                </div>
                <div class="flex justify-between text-sm border-t border-slate-200/50 dark:border-gray-700/50 pt-2">
                    <span class="font-bold text-slate-700 dark:text-white">Estimated Refund:</span>
                    <span class="font-bold text-primary">₹<span x-text="
                        (() => {
                            const newSubtotal = returnForm.items.reduce((sum, item) => {
                                const rem = item.purchasedQty - (item.returnedQty || 0);
                                return sum + (rem * item.selling_price);
                            }, 0);
                            const oldSubtotal = returnForm.items.reduce((sum, item) => sum + (item.purchasedQty * item.selling_price), 0);
                            const oldDiscount = returnForm.discount;
                            const oldGrandTotal = Math.max(0, oldSubtotal - oldDiscount);
                            const newDiscount = Math.min(oldDiscount, newSubtotal);
                            const newGrandTotal = Math.max(0, newSubtotal - newDiscount);
                            return Math.max(0, oldGrandTotal - newGrandTotal).toFixed(2);
                        })()
                    "></span></span>
                </div>
            </div>
        </div>
        <div
            class="px-6 py-4 border-t border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 flex justify-end gap-3">
            <button @click="showReturnModal = false"
                class="px-4 py-2 border border-slate-300 dark:border-gray-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800 transition-all">Cancel</button>
            <button @click="submitPartialReturn()"
                class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-xl transition-all shadow-md">Process
                Return</button>
        </div>
    </div>
</div>

{{-- 12. EDIT SALE MODAL --}}
<div x-show="showEditSaleModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div
            class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900">
            <h3 class="font-bold text-slate-800 dark:text-white">Edit Sale</h3>
            <button @click="showEditSaleModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <form @submit.prevent="updateSale()" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Customer</label>
                <select x-model="editSaleForm.customer_id"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    <option value="">Walk-In Customer</option>
                    <template x-for="cust in customers" :key="cust.id">
                        <option :value="cust.id" x-text="cust.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Payment Method</label>
                <select x-model="editSaleForm.payment_type" required
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    <option value="Cash">Cash</option>
                    <option value="UPI">UPI</option>
                    <option value="Bank">Bank</option>
                    <option value="Credit">Credit</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Sale Date</label>
                <input type="date" required x-model="editSaleForm.sale_date"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="showEditSaleModal = false"
                    class="px-4 py-2 border border-slate-300 dark:border-gray-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800 transition-all">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-xl transition-all shadow-md">Save
                    Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- 13. PURCHASE RETURN ITEMS MODAL --}}
<div x-show="showPurchaseReturnModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white">Return Purchase Items</h3>
                <p class="text-xs text-slate-400 mt-0.5" x-text="'Purchase Number: ' + purchaseReturnForm.purchase_number"></p>
            </div>
            <button @click="showPurchaseReturnModal = false" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-gray-700">
                        <th class="py-2 font-semibold text-slate-400">Product</th>
                        <th class="py-2 font-semibold text-slate-400 text-center">Price</th>
                        <th class="py-2 font-semibold text-slate-400 text-center">Purchased</th>
                        <th class="py-2 font-semibold text-slate-400 text-right w-24">Return Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in purchaseReturnForm.items" :key="item.product_id">
                        <tr class="border-b border-slate-50 dark:border-gray-700/50">
                            <td class="py-3 font-semibold text-slate-700 dark:text-white" x-text="item.name">
                            </td>
                            <td class="py-3 text-slate-500 text-center">₹<span
                                    x-text="item.purchase_price.toFixed(2)"></span></td>
                            <td class="py-3 text-slate-500 text-center" x-text="item.purchasedQty"></td>
                            <td class="py-3 text-right">
                                <input type="number" min="0" :max="item.purchasedQty" x-model.number="item.returnedQty"
                                    class="w-20 px-2 py-1 text-center border border-slate-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:outline-none focus:border-primary">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div class="bg-slate-50 dark:bg-gray-900/50 rounded-xl p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Refund Method:</span>
                    <span class="font-semibold text-slate-800 dark:text-white"
                        x-text="purchaseReturnForm.payment_type === 'Credit' ? 'Reduce Supplier Due' : 'Refund (' + purchaseReturnForm.payment_type + ')'"></span>
                </div>
                <div class="flex justify-between text-sm border-t border-slate-200/50 dark:border-gray-700/50 pt-2">
                    <span class="font-bold text-slate-700 dark:text-white">Estimated Refund:</span>
                    <span class="font-bold text-primary">₹<span x-text="
                        (() => {
                            const newTotal = purchaseReturnForm.items.reduce((sum, item) => {
                                const rem = item.purchasedQty - (item.returnedQty || 0);
                                return sum + (rem * item.purchase_price);
                            }, 0);
                            const oldTotal = purchaseReturnForm.items.reduce((sum, item) => sum + (item.purchasedQty * item.purchase_price), 0);
                            return Math.max(0, oldTotal - newTotal).toFixed(2);
                        })()
                    "></span></span>
                </div>
            </div>
        </div>
        <div
            class="px-6 py-4 border-t border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 flex justify-end gap-3">
            <button @click="showPurchaseReturnModal = false"
                class="px-4 py-2 border border-slate-300 dark:border-gray-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800 transition-all">Cancel</button>
            <button @click="submitPurchasePartialReturn()"
                class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-xl transition-all shadow-md">Process
                Return</button>
        </div>
    </div>
</div>
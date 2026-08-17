{{-- ALL MODALS — x-cloak prevents invisible overlay flash before Alpine init --}}

{{-- 1. INVOICE MODAL --}}
<div x-show="showInvoiceModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]" @click.outside="showInvoiceModal = false">
        <div
            class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900">
            <h3 class="font-bold text-slate-800 dark:text-white" x-text="t('sale_invoice')">Sale Invoice</h3>
            <button @click="showInvoiceModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <div id="print-area" class="flex-1 overflow-y-auto p-6 space-y-6 text-slate-800 dark:text-slate-200">
            <template x-if="selectedSale">
                <div>
                    <div class="rounded-lg p-3.5 flex items-start justify-between gap-4 transition-all mb-4 invoice-theme-header"
                         :class="getContrastColor(invoiceSettings ? invoiceSettings.theme_color : '#0F766E')"
                         :style="'background-color: ' + (invoiceSettings ? invoiceSettings.theme_color : '#0F766E')">
                        <!-- Left Side: Logo & Shop Name -->
                        <div class="flex items-start gap-2">
                            <div x-show="shop && shop.logo" class="w-9 h-9 rounded overflow-hidden bg-white/20 flex items-center justify-center shrink-0">
                                <img :src="shop && shop.logo ? '/storage/' + shop.logo : ''" class="w-full h-full object-cover">
                            </div>
                            <div class="flex flex-col items-start gap-0.5">
                                <span class="text-xs font-bold leading-tight" x-text="shop ? shop.name : 'DukanHisab'"></span>
                                <p x-show="shop && shop.mobile" class="text-[9px] opacity-90" x-text="(t('mobile') || 'Mobile') + ': ' + (shop ? (shop.mobile || '') : '')"></p>
                                <p x-show="shop && shop.address" class="text-[9px] opacity-90" x-text="shop ? shop.address : ''"></p>
                                <p x-show="shop && shop.gst_number" class="text-[9px] opacity-90" x-text="shop && shop.gst_number ? 'GSTIN: ' + shop.gst_number : ''"></p>
                            </div>
                        </div>
                        
                        <!-- Right Side: Invoice Meta -->
                        <div class="text-[9px] text-right space-y-0.5 leading-tight font-medium opacity-90 max-w-[60%]">
                            <h3 class="font-bold text-sm flex items-center justify-end gap-2">
                                <span x-text="t('invoice')">INVOICE</span>
                                <template x-if="selectedSale.status === 'Returned' || selectedSale.status === 'Partially Returned'">
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded-full border border-white/20"
                                        x-text="t(selectedSale.status.toLowerCase().replace(/ /g, '_')) || selectedSale.status"></span>
                                </template>
                            </h3>
                            <p class="font-bold"><span class="font-normal opacity-80" x-text="t('invoice_no') + ':'">Invoice No:</span> <span x-text="selectedSale.sale_number"></span></p>
                            <p class="opacity-90 mt-1"><span class="font-normal opacity-80" x-text="t('date') + ':'">Date:</span> <span x-text="new Date(selectedSale.sale_date).toLocaleString()"></span></p>
                            <template x-if="selectedSale.status === 'Completed' && selectedSale.payment_type === 'Credit' && selectedSale.updated_at">
                                <p class="opacity-90"><span class="font-normal opacity-80" x-text="t('paid_date') + ':'">Paid Date:</span> <span x-text="new Date(selectedSale.updated_at).toLocaleString()"></span></p>
                            </template>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 py-4 text-xs">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider" x-text="t('bill_to')">Bill To</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white"
                                x-text="selectedSale.customer ? selectedSale.customer.name : t('walk_in_customer')"></p>
                            <template x-if="(!invoiceSettings || invoiceSettings.show_customer_address) && selectedSale.customer && selectedSale.customer.mobile">
                                <p class="text-slate-500" x-text="(t('mobile') || 'Mobile') + ': ' + selectedSale.customer.mobile"></p>
                            </template>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider" x-text="t('payment_info')">Payment Info</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">
                                <span class="text-slate-500 font-normal" x-text="t('payment_status') + ':'">Payment Status:</span>
                                <span :class="selectedSale.status === 'Returned' ? 'text-rose-600 font-bold' : (selectedSale.status === 'Unpaid' ? 'text-amber-600 font-bold' : 'text-emerald-600 font-bold')" x-text="t(selectedSale.status ? selectedSale.status.toLowerCase().replace(/ /g, '_') : 'paid') || selectedSale.status"></span>
                            </p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">
                                <span class="text-slate-500 font-normal" x-text="t('payment_type') + ':'">Method:</span>
                                <span x-text="t(selectedSale.payment_type ? selectedSale.payment_type.toLowerCase() : '') || selectedSale.payment_type"></span>
                            </p>
                        </div>
                    </div>
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700 text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-700">
                                <th class="px-4 py-2 text-left font-bold text-slate-500" x-text="t('product')">Item</th>
                                <th class="px-4 py-2 text-right font-bold text-slate-500" x-text="t('price')">Price</th>
                                <th class="px-4 py-2 text-center font-bold text-slate-500" x-text="t('quantity')">Qty</th>
                                <template x-if="selectedSale.status === 'Returned' || selectedSale.status === 'Partially Returned'">
                                    <th class="px-4 py-2 text-center font-bold text-slate-500" x-text="t('returned')">Returned</th>
                                </template>
                                <template x-if="selectedSale.status === 'Returned' || selectedSale.status === 'Partially Returned'">
                                    <th class="px-4 py-2 text-center font-bold text-slate-500" x-text="t('net_qty')">Net Qty</th>
                                </template>
                                <th class="px-4 py-2 text-right font-bold text-slate-500" x-text="t('total')">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in selectedSale.items" :key="item.id">
                                <tr>
                                    <td class="px-4 py-2.5 font-semibold" x-text="item.product ? item.product.name : (t('deleted_product') || 'Deleted Product')"></td>
                                    <td class="px-4 py-2.5 text-right" x-text="'₹' + parseFloat(item.selling_price).toFixed(2)"></td>
                                    <td class="px-4 py-2.5 text-center" x-text="item.quantity"></td>
                                    <template x-if="selectedSale.status === 'Returned' || selectedSale.status === 'Partially Returned'">
                                        <td class="px-4 py-2.5 text-center text-rose-600 font-bold" 
                                            x-text="
                                                (() => {
                                                    if (item.returned_quantity > 0) return item.returned_quantity;
                                                    const hasReturnedQty = selectedSale.items.some(i => i.returned_quantity > 0);
                                                    if (!hasReturnedQty && selectedSale.status === 'Returned') return item.quantity;
                                                    return 0;
                                                })()
                                            "></td>
                                    </template>
                                    <template x-if="selectedSale.status === 'Returned' || selectedSale.status === 'Partially Returned'">
                                        <td class="px-4 py-2.5 text-center font-semibold text-slate-600 dark:text-slate-400" 
                                            x-text="
                                                (() => {
                                                    if (item.returned_quantity > 0) return item.quantity - item.returned_quantity;
                                                    const hasReturnedQty = selectedSale.items.some(i => i.returned_quantity > 0);
                                                    if (!hasReturnedQty && selectedSale.status === 'Returned') return 0;
                                                    return item.quantity;
                                                })()
                                            "></td>
                                    </template>
                                    <td class="px-4 py-2.5 text-right font-bold"
                                        x-text="'₹' + (item.selling_price * (
                                            (() => {
                                                if (item.returned_quantity > 0) return item.quantity - item.returned_quantity;
                                                const hasReturnedQty = selectedSale.items.some(i => i.returned_quantity > 0);
                                                if (!hasReturnedQty && selectedSale.status === 'Returned') return 0;
                                                return item.quantity;
                                            })()
                                        )).toFixed(2)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div class="border-t border-slate-200 dark:border-gray-700 pt-4 flex justify-between items-start gap-4 text-xs">
                        <!-- Left Side: UPI QR Code & Bank Details -->
                        <div class="flex flex-col items-start gap-2 max-w-[50%]">
                            <template x-if="invoiceSettings && invoiceSettings.show_upi_qr && shop && shop.upi_id">
                                <div class="flex flex-col items-center gap-1">
                                    <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent('upi://pay?pa=' + shop.upi_id + '&pn=' + (shop.name || 'Shop') + '&am=' + parseFloat(selectedSale.grand_total).toFixed(2) + '&cu=INR')" class="w-20 h-20 p-1 bg-white rounded border border-slate-200 shadow-sm">
                                    <span class="text-[8px] text-slate-400 font-semibold" x-text="shop.upi_id"></span>
                                </div>
                            </template>
                            <template x-if="invoiceSettings && invoiceSettings.show_bank_details && shop && shop.bank_details">
                                <p class="text-[8px] text-slate-500 whitespace-pre-line leading-tight border-t border-dashed border-slate-200 dark:border-gray-700 pt-1 mt-1 w-full" x-text="shop.bank_details"></p>
                            </template>
                        </div>
                        
                        <!-- Right Side: Totals -->
                        <div class="flex flex-col items-end gap-1">
                            <div class="flex justify-between w-48"><span class="text-slate-500" x-text="t('subtotal') + ':'">Subtotal:</span><span
                                    class="font-semibold">₹<span x-text="selectedSale.subtotal"></span></span></div>
                            <div class="flex justify-between w-48"><span class="text-slate-500" x-text="t('discount') + ':'">Discount:</span><span
                                    class="font-semibold">-₹<span x-text="selectedSale.discount"></span></span></div>
                            <div
                                class="flex justify-between w-48 text-sm font-bold border-t border-dashed border-slate-200 dark:border-gray-700 pt-2">
                                <span x-text="t('grand_total') + ':'">Grand Total:</span><span class="text-primary">₹<span
                                        x-text="selectedSale.grand_total"></span></span>
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-[10px] text-slate-400 mt-3" x-text="shop && shop.invoice_footer ? shop.invoice_footer : (t('invoice_footer_default') || 'Thank you for your business!')"></p>
                    <template x-if="shop && shop.signature">
                        <img :src="'/storage/' + shop.signature" class="h-10 ml-auto object-contain mt-2">
                    </template>
                </div>
            </template>
        </div>
        <div
            class="px-6 py-4 border-t border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 flex justify-between gap-3">
            <div class="flex gap-2">
                <button @click="if (user && user.active_plan && user.active_plan.slug !== 'free') printInvoice(); else showToast('Please upgrade your plan to print invoices.', 'error')"
                    :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg flex items-center gap-1">
                    Print
                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                    </span>
                </button>
                <button @click="if (user && user.active_plan && user.active_plan.slug !== 'free') downloadPDF(); else showToast('Please upgrade your plan to download PDFs.', 'error')"
                    :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-lg flex items-center gap-1">
                    Download PDF
                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                    </span>
                </button>
            </div>
            <div class="flex gap-2">
                <button @click="if (user && user.active_plan && user.active_plan.slug !== 'free') window.open(whatsappLink(), '_blank'); else showToast('Please upgrade your plan to share via WhatsApp.', 'error')"
                    :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg flex items-center gap-1">
                    Share WhatsApp
                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                    </span>
                </button>
                <button type="button" @click="if (user && user.active_plan && user.active_plan.slug !== 'free') sendSaleInvoiceEmail(); else showToast('Please upgrade your plan to share via Email.', 'error')" :disabled="sendingSaleEmail"
                    :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white text-xs font-bold rounded-lg flex items-center gap-1">
                    <span x-show="!sendingSaleEmail">Share Email</span>
                    <span x-show="sendingSaleEmail">Sending...</span>
                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 2. ADD CUSTOMER MODAL --}}
<div x-show="showCustomerModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="showCustomerModal = false">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 dark:text-white"
                x-text="newCustomer.id ? t('edit') : t('add_customer')"></h3>
            <button @click="showCustomerModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <form @submit.prevent="saveCustomer()" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('customer_name')">Customer Name</label>
                <input type="text" required x-model="newCustomer.name" :placeholder="t('enter_customer_name') || 'Enter Customer Name'"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('mobile')">Mobile Number</label>
                <input type="text" x-model="newCustomer.mobile" maxlength="10"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                    :placeholder="t('enter_mobile') || 'Enter 10-digit mobile'"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('email')">Email Address</label>
                <input type="email" x-model="newCustomer.email" :placeholder="t('enter_email') || 'Enter Email Address'"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all"
                x-text="newCustomer.id ? t('save') : t('add_customer')"></button>
        </form>
    </div>
</div>

{{-- 3. ADD/EDIT PRODUCT MODAL --}}
<div x-show="showProductModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="showProductModal = false">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 dark:text-white"
                x-text="newProduct.id ? t('edit') : t('add_product')"></h3>
            <button @click="showProductModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <form @submit.prevent="saveProduct()" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('product_name')">Product Name</label>
                <input type="text" required x-model="newProduct.name" :placeholder="t('enter_product_name') || 'Enter Product Name'"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('selling_price')">Selling Price</label>
                    <input type="number" step="0.01" required x-model.number="newProduct.selling_price"
                        :placeholder="t('enter_selling_price') || 'Enter Selling Price'"
                        class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('purchase_price')">Purchase Price</label>
                    <input type="number" step="0.01" x-model.number="newProduct.purchase_price"
                        :placeholder="t('enter_purchase_price') || 'Enter Purchase Price'"
                        class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1" x-text="t('barcode')">Barcode</label>
                <input type="text" x-model="newProduct.barcode" :placeholder="t('enter_barcode') || 'Enter Barcode'" class="block w-full px-3 py-2 border border-slate-300
                     dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1"
                        x-text="newProduct.id ? t('stock') : t('initial_stock')"></label>
                    <input type="number" required x-model.number="newProduct.stock" :placeholder="t('enter_stock') || 'Enter Stock'"
                        class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('low_stock')">Alert Limit</label>
                    <input type="number" required x-model.number="newProduct.low_stock_threshold"
                        :placeholder="t('enter_alert_limit') || 'Enter Alert Limit'"
                        class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all"
                x-text="newProduct.id ? t('save') : t('add_product')"></button>
        </form>
    </div>
</div>

{{-- 4. ADD EXPENSE MODAL --}}
<div x-show="showExpenseModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="showExpenseModal = false">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 dark:text-white" x-text="t('add_expense')">Add Expense</h3>
            <button @click="showExpenseModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <form @submit.prevent="saveExpense()" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('expense_description')">Expense Description</label>
                <input type="text" required :placeholder="t('expense_desc_placeholder') || 'Electricity, Tea, Rent...'" x-model="newExpense.description"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('amount_rs')">Amount (₹)</label>
                <input type="number" step="0.01" required x-model.number="newExpense.amount"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('payment_type')">Payment Method</label>
                <select x-model="newExpense.payment_method"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                    <option value="cash" x-text="t('cash')">Cash</option>
                    <option value="upi" x-text="t('upi') + ' / ' + (t('wallet') || 'Wallet')">UPI / Wallet</option>
                    <option value="bank" x-text="t('bank')">Bank Transfer</option>
                </select>
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all" x-text="t('save_expense')">Save
                Expense</button>
        </form>
    </div>
</div>

{{-- 5. ADD SUPPLIER MODAL --}}
<div x-show="showSupplierModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="showSupplierModal = false">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 dark:text-white"
                x-text="newSupplier.id ? t('edit') : t('add_supplier')"></h3>
            <button @click="showSupplierModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <form @submit.prevent="saveSupplier()" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('supplier_name')">Supplier Name</label>
                <input type="text" required x-model="newSupplier.name" :placeholder="t('enter_supplier_name') || 'Enter Supplier Name'"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('mobile')">Mobile Number</label>
                <input type="text" x-model="newSupplier.mobile" maxlength="10"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                    :placeholder="t('enter_supplier_mobile') || 'Enter Supplier Mobile'"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('email')">Email Address</label>
                <input type="email" x-model="newSupplier.email" :placeholder="t('enter_supplier_email') || 'Enter Supplier Email'"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <button type="submit"
                class="w-full py-2.5 bg-primary hover:bg-primary-hover text-white text-sm font-semibold rounded-xl shadow-md transition-all"
                x-text="newSupplier.id ? t('save') : t('add_supplier')"></button>
        </form>
    </div>
</div>

{{-- 8. PURCHASE DETAILS MODAL --}}
<div x-show="showPurchaseDetailsModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]" @click.outside="showPurchaseDetailsModal = false">
        <div
            class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900">
            <h3 class="font-bold text-slate-800 dark:text-white" x-text="t('purchase_details')">Purchase Details</h3>
            <button @click="showPurchaseDetailsModal = false" class="text-slate-400 hover:text-slate-600"><svg
                    class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <div id="purchase-print-area" class="flex-1 overflow-y-auto p-6 space-y-6 text-slate-800 dark:text-slate-200">
            <template x-if="selectedPurchase">
                <div>
                    <div class="rounded-lg p-3.5 flex items-start justify-between gap-4 transition-all mb-4 invoice-theme-header"
                         :class="getContrastColor(invoiceSettings ? invoiceSettings.theme_color : '#0F766E')"
                         :style="'background-color: ' + (invoiceSettings ? invoiceSettings.theme_color : '#0F766E')">
                        <!-- Left Side: Logo & Shop Name -->
                        <div class="flex items-start gap-2">
                            <div x-show="shop && shop.logo" class="w-9 h-9 rounded overflow-hidden bg-white/20 flex items-center justify-center shrink-0">
                                <img :src="shop && shop.logo ? '/storage/' + shop.logo : ''" class="w-full h-full object-cover">
                            </div>
                            <div class="flex flex-col items-start gap-0.5">
                                <span class="text-xs font-bold leading-tight" x-text="shop ? shop.name : 'DukanHisab'"></span>
                                <p x-show="shop && shop.mobile" class="text-[9px] opacity-90" x-text="(t('mobile') || 'Mobile') + ': ' + (shop ? (shop.mobile || '') : '')"></p>
                                <p x-show="shop && shop.address" class="text-[9px] opacity-90" x-text="shop ? shop.address : ''"></p>
                                <p x-show="shop && shop.gst_number" class="text-[9px] opacity-90" x-text="shop && shop.gst_number ? 'GSTIN: ' + shop.gst_number : ''"></p>
                            </div>
                        </div>

                        <!-- Right Side: Invoice Meta -->
                        <div class="text-[9px] text-right space-y-0.5 leading-tight font-medium opacity-90 max-w-[60%]">
                            <h3 class="font-bold text-sm flex items-center justify-end gap-2">
                                <span x-text="t('purchase_invoice')">PURCHASE INVOICE</span>
                                <template x-if="selectedPurchase.status === 'Returned' || selectedPurchase.status === 'Partially Returned'">
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded-full border border-white/20"
                                        x-text="t(selectedPurchase.status.toLowerCase().replace(/ /g, '_')) || selectedPurchase.status"></span>
                                </template>
                            </h3>
                            <p class="font-bold"><span class="font-normal opacity-80" x-text="t('invoice_no') + ':'">Invoice No:</span> <span x-text="selectedPurchase.purchase_number"></span></p>
                            <p class="opacity-90 mt-1"><span class="font-normal opacity-80" x-text="t('date') + ':'">Date:</span> <span x-text="new Date(selectedPurchase.purchase_date).toLocaleString()"></span></p>
                            <template x-if="selectedPurchase.status === 'Completed' && selectedPurchase.payment_type === 'Credit' && selectedPurchase.updated_at">
                                <p class="opacity-90"><span class="font-normal opacity-80" x-text="t('paid_date') + ':'">Paid Date:</span> <span x-text="new Date(selectedPurchase.updated_at).toLocaleString()"></span></p>
                            </template>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 py-4 text-xs">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider" x-text="t('supplier_details')">Supplier Details
                            </p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white"
                                x-text="selectedPurchase.supplier ? selectedPurchase.supplier.name : t('walk_in_supplier')">
                            </p>
                            <p class="text-slate-500"
                                x-text="selectedPurchase.supplier && selectedPurchase.supplier.mobile ? (t('mobile') || 'Mobile') + ': ' + selectedPurchase.supplier.mobile : ''">
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider" x-text="t('payment_info')">Payment Info</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">
                                <span class="text-slate-500 font-normal" x-text="t('payment_status') + ':'">Payment Status:</span>
                                <span :class="selectedPurchase.status === 'Returned' ? 'text-rose-600 font-bold' : (selectedPurchase.status === 'Unpaid' ? 'text-amber-600 font-bold' : 'text-emerald-600 font-bold')" x-text="t(selectedPurchase.status ? selectedPurchase.status.toLowerCase().replace(/ /g, '_') : 'paid') || selectedPurchase.status"></span>
                            </p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">
                                <span class="text-slate-500 font-normal" x-text="t('payment_type') + ':'">Method:</span>
                                <span x-text="t(selectedPurchase.payment_type ? selectedPurchase.payment_type.toLowerCase() : '') || selectedPurchase.payment_type"></span>
                            </p>
                        </div>
                    </div>
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700 text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-700">
                                <th class="px-4 py-2 text-left font-bold text-slate-500" x-text="t('product')">Product</th>
                                <th class="px-4 py-2 text-right font-bold text-slate-500" x-text="t('price')">Unit Price</th>
                                <th class="px-4 py-2 text-center font-bold text-slate-500" x-text="t('quantity')">Qty</th>
                                <template x-if="selectedPurchase.status === 'Returned' || selectedPurchase.status === 'Partially Returned'">
                                    <th class="px-4 py-2 text-center font-bold text-slate-500" x-text="t('returned')">Returned</th>
                                </template>
                                <template x-if="selectedPurchase.status === 'Returned' || selectedPurchase.status === 'Partially Returned'">
                                    <th class="px-4 py-2 text-center font-bold text-slate-500" x-text="t('net_qty')">Net Qty</th>
                                </template>
                                <th class="px-4 py-2 text-right font-bold text-slate-500" x-text="t('total')">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in selectedPurchase.items" :key="item.id">
                                <tr>
                                    <td class="px-4 py-2.5 font-semibold"
                                        x-text="item.product ? item.product.name : (t('deleted_product') || 'Deleted Product')"></td>
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
                    <div class="border-t border-slate-200 dark:border-gray-700 pt-4 flex justify-between items-start gap-4 text-xs">
                        <!-- Left Side: UPI QR Code & Bank Details -->
                        <div class="flex flex-col items-start gap-2 max-w-[50%]">
                            <template x-if="invoiceSettings && invoiceSettings.show_upi_qr && shop && shop.upi_id">
                                <div class="flex flex-col items-center gap-1">
                                    <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent('upi://pay?pa=' + shop.upi_id + '&pn=' + (shop.name || 'Shop') + '&am=' + parseFloat(selectedPurchase.total_amount).toFixed(2) + '&cu=INR')" class="w-20 h-20 p-1 bg-white rounded border border-slate-200 shadow-sm">
                                    <span class="text-[8px] text-slate-400 font-semibold" x-text="shop.upi_id"></span>
                                </div>
                            </template>
                            <template x-if="invoiceSettings && invoiceSettings.show_bank_details && shop && shop.bank_details">
                                <p class="text-[8px] text-slate-500 whitespace-pre-line leading-tight border-t border-dashed border-slate-200 dark:border-gray-700 pt-1 mt-1 w-full" x-text="shop.bank_details"></p>
                            </template>
                        </div>

                        <!-- Right Side: Totals -->
                        <div class="flex flex-col items-end gap-1">
                            <div
                                class="flex justify-between w-48 text-sm font-bold border-t border-dashed border-slate-200 dark:border-gray-700 pt-2">
                                <span x-text="t('total') + ':'">Total Amount:</span><span class="text-primary font-extrabold">₹<span
                                        x-text="parseFloat(selectedPurchase.total_amount).toFixed(2)"></span></span>
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-[10px] text-slate-400 mt-3" x-text="shop && shop.invoice_footer ? shop.invoice_footer : (t('invoice_footer_default') || 'Thank you for your business!')"></p>
                    <template x-if="shop && shop.signature">
                        <img :src="'/storage/' + shop.signature" class="h-10 ml-auto object-contain mt-2">
                    </template>
                </div>
            </template>
        </div>
        <div
            class="px-6 py-4 border-t border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 flex justify-between gap-3">
            <div class="flex gap-2">
                <button @click="if (user && user.active_plan && user.active_plan.slug !== 'free') printPurchase(); else showToast('Please upgrade your plan to print invoices.', 'error')"
                    :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg flex items-center gap-1">
                    Print
                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                    </span>
                </button>
                <button @click="if (user && user.active_plan && user.active_plan.slug !== 'free') downloadPurchasePDF(); else showToast('Please upgrade your plan to download PDFs.', 'error')"
                    :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-lg flex items-center gap-1">
                    Download PDF
                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                    </span>
                </button>
            </div>
            <div class="flex gap-2">
                <button @click="if (user && user.active_plan && user.active_plan.slug !== 'free') window.open(whatsappPurchaseLink(), '_blank'); else showToast('Please upgrade your plan to share via WhatsApp.', 'error')"
                    :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg flex items-center gap-1">
                    Share WhatsApp
                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                    </span>
                </button>
                <button type="button" @click="if (user && user.active_plan && user.active_plan.slug !== 'free') sendPurchaseInvoiceEmail(); else showToast('Please upgrade your plan to share via Email.', 'error')" :disabled="sendingPurchaseEmail"
                    :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white text-xs font-bold rounded-lg flex items-center gap-1">
                    <span x-show="!sendingPurchaseEmail">Share Email</span>
                    <span x-show="sendingPurchaseEmail">Sending...</span>
                    <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="text-amber-500">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path></svg>
                    </span>
                </button>
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
            <h3 class="font-bold text-lg text-slate-800 dark:text-white" x-text="t(confirmModal.title) || confirmModal.title"></h3>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400" x-text="t(confirmModal.message) || confirmModal.message"></p>
        <div class="flex justify-end gap-3 pt-2">
            <button @click="confirmModal.show = false"
                class="px-4 py-2 border border-slate-300 dark:border-gray-600 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition-all" x-text="t('cancel')">Cancel</button>
            <button @click="triggerConfirm()"
                class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-xl transition-all" x-text="t('confirm')">Confirm</button>
        </div>
    </div>
</div>

{{-- 8. RETURN ITEMS MODAL --}}
<div x-show="showReturnModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden" @click.outside="showReturnModal = false">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white" x-text="t('return_items')">Return Items</h3>
                <p class="text-xs text-slate-400 mt-0.5" x-text="t('sale_number') + ': ' + returnForm.sale_number"></p>
            </div>
            <button @click="showReturnModal = false" class="text-slate-400 hover:text-slate-600">
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
                        <th class="py-2 font-semibold text-slate-400" x-text="t('product')">Product</th>
                        <th class="py-2 font-semibold text-slate-400 text-center" x-text="t('price')">Price</th>
                        <th class="py-2 font-semibold text-slate-400 text-center" x-text="t('purchased')">Purchased</th>
                        <th class="py-2 font-semibold text-slate-400 text-right w-24" x-text="t('return_qty')">Return Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in returnForm.items" :key="item.product_id">
                        <tr class="border-b border-slate-50 dark:border-gray-700/50">
                            <td class="py-3 font-semibold text-slate-700 dark:text-white" x-text="item.name">
                            </td>
                            <td class="py-3 text-slate-500 text-center">₹<span
                                    x-text="parseFloat(item.selling_price).toFixed(2)"></span></td>
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
                    <span class="text-slate-500" x-text="t('refund_method') + ':'">Refund Method:</span>
                    <span class="font-semibold text-slate-800 dark:text-white"
                        x-text="returnForm.payment_type === 'Credit' ? t('reduce_customer_due') : (t('refund') + ' (' + (t(returnForm.payment_type ? returnForm.payment_type.toLowerCase() : '') || returnForm.payment_type) + ')')"></span>
                </div>
                <div class="flex justify-between text-sm border-t border-slate-200/50 dark:border-gray-700/50 pt-2">
                    <span class="font-bold text-slate-700 dark:text-white" x-text="t('estimated_refund') + ':'">Estimated Refund:</span>
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
                class="px-4 py-2 border border-slate-300 dark:border-gray-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800 transition-all" x-text="t('cancel')">Cancel</button>
            <button @click="submitPartialReturn()"
                class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-xl transition-all shadow-md" x-text="t('process_return')">Process
                Return</button>
        </div>
    </div>
</div>

{{-- 12. EDIT SALE ITEMS MODAL --}}
<div x-show="showEditSaleItemsModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-5xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900 shrink-0">
            <h3 class="font-bold text-slate-800 dark:text-white" x-text="'Edit Sale #' + (editingSaleId ? (selectedSale && selectedSale.id === editingSaleId ? selectedSale.sale_number : '') : '')"></h3>
            <button @click="showEditSaleItemsModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <div class="flex-1 overflow-y-auto p-4">
            <div class="flex flex-col lg:flex-row gap-4">
                {{-- Product Selection Side --}}
                <div class="flex-1 flex flex-col bg-slate-50/50 dark:bg-gray-900/20 rounded-2xl border border-slate-200 dark:border-gray-700 p-4 overflow-hidden min-h-[400px]">
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
                            <input type="text" placeholder="Scan barcode..." x-model="pos.barcodeInput"
                                @keydown.enter.prevent="handleBarcodeScan()"
                                class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-4V8m0 8l-4-4m4 4l4-4"></path></svg>
                            </span>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto grid grid-cols-2 md:grid-cols-3 gap-3 pr-1 max-h-[420px] content-start">
                        <template x-for="prod in filteredProducts()" :key="prod.id">
                            <div @click="prod.stock > 0 ? addToBill(prod) : showToast('Product is Out of Stock!', 'error')"
                                :class="prod.stock <= 0 ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-gray-800' : 'bg-white dark:bg-gray-700/50 hover:bg-primary/5 cursor-pointer hover:border-primary'"
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
                <div class="w-full lg:w-96 flex flex-col bg-slate-50/50 dark:bg-gray-900/20 rounded-2xl border border-slate-200 dark:border-gray-700 p-4 overflow-hidden shrink-0">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3 flex items-center justify-between">
                        Billing Cart
                        <button @click="pos.items = []" class="text-xs text-rose-500 hover:underline">Clear All</button>
                    </h3>

                    {{-- Customer Selector --}}
                    <div class="flex items-center gap-2 mb-4 relative" x-data="{ open: false }" @click.away="open = false">
                        <div class="flex-1 relative">
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white bg-white text-left focus:outline-none">
                                <span class="truncate pr-2" x-text="getSelectedPosCustomerName()"></span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" x-cloak
                                class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto p-2 space-y-2">
                                <input type="text" placeholder="Search customer name or mobile..."
                                    x-model="posCustomerSearchQuery"
                                    @input.debounce.300ms="searchPosCustomers()"
                                    @click.stop
                                    class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg text-xs dark:bg-gray-900 dark:text-white bg-slate-50 focus:outline-none focus:border-primary">

                                <div class="space-y-1">
                                    <button type="button" @click="selectPosCustomer(null); open = false;"
                                        class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-slate-50 dark:hover:bg-gray-700/50 font-medium text-slate-500 dark:text-slate-400"
                                        x-text="t('walk_in_customer')">
                                        Walk-In Customer
                                    </button>

                                    <template x-for="cust in posFilteredCustomers" :key="cust.id">
                                        <button type="button" @click="selectPosCustomer(cust); open = false;"
                                            class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary transition-all font-medium text-slate-700 dark:text-slate-300 flex justify-between items-center">
                                            <span x-text="cust.name"></span>
                                            <span class="text-[10px] text-slate-400 font-mono" x-text="cust.mobile || 'No Mobile'"></span>
                                        </button>
                                    </template>

                                    <template x-if="posFilteredCustomers.length === 0">
                                        <div class="text-center py-4 text-xs text-slate-400" x-text="t('no_customers_found')">No customers found.</div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <button @click="showCustomerModal = true" class="p-2 border border-slate-300 dark:border-gray-600 hover:border-primary rounded-xl text-slate-500 hover:text-primary transition-all shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>

                    {{-- Cart Items (delete button top-right on each card) --}}
                    <div class="flex-1 overflow-y-auto space-y-2 pr-1 mb-4 max-h-[280px]">
                        <template x-for="(item, idx) in pos.items" :key="idx">
                            <div class="p-2 bg-white dark:bg-gray-700/50 rounded-xl border border-slate-100 dark:border-gray-700 flex flex-col gap-1.5">
                                <div class="flex justify-between items-start">
                                    <span class="text-xs font-bold text-slate-800 dark:text-white truncate max-w-[180px]" x-text="item.name"></span>
                                    <button @click="removeFromBill(idx)" title="Remove item" class="text-slate-400 hover:text-rose-500">
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
                            <div class="text-center py-10 text-slate-400 text-sm" x-text="t('cart_empty')">Cart is empty. Click products or scan barcodes to begin.</div>
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
                                <button @click="pos.paymentType = 'Cash'" :class="pos.paymentType === 'Cash' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Cash</button>
                                <button @click="pos.paymentType = 'UPI'"  :class="pos.paymentType === 'UPI'  ? 'bg-primary text-white' : 'bg-white dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">UPI</button>
                                <button @click="pos.paymentType = 'Bank'" :class="pos.paymentType === 'Bank' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Bank</button>
                                <button @click="pos.paymentType = 'Credit'" :class="pos.paymentType === 'Credit' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Credit</button>
                            </div>
                        </div>

                        <button @click="saveEditedSale()" :disabled="pos.items.length === 0"
                            class="w-full mt-3 py-3 bg-primary hover:bg-primary-hover text-white text-sm font-bold rounded-xl shadow-md transition-all disabled:opacity-50">
                            Save & Print Bill
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 13. EDIT PURCHASE ITEMS MODAL --}}
<div x-show="showEditPurchaseItemsModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-5xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900 shrink-0">
            <h3 class="font-bold text-slate-800 dark:text-white" x-text="'Edit Purchase #' + (editingPurchaseId ? (selectedPurchase && selectedPurchase.id === editingPurchaseId ? selectedPurchase.purchase_number : '') : '')"></h3>
            <button @click="showEditPurchaseItemsModal = false" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg></button>
        </div>
        <div class="flex-1 overflow-y-auto p-4">
            <div class="flex flex-col lg:flex-row gap-4">
                {{-- Product Selection Side --}}
                <div class="flex-1 flex flex-col bg-slate-50/50 dark:bg-gray-900/20 rounded-2xl border border-slate-200 dark:border-gray-700 p-4 overflow-hidden min-h-[400px]">
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
                            <input type="text" placeholder="Scan barcode..." x-model="pos.barcodeInput"
                                @keydown.enter.prevent="handlePurchaseBarcodeScan()"
                                class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-4V8m0 8l-4-4m4 4l4-4"></path></svg>
                            </span>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto grid grid-cols-2 md:grid-cols-3 gap-3 pr-1 max-h-[420px] content-start">
                        <template x-for="prod in filteredProducts()" :key="prod.id">
                            <div @click="addPurchaseItemById(prod.id)"
                                class="p-3 border border-slate-200 dark:border-gray-700 rounded-xl transition-all flex flex-col justify-between bg-white dark:bg-gray-700/50 hover:bg-primary/5 cursor-pointer hover:border-primary">
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
                <div class="w-full lg:w-96 flex flex-col bg-slate-50/50 dark:bg-gray-900/20 rounded-2xl border border-slate-200 dark:border-gray-700 p-4 overflow-hidden shrink-0">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3 flex items-center justify-between">
                        Purchase Cart
                        <button @click="newPurchase.items = []" class="text-xs text-rose-500 hover:underline">Clear All</button>
                    </h3>

                    {{-- Supplier Selector --}}
                    <div class="flex items-center gap-2 mb-4 relative" x-data="{ open: false }" @click.away="open = false">
                        <div class="flex-1 relative">
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white bg-white text-left focus:outline-none">
                                <span class="truncate pr-2" x-text="getSelectedPurchaseSupplierName()"></span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" x-cloak
                                class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto p-2 space-y-2">
                                <input type="text" placeholder="Search supplier..."
                                    x-model="purchaseSupplierSearchQuery"
                                    @input.debounce.300ms="searchPurchaseSuppliers()"
                                    @click.stop
                                    class="block w-full px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg text-xs dark:bg-gray-900 dark:text-white bg-slate-50 focus:outline-none focus:border-primary">

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
                        <button @click="showSupplierModal = true" class="p-2 border border-slate-300 dark:border-gray-600 hover:border-primary rounded-xl text-slate-500 hover:text-primary transition-all shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>

                    {{-- Cart Items (delete button top-right on each card) --}}
                    <div class="flex-1 overflow-y-auto space-y-2 pr-1 mb-4 max-h-[280px]">
                        <template x-for="(item, idx) in newPurchase.items" :key="idx">
                            <div class="p-2 bg-white dark:bg-gray-700/50 rounded-xl border border-slate-100 dark:border-gray-700 flex flex-col gap-1.5">
                                <div class="flex justify-between items-start">
                                    <span class="text-xs font-bold text-slate-800 dark:text-white truncate max-w-[180px]" x-text="item.name"></span>
                                    <button @click="newPurchase.items.splice(idx, 1)" title="Remove item" class="text-slate-400 hover:text-rose-500">
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
                            <div class="text-center py-10 text-slate-400 text-sm">Cart is empty. Click products above to add.</div>
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
                                <button @click="newPurchase.payment_type = 'Cash'" :class="newPurchase.payment_type === 'Cash' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Cash</button>
                                <button @click="newPurchase.payment_type = 'UPI'"  :class="newPurchase.payment_type === 'UPI'  ? 'bg-primary text-white' : 'bg-white dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">UPI</button>
                                <button @click="newPurchase.payment_type = 'Bank'" :class="newPurchase.payment_type === 'Bank' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Bank</button>
                                <button @click="newPurchase.payment_type = 'Credit'" :class="newPurchase.payment_type === 'Credit' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-700 text-slate-600 dark:text-slate-300'" class="py-2 text-center text-xs font-bold rounded-lg transition-all">Credit</button>
                            </div>
                        </div>

                        <button @click="saveEditedPurchase()" :disabled="newPurchase.items.length === 0"
                            class="w-full mt-3 py-3 bg-primary hover:bg-primary-hover text-white text-sm font-bold rounded-xl shadow-md transition-all disabled:opacity-50">
                            Save & Purchase
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 13. PURCHASE RETURN ITEMS MODAL --}}
<div x-show="showPurchaseReturnModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden" @click.outside="showPurchaseReturnModal = false">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white" x-text="t('return_purchase_items')">Return Purchase Items</h3>
                <p class="text-xs text-slate-400 mt-0.5" x-text="t('purchase_number') + ': ' + purchaseReturnForm.purchase_number"></p>
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
                        <th class="py-2 font-semibold text-slate-400" x-text="t('product')">Product</th>
                        <th class="py-2 font-semibold text-slate-400 text-center" x-text="t('price')">Price</th>
                        <th class="py-2 font-semibold text-slate-400 text-center" x-text="t('purchased')">Purchased</th>
                        <th class="py-2 font-semibold text-slate-400 text-right w-24" x-text="t('return_qty')">Return Qty</th>
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
                    <span class="text-slate-500" x-text="t('refund_method') + ':'">Refund Method:</span>
                    <span class="font-semibold text-slate-800 dark:text-white"
                        x-text="purchaseReturnForm.payment_type === 'Credit' ? t('reduce_supplier_due') : (t('refund') + ' (' + (t(purchaseReturnForm.payment_type ? purchaseReturnForm.payment_type.toLowerCase() : '') || purchaseReturnForm.payment_type) + ')')"></span>
                </div>
                <div class="flex justify-between text-sm border-t border-slate-200/50 dark:border-gray-700/50 pt-2">
                    <span class="font-bold text-slate-700 dark:text-white" x-text="t('estimated_refund') + ':'">Estimated Refund:</span>
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
                class="px-4 py-2 border border-slate-300 dark:border-gray-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800 transition-all" x-text="t('cancel')">Cancel</button>
            <button @click="submitPurchasePartialReturn()"
                class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-xl transition-all shadow-md" x-text="t('process_return')">Process
                Return</button>
        </div>
    </div>
</div>

{{-- 14. COLLECT CUSTOMER PAYMENT MODAL --}}
<div x-show="collectCustomerModalOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="collectCustomerModalOpen = false">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white" x-text="t('collect_payment')">Collect Customer Payment</h3>
            </div>
            <button @click="collectCustomerModalOpen = false" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <form @submit.prevent="submitCollectCustomerPayment()" class="p-6 space-y-4">
            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/40 rounded-xl p-3.5 flex justify-between items-center">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400" x-text="t('customer_name')">Customer Name</span>
                    <h4 class="font-bold text-slate-800 dark:text-white text-sm" x-text="collectCustomerForm.customer_name"></h4>
                </div>
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400" x-text="t('due_balance')">Outstanding Due</span>
                    <p class="font-black text-rose-600 text-base">₹<span x-text="collectCustomerForm.current_due.toFixed(2)"></span></p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1" x-text="t('amount')">Amount Received (₹)</label>
                <input type="number" step="0.01" min="0.01" :max="collectCustomerForm.current_due" x-model.number="collectCustomerForm.amount" required
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-slate-800 dark:text-white font-bold text-lg focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1" x-text="t('payment_type')">Payment Method</label>
                <select x-model="collectCustomerForm.payment_method" required
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
                    <option value="Cash" x-text="t('cash')">Cash</option>
                    <option value="Bank" x-text="t('bank')">Bank Transfer</option>
                    <option value="UPI" x-text="t('upi') + ' / GPay / PhonePe'">UPI / GPay / PhonePe</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1" x-text="t('notes')">Remarks / Note</label>
                <input type="text" x-model="collectCustomerForm.note" :placeholder="t('notes') || 'e.g. Received partial payment via Google Pay'"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-emerald-500">
            </div>

            <div class="pt-3 border-t border-slate-200 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" @click="collectCustomerModalOpen = false"
                    class="px-4 py-2 border border-slate-300 dark:border-gray-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800 transition-all" x-text="t('cancel')">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-md" x-text="t('collect_payment')">
                    Collect Payment
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 15. PAY SUPPLIER DUE MODAL --}}
<div x-show="paySupplierModalOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="paySupplierModalOpen = false">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white" x-text="t('pay_supplier')">Pay Supplier Due</h3>
            </div>
            <button @click="paySupplierModalOpen = false" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <form @submit.prevent="submitPaySupplierDue()" class="p-6 space-y-4">
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 rounded-xl p-3.5 flex justify-between items-center">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400" x-text="t('supplier_name')">Supplier Name</span>
                    <h4 class="font-bold text-slate-800 dark:text-white text-sm" x-text="paySupplierForm.supplier_name"></h4>
                </div>
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400" x-text="t('due_balance')">Total Owed</span>
                    <p class="font-black text-rose-600 text-base">₹<span x-text="paySupplierForm.current_due.toFixed(2)"></span></p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1" x-text="t('amount')">Amount Paid (₹)</label>
                <input type="number" step="0.01" min="0.01" :max="paySupplierForm.current_due" x-model.number="paySupplierForm.amount" required
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-slate-800 dark:text-white font-bold text-lg focus:outline-none focus:border-amber-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1" x-text="t('payment_type')">Payment Method</label>
                <select x-model="paySupplierForm.payment_method" required
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-amber-500">
                    <option value="Cash" x-text="t('cash')">Cash</option>
                    <option value="Bank" x-text="t('bank')">Bank Transfer</option>
                    <option value="UPI" x-text="t('upi') + ' / GPay / PhonePe'">UPI / GPay / PhonePe</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1" x-text="t('notes')">Remarks / Note</label>
                <input type="text" x-model="paySupplierForm.note" :placeholder="t('notes') || 'e.g. Paid balance via Net Banking'"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-amber-500">
            </div>

            <div class="pt-3 border-t border-slate-200 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" @click="paySupplierModalOpen = false"
                    class="px-4 py-2 border border-slate-300 dark:border-gray-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800 transition-all" x-text="t('cancel')">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition-all shadow-md" x-text="t('pay_supplier')">
                    Pay Supplier
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ADD NEW SHOP MODAL --}}
<div x-show="addShopModal.show" x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-md overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-900/40">
            <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">Add New Shop</h3>
            <button @click="addShopModal.show = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form @submit.prevent="handleAddShopSubmit()" class="p-6 space-y-4 overflow-y-auto">
            {{-- Logo Upload --}}
            <div class="flex flex-col items-center gap-3">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Shop Logo</label>
                <div class="relative w-24 h-24 rounded-full border-2 border-dashed border-slate-300 dark:border-gray-600 flex items-center justify-center overflow-hidden bg-slate-50 dark:bg-gray-700">
                    <template x-if="addShopModal.logoPreview">
                        <img :src="addShopModal.logoPreview" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!addShopModal.logoPreview">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </template>
                    <input type="file" accept="image/*" @change="onAddShopLogoChange($event)" class="absolute inset-0 opacity-0 cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Shop Name</label>
                <input type="text" required placeholder="e.g. My Premium Store" x-model="addShopModal.name"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Owner Name</label>
                <input type="text" required placeholder="Owner Name" x-model="addShopModal.owner_name"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Mobile Number</label>
                <input type="text" required placeholder="Mobile" x-model="addShopModal.mobile" maxlength="10" 
                    x-on:input="addShopModal.mobile = addShopModal.mobile.replace(/\D/g, '').slice(0, 10)"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">GST Number (Optional)</label>
                <input type="text" placeholder="22AAAAA0000A1Z5" x-model="addShopModal.gst_number"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-primary">
            </div>

            <div class="pt-3 border-t border-slate-200 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" @click="addShopModal.show = false"
                    class="px-4 py-2 border border-slate-300 dark:border-gray-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800 transition-all">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl transition-all shadow-md">
                    Create Shop
                </button>
            </div>
        </form>
    </div>
</div>
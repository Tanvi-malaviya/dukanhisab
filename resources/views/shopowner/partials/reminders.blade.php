{{-- REMINDERS PANEL --}}
<div x-show="page === 'reminders'" class="space-y-4" @reminders-open.window="loadWhatsAppSettingsSummary()" @whatsapp-settings-updated.window="loadWhatsAppSettingsSummary()" x-init="$watch('page', p => { if (p === 'reminders') loadWhatsAppSettingsSummary(); })">
    {{-- WhatsApp Reminders Automation Status Banner --}}
    <div class="rounded-2xl border p-4 shadow-sm transition-all"
        :class="waDueReminderActive() 
            ? 'bg-gradient-to-r from-teal-500/10 via-emerald-500/5 to-transparent border-teal-200 dark:border-teal-800/60 dark:bg-gray-800' 
            : 'bg-slate-50 dark:bg-gray-800 border-slate-200 dark:border-gray-700'">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-xs"
                    :class="waDueReminderActive() ? 'bg-teal-600 text-white' : 'bg-slate-200 dark:bg-gray-700 text-slate-500 dark:text-slate-400'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-1.5">
                            <span x-show="waDueReminderActive()">
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-1"></span>
                                <span x-text="t('wa_auto_reminders_active') || 'Automatic WhatsApp Reminders: Active'"></span>
                            </span>
                            <span x-show="!waDueReminderActive()">
                                <span class="inline-block w-2 h-2 rounded-full bg-slate-400 mr-1"></span>
                                <span x-text="t('wa_auto_reminders_off') || 'Automatic WhatsApp Reminders: Off (Paused)'"></span>
                            </span>
                        </h4>
                        <span x-show="waCreditsBalance() !== null" 
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="waCreditsBalance() > 5 ? 'bg-teal-100 dark:bg-teal-900/40 text-teal-800 dark:text-teal-300' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300'">
                            <span x-text="(waCreditsBalance() || 0) + ' ' + (t('wa_credits_left') || 'credits left')"></span>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        <span x-show="waDueReminderActive()">
                            <span x-text="t('wa_auto_reminders_active_desc') || 'Scheduled reminders are sent automatically to customers in the background.'"></span>
                            <template x-if="waDueReminderScheduleText()">
                                <strong class="text-slate-700 dark:text-slate-200" x-text="' (' + waDueReminderScheduleText() + ')'"></strong>
                            </template>
                        </span>
                        <span x-show="!waDueReminderActive()">
                            <span x-text="t('wa_auto_reminders_off_desc') || 'Automatic background sending is paused. You can send on-demand reminders by clicking WhatsApp below, or enable automation.'"></span>
                        </span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                <button type="button" @click="navigateTo('whatsapp')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer"
                    :class="waDueReminderActive() 
                        ? 'bg-teal-700 hover:bg-teal-800 text-white' 
                        : 'bg-primary hover:bg-primary-hover text-white'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span x-text="waDueReminderActive() 
                        ? (t('wa_manage_automation') || 'Manage Rules & Logs') 
                        : (t('wa_turn_on_automation') || 'Turn On Auto Reminders')"></span>
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- CUSTOMER DUES TRACKER --}}
        <div
            class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 truncate" x-text="t('customer_dues_outstanding')">Customer Dues (Outstanding)</h4>
                    <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 rounded-full text-xs font-bold shrink-0 ml-2"
                        x-text="customers.filter(c => customerNetDue(c) > 0).length"></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-slate-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-700/50">
                                <th class="px-2.5 py-2 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider" x-text="t('customer')">Customer</th>
                                <th class="px-2.5 py-2 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider" x-text="t('mobile')">Mobile</th>
                                <th class="px-2.5 py-2 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider" x-text="t('due')">Due</th>
                                <th class="px-2.5 py-2 text-right text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider" x-text="t('actions')">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                            <template x-if="customersLoading">
                                <tr>
                                    <td colspan="4" class="text-center py-6">
                                        <div
                                            class="inline-block animate-spin rounded-full h-7 w-7 border-3 border-primary border-t-transparent">
                                        </div>
                                        <p class="text-xs text-slate-400 mt-2 font-medium" x-text="t('loading_customer_dues')">Loading customer dues...</p>
                                    </td>
                                </tr>
                            </template>
                            <template
                                x-for="cust in (customersLoading ? [] : customers.filter(c => customerNetDue(c) > 0).slice((customerDuesPage - 1) * customerDuesPerPage, customerDuesPage * customerDuesPerPage))"
                                :key="cust.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-2.5 py-2.5 text-xs font-bold text-slate-800 dark:text-white truncate max-w-[110px]" x-text="cust.name">
                                    </td>
                                    <td class="px-2.5 py-2.5 text-xs text-slate-500 dark:text-slate-400 font-mono whitespace-nowrap" x-text="cust.mobile || 'N/A'"></td>
                                    <td class="px-2.5 py-2.5 text-xs font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">₹<span
                                            x-text="customerNetDue(cust).toFixed(2)"></span></td>
                                    <td class="px-2.5 py-2.5 text-right text-xs whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                                            <button @click="openCollectCustomerPaymentModal(cust)"
                                                class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold transition-all inline-flex items-center gap-1 shrink-0 shadow-sm cursor-pointer whitespace-nowrap">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span x-text="t('collect')">Collect</span>
                                            </button>
                                            {{-- Automatic: sent from the DukanHisab number using WhatsApp credits --}}
                                            <button type="button" @click.stop="sendWhatsAppReminder('customer', cust.id)" :disabled="sendingWhatsAppKey === ('customer_' + cust.id)"
                                                class="px-2.5 py-1 bg-teal-700 hover:bg-teal-800 text-white rounded-lg text-[11px] font-bold transition-all inline-flex items-center gap-1 shrink-0 shadow-sm disabled:opacity-60 cursor-pointer whitespace-nowrap"
                                                :title="t('wa_send_auto_reminder')">
                                                <template x-if="sendingWhatsAppKey === ('customer_' + cust.id)">
                                                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                </template>
                                                <template x-if="sendingWhatsAppKey !== ('customer_' + cust.id)">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                                </template>
                                                <span>WhatsApp</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template
                                x-if="!customersLoading && customers.filter(c => customerNetDue(c) > 0).length === 0">
                                <tr>
                                    <td colspan="4" class="text-center text-slate-400 py-6 text-xs" x-text="t('no_customer_dues_outstanding')">No customer dues
                                        outstanding. Great job!</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">
                <x-pagination currentPage="customerDuesPage"
                    totalItems="customers.filter(c => customerNetDue(c) > 0).length" perPage="customerDuesPerPage" loading="customersLoading" />
            </div>
        </div>

        {{-- SUPPLIER DUES TRACKER --}}
        <div
            class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 truncate" x-text="t('supplier_dues_we_owe')">Supplier Dues (We Owe)</h4>
                    <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300 rounded-full text-xs font-bold shrink-0 ml-2"
                        x-text="suppliers.filter(s => parseFloat(s.due_amount) > 0).length"></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-slate-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-700/50">
                                <th class="px-2.5 py-2 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider" x-text="t('supplier')">Supplier</th>
                                <th class="px-2.5 py-2 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider" x-text="t('mobile')">Mobile</th>
                                <th class="px-2.5 py-2 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider" x-text="t('due')">Due</th>
                                <th class="px-2.5 py-2 text-right text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider" x-text="t('actions')">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                            <template x-if="suppliersLoading">
                                <tr>
                                    <td colspan="4" class="text-center py-6">
                                        <div
                                            class="inline-block animate-spin rounded-full h-7 w-7 border-3 border-primary border-t-transparent">
                                        </div>
                                        <p class="text-xs text-slate-400 mt-2 font-medium" x-text="t('loading_supplier_dues')">Loading supplier dues...</p>
                                    </td>
                                </tr>
                            </template>
                            <template
                                x-for="sup in (suppliersLoading ? [] : suppliers.filter(s => parseFloat(s.due_amount) > 0).slice((supplierDuesPage - 1) * supplierDuesPerPage, supplierDuesPage * supplierDuesPerPage))"
                                :key="sup.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-2.5 py-2.5 text-xs font-bold text-slate-800 dark:text-white truncate max-w-[110px]" x-text="sup.name">
                                    </td>
                                    <td class="px-2.5 py-2.5 text-xs text-slate-500 dark:text-slate-400 font-mono whitespace-nowrap" x-text="sup.mobile || 'N/A'"></td>
                                    <td class="px-2.5 py-2.5 text-xs font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">₹<span
                                            x-text="parseFloat(sup.due_amount).toFixed(2)"></span></td>
                                    <td class="px-2.5 py-2.5 text-right text-xs whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                                            <button @click="openPaySupplierDueModal(sup)"
                                                class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-[11px] font-bold transition-all inline-flex items-center gap-1 shrink-0 shadow-sm cursor-pointer whitespace-nowrap">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                <span class="whitespace-nowrap" x-text="t('pay_due')">Pay Due</span>
                                            </button>
                                            {{-- Automatic: sent from the DukanHisab number using WhatsApp credits --}}
                                            <button type="button" @click.stop="sendWhatsAppReminder('supplier', sup.id)" :disabled="sendingWhatsAppKey === ('supplier_' + sup.id)"
                                                class="px-2.5 py-1 bg-teal-700 hover:bg-teal-800 text-white rounded-lg text-[11px] font-bold transition-all inline-flex items-center gap-1 shrink-0 shadow-sm disabled:opacity-60 cursor-pointer whitespace-nowrap"
                                                :title="t('wa_send_auto_reminder')">
                                                <template x-if="sendingWhatsAppKey === ('supplier_' + sup.id)">
                                                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                </template>
                                                <template x-if="sendingWhatsAppKey !== ('supplier_' + sup.id)">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                                </template>
                                                <span>WhatsApp</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template
                                x-if="!suppliersLoading && suppliers.filter(s => parseFloat(s.due_amount) > 0).length === 0">
                                <tr>
                                    <td colspan="4" class="text-center text-slate-400 py-6 text-xs" x-text="t('no_supplier_dues_outstanding')">No outstanding supplier dues.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">
                <x-pagination currentPage="supplierDuesPage"
                    totalItems="suppliers.filter(s => parseFloat(s.due_amount) > 0).length" perPage="supplierDuesPerPage" loading="suppliersLoading" />
            </div>
        </div>
    </div>

    {{-- INVENTORY ALERT (LOW STOCK) --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-4">
        <div class="flex justify-between items-center mb-1">
            <div class="flex items-center gap-2">
                <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 truncate" x-text="t('inventory_alert_low_stock')">Inventory Alert (Low Stock)</h4>
                <span class="px-2 py-0.5 bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 rounded-full text-xs font-bold shrink-0" 
                    x-text="products && products.length > 0 ? products.filter(p => parseFloat(p.stock) <= parseFloat(p.low_stock_threshold)).length : (dashboardStats.low_stock_count || 0)"></span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="navigateTo('inventory')" 
                    class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                    <span x-text="t('inventory') || 'Inventory'">Inventory</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>

        <template x-if="productsLoading && (!products || products.length === 0)">
            <div class="text-center py-6">
                <div class="inline-block animate-spin rounded-full h-7 w-7 border-3 border-primary border-t-transparent"></div>
                <p class="text-xs text-slate-400 mt-2 font-medium" x-text="t('loading') || 'Loading...'">Loading...</p>
            </div>
        </template>

        {{-- Low stock items list/cards grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <template x-for="prod in (products && products.length > 0 ? products.filter(p => parseFloat(p.stock) <= parseFloat(p.low_stock_threshold)) : (dashboardStats.low_stock_products || []))" :key="prod.id">
                <div class="flex justify-between items-center p-3 bg-slate-50 dark:bg-gray-700/50 rounded-xl border border-slate-100 dark:border-gray-700">
                    <div class="min-w-0 pr-2">
                        <p class="text-sm font-semibold text-slate-800 dark:text-white truncate" x-text="prod.name"></p>
                        <p class="text-xs text-slate-400 truncate"><span x-text="t('barcode')">Barcode</span>: <span x-text="prod.barcode || '-'"></span></p>
                    </div>
                    <div class="text-right shrink-0 flex flex-col items-end gap-1">
                        <div>
                            <p class="text-xs font-bold text-rose-600"><span x-text="t('stock')">Stock</span>: <span x-text="prod.stock"></span></p>
                            <p class="text-[10px] text-slate-400"><span x-text="t('limit')">Limit</span>: <span x-text="prod.low_stock_threshold"></span></p>
                        </div>
                        <button @click="navigateTo('purchases')" class="px-2 py-0.5 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded text-[10px] font-bold transition-all">
                            + <span x-text="t('purchase') || 'Purchase'">Purchase</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <template x-if="!productsLoading && ((products && products.length > 0 ? products.filter(p => parseFloat(p.stock) <= parseFloat(p.low_stock_threshold)).length : (dashboardStats.low_stock_products || []).length) === 0)">
            <p class="text-sm text-slate-400 text-center py-6" x-text="t('all_items_sufficiently_stocked')">All items are sufficiently stocked.</p>
        </template>
    </div>
</div>
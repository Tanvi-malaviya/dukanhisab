{{-- REMINDERS PANEL --}}
<div x-show="page === 'reminders'" class="space-y-2">
   

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        {{-- CUSTOMER DUES TRACKER --}}
        <div
            class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Customer Dues (Outstanding Balances)</h4>
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold"
                        x-text="customers.filter(c => parseFloat(c.due_amount) > 0).length"></span>
                </div>
                <div class="overflow-x-auto overflow-y-hidden">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-slate-400 uppercase">Customer</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-slate-400 uppercase">Mobile</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-slate-400 uppercase">Due Balance</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-slate-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                            <template x-if="customersLoading">
                                <tr>
                                    <td colspan="4" class="text-center py-6">
                                        <div
                                            class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent">
                                        </div>
                                        <p class="text-xs text-slate-400 mt-2 font-medium">Loading customer dues...</p>
                                    </td>
                                </tr>
                            </template>
                            <template
                                x-for="cust in (customersLoading ? [] : customers.filter(c => parseFloat(c.due_amount) > 0).slice((customerDuesPage - 1) * customerDuesPerPage, customerDuesPage * customerDuesPerPage))"
                                :key="cust.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 text-xs font-bold text-slate-800 dark:text-white" x-text="cust.name">
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-500" x-text="cust.mobile || 'N/A'"></td>
                                    <td class="px-4 py-3 text-xs font-bold text-rose-600">₹<span
                                            x-text="parseFloat(cust.due_amount).toFixed(2)"></span></td>
                                    <td class="px-4 py-3 text-right text-xs flex items-center justify-end gap-1.5">
                                        <button @click="openCollectCustomerPaymentModal(cust)"
                                            class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold shadow-2xs transition-all flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span>Collect</span>
                                        </button>
                                        <a :href="`https://wa.me/${cust.mobile || ''}?text=${encodeURIComponent('Dear ' + cust.name + ', this is a friendly reminder that you have an outstanding balance of ₹' + cust.due_amount + ' at ' + (shop ? shop.name : 'DukanHisab') + '. Please clear it at your earliest convenience. Thank you!')}`"
                                            target="_blank"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 hover:bg-emerald-200 text-emerald-800 transition-all hover:scale-105"
                                            title="Send WhatsApp Reminder">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.377-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.454 5.709 1.455h.008c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            </template>
                            <template
                                x-if="!customersLoading && customers.filter(c => parseFloat(c.due_amount) > 0).length === 0">
                                <tr>
                                    <td colspan="4" class="text-center text-slate-400 py-6 text-xs">No customer dues
                                        outstanding. Great job!</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">
                <x-pagination currentPage="customerDuesPage"
                    totalItems="customers.filter(c => parseFloat(c.due_amount) > 0).length" perPage="customerDuesPerPage" loading="customersLoading" />
            </div>
        </div>

        {{-- SUPPLIER DUES TRACKER --}}
        <div
            class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Supplier Dues (We Owe)</h4>
                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded-full text-xs font-bold"
                        x-text="suppliers.filter(s => parseFloat(s.due_amount) > 0).length"></span>
                </div>
                <div class="overflow-x-auto overflow-y-hidden">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-slate-400 uppercase">Supplier</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-slate-400 uppercase">Mobile</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-slate-400 uppercase">Outstanding Balance</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-slate-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                            <template x-if="suppliersLoading">
                                <tr>
                                    <td colspan="4" class="text-center py-6">
                                        <div
                                            class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent">
                                        </div>
                                        <p class="text-xs text-slate-400 mt-2 font-medium">Loading supplier dues...</p>
                                    </td>
                                </tr>
                            </template>
                            <template
                                x-for="sup in (suppliersLoading ? [] : suppliers.filter(s => parseFloat(s.due_amount) > 0).slice((supplierDuesPage - 1) * supplierDuesPerPage, supplierDuesPage * supplierDuesPerPage))"
                                :key="sup.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 text-xs font-bold text-slate-800 dark:text-white" x-text="sup.name">
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-500" x-text="sup.mobile || 'N/A'"></td>
                                    <td class="px-4 py-3 text-xs font-bold text-rose-600">₹<span
                                            x-text="parseFloat(sup.due_amount).toFixed(2)"></span></td>
                                    <td class="px-4 py-3 text-right text-xs flex items-center justify-end gap-1.5">
                                        <button @click="openPaySupplierDueModal(sup)"
                                            class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-[11px] font-bold shadow-2xs transition-all flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            <span>Pay Due</span>
                                        </button>
                                        <a :href="`https://wa.me/${sup.mobile || ''}?text=${encodeURIComponent('Hello ' + sup.name + ', I am contacting you regarding the outstanding balance of ₹' + sup.due_amount + ' that I owe you. Let\'s coordinate the payment. Thank you!')}`"
                                            target="_blank"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 hover:bg-emerald-200 text-emerald-800 transition-all hover:scale-105"
                                            title="Send WhatsApp Message">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.377-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.454 5.709 1.455h.008c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            </template>
                            <template
                                x-if="!suppliersLoading && suppliers.filter(s => parseFloat(s.due_amount) > 0).length === 0">
                                <tr>
                                    <td colspan="4" class="text-center text-slate-400 py-6 text-xs">No outstanding supplier dues.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">
                <x-pagination currentPage="supplierDuesPage"
                    totalItems="suppliers.filter(s => parseFloat(s.due_amount) > 0).length" perPage="supplierDuesPerPage" loading="suppliersLoading" />
            </div>
        </div>
    </div>

    {{-- LOW STOCK ALERTS --}}
  
</div>
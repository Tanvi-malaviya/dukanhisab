{{-- RETURNABLE CONTAINERS MODALS (module enabled per shop by the admin) --}}

{{-- 1. GIVE / RETURN / EXCHANGE / OPENING ENTRY --}}
<div x-show="showContainerEntryModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl my-auto overflow-hidden flex flex-col max-h-[92vh]"
        @click.outside="if (!confirmModal.show) showContainerEntryModal = false">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-slate-800 dark:text-white" x-text="t('container_entry')">Container Entry</h3>
            <button @click="showContainerEntryModal = false" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-5 space-y-4 overflow-y-auto">
            {{-- Mode --}}
            <div class="grid grid-cols-4 gap-1.5">
                <template x-for="m in [['return', 'receive_return'], ['give', 'give_containers'], ['exchange', 'container_exchange'], ['opening', 'opening_balance']]" :key="m[0]">
                    <button type="button" @click="setContainerMode(m[0])"
                        :class="containerForm.mode === m[0] ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'"
                        class="py-2 text-[11px] font-bold rounded-lg transition-all" x-text="t(m[1])"></button>
                </template>
            </div>
            <p x-show="containerForm.mode === 'opening'" class="text-[11px] text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-2 rounded-lg"
                x-text="t('opening_balance_hint')">Containers already with the customer before you started using this page. Records the deposit held — no cash entry today.</p>

            {{-- Customer --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('customer')">Customer</label>
                <template x-if="!containerForm.customer_id">
                    <div class="space-y-1">
                        <input type="text" x-model="containerCustomerQuery" :placeholder="t('search_customer_placeholder')"
                            class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                        <div class="max-h-40 overflow-y-auto border border-slate-200 dark:border-gray-700 rounded-xl divide-y divide-slate-100 dark:divide-gray-700">
                            <template x-for="cust in containerCustomerOptions()" :key="cust.id">
                                <button type="button" @click="selectContainerCustomer(cust.id)"
                                    class="w-full text-left px-3 py-2 text-xs hover:bg-primary/10 flex justify-between">
                                    <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="cust.name"></span>
                                    <span class="text-slate-400 font-mono" x-text="cust.mobile || ''"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="containerForm.customer_id">
                    <div class="flex items-center justify-between px-3 py-2 bg-slate-50 dark:bg-gray-700/50 rounded-xl border border-slate-200 dark:border-gray-700">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800 dark:text-white truncate"
                                x-text="containerCustomerDetail ? containerCustomerDetail.customer.name : '...'"></p>
                            <div class="flex flex-wrap gap-1 mt-1" x-show="containerCustomerDetail">
                                <template x-for="h in (containerCustomerDetail ? containerCustomerDetail.holdings : [])" :key="h.container_type_id">
                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 font-semibold text-[10px]"
                                        x-text="h.pending + ' ' + h.name + ' · ' + money(h.deposit)"></span>
                                </template>
                                <template x-if="containerCustomerDetail && containerCustomerDetail.holdings.length === 0">
                                    <span class="text-[10px] text-slate-400" x-text="t('no_containers_held')">No containers with this customer.</span>
                                </template>
                            </div>
                        </div>
                        <button type="button" @click="selectContainerCustomer(null)" class="text-xs text-primary font-bold hover:underline shrink-0 ml-2" x-text="t('change')">Change</button>
                    </div>
                </template>
            </div>

            {{-- Returned --}}
            <div x-show="['return', 'exchange'].includes(containerForm.mode) && containerForm.customer_id" class="space-y-2">
                <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-700" x-text="t('containers_coming_back')">Containers Coming Back</h4>
                <template x-if="containerCustomerDetail && containerForm.returns.length === 0">
                    <p class="text-xs text-slate-400" x-text="t('no_containers_held')">No containers with this customer.</p>
                </template>
                <template x-for="(row, idx) in containerForm.returns" :key="idx">
                    <div class="p-3 bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/40 rounded-xl space-y-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-sm font-bold text-slate-800 dark:text-white" x-text="containerTypeName(row.container_type_id)"></span>
                            <div class="flex items-center gap-2">
                                <select x-model="row.sale_id" class="px-2 py-1 border border-slate-300 dark:border-gray-600 rounded-lg text-[11px] dark:bg-gray-700 dark:text-white">
                                    <option value="" x-text="t('all_invoices_oldest_first')">All invoices (oldest first)</option>
                                    <template x-for="opt in containerInvoiceOptions(row.container_type_id)" :key="opt.sale_id">
                                        <option :value="String(opt.sale_id)" :selected="String(row.sale_id) === String(opt.sale_id)" x-text="opt.label + ' (' + opt.pending + ')'"></option>
                                    </template>
                                </select>
                                <button type="button" @click="splitContainerReturnRow(idx)" :title="t('split_by_invoice')" class="text-[11px] text-primary font-bold hover:underline">+ <span x-text="t('invoice')">Invoice</span></button>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-500"><span x-text="t('pending')">Pending</span>: <span class="font-bold" x-text="containerRowPending(row)"></span></p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-0.5" x-text="t('returned_ok')">Returned OK</label>
                                <input type="number" min="0" x-model="row.returned" placeholder="0" class="w-full px-2 py-1.5 border border-slate-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-0.5" x-text="t('damaged')">Damaged</label>
                                <input type="number" min="0" x-model="row.damaged" placeholder="0" class="w-full px-2 py-1.5 border border-slate-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-0.5" x-text="t('damage_deduction_rs')">Deduction ₹</label>
                                <input type="number" min="0" step="0.01" x-model="row.damage_deduction" placeholder="0" :disabled="!(parseInt(row.damaged) > 0)"
                                    class="w-full px-2 py-1.5 border border-slate-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white disabled:opacity-50">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-0.5" x-text="t('lost')">Lost</label>
                                <input type="number" min="0" x-model="row.lost" placeholder="0" class="w-full px-2 py-1.5 border border-slate-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Given --}}
            <div x-show="['give', 'exchange', 'opening'].includes(containerForm.mode)" class="space-y-2">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700"
                        x-text="containerForm.mode === 'opening' ? t('containers_already_with_customer') : t('containers_going_out')">Containers Going Out</h4>
                    <label x-show="containerForm.mode !== 'opening'" class="flex items-center gap-2 text-[11px] font-semibold text-slate-600 dark:text-slate-300 cursor-pointer">
                        <span x-text="t('deposit_collected')">Deposit collected</span>
                        <span class="app-toggle app-toggle-sm"><input type="checkbox" x-model="containerForm.collect_deposit"><span class="app-toggle-slider"></span></span>
                    </label>
                </div>
                <template x-for="(row, idx) in containerForm.issues" :key="row.container_type_id">
                    <div class="grid grid-cols-3 gap-2 items-end">
                        <span class="text-sm font-bold text-slate-800 dark:text-white pb-1.5" x-text="containerTypeName(row.container_type_id)"></span>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5" x-text="t('quantity')">Quantity</label>
                            <input type="number" min="0" x-model="row.quantity" placeholder="0" class="w-full px-2 py-1.5 border border-slate-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5" x-text="t('deposit_per_unit')">Deposit / Unit</label>
                            <input type="number" min="0" step="0.01" x-model="row.deposit_per_unit" :disabled="containerForm.mode !== 'opening' && !containerForm.collect_deposit"
                                class="w-full px-2 py-1.5 border border-slate-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white disabled:opacity-50">
                        </div>
                    </div>
                </template>
                <div x-show="containerForm.mode === 'opening'">
                    <label class="block text-[10px] font-semibold text-slate-500 mb-0.5" x-text="t('given_on_date')">Given on (date)</label>
                    <input type="date" x-model="containerForm.entry_date" class="px-2 py-1.5 border border-slate-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            {{-- Settlement preview --}}
            <div x-show="containerForm.customer_id" class="p-3 rounded-xl bg-slate-50 dark:bg-gray-700/40 border border-slate-200 dark:border-gray-700 space-y-1.5 text-xs">
                <div class="flex justify-between" x-show="containerPreview().deposit > 0">
                    <span class="text-slate-500" x-text="containerForm.mode === 'opening' ? t('deposit_held') : t('deposit_to_collect')">Deposit</span>
                    <span class="font-semibold" x-text="money(containerPreview().deposit)"></span>
                </div>
                <div class="flex justify-between" x-show="containerPreview().refund > 0">
                    <span class="text-slate-500" x-text="t('deposit_to_refund')">Deposit to refund</span>
                    <span class="font-semibold" x-text="'−' + money(containerPreview().refund)"></span>
                </div>
                <div class="flex justify-between" x-show="containerPreview().forfeit > 0">
                    <span class="text-slate-500" x-text="t('forfeit_kept')">Kept (damage / lost)</span>
                    <span class="font-semibold text-rose-600" x-text="money(containerPreview().forfeit)"></span>
                </div>
                <div class="flex justify-between text-sm font-extrabold border-t border-dashed border-slate-200 dark:border-gray-600 pt-1.5">
                    <span x-text="containerForm.mode === 'opening' ? t('no_cash_entry') : (containerPreview().net > 0 ? t('collect_from_customer') : (containerPreview().net < 0 ? t('pay_to_customer') : t('nothing_to_settle')))"></span>
                    <span :class="containerPreview().net < 0 ? 'text-rose-600' : 'text-primary'" x-text="containerForm.mode === 'opening' ? '' : money(Math.abs(containerPreview().net))"></span>
                </div>
                <p x-show="containerPreview().overReturn" class="text-[11px] font-bold text-rose-600" x-text="t('return_more_than_pending')">More than the customer holds.</p>

                {{-- How the net amount is settled --}}
                <div x-show="containerForm.mode !== 'opening' && containerPreview().net !== 0" class="pt-1.5 space-y-1">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400" x-text="containerPreview().net > 0 ? t('received_by') : t('refund_by')">Refund by</label>
                    <div class="grid gap-1.5" :class="containerPreview().net < 0 ? 'grid-cols-4' : 'grid-cols-3'">
                        <template x-for="m in (containerPreview().net < 0 ? ['cash', 'upi', 'bank', 'due_adjustment'] : ['cash', 'upi', 'bank'])" :key="m">
                            <button type="button" @click="containerForm.settlement_method = m"
                                :class="containerForm.settlement_method === m ? 'bg-primary text-white' : 'bg-white dark:bg-gray-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-gray-600'"
                                class="py-1.5 text-[11px] font-bold rounded-lg" x-text="containerMethodLabel(m)"></button>
                        </template>
                    </div>
                    <p x-show="containerPreview().net < 0 && containerForm.settlement_method === 'due_adjustment' && containerCustomerDetail"
                        class="text-[10px] text-slate-500">
                        <span x-text="t('customer_due')">Customer due</span>: <span class="font-bold" x-text="containerCustomerDetail ? money(containerCustomerDetail.customer.due_amount) : ''"></span>
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('note')">Note</label>
                <input type="text" x-model="containerForm.note" maxlength="255" class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
        </div>

        <div class="px-5 py-3.5 border-t border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 flex justify-end gap-2 shrink-0">
            <button @click="showContainerEntryModal = false" class="px-4 py-2 bg-slate-200 dark:bg-gray-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-lg" x-text="t('cancel')">Cancel</button>
            <button @click="saveContainerEntry()" :disabled="containerSaving || !containerForm.customer_id"
                class="px-5 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-lg disabled:opacity-50" x-text="containerSaving ? '...' : t('save')">Save</button>
        </div>
    </div>
</div>

{{-- 2. ENTRY DETAIL / RECEIPT --}}
<div x-show="showContainerEntryDetail" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg my-auto overflow-hidden flex flex-col max-h-[92vh]"
        @click.outside="if (!confirmModal.show) showContainerEntryDetail = false">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-slate-800 dark:text-white" x-text="t('container_receipt')">Container Receipt</h3>
            <button @click="showContainerEntryDetail = false" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <template x-if="containerEntryDetail">
            <div class="p-5 overflow-y-auto">
                <div id="container-receipt" class="space-y-3 text-xs text-slate-800 dark:text-slate-200">
                    <div class="text-center">
                        <p class="text-base font-extrabold" x-text="shop ? shop.name : ''"></p>
                        <p class="text-[11px] text-slate-500" x-text="shop ? (shop.mobile || '') : ''"></p>
                        <p class="mt-2 text-sm font-bold" x-text="containerEntryTypeLabel(containerEntryDetail.type)"></p>
                    </div>
                    <div class="flex justify-between">
                        <div>
                            <p class="font-mono font-bold" x-text="containerEntryDetail.entry_number"></p>
                            <p class="text-slate-500" x-text="formatDate(containerEntryDetail.entry_date)"></p>
                            <template x-if="containerEntryDetail.reversal_of"><p class="text-rose-600 font-semibold" x-text="t('reversal_of') + ' ' + containerEntryDetail.reversal_of.entry_number"></p></template>
                            <template x-if="containerEntryDetail.reversed_at"><p class="text-rose-600 font-bold" x-text="t('reversed')"></p></template>
                        </div>
                        <div class="text-right" x-show="containerEntryDetail.customer">
                            <p class="font-bold" x-text="containerEntryDetail.customer ? containerEntryDetail.customer.name : ''"></p>
                            <p class="text-slate-500" x-text="containerEntryDetail.customer ? (containerEntryDetail.customer.mobile || '') : ''"></p>
                        </div>
                    </div>
                    <table class="w-full border-t border-b border-slate-200 dark:border-gray-700">
                        <thead>
                            <tr class="text-slate-500">
                                <th class="text-left py-1.5" x-text="t('container')">Container</th>
                                <th class="text-left py-1.5" x-text="t('type')">Type</th>
                                <th class="text-left py-1.5" x-text="t('invoice')">Invoice</th>
                                <th class="text-center py-1.5" x-text="t('quantity')">Qty</th>
                                <th class="text-right py-1.5" x-text="t('amount')">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="m in (containerEntryDetail.movements || [])" :key="m.id">
                                <tr class="border-t border-slate-100 dark:border-gray-700">
                                    <td class="py-1.5 font-semibold" x-text="m.container_type ? m.container_type.name : containerTypeName(m.container_type_id)"></td>
                                    <td class="py-1.5" x-text="containerKindLabel(m.kind)"></td>
                                    <td class="py-1.5 font-mono text-[10px]" x-text="m.sale ? m.sale.sale_number : '-'"></td>
                                    <td class="py-1.5 text-center" x-text="m.quantity"></td>
                                    <td class="py-1.5 text-right">
                                        <span x-text="['stock_in', 'stock_out'].includes(m.kind) ? '-' : money(m.amount)"></span>
                                        <template x-if="parseFloat(m.forfeit_amount) > 0"><p class="text-[10px] text-rose-600" x-text="t('kept') + ' ' + money(m.forfeit_amount)"></p></template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div class="space-y-1 ml-auto w-60">
                        <div class="flex justify-between" x-show="parseFloat(containerEntryDetail.deposit_amount) > 0"><span class="text-slate-500" x-text="t('deposit')">Deposit</span><span x-text="money(containerEntryDetail.deposit_amount)"></span></div>
                        <div class="flex justify-between" x-show="parseFloat(containerEntryDetail.refund_amount) > 0"><span class="text-slate-500" x-text="t('refund')">Refund</span><span x-text="money(containerEntryDetail.refund_amount)"></span></div>
                        <div class="flex justify-between" x-show="parseFloat(containerEntryDetail.forfeit_amount) > 0"><span class="text-slate-500" x-text="t('forfeit_kept')">Kept</span><span x-text="money(containerEntryDetail.forfeit_amount)"></span></div>
                        <div class="flex justify-between font-bold border-t border-slate-200 dark:border-gray-700 pt-1">
                            <span x-text="parseFloat(containerEntryDetail.net_amount) !== 0 ? containerMethodLabel(containerEntryDetail.settlement_method) : ''"></span>
                            <span x-text="containerNetText(containerEntryDetail)"></span>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-500" x-show="containerEntryDetail.note" x-text="containerEntryDetail.note"></p>
                    <p class="text-[10px] text-center text-slate-400" x-text="t('container_deposit_invoice_note')"></p>
                </div>
            </div>
        </template>
        <div class="px-5 py-3.5 border-t border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 flex justify-between gap-2 shrink-0">
            <button x-show="containerEntryDetail && canReverseContainerEntry(containerEntryDetail)" @click="reverseContainerEntry(containerEntryDetail)"
                class="px-4 py-2 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white text-xs font-bold rounded-lg" x-text="t('reverse')">Reverse</button>
            <span x-show="!(containerEntryDetail && canReverseContainerEntry(containerEntryDetail))"></span>
            <button @click="printHtmlBlock('container-receipt', containerEntryDetail ? containerEntryDetail.entry_number : 'Receipt')"
                class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg" x-text="t('print')">Print</button>
        </div>
    </div>
</div>

{{-- 3. ONE CUSTOMER'S CONTAINERS --}}
<div x-show="showContainerCustomerModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl my-auto overflow-hidden flex flex-col max-h-[92vh]"
        @click.outside="if (!confirmModal.show && !showContainerEntryDetail) showContainerCustomerModal = false">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-slate-800 dark:text-white" x-text="containerCustomerView ? containerCustomerView.customer.name : t('loading')"></h3>
            <button @click="showContainerCustomerModal = false" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <template x-if="containerCustomerView">
            <div class="p-5 space-y-4 overflow-y-auto text-xs">
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20">
                        <p class="text-[10px] font-bold uppercase text-amber-700" x-text="t('deposit_held')">Deposit Held</p>
                        <p class="text-lg font-extrabold text-amber-700" x-text="money(containerCustomerView.deposit_held)"></p>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-900/20">
                        <p class="text-[10px] font-bold uppercase text-rose-700" x-text="t('due')">Due</p>
                        <p class="text-lg font-extrabold text-rose-700" x-text="money(containerCustomerView.customer.due_amount)"></p>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-slate-700 dark:text-slate-200 mb-1.5" x-text="t('pending_by_invoice')">Pending by Invoice</h4>
                    <table class="w-full">
                        <thead><tr class="text-slate-500 text-left">
                            <th class="py-1" x-text="t('date')">Date</th><th class="py-1" x-text="t('invoice')">Invoice</th>
                            <th class="py-1" x-text="t('container')">Container</th><th class="py-1 text-center" x-text="t('pending')">Pending</th>
                            <th class="py-1 text-right" x-text="t('deposit_per_unit')">Deposit / Unit</th>
                        </tr></thead>
                        <tbody>
                            <template x-for="lot in containerCustomerView.lots" :key="lot.id">
                                <tr class="border-t border-slate-100 dark:border-gray-700">
                                    <td class="py-1.5" x-text="formatDate(lot.entry ? lot.entry.entry_date : lot.created_at)"></td>
                                    <td class="py-1.5 font-mono" x-text="lot.sale ? lot.sale.sale_number : (lot.entry ? lot.entry.entry_number : '-')"></td>
                                    <td class="py-1.5 font-semibold" x-text="lot.container_type ? lot.container_type.name : ''"></td>
                                    <td class="py-1.5 text-center font-bold" x-text="lot.pending_quantity"></td>
                                    <td class="py-1.5 text-right" x-text="money(lot.deposit_per_unit)"></td>
                                </tr>
                            </template>
                            <template x-if="containerCustomerView.lots.length === 0">
                                <tr><td colspan="5" class="py-3 text-center text-slate-400" x-text="t('no_containers_held')"></td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div>
                    <h4 class="font-bold text-slate-700 dark:text-slate-200 mb-1.5" x-text="t('history')">History</h4>
                    <div class="divide-y divide-slate-100 dark:divide-gray-700">
                        <template x-for="entry in containerCustomerView.entries" :key="entry.id">
                            <button type="button" @click="openContainerEntryDetail(entry)" class="w-full flex justify-between items-center py-2 hover:bg-slate-50 dark:hover:bg-gray-700/50 text-left" :class="entry.reversed_at ? 'opacity-60' : ''">
                                <div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="containerEntryTypeClass(entry.type)" x-text="containerEntryTypeLabel(entry.type)"></span>
                                    <span class="ml-1 font-mono text-[11px]" x-text="entry.entry_number"></span>
                                    <p class="text-[11px] text-slate-500 mt-0.5" x-text="formatDate(entry.entry_date) + ' · ' + containerEntrySummary(entry)"></p>
                                </div>
                                <span class="font-semibold whitespace-nowrap" x-text="containerNetText(entry)"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </template>
        <div class="px-5 py-3.5 border-t border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 flex justify-end gap-2 shrink-0" x-show="containerCustomerView">
            <button @click="showContainerCustomerModal = false; openContainerEntryModal('give', containerCustomerView.customer.id)" class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-lg" x-text="t('give')">Give</button>
            <button @click="showContainerCustomerModal = false; openContainerEntryModal('return', containerCustomerView.customer.id)" class="px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-lg" x-text="t('receive_return')">Receive Return</button>
        </div>
    </div>
</div>

{{-- 4. CONTAINER TYPE --}}
<div x-show="showContainerTypeModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden" @click.outside="if (!confirmModal.show) showContainerTypeModal = false">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-gray-700">
            <h3 class="font-bold text-slate-800 dark:text-white" x-text="containerTypeForm.id ? t('edit') : t('add_container_type')"></h3>
        </div>
        <form @submit.prevent="saveContainerType()" class="p-5 space-y-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('name')">Name</label>
                <input type="text" required maxlength="100" x-model="containerTypeForm.name" :placeholder="t('container_type_placeholder')"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('deposit_per_unit')">Deposit / Unit (₹)</label>
                <input type="number" required min="0" step="0.01" x-model="containerTypeForm.deposit_amount"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div x-show="!containerTypeForm.id">
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('total_owned_optional')">Total owned (optional)</label>
                <input type="number" min="0" x-model="containerTypeForm.total_owned" placeholder="0"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
                <p class="text-[10px] text-slate-400 mt-1" x-text="t('total_owned_hint')">How many you own in total, to see how many are in the shop.</p>
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" @click="showContainerTypeModal = false" class="px-4 py-2 bg-slate-200 dark:bg-gray-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-lg" x-text="t('cancel')">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-primary text-white text-xs font-bold rounded-lg" x-text="t('save')">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- 5. STOCK ADJUSTMENT --}}
<div x-show="showContainerStockModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden" @click.outside="if (!confirmModal.show) showContainerStockModal = false">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-gray-700">
            <h3 class="font-bold text-slate-800 dark:text-white"><span x-text="t('adjust_stock')"></span> — <span x-text="containerStockForm.type ? containerStockForm.type.name : ''"></span></h3>
        </div>
        <form @submit.prevent="saveContainerStock()" class="p-5 space-y-3">
            <div class="grid grid-cols-2 gap-1.5">
                <button type="button" @click="containerStockForm.direction = 'in'" :class="containerStockForm.direction === 'in' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'"
                    class="py-2 text-xs font-bold rounded-lg" x-text="t('stock_added')">Added (bought)</button>
                <button type="button" @click="containerStockForm.direction = 'out'" :class="containerStockForm.direction === 'out' ? 'bg-rose-600 text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300'"
                    class="py-2 text-xs font-bold rounded-lg" x-text="t('stock_removed')">Removed (scrapped)</button>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('quantity')">Quantity</label>
                <input type="number" required min="1" x-model="containerStockForm.quantity"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="t('note')">Note</label>
                <input type="text" maxlength="255" x-model="containerStockForm.note"
                    class="block w-full px-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white">
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" @click="showContainerStockModal = false" class="px-4 py-2 bg-slate-200 dark:bg-gray-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-lg" x-text="t('cancel')">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-primary text-white text-xs font-bold rounded-lg" x-text="t('save')">Save</button>
            </div>
        </form>
    </div>
</div>

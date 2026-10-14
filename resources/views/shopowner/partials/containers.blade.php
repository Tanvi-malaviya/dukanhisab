{{-- RETURNABLE CONTAINERS PAGE (optional module, enabled per shop by the admin) --}}
<div x-show="page === 'containers'" class="space-y-4">

    <template x-if="!hasContainers()">
        <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm text-center space-y-2">
            <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="t('containers_not_enabled_title')">Returnable Containers is not enabled</p>
            <p class="text-xs text-slate-500" x-text="t('containers_not_enabled_msg')">Please contact support to enable it for your shop.</p>
        </div>
    </template>

    <template x-if="hasContainers()">
        <div class="space-y-4">
            {{-- Header & actions --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white" x-text="t('returnable_containers')">Returnable Containers</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="t('returnable_containers_subtitle')">Containers with customers and the refundable deposit held against them.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="openContainerEntryModal('return')" :disabled="!containerTypes.length"
                        class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm disabled:opacity-50"
                        x-text="t('receive_return')">Receive Return</button>
                    <button @click="openContainerEntryModal('give')" :disabled="!containerTypes.length"
                        class="px-3 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold shadow-sm disabled:opacity-50"
                        x-text="t('give_containers')">Give Containers</button>
                    <button @click="openContainerEntryModal('exchange')" :disabled="!containerTypes.length"
                        class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-sm disabled:opacity-50"
                        x-text="t('container_exchange')">Exchange</button>
                    <button @click="openContainerEntryModal('opening')" :disabled="!containerTypes.length"
                        class="px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-gray-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold"
                        x-text="t('opening_balance')">Opening Balance</button>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="flex gap-1 border-b border-slate-200 dark:border-gray-700">
                <template x-for="tab in [['overview', 'overview'], ['history', 'history'], ['types', 'container_types']]" :key="tab[0]">
                    <button @click="containerTab = tab[0]"
                        :class="containerTab === tab[0] ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                        class="px-3 py-2 text-xs font-bold border-b-2 -mb-px transition-all" x-text="t(tab[1])"></button>
                </template>
            </div>

            {{-- No types yet --}}
            <template x-if="!containersLoading && containerTypes.length === 0 && containerTab !== 'types'">
                <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-dashed border-slate-300 dark:border-gray-600 text-center space-y-3">
                    <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="t('add_first_container_type')">Add your first container type</p>
                    <p class="text-xs text-slate-500" x-text="t('add_first_container_type_msg')">For example Tub ₹200, Can ₹500, Bucket ₹300. Then link it to products so containers are counted on every bill.</p>
                    <button @click="openContainerTypeModal()" class="px-4 py-2 bg-primary text-white rounded-xl text-xs font-bold" x-text="'+ ' + t('add_container_type')">+ Add Container Type</button>
                </div>
            </template>

            {{-- ===== OVERVIEW ===== --}}
            <div x-show="containerTab === 'overview' && containerTypes.length > 0" class="space-y-4">
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                    <div class="bg-white dark:bg-gray-800 p-3.5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400" x-text="t('containers_out')">Containers Out</p>
                        <p class="text-xl font-extrabold text-slate-800 dark:text-white mt-1" x-text="containerSummary ? containerSummary.containers_out : '-'"></p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-3.5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400" x-text="t('deposit_held')">Deposit Held</p>
                        <p class="text-xl font-extrabold text-amber-600 mt-1" x-text="containerSummary ? money(containerSummary.deposit_held) : '-'"></p>
                        <p class="text-[10px] text-slate-400" x-text="t('refundable_liability')">Refundable (liability)</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-3.5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400" x-text="t('customers_holding')">Customers Holding</p>
                        <p class="text-xl font-extrabold text-slate-800 dark:text-white mt-1" x-text="containerSummary ? containerSummary.customers_holding : '-'"></p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-3.5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400" x-text="t('returned_this_month')">Returned This Month</p>
                        <p class="text-xl font-extrabold text-emerald-600 mt-1" x-text="containerSummary ? containerSummary.returned_this_month : '-'"></p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-3.5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm col-span-2 lg:col-span-1">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400" x-text="t('forfeit_income_this_month')">Forfeit Income (Month)</p>
                        <p class="text-xl font-extrabold text-slate-800 dark:text-white mt-1" x-text="containerSummary ? money(containerSummary.forfeit_income_this_month) : '-'"></p>
                        <p class="text-[10px] text-slate-400" x-text="t('damage_and_lost')">Damage deductions & lost</p>
                    </div>
                </div>

                {{-- Per-type position --}}
                <div class="flex flex-wrap gap-2">
                    <template x-for="type in (containerSummary ? containerSummary.types : [])" :key="type.id">
                        <div class="px-3 py-2 bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-gray-700 text-xs flex items-center gap-2">
                            <span class="font-bold text-slate-800 dark:text-white" x-text="type.name"></span>
                            <span class="text-amber-600 font-semibold"><span x-text="type.with_customers"></span> <span x-text="t('out')">out</span></span>
                            <template x-if="type.in_shop !== null">
                                <span class="text-emerald-600 font-semibold"><span x-text="type.in_shop"></span> <span x-text="t('in_shop')">in shop</span></span>
                            </template>
                            <template x-if="type.lost > 0">
                                <span class="text-rose-600 font-semibold"><span x-text="type.lost"></span> <span x-text="t('lost')">lost</span></span>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Customers holding containers --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200" x-text="t('customers_with_containers')">Customers with Containers</h4>
                        <input type="text" x-model="containerSearch" @input="containerCustomersPage = 1" :placeholder="t('search_customer_placeholder')"
                            class="w-full sm:w-64 px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-xs dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-slate-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-gray-700/50">
                                    <th class="px-3 py-2 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('customer')">Customer</th>
                                    <th class="px-3 py-2 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('containers')">Containers</th>
                                    <th class="px-3 py-2 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('deposit_held')">Deposit Held</th>
                                    <th class="px-3 py-2 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('since')">Since</th>
                                    <th class="px-3 py-2 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('due')">Due</th>
                                    <th class="px-3 py-2 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('actions')">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                                <template x-if="containersLoading && containerCustomers.length === 0">
                                    <tr><td colspan="6" class="text-center py-6"><div class="inline-block animate-spin rounded-full h-7 w-7 border-3 border-primary border-t-transparent"></div></td></tr>
                                </template>
                                <template x-for="cust in filteredContainerCustomers().slice((containerCustomersPage - 1) * containerCustomersPerPage, containerCustomersPage * containerCustomersPerPage)" :key="cust.customer_id">
                                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                                        <td class="px-3 py-2.5 text-xs">
                                            <button @click="openContainerCustomer(cust.customer_id)" class="font-bold text-slate-800 dark:text-white hover:text-primary text-left" x-text="cust.name"></button>
                                            <p class="text-[10px] text-slate-400 font-mono" x-text="cust.mobile || ''"></p>
                                        </td>
                                        <td class="px-3 py-2.5 text-xs">
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="h in cust.holdings" :key="h.container_type_id">
                                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 font-semibold text-[11px]" x-text="h.pending + ' ' + h.name"></span>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 text-xs text-right font-bold text-slate-800 dark:text-white whitespace-nowrap" x-text="money(cust.deposit_held)"></td>
                                        <td class="px-3 py-2.5 text-xs text-slate-500 whitespace-nowrap" x-text="formatDate(cust.since)"></td>
                                        <td class="px-3 py-2.5 text-xs text-right whitespace-nowrap" :class="cust.due_amount > 0 ? 'text-rose-600 font-bold' : 'text-slate-400'" x-text="money(cust.due_amount)"></td>
                                        <td class="px-3 py-2.5 text-xs text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-1">
                                                <button @click="openContainerEntryModal('return', cust.customer_id)" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold" x-text="t('return')">Return</button>
                                                <button @click="openContainerEntryModal('give', cust.customer_id)" class="px-2.5 py-1 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-lg text-[11px] font-bold" x-text="t('give')">Give</button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!containersLoading && filteredContainerCustomers().length === 0">
                                    <tr><td colspan="6" class="text-center text-slate-400 py-6 text-xs" x-text="t('no_containers_with_customers')">No containers are with customers right now.</td></tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <x-pagination currentPage="containerCustomersPage" totalItems="filteredContainerCustomers().length" perPage="containerCustomersPerPage" loading="containersLoading" />
                </div>
            </div>

            {{-- ===== HISTORY ===== --}}
            <div x-show="containerTab === 'history' && containerTypes.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                <div class="p-4 grid grid-cols-2 md:grid-cols-5 gap-2">
                    <input type="text" x-model="containerEntryFilters.search" @input.debounce.400ms="loadContainerEntries(1)" :placeholder="t('search') + '...'"
                        class="col-span-2 md:col-span-2 px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-xs dark:bg-gray-700 dark:text-white">
                    <select x-model="containerEntryFilters.type" @change="loadContainerEntries(1)"
                        class="px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-xs dark:bg-gray-700 dark:text-white">
                        <option value="" x-text="t('all_types') || 'All'">All</option>
                        <template x-for="type in ['give', 'return', 'exchange', 'opening', 'stock', 'reversal']" :key="type">
                            <option :value="type" x-text="containerEntryTypeLabel(type)"></option>
                        </template>
                    </select>
                    <input type="date" x-model="containerEntryFilters.start_date" @change="loadContainerEntries(1)"
                        class="px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-xs dark:bg-gray-700 dark:text-white">
                    <input type="date" x-model="containerEntryFilters.end_date" @change="loadContainerEntries(1)"
                        class="px-3 py-1.5 border border-slate-300 dark:border-gray-600 rounded-xl text-xs dark:bg-gray-700 dark:text-white">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-slate-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-700/50">
                                <th class="px-3 py-2 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('date')">Date</th>
                                <th class="px-3 py-2 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('entry_no')">Entry No</th>
                                <th class="px-3 py-2 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('customer')">Customer</th>
                                <th class="px-3 py-2 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('type')">Type</th>
                                <th class="px-3 py-2 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('containers')">Containers</th>
                                <th class="px-3 py-2 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('amount')">Amount</th>
                                <th class="px-3 py-2 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('actions')">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                            <template x-for="entry in containerEntries" :key="entry.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50" :class="entry.reversed_at ? 'opacity-60' : ''">
                                    <td class="px-3 py-2.5 text-xs text-slate-500 whitespace-nowrap" x-text="formatDate(entry.entry_date)"></td>
                                    <td class="px-3 py-2.5 text-xs font-mono font-semibold text-slate-700 dark:text-slate-200 whitespace-nowrap">
                                        <span x-text="entry.entry_number"></span>
                                        <template x-if="entry.sale"><p class="text-[10px] text-slate-400" x-text="entry.sale.sale_number"></p></template>
                                    </td>
                                    <td class="px-3 py-2.5 text-xs font-semibold text-slate-800 dark:text-white" x-text="entry.customer ? entry.customer.name : '-'"></td>
                                    <td class="px-3 py-2.5 text-xs whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="containerEntryTypeClass(entry.type)" x-text="containerEntryTypeLabel(entry.type)"></span>
                                        <template x-if="entry.reversed_at"><span class="ml-1 text-[10px] font-bold text-rose-600" x-text="t('reversed')">Reversed</span></template>
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-slate-700 dark:text-slate-300" x-text="containerEntrySummary(entry)"></td>
                                    <td class="px-3 py-2.5 text-xs text-right whitespace-nowrap">
                                        <span class="font-semibold" x-text="containerNetText(entry)"></span>
                                        <template x-if="parseFloat(entry.net_amount) !== 0"><p class="text-[10px] text-slate-400" x-text="containerMethodLabel(entry.settlement_method)"></p></template>
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-right whitespace-nowrap">
                                        <button @click="openContainerEntryDetail(entry)" class="px-2.5 py-1 bg-slate-100 dark:bg-gray-700 hover:bg-slate-200 rounded-lg text-[11px] font-bold text-slate-700 dark:text-slate-200" x-text="t('view')">View</button>
                                        <template x-if="canReverseContainerEntry(entry)">
                                            <button @click="reverseContainerEntry(entry)" class="px-2.5 py-1 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-600 hover:text-white text-rose-600 rounded-lg text-[11px] font-bold" x-text="t('reverse')">Reverse</button>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="containerEntries.length === 0">
                                <tr><td colspan="7" class="text-center text-slate-400 py-6 text-xs" x-text="t('no_container_entries')">No container entries yet.</td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="containerEntriesMeta.last_page > 1" class="px-4 py-3 border-t border-slate-200 dark:border-gray-700 flex items-center justify-between text-xs">
                    <span class="text-slate-500"><span x-text="containerEntriesMeta.total"></span> <span x-text="t('entries') || 'entries'"></span></span>
                    <div class="flex items-center gap-1">
                        <button @click="loadContainerEntries(containerEntriesMeta.current_page - 1)" :disabled="containerEntriesMeta.current_page <= 1"
                            class="px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg font-bold disabled:opacity-50">‹</button>
                        <span class="px-2 font-bold" x-text="containerEntriesMeta.current_page + ' / ' + containerEntriesMeta.last_page"></span>
                        <button @click="loadContainerEntries(containerEntriesMeta.current_page + 1)" :disabled="containerEntriesMeta.current_page >= containerEntriesMeta.last_page"
                            class="px-3 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg font-bold disabled:opacity-50">›</button>
                    </div>
                </div>
            </div>

            {{-- ===== CONTAINER TYPES ===== --}}
            <div x-show="containerTab === 'types'" class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                <div class="p-4 flex items-center justify-between gap-2">
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200" x-text="t('container_types')">Container Types</h4>
                        <p class="text-[11px] text-slate-500" x-text="t('container_types_hint')">Deposit rate applies to containers given from now on. Link types to products in the product form.</p>
                    </div>
                    <button @click="openContainerTypeModal()" class="px-3 py-2 bg-primary text-white rounded-xl text-xs font-bold shrink-0" x-text="'+ ' + t('add_container_type')">+ Add Container Type</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-slate-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-700/50">
                                <th class="px-3 py-2 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('name')">Name</th>
                                <th class="px-3 py-2 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('deposit_per_unit')">Deposit / Unit</th>
                                <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('total_owned')">Total Owned</th>
                                <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('with_customers')">With Customers</th>
                                <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('in_shop')">In Shop</th>
                                <th class="px-3 py-2 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('lost')">Lost</th>
                                <th class="px-3 py-2 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="t('actions')">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                            <template x-for="type in containerTypes" :key="type.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                                    <td class="px-3 py-2.5 text-xs font-bold text-slate-800 dark:text-white" x-text="type.name"></td>
                                    <td class="px-3 py-2.5 text-xs text-right font-semibold" x-text="money(type.deposit_amount)"></td>
                                    <td class="px-3 py-2.5 text-xs text-center" x-text="type.total_owned || t('not_tracked')"></td>
                                    <td class="px-3 py-2.5 text-xs text-center font-semibold text-amber-600" x-text="type.with_customers"></td>
                                    <td class="px-3 py-2.5 text-xs text-center font-semibold text-emerald-600" x-text="type.in_shop === null ? '-' : type.in_shop"></td>
                                    <td class="px-3 py-2.5 text-xs text-center text-rose-600" x-text="type.lost"></td>
                                    <td class="px-3 py-2.5 text-xs text-right whitespace-nowrap">
                                        <button @click="openContainerStockModal(type)" class="px-2.5 py-1 bg-slate-100 dark:bg-gray-700 hover:bg-slate-200 rounded-lg text-[11px] font-bold text-slate-700 dark:text-slate-200" x-text="t('adjust_stock')">± Stock</button>
                                        <button @click="openContainerTypeModal(type)" class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-600 hover:text-white text-blue-700 rounded-lg text-[11px] font-bold" x-text="t('edit')">Edit</button>
                                        <button @click="deleteContainerType(type)" class="px-2.5 py-1 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-600 hover:text-white text-rose-600 rounded-lg text-[11px] font-bold" x-text="t('delete')">Delete</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </template>
</div>

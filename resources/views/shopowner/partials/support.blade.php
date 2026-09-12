{{-- SUPPORT TICKETS PANEL --}}
<div x-show="page === 'support'" class="space-y-4" x-cloak>

    {{-- Top Banner / Header Card --}}
    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/50 flex items-center justify-center text-teal-600 dark:text-teal-400 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-slate-800 dark:text-white" x-text="t('support_tickets') || 'Support Tickets'">Support Tickets</h3>
                <p class="text-xs text-slate-400 mt-0.5" x-text="t('support_subtitle') || 'Submit help requests, track issue resolutions, and view replies from support team.'">
                    Submit help requests, track issue resolutions, and view replies from support team.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" @click="loadSupportTickets()" class="p-2.5 rounded-xl border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all" title="Refresh">
                <svg class="w-4 h-4" :class="supportTicketsLoading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </button>
            <button type="button" @click="openNewTicketModal()" class="px-4 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span x-text="t('create_ticket') || 'Create Support Ticket'">Create Support Ticket</span>
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400" x-text="t('total') || 'Total Tickets'">Total Tickets</span>
            <p class="text-2xl font-extrabold text-slate-800 dark:text-white mt-1" x-text="supportTickets.length"></p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-500" x-text="t('open_tickets') || 'Open Tickets'">Open Tickets</span>
            <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1" x-text="supportTickets.filter(t => t.status === 'open').length"></p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-500" x-text="t('in_progress_tickets') || 'In Progress'">In Progress</span>
            <p class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1" x-text="supportTickets.filter(t => ['inProgress', 'pending'].includes(t.status)).length"></p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-500" x-text="t('resolved_tickets') || 'Resolved'">Resolved</span>
            <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1" x-text="supportTickets.filter(t => ['resolved', 'closed'].includes(t.status)).length"></p>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" x-model="supportFilter.search" :placeholder="t('search_placeholder') || 'Search tickets...'"
                class="block w-full pl-9 pr-3 py-2 border border-slate-200 dark:border-gray-600 rounded-xl text-xs dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select x-model="supportFilter.status" class="px-3 py-2 border border-slate-200 dark:border-gray-600 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 dark:bg-gray-700 focus:outline-none cursor-pointer">
                <option value="">All Statuses</option>
                <option value="open">Open</option>
                <option value="inProgress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>
        </div>
    </div>

    {{-- Tickets Table / List --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-gray-700/50 border-b border-slate-100 dark:border-gray-700 text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                        <th class="px-6 py-3.5">Ticket ID</th>
                        <th class="px-6 py-3.5">Subject & Details</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Reply Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-700/50 text-xs">
                    <template x-for="tkt in filteredSupportTickets()" :key="tkt.id">
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-extrabold text-slate-800 dark:text-white" x-text="'#' + tkt.id"></span>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="formatDate(tkt.created_at)"></div>
                            </td>
                            <td class="px-6 py-4 max-w-md">
                                <div class="font-bold text-slate-800 dark:text-slate-100 truncate" x-text="tkt.subject"></div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1" x-text="tkt.message"></div>
                                <template x-if="tkt.screenshot">
                                    <div class="inline-flex items-center gap-1 mt-1 text-[10px] text-teal-600 dark:text-teal-400 font-semibold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        <span>Attachment attached</span>
                                    </div>
                                </template>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase"
                                    :class="{
                                        'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300': tkt.status === 'open',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300': ['inProgress', 'pending'].includes(tkt.status),
                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300': ['resolved', 'closed'].includes(tkt.status)
                                    }">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="{
                                            'bg-amber-500': tkt.status === 'open',
                                            'bg-blue-500': ['inProgress', 'pending'].includes(tkt.status),
                                            'bg-emerald-500': ['resolved', 'closed'].includes(tkt.status)
                                        }"></span>
                                    <span x-text="tkt.status === 'inProgress' ? 'In Progress' : (tkt.status === 'open' ? 'Open' : (tkt.status === 'resolved' ? 'Resolved' : tkt.status))"></span>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <template x-if="tkt.admin_reply">
                                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span>Replied</span>
                                    </span>
                                </template>
                                <template x-if="!tkt.admin_reply">
                                    <span class="text-slate-400 text-xs italic">Waiting</span>
                                </template>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="openTicketDetails(tkt)" class="px-3 py-1.5 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                                        <span x-text="tkt.admin_reply ? (t('view_ticket') || 'View Reply') : (t('ticket_details') || 'Details')"></span>
                                    </button>
                                    <template x-if="tkt.status === 'open' && !tkt.admin_reply">
                                        <button type="button" @click="deleteTicket(tkt.id)" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition-colors" title="Delete Ticket">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredSupportTickets().length === 0">
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-gray-700/50 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    </div>
                                    <h5 class="text-sm font-bold text-slate-700 dark:text-slate-300" x-text="t('no_tickets_found') || 'No support tickets found'">No support tickets found</h5>
                                    <p class="text-xs text-slate-400 mt-1 max-w-sm" x-text="t('no_tickets_desc') || 'Have questions or need help? Submit a ticket and our support team will assist you.'">
                                        Have questions or need help? Submit a ticket and our support team will assist you.
                                    </p>
                                    <button type="button" @click="openNewTicketModal()" class="mt-4 px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        <span x-text="t('create_ticket') || 'Create Support Ticket'">Create Support Ticket</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>

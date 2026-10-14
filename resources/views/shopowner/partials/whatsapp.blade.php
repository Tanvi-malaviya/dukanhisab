{{-- WHATSAPP MESSAGING PANEL: Compact, high-efficiency design matching DukanHisab UI --}}
<style>
.app-toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}
.app-toggle input {
    opacity: 0 !important;
    width: 0 !important;
    height: 0 !important;
    position: absolute !important;
    margin: 0 !important;
    padding: 0 !important;
    pointer-events: none;
}
.app-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #CBD5E1;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 9999px;
}
.app-toggle-slider::before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: #FFFFFF;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
}
.app-toggle input:checked + .app-toggle-slider {
    background-color: #0F766E !important;
}
.app-toggle input:checked + .app-toggle-slider::before {
    transform: translateX(20px);
}
.dark .app-toggle-slider {
    background-color: #4B5563;
}
.dark .app-toggle input:checked + .app-toggle-slider {
    background-color: #0F766E !important;
}
.app-toggle.app-toggle-sm {
    width: 38px;
    height: 22px;
}
.app-toggle.app-toggle-sm .app-toggle-slider::before {
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
}
.app-toggle.app-toggle-sm input:checked + .app-toggle-slider::before {
    transform: translateX(16px);
}
</style>
<div x-show="page === 'whatsapp'" x-cloak x-data="whatsappPage()" @whatsapp-open.window="load()" @language-changed.window="load()" class="space-y-4">

    {{-- Top Header Bar: Clean white card, matching other DukanHisab panels --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-gray-700 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        
        {{-- Left: Icon & Title --}}
        <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-11 h-11 rounded-2xl bg-teal-50 dark:bg-teal-950/60 border border-teal-100 dark:border-teal-800/60 flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-6 h-6 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.585 1.961.947 2.801.947 3.179 0 5.766-2.587 5.767-5.766.001-3.182-2.585-5.734-5.772-5.734zm3.392 8.234c-.144.405-.837.774-1.17.824-.312.045-.698.071-2.029-.481-1.637-.678-2.678-2.348-2.759-2.457-.081-.109-.658-.876-.658-1.67 0-.793.415-1.183.562-1.344.148-.161.323-.202.43-.202.108 0 .216.002.31.007.1.005.234-.038.366.279.135.324.46 1.119.5 1.2.04.082.067.177.013.285-.054.108-.081.176-.162.27-.08.096-.17.213-.243.287-.081.082-.165.172-.071.334.094.162.42 6.94 1.121 1.564.701.624 1.294.817 1.474.908.181.09.288.077.396-.045.108-.122.46-.539.584-.723.121-.185.243-.153.405-.094.162.06 1.026.484 1.202.572.176.088.293.131.336.205.044.075.044.432-.1 8.37zM12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.662 1.435 5.176L2 22l4.981-1.306A9.957 9.957 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-800 dark:text-white tracking-tight truncate" x-text="wt('whatsapp_messages', 'WhatsApp Messages')"></h3>
                    <span x-show="wa.available" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 dark:bg-teal-950/60 text-primary dark:text-teal-300 border border-teal-200/70 dark:border-teal-800/50">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                        <span>Active</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-gray-600">
                        <span>Language:</span>
                        <strong class="uppercase text-primary dark:text-teal-300" x-text="currentLang"></strong>
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-xl" x-text="wt('whatsapp_messages_desc', 'Automated invoices, receipts & due reminders to customers & suppliers.')"></p>
            </div>
        </div>

        {{-- Right: Credit Stat Badge & Buy Trigger (No duplicate Save button) --}}
        <div class="flex items-center gap-2 shrink-0">
            <div class="bg-slate-50 dark:bg-gray-700/60 border border-slate-200 dark:border-gray-600 rounded-xl px-3 py-1.5 flex items-center gap-3">
                <div class="text-left">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400 leading-none" x-text="wt('wa_credits_left', 'Credits Left')"></p>
                    <p class="text-base sm:text-lg font-black text-slate-800 dark:text-white leading-tight mt-0.5" x-text="wa.balance.toLocaleString()"></p>
                </div>
                <button type="button" @click="activeTab = 'packs'"
                    class="px-3 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-lg text-xs font-bold shadow-xs transition-all shrink-0 cursor-pointer">
                    + Buy Packs
                </button>
            </div>
        </div>
    </div>

    {{-- System Alerts (Clean, soft notification bars) --}}
    <div class="space-y-2">
        <div x-show="!wa.available" class="p-3 rounded-xl bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-300 text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span x-text="wt('wa_unavailable', 'WhatsApp messaging is temporarily paused. Your settings are saved and will resume automatically.')"></span>
        </div>

        <div x-show="!wa.upiId" class="p-3 rounded-xl bg-teal-50/70 dark:bg-teal-950/30 border border-teal-200/70 dark:border-teal-800/50 text-teal-900 dark:text-teal-200 text-xs font-medium flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
                <span x-text="wt('wa_add_upi', 'Add your shop UPI ID to enable the “Pay Now” direct payment link on WhatsApp due reminders.')"></span>
            </span>
            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" @click="openUpiModal()" class="px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Add UPI ID</span>
                </button>
                <button type="button" @click="goToShopSettingsUpi()" class="text-xs font-bold underline text-primary dark:text-teal-300 hover:text-teal-950 dark:hover:text-white cursor-pointer" x-text="t('settings')"></button>
            </div>
        </div>

        <div x-show="wa.upiId" class="px-3.5 py-2 rounded-xl bg-teal-50/40 dark:bg-teal-950/20 border border-teal-200/60 dark:border-teal-800/40 text-teal-900 dark:text-teal-200 text-xs flex items-center justify-between gap-2">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                <span>Active UPI ID: <strong class="font-mono text-slate-800 dark:text-white" x-text="wa.upiId"></strong> ("Pay Now" button active on Due Reminders)</span>
            </span>
            <button type="button" @click="openUpiModal()" class="text-[11px] font-bold text-primary dark:text-teal-300 hover:underline cursor-pointer">Change</button>
        </div>
    </div>

    {{-- Main Navigation Sub-Tabs (High Density Switcher matching Brand Theme) --}}
    <div class="bg-white dark:bg-gray-800 p-1.5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-xs flex items-center gap-1.5 overflow-x-auto no-scrollbar">
        <button type="button" @click="activeTab = 'automations'"
            class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer shrink-0"
            :class="activeTab === 'automations' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-gray-700'">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <span>Automation Rules</span>
        </button>

        <button type="button" @click="activeTab = 'packs'"
            class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer shrink-0"
            :class="activeTab === 'packs' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-gray-700'">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            <span>Buy Packs & Wallet</span>
            <span class="px-1.5 py-0.2 rounded-md text-[10px] font-extrabold"
                  :class="activeTab === 'packs' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-gray-700 text-slate-500 dark:text-slate-400'"
                  x-text="wa.packs.length"></span>
        </button>

        <button type="button" @click="activeTab = 'history'"
            class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer shrink-0"
            :class="activeTab === 'history' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-gray-700'">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Delivery Logs</span>
        </button>

        <button type="button" @click="activeTab = 'claims'"
            class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer shrink-0"
            :class="activeTab === 'claims' ? 'bg-primary text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-gray-700'">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Payment Claims</span>
            <span x-show="wa.claims.length" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-white animate-pulse" x-text="wa.claims.length"></span>
        </button>
    </div>

    {{-- Loading Indicator --}}
    <template x-if="wa.loading && !wa.loaded">
        <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-3 border-primary border-t-transparent"></div>
            <p class="text-xs text-slate-400 mt-2 font-medium">Loading WhatsApp configurations...</p>
        </div>
    </template>

    <template x-if="wa.loaded">
        <div class="space-y-4">

            {{-- ======================================================== --}}
            {{-- TAB 1: AUTOMATIONS (SIDE-BY-SIDE 2-COLUMN COMPACT LAYOUT) --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'automations'" class="space-y-4">
                
                {{-- 2-Column Responsive Grid: Customers (Left) & Suppliers (Right) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                    
                    {{-- Column 1: Customer Automations --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-gray-700 bg-slate-50/70 dark:bg-gray-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-primary dark:text-teal-300 border border-teal-200/60 dark:border-teal-800/50 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </span>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100" x-text="wt('wa_to_customers', 'Messages to Customers')"></h4>
                                    <p class="text-[11px] text-slate-400">Automatic messages sent on sale & payment</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-50 dark:bg-teal-900/30 text-primary dark:text-teal-300 border border-teal-100 dark:border-teal-800/50">
                                3 Events
                            </span>
                        </div>

                        <div class="p-3.5 space-y-2.5 divide-y divide-slate-100 dark:divide-gray-700/60">
                            <template x-for="ev in wa.events.filter(e => e.recipient === 'customer')" :key="ev.key">
                                <div class="pt-2.5 first:pt-0 space-y-2.5">
                                    
                                    {{-- Event Header Row with Switch --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="text-xs font-bold text-slate-800 dark:text-white" x-text="wt('wa_event_' + ev.key, ev.label)"></p>
                                                <span x-show="ev.has_document" class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-teal-50 dark:bg-teal-950 text-primary dark:text-teal-300 border border-teal-200 dark:border-teal-800">PDF</span>
                                                <span x-show="ev.has_pay_button" class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-sky-50 dark:bg-sky-950 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">Pay Now</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug"
                                                x-text="!ev.template_available ? wt('wa_not_available', 'Template not configured yet') : (ev.scheduled ? wt('wa_scheduled_info', 'Sent automatically on chosen days & time') : wt('wa_sent_instantly', 'Sent automatically right after action'))"></p>
                                        </div>

                                        {{-- Toggle Switch --}}
                                        <label class="app-toggle app-toggle-sm shrink-0 mt-0.5"
                                            :class="{ 'opacity-40 pointer-events-none cursor-not-allowed': !ev.template_available, 'cursor-pointer': ev.template_available }">
                                            <input type="checkbox" x-model="ev.enabled" :disabled="!ev.template_available">
                                            <span class="app-toggle-slider"></span>
                                        </label>
                                    </div>

                                    {{-- Weekly schedule block (for due reminders) --}}
                                    <div x-show="ev.scheduled && ev.enabled" class="bg-slate-50 dark:bg-gray-700/40 rounded-xl p-2.5 border border-slate-200/70 dark:border-gray-600/50 space-y-2">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1"
                                                x-text="wt('wa_days', 'Schedule Days') + ' (max ' + wa.maxDays + ')'"></label>
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="day in days" :key="day">
                                                    <button type="button" @click="toggleDay(ev, day)"
                                                        :class="(ev.schedule_days || []).includes(day) ? 'bg-primary text-white border-primary shadow-2xs' : 'bg-white dark:bg-gray-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-gray-600 hover:bg-slate-100 dark:hover:bg-gray-700'"
                                                        class="px-2 py-1 rounded-lg border text-[11px] font-bold capitalize transition-all cursor-pointer" x-text="day"></button>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1" x-text="wt('wa_time', 'Send Time')"></label>
                                                <input type="time" x-model="ev.schedule_time" class="block w-full px-2.5 py-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded-lg text-xs dark:text-white">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1" x-text="wt('wa_min_due', 'Min Due (₹)')"></label>
                                                <input type="number" min="0" step="1" x-model.number="ev.min_due_amount" class="block w-full px-2.5 py-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded-lg text-xs dark:text-white">
                                            </div>
                                        </div>

                                        <p x-show="ev.last_run_result" class="text-[10px] text-slate-500 dark:text-slate-400"
                                            x-text="'Last sent: ' + new Date(ev.last_run_at).toLocaleString() + ' (' + ev.last_run_result.sent + ' sent)'"></p>
                                    </div>

                                    {{-- Collapsible Preview Bubble --}}
                                    <div x-show="ev.preview" x-data="{ open: false }">
                                        <button type="button" @click="open = !open" class="text-[11px] font-semibold text-primary dark:text-teal-300 hover:underline flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <span x-text="open ? wt('wa_hide_preview', 'Hide Preview') : wt('wa_show_preview', 'View Message Preview')"></span>
                                        </button>
                                        <div x-show="open" x-transition class="mt-1.5 rounded-xl rounded-tl-none bg-[#eef9f2] dark:bg-teal-950/40 border border-teal-200/80 dark:border-teal-800/40 p-2.5 text-xs text-slate-800 dark:text-slate-200 shadow-xs max-w-sm">
                                            <p class="text-[10px] font-black text-primary dark:text-teal-300 mb-1 flex items-center justify-between">
                                                <span>DukanHisab Notification</span>
                                                <span class="text-[9px] text-slate-400 font-semibold" x-text="'Preview (' + (currentLang === 'gu' ? 'ગુજરાતી' : (currentLang === 'hi' ? 'हिंदी' : 'English')) + ')'"></span>
                                            </p>
                                            <p x-show="ev.has_document" class="mb-1 text-primary dark:text-teal-300 font-semibold text-[11px]">📄 INV-1024.pdf</p>
                                            <p class="whitespace-pre-line text-[11px] leading-relaxed" x-text="ev.preview"></p>
                                            <p x-show="ev.has_pay_button" class="mt-1.5 pt-1.5 border-t border-teal-800/10 text-center font-bold text-sky-700 dark:text-sky-300 text-[11px]">💳 Pay Now</p>
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Column 2: Supplier Automations --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-gray-700 bg-slate-50/70 dark:bg-gray-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-primary dark:text-teal-300 border border-teal-200/60 dark:border-teal-800/50 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </span>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100" x-text="wt('wa_to_suppliers', 'Messages to Suppliers')"></h4>
                                    <p class="text-[11px] text-slate-400">Automatic updates sent on purchase & payout</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-50 dark:bg-teal-900/30 text-primary dark:text-teal-300 border border-teal-100 dark:border-teal-800/50">
                                3 Events
                            </span>
                        </div>

                        <div class="p-3.5 space-y-2.5 divide-y divide-slate-100 dark:divide-gray-700/60">
                            <template x-for="ev in wa.events.filter(e => e.recipient === 'supplier')" :key="ev.key">
                                <div class="pt-2.5 first:pt-0 space-y-2.5">
                                    
                                    {{-- Event Header Row with Switch --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="text-xs font-bold text-slate-800 dark:text-white" x-text="wt('wa_event_' + ev.key, ev.label)"></p>
                                                <span x-show="ev.has_document" class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-teal-50 dark:bg-teal-950 text-primary dark:text-teal-300 border border-teal-200 dark:border-teal-800">PDF</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug"
                                                x-text="!ev.template_available ? wt('wa_not_available', 'Template not configured yet') : (ev.scheduled ? wt('wa_scheduled_info', 'Sent automatically on chosen days & time') : wt('wa_sent_instantly', 'Sent automatically on purchase/payment'))"></p>
                                        </div>

                                        {{-- Toggle Switch --}}
                                        <label class="app-toggle app-toggle-sm shrink-0 mt-0.5"
                                            :class="{ 'opacity-40 pointer-events-none cursor-not-allowed': !ev.template_available, 'cursor-pointer': ev.template_available }">
                                            <input type="checkbox" x-model="ev.enabled" :disabled="!ev.template_available">
                                            <span class="app-toggle-slider"></span>
                                        </label>
                                    </div>

                                    {{-- Weekly schedule block (for supplier dues) --}}
                                    <div x-show="ev.scheduled && ev.enabled" class="bg-slate-50 dark:bg-gray-700/40 rounded-xl p-2.5 border border-slate-200/70 dark:border-gray-600/50 space-y-2">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1"
                                                x-text="wt('wa_days', 'Schedule Days') + ' (max ' + wa.maxDays + ')'"></label>
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="day in days" :key="day">
                                                    <button type="button" @click="toggleDay(ev, day)"
                                                        :class="(ev.schedule_days || []).includes(day) ? 'bg-primary text-white border-primary shadow-2xs' : 'bg-white dark:bg-gray-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-gray-600 hover:bg-slate-100 dark:hover:bg-gray-700'"
                                                        class="px-2 py-1 rounded-lg border text-[11px] font-bold capitalize transition-all cursor-pointer" x-text="day"></button>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1" x-text="wt('wa_time', 'Send Time')"></label>
                                                <input type="time" x-model="ev.schedule_time" class="block w-full px-2.5 py-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded-lg text-xs dark:text-white">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1" x-text="wt('wa_min_due', 'Min Due (₹)')"></label>
                                                <input type="number" min="0" step="1" x-model.number="ev.min_due_amount" class="block w-full px-2.5 py-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded-lg text-xs dark:text-white">
                                            </div>
                                        </div>

                                        <p x-show="ev.last_run_result" class="text-[10px] text-slate-500 dark:text-slate-400"
                                            x-text="'Last sent: ' + new Date(ev.last_run_at).toLocaleString() + ' (' + ev.last_run_result.sent + ' sent)'"></p>
                                    </div>

                                    {{-- Collapsible Preview Bubble --}}
                                    <div x-show="ev.preview" x-data="{ open: false }">
                                        <button type="button" @click="open = !open" class="text-[11px] font-semibold text-primary dark:text-teal-300 hover:underline flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <span x-text="open ? wt('wa_hide_preview', 'Hide Preview') : wt('wa_show_preview', 'View Message Preview')"></span>
                                        </button>
                                        <div x-show="open" x-transition class="mt-1.5 rounded-xl rounded-tl-none bg-[#eef9f2] dark:bg-teal-950/40 border border-teal-200/80 dark:border-teal-800/40 p-2.5 text-xs text-slate-800 dark:text-slate-200 shadow-xs max-w-sm">
                                            <p class="text-[10px] font-black text-primary dark:text-teal-300 mb-1 flex items-center justify-between">
                                                <span>DukanHisab Notification</span>
                                                <span class="text-[9px] text-slate-400 font-semibold" x-text="'Preview (' + (currentLang === 'gu' ? 'ગુજરાતી' : (currentLang === 'hi' ? 'हिंदी' : 'English')) + ')'"></span>
                                            </p>
                                            <p x-show="ev.has_document" class="mb-1 text-primary dark:text-teal-300 font-semibold text-[11px]">📄 Purchase-PO-1024.pdf</p>
                                            <p class="whitespace-pre-line text-[11px] leading-relaxed" x-text="ev.preview"></p>
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                {{-- Bottom Action Bar: ONLY SINGLE SAVE BUTTON ON THE PAGE --}}
                <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        <span class="font-bold text-slate-800 dark:text-slate-200">Ready?</span> Changes apply immediately once saved.
                    </p>
                    <button type="button" @click="save()" :disabled="wa.saving"
                        class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-black shadow-md hover:shadow-lg transition-all disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2">
                        <template x-if="wa.saving">
                            <div class="inline-block animate-spin rounded-full h-3.5 w-3.5 border-2 border-white border-t-transparent"></div>
                        </template>
                        <svg x-show="!wa.saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span x-text="wa.saving ? wt('saving', 'Saving...') : wt('wa_save_settings', 'Save WhatsApp Settings')"></span>
                    </button>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 2: BUY PACKS & WALLET LEDGER                          --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'packs'" class="space-y-4">
                
                {{-- Pack Buying Cards --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-4.5 space-y-3.5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-gray-700 pb-3">
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-100" x-text="wt('wa_buy_messages', 'Buy WhatsApp Message Credits')"></h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="wt('wa_one_credit', '1 credit = 1 WhatsApp message. Credits never expire. Failed messages are auto-refunded.')"></p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-teal-50 dark:bg-teal-950 text-primary dark:text-teal-300 border border-teal-200 dark:border-teal-800 shrink-0">
                            Current: <strong x-text="wa.balance.toLocaleString()"></strong> Credits
                        </span>
                    </div>

                    <p x-show="wa.packs.length === 0" class="text-xs text-slate-400 py-6 text-center" x-text="wt('wa_no_packs', 'No message packs available right now.')"></p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                        <template x-for="pack in wa.packs" :key="pack.id">
                            <div class="rounded-2xl border border-slate-200 dark:border-gray-700 p-4 bg-slate-50/50 dark:bg-gray-800/50 hover:border-primary dark:hover:border-primary transition-all flex flex-col justify-between shadow-2xs group">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-extrabold text-slate-800 dark:text-white" x-text="pack.name"></p>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="'₹' + (pack.price / pack.credits).toFixed(2) + '/msg'"></span>
                                    </div>
                                    <p class="text-2xl font-black text-primary dark:text-teal-300" x-text="Number(pack.credits).toLocaleString() + ' ' + wt('wa_messages', 'messages')"></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium" x-text="'One-time payment of ₹' + Number(pack.price).toFixed(2)"></p>
                                </div>

                                <button type="button" @click="buyPack(pack)" :disabled="wa.buyingId !== null"
                                    class="mt-3.5 w-full py-2.5 rounded-xl bg-primary hover:bg-primary-hover group-hover:shadow-md text-white text-xs font-black transition-all disabled:opacity-60 cursor-pointer flex items-center justify-center gap-2">
                                    <template x-if="wa.buyingId === pack.id">
                                        <div class="inline-block animate-spin rounded-full h-3.5 w-3.5 border-2 border-white border-t-transparent"></div>
                                    </template>
                                    <span x-text="wa.buyingId === pack.id ? wt('processing', 'Processing...') : ('Buy for ₹' + Number(pack.price).toFixed(0))"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Credit Ledger Table --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-4.5 space-y-3" x-show="wa.ledger.length">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400" x-text="wt('wa_credit_history', 'Recent Credit Activity')"></h4>
                        <span class="text-[11px] text-slate-400 font-medium">Last 15 records</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-left text-[10px] uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-gray-700">
                                    <th class="pb-2">Date</th>
                                    <th class="pb-2 px-3">Description</th>
                                    <th class="pb-2 text-right">Change</th>
                                    <th class="pb-2 pl-3 text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-gray-700/60">
                                <template x-for="row in wa.ledger.slice(0, 15)" :key="row.id">
                                    <tr>
                                        <td class="py-2 text-slate-500 whitespace-nowrap text-[11px]" x-text="new Date(row.created_at).toLocaleDateString() + ' ' + new Date(row.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></td>
                                        <td class="py-2 px-3 text-slate-700 dark:text-slate-300" x-text="wt('wa_ledger_' + row.type, row.type) + (row.note ? ' — ' + row.note : '')"></td>
                                        <td class="py-2 text-right font-black" :class="row.credits > 0 ? 'text-primary dark:text-teal-400' : 'text-slate-500'" x-text="(row.credits > 0 ? '+' : '') + row.credits"></td>
                                        <td class="py-2 pl-3 text-right text-slate-400 font-mono text-[11px]" x-text="row.balance_after"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- ======================================================== --}}
            {{-- TAB 3: DELIVERY HISTORY LOGS                              --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'history'" class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-4.5 space-y-3.5">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 dark:border-gray-700 pb-3">
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-100" x-text="wt('wa_message_history', 'WhatsApp Delivery Logs')"></h4>
                        <p class="text-xs text-slate-400">Live delivery status reported via Meta Webhooks</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-[11px] font-bold text-slate-500">Filter:</label>
                        <select x-model="wa.historyStatus" @change="loadMessages(1)"
                            class="px-2.5 py-1 bg-slate-50 dark:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xl text-xs dark:text-white font-medium focus:ring-1 focus:ring-primary">
                            <option value="" x-text="wt('all', 'All Statuses')"></option>
                            <template x-for="st in ['sent', 'delivered', 'read', 'failed', 'queued']" :key="st">
                                <option :value="st" x-text="wt('wa_status_' + st, st)"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <p x-show="!wa.messages.length" class="text-xs text-slate-400 py-8 text-center" x-text="wt('wa_no_messages', 'No WhatsApp messages found.')"></p>

                <div class="overflow-x-auto" x-show="wa.messages.length">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-left text-[10px] uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-gray-700">
                                <th class="py-2" x-text="wt('date', 'Date & Time')"></th>
                                <th class="py-2 px-3" x-text="wt('wa_to', 'Recipient')"></th>
                                <th class="py-2 px-3" x-text="wt('wa_message', 'Message Event')"></th>
                                <th class="py-2 text-right" x-text="wt('status', 'Delivery Status')"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700/60">
                            <template x-for="m in wa.messages" :key="m.id">
                                <tr>
                                    <td class="py-2.5 text-slate-500 whitespace-nowrap text-[11px]" x-text="new Date(m.created_at).toLocaleString([], {dateStyle:'short', timeStyle:'short'})"></td>
                                    <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300">
                                        <span class="font-bold text-slate-800 dark:text-white" x-text="m.recipient_name || '—'"></span>
                                        <span class="block text-slate-400 font-mono text-[10px]" x-text="'+' + m.phone"></span>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300 font-medium" x-text="wt('wa_event_' + m.event, m.event_label)"></td>
                                    <td class="py-2.5 text-right">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase whitespace-nowrap"
                                            :class="{
                                                'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300': m.status === 'failed',
                                                'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300': m.status === 'read',
                                                'bg-teal-100 text-primary dark:bg-teal-950 dark:text-teal-300': m.status === 'delivered',
                                                'bg-slate-100 text-slate-600 dark:bg-gray-700 dark:text-slate-300': m.status === 'sent' || m.status === 'queued'
                                            }"
                                            x-text="wt('wa_status_' + m.status, m.status)"></span>
                                        <span x-show="m.status === 'failed' && m.error" class="block mt-0.5 text-[9px] text-rose-600 dark:text-rose-400 max-w-[200px] ml-auto truncate" :title="m.error" x-text="m.error"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Controls --}}
                <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-gray-700" x-show="wa.historyLastPage > 1">
                    <span class="text-xs text-slate-500" x-text="'Page ' + wa.historyPage + ' of ' + wa.historyLastPage"></span>
                    <div class="flex gap-1.5">
                        <button type="button" @click="loadMessages(wa.historyPage - 1)" :disabled="wa.historyPage <= 1"
                            class="px-3 py-1 rounded-lg border border-slate-200 dark:border-gray-600 text-xs font-bold disabled:opacity-40 cursor-pointer dark:text-slate-300">Previous</button>
                        <button type="button" @click="loadMessages(wa.historyPage + 1)" :disabled="wa.historyPage >= wa.historyLastPage"
                            class="px-3 py-1 rounded-lg border border-slate-200 dark:border-gray-600 text-xs font-bold disabled:opacity-40 cursor-pointer dark:text-slate-300">Next</button>
                    </div>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 4: PAYMENT CLAIMS                                     --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'claims'" class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-4.5 space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-gray-700 pb-3">
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-100" x-text="wt('wa_payment_claims', 'Customer Payment Claims')"></h4>
                        <p class="text-xs text-slate-400" x-text="wt('wa_payment_claims_desc', 'Customers who clicked Pay Now and submitted UTR transaction numbers.')"></p>
                    </div>
                    <span x-show="wa.claims.length" class="px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 text-xs font-black" x-text="wa.claims.length + ' Pending'"></span>
                </div>

                <div x-show="!wa.claims.length" class="py-8 text-center text-xs text-slate-400">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span x-text="wt('wa_no_claims', 'No pending payments waiting for confirmation.')"></span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-gray-700/60" x-show="wa.claims.length">
                    <template x-for="claim in wa.claims" :key="claim.id">
                        <div class="py-3 first:pt-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-800 dark:text-white" x-text="(claim.customer ? claim.customer.name : 'Customer') + ' — ₹' + Number(claim.claimed_amount).toFixed(2)"></p>
                                <p class="text-xs text-slate-500 font-mono mt-0.5" x-text="'UTR: ' + claim.claimed_utr + ' · ' + new Date(claim.claimed_at).toLocaleString()"></p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" @click="resolveClaim(claim, 'confirm')" :disabled="wa.resolvingId === claim.id"
                                    class="px-3.5 py-1.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold disabled:opacity-60 cursor-pointer shadow-xs" x-text="wt('wa_confirm', 'Confirm & Record')"></button>
                                <button type="button" @click="resolveClaim(claim, 'reject')" :disabled="wa.resolvingId === claim.id"
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-gray-700 hover:bg-slate-200 dark:hover:bg-gray-600 text-slate-600 dark:text-slate-300 text-xs font-semibold disabled:opacity-60 cursor-pointer" x-text="wt('wa_reject', 'Dismiss')"></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </template>

    {{-- MODERN UPI SETUP MODAL POPUP (Teleported to body for true full-screen overlay) --}}
    <template x-teleport="body">
        <div x-show="showUpiModal" x-cloak
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col"
                @click.outside="showUpiModal = false"
                @keydown.escape.window="showUpiModal = false">
                
                {{-- Modal Header --}}
                <div class="px-5 py-4 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-primary dark:text-teal-300 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </span>
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Shop UPI ID Setup</h4>
                            <p class="text-[11px] text-slate-400">Receive instant customer payments via WhatsApp</p>
                        </div>
                    </div>
                    <button type="button" @click="showUpiModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form @submit.prevent="saveUpiFromModal()" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5" for="modal-upi-input">
                            UPI ID (VPA Address)
                        </label>
                        <div class="relative">
                            <input type="text" id="modal-upi-input" x-model="upiForm.upi_id"
                                placeholder="e.g. mobile@upi, shop@okhdfcbank"
                                required
                                class="block w-full pl-9 pr-3 py-2 border border-slate-300 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <span class="absolute left-3 top-2.5 text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4 text-primary dark:text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2 leading-relaxed">
                            When you send due reminders on WhatsApp, customers receive a direct <strong>"💳 Pay Now"</strong> payment link that allows them to pay directly to this UPI ID via Google Pay, PhonePe, Paytm, or BHIM.
                        </p>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showUpiModal = false"
                            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-gray-600 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700 cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" :disabled="upiForm.saving"
                            class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-black shadow-md transition-all disabled:opacity-50 cursor-pointer flex items-center gap-2">
                            <template x-if="upiForm.saving">
                                <div class="inline-block animate-spin rounded-full h-3.5 w-3.5 border-2 border-white border-t-transparent"></div>
                            </template>
                            <span x-text="upiForm.saving ? 'Saving...' : 'Save UPI ID'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
    function whatsappPage() {
        return {
            activeTab: 'automations',
            showUpiModal: false,
            upiForm: { upi_id: '', saving: false },
            days: ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
            wa: { loading: false, loaded: false, saving: false, buyingId: null, resolvingId: null, available: true, balance: 0, lowBalance: false, upiId: null, maxDays: 2, events: [], packs: [], ledger: [], claims: [], messages: [], historyStatus: '', historyPage: 1, historyLastPage: 1 },

            init() {
                if (this.page === 'whatsapp') this.load();
            },

            // Translation with an English fallback for keys missing from the language file.
            wt(key, fallback) {
                const value = this.t(key);
                return value === key ? fallback : value;
            },

            applySettings(d) {
                this.wa.available = !!d.available;
                this.wa.balance = d.balance || 0;
                this.wa.lowBalance = !!d.low_balance;
                this.wa.upiId = d.upi_id;
                this.wa.maxDays = d.max_schedule_days || 2;
                this.wa.events = d.events || [];
            },

            openUpiModal() {
                this.upiForm.upi_id = this.wa.upiId || (this.shop && this.shop.upi_id) || '';
                this.upiForm.saving = false;
                this.showUpiModal = true;
                this.$nextTick(() => {
                    const el = document.getElementById('modal-upi-input');
                    if (el) el.focus();
                });
            },

            async saveUpiFromModal() {
                const clean = (this.upiForm.upi_id || '').trim();
                if (!clean) {
                    this.showToast('Please enter a valid UPI ID (e.g. mobile@upi).', 'warning');
                    return;
                }
                this.upiForm.saving = true;
                try {
                    const res = await fetch('/api/v1/whatsapp/upi', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            ...this.getHeaders(),
                        },
                        body: JSON.stringify({ upi_id: clean }),
                    });
                    const d = await res.json();
                    if (res.ok) {
                        this.wa.upiId = d.upi_id || clean;
                        if (this.shop) {
                            this.shop.upi_id = this.wa.upiId;
                            localStorage.setItem('shopowner_shop', JSON.stringify(this.shop));
                        }
                        this.showUpiModal = false;
                        this.showToast('UPI ID saved! "Pay Now" payment links are now active.');
                        this.load();
                    } else {
                        const first = d.errors ? Object.values(d.errors).flat()[0] : null;
                        this.showToast(first || d.message || 'Failed to save UPI ID', 'error');
                    }
                } catch (e) {
                    this.showToast('Could not save UPI ID', 'error');
                } finally {
                    this.upiForm.saving = false;
                }
            },

            goToShopSettingsUpi() {
                this.navigateTo('settings');
                setTimeout(() => {
                    const inp = document.getElementById('shop-settings-upi-input');
                    if (inp) {
                        inp.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        inp.focus();
                    }
                }, 250);
            },

            async load() {
                if (this.wa.loading || !this.shop) return;
                this.wa.loading = true;
                try {
                    const lang = this.currentLang || localStorage.getItem('dukanhisab_lang') || localStorage.getItem('locale') || 'en';
                    const [settings, wallet, claims] = await Promise.all([
                        fetch('/api/v1/whatsapp/settings?locale=' + encodeURIComponent(lang), { headers: this.getHeaders() }).then(r => r.json()),
                        fetch('/api/v1/whatsapp/wallet', { headers: this.getHeaders() }).then(r => r.json()),
                        fetch('/api/v1/whatsapp/payment-claims', { headers: this.getHeaders() }).then(r => r.json()),
                    ]);
                    this.applySettings(settings);
                    this.wa.claims = claims.pending || [];
                    this.loadMessages(1);
                    this.wa.packs = wallet.packs || [];
                    this.wa.ledger = wallet.ledger || [];
                    this.wa.loaded = true;
                } catch (e) {
                    this.showToast(this.wt('wa_load_failed', 'Could not load WhatsApp settings.'), 'error');
                } finally {
                    this.wa.loading = false;
                }
            },

            async loadMessages(page) {
                try {
                    const params = new URLSearchParams({ page });
                    if (this.wa.historyStatus) params.set('status', this.wa.historyStatus);
                    const d = await fetch('/api/v1/whatsapp/messages?' + params, { headers: this.getHeaders() }).then(r => r.json());
                    this.wa.messages = d.data || [];
                    this.wa.historyPage = d.current_page || 1;
                    this.wa.historyLastPage = d.last_page || 1;
                } catch (e) { /* history is secondary; keep the rest of the page usable */ }
            },

            toggleDay(ev, day) {
                let daysSet = Array.isArray(ev.schedule_days) ? [...ev.schedule_days] : [];
                if (daysSet.includes(day)) {
                    ev.schedule_days = daysSet.filter(d => d !== day);
                } else if (daysSet.length >= this.wa.maxDays) {
                    this.showToast(this.wt('wa_max_days_msg', 'You can choose at most 2 days a week.'), 'warning');
                } else {
                    ev.schedule_days = this.days.filter(d => d === day || daysSet.includes(d));
                }
            },

            async save() {
                this.wa.saving = true;
                try {
                    const response = await fetch('/api/v1/whatsapp/settings', {
                        method: 'POST',
                        headers: this.getHeaders(),
                        body: JSON.stringify({
                            events: this.wa.events.map(e => ({
                                key: e.key,
                                enabled: !!e.enabled,
                                schedule_days: e.scheduled && Array.isArray(e.schedule_days) ? [...new Set(e.schedule_days)] : null,
                                schedule_time: e.scheduled ? e.schedule_time : null,
                                min_due_amount: e.scheduled ? e.min_due_amount : null,
                            })),
                        }),
                    });
                    const d = await response.json();
                    if (!response.ok) {
                        const first = d.errors ? Object.values(d.errors).flat()[0] : null;
                        this.showToast(first || d.message || 'Could not save settings.', 'error');
                        return;
                    }
                    this.applySettings(d);
                    this.showToast(this.wt('wa_settings_saved', 'WhatsApp settings saved successfully.'));
                } catch (e) {
                    this.showToast('Could not save settings.', 'error');
                } finally {
                    this.wa.saving = false;
                }
            },

            resolveClaim(claim, action) {
                const run = async () => {
                    this.wa.resolvingId = claim.id;
                    try {
                        const response = await fetch(`/api/v1/whatsapp/payment-claims/${claim.id}/${action}`, { method: 'POST', headers: this.getHeaders() });
                        const d = await response.json();
                        const first = d.errors ? Object.values(d.errors).flat()[0] : null;
                        this.showToast(first || d.message || (response.ok ? 'Done.' : 'Could not update.'), response.ok ? 'success' : 'error');
                        if (response.ok) this.wa.claims = this.wa.claims.filter(c => c.id !== claim.id);
                    } catch (e) {
                        this.showToast('Failed to update claim.', 'error');
                    } finally {
                        this.wa.resolvingId = null;
                    }
                };

                if (action === 'confirm') {
                    this.showConfirm(
                        this.wt('wa_confirm_payment_claim', 'Confirm Payment Claim'),
                        `Record ₹${claim.claimed_amount} received from ${claim.customer ? claim.customer.name : 'customer'} (UTR: ${claim.claimed_utr})? This will create a ledger payment entry.`,
                        run
                    );
                } else {
                    run();
                }
            },

            async buyPack(pack) {
                this.wa.buyingId = pack.id;
                try {
                    const response = await fetch(`/api/v1/whatsapp/packs/${pack.id}/purchase`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            ...this.getHeaders(),
                        },
                    });
                    const d = await response.json();
                    if (!response.ok) {
                        this.showToast(d.message || 'Could not initiate purchase.', 'error');
                        return;
                    }

                    if (d.mock) {
                        this.wa.balance = d.balance;
                        this.wa.lowBalance = false;
                        const w = await fetch('/api/v1/whatsapp/wallet', { headers: this.getHeaders() }).then(r => r.json());
                        this.wa.ledger = w.ledger || [];
                        this.showToast(`Purchased ${pack.credits} credits successfully!`);
                        return;
                    }

                    const keyId = d.key_id || d.razorpay_key;
                    const isLiveRazorpay = typeof window.Razorpay !== 'undefined' && keyId && keyId !== 'rzp_test_placeholder';

                    if (isLiveRazorpay) {
                        const opt = {
                            key: keyId,
                            amount: d.amount,
                            currency: d.currency || 'INR',
                            order_id: d.order_id,
                            name: 'DukanHisab',
                            description: `${pack.name} (${pack.credits} Credits)`,
                            handler: async (res) => {
                                await this.verifyPackPayment(d, res, pack);
                            },
                            prefill: {
                                name: (this.shop && this.shop.name) || '',
                                contact: (this.shop && this.shop.mobile) || '',
                            },
                            theme: { color: '#0F766E' },
                            modal: {
                                ondismiss: () => {
                                    this.wa.buyingId = null;
                                    this.showToast('Payment was cancelled.', 'warning');
                                }
                            }
                        };
                        try {
                            const rzp = new window.Razorpay(opt);
                            rzp.on('payment.failed', (resp) => {
                                const err = resp && resp.error ? resp.error : {};
                                this.showToast(err.description || 'Payment failed.', 'error');
                            });
                            rzp.open();
                        } catch (err) {
                            this.showToast('Error opening payment checkout.', 'error');
                        }
                    } else {
                        // In test mode / without Razorpay keys: show clean DukanHisab popup modal
                        const amountLabel = parseFloat(pack.price).toFixed(2);
                        this.showConfirm(
                            'Test Payment Mode',
                            `Razorpay order created for ₹${amountLabel} (${pack.name} — ${pack.credits} Credits). Would you like to simulate a successful payment? (Add RAZORPAY_KEY_ID & RAZORPAY_KEY_SECRET in .env for live checkout)`,
                            async () => {
                                await this.verifyPackPayment(d, {
                                    razorpay_payment_id: 'pay_mock_' + Math.random().toString(36).substring(2, 15),
                                    razorpay_signature: 'sig_mock_verified'
                                }, pack);
                            }
                        );
                    }
                } catch (e) {
                    this.showToast('Could not start purchase.', 'error');
                } finally {
                    this.wa.buyingId = null;
                }
            },

            async verifyPackPayment(orderData, rzpRes, pack) {
                try {
                    const verify = await fetch('/api/v1/whatsapp/packs/verify-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            ...this.getHeaders(),
                        },
                        body: JSON.stringify({
                            razorpay_order_id: orderData.order_id,
                            razorpay_payment_id: rzpRes.razorpay_payment_id,
                            razorpay_signature: rzpRes.razorpay_signature,
                        }),
                    });
                    const v = await verify.json();
                    if (verify.ok) {
                        this.wa.balance = v.balance;
                        this.wa.lowBalance = false;
                        const w = await fetch('/api/v1/whatsapp/wallet', { headers: this.getHeaders() }).then(r => r.json());
                        this.wa.ledger = w.ledger || [];
                        this.showToast(v.message || `Purchased ${pack.credits} credits successfully!`);
                    } else {
                        this.showToast(v.message || 'Payment verification failed.', 'error');
                    }
                } catch (e) {
                    this.showToast('Payment verification failed.', 'error');
                }
            },
        };
    }
</script>

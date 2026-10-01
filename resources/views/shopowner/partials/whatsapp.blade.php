{{-- WHATSAPP MESSAGING PANEL: which messages go out, weekly reminder schedule, credits and packs --}}
<div x-show="page === 'whatsapp'" x-cloak x-data="whatsappPage()" @whatsapp-open.window="load()" class="space-y-4">

    {{-- Header: credits --}}
    <div class="bg-gradient-to-r from-emerald-600 to-teal-800 p-6 rounded-2xl text-white shadow-md relative overflow-hidden">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-2xl font-black" x-text="wt('whatsapp_messages', 'WhatsApp Messages')"></h3>
                <p class="text-sm opacity-90 max-w-2xl" x-text="wt('whatsapp_messages_desc', 'Automatically send invoices, payment receipts and due reminders to your customers and suppliers on WhatsApp.')"></p>
            </div>
            <div class="bg-white/15 rounded-2xl px-5 py-3 text-center shrink-0">
                <p class="text-[11px] font-bold uppercase tracking-wider opacity-80" x-text="wt('wa_credits_left', 'Messages left')"></p>
                <p class="text-3xl font-black" x-text="wa.balance.toLocaleString()"></p>
                <button type="button" @click="document.getElementById('wa-packs').scrollIntoView({ behavior: 'smooth' })"
                    class="mt-1 text-xs font-bold underline underline-offset-2" x-text="wt('wa_buy_more', 'Buy more')"></button>
            </div>
        </div>
    </div>

    <template x-if="wa.loading && !wa.loaded">
        <div class="text-center py-10">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-3 border-primary border-t-transparent"></div>
        </div>
    </template>

    <template x-if="wa.loaded">
        <div class="space-y-4">
            {{-- Notices --}}
            <div x-show="!wa.available" class="p-3 rounded-xl bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300 text-xs font-medium"
                x-text="wt('wa_unavailable', 'WhatsApp messaging is temporarily unavailable. Your settings are saved and will apply once it is back.')"></div>
            <div x-show="wa.lowBalance" class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs font-semibold"
                x-text="wa.balance === 0 ? wt('wa_no_credits', 'You have no message credits. Messages will not be sent until you buy a pack.') : wt('wa_low_credits', 'Your message credits are running low.')"></div>
            <div x-show="!wa.upiId" class="p-3 rounded-xl bg-sky-50 dark:bg-sky-900/30 border border-sky-200 dark:border-sky-800 text-sky-800 dark:text-sky-300 text-xs font-medium flex items-center justify-between gap-3">
                <span x-text="wt('wa_add_upi', 'Add your UPI ID in Settings to show a “Pay Now” button on due reminders.')"></span>
                <button type="button" @click="navigateTo('settings')" class="font-bold underline shrink-0" x-text="t('settings')"></button>
            </div>

            {{-- Message types, grouped by recipient --}}
            <template x-for="group in [{ key: 'customer', title: wt('wa_to_customers', 'Messages to Customers') }, { key: 'supplier', title: wt('wa_to_suppliers', 'Messages to Suppliers') }]" :key="group.key">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm">
                    <div class="px-4 py-3 border-b border-slate-100 dark:border-gray-700">
                        <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200" x-text="group.title"></h4>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-gray-700">
                        <template x-for="ev in wa.events.filter(e => e.recipient === group.key)" :key="ev.key">
                            <div class="p-4 space-y-3">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="wt('wa_event_' + ev.key, ev.label)"></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400"
                                            x-text="!ev.template_available ? wt('wa_not_available', 'Not available yet') : (ev.scheduled ? wt('wa_sent_on_schedule', 'Sent on the days you choose') : wt('wa_sent_instantly', 'Sent automatically right after it happens'))"></p>
                                        <div class="flex flex-wrap gap-1.5 mt-1">
                                            <span x-show="ev.has_document" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300" x-text="wt('wa_with_pdf', 'With PDF invoice')"></span>
                                            <span x-show="ev.has_pay_button" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300" x-text="wt('wa_with_pay_now', 'With Pay Now button')"></span>
                                        </div>
                                    </div>
                                    <button type="button" role="switch" :aria-checked="ev.enabled" :disabled="!ev.template_available"
                                        @click="ev.enabled = !ev.enabled"
                                        :class="ev.enabled ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-gray-600'"
                                        class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                                        <span :class="ev.enabled ? 'translate-x-5' : 'translate-x-0.5'" class="inline-block h-5 w-5 mt-0.5 rounded-full bg-white shadow transform transition-transform"></span>
                                    </button>
                                </div>

                                {{-- Weekly schedule (due reminders) --}}
                                <div x-show="ev.scheduled && ev.enabled" class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 dark:bg-gray-700/40 rounded-xl p-3">
                                    <div class="sm:col-span-3">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5"
                                            x-text="wt('wa_days', 'Days') + ' (' + wt('wa_max_days', 'max') + ' ' + wa.maxDays + ')'"></label>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="day in days" :key="day">
                                                <button type="button" @click="toggleDay(ev, day)"
                                                    :class="(ev.schedule_days || []).includes(day) ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white dark:bg-gray-800 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-gray-600'"
                                                    class="px-3 py-1.5 rounded-lg border text-xs font-bold capitalize cursor-pointer" x-text="wt('day_' + day, day)"></button>
                                            </template>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1" x-text="wt('wa_time', 'Time')"></label>
                                        <input type="time" x-model="ev.schedule_time" class="block w-full px-3 py-1.5 bg-white dark:bg-gray-800 border border-slate-300 dark:border-gray-600 rounded-lg text-xs dark:text-white">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1" x-text="wt('wa_min_due', 'Only if due is at least (₹)')"></label>
                                        <input type="number" min="0" step="1" x-model.number="ev.min_due_amount" class="block w-full px-3 py-1.5 bg-white dark:bg-gray-800 border border-slate-300 dark:border-gray-600 rounded-lg text-xs dark:text-white">
                                    </div>
                                </div>

                                {{-- Read-only preview of the fixed template --}}
                                <div x-show="ev.preview" x-data="{ open: false }">
                                    <button type="button" @click="open = !open" class="text-xs font-semibold text-primary cursor-pointer" x-text="open ? wt('wa_hide_preview', 'Hide preview') : wt('wa_show_preview', 'Preview message')"></button>
                                    <div x-show="open" x-transition class="mt-2 max-w-sm rounded-xl rounded-tl-none bg-[#dcf8c6] dark:bg-emerald-900/40 p-3 text-xs text-slate-800 dark:text-slate-100 shadow-sm">
                                        <p class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 mb-1">DukanHisab</p>
                                        <p x-show="ev.has_document" class="mb-1 text-teal-700 dark:text-teal-300 font-semibold">📄 INV-1024.pdf</p>
                                        <p class="whitespace-pre-line" x-text="ev.preview"></p>
                                        <p x-show="ev.has_pay_button" class="mt-2 pt-2 border-t border-emerald-800/10 text-center font-bold text-sky-700 dark:text-sky-300">💳 Pay Now</p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div class="flex justify-end">
                <button type="button" @click="save()" :disabled="wa.saving"
                    class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-sm font-bold shadow-sm disabled:opacity-60 cursor-pointer"
                    x-text="wa.saving ? wt('saving', 'Saving...') : wt('wa_save_settings', 'Save WhatsApp Settings')"></button>
            </div>

            {{-- Message packs --}}
            <div id="wa-packs" class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-4 space-y-3">
                <div>
                    <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200" x-text="wt('wa_buy_messages', 'Buy WhatsApp Messages')"></h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="wt('wa_one_credit', '1 credit = 1 WhatsApp message. Credits never expire. Failed messages are refunded automatically.')"></p>
                </div>
                <p x-show="wa.packs.length === 0" class="text-xs text-slate-400" x-text="wt('wa_no_packs', 'No message packs are available right now.')"></p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <template x-for="pack in wa.packs" :key="pack.id">
                        <div class="rounded-xl border border-slate-200 dark:border-gray-700 p-4 flex flex-col gap-2">
                            <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="pack.name"></p>
                            <p class="text-2xl font-black text-emerald-600" x-text="Number(pack.credits).toLocaleString() + ' ' + wt('wa_messages', 'messages')"></p>
                            <p class="text-xs text-slate-500" x-text="'₹' + Number(pack.price).toFixed(2) + ' · ₹' + (pack.price / pack.credits).toFixed(2) + ' ' + wt('wa_per_message', 'per message')"></p>
                            <button type="button" @click="buyPack(pack)" :disabled="wa.buyingId !== null"
                                class="mt-auto px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold disabled:opacity-60 cursor-pointer"
                                x-text="wa.buyingId === pack.id ? wt('processing', 'Processing...') : wt('wa_buy', 'Buy')"></button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Credit history --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-4 space-y-3" x-show="wa.ledger.length">
                <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200" x-text="wt('wa_credit_history', 'Credit History')"></h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                            <template x-for="row in wa.ledger.slice(0, 15)" :key="row.id">
                                <tr>
                                    <td class="py-2 text-slate-500 whitespace-nowrap" x-text="new Date(row.created_at).toLocaleString()"></td>
                                    <td class="py-2 px-3 text-slate-700 dark:text-slate-300" x-text="wt('wa_ledger_' + row.type, row.type) + (row.note ? ' — ' + row.note : '')"></td>
                                    <td class="py-2 text-right font-bold" :class="row.credits > 0 ? 'text-emerald-600' : 'text-slate-500'" x-text="(row.credits > 0 ? '+' : '') + row.credits"></td>
                                    <td class="py-2 pl-3 text-right text-slate-400" x-text="row.balance_after"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function whatsappPage() {
        return {
            days: ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
            wa: { loading: false, loaded: false, saving: false, buyingId: null, available: true, balance: 0, lowBalance: false, upiId: null, maxDays: 2, events: [], packs: [], ledger: [] },

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

            async load() {
                if (this.wa.loading || !this.shop) return;
                this.wa.loading = true;
                try {
                    const [settings, wallet] = await Promise.all([
                        fetch('/api/v1/whatsapp/settings', { headers: this.getHeaders() }).then(r => r.json()),
                        fetch('/api/v1/whatsapp/wallet', { headers: this.getHeaders() }).then(r => r.json()),
                    ]);
                    this.applySettings(settings);
                    this.wa.packs = wallet.packs || [];
                    this.wa.ledger = wallet.ledger || [];
                    this.wa.loaded = true;
                } catch (e) {
                    this.showToast(this.wt('wa_load_failed', 'Could not load WhatsApp settings.'), 'error');
                } finally {
                    this.wa.loading = false;
                }
            },

            toggleDay(ev, day) {
                const daysSet = ev.schedule_days || [];
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
                                key: e.key, enabled: e.enabled,
                                schedule_days: e.scheduled ? e.schedule_days : null,
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
                    this.showToast(this.wt('wa_settings_saved', 'WhatsApp settings saved.'));
                } catch (e) {
                    this.showToast('Could not save settings.', 'error');
                } finally {
                    this.wa.saving = false;
                }
            },

            async buyPack(pack) {
                if (this.wa.buyingId !== null) return;
                this.wa.buyingId = pack.id;
                const self = this;
                try {
                    const response = await fetch(`/api/v1/whatsapp/packs/${pack.id}/purchase`, { method: 'POST', headers: this.getHeaders() });
                    const d = await response.json();
                    if (!response.ok || !d.order_id) {
                        this.wa.buyingId = null;
                        this.showToast(d.message || 'Failed to initialize payment gateway.', 'error');
                        return;
                    }

                    if (typeof Razorpay !== 'undefined' && d.key_id && d.key_id !== 'rzp_test_placeholder') {
                        let handled = false;
                        const rzp = new Razorpay({
                            key: d.key_id,
                            order_id: d.order_id,
                            name: 'DukanHisab',
                            description: pack.name + ' — WhatsApp messages',
                            handler: (res) => { handled = true; self.verifyPack(res); },
                            prefill: { name: d.user?.name || '', email: d.user?.email || '', contact: d.user?.mobile || '' },
                            theme: { color: '#0F766E' },
                            modal: {
                                ondismiss: () => {
                                    self.wa.buyingId = null;
                                    if (!handled) { handled = true; self.showToast(self.t('payment_cancelled') || 'Payment was cancelled.', 'warning'); }
                                },
                            },
                        });
                        rzp.on('payment.failed', (res) => {
                            self.wa.buyingId = null;
                            if (handled) return;
                            handled = true;
                            const err = res?.error || {};
                            self.showToast(err.description || err.reason || 'Payment failed. Please try again.', 'error');
                        });
                        rzp.open();
                    } else {
                        this.wa.buyingId = null;
                        this.showConfirm('Test Payment Mode',
                            `Razorpay order created for ${pack.name} (₹${Number(pack.price).toFixed(2)}). Simulate a successful payment?`,
                            () => self.verifyPack({ razorpay_order_id: d.order_id, razorpay_payment_id: 'pay_mock_' + Math.random().toString(36).substring(2, 15), razorpay_signature: 'sig_mock_verified' }));
                    }
                } catch (e) {
                    this.wa.buyingId = null;
                    this.showToast('Error connecting to payment service.', 'error');
                }
            },

            async verifyPack(res) {
                try {
                    const response = await fetch('/api/v1/whatsapp/packs/verify-payment', {
                        method: 'POST',
                        headers: this.getHeaders(),
                        body: JSON.stringify({ razorpay_order_id: res.razorpay_order_id, razorpay_payment_id: res.razorpay_payment_id, razorpay_signature: res.razorpay_signature || '' }),
                    });
                    const d = await response.json();
                    this.showToast(d.message || (response.ok ? 'Credits added.' : 'Payment verification failed.'), response.ok ? 'success' : 'error');
                    if (response.ok) { this.wa.loaded = false; await this.load(); }
                } finally {
                    this.wa.buyingId = null;
                }
            },
        };
    }
</script>

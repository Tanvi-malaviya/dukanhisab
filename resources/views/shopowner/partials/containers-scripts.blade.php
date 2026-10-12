<script>
    // Returnable containers module (Tub / Can / Bucket lent against a refundable deposit).
    // Spread into appState() — only visible for shops where the admin switched the module on.
    // All money effects (cashbook, dues, invoices) are posted by the server; this file only
    // collects quantities and shows a preview of what will be settled.
    function containersModule() {
        return {
            containerTypes: [],
            containerSummary: null,
            containerCustomers: [],
            containerEntries: [],
            containerEntriesMeta: { current_page: 1, last_page: 1, total: 0 },
            containersLoading: false,
            containerTab: 'overview',
            containerSearch: '',
            containerCustomersPage: 1,
            containerCustomersPerPage: 10,
            containerEntryFilters: { search: '', type: '', start_date: '', end_date: '' },

            showContainerEntryModal: false,
            containerForm: {},
            containerCustomerQuery: '',
            containerCustomerDetail: null,
            containerSaving: false,

            showContainerEntryDetail: false,
            containerEntryDetail: null,

            showContainerCustomerModal: false,
            containerCustomerView: null,

            showContainerTypeModal: false,
            containerTypeForm: { id: null, name: '', deposit_amount: '', total_owned: '' },
            showContainerStockModal: false,
            containerStockForm: { type: null, direction: 'in', quantity: '', note: '' },

            // POS: containers given / empties back with the bill being made
            posContainers: { overrides: {}, returned: {}, collect_deposit: true, settlement_method: 'cash' },
            posContainerDetail: null,

            hasContainers() {
                return !!(this.shop && Array.isArray(this.shop.features) && this.shop.features.includes('containers'));
            },

            // ── Loaders ───────────────────────────────────────────────
            loadContainersPage() {
                if (!this.hasContainers()) return Promise.resolve();
                this.containersLoading = true;
                return Promise.allSettled([
                    this.loadContainerTypes(),
                    this.loadContainerSummary(),
                    this.loadContainerCustomers(),
                    this.loadContainerEntries(1),
                    this.customers && this.customers.length ? null : this.loadCustomers(),
                ]).finally(() => { this.containersLoading = false; });
            },
            containerFetch(url, options = {}) {
                return fetch(url, { headers: this.getHeaders(), ...options })
                    .then(r => r.json().then(body => ({ ok: r.ok, body }))
                        .catch(() => ({ ok: r.ok, body: {} })));
            },
            containerError(body, fallback) {
                if (body && body.errors) return Object.values(body.errors).flat().join('\n');
                return (body && body.message) || fallback;
            },
            loadContainerTypes() {
                if (!this.hasContainers()) return Promise.resolve();
                return this.containerFetch('/api/v1/container-types').then(({ ok, body }) => {
                    if (ok && Array.isArray(body)) this.containerTypes = body;
                });
            },
            loadContainerSummary() {
                return this.containerFetch('/api/v1/containers/summary').then(({ ok, body }) => {
                    if (ok) this.containerSummary = body;
                });
            },
            loadContainerCustomers() {
                return this.containerFetch('/api/v1/containers/customers').then(({ ok, body }) => {
                    if (ok && Array.isArray(body)) this.containerCustomers = body;
                });
            },
            loadContainerEntries(page = 1) {
                const f = this.containerEntryFilters;
                const params = new URLSearchParams({ page, per_page: 15 });
                ['search', 'type', 'start_date', 'end_date'].forEach(k => { if (f[k]) params.set(k, f[k]); });
                return this.containerFetch('/api/v1/containers/entries?' + params.toString()).then(({ ok, body }) => {
                    if (ok && body && Array.isArray(body.data)) {
                        this.containerEntries = body.data;
                        this.containerEntriesMeta = { current_page: body.current_page, last_page: body.last_page, total: body.total };
                    }
                });
            },
            filteredContainerCustomers() {
                const q = (this.containerSearch || '').toLowerCase().trim();
                if (!q) return this.containerCustomers;
                return this.containerCustomers.filter(c => (c.name || '').toLowerCase().includes(q) || (c.mobile || '').includes(q));
            },

            // ── Labels & formatting ───────────────────────────────────
            money(v) { return '₹' + (parseFloat(v) || 0).toFixed(2); },
            containerTypeName(id) {
                const t = this.containerTypes.find(t => t.id == id);
                return t ? t.name : (this.t('container') || 'Container');
            },
            containerEntryTypeLabel(type) {
                const map = { give: 'containers_given', return: 'containers_returned', exchange: 'container_exchange', opening: 'opening_balance', stock: 'stock_adjustment', reversal: 'reversal' };
                return this.t(map[type] || type);
            },
            containerEntryTypeClass(type) {
                return {
                    give: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                    return: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                    exchange: 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
                    opening: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300',
                    stock: 'bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-slate-300',
                    reversal: 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
                }[type] || 'bg-slate-100 text-slate-700';
            },
            containerMethodLabel(method) {
                const map = { cash: 'cash', upi: 'upi', bank: 'bank', due_adjustment: 'adjust_against_dues', none: 'no_money_movement' };
                return this.t(map[method] || method);
            },
            containerKindLabel(kind) {
                const map = { issue: 'given', return: 'returned', damaged: 'damaged', lost: 'lost', stock_in: 'stock_added', stock_out: 'stock_removed' };
                return this.t(map[kind] || kind);
            },
            // "+5 Tub · −3 Can" style summary of an entry's lines
            containerEntrySummary(entry) {
                const totals = {};
                (entry.movements || []).forEach(m => {
                    const name = m.container_type ? m.container_type.name : this.containerTypeName(m.container_type_id);
                    const sign = ['issue', 'stock_in'].includes(m.kind) ? 1 : -1;
                    totals[name] = (totals[name] || 0) + sign * m.quantity;
                });
                return Object.entries(totals).map(([name, q]) => (q > 0 ? '+' : (q < 0 ? '−' : '')) + Math.abs(q) + ' ' + name).join(' · ') || '-';
            },
            containerNetText(entry) {
                const net = parseFloat(entry.net_amount) || 0;
                if (entry.type === 'opening') return this.t('deposit_held') + ' ' + this.money(entry.deposit_amount);
                if (net > 0) return this.t('collected') + ' ' + this.money(net);
                if (net < 0) return (entry.settlement_method === 'due_adjustment' ? this.t('adjusted') : this.t('refunded')) + ' ' + this.money(-net);
                return '-';
            },
            containerWhatsappLink(cust) {
                const items = (cust.holdings || []).map(h => h.pending + ' ' + h.name).join(', ');
                const shopName = this.shop ? this.shop.name : 'DukanHisab';
                const deposit = this.money(cust.deposit_held);
                const msg = this.currentLang === 'gu'
                    ? `નમસ્તે ${cust.name}, ${shopName} ના ${items} તમારી પાસે છે (ડિપોઝિટ ${deposit}). ખાલી થાય ત્યારે પરત કરવા વિનંતી. આભાર!`
                    : this.currentLang === 'hi'
                        ? `नमस्ते ${cust.name}, ${shopName} के ${items} आपके पास हैं (डिपॉज़िट ${deposit})। खाली होने पर कृपया लौटा दें। धन्यवाद!`
                        : `Dear ${cust.name}, you have ${items} of ${shopName} with you (deposit ${deposit}). Please return them once empty. Thank you!`;
                return `https://wa.me/${cust.mobile || ''}?text=${encodeURIComponent(msg)}`;
            },

            // ── Give / Return / Exchange / Opening entry ──────────────
            openContainerEntryModal(mode = 'return', customerId = null, saleId = null) {
                if (!this.customers || !this.customers.length) this.loadCustomers();
                if (!this.containerTypes.length) {
                    this.loadContainerTypes().then(() => {
                        if (!this.containerForm.issues || !this.containerForm.issues.some(l => l.quantity)) this.resetContainerIssueRows();
                    });
                }
                this.containerForm = {
                    mode,
                    customer_id: customerId ? String(customerId) : '',
                    sale_id: saleId,
                    issues: [],
                    returns: [],
                    collect_deposit: true,
                    settlement_method: 'cash',
                    note: '',
                    entry_date: '',
                };
                this.containerCustomerQuery = '';
                this.containerCustomerDetail = null;
                this.resetContainerIssueRows();
                this.showContainerEntryModal = true;
                if (customerId) this.selectContainerCustomer(customerId);
            },
            setContainerMode(mode) {
                this.containerForm.mode = mode;
                if (['give', 'exchange', 'opening'].includes(mode) && !this.containerForm.issues.length) this.resetContainerIssueRows();
                this.resetContainerReturnRows();
            },
            resetContainerIssueRows() {
                const types = this.containerTypes.length ? this.containerTypes : [];
                this.containerForm.issues = types.map(t => ({ container_type_id: t.id, quantity: '', deposit_per_unit: parseFloat(t.deposit_amount) || 0 }));
            },
            resetContainerReturnRows() {
                const holdings = this.containerCustomerDetail ? this.containerCustomerDetail.holdings : [];
                const saleId = this.containerForm.sale_id;
                this.containerForm.returns = holdings
                    .filter(h => !saleId || this.containerLotsFor(h.container_type_id, saleId).length)
                    .map(h => {
                        const salePending = saleId
                            ? this.containerLotsFor(h.container_type_id, saleId).reduce((s, l) => s + l.pending_quantity, 0)
                            : 0;
                        return {
                            container_type_id: h.container_type_id,
                            sale_id: saleId ? String(saleId) : '',
                            // Coming from an invoice: pre-fill everything from that invoice as returned.
                            returned: saleId ? salePending : '',
                            damaged: '', damage_deduction: '', lost: '',
                        };
                    });
            },
            containerCustomerOptions() {
                const q = (this.containerCustomerQuery || '').toLowerCase().trim();
                const list = this.customers || [];
                return (q ? list.filter(c => (c.name || '').toLowerCase().includes(q) || (c.mobile || '').includes(q)) : list).slice(0, 50);
            },
            selectContainerCustomer(id) {
                this.containerForm.customer_id = id ? String(id) : '';
                this.containerCustomerDetail = null;
                if (!id) { this.containerForm.returns = []; return; }
                this.containerFetch('/api/v1/containers/customers/' + id).then(({ ok, body }) => {
                    if (!ok || String(this.containerForm.customer_id) !== String(id)) return;
                    this.containerCustomerDetail = body;
                    this.resetContainerReturnRows();
                });
            },
            containerLotsFor(typeId, saleId = null) {
                const lots = this.containerCustomerDetail ? this.containerCustomerDetail.lots : [];
                return lots.filter(l => l.container_type_id == typeId && (!saleId || l.sale_id == saleId));
            },
            // Invoices still holding this container type, for the "which invoice" picker
            containerInvoiceOptions(typeId) {
                const map = {};
                this.containerLotsFor(typeId).forEach(l => {
                    const key = l.sale_id || '';
                    if (!key) return;
                    if (!map[key]) map[key] = { sale_id: l.sale_id, label: l.sale ? l.sale.sale_number : ('#' + l.sale_id), pending: 0 };
                    map[key].pending += l.pending_quantity;
                });
                return Object.values(map);
            },
            containerRowPending(row) {
                return this.containerLotsFor(row.container_type_id, row.sale_id || null).reduce((s, l) => s + l.pending_quantity, 0);
            },
            splitContainerReturnRow(idx) {
                const row = this.containerForm.returns[idx];
                this.containerForm.returns.splice(idx + 1, 0, { container_type_id: row.container_type_id, sale_id: '', returned: '', damaged: '', damage_deduction: '', lost: '' });
            },

            // Mirrors the server's oldest-first matching so the owner sees the refund before saving.
            containerPreview() {
                const f = this.containerForm;
                let deposit = 0, refund = 0, forfeit = 0, overReturn = false;
                if (['return', 'exchange'].includes(f.mode)) {
                    const used = {};
                    (f.returns || []).forEach(line => {
                        const wanted = [['return', parseInt(line.returned) || 0], ['damaged', parseInt(line.damaged) || 0], ['lost', parseInt(line.lost) || 0]];
                        let deduction = parseFloat(line.damage_deduction) || 0;
                        const lots = this.containerLotsFor(line.container_type_id, line.sale_id || null);
                        lots.forEach(lot => {
                            wanted.forEach(w => {
                                const avail = lot.pending_quantity - (used[lot.id] || 0);
                                if (w[1] <= 0 || avail <= 0) return;
                                const q = Math.min(w[1], avail);
                                w[1] -= q;
                                used[lot.id] = (used[lot.id] || 0) + q;
                                const value = q * (parseFloat(lot.deposit_per_unit) || 0);
                                if (w[0] === 'return') refund += value;
                                else if (w[0] === 'damaged') { const d = Math.min(deduction, value); deduction -= d; forfeit += d; refund += value - d; }
                                else forfeit += value;
                            });
                        });
                        if (wanted.some(w => w[1] > 0)) overReturn = true;
                    });
                }
                if (['give', 'exchange', 'opening'].includes(f.mode)) {
                    (f.issues || []).forEach(line => {
                        const q = parseInt(line.quantity) || 0;
                        if (q > 0 && (f.collect_deposit || f.mode === 'opening')) deposit += q * (parseFloat(line.deposit_per_unit) || 0);
                    });
                }
                const net = f.mode === 'opening' ? 0 : Math.round((deposit - refund) * 100) / 100;
                return { deposit, refund, forfeit, net, overReturn };
            },
            containerFormHasQuantity() {
                const f = this.containerForm;
                const ret = ['return', 'exchange'].includes(f.mode) && (f.returns || []).some(l => (parseInt(l.returned) || 0) + (parseInt(l.damaged) || 0) + (parseInt(l.lost) || 0) > 0);
                const iss = ['give', 'exchange', 'opening'].includes(f.mode) && (f.issues || []).some(l => (parseInt(l.quantity) || 0) > 0);
                return ret || iss;
            },
            saveContainerEntry() {
                const f = this.containerForm;
                if (!f.customer_id) { this.showToast(this.t('select_customer_first'), 'warning'); return; }
                if (!this.containerFormHasQuantity()) { this.showToast(this.t('enter_container_quantity'), 'warning'); return; }
                const preview = this.containerPreview();
                if (preview.overReturn) { this.showToast(this.t('return_more_than_pending'), 'error'); return; }
                if (preview.net > 0 && !['cash', 'upi', 'bank'].includes(f.settlement_method)) f.settlement_method = 'cash';

                const payload = {
                    customer_id: parseInt(f.customer_id),
                    opening: f.mode === 'opening',
                    collect_deposit: f.mode === 'opening' ? true : !!f.collect_deposit,
                    settlement_method: preview.net === 0 ? null : f.settlement_method,
                    note: f.note || null,
                    entry_date: f.mode === 'opening' && f.entry_date ? f.entry_date : null,
                    issues: ['give', 'exchange', 'opening'].includes(f.mode)
                        ? f.issues.filter(l => (parseInt(l.quantity) || 0) > 0).map(l => ({
                            container_type_id: l.container_type_id,
                            quantity: parseInt(l.quantity),
                            deposit_per_unit: parseFloat(l.deposit_per_unit) || 0,
                        }))
                        : [],
                    returns: ['return', 'exchange'].includes(f.mode)
                        ? f.returns.filter(l => (parseInt(l.returned) || 0) + (parseInt(l.damaged) || 0) + (parseInt(l.lost) || 0) > 0).map(l => ({
                            container_type_id: l.container_type_id,
                            sale_id: l.sale_id ? parseInt(l.sale_id) : null,
                            returned: parseInt(l.returned) || 0,
                            damaged: parseInt(l.damaged) || 0,
                            damage_deduction: parseFloat(l.damage_deduction) || 0,
                            lost: parseInt(l.lost) || 0,
                        }))
                        : [],
                };

                this.containerSaving = true;
                this.containerFetch('/api/v1/containers/entries', { method: 'POST', body: JSON.stringify(payload) })
                    .then(({ ok, body }) => {
                        this.containerSaving = false;
                        if (!ok) { this.showConfirm(this.t('validation_error') || 'Validation Error', this.containerError(body, 'Failed to save.'), () => { }); return; }
                        this.showToast(this.t('container_entry_saved') + ' ' + body.entry_number);
                        this.showContainerEntryModal = false;
                        this.containerEntryDetail = body;
                        this.showContainerEntryDetail = true;
                        this.afterContainerChange();
                    })
                    .catch(() => { this.containerSaving = false; this.showToast('Network error.', 'error'); });
            },
            afterContainerChange() {
                if (this.page === 'containers') this.loadContainersPage();
                else this.loadContainerTypes();
                // Dues, cashbook and invoices may all have changed.
                if (typeof this.loadCustomers === 'function') this.loadCustomers();
                if (['sales-history', 'sales-returned'].includes(this.page) && typeof this.loadSales === 'function') this.loadSales();
            },

            // ── Entry detail / receipt / reversal ─────────────────────
            openContainerEntryDetail(entry) {
                this.containerEntryDetail = entry;
                this.showContainerEntryDetail = true;
                this.containerFetch('/api/v1/containers/entries/' + entry.id).then(({ ok, body }) => {
                    if (ok) this.containerEntryDetail = body;
                });
            },
            reverseContainerEntry(entry) {
                this.showConfirm(
                    this.t('reverse_entry') || 'Reverse Entry',
                    (this.t('reverse_entry_confirm') || 'This posts an opposite entry and undoes its containers, cash and dues.') + ' (' + entry.entry_number + ')',
                    () => {
                        this.containerFetch('/api/v1/containers/entries/' + entry.id + '/reverse', { method: 'POST', body: JSON.stringify({}) })
                            .then(({ ok, body }) => {
                                if (!ok) { this.showConfirm(this.t('validation_error') || 'Error', this.containerError(body, 'Failed to reverse.'), () => { }); return; }
                                this.showToast(this.t('entry_reversed') + ' ' + body.entry_number);
                                this.showContainerEntryDetail = false;
                                this.afterContainerChange();
                            });
                    }
                );
            },
            canReverseContainerEntry(entry) {
                return entry && entry.type !== 'reversal' && !entry.reversed_at;
            },

            // ── Customer view ─────────────────────────────────────────
            openContainerCustomer(customerId) {
                this.containerCustomerView = null;
                this.showContainerCustomerModal = true;
                this.containerFetch('/api/v1/containers/customers/' + customerId).then(({ ok, body }) => {
                    if (ok) this.containerCustomerView = body;
                });
            },

            // ── Container types & stock ───────────────────────────────
            openContainerTypeModal(type = null) {
                this.containerTypeForm = type
                    ? { id: type.id, name: type.name, deposit_amount: type.deposit_amount, total_owned: type.total_owned }
                    : { id: null, name: '', deposit_amount: '', total_owned: '' };
                this.showContainerTypeModal = true;
            },
            saveContainerType() {
                const f = this.containerTypeForm;
                const isEdit = !!f.id;
                const payload = { name: f.name, deposit_amount: parseFloat(f.deposit_amount) || 0 };
                if (!isEdit) payload.total_owned = parseInt(f.total_owned) || 0;
                this.containerFetch('/api/v1/container-types' + (isEdit ? '/' + f.id : ''), { method: isEdit ? 'PUT' : 'POST', body: JSON.stringify(payload) })
                    .then(({ ok, body }) => {
                        if (!ok) { this.showToast(this.containerError(body, 'Failed to save.'), 'error'); return; }
                        this.showToast(this.t('saved') || 'Saved');
                        this.showContainerTypeModal = false;
                        this.loadContainerTypes();
                        if (this.page === 'containers') this.loadContainerSummary();
                    });
            },
            deleteContainerType(type) {
                this.showConfirm(this.t('delete') || 'Delete', (this.t('delete_container_type_confirm') || 'Delete this container type?') + ' (' + type.name + ')', () => {
                    this.containerFetch('/api/v1/container-types/' + type.id, { method: 'DELETE' }).then(({ ok, body }) => {
                        if (!ok) { this.showConfirm(this.t('validation_error') || 'Error', this.containerError(body, 'Failed to delete.'), () => { }); return; }
                        this.showToast(this.t('deleted') || 'Deleted');
                        this.loadContainerTypes();
                        this.loadContainerSummary();
                    });
                });
            },
            openContainerStockModal(type) {
                this.containerStockForm = { type, direction: 'in', quantity: '', note: '' };
                this.showContainerStockModal = true;
            },
            saveContainerStock() {
                const f = this.containerStockForm;
                const qty = parseInt(f.quantity) || 0;
                if (qty <= 0) { this.showToast(this.t('enter_container_quantity'), 'warning'); return; }
                this.containerFetch('/api/v1/container-types/' + f.type.id + '/adjust-stock', {
                    method: 'POST',
                    body: JSON.stringify({ quantity: f.direction === 'in' ? qty : -qty, note: f.note || null }),
                }).then(({ ok, body }) => {
                    if (!ok) { this.showToast(this.containerError(body, 'Failed to save.'), 'error'); return; }
                    this.showToast(this.t('container_entry_saved') + ' ' + body.entry.entry_number);
                    this.showContainerStockModal = false;
                    this.loadContainersPage();
                });
            },

            // ── POS integration ───────────────────────────────────────
            resetPosContainers() {
                this.posContainers = { overrides: {}, returned: {}, collect_deposit: true, settlement_method: 'cash' };
                this.posContainerDetail = null;
            },
            loadPosContainerHoldings(customerId) {
                this.posContainerDetail = null;
                if (!this.hasContainers() || !customerId) return;
                this.containerFetch('/api/v1/containers/customers/' + customerId).then(({ ok, body }) => {
                    if (ok && String(this.pos.selectedCustomer) === String(customerId)) this.posContainerDetail = body;
                });
            },
            // Containers the cart sends automatically: product's container type × containers per unit
            posContainerAuto() {
                const auto = {};
                (this.pos.items || []).forEach(item => {
                    const prod = (this.products || []).find(p => p.id === item.product_id);
                    if (prod && prod.container_type_id) {
                        auto[prod.container_type_id] = (auto[prod.container_type_id] || 0) + (parseInt(item.quantity) || 0) * (parseInt(prod.containers_per_unit) || 1);
                    }
                });
                return auto;
            },
            posContainerGiven(typeId) {
                const o = this.posContainers.overrides[typeId];
                return o !== undefined && o !== '' ? (parseInt(o) || 0) : (this.posContainerAuto()[typeId] || 0);
            },
            posContainerHeld(typeId) {
                const h = this.posContainerDetail ? this.posContainerDetail.holdings.find(h => h.container_type_id == typeId) : null;
                return h ? h.pending : 0;
            },
            posContainerRows() {
                return this.containerTypes.filter(t =>
                    this.posContainerGiven(t.id) > 0 || this.posContainerHeld(t.id) > 0
                    || this.posContainers.overrides[t.id] !== undefined || (parseInt(this.posContainers.returned[t.id]) || 0) > 0);
            },
            posHasContainerActivity() {
                return this.containerTypes.some(t => this.posContainerGiven(t.id) > 0 || (parseInt(this.posContainers.returned[t.id]) || 0) > 0);
            },
            posContainerPreview() {
                let deposit = 0, refund = 0;
                this.containerTypes.forEach(t => {
                    if (this.posContainers.collect_deposit) deposit += this.posContainerGiven(t.id) * (parseFloat(t.deposit_amount) || 0);
                    let back = parseInt(this.posContainers.returned[t.id]) || 0;
                    const lots = this.posContainerDetail ? this.posContainerDetail.lots.filter(l => l.container_type_id == t.id) : [];
                    lots.forEach(l => {
                        if (back <= 0) return;
                        const q = Math.min(back, l.pending_quantity);
                        back -= q;
                        refund += q * (parseFloat(l.deposit_per_unit) || 0);
                    });
                });
                return { deposit, refund, net: Math.round((deposit - refund) * 100) / 100 };
            },
            posContainerPayload() {
                if (!this.hasContainers() || !this.posHasContainerActivity()) return null;
                const net = this.posContainerPreview().net;
                let method = this.posContainers.settlement_method;
                if (net > 0 && !['cash', 'upi', 'bank'].includes(method)) method = 'cash';
                return {
                    given: this.containerTypes.map(t => ({ container_type_id: t.id, quantity: this.posContainerGiven(t.id) })).filter(l => l.quantity > 0),
                    returned: this.containerTypes.map(t => ({ container_type_id: t.id, quantity: parseInt(this.posContainers.returned[t.id]) || 0 })).filter(l => l.quantity > 0),
                    collect_deposit: !!this.posContainers.collect_deposit,
                    settlement_method: method,
                };
            },
            posDefaultContainerMethod() {
                return { Cash: 'cash', UPI: 'upi', Bank: 'bank' }[this.pos.paymentType] || 'cash';
            },

            // ── Invoices ──────────────────────────────────────────────
            // Containers still with the customer from this sale, grouped by type and deposit rate
            saleContainerRows(sale) {
                if (!sale || !Array.isArray(sale.container_lots)) return [];
                const rows = {};
                sale.container_lots.forEach(l => {
                    const pending = (l.quantity || 0) - (l.closed_quantity || 0);
                    if (pending <= 0) return;
                    const key = l.container_type_id + '|' + l.deposit_per_unit;
                    if (!rows[key]) rows[key] = { name: l.container_type ? l.container_type.name : this.containerTypeName(l.container_type_id), qty: 0, rate: parseFloat(l.deposit_per_unit) || 0 };
                    rows[key].qty += pending;
                });
                return Object.values(rows).map(r => ({ ...r, deposit: r.qty * r.rate }));
            },
            salePendingContainers(sale) {
                return this.saleContainerRows(sale).reduce((s, r) => s + r.qty, 0);
            },
        };
    }
</script>

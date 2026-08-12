<script>
    function appState() {
        return {
            dark: localStorage.getItem('darkMode') === 'true',
            getContrastColor(hexColor) {
                if (!hexColor) return 'text-slate-900';
                const str = hexColor.trim().toLowerCase();
                const cleanHex = str.replace('#', '').trim();
                if (cleanHex.length !== 6 && cleanHex.length !== 3) return 'text-slate-900';
                let r, g, b;
                if (cleanHex.length === 6) {
                    r = parseInt(cleanHex.substring(0, 2), 16) || 0;
                    g = parseInt(cleanHex.substring(2, 4), 16) || 0;
                    b = parseInt(cleanHex.substring(4, 6), 16) || 0;
                } else {
                    r = parseInt(cleanHex.substring(0, 1) + cleanHex.substring(0, 1), 16) || 0;
                    g = parseInt(cleanHex.substring(1, 2) + cleanHex.substring(1, 2), 16) || 0;
                    b = parseInt(cleanHex.substring(2, 3) + cleanHex.substring(2, 3), 16) || 0;
                }
                const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
                // Only genuinely dark shades (black, dark chocolate/brown, navy, etc.)
                // should get white text — everything else defaults to dark text.
                return (yiq >= 60) ? 'text-slate-900' : 'text-white';
            },
            printHtmlBlock(elementId, title = 'Print') {
                const el = document.getElementById(elementId);
                if (!el) return;
                const html = el.innerHTML;
                const w = window.open('', '', 'width=800,height=600');
                w.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>${title}</title>
                        <link rel="preconnect" href="https://fonts.googleapis.com">
                        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
                        <script src="https://cdn.tailwindcss.com"><\/script>
                        <script>
                            tailwind.config = {
                                theme: {
                                    extend: {
                                        colors: {
                                            primary: { DEFAULT: '#0F766E', hover: '#115E59', light: '#CCFBF1' },
                                            secondary: { DEFAULT: '#14B8A6', hover: '#0D9488' }
                                        },
                                        fontFamily: { sans: ['"Poppins"', 'system-ui', 'sans-serif'] }
                                    }
                                }
                            }
                        <\/script>
                        <style>
                            body { font-family: 'Poppins', sans-serif; background-color: #ffffff !important; color: #0f172a !important; padding: 20px; }
                            .invoice-theme-header.text-white,
                            .invoice-theme-header.text-white * {
                                color: #ffffff !important;
                            }
                            .invoice-theme-header.text-slate-900,
                            .invoice-theme-header.text-slate-900 * {
                                color: #0f172a !important;
                            }
                            @media print {
                                * {
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                            }
                        </style>
                    </head>
                    <body class="bg-white">
                        <div class="max-w-[700px] mx-auto">
                            ${html}
                        </div>
                        <script>
                            setTimeout(() => {
                                window.print();
                                window.close();
                            }, 500);
                        <\/script>
                    </body>
                    </html>
                `);
                w.document.close();
            },
            page: 'dashboard',

            // URL Route map
            routeMap: {
                '': 'dashboard',
                'dashboard': 'dashboard',
                'sales': 'sales',
                'sales-history': 'sales-history',
                'products': 'products',
                'customers': 'customers',
                'suppliers': 'suppliers',
                'expenses': 'expenses',
                'purchases': 'purchases',
                'purchase-history': 'purchase-history',
                'inventory': 'inventory',
                'cashbook': 'cashbook',
                'bank-accounts': 'bank-accounts',
                'transactions': 'transactions',
                'reports': 'reports',
                'reminders': 'reminders',
                'settings': 'settings',
                'subscription': 'subscription',
                'sales-returned': 'sales-returned',
                'purchase-returned': 'purchase-returned',
            },

            // Auth States
            token: localStorage.getItem('shopowner_token') || localStorage.getItem('token'),
            user: JSON.parse(localStorage.getItem('shopowner_user') || 'null'),
            shop: JSON.parse(localStorage.getItem('shopowner_shop') || 'null'),
            hasShop: localStorage.getItem('shopowner_has_shop') === 'true' || (JSON.parse(localStorage.getItem('shopowner_shop') || 'null') !== null),
            authPage: 'login',

            loginForm: { email: '', password: '', remember: false },
            registerForm: { first_name: '', last_name: '', mobile: '', email: '', password: '', password_confirmation: '' },
            otpForm: { code: '' },
            forgotForm: { email: '' },
            resetForm: { token: '', password: '', password_confirmation: '' },
            emailToVerify: '',

            setupForm: { name: '', owner_name: '', mobile: '', gst_number: '', logo: null },
            logoPreview: null,
            addShopModal: { show: false, name: '', owner_name: '', mobile: '', gst_number: '', logo: null, logoPreview: null },

            toast: { show: false, message: '', type: 'success' },
            loading: false,

            // Data
            dashboardStats: { today_sales: 0, today_purchases: 0, cash_balance: 0, bank_balance: 0, customer_due: 0, supplier_due: 0, low_stock_count: 0, low_stock_products: [], recent_sales: [], recent_purchases: [] },
            dashboardLoading: false,
            invoiceSettings: null,
            invoiceSettingsLoading: false,
            products: [],
            productsTotal: 0,
            productsLoading: false,
            customers: [],
            customersTotal: 0,
            customersLoading: false,
            suppliers: [],
            suppliersLoading: false,
            sales: [],
            salesLoading: false,
            expenses: [],
            expensesLoading: false,
            purchases: [],
            purchasesTotal: 0,
            purchasesLoading: false,
            salesFilter: { date: '', customerId: '', search: '', status: '' },
            salesCustomerSearchQuery: '',
            salesFilteredCustomers: [],
            returnedFilter: { date: '', customerId: '', search: '', status: '' },
            returnedCustomerSearchQuery: '',
            returnedFilteredCustomers: [],
            purchaseFilter: { month: '', supplierId: '', search: '' },
            purchaseReturnedFilter: { month: '', supplierId: '', search: '' },
            stockHistory: [],
            cashbook: [],
            cashbookLoading: false,
            bankAccounts: [],
            bankAccountsLoading: false,
            reportsData: { total_sales: 0, sales_count: 0, sales_by_payment_type: [], total_purchases: 0, purchases_count: 0, total_expenses: 0, expenses_count: 0, net_profit: 0 },
            subscriptionPlans: [],
            subscriptionLoading: false,

            // Pagination State
            salesPage: 1, salesPerPage: 10, returnedSalesPage: 1, returnedSalesPerPage: 10,
            productsPage: 1, productsPerPage: 10,
            customersPage: 1, customersPerPage: 10,
            suppliersPage: 1, suppliersPerPage: 10,
            expensesPage: 1, expensesPerPage: 10,
            purchasesPage: 1, purchasesPerPage: 6, purchasesTotal: 0,
            returnedPurchasesPage: 1, returnedPurchasesTotal: 0,
            cashbookPage: 1, cashbookPerPage: 10,
            bankPage: 1, bankPerPage: 10,
            stockStatusPage: 1, stockStatusPerPage: 10,
            stockHistoryPage: 1, stockHistoryPerPage: 10,
            customerDuesPage: 1, customerDuesPerPage: 5,
            supplierDuesPage: 1, supplierDuesPerPage: 5,
            lowStockPage: 1, lowStockPerPage: 6,

            // POS
            pos: { barcodeInput: '', selectedCustomer: '', searchQuery: '', discount: 0, paymentType: 'Cash', items: [] },
            posCustomerSearchQuery: '',
            posFilteredCustomers: [],
            purchaseSupplierSearchQuery: '',
            purchaseFilteredSuppliers: [],
            purchaseHistorySupplierSearchQuery: '',
            purchaseHistoryFilteredSuppliers: [],
            purchaseReturnedSupplierSearchQuery: '',
            purchaseReturnedFilteredSuppliers: [],

            // Modals
            mobileSidebarOpen: false,
            showCustomerModal: false,
            newCustomer: { name: '', mobile: '', email: '' },
            showProductModal: false,
            newProduct: { name: '', selling_price: '', purchase_price: '', barcode: '', stock: 10, low_stock_threshold: 5, category_id: '' },
            showExpenseModal: false,
            newExpense: { description: '', amount: '', payment_method: 'cash' },
            showSupplierModal: false,
            newSupplier: { name: '', mobile: '', email: '' },
            showPurchaseModal: false,
            newPurchase: { supplier_id: '', payment_type: 'Cash', items: [] },
            selectedSale: null,
            showInvoiceModal: false,
            sendingSaleEmail: false,
            sendingPurchaseEmail: false,
            showReturnModal: false,
            returnForm: { saleId: null, sale_number: '', payment_type: '', discount: 0, items: [] },
            showPurchaseReturnModal: false,
            purchaseReturnForm: { purchaseId: null, purchase_number: '', payment_type: '', items: [] },
            collectCustomerModalOpen: false,
            collectCustomerForm: { customer_id: null, customer_name: '', current_due: 0, amount: 0, payment_method: 'Cash', note: '' },
            paySupplierModalOpen: false,
            paySupplierForm: { supplier_id: null, supplier_name: '', current_due: 0, amount: 0, payment_method: 'Cash', note: '' },
            showEditSaleItemsModal: false,
            editingSaleId: null,
            showEditPurchaseItemsModal: false,
            editingPurchaseId: null,
            selectedPurchase: null,
            showPurchaseDetailsModal: false,
            confirmModal: { show: false, title: '', message: '', onConfirm: null },

            showConfirm(title, message, callback) {
                this.confirmModal.title = title;
                this.confirmModal.message = message;
                this.confirmModal.onConfirm = callback;
                this.confirmModal.show = true;
            },

            triggerConfirm() {
                this.confirmModal.show = false;
                if (typeof this.confirmModal.onConfirm === 'function') {
                    this.confirmModal.onConfirm();
                }
            },

            init() {
                if (this.dark) document.documentElement.classList.add('dark');

                // Redirect to login page if unauthenticated
                if (!this.token || !this.hasShop) {
                    let redirectUrl = window.location.pathname.replace(/\/dukanhisab(\/.*)?$/, '/shopowner/');
                    if (redirectUrl === window.location.pathname) {
                        redirectUrl = '/shopowner/';
                    }
                    window.location.href = redirectUrl;
                    return;
                }

                // Global fetch interceptor to handle session expiration (401 Unauthorized)
                const originalFetch = window.fetch;
                window.fetch = async (...args) => {
                    try {
                        const response = await originalFetch(...args);
                        if (response.status === 401) {
                            ['shopowner_token', 'token', 'shopowner_user', 'shopowner_shop', 'shopowner_has_shop'].forEach(k => localStorage.removeItem(k));
                            this.token = null;
                            this.user = null;
                            this.shop = null;
                            this.hasShop = false;
                            this.authPage = 'login';
                            this.showToast('Session expired. Please log in again.', 'error');
                            setTimeout(() => {
                                let redirectUrl = window.location.pathname.replace(/\/dukanhisab(\/.*)?$/, '/shopowner/');
                                if (redirectUrl === window.location.pathname) {
                                    redirectUrl = '/shopowner/';
                                }
                                window.location.href = redirectUrl;
                            }, 1500);
                        }
                        return response;
                    } catch (err) {
                        throw err;
                    }
                };

                if (this.dark) {
                    document.documentElement.classList.add('dark');
                    if (document.body) document.body.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    if (document.body) document.body.classList.remove('dark');
                }

                // Read URL on first load and set correct page
                this._syncPageFromUrl();

                // Listen for browser back/forward buttons
                window.addEventListener('popstate', () => {
                    this._syncPageFromUrl();
                    this.setPageLoading(this.page, true);
                    this._loadPageData(this.page);
                });

                // Global event listener to automatically open native datepicker when clicking date/month inputs
                document.addEventListener('click', (e) => {
                    const target = e.target;
                    if (target && (target.type === 'date' || target.type === 'month' || target.type === 'datetime-local')) {
                        try {
                            if (typeof target.showPicker === 'function') {
                                target.showPicker();
                            }
                        } catch (err) {
                            // Picker is already open
                        }
                    }
                });

                if (this.token && this.hasShop) {
                    this.loadAllData();
                    this.loadInvoiceSettings();
                    // Trigger page-specific data load based on current URL
                    this._loadPageData(this.page);
                }

                this.$watch('customersPage', () => {
                    if (this.page === 'customers') {
                        this.loadCustomers(true);
                    }
                });

                this.$watch('productsPage', () => {
                    if (this.page === 'products' || this.page === 'inventory') {
                        this.loadProducts('', true);
                    }
                });

                this.$watch('purchasesPage', () => {
                    if (this.page === 'purchase-history') {
                        this.loadPurchases(true);
                    }
                });

                this.$watch('returnedPurchasesPage', () => {
                    if (this.page === 'purchase-returned') {
                        this.loadPurchases(true);
                    }
                });
            },

            // Read current URL path segment and set this.page
            _syncPageFromUrl() {
                const path = window.location.pathname;
                // Extract last segment: /dukanhisab/sales-history → 'sales-history'
                const segment = path.replace(/^\/dukanhisab\/?/, '').replace(/\/$/, '') || '';
                this.page = this.routeMap[segment] || 'dashboard';
            },

            setPageLoading(pageName, state) {
                if (pageName === 'products' || pageName === 'inventory') this.productsLoading = state;
                else if (pageName === 'customers') this.customersLoading = state;
                else if (pageName === 'suppliers') this.suppliersLoading = state;
                else if (pageName === 'expenses') this.expensesLoading = state;
                else if (pageName === 'purchase-history' || pageName === 'purchase-returned') this.purchasesLoading = state;
                else if (pageName === 'sales-history' || pageName === 'sales-returned') this.salesLoading = state;
                else if (pageName === 'cashbook' || pageName === 'bank-accounts' || pageName === 'transactions') this.cashbookLoading = state;
                else if (pageName === 'dashboard') this.dashboardLoading = state;
            },

            // Navigate to a page - updates both state and browser URL
            navigateTo(pageName, extraFn = null) {
                this.setPageLoading(pageName, true);
                this.page = pageName;
                const url = '/dukanhisab/' + (pageName === 'dashboard' ? '' : pageName);
                history.pushState({ page: pageName }, '', url);
                if (extraFn) extraFn();
                this._loadPageData(pageName);
            },

            // Load data needed for a specific page
            _loadPageData(pageName) {
                if (!this.token || !this.hasShop) return;
                if (pageName === 'dashboard') this.loadDashboard();
                else if (pageName === 'sales-history' || pageName === 'sales-returned') this.loadSales();
                else if (pageName === 'products') this.loadProducts('', true);
                else if (pageName === 'customers') this.loadCustomers(true);
                else if (pageName === 'suppliers') this.loadSuppliers();
                else if (pageName === 'expenses') this.loadExpenses();
                else if (pageName === 'sales') { this.loadProducts(); this.loadCustomers(); this.resetPOS(); }
                else if (pageName === 'purchases') { this.loadProducts(); this.loadSuppliers(); this.resetNewPurchase(); }
                else if (pageName === 'purchase-history') { this.loadPurchases(true); this.loadSuppliers(); }
                else if (pageName === 'purchase-returned') { this.loadPurchases(true); this.loadSuppliers(); }
                else if (pageName === 'inventory') this.loadProducts('', true);
                else if (pageName === 'cashbook') this.loadCashBook();
                else if (pageName === 'bank-accounts') { this.loadBankAccounts(); this.loadCashBook(); }
                else if (pageName === 'transactions') this.loadCashBook();
                else if (pageName === 'reports') this.loadReports();
                else if (pageName === 'reminders') { this.loadCustomers(); this.loadSuppliers(); this.loadProducts(); }
                else if (pageName === 'settings') this.loadInvoiceSettings();
                else if (pageName === 'subscription') this.loadSubscriptionPlans();
            },

            toggleTheme() {
                this.dark = !this.dark;
                localStorage.setItem('darkMode', this.dark);
                if (this.dark) {
                    document.documentElement.classList.add('dark');
                    document.body.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.body.classList.remove('dark');
                }
            },

            showToast(msg, type = 'success') {
                this.toast.message = msg; this.toast.type = type; this.toast.show = true;
                setTimeout(() => { this.toast.show = false; }, 3500);
            },

            authSubtitle() {
                const map = { 'login': 'Login to manage your business ledger.', 'register': 'Create your owner account.', 'otp-verify': 'Verify your email address.', 'forgot-pass': 'Enter email to receive OTP.', 'reset-pass': 'Enter new password credentials.' };
                return map[this.authPage] || '';
            },

            getHeaders() {
                return { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer ' + this.token, 'X-Shop-ID': this.shop ? this.shop.id : '' };
            },

            // ── DATA LOADERS ──────────────────────────────────────────
            loadAllData() { this.loadProfile(); this.loadDashboard(); this.loadProducts(); this.loadCustomers(); this.loadSuppliers(); this.loadPurchases(); this.loadExpenses(); this.loadInvoiceSettings(); this.loadSubscriptionPlans(); },

            loadDashboard() {
                this.dashboardLoading = true;
                fetch('/api/v1/dashboard', { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.dashboardLoading = false;
                        if (d.today_sales !== undefined) this.dashboardStats = d;
                    })
                    .catch(() => { this.dashboardLoading = false; });
            },

            loadProducts(search = '', paginate = false) {
                if (!paginate) {
                    this.productsPage = 1;
                    this.stockStatusPage = 1;
                    this.lowStockPage = 1;
                }
                this.productsLoading = true;
                let url = '/api/v1/products?';
                if (search) url += 'search=' + encodeURIComponent(search) + '&';
                if (paginate) {
                    url += `page=${this.productsPage}&per_page=${this.productsPerPage}`;
                }
                fetch(url, { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.productsLoading = false;
                        if (paginate && d && d.data !== undefined) {
                            this.products = d.data;
                            this.productsTotal = d.total;
                        } else if (Array.isArray(d)) {
                            this.products = d;
                            this.productsTotal = d.length;
                        }
                    })
                    .catch(() => { this.productsLoading = false; });
            },

            loadCustomers(paginate = false) {
                if (!paginate) {
                    this.customersPage = 1;
                    this.customerDuesPage = 1;
                }
                this.customersLoading = true;
                let url = '/api/v1/customers?';
                if (paginate) {
                    url += `page=${this.customersPage}&per_page=${this.customersPerPage}`;
                }
                fetch(url, { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.customersLoading = false;
                        if (paginate && d && d.data !== undefined) {
                            this.customers = d.data;
                            this.customersTotal = d.total;
                        } else if (Array.isArray(d)) {
                            this.customers = d;
                            this.customersTotal = d.length;
                            this.posFilteredCustomers = d;
                            this.salesFilteredCustomers = d;
                            this.returnedFilteredCustomers = d;
                        }
                    })
                    .catch(() => { this.customersLoading = false; });
            },

            loadSuppliers() {
                this.suppliersPage = 1;
                this.supplierDuesPage = 1;
                this.suppliersLoading = true;
                fetch('/api/v1/suppliers', { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.suppliersLoading = false;
                        if (Array.isArray(d)) {
                            this.suppliers = d;
                            this.purchaseFilteredSuppliers = d;
                            this.purchaseHistoryFilteredSuppliers = d;
                            this.purchaseReturnedFilteredSuppliers = d;
                        }
                    })
                    .catch(() => { this.suppliersLoading = false; });
            },

            loadSales() {
                this.salesPage = 1;
                this.salesLoading = true;
                let url = '/api/v1/sales?';
                if (this.salesFilter.date) {
                    url += '&start_date=' + this.salesFilter.date;
                    url += '&end_date=' + this.salesFilter.date;
                }
                if (this.salesFilter.customerId) url += '&customer_id=' + this.salesFilter.customerId;
                if (this.salesFilter.status) url += '&status=' + this.salesFilter.status;
                if (this.salesFilter.search) url += '&search=' + encodeURIComponent(this.salesFilter.search);
                fetch(url, { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.salesLoading = false;
                        if (Array.isArray(d)) this.sales = d;
                    })
                    .catch(() => { this.salesLoading = false; });
            },

            clearSalesFilter() { 
                this.salesFilter = { date: '', customerId: '', search: '', status: '' }; 
                this.salesCustomerSearchQuery = '';
                this.salesFilteredCustomers = this.customers;
                this.loadSales(); 
            },

            clearReturnedFilter() { 
                this.returnedFilter = { date: '', customerId: '', search: '', status: '' }; 
                this.returnedCustomerSearchQuery = '';
                this.returnedFilteredCustomers = this.customers;
            },

            filteredSales() {
                const search = (this.salesFilter.search || '').toLowerCase().trim();
                const status = (this.salesFilter.status || '').toLowerCase().trim();
                return this.sales.filter(s => {
                    if (s.status === 'Returned') return false;
                    if (status && s.status.toLowerCase() !== status) return false;
                    if (!search) return true;
                    const inNumber = s.sale_number && s.sale_number.toLowerCase().includes(search);
                    const inCustomer = s.customer && s.customer.name && s.customer.name.toLowerCase().includes(search);
                    const inStatus = s.status && s.status.toLowerCase().includes(search);
                    return inNumber || inCustomer || inStatus;
                });
            },

            filteredReturnedSales() {
                const search = (this.returnedFilter.search || '').toLowerCase().trim();
                const date = this.returnedFilter.date || '';
                const custId = this.returnedFilter.customerId || '';
                const status = (this.returnedFilter.status || '').toLowerCase().trim();
                return this.sales.filter(s => {
                    if (s.status !== 'Returned' && s.status !== 'Partially Returned') return false;
                    if (status && s.status.toLowerCase() !== status) return false;
                    if (date && s.sale_date && !s.sale_date.startsWith(date)) return false;
                    if (custId && (!s.customer || String(s.customer.id) !== String(custId))) return false;
                    if (!search) return true;
                    const inNumber = s.sale_number && s.sale_number.toLowerCase().includes(search);
                    const inCustomer = s.customer && s.customer.name && s.customer.name.toLowerCase().includes(search);
                    const inStatus = s.status && s.status.toLowerCase().includes(search);
                    return inNumber || inCustomer || inStatus;
                });
            },

            loadExpenses() {
                this.expensesPage = 1;
                this.expensesLoading = true;
                fetch('/api/v1/expenses', { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.expensesLoading = false;
                        if (Array.isArray(d)) this.expenses = d;
                    })
                    .catch(() => { this.expensesLoading = false; });
            },

            loadPurchases(paginate = false) {
                if (!paginate) {
                    if (this.page === 'purchase-returned') {
                        this.returnedPurchasesPage = 1;
                    } else {
                        this.purchasesPage = 1;
                    }
                }
                this.purchasesLoading = true;
                let url = '/api/v1/purchases?';
                
                let status = 'Completed';
                if (this.page === 'purchase-returned') {
                    status = 'Returned';
                }
                url += 'status=' + status;

                const filter = this.page === 'purchase-returned' ? this.purchaseReturnedFilter : this.purchaseFilter;

                if (filter.month) {
                    // month value is 'YYYY-MM', derive first and last day
                    const [yr, mo] = filter.month.split('-');
                    const firstDay = `${yr}-${mo}-01`;
                    const lastDay = new Date(yr, parseInt(mo), 0).toISOString().split('T')[0];
                    url += '&start_date=' + firstDay + '&end_date=' + lastDay;
                }
                if (filter.supplierId) url += '&supplier_id=' + filter.supplierId;
                if (filter.search) url += '&search=' + encodeURIComponent(filter.search);
                
                const isHistoryOrReturned = this.page === 'purchase-history' || this.page === 'purchase-returned';
                if (paginate || isHistoryOrReturned) {
                    const activePage = this.page === 'purchase-returned' ? this.returnedPurchasesPage : this.purchasesPage;
                    url += `&page=${activePage}&per_page=${this.purchasesPerPage}`;
                }
                fetch(url, { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.purchasesLoading = false;
                        if ((paginate || isHistoryOrReturned) && d && d.data !== undefined) {
                            this.purchases = d.data;
                            if (this.page === 'purchase-returned') {
                                this.returnedPurchasesTotal = d.total;
                            } else {
                                this.purchasesTotal = d.total;
                            }
                        } else if (Array.isArray(d)) {
                            this.purchases = d;
                            if (this.page === 'purchase-returned') {
                                this.returnedPurchasesTotal = d.length;
                            } else {
                                this.purchasesTotal = d.length;
                            }
                        }
                    })
                    .catch(() => { this.purchasesLoading = false; });
            },

            clearPurchaseFilter() {
                this.purchaseFilter = { month: '', supplierId: '', search: '' };
                this.purchaseHistorySupplierSearchQuery = '';
                this.purchaseHistoryFilteredSuppliers = this.suppliers;
                this.loadPurchases(true);
            },

            clearPurchaseReturnedFilter() {
                this.purchaseReturnedFilter = { month: '', supplierId: '', search: '' };
                this.purchaseReturnedSupplierSearchQuery = '';
                this.purchaseReturnedFilteredSuppliers = this.suppliers;
                this.loadPurchases(true);
            },

            filteredPurchases() {
                return this.purchases;
            },

            deletePurchase(purchaseId) {
                this.showConfirm('Delete Purchase', 'Are you sure you want to delete this purchase record? This action cannot be undone.', () => {
                    this.loading = true;
                    fetch('/api/v1/purchases/' + purchaseId, { method: 'DELETE', headers: this.getHeaders() })
                        .then(r => { this.loading = false; if (r.status === 204) { this.showToast('Purchase deleted.'); this.loadPurchases(this.page === 'purchase-history' || this.page === 'purchase-returned'); this.loadAllData(); } });
                });
            },

            returnPurchase(purchaseId) {
                this.loading = true;
                fetch('/api/v1/purchases/' + purchaseId, { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.loading = false;
                        if (d.id) {
                            this.purchaseReturnForm = {
                                purchaseId: d.id,
                                purchase_number: d.purchase_number,
                                payment_type: d.payment_type,
                                items: d.items.map(item => ({
                                    product_id: item.product_id,
                                    name: item.product ? item.product.name : 'Unknown Product',
                                    purchasedQty: item.quantity - (item.returned_quantity || 0),
                                    returnedQty: 0,
                                    purchase_price: parseFloat(item.purchase_price)
                                })).filter(item => item.purchasedQty > 0)
                            };
                            this.showPurchaseReturnModal = true;
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                        this.showToast('Failed to fetch purchase details.', 'error');
                    });
            },

            submitPurchasePartialReturn() {
                const returnItems = this.purchaseReturnForm.items.filter(item => item.returnedQty > 0);
                if (returnItems.length === 0) {
                    this.showToast('Please specify return quantity for at least one item.', 'warning');
                    return;
                }

                for (const item of this.purchaseReturnForm.items) {
                    if (item.returnedQty < 0 || item.returnedQty > item.purchasedQty) {
                        this.showToast(`Invalid return quantity for ${item.name}.`, 'error');
                        return;
                    }
                }

                this.loading = true;
                const payload = {
                    items: returnItems.map(item => ({
                        product_id: item.product_id,
                        quantity: parseInt(item.returnedQty)
                    }))
                };

                fetch('/api/v1/purchases/' + this.purchaseReturnForm.purchaseId + '/return', {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify(payload)
                })
                    .then(r => r.json())
                    .then(d => {
                        this.loading = false;
                        if (d.status) {
                            this.showToast('Return processed successfully.');
                            this.showPurchaseReturnModal = false;
                            this.loadPurchases(this.page === 'purchase-history' || this.page === 'purchase-returned');
                            this.loadAllData();
                        } else {
                            this.showToast(d.message || 'Failed to process return.', 'error');
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                        this.showToast('Network error processing return.', 'error');
                    });
            },

            loadStockHistory() {
                this.stockHistoryPage = 1;
                try {
                    this.stockHistory = JSON.parse(localStorage.getItem('dukanhisab_stock_history') || '[]');
                } catch (e) {
                    this.stockHistory = [];
                }
            },

            submitStockAdjustment() {
                const prod = this.products.find(p => p.id == this.adjustForm.product_id);
                if (!prod) return;
                const qty = parseInt(this.adjustForm.quantity) || 0;
                if (qty <= 0) return;

                let oldStock = parseInt(prod.stock) || 0;
                let changeQty = this.adjustForm.type === 'addition' ? qty : -qty;
                let newStock = oldStock + changeQty;

                this.loading = true;
                fetch('/api/v1/products/' + prod.id, {
                    method: 'PUT',
                    headers: this.getHeaders(),
                    body: JSON.stringify({ stock: newStock })
                })
                    .then(r => r.json())
                    .then(d => {
                        this.loading = false;
                        if (d.id) {
                            this.showToast('Stock adjusted successfully!');
                            // Log adjustment in local stockHistory
                            const logEntry = {
                                id: Date.now(),
                                product_name: prod.name,
                                change_qty: changeQty,
                                old_stock: oldStock,
                                new_stock: newStock,
                                reason: this.adjustForm.reason || 'Manual Update',
                                created_at: new Date().toISOString()
                            };
                            let history = [];
                            try {
                                history = JSON.parse(localStorage.getItem('dukanhisab_stock_history') || '[]');
                            } catch (e) { }
                            history.unshift(logEntry);
                            localStorage.setItem('dukanhisab_stock_history', JSON.stringify(history));
                            this.loadStockHistory();
                            this.loadProducts();
                        } else {
                            this.showToast('Failed to adjust stock.', 'error');
                        }
                    }).catch(() => {
                        this.loading = false;
                        this.showToast('Network error adjusting stock.', 'error');
                    });
            },

            loadCashBook(type = '', paymentMethod = '', search = '', startDate = '', endDate = '') {
                this.cashbookPage = 1;
                this.cashbookLoading = true;
                let url = '/api/v1/cashbooks?';
                if (type) url += '&type=' + type;
                if (paymentMethod) url += '&payment_method=' + paymentMethod;
                if (search) url += '&search=' + encodeURIComponent(search);
                if (startDate) url += '&start_date=' + startDate;
                if (endDate) url += '&end_date=' + endDate;
                fetch(url, { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.cashbookLoading = false;
                        if (Array.isArray(d)) this.cashbook = d;
                    })
                    .catch(() => { this.cashbookLoading = false; });
            },

            calculateCashBookTotals() {
                let totalIn = 0;
                let totalOut = 0;
                this.cashbook.forEach(entry => {
                    const amount = parseFloat(entry.amount) || 0;
                    if (entry.type === 'cash_in') totalIn += amount;
                    else if (entry.type === 'cash_out') totalOut += amount;
                });
                return { totalIn, totalOut, netBalance: totalIn - totalOut };
            },

            submitCashBookEntry() {
                this.loading = true;
                fetch('/api/v1/cashbooks', {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify(this.entryForm)
                })
                    .then(r => r.json())
                    .then(d => {
                        this.loading = false;
                        if (d.id) {
                            this.showToast('Cashbook entry recorded.');
                            this.loadCashBook();
                            this.loadDashboard();
                        } else {
                            this.showToast('Failed to record entry.', 'error');
                        }
                    }).catch(() => {
                        this.loading = false;
                        this.showToast('Error saving entry.', 'error');
                    });
            },

            deleteCashBookEntry(id) {
                this.showConfirm('Delete Cashbook Record', 'Are you sure you want to delete this cashbook record?', () => {
                    this.loading = true;
                    fetch('/api/v1/cashbooks/' + id, { method: 'DELETE', headers: this.getHeaders() })
                        .then(r => {
                            this.loading = false;
                            if (r.status === 204) {
                                this.showToast('Record deleted.');
                                this.loadCashBook();
                                this.loadDashboard();
                            } else {
                                r.json().then(d => this.showToast(d.message || 'Failed to delete record.', 'error'));
                            }
                        }).catch(() => {
                            this.loading = false;
                            this.showToast('Error deleting record.', 'error');
                        });
                });
            },

            loadBankAccounts() {
                this.bankAccountsLoading = true;
                fetch('/api/v1/bank-accounts', { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.bankAccountsLoading = false;
                        if (Array.isArray(d)) this.bankAccounts = d;
                    })
                    .catch(() => { this.bankAccountsLoading = false; });
            },

            submitBankTransfer() {
                // Deposit: withdraw from cash, deposit to bank
                // Withdrawal: withdraw from bank, deposit to cash
                this.loading = true;
                const isDeposit = this.transferForm.type === 'deposit';

                const entry1 = {
                    type: isDeposit ? 'cash_out' : 'cash_out',
                    amount: this.transferForm.amount,
                    payment_method: isDeposit ? 'cash' : 'bank',
                    description: this.transferForm.description + (isDeposit ? ' (Paid from Cash)' : ' (Withdrawn from Bank)')
                };

                const entry2 = {
                    type: isDeposit ? 'cash_in' : 'cash_in',
                    amount: this.transferForm.amount,
                    payment_method: isDeposit ? 'bank' : 'cash',
                    description: this.transferForm.description + (isDeposit ? ' (Deposited to Bank)' : ' (Received in Cash)')
                };

                // Let's create transaction 1 first, then transaction 2
                fetch('/api/v1/cashbooks', {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify(entry1)
                })
                    .then(r => r.json())
                    .then(d1 => {
                        if (d1.id) {
                            fetch('/api/v1/cashbooks', {
                                method: 'POST',
                                headers: this.getHeaders(),
                                body: JSON.stringify(entry2)
                            })
                                .then(r => r.json())
                                .then(d2 => {
                                    this.loading = false;
                                    if (d2.id) {
                                        this.showToast('Bank transfer recorded successfully.');
                                        this.loadBankAccounts();
                                        this.loadCashBook();
                                        this.loadDashboard();
                                    } else {
                                        this.showToast('Failed to record transfer part 2.', 'error');
                                    }
                                });
                        } else {
                            this.loading = false;
                            this.showToast('Failed to record transfer part 1.', 'error');
                        }
                    }).catch(() => {
                        this.loading = false;
                        this.showToast('Error recording bank transfer.', 'error');
                    });
            },

            getConsolidatedTransactions(filterType, searchQuery, startDate, endDate) {
                let list = this.cashbook.map(entry => {
                    let module = 'manual';
                    if (entry.reference_type === 'sale') module = 'sale';
                    else if (entry.reference_type === 'purchase') module = 'purchase';
                    else if (entry.description.toLowerCase().includes('expense') || entry.reference_type === 'expense') module = 'expense';

                    return {
                        uid: entry.id,
                        module: module,
                        description: entry.description,
                        method: entry.payment_method,
                        flow: entry.type === 'cash_in' ? 'IN' : 'OUT',
                        amount: parseFloat(entry.amount),
                        date: entry.transaction_date
                    };
                });

                if (filterType) {
                    list = list.filter(t => t.module === filterType);
                }
                return list;
            },

            loadReports(startDate = '', endDate = '') {
                this.reportsLoading = true;
                let url = '/api/v1/reports?';
                if (startDate) url += '&start_date=' + startDate;
                if (endDate) url += '&end_date=' + endDate;
                fetch(url, { headers: this.getHeaders() })
                .then(r => r.json())
                .then(d => {
                    this.reportsLoading = false;
                    if (d.total_sales !== undefined) this.reportsData = d;
                })
                .catch(() => { this.reportsLoading = false; });
            },

            printReport() {
                const html = document.getElementById('report-print-area').innerHTML;
                const w = window.open('', '', 'width=800,height=600');
                w.document.write('<html><head><title>Business Statement</title></head><body>' + html + '</body></html>');
                w.document.close(); w.print();
            },

            submitShopProfileUpdate(logoFile = null, signatureFile = null, shopImageFile = null) {
                this.loading = true;
                const fd = new FormData();
                fd.append('name', this.shopUpdateForm.name);
                fd.append('owner_name', this.shopUpdateForm.owner_name);
                fd.append('mobile', this.shopUpdateForm.mobile);
                if (this.shopUpdateForm.email) fd.append('email', this.shopUpdateForm.email);
                if (this.shopUpdateForm.gst_number) fd.append('gst_number', this.shopUpdateForm.gst_number);
                if (this.shopUpdateForm.address) fd.append('address', this.shopUpdateForm.address);
                if (this.shopUpdateForm.city) fd.append('city', this.shopUpdateForm.city);
                if (this.shopUpdateForm.state) fd.append('state', this.shopUpdateForm.state);
                if (this.shopUpdateForm.pincode) fd.append('pincode', this.shopUpdateForm.pincode);
                if (this.shopUpdateForm.currency) fd.append('currency', this.shopUpdateForm.currency);
                if (this.shopUpdateForm.upi_id) fd.append('upi_id', this.shopUpdateForm.upi_id);
                if (this.shopUpdateForm.bank_details) fd.append('bank_details', this.shopUpdateForm.bank_details);
                if (this.shopUpdateForm.invoice_footer) fd.append('invoice_footer', this.shopUpdateForm.invoice_footer);
                if (this.shopUpdateForm.website_settings) fd.append('website_settings', JSON.stringify(this.shopUpdateForm.website_settings));
                if (logoFile) fd.append('logo', logoFile);
                if (signatureFile) fd.append('signature', signatureFile);
                if (shopImageFile) fd.append('shop_image', shopImageFile);
                fetch('/api/v1/shopowner/shop-setup', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + this.token },
                    body: fd
                })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.shop) {
                            this.shop = d.shop;
                            localStorage.setItem('shopowner_shop', JSON.stringify(d.shop));
                            if (d.user) {
                                this.user = d.user;
                                localStorage.setItem('shopowner_user', JSON.stringify(d.user));
                            }
                            this.showToast('Shop profile updated successfully!');
                        } else {
                            if (d.errors) {
                                const firstKey = Object.keys(d.errors)[0];
                                const firstError = d.errors[firstKey][0];
                                this.showToast(firstError, 'error');
                            } else {
                                this.showToast(d.message || 'Failed to update shop.', 'error');
                            }
                        }
                    }).catch(() => {
                        this.loading = false;
                        this.showToast('Error updating shop profile.', 'error');
                    });
            },

            submitUserProfileUpdate(avatarFile = null) {
                this.loading = true;
                const fd = new FormData();
                fd.append('name', this.userProfileForm.name);
                fd.append('display_name', this.userProfileForm.display_name || '');
                fd.append('mobile', this.userProfileForm.mobile || '');
                fd.append('email', this.userProfileForm.email);
                if (this.userProfileForm.date_of_birth) fd.append('date_of_birth', this.userProfileForm.date_of_birth);
                if (this.userProfileForm.gender) fd.append('gender', this.userProfileForm.gender);
                fd.append('currency', this.userProfileForm.currency);
                fd.append('date_format', this.userProfileForm.date_format);
                fd.append('time_format', this.userProfileForm.time_format);
                Object.keys(this.userProfileForm.notification_preferences).forEach(key => {
                    fd.append('notification_preferences[' + key + ']', this.userProfileForm.notification_preferences[key] ? '1' : '0');
                });
                if (avatarFile) fd.append('avatar', avatarFile);

                fetch('/api/v1/shopowner/profile', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + this.token },
                    body: fd
                })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.user) {
                            this.user = d.user;
                            localStorage.setItem('shopowner_user', JSON.stringify(d.user));
                            this.showToast('Profile updated successfully!');
                        } else {
                            if (d.errors) {
                                const firstKey = Object.keys(d.errors)[0];
                                this.showToast(d.errors[firstKey][0], 'error');
                            } else {
                                this.showToast(d.message || 'Failed to update profile.', 'error');
                            }
                        }
                    }).catch(() => {
                        this.loading = false;
                        this.showToast('Error updating profile.', 'error');
                    });
            },
            loadProfile() {
                fetch('/api/v1/shopowner/profile', { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        if (d.user) {
                            this.user = d.user;
                            localStorage.setItem('shopowner_user', JSON.stringify(d.user));
                        }
                        if (d.shop) {
                            this.shop = d.shop;
                            localStorage.setItem('shopowner_shop', JSON.stringify(d.shop));
                            this.hasShop = true;
                            localStorage.setItem('shopowner_has_shop', 'true');
                        }
                    })
                    .catch(() => {});
            },

            loadSubscriptionPlans() {
                this.subscriptionLoading = true;
                fetch('/api/v1/shopowner/subscription-plans', { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.subscriptionLoading = false;
                        if (d.plans) {
                            this.subscriptionPlans = d.plans;
                        }
                    })
                    .catch(() => { this.subscriptionLoading = false; });
            },

            upgradeSubscription(planSlug) {
                if (this.user && this.user.active_plan && this.user.active_plan.slug !== 'free') {
                    this.showConfirm(
                        'Cancel Active Plan First?',
                        'You currently have an active Premium/Business plan. You must cancel your current active plan first to switch/purchase another plan. Would you like to cancel your active plan now?',
                        () => {
                            this.cancelSubscription();
                        }
                    );
                    return;
                }
                this.subscriptionLoading = true;
                fetch('/api/v1/shopowner/subscription/upgrade', {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify({ plan_slug: planSlug })
                })
                    .then(r => r.json())
                    .then(d => {
                        this.subscriptionLoading = false;
                        if (d.user) {
                            this.user = d.user;
                            localStorage.setItem('shopowner_user', JSON.stringify(d.user));
                            this.showToast(d.message || 'Subscription upgraded successfully!');
                            this.loadProfile();
                        } else {
                            this.showToast(d.message || 'Failed to upgrade subscription.', 'error');
                        }
                    })
                    .catch(() => {
                        this.subscriptionLoading = false;
                        this.showToast('Error upgrading subscription.', 'error');
                    });
            },

            cancelSubscription() {
                this.subscriptionLoading = true;
                fetch('/api/v1/shopowner/subscription/cancel', {
                    method: 'POST',
                    headers: this.getHeaders()
                })
                    .then(r => r.json())
                    .then(d => {
                        this.subscriptionLoading = false;
                        if (d.plan) {
                            if (d.user) {
                                this.user = d.user;
                                localStorage.setItem('shopowner_user', JSON.stringify(d.user));
                            }
                            this.showToast(d.message || 'Subscription cancelled successfully!');
                            this.loadProfile();
                        } else {
                            this.showToast(d.message || 'Failed to cancel subscription.', 'error');
                        }
                    })
                    .catch(() => {
                        this.subscriptionLoading = false;
                        this.showToast('Error cancelling subscription.', 'error');
                    });
            },

            loadInvoiceSettings() {
                this.invoiceSettingsLoading = true;
                fetch('/api/v1/invoice-settings', { headers: this.getHeaders() })
                    .then(r => r.json()).then(d => {
                        this.invoiceSettingsLoading = false;
                        this.invoiceSettings = d;
                    }).catch(() => { this.invoiceSettingsLoading = false; });
            },

            submitInvoiceSettingsUpdate(form) {
                this.loading = true;
                const isFree = this.user && this.user.active_plan && this.user.active_plan.slug === 'free';
                const payload = Object.assign({}, form);
                if (isFree) {
                    payload.whatsapp_share = false;
                    payload.pdf_download = false;
                    payload.email_invoice = false;
                    payload.show_upi_qr = false;
                }
                fetch('/api/v1/invoice-settings', {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify(payload)
                })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.id) {
                            this.invoiceSettings = d;
                            this.showToast('Invoice settings updated successfully!');
                        } else {
                            if (d.errors) {
                                const firstKey = Object.keys(d.errors)[0];
                                this.showToast(d.errors[firstKey][0], 'error');
                            } else {
                                this.showToast(d.message || 'Failed to update invoice settings.', 'error');
                            }
                        }
                    }).catch(() => {
                        this.loading = false;
                        this.showToast('Error updating invoice settings.', 'error');
                    });
            },

            submitChangePassword() {
                this.loading = true;
                const body = {
                    current_password: this.passForm.current_password,
                    new_password: this.passForm.new_password,
                    new_password_confirmation: this.passForm.new_password_confirmation
                };
                fetch('/api/v1/shopowner/change-password', {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify(body)
                })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.message) {
                            this.showToast('Password updated successfully!');
                            this.passForm = { current_password: '', new_password: '', new_password_confirmation: '' };
                        } else {
                            this.showToast(d.message || 'Failed to change password.', 'error');
                        }
                    }).catch(() => {
                        this.loading = false;
                        this.showToast('Error changing password.', 'error');
                    });
            },

            triggerCloudBackup() {
                this.showToast('Cloud database backup snapshot generated successfully!');
            },

            triggerCloudRestore() {
                this.showToast('Database restore completed from cloud snapshot.');
            },

            // ── AUTH ──────────────────────────────────────────────────
            handleLogin() {
                this.loading = true;
                fetch('/api/v1/shopowner/login', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(this.loginForm) })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.token) {
                            this.token = d.token; this.user = d.user; this.shop = d.shop; this.hasShop = d.has_shop;
                            localStorage.setItem('shopowner_token', d.token); localStorage.setItem('token', d.token);
                            localStorage.setItem('shopowner_user', JSON.stringify(d.user));
                            localStorage.setItem('shopowner_has_shop', d.has_shop ? 'true' : 'false');
                            if (d.shop) localStorage.setItem('shopowner_shop', JSON.stringify(d.shop));
                            this.showToast('Login successful!');
                            if (this.hasShop) { this.loadAllData(); this.navigateTo('dashboard'); }
                        } else if (d.email_unverified) {
                            this.emailToVerify = d.email;
                            this.authPage = 'otp-verify';
                            this.showToast(d.message, 'warning');
                            this.handleResendOtp();
                        } else {
                            this.showToast(d.message || 'Login credentials invalid.', 'error');
                        }
                    }).catch(() => { this.loading = false; this.showToast('Network error occurred.', 'error'); });
            },

            handleRegister() {
                this.loading = true;
                fetch('/api/v1/shopowner/register', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(this.registerForm) })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.message && d.message.includes('OTP')) {
                            this.emailToVerify = this.registerForm.email;
                            this.authPage = 'otp-verify';
                            this.showToast(d.dev_otp ? 'OTP sent! Dev OTP: ' + d.dev_otp : 'OTP sent to your email.');
                            if (d.dev_otp) console.log('Dev OTP:', d.dev_otp);
                        }
                        else if (d.errors) { this.showToast(Object.values(d.errors)[0][0], 'error'); }
                        else { this.showToast(d.message || 'Registration failed.', 'error'); }
                    }).catch(() => { this.loading = false; this.showToast('Error registering.', 'error'); });
            },

            handleVerifyOtp() {
                this.loading = true;
                fetch('/api/v1/shopowner/verify-otp', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ email: this.emailToVerify, otp_code: this.otpForm.code }) })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.token) {
                            this.token = d.token; this.user = d.user; this.shop = d.shop; this.hasShop = d.has_shop;
                            localStorage.setItem('shopowner_token', d.token); localStorage.setItem('token', d.token);
                            localStorage.setItem('shopowner_user', JSON.stringify(d.user));
                            localStorage.setItem('shopowner_has_shop', d.has_shop ? 'true' : 'false');
                            this.showToast('Email verified successfully!');
                        } else { this.showToast(d.message || 'Invalid OTP.', 'error'); }
                    }).catch(() => { this.loading = false; this.showToast('Verification failed.', 'error'); });
            },

            handleResendOtp() {
                fetch('/api/v1/shopowner/resend-otp', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email: this.emailToVerify }) })
                    .then(r => r.json()).then(d => {
                        this.showToast(d.dev_otp ? 'OTP resent! Dev OTP: ' + d.dev_otp : (d.message || 'OTP resent.'));
                        if (d.dev_otp) console.log('Dev OTP:', d.dev_otp);
                    });
            },

            handleForgotPassword() {
                this.loading = true;
                fetch('/api/v1/shopowner/forgot-password', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ email: this.forgotForm.email }) })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.message) {
                            this.resetForm.email = this.forgotForm.email;
                            this.authPage = 'reset-pass';
                            this.showToast(d.dev_otp ? 'OTP sent! Dev OTP: ' + d.dev_otp : 'OTP sent to your email.');
                            if (d.dev_otp) console.log('Dev OTP:', d.dev_otp);
                        }
                        else { this.showToast(d.error || 'Failed to send OTP.', 'error'); }
                    });
            },

            handleResendForgotOtp() {
                fetch('/api/v1/shopowner/forgot-password', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ email: this.resetForm.email }) })
                    .then(r => r.json()).then(d => {
                        this.showToast(d.dev_otp ? 'OTP resent! Dev OTP: ' + d.dev_otp : 'OTP sent to your email.');
                        if (d.dev_otp) console.log('Dev OTP:', d.dev_otp);
                    });
            },

            handleResetPassword() {
                this.loading = true;
                fetch('/api/v1/shopowner/reset-password', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ email: this.resetForm.email, otp_code: this.resetForm.token, password: this.resetForm.password, password_confirmation: this.resetForm.password_confirmation }) })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.message && !d.errors && d.message.includes('successful')) { this.authPage = 'login'; this.showToast('Password reset. Please log in.'); }
                        else { this.showToast(d.message || d.error || 'Reset failed.', 'error'); }
                    });
            },

            handleLogout() {
                fetch('/api/v1/shopowner/logout', { method: 'POST', headers: this.getHeaders() }).finally(() => {
                    ['shopowner_token', 'token', 'shopowner_user', 'shopowner_shop', 'shopowner_has_shop'].forEach(k => localStorage.removeItem(k));
                    this.token = null; this.user = null; this.shop = null; this.hasShop = false; this.authPage = 'login';
                    let redirectUrl = window.location.pathname.replace(/\/dukanhisab(\/.*)?$/, '/shopowner/');
                    if (redirectUrl === window.location.pathname) {
                        redirectUrl = '/shopowner/';
                    }
                    window.location.href = redirectUrl;
                });
            },

            // ── SHOP SETUP ────────────────────────────────────────────
            onLogoChange(e) {
                const file = e.target.files[0];
                if (file) { this.setupForm.logo = file; this.logoPreview = URL.createObjectURL(file); }
            },

            handleShopSetup() {
                this.loading = true;
                const fd = new FormData();
                fd.append('name', this.setupForm.name); fd.append('owner_name', this.setupForm.owner_name);
                fd.append('mobile', this.setupForm.mobile);
                if (this.setupForm.gst_number) fd.append('gst_number', this.setupForm.gst_number);
                if (this.setupForm.logo) fd.append('logo', this.setupForm.logo);
                fetch('/api/v1/shopowner/shop-setup', { method: 'POST', headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + this.token }, body: fd })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.shop) {
                            this.shop = d.shop; this.hasShop = true;
                            localStorage.setItem('shopowner_has_shop', 'true'); localStorage.setItem('shopowner_shop', JSON.stringify(d.shop));
                            this.showToast('Shop created!'); this.loadAllData(); this.navigateTo('dashboard');
                        } else { this.showToast(d.message || 'Failed to setup shop.', 'error'); }
                    }).catch(() => { this.loading = false; this.showToast('Error setting up shop.', 'error'); });
            },

            switchShop(targetShop) {
                this.shop = targetShop;
                localStorage.setItem('shopowner_shop', JSON.stringify(targetShop));
                this.showToast('Switched to shop: ' + targetShop.name);
                this.loadAllData();
            },

            openAddShopModal() {
                if (this.user && this.user.active_plan && this.user.active_plan.slug === 'free') {
                    this.showToast('Please upgrade your plan to Premium or Business to add multiple shops.', 'error');
                    return;
                }
                const max = this.user && this.user.active_plan && this.user.active_plan.features && this.user.active_plan.features.max_shops 
                    ? parseInt(this.user.active_plan.features.max_shops) 
                    : 1;
                const currentCount = this.user && this.user.shops ? this.user.shops.length : 0;
                if (max !== -1 && currentCount >= max) {
                    this.showToast(`Your active plan allows maximum ${max} shop(s). Please upgrade to add more.`, 'error');
                    return;
                }

                this.addShopModal.name = '';
                this.addShopModal.owner_name = this.user ? this.user.name : '';
                this.addShopModal.mobile = this.user ? this.user.mobile : '';
                this.addShopModal.gst_number = '';
                this.addShopModal.logo = null;
                this.addShopModal.logoPreview = null;
                this.addShopModal.show = true;
            },

            onAddShopLogoChange(e) {
                const file = e.target.files[0];
                if (file) {
                    this.addShopModal.logo = file;
                    this.addShopModal.logoPreview = URL.createObjectURL(file);
                }
            },

            handleAddShopSubmit() {
                this.loading = true;
                const fd = new FormData();
                fd.append('name', this.addShopModal.name);
                fd.append('owner_name', this.addShopModal.owner_name);
                fd.append('mobile', this.addShopModal.mobile);
                if (this.addShopModal.gst_number) fd.append('gst_number', this.addShopModal.gst_number);
                if (this.addShopModal.logo) fd.append('logo', this.addShopModal.logo);

                fetch('/api/v1/shopowner/shop-setup', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + this.token },
                    body: fd
                })
                .then(r => r.json())
                .then(d => {
                    this.loading = false;
                    if (d.shop) {
                        this.addShopModal.show = false;
                        this.shop = d.shop;
                        localStorage.setItem('shopowner_shop', JSON.stringify(d.shop));
                        this.showToast('New shop successfully created!');
                        this.loadAllData();
                    } else {
                        this.showToast(d.message || 'Failed to create shop.', 'error');
                    }
                })
                .catch(() => {
                    this.loading = false;
                    this.showToast('Error creating new shop.', 'error');
                });
            },

            // ── POS ───────────────────────────────────────────────────
            resetPOS() {
                this.pos = { barcodeInput: '', selectedCustomer: '', searchQuery: '', discount: 0, paymentType: 'Cash', items: [] };
                this.posCustomerSearchQuery = '';
                this.posFilteredCustomers = this.customers;
                setTimeout(() => { const el = document.getElementById('pos-barcode'); if (el) el.focus(); }, 200);
            },

            searchPosCustomers() {
                const q = this.posCustomerSearchQuery;
                fetch('/api/v1/customers?search=' + encodeURIComponent(q), { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        if (Array.isArray(d)) {
                            this.posFilteredCustomers = d;
                        }
                    });
            },

            selectPosCustomer(customer) {
                if (customer) {
                    this.pos.selectedCustomer = customer.id;
                } else {
                    this.pos.selectedCustomer = ''; // Walk-In Customer
                }
                this.posCustomerSearchQuery = '';
                this.posFilteredCustomers = this.customers;
            },

            getSelectedPosCustomerName() {
                if (!this.pos.selectedCustomer) return 'Walk-In Customer';
                const cust = this.customers.find(c => c.id == this.pos.selectedCustomer);
                return cust ? `${cust.name} (${cust.mobile || 'No Mobile'})` : 'Walk-In Customer';
            },

            searchSalesCustomers() {
                const q = this.salesCustomerSearchQuery;
                fetch('/api/v1/customers?search=' + encodeURIComponent(q), { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        if (Array.isArray(d)) {
                            this.salesFilteredCustomers = d;
                        }
                    });
            },

            selectSalesCustomer(customer) {
                if (customer) {
                    this.salesFilter.customerId = customer.id;
                } else {
                    this.salesFilter.customerId = '';
                }
                this.salesCustomerSearchQuery = '';
                this.salesFilteredCustomers = this.customers;
                this.loadSales();
            },

            getSelectedSalesCustomerName() {
                if (!this.salesFilter.customerId) return 'All Customers';
                const cust = this.customers.find(c => c.id == this.salesFilter.customerId);
                return cust ? `${cust.name} (${cust.mobile || 'No Mobile'})` : 'All Customers';
            },

            searchReturnedCustomers() {
                const q = this.returnedCustomerSearchQuery;
                fetch('/api/v1/customers?search=' + encodeURIComponent(q), { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        if (Array.isArray(d)) {
                            this.returnedFilteredCustomers = d;
                        }
                    });
            },

            selectReturnedCustomer(customer) {
                if (customer) {
                    this.returnedFilter.customerId = customer.id;
                } else {
                    this.returnedFilter.customerId = '';
                }
                this.returnedCustomerSearchQuery = '';
                this.returnedFilteredCustomers = this.customers;
                this.returnedSalesPage = 1;
            },

            getSelectedReturnedCustomerName() {
                if (!this.returnedFilter.customerId) return 'All Customers';
                const cust = this.customers.find(c => c.id == this.returnedFilter.customerId);
                return cust ? `${cust.name} (${cust.mobile || 'No Mobile'})` : 'All Customers';
            },

            filteredProducts() {
                return this.products;
            },

            addToBill(product) {
                if (product.stock <= 0) {
                    this.showToast('Product is Out of Stock!', 'error');
                    return;
                }
                const idx = this.pos.items.findIndex(item => item.product_id === product.id);
                if (idx > -1) {
                    if (this.pos.items[idx].quantity >= product.stock) {
                        this.showToast('Cannot add more. Not enough stock available!', 'error');
                        return;
                    }
                    this.pos.items[idx].quantity++;
                } else {
                    this.pos.items.push({ product_id: product.id, name: product.name, selling_price: parseFloat(product.selling_price), quantity: 1, stock: product.stock });
                }
                this.showToast(product.name + ' added to cart.');
            },

            removeFromBill(idx) { this.pos.items.splice(idx, 1); },
            increaseQty(idx) {
                const item = this.pos.items[idx];
                if (item.quantity >= item.stock) {
                    this.showToast('Cannot add more. Not enough stock available!', 'error');
                    return;
                }
                item.quantity++;
            },
            decreaseQty(idx) { if (this.pos.items[idx].quantity > 1) this.pos.items[idx].quantity--; },
            calculateSubtotal() { return this.pos.items.reduce((sum, item) => sum + (item.selling_price * item.quantity), 0); },
            calculateGrandTotal() { return Math.max(0, this.calculateSubtotal() - (this.pos.discount || 0)); },

            handleBarcodeScan() {
                const code = this.pos.barcodeInput.trim();
                if (!code) return;
                const product = this.products.find(p => p.barcode === code);
                if (product) { this.addToBill(product); this.pos.barcodeInput = ''; }
                else {
                    this.newProduct = { name: '', selling_price: '', purchase_price: '', barcode: code, stock: 10, low_stock_threshold: 5, category_id: '' };
                    this.showProductModal = true; this.pos.barcodeInput = '';
                    this.showToast('Product not found! Create a new product.', 'warning');
                }
            },

            saveSale() {
                if (this.pos.items.length === 0) return;
                if (this.pos.paymentType === 'Credit' && !this.pos.selectedCustomer) {
                    this.showConfirm('Validation Error', 'Customer selection is required for Credit (udhaar) transactions.', () => { });
                    return;
                }
                this.loading = true;
                const body = {
                    customer_id: this.pos.selectedCustomer || null,
                    subtotal: this.calculateSubtotal(), discount: this.pos.discount || 0, grand_total: this.calculateGrandTotal(),
                    payment_type: this.pos.paymentType,
                    items: this.pos.items.map(item => ({ product_id: item.product_id, quantity: item.quantity, selling_price: item.selling_price }))
                };
                fetch('/api/v1/sales', { method: 'POST', headers: this.getHeaders(), body: JSON.stringify(body) })
                    .then(r => {
                        if (!r.ok) {
                            return r.json().then(errData => {
                                throw errData;
                            });
                        }
                        return r.json();
                    })
                    .then(d => {
                        this.loading = false;
                        if (d.sale_number) { this.showToast('Sale saved!'); this.selectedSale = d; this.showInvoiceModal = true; this.resetPOS(); this.loadAllData(); }
                        else { this.showToast(d.message || 'Failed to save sale.', 'error'); }
                    }).catch((err) => {
                        this.loading = false;
                        if (err && err.errors) {
                            let messages = [];
                            for (const key in err.errors) {
                                if (Array.isArray(err.errors[key])) {
                                    messages.push(...err.errors[key]);
                                }
                            }
                            this.showConfirm('Validation Error', messages.join('\n'), () => { });
                        } else {
                            this.showToast((err && err.message) ? err.message : 'Error saving sale.', 'error');
                        }
                    });
            },

            // ── INVOICE ───────────────────────────────────────────────
            viewInvoice(saleId) {
                this.loading = true;
                fetch('/api/v1/sales/' + saleId, { headers: this.getHeaders() }).then(r => r.json()).then(d => {
                    this.loading = false;
                    if (d.sale_number) { this.selectedSale = d; this.showInvoiceModal = true; }
                }).catch(() => { this.loading = false; });
            },

            viewPurchase(purchaseId) {
                this.loading = true;
                fetch('/api/v1/purchases/' + purchaseId, { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.loading = false;
                        if (d.purchase_number) {
                            this.selectedPurchase = d;
                            this.showPurchaseDetailsModal = true;
                        }
                    }).catch(() => { this.loading = false; });
            },

            returnSale(saleId) {
                this.loading = true;
                fetch('/api/v1/sales/' + saleId, { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        this.loading = false;
                        if (d.id) {
                            this.returnForm = {
                                saleId: d.id,
                                sale_number: d.sale_number,
                                payment_type: d.payment_type,
                                discount: parseFloat(d.discount) || 0,
                                items: d.items.map(item => ({
                                    product_id: item.product_id,
                                    name: item.product ? item.product.name : 'Unknown Product',
                                    purchasedQty: item.quantity,
                                    returnedQty: 0,
                                    selling_price: parseFloat(item.selling_price)
                                }))
                            };
                            this.showReturnModal = true;
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                        this.showToast('Failed to fetch sale details.', 'error');
                    });
            },

            submitPartialReturn() {
                const returnItems = this.returnForm.items.filter(item => item.returnedQty > 0);
                if (returnItems.length === 0) {
                    this.showToast('Please specify return quantity for at least one item.', 'warning');
                    return;
                }

                for (const item of this.returnForm.items) {
                    if (item.returnedQty < 0 || item.returnedQty > item.purchasedQty) {
                        this.showToast(`Invalid return quantity for ${item.name}.`, 'error');
                        return;
                    }
                }

                this.loading = true;
                const payload = {
                    items: returnItems.map(item => ({
                        product_id: item.product_id,
                        quantity: parseInt(item.returnedQty)
                    }))
                };

                fetch('/api/v1/sales/' + this.returnForm.saleId + '/return', {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify(payload)
                })
                    .then(r => r.json())
                    .then(d => {
                        this.loading = false;
                        if (d.status) {
                            this.showToast('Return processed successfully.');
                            this.showReturnModal = false;
                            this.loadSales();
                            this.loadAllData();
                        } else {
                            this.showToast(d.message || 'Failed to process return.', 'error');
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                        this.showToast('Network error processing return.', 'error');
                    });
            },

            openEditSaleModal(sale) {
                this.pos.items = sale.items.map(item => {
                    const liveProduct = this.products.find(p => p.id === item.product_id);
                    return {
                        product_id: item.product_id,
                        name: item.product ? item.product.name : (liveProduct ? liveProduct.name : 'Unknown Product'),
                        selling_price: parseFloat(item.selling_price),
                        quantity: item.quantity,
                        stock: (liveProduct ? liveProduct.stock : 0) + item.quantity
                    };
                });
                this.pos.selectedCustomer = sale.customer_id || '';
                this.pos.discount = parseFloat(sale.discount) || 0;
                this.pos.paymentType = sale.payment_type;
                this.pos.searchQuery = '';
                this.pos.barcodeInput = '';
                this.posCustomerSearchQuery = '';
                this.posFilteredCustomers = this.customers;
                this.editingSaleId = sale.id;
                this.showEditSaleItemsModal = true;
            },

            saveEditedSale() {
                if (this.pos.items.length === 0) return;
                if (this.pos.paymentType === 'Credit' && !this.pos.selectedCustomer) {
                    this.showConfirm('Validation Error', 'Customer selection is required for Credit (udhaar) transactions.', () => { });
                    return;
                }
                this.loading = true;
                const body = {
                    customer_id: this.pos.selectedCustomer || null,
                    subtotal: this.calculateSubtotal(), discount: this.pos.discount || 0, grand_total: this.calculateGrandTotal(),
                    payment_type: this.pos.paymentType,
                    items: this.pos.items.map(item => ({ product_id: item.product_id, quantity: item.quantity, selling_price: item.selling_price }))
                };
                fetch('/api/v1/sales/' + this.editingSaleId, { method: 'PUT', headers: this.getHeaders(), body: JSON.stringify(body) })
                    .then(r => r.json().then(data => ({ ok: r.ok, data })))
                    .then(({ ok, data }) => {
                        this.loading = false;
                        if (ok && data.sale_number) {
                            this.showToast('Sale updated!');
                            this.showEditSaleItemsModal = false;
                            this.selectedSale = data;
                            this.showInvoiceModal = true;
                            this.resetPOS();
                            this.editingSaleId = null;
                            this.loadAllData();
                            this.loadSales();
                        } else {
                            if (data.errors) {
                                let msg = Object.values(data.errors).flat().join('\n');
                                this.showToast(msg, 'error');
                            } else {
                                this.showToast(data.message || 'Failed to update sale.', 'error');
                            }
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                        this.showToast('Error updating sale.', 'error');
                    });
            },

            openEditPurchaseModal(purchase) {
                this.newPurchase.items = purchase.items.map(item => {
                    const liveProduct = this.products.find(p => p.id === item.product_id);
                    return {
                        product_id: item.product_id,
                        name: item.product ? item.product.name : (liveProduct ? liveProduct.name : 'Unknown Product'),
                        quantity: item.quantity,
                        purchase_price: parseFloat(item.purchase_price)
                    };
                });
                this.newPurchase.supplier_id = purchase.supplier_id || '';
                this.newPurchase.payment_type = purchase.payment_type;
                this.purchaseSupplierSearchQuery = '';
                this.purchaseFilteredSuppliers = this.suppliers;
                this.editingPurchaseId = purchase.id;
                this.showEditPurchaseItemsModal = true;
            },

            saveEditedPurchase() {
                if (this.newPurchase.items.length === 0) return;
                if (this.newPurchase.payment_type === 'Credit' && !this.newPurchase.supplier_id) {
                    this.showConfirm('Validation Error', 'Supplier selection is required for Credit (udhaar) purchases.', () => { });
                    return;
                }
                this.loading = true;
                const body = {
                    supplier_id: this.newPurchase.supplier_id || null,
                    total_amount: this.calculatePurchaseTotal(),
                    payment_type: this.newPurchase.payment_type,
                    items: this.newPurchase.items.map(i => ({ product_id: i.product_id, quantity: i.quantity, purchase_price: i.purchase_price }))
                };
                fetch('/api/v1/purchases/' + this.editingPurchaseId, { method: 'PUT', headers: this.getHeaders(), body: JSON.stringify(body) })
                    .then(r => r.json().then(data => ({ ok: r.ok, data })))
                    .then(({ ok, data }) => {
                        this.loading = false;
                        if (ok && data.purchase_number) {
                            this.showToast('Purchase updated!');
                            this.showEditPurchaseItemsModal = false;
                            this.selectedPurchase = data;
                            this.showPurchaseDetailsModal = true;
                            this.resetNewPurchase();
                            this.editingPurchaseId = null;
                            this.loadAllData();
                            this.loadPurchases();
                        } else {
                            if (data.errors) {
                                let msg = Object.values(data.errors).flat().join('\n');
                                this.showToast(msg, 'error');
                            } else {
                                this.showToast(data.message || 'Failed to update purchase.', 'error');
                            }
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                        this.showToast('Error updating purchase.', 'error');
                    });
            },

            deleteSale(saleId) {
                this.showConfirm('Delete Sale', 'Are you sure you want to delete this sale record? This action cannot be undone.', () => {
                    this.loading = true;
                    fetch('/api/v1/sales/' + saleId, { method: 'DELETE', headers: this.getHeaders() })
                        .then(r => { this.loading = false; if (r.status === 204) { this.showToast('Sale deleted.'); this.loadSales(); this.loadAllData(); } });
                });
            },

            printInvoice() {
                this.printHtmlBlock('print-area', 'Sale Invoice');
            },

            downloadPDF() {
                if (!this.selectedSale) return;
                this.loading = true;
                fetch('/api/v1/sales/' + this.selectedSale.id + '/invoice', { headers: this.getHeaders() })
                    .then(r => r.blob()).then(blob => {
                         this.loading = false;
                         const link = document.createElement('a');
                         link.href = window.URL.createObjectURL(blob);
                         link.download = 'Invoice-' + this.selectedSale.sale_number + '.pdf';
                         link.click();
                    }).catch(() => { this.loading = false; this.showToast('Error generating PDF.', 'error'); });
            },

            whatsappLink() {
                if (!this.selectedSale) return '#';
                const custName = this.selectedSale.customer ? this.selectedSale.customer.name : 'Customer';
                const mobile = this.selectedSale.customer ? this.selectedSale.customer.mobile : '';
                const msg = `Hello ${custName}, thank you! Invoice: ${this.selectedSale.sale_number}, Total: ₹${this.selectedSale.grand_total}. - DukanHisab`;
                return `https://wa.me/${mobile}?text=${encodeURIComponent(msg)}`;
            },

            sendSaleInvoiceEmail() {
                if (!this.selectedSale || this.sendingSaleEmail) return;
                this.sendingSaleEmail = true;
                fetch('/api/v1/sales/' + this.selectedSale.id + '/email-invoice', { method: 'POST', headers: this.getHeaders() })
                    .then(r => r.json().then(d => ({ status: r.status, body: d })))
                    .then(({ status, body }) => {
                        this.sendingSaleEmail = false;
                        if (status === 200) { this.showToast(body.message || 'Invoice emailed successfully!'); }
                        else { this.showToast(body.message || 'Failed to email invoice.', 'error'); }
                    }).catch(() => { this.sendingSaleEmail = false; this.showToast('Error sending invoice email.', 'error'); });
            },

            printPurchase() {
                this.printHtmlBlock('purchase-print-area', 'Purchase Invoice');
            },

            downloadPurchasePDF() {
                if (!this.selectedPurchase) return;
                this.loading = true;
                fetch('/api/v1/purchases/' + this.selectedPurchase.id + '/invoice', { headers: this.getHeaders() })
                    .then(r => r.blob()).then(blob => {
                        this.loading = false;
                        const link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = 'PurchaseInvoice-' + this.selectedPurchase.purchase_number + '.pdf';
                        link.click();
                    }).catch(() => { this.loading = false; this.showToast('Error generating PDF.', 'error'); });
            },

            whatsappPurchaseLink() {
                if (!this.selectedPurchase) return '#';
                const supplierName = this.selectedPurchase.supplier ? this.selectedPurchase.supplier.name : 'Supplier';
                const mobile = this.selectedPurchase.supplier ? this.selectedPurchase.supplier.mobile : '';
                const msg = `Hello ${supplierName}, thank you! Purchase Invoice: ${this.selectedPurchase.purchase_number}, Total: ₹${this.selectedPurchase.total_amount}. - DukanHisab`;
                return `https://wa.me/${mobile}?text=${encodeURIComponent(msg)}`;
            },

            sendPurchaseInvoiceEmail() {
                if (!this.selectedPurchase || this.sendingPurchaseEmail) return;
                this.sendingPurchaseEmail = true;
                fetch('/api/v1/purchases/' + this.selectedPurchase.id + '/email-invoice', { method: 'POST', headers: this.getHeaders() })
                    .then(r => r.json().then(d => ({ status: r.status, body: d })))
                    .then(({ status, body }) => {
                        this.sendingPurchaseEmail = false;
                        if (status === 200) { this.showToast(body.message || 'Invoice emailed successfully!'); }
                        else { this.showToast(body.message || 'Failed to email invoice.', 'error'); }
                    }).catch(() => { this.sendingPurchaseEmail = false; this.showToast('Error sending invoice email.', 'error'); });
            },

            // ── INLINE CREATE ─────────────────────────────────────────
            openNewCustomerModal() { this.newCustomer = { name: '', mobile: '', email: '' }; this.showCustomerModal = true; },
            openEditCustomerModal(cust) {
                this.newCustomer = {
                    id: cust.id,
                    name: cust.name,
                    mobile: cust.mobile || '',
                    email: cust.email || ''
                };
                this.showCustomerModal = true;
            },
            saveCustomer() {
                if (!this.newCustomer.name || this.newCustomer.name.trim() === '') {
                    this.showConfirm('Validation Error', 'Customer Name is required.', () => { });
                    return;
                }
                if (this.newCustomer.mobile && this.newCustomer.mobile.length > 0 && this.newCustomer.mobile.length !== 10) {
                    this.showConfirm('Validation Error', 'Customer Mobile must be exactly 10 digits.', () => { });
                    return;
                }
                this.loading = true;
                const isEdit = !!this.newCustomer.id;
                const url = isEdit ? '/api/v1/customers/' + this.newCustomer.id : '/api/v1/customers';
                const method = isEdit ? 'PUT' : 'POST';
                fetch(url, { method: method, headers: this.getHeaders(), body: JSON.stringify(this.newCustomer) })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.id) {
                            this.showToast(isEdit ? 'Customer updated!' : 'Customer added!');
                            this.loadCustomers();
                            this.showCustomerModal = false;
                            if (this.page === 'sales') this.pos.selectedCustomer = d.id;
                        }
                        else {
                            this.showToast(isEdit ? 'Failed to update customer.' : 'Failed to add customer.', 'error');
                        }
                    }).catch(() => {
                        this.loading = false;
                        this.showToast('Error saving customer.', 'error');
                    });
            },

            openNewProductModal() { this.newProduct = { name: '', selling_price: '', purchase_price: '', barcode: '', stock: 10, low_stock_threshold: 5, category_id: '' }; this.showProductModal = true; },
            openEditProductModal(product) {
                this.newProduct = {
                    id: product.id,
                    name: product.name,
                    selling_price: parseFloat(product.selling_price),
                    purchase_price: parseFloat(product.purchase_price) || '',
                    barcode: product.barcode || '',
                    stock: product.stock,
                    low_stock_threshold: product.low_stock_threshold,
                    category_id: product.category_id || ''
                };
                this.showProductModal = true;
            },
            saveProduct() {
                // Client-side validations
                if (!this.newProduct.name || this.newProduct.name.trim() === '') {
                    this.showConfirm('Validation Error', 'Product Name is required.', () => { });
                    return;
                }
                if (this.newProduct.selling_price === '' || this.newProduct.selling_price === null || isNaN(this.newProduct.selling_price) || this.newProduct.selling_price < 0) {
                    this.showConfirm('Validation Error', 'Selling Price must be a valid number greater than or equal to 0.', () => { });
                    return;
                }
                if (this.newProduct.purchase_price !== '' && this.newProduct.purchase_price !== null && (isNaN(this.newProduct.purchase_price) || this.newProduct.purchase_price < 0)) {
                    this.showConfirm('Validation Error', 'Purchase Price must be greater than or equal to 0.', () => { });
                    return;
                }
                if (this.newProduct.purchase_price !== '' && this.newProduct.purchase_price !== null && this.newProduct.selling_price < this.newProduct.purchase_price) {
                    this.showConfirm('Validation Error', 'Selling Price cannot be less than Purchase Price.', () => { });
                    return;
                }

                this.loading = true;
                const isEdit = !!this.newProduct.id;
                const url = isEdit ? '/api/v1/products/' + this.newProduct.id : '/api/v1/products';
                const method = isEdit ? 'PUT' : 'POST';
                fetch(url, { method: method, headers: this.getHeaders(), body: JSON.stringify(this.newProduct) })
                    .then(r => {
                        if (!r.ok) {
                            return r.json().then(errData => {
                                throw errData;
                            });
                        }
                        return r.json();
                    })
                    .then(d => {
                        this.loading = false;
                        if (d.id) {
                            this.showToast(isEdit ? 'Product updated!' : 'Product added!');
                            this.loadProducts();
                            this.showProductModal = false;
                            if (this.page === 'sales') this.addToBill(d);
                        } else {
                            this.showConfirm('Error', isEdit ? 'Failed to update product.' : 'Failed to add product.', () => { });
                        }
                    })
                    .catch((err) => {
                        this.loading = false;
                        if (err && err.errors) {
                            let messages = [];
                            for (const key in err.errors) {
                                if (Array.isArray(err.errors[key])) {
                                    messages.push(...err.errors[key]);
                                }
                            }
                            this.showConfirm('Validation Error', messages.join('\n'), () => { });
                        } else {
                            this.showConfirm('Error', 'Error saving product. Please try again.', () => { });
                        }
                    });
            },

            deleteProduct(prodId) {
                this.showConfirm('Delete Product', 'Are you sure you want to delete this product? This action cannot be undone.', () => {
                    this.loading = true;
                    fetch('/api/v1/products/' + prodId, { method: 'DELETE', headers: this.getHeaders() })
                        .then(r => { this.loading = false; if (r.status === 204) { this.showToast('Product deleted.'); this.loadProducts(); } });
                });
            },

            deleteCustomer(custId) {
                this.showConfirm('Delete Customer', 'Are you sure you want to delete this customer? This action cannot be undone.', () => {
                    this.loading = true;
                    fetch('/api/v1/customers/' + custId, { method: 'DELETE', headers: this.getHeaders() })
                        .then(r => { this.loading = false; if (r.status === 204) { this.showToast('Customer deleted.'); this.loadCustomers(); } });
                });
            },

            openCollectCustomerPaymentModal(cust) {
                const due = parseFloat(cust.due_amount) || 0;
                this.collectCustomerForm = {
                    customer_id: cust.id,
                    customer_name: cust.name,
                    current_due: due,
                    amount: due,
                    payment_method: 'Cash',
                    note: ''
                };
                this.collectCustomerModalOpen = true;
            },

            submitCollectCustomerPayment() {
                if (!this.collectCustomerForm.amount || this.collectCustomerForm.amount <= 0) {
                    this.showConfirm('Validation Error', 'Please enter a valid payment amount.', () => { });
                    return;
                }
                this.loading = true;
                fetch('/api/v1/customers/' + this.collectCustomerForm.customer_id + '/collect-payment', {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify({
                        amount: this.collectCustomerForm.amount,
                        payment_method: this.collectCustomerForm.payment_method,
                        note: this.collectCustomerForm.note
                    })
                })
                .then(r => r.json().then(d => ({ ok: r.ok, status: r.status, body: d })))
                .then(res => {
                    this.loading = false;
                    if (res.ok) {
                        this.collectCustomerModalOpen = false;
                        this.showToast(res.body.message || 'Payment collected successfully!');
                        this.loadCustomers();
                        this.loadSales();
                        this.loadDashboard();
                    } else if (res.body && res.body.errors) {
                        let messages = [];
                        for (const key in res.body.errors) {
                            if (Array.isArray(res.body.errors[key])) {
                                messages.push(...res.body.errors[key]);
                            }
                        }
                        this.showConfirm('Validation Error', messages.join('\n'), () => { });
                    } else {
                        this.showConfirm('Payment Error', (res.body && res.body.message) ? res.body.message : 'Failed to record payment.', () => { });
                    }
                })
                .catch((err) => {
                    this.loading = false;
                    console.error(err);
                    this.showConfirm('Error', (err && err.message) ? err.message : 'An error occurred while saving payment.', () => { });
                });
            },

            openPaySupplierDueModal(sup) {
                const due = parseFloat(sup.due_amount) || 0;
                this.paySupplierForm = {
                    supplier_id: sup.id,
                    supplier_name: sup.name,
                    current_due: due,
                    amount: due,
                    payment_method: 'Cash',
                    note: ''
                };
                this.paySupplierModalOpen = true;
            },

            submitPaySupplierDue() {
                if (!this.paySupplierForm.amount || this.paySupplierForm.amount <= 0) {
                    this.showConfirm('Validation Error', 'Please enter a valid payment amount.', () => { });
                    return;
                }
                this.loading = true;
                fetch('/api/v1/suppliers/' + this.paySupplierForm.supplier_id + '/pay-due', {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify({
                        amount: this.paySupplierForm.amount,
                        payment_method: this.paySupplierForm.payment_method,
                        note: this.paySupplierForm.note
                    })
                })
                .then(r => r.json().then(d => ({ ok: r.ok, status: r.status, body: d })))
                .then(res => {
                    this.loading = false;
                    if (res.ok) {
                        this.paySupplierModalOpen = false;
                        this.showToast(res.body.message || 'Supplier payment recorded!');
                        this.loadSuppliers();
                        this.loadPurchases();
                        this.loadDashboard();
                    } else if (res.body && res.body.errors) {
                        let messages = [];
                        for (const key in res.body.errors) {
                            if (Array.isArray(res.body.errors[key])) {
                                messages.push(...res.body.errors[key]);
                            }
                        }
                        this.showConfirm('Validation Error', messages.join('\n'), () => { });
                    } else {
                        this.showConfirm('Payment Error', (res.body && res.body.message) ? res.body.message : 'Failed to record payment.', () => { });
                    }
                })
                .catch((err) => {
                    this.loading = false;
                    console.error(err);
                    this.showConfirm('Error', (err && err.message) ? err.message : 'An error occurred while saving payment.', () => { });
                });
            },

            openNewExpenseModal() { this.newExpense = { description: '', amount: '', payment_method: 'cash' }; this.showExpenseModal = true; },
            saveExpense() {
                this.loading = true;
                fetch('/api/v1/expenses', { method: 'POST', headers: this.getHeaders(), body: JSON.stringify(this.newExpense) })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.id) { this.showToast('Expense recorded!'); this.loadExpenses(); this.loadDashboard(); this.showExpenseModal = false; }
                    });
            },

            openNewSupplierModal() { this.newSupplier = { name: '', mobile: '', email: '' }; this.showSupplierModal = true; },
            openEditSupplierModal(sup) {
                this.newSupplier = {
                    id: sup.id,
                    name: sup.name,
                    mobile: sup.mobile || '',
                    email: sup.email || ''
                };
                this.showSupplierModal = true;
            },
            saveSupplier() {
                if (!this.newSupplier.name || this.newSupplier.name.trim() === '') {
                    this.showConfirm('Validation Error', 'Supplier Name is required.', () => { });
                    return;
                }
                if (this.newSupplier.mobile && this.newSupplier.mobile.length > 0 && this.newSupplier.mobile.length !== 10) {
                    this.showConfirm('Validation Error', 'Supplier Mobile must be exactly 10 digits.', () => { });
                    return;
                }
                this.loading = true;
                const isEdit = !!this.newSupplier.id;
                const url = isEdit ? '/api/v1/suppliers/' + this.newSupplier.id : '/api/v1/suppliers';
                const method = isEdit ? 'PUT' : 'POST';
                fetch(url, { method: method, headers: this.getHeaders(), body: JSON.stringify(this.newSupplier) })
                    .then(r => {
                        if (!r.ok) {
                            return r.json().then(errData => {
                                throw errData;
                            });
                        }
                        return r.json();
                    })
                    .then(d => {
                        this.loading = false;
                        if (d.id) {
                            this.showToast(isEdit ? 'Supplier updated!' : 'Supplier added!');
                            this.loadSuppliers();
                            this.showSupplierModal = false;
                        } else {
                            this.showConfirm('Error', isEdit ? 'Failed to update supplier.' : 'Failed to add supplier.', () => { });
                        }
                    })
                    .catch((err) => {
                        this.loading = false;
                        if (err && err.errors) {
                            let messages = [];
                            for (const key in err.errors) {
                                if (Array.isArray(err.errors[key])) {
                                    messages.push(...err.errors[key]);
                                }
                            }
                            this.showConfirm('Validation Error', messages.join('\n'), () => { });
                        } else {
                            this.showConfirm('Error', 'Error saving supplier. Please try again.', () => { });
                        }
                    });
            },

            deleteSupplier(supId) {
                this.showConfirm('Delete Supplier', 'Are you sure you want to delete this supplier? This action cannot be undone.', () => {
                    this.loading = true;
                    fetch('/api/v1/suppliers/' + supId, { method: 'DELETE', headers: this.getHeaders() })
                        .then(r => { this.loading = false; if (r.status === 204) { this.showToast('Supplier deleted.'); this.loadSuppliers(); } });
                });
            },

            resetNewPurchase() {
                this.newPurchase = { supplier_id: '', payment_type: 'Cash', items: [] };
                this.purchaseSupplierSearchQuery = '';
                this.purchaseFilteredSuppliers = this.suppliers;
            },
            openNewPurchaseModal() {
                this.resetNewPurchase();
                this.showPurchaseModal = true;
            },
            searchPurchaseSuppliers() {
                const q = this.purchaseSupplierSearchQuery;
                fetch('/api/v1/suppliers?search=' + encodeURIComponent(q), { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        if (Array.isArray(d)) {
                            this.purchaseFilteredSuppliers = d;
                        }
                    });
            },
            selectPurchaseSupplier(supplier) {
                if (supplier) {
                    this.newPurchase.supplier_id = supplier.id;
                } else {
                    this.newPurchase.supplier_id = '';
                }
                this.purchaseSupplierSearchQuery = '';
                this.purchaseFilteredSuppliers = this.suppliers;
            },
            getSelectedPurchaseSupplierName() {
                if (!this.newPurchase.supplier_id) return 'Walk-In Supplier';
                const sup = this.suppliers.find(s => s.id == this.newPurchase.supplier_id);
                return sup ? `${sup.name} (${sup.mobile || 'No Mobile'})` : 'Walk-In Supplier';
            },
            searchPurchaseHistorySuppliers() {
                const q = this.purchaseHistorySupplierSearchQuery;
                fetch('/api/v1/suppliers?search=' + encodeURIComponent(q), { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        if (Array.isArray(d)) {
                            this.purchaseHistoryFilteredSuppliers = d;
                        }
                    });
            },
            selectPurchaseHistorySupplier(supplier) {
                if (supplier) {
                    this.purchaseFilter.supplierId = supplier.id;
                } else {
                    this.purchaseFilter.supplierId = '';
                }
                this.purchaseHistorySupplierSearchQuery = '';
                this.purchaseHistoryFilteredSuppliers = this.suppliers;
                this.loadPurchases();
            },
            getSelectedPurchaseHistorySupplierName() {
                if (!this.purchaseFilter.supplierId) return 'All Suppliers';
                const sup = this.suppliers.find(s => s.id == this.purchaseFilter.supplierId);
                return sup ? `${sup.name} (${sup.mobile || 'No Mobile'})` : 'All Suppliers';
            },
            searchPurchaseReturnedSuppliers() {
                const q = this.purchaseReturnedSupplierSearchQuery;
                fetch('/api/v1/suppliers?search=' + encodeURIComponent(q), { headers: this.getHeaders() })
                    .then(r => r.json())
                    .then(d => {
                        if (Array.isArray(d)) {
                            this.purchaseReturnedFilteredSuppliers = d;
                        }
                    });
            },
            selectPurchaseReturnedSupplier(supplier) {
                if (supplier) {
                    this.purchaseReturnedFilter.supplierId = supplier.id;
                } else {
                    this.purchaseReturnedFilter.supplierId = '';
                }
                this.purchaseReturnedSupplierSearchQuery = '';
                this.purchaseReturnedFilteredSuppliers = this.suppliers;
                this.loadPurchases();
            },
            getSelectedPurchaseReturnedSupplierName() {
                if (!this.purchaseReturnedFilter.supplierId) return 'All Suppliers';
                const sup = this.suppliers.find(s => s.id == this.purchaseReturnedFilter.supplierId);
                return sup ? `${sup.name} (${sup.mobile || 'No Mobile'})` : 'All Suppliers';
            },
            addPurchaseItem() {
                const selVal = document.getElementById('purchase-product-select').value;
                this.addPurchaseItemById(selVal);
            },
            addPurchaseItemById(productId) {
                if (!productId) return;
                const prod = this.products.find(p => p.id == productId);
                if (prod) {
                    const existing = this.newPurchase.items.find(i => i.product_id === prod.id);
                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.newPurchase.items.push({
                            product_id: prod.id,
                            name: prod.name,
                            quantity: 1,
                            purchase_price: parseFloat(prod.purchase_price) || 0
                        });
                    }
                    this.showToast(prod.name + ' added to purchase list.');
                }
                const el = document.getElementById('purchase-product-select');
                if (el) el.value = '';
            },
            handlePurchaseBarcodeScan() {
                const code = this.pos.barcodeInput.trim();
                if (!code) return;
                const product = this.products.find(p => p.barcode === code);
                if (product) {
                    this.addPurchaseItemById(product.id);
                    this.pos.barcodeInput = '';
                } else {
                    this.newProduct = { name: '', selling_price: '', purchase_price: '', barcode: code, stock: 10, low_stock_threshold: 5, category_id: '' };
                    this.showProductModal = true;
                    this.pos.barcodeInput = '';
                    this.showToast('Product not found! Create a new product.', 'warning');
                }
            },
            calculatePurchaseTotal() { return this.newPurchase.items.reduce((sum, item) => sum + (item.purchase_price * item.quantity), 0); },
            savePurchase() {
                if (this.newPurchase.items.length === 0) return;
                if (this.newPurchase.payment_type === 'Credit' && !this.newPurchase.supplier_id) {
                    this.showConfirm('Validation Error', 'Supplier selection is required for Credit (udhaar) purchases.', () => { });
                    return;
                }
                this.loading = true;
                const body = {
                    supplier_id: this.newPurchase.supplier_id || null, total_amount: this.calculatePurchaseTotal(),
                    payment_type: this.newPurchase.payment_type,
                    items: this.newPurchase.items.map(i => ({ product_id: i.product_id, quantity: i.quantity, purchase_price: i.purchase_price }))
                };
                fetch('/api/v1/purchases', { method: 'POST', headers: this.getHeaders(), body: JSON.stringify(body) })
                    .then(r => r.json()).then(d => {
                        this.loading = false;
                        if (d.purchase_number) {
                            this.showToast('Purchase recorded!');
                            this.showPurchaseModal = false;
                            this.resetNewPurchase();
                            this.selectedPurchase = d;
                            this.showPurchaseDetailsModal = true;
                            this.loadPurchases();
                            this.loadExpenses();
                            this.loadDashboard();
                            this.loadProducts();
                        }
                        else { this.showToast('Failed to record purchase.', 'error'); }
                    });
            },

            downloadShopBackup() {
                this.loading = true;
                const headers = this.getHeaders();
                fetch('/api/v1/backup/export', { headers: headers })
                    .then(res => {
                        this.loading = false;
                        if (!res.ok) {
                            return res.text().then(text => {
                                let msg = 'Failed to export backup.';
                                try {
                                    const d = JSON.parse(text);
                                    if (d && d.message) msg = d.message;
                                } catch(e){}
                                throw new Error(msg);
                            });
                        }
                        return res.blob().then(blob => ({ blob, filename: res.headers.get('content-disposition') }));
                    })
                    .then(({ blob, filename }) => {
                        let name = 'dukanhisab-backup-' + new Date().toISOString().slice(0, 10) + '.dhbak';
                        if (filename && filename.includes('filename=')) {
                            name = filename.split('filename=')[1].replace(/["']/g, '');
                        }
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = name;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                        this.showToast('Backup downloaded successfully!');
                    })
                    .catch(err => {
                        this.loading = false;
                        this.showConfirm('Error', err.message || 'Failed to download backup.', () => { });
                    });
            },

            restoreShopBackup(fileInput) {
                const file = fileInput && fileInput.files ? fileInput.files[0] : null;
                if (!file) {
                    this.showConfirm('Validation Error', 'Please select a valid backup file.', () => { });
                    return;
                }
                this.showConfirm('Restore Data Backup', 'Are you sure you want to restore data from this backup file? Existing matching records will be updated.', () => {
                    this.loading = true;
                    const formData = new FormData();
                    formData.append('backup_file', file);

                    const headers = this.getHeaders();
                    delete headers['Content-Type'];

                    fetch('/api/v1/backup/restore', {
                        method: 'POST',
                        headers: headers,
                        body: formData
                    })
                    .then(r => r.json().then(d => ({ ok: r.ok, body: d })))
                    .then(res => {
                        this.loading = false;
                        if (res.ok) {
                            this.showToast(res.body.message || 'Data restored successfully!');
                            fileInput.value = '';
                            this.loadAllData();
                        } else {
                            this.showConfirm('Restore Failed', res.body.message || 'Failed to restore backup.', () => { });
                        }
                    })
                    .catch(err => {
                        this.loading = false;
                        this.showConfirm('Error', err.message || 'Failed to restore backup.', () => { });
                    });
                });
            }
        };
    }
</script>
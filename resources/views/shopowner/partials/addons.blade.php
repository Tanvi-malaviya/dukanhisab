<div x-show="page === 'addons'" class="space-y-3" x-cloak>
    <!-- Add-Ons Status Header Card -->
    <div
        class="bg-gradient-to-r from-teal-700 to-teal-900 p-6 rounded-2xl text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-10 transform translate-x-6 translate-y-6">
            <svg class="w-48 h-48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4v16m8-8H4"></path>
            </svg>
        </div>
        <div class="relative z-10 space-y-2">
            <h3 class="text-2xl font-black" x-text="t('add_ons') || 'Add Ons'">Add Ons</h3>
            <p class="text-sm opacity-90 max-w-2xl"
                x-text="t('add_ons_desc') || 'Extend your account beyond your subscription plan — buy extra shop slots or unlock your public shop website. Every add-on renews automatically each year.'">
                Extend your account beyond your subscription plan &mdash; buy extra shop slots or unlock your public
                shop website. Every add-on renews automatically each year.
            </p>
        </div>
    </div>

    <!-- 4 Standalone KPI Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
        <!-- Card 1: Extra Purchased -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500" x-text="t('extra_purchased') || 'Extra Purchased'">Extra Purchased</span>
                <p class="text-2xl font-black text-slate-800 dark:text-white mt-1" x-text="(addOnStats && addOnStats.purchased) ? addOnStats.purchased : 0">0</p>
                <span class="text-[11px] text-teal-600 dark:text-teal-400 font-semibold" x-text="t('extra_shop_slots') || 'Extra shop slot(s)'">Extra shop slot(s)</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
        </div>

        <!-- Card 2: Shops In Use / Max Limit -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500" x-text="t('shops_in_use') || 'Shops In Use'">Shops In Use</span>
                <p class="text-2xl font-black text-slate-800 dark:text-white mt-1">
                    <span x-text="(user && user.shops) ? user.shops.length : 1">1</span>
                    <span class="text-slate-400 text-lg font-normal"> / </span>
                    <span x-text="maxShops">1</span>
                </p>
                <span class="text-[11px] text-slate-400 font-semibold" x-text="((maxShops - ((user && user.shops) ? user.shops.length : 1)) > 0 ? (maxShops - ((user && user.shops) ? user.shops.length : 1)) + ' ' + (t('slots_free') || 'slot(s) free') : (t('limit_reached') || 'Limit reached'))">1 / 1 limit</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
        </div>

        <!-- Card 3: Expired Shops -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500" x-text="t('expired_shops') || 'Expired Shops'">Expired Shops</span>
                <p class="text-2xl font-black mt-1" :class="(addOnStats && addOnStats.expired > 0) ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-white'" x-text="(addOnStats && addOnStats.expired) ? addOnStats.expired : 0">0</p>
                <span class="text-[11px] font-semibold" :class="(addOnStats && addOnStats.expired > 0) ? 'text-amber-500' : 'text-slate-400'" x-text="(addOnStats && addOnStats.expired > 0) ? (t('needs_renewal') || 'Needs renewal') : (t('all_active') || 'All active')">All active</span>
            </div>
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" :class="(addOnStats && addOnStats.expired > 0) ? 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' : 'bg-slate-100 dark:bg-gray-700 text-slate-400'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Card 4: Shop Website -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500" x-text="t('shop_website') || 'Shop Website'">Shop Website</span>
                <p class="text-2xl font-black mt-1" :class="hasWebsiteAddon ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400'" x-text="hasWebsiteAddon ? (t('active') || 'Active') : (t('not_active') || 'Not Active')">Not Active</p>
                <span class="text-[11px] font-semibold" :class="hasWebsiteAddon ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'" x-text="hasWebsiteAddon ? (t('online_store_live') || 'Online store live') : (t('publish_store_online') || 'Publish store online')">Publish store online</span>
            </div>
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" :class="hasWebsiteAddon ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-gray-700 text-slate-400'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Add-On Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <!-- Card 1: Extra Shop -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-slate-100 dark:bg-gray-700 overflow-hidden flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-800 dark:text-white"
                            x-text="t('extra_shop') || ((addOns.find(a => a.type === 'shop') || {}).title || 'Extra Shop')">Extra Shop</h4>
                        <p class="text-xs text-slate-400 mt-1"
                            x-text="t('extra_shop_desc') || ((addOns.find(a => a.type === 'shop') || {}).description || 'Add one more shop to your account. Each purchase grants 1 additional shop for a full year.')">
                            Add one more shop to your account. Each purchase grants 1 additional shop for a full year.
                        </p>
                    </div>
                </div>

                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-slate-800 dark:text-white"
                        x-text="'₹' + parseFloat((addOns.find(a => a.type === 'shop') || {}).price || 200).toLocaleString('en-IN', { maximumFractionDigits: 0 })">₹200</span>
                    <span class="text-xs text-slate-400" x-text="t('year_auto_renewal') || '/ Year (Auto-Renewal)'">/ Year (Auto-Renewal)</span>
                </div>

                <!-- Shop add-on quantity stepper -->
                <div class="flex items-center gap-3 pt-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase" x-text="t('extra_shops') || 'Extra Shops'">Extra Shops</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click.stop="addOnShopQty = Math.max(1, addOnShopQty - 1)"
                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-gray-600 cursor-pointer">&minus;</button>
                        <span class="w-6 text-center text-sm font-bold text-slate-800 dark:text-white"
                            x-text="addOnShopQty">1</span>
                        <button type="button" @click.stop="addOnShopQty = Math.min(20, addOnShopQty + 1)"
                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-gray-600 cursor-pointer">+</button>
                    </div>
                    <span class="text-xs text-slate-400"
                        x-text="'₹' + (parseFloat((addOns.find(a => a.type === 'shop') || {}).price || 200) * addOnShopQty).toLocaleString('en-IN') + ' ' + (t('total') || 'total')">₹200 total</span>
                </div>
            </div>

            <div class="pt-6">
                <button type="button"
                    id="btn-buy-extra-shop"
                    @click.stop="purchaseAddOn('shop', addOnShopQty)"
                    :disabled="addOnPurchasingSlug === 'shop'"
                    class="w-full py-2.5 rounded-xl text-xs font-bold transition-all bg-teal-600 hover:bg-teal-700 text-white disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                    <svg x-show="addOnPurchasingSlug === 'shop'" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span x-text="addOnPurchasingSlug === 'shop' ? (t('processing') || 'Processing...') : (t('buy_extra_shop') || 'Buy Extra Shop')">Buy Extra Shop</span>
                </button>
            </div>
        </div>

        <!-- Card 2: Shop Website -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-slate-100 dark:bg-gray-700 overflow-hidden flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-800 dark:text-white"
                            x-text="t('shop_website') || ((addOns.find(a => a.type === 'website') || {}).title || 'Shop Website')">Shop Website</h4>
                        <p class="text-xs text-slate-400 mt-1"
                            x-text="t('shop_website_desc') || ((addOns.find(a => a.type === 'website') || {}).description || 'Publish a public website to showcase your products online, with a shareable link for your customers.')">
                            Publish a public website to showcase your products online, with a shareable link for your customers.
                        </p>
                    </div>
                </div>

                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-slate-800 dark:text-white"
                        x-text="'₹' + parseFloat((addOns.find(a => a.type === 'website') || {}).price || 200).toLocaleString('en-IN', { maximumFractionDigits: 0 })">₹200</span>
                    <span class="text-xs text-slate-400" x-text="t('year_auto_renewal') || '/ Year (Auto-Renewal)'">/ Year (Auto-Renewal)</span>
                </div>
            </div>

            <div class="pt-6">
                <!-- If already active -->
                <div x-show="hasWebsiteAddon">
                    <button type="button" disabled
                        x-text="t('active_auto_renews_yearly') || 'Active — Auto-Renews Yearly'"
                        class="w-full py-2.5 rounded-xl text-xs font-bold border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 cursor-not-allowed">
                        Active &mdash; Auto-Renews Yearly
                    </button>
                </div>

                <!-- If not active, purchasable -->
                <div x-show="!hasWebsiteAddon">
                    <button type="button"
                        id="btn-activate-website"
                        @click.stop="purchaseAddOn('website', 1)"
                        :disabled="addOnPurchasingSlug === 'website'"
                        class="w-full py-2.5 rounded-xl text-xs font-bold transition-all bg-teal-600 hover:bg-teal-700 text-white disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                        <svg x-show="addOnPurchasingSlug === 'website'" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span x-text="addOnPurchasingSlug === 'website' ? (t('activating') || 'Activating...') : (t('activate_website') || 'Activate Website')">Activate Website</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add-On Purchases Ledger -->
    <div x-show="userAddOns.length > 0" class="space-y-3">
        <div class="flex items-center justify-between">
            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider" x-text="t('addon_purchases_ledger') || 'Add-On Purchases Ledger'">Add-On Purchases Ledger</h4>
            <span class="text-xs text-slate-400" x-text="userAddOns.length + ' ' + (t('purchase_records') || 'purchase record(s)')"></span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <template x-for="(ua, idx) in userAddOns" :key="ua.id">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 gap-3"
                    :class="idx > 0 ? 'border-t border-slate-100 dark:border-gray-700' : ''">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-bold text-slate-800 dark:text-white"
                                x-text="(ua.add_on ? (t(ua.add_on.slug === 'extra-shop' || ua.add_on.type === 'shop' ? 'extra_shop' : (ua.add_on.slug === 'website-addon' || ua.add_on.type === 'website' ? 'shop_website' : 'add_ons')) || ua.add_on.title) : (t('add_ons') || 'Add-On')) + (ua.quantity > 1 ? ' × ' + ua.quantity : '')">
                            </p>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                :class="ua.status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'"
                                x-text="ua.status === 'active' ? (t('active') || 'Active') : (t('expired') || 'Expired')">
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400">
                            <!-- Linked Shop Name -->
                            <span x-show="ua.shop && ua.shop.name" class="inline-flex items-center gap-1 font-medium text-teal-600 dark:text-teal-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                <span x-text="(t('shop') || 'Shop') + ': ' + (ua.shop ? ua.shop.name : '')"></span>
                            </span>
                            <span x-text="(t('started') || 'Started') + ': ' + (ua.starts_at ? ua.starts_at.substring(0, 10) : 'N/A')"></span>
                            <span x-text="(ua.status === 'active' ? (t('renews') || 'Renews') : (t('expired') || 'Expired')) + ': ' + (ua.ends_at ? ua.ends_at.substring(0, 10) : 'N/A')"></span>
                            <span x-show="ua.status === 'active'" x-text="ua.auto_renew ? (t('auto_renew_on') || '(Auto-Renew On)') : (t('auto_renew_off') || '(Auto-Renew Off)')"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-center">
                        <button x-show="ua.status === 'active' && ua.auto_renew" type="button"
                            @click="showConfirm(t('disable_auto_renewal_title') || 'Disable Auto-Renewal?', t('disable_auto_renewal_confirm') || 'This add-on will stay active until its current period ends, then it will not renew.', () => cancelAddOn(ua.id))"
                            x-text="t('cancel_auto_renew') || 'Cancel Auto-Renew'"
                            class="px-3 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 rounded-lg transition-colors cursor-pointer">
                            Cancel Auto-Renew
                        </button>
                        <button x-show="ua.status === 'expired' && ua.add_on && ua.add_on.type === 'shop'" type="button"
                            @click="purchaseAddOn('shop', ua.quantity || 1)"
                            x-text="t('renew_shop') || 'Renew Shop'"
                            class="px-3 py-1.5 text-xs font-semibold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/20 hover:bg-teal-100 dark:hover:bg-teal-900/40 rounded-lg transition-colors cursor-pointer">
                            Renew Shop
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
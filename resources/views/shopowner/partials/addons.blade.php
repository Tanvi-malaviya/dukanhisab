<div x-show="page === 'addons'" class="space-y-6" x-cloak>
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
            <p class="text-sm opacity-90 max-w-xl">
                Extend your account beyond your subscription plan &mdash; buy extra shop slots or unlock your public
                shop website. Every add-on renews automatically each year.
            </p>
            <div class="flex flex-wrap items-center gap-4 pt-2 text-sm">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    <span
                        x-text="Math.min(user && user.shops ? user.shops.length : 0, maxShops) + ' / ' + maxShops + ' Shops Used' + (lockedShopIds.length ? ' (' + lockedShopIds.length + ' locked)' : '')"></span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18 15 15 0 010-18z"></path>
                    </svg>
                    <span x-text="'Website: ' + (hasWebsiteAddon ? 'Active' : 'Not Active')"></span>
                </span>
            </div>
        </div>
    </div>

    <!-- Loading state -->
    <div x-show="addOnLoading && addOns.length === 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <template x-for="i in 2" :key="i">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 animate-pulse">
                <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-24 mb-3"></div>
                <div class="h-3 bg-slate-100 dark:bg-slate-600 rounded w-40 mb-4"></div>
                <div class="h-8 bg-slate-200 dark:bg-slate-700 rounded w-20 mb-6"></div>
                <div class="h-10 bg-slate-200 dark:bg-slate-700 rounded-xl"></div>
            </div>
        </template>
    </div>

    <!-- Add-On Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            x-text="(addOns.find(a => a.type === 'shop') || {}).title || 'Extra Shop'">Extra Shop</h4>
                        <p class="text-xs text-slate-400 mt-1"
                            x-text="(addOns.find(a => a.type === 'shop') || {}).description || 'Add one more shop to your account. Each purchase grants 1 additional shop for a full year.'">
                            Add one more shop to your account. Each purchase grants 1 additional shop for a full year.
                        </p>
                    </div>
                </div>

                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-slate-800 dark:text-white"
                        x-text="'₹' + parseFloat((addOns.find(a => a.type === 'shop') || {}).price || 200).toLocaleString('en-IN', { maximumFractionDigits: 0 })">₹200</span>
                    <span class="text-xs text-slate-400">/ Year (Auto-Renewal)</span>
                </div>

                <!-- Shop add-on quantity stepper -->
                <div class="flex items-center gap-3 pt-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Extra Shops</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click.stop="addOnShopQty = Math.max(1, addOnShopQty - 1)"
                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-gray-600 cursor-pointer">&minus;</button>
                        <span class="w-6 text-center text-sm font-bold text-slate-800 dark:text-white"
                            x-text="addOnShopQty">1</span>
                        <button type="button" @click.stop="addOnShopQty = Math.min(20, addOnShopQty + 1)"
                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-gray-600 cursor-pointer">+</button>
                    </div>
                    <span class="text-xs text-slate-400"
                        x-text="'₹' + (parseFloat((addOns.find(a => a.type === 'shop') || {}).price || 200) * addOnShopQty).toLocaleString('en-IN') + ' total'">₹200 total</span>
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
                    <span x-text="addOnPurchasingSlug === 'shop' ? 'Processing...' : 'Buy Extra Shop'">Buy Extra Shop</span>
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
                            x-text="(addOns.find(a => a.type === 'website') || {}).title || 'Shop Website'">Shop Website</h4>
                        <p class="text-xs text-slate-400 mt-1"
                            x-text="(addOns.find(a => a.type === 'website') || {}).description || 'Publish a public website to showcase your products online, with a shareable link for your customers.'">
                            Publish a public website to showcase your products online, with a shareable link for your customers.
                        </p>
                    </div>
                </div>

                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-slate-800 dark:text-white"
                        x-text="'₹' + parseFloat((addOns.find(a => a.type === 'website') || {}).price || 200).toLocaleString('en-IN', { maximumFractionDigits: 0 })">₹200</span>
                    <span class="text-xs text-slate-400">/ Year (Auto-Renewal)</span>
                </div>
            </div>

            <div class="pt-6">
                <!-- If already active -->
                <div x-show="hasWebsiteAddon">
                    <button type="button" disabled
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
                        <span x-text="addOnPurchasingSlug === 'website' ? 'Activating...' : 'Activate Website'">Activate Website</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty state -->
    <div x-show="!addOnLoading && addOns.length === 0" class="text-center py-12 text-slate-400 text-sm">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
            </path>
        </svg>
        <p>Could not load add-ons. <button @click="loadAddOns()" class="text-teal-600 underline hover:no-underline">Try
                again</button></p>
    </div>

    <!-- Owned Add-Ons -->
    <div x-show="userAddOns.length > 0" class="space-y-3">
        <h4 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Your Active Add-Ons
        </h4>
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <template x-for="(ua, idx) in userAddOns" :key="ua.id">
                <div class="flex items-center justify-between px-5 py-4"
                    :class="idx > 0 ? 'border-t border-slate-100 dark:border-gray-700' : ''">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-white"
                            x-text="(ua.add_on ? ua.add_on.title : '') + (ua.quantity > 1 ? ' × ' + ua.quantity : '')">
                        </p>
                        <p class="text-xs text-slate-400"
                            x-text="'Renews on ' + (ua.ends_at ? ua.ends_at.substring(0, 10) : 'N/A') + (ua.auto_renew ? ' (Auto-Renew On)' : ' (Auto-Renew Off)')">
                        </p>
                    </div>
                    <button x-show="ua.auto_renew" type="button"
                        @click="showConfirm('Disable Auto-Renewal?', 'This add-on will stay active until its current period ends, then it will not renew.', () => cancelAddOn(ua.id))"
                        class="text-xs text-red-600 font-medium hover:underline cursor-pointer">
                        Cancel Auto-Renew
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>
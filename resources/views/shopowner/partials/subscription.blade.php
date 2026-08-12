<div x-show="page === 'subscription'" class="space-y-6" x-cloak>
    <!-- Subscription Status Header Card -->
    <div class="bg-gradient-to-r from-teal-700 to-teal-900 p-6 rounded-2xl text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-10 transform translate-x-6 translate-y-6">
            <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
        </div>
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider backdrop-blur-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    <span x-text="user && user.active_plan ? (t(user.active_plan.slug + '_plan') || user.active_plan.name) : t('free_plan')"></span>
                </div>
                <h3 class="text-2xl font-black">
                    <span x-show="user && user.active_plan && user.active_plan.slug !== 'free'" x-text="t('premium_features_unlocked')">Premium Features Unlocked</span>
                    <span x-show="!user || !user.active_plan || user.active_plan.slug === 'free'" x-text="t('go_premium_heading')">Go Premium to Unlock Pro Features</span>
                </h3>
                <p class="text-sm opacity-90 max-w-xl" x-text="t('go_premium_desc')">
                    Manage multiple shops and login devices, remove invoice branding, WhatsApp/Email invoices, and download cloud backups securely.
                </p>
            </div>
            
            <div x-show="user && user.active_plan && user.active_plan.slug !== 'free'" class="shrink-0">
                <button type="button" @click="showConfirm(t('cancel_subscription_confirm_title'), t('cancel_subscription_confirm_desc'), () => cancelSubscription())"
                    class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl border border-red-600 transition-all text-xs flex items-center gap-1.5 shadow-sm"
                    x-text="t('cancel_active_plan')">
                    Cancel Active Plan
                </button>
            </div>
        </div>
    </div>

    <!-- Available Plans Grid -->
    <div>
        <h4 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4" x-text="t('choose_subscription_plan')">Choose a Subscription Plan</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Free Plan Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between transition-all"
                :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'ring-2 ring-primary border-transparent' : ''">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-bold text-slate-800 dark:text-white" x-text="t('free_plan')">Free Plan</span>
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="px-2 py-0.5 text-[10px] font-bold text-primary bg-primary/10 rounded-full" x-text="t('current_plan')">Current Plan</span>
                    </div>
                    <p class="text-xs text-slate-400" x-text="t('free_plan_desc')">Core accounting features for single-shop owners.</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-slate-800 dark:text-white">₹0</span>
                        <span class="text-xs text-slate-400">/ <span x-text="t('forever')">Forever</span></span>
                    </div>
                    <ul class="space-y-2.5 pt-4 text-xs text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('plan_feature_1_shop_1_device')">1 Shop & 1 Login Device</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('plan_feature_core_ledger_inventory')">Core Ledger & Inventory</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('plan_feature_unlimited_sales_purchases')">Unlimited Sales & Purchases</span>
                        </li>
                        <li class="text-slate-400 line-through flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            <span x-text="t('cloud_backup_restore')">Cloud Backup & Restore</span>
                        </li>
                        <li class="text-slate-400 line-through flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            <span x-text="t('plan_feature_remove_branding')">Remove DukanHisab Branding</span>
                        </li>
                    </ul>
                </div>
                <div class="pt-6">
                    <button type="button" @click="upgradeSubscription('free')" :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                        class="w-full py-2.5 rounded-xl border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700/50 text-xs font-bold transition-all disabled:opacity-50 disabled:pointer-events-none text-slate-800 dark:text-white">
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" x-text="t('active_free_plan')">Active Free Plan</span>
                        <span x-show="!user || !user.active_plan || user.active_plan.slug !== 'free'" x-text="t('free_plan')">Free Plan</span>
                    </button>
                </div>
            </div>

            <!-- Premium Plan Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between transition-all"
                :class="user && user.active_plan && user.active_plan.slug === 'premium' ? 'ring-2 ring-primary border-transparent' : ''">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span class="text-base font-bold text-slate-800 dark:text-white" x-text="t('premium_plan')">Premium Plan</span>
                        </div>
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'premium'" class="px-2 py-0.5 text-[10px] font-bold text-primary bg-primary/10 rounded-full" x-text="t('current_plan')">Current Plan</span>
                    </div>
                    <p class="text-xs text-slate-400" x-text="t('premium_plan_desc')">Ad-free professional experience and multi-shop.</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-slate-800 dark:text-white">₹365</span>
                        <span class="text-xs text-slate-400">/ <span x-text="t('year')">Year</span></span>
                    </div>
                    <ul class="space-y-2.5 pt-4 text-xs text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('plan_feature_max_2_shops_2_devices')">Max 2 Shops & 2 Devices</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('cloud_backup_restore')">Cloud Backup & Restore</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('ad_free_experience')">Ad-Free Experience</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('plan_feature_pdf_print_whatsapp')">PDF, Print & WhatsApp Invoice</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('plan_feature_excel_export_branding')">Excel Export & Remove Branding</span>
                        </li>
                    </ul>
                </div>
                <div class="pt-6">
                    <button type="button" @click="upgradeSubscription('premium')" :disabled="user && user.active_plan && user.active_plan.slug === 'premium'"
                        class="w-full py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition-all disabled:opacity-50 disabled:pointer-events-none">
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'premium'" x-text="t('active_premium_plan')">Active Premium Plan</span>
                        <span x-show="!user || !user.active_plan || user.active_plan.slug !== 'premium'" x-text="t('upgrade_to_premium')">Upgrade to Premium</span>
                    </button>
                </div>
            </div>

            <!-- Business Plan Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between transition-all"
                :class="user && user.active_plan && user.active_plan.slug === 'business' ? 'ring-2 ring-primary border-transparent' : ''">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span class="text-base font-bold text-slate-800 dark:text-white" x-text="t('business_plan_lifetime')">Business (Lifetime)</span>
                        </div>
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'business'" class="px-2 py-0.5 text-[10px] font-bold text-primary bg-primary/10 rounded-full" x-text="t('current_plan')">Current Plan</span>
                    </div>
                    <p class="text-xs text-slate-400" x-text="t('business_plan_desc')">Complete control with lifetime updates & max capacity.</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-slate-800 dark:text-white">₹999</span>
                        <span class="text-xs text-slate-400">/ <span x-text="t('one_time')">One-Time</span></span>
                    </div>
                    <ul class="space-y-2.5 pt-4 text-xs text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('plan_feature_max_5_shops_5_devices')">Max 5 Shops & 5 Devices</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('cloud_backup_restore')">Cloud Backup & Restore</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('plan_feature_excel_no_ads')">Excel Export & No Ads</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('plan_feature_website_themes_seo')">Premium Website Themes & SEO</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="t('plan_feature_founder_badge_support')">Founder Badge & Priority Support</span>
                        </li>
                    </ul>
                </div>
                <div class="pt-6">
                    <button type="button" @click="upgradeSubscription('business')" :disabled="user && user.active_plan && user.active_plan.slug === 'business'"
                        class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-700 dark:hover:bg-slate-600">
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'business'" x-text="t('active_business_plan')">Active Business Plan</span>
                        <span x-show="!user || !user.active_plan || user.active_plan.slug !== 'business'" x-text="t('buy_lifetime')">Buy Lifetime</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div x-show="page === 'subscription'" class="space-y-6" x-cloak>
    <!-- Lifetime owner view: nothing to upgrade, downgrade or cancel -->
    <template x-if="isLifetimePlan()">
        <div class="space-y-6">
            <!-- Lifetime Banner Card -->
            <div class="subscription-banner relative overflow-hidden rounded-3xl p-6 md:p-8 text-white shadow-xl border border-teal-600/40"
                style="background: linear-gradient(135deg, #0F766E 0%, #115E59 45%, #042F2E 100%) !important;">

                <!-- Subtle ambient background pattern -->
                <div class="absolute -right-8 -bottom-8 opacity-15 pointer-events-none transform translate-x-4 translate-y-4">
                    <svg class="w-64 h-64 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                    <div class="space-y-3.5 max-w-2xl">
                        <!-- Lifetime Badge with Solid Gold Theme -->
                        <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider shadow-sm"
                            style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%) !important; color: #92400E !important; border: 1px solid #F59E0B !important;">
                            <svg class="w-4 h-4" style="color: #D97706 !important;" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span style="color: #92400E !important;" x-text="t('lifetime_member') || 'Lifetime Member'">Lifetime Member</span>
                        </div>

                        <!-- Title with guaranteed pure white color -->
                        <h3 class="text-2xl sm:text-3xl font-black tracking-tight"
                            style="color: #FFFFFF !important;"
                            x-text="(t('you_own_plan') || 'You own') + ' ' + (user && user.active_plan ? user.active_plan.name : '')"></h3>

                        <!-- Description with high-contrast soft white -->
                        <p class="text-sm leading-relaxed"
                            style="color: rgba(240, 253, 250, 0.95) !important;"
                            x-text="t('lifetime_plan_desc') || 'One-time purchase. No renewals, no expiry. Every feature of your plan stays unlocked for as long as you use DukanHisab.'"></p>

                        <!-- Feature Pills with clear white text and glass background -->
                        <div class="flex flex-wrap gap-2.5 pt-1">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm"
                                style="background: rgba(255, 255, 255, 0.16) !important; color: #FFFFFF !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(8px);">
                                <svg class="w-3.5 h-3.5" style="color: #6EE7B7 !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                <span style="color: #FFFFFF !important;" x-text="t('lifetime_no_renewal') || 'No renewal charges'">No renewal charges</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm"
                                style="background: rgba(255, 255, 255, 0.16) !important; color: #FFFFFF !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(8px);">
                                <svg class="w-3.5 h-3.5" style="color: #6EE7B7 !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                <span style="color: #FFFFFF !important;" x-text="t('lifetime_free_updates') || 'Free updates'">Free updates</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm"
                                style="background: rgba(255, 255, 255, 0.16) !important; color: #FFFFFF !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(8px);">
                                <svg class="w-3.5 h-3.5" style="color: #6EE7B7 !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                <span style="color: #FFFFFF !important;" x-text="t('lifetime_never_expires') || 'Never expires'">Never expires</span>
                            </span>
                        </div>
                    </div>

                    <!-- Right side: Lifetime Status Highlight Card -->
                    <div class="shrink-0 p-4 sm:p-5 rounded-2xl border"
                        style="background: rgba(255, 255, 255, 0.12) !important; border-color: rgba(255, 255, 255, 0.25) !important; backdrop-filter: blur(12px);">
                        <div class="space-y-3 min-w-[210px]">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs uppercase tracking-wider font-semibold" style="color: rgba(255, 255, 255, 0.85) !important;">Plan Status</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-black"
                                    style="background: #10B981 !important; color: #FFFFFF !important;">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                    Active
                                </span>
                            </div>
                            <div class="pt-2 border-t" style="border-color: rgba(255, 255, 255, 0.18) !important;">
                                <div class="text-[11px]" style="color: rgba(255, 255, 255, 0.75) !important;">Plan Validity</div>
                                <div class="text-base font-black" style="color: #FFFFFF !important;">Lifetime Access</div>
                            </div>
                            <div class="pt-1">
                                <div class="text-[11px]" style="color: rgba(255, 255, 255, 0.75) !important;">Renewal Charges</div>
                                <div class="text-xs font-bold" style="color: #FDE68A !important;">₹0 • Never Expires</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- What's included Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 sm:p-8 transition-all">
                <div class="flex items-center justify-between mb-5">
                    <h4 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                        <span x-text="t('whats_included') || 'What\'s Included'">What's Included</span>
                    </h4>
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/80 dark:border-emerald-800/60 px-3 py-1 rounded-full flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        <span>All Features Active</span>
                    </span>
                </div>

                <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 text-sm text-slate-700 dark:text-slate-200">
                    <li class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50/90 dark:bg-gray-750 border border-slate-200/70 dark:border-gray-700/60 transition-all hover:border-teal-300 dark:hover:border-teal-700/60 hover:shadow-sm">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm" x-text="((user && user.active_plan && user.active_plan.features && user.active_plan.features.max_devices) || 1) + ' Login Device' + (((user && user.active_plan && user.active_plan.features && user.active_plan.features.max_devices) || 1) > 1 ? 's' : '')"></span>
                    </li>
                    <li class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50/90 dark:bg-gray-750 border border-slate-200/70 dark:border-gray-700/60 transition-all hover:border-teal-300 dark:hover:border-teal-700/60 hover:shadow-sm">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm" x-text="t('plan_feature_unlimited_sales_purchases')">Unlimited Sales & Purchases</span>
                    </li>
                    <li class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50/90 dark:bg-gray-750 border border-slate-200/70 dark:border-gray-700/60 transition-all hover:border-teal-300 dark:hover:border-teal-700/60 hover:shadow-sm">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm" x-text="t('cloud_backup_restore')">Cloud Backup & Restore</span>
                    </li>
                    <li class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50/90 dark:bg-gray-750 border border-slate-200/70 dark:border-gray-700/60 transition-all hover:border-teal-300 dark:hover:border-teal-700/60 hover:shadow-sm">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm" x-text="t('plan_feature_advanced_reports') || 'Advanced Reports'">Advanced Reports</span>
                    </li>
                    <li class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50/90 dark:bg-gray-750 border border-slate-200/70 dark:border-gray-700/60 transition-all hover:border-teal-300 dark:hover:border-teal-700/60 hover:shadow-sm">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm" x-text="t('ad_free_experience') || 'Ad-Free Experience'">Ad-Free Experience</span>
                    </li>
                    <li class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50/90 dark:bg-gray-750 border border-slate-200/70 dark:border-gray-700/60 transition-all hover:border-teal-300 dark:hover:border-teal-700/60 hover:shadow-sm">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm" x-text="t('plan_feature_remove_branding')">Remove DukanHisab Branding</span>
                    </li>
                </ul>

                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="t('lifetime_addons_hint') || 'Need more shops or a website? Add-ons work with your lifetime plan.'"></p>
                    <button type="button" @click="navigateTo('addons')"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-xs font-bold transition-all shadow-md shadow-teal-600/20 shrink-0 cursor-pointer"
                        x-text="t('browse_addons') || 'Browse Add-ons'">Browse Add-ons</button>
                </div>
            </div>
        </div>
    </template>

    <!-- Subscription Status Header Card -->
    <div x-show="!isLifetimePlan()"
        class="subscription-banner relative overflow-hidden p-6 md:p-8 rounded-3xl text-white shadow-xl border border-teal-600/40"
        style="background: linear-gradient(135deg, #0F766E 0%, #115E59 45%, #042F2E 100%) !important;">

        <div class="absolute -right-8 -bottom-8 opacity-15 pointer-events-none transform translate-x-4 translate-y-4">
            <svg class="w-60 h-60 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
            </svg>
        </div>
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="space-y-3">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm"
                    style="background: rgba(255, 255, 255, 0.2) !important; color: #FFFFFF !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                    <svg class="w-3.5 h-3.5" style="color: #6EE7B7 !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    <span style="color: #FFFFFF !important;" x-text="user && user.active_plan ? user.active_plan.name : t('free_plan')"></span>
                </div>
                <h3 class="text-2xl md:text-3xl font-black tracking-tight" style="color: #FFFFFF !important;">
                    <span x-show="user && user.active_plan && user.active_plan.slug !== 'free'" x-text="t('premium_features_unlocked')">Premium Features Unlocked</span>
                    <span x-show="!user || !user.active_plan || user.active_plan.slug === 'free'" x-text="t('go_premium_heading')">Go Premium to Unlock Pro Features</span>
                </h3>
                <p class="text-sm max-w-xl leading-relaxed" style="color: rgba(240, 253, 250, 0.95) !important;" x-text="t('go_premium_desc')">
                    Manage multiple shops and login devices, remove invoice branding, WhatsApp/Email invoices, and download cloud backups securely.
                </p>
            </div>
            
            <div x-show="user && user.active_plan && user.active_plan.slug !== 'free'" class="shrink-0">
                <button type="button" @click="showConfirm(t('cancel_subscription_confirm_title'), t('cancel_subscription_confirm_desc'), () => cancelSubscription())"
                    class="px-5 py-2.5 bg-red-600/95 hover:bg-red-600 text-white font-bold rounded-xl border border-red-500/60 hover:border-red-400 transition-all text-xs flex items-center gap-1.5 shadow-md shadow-red-900/30 cursor-pointer"
                    x-text="t('cancel_active_plan')">
                    Cancel Active Plan
                </button>
            </div>
        </div>
    </div>

    <!-- Available Plans Grid (Dynamic from API) -->
    <div x-show="!isLifetimePlan()">
        <h4 class="text-sm font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
            <span x-text="t('choose_subscription_plan')">Choose a Subscription Plan</span>
        </h4>

        <!-- Loading state -->
        <div x-show="subscriptionLoading && subscriptionPlans.length === 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <template x-for="i in 3" :key="i">
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 sm:p-7 animate-pulse">
                    <div class="h-5 bg-slate-200 dark:bg-slate-700 rounded-lg w-28 mb-3"></div>
                    <div class="h-3 bg-slate-100 dark:bg-slate-600 rounded w-44 mb-5"></div>
                    <div class="h-9 bg-slate-200 dark:bg-slate-700 rounded-lg w-24 mb-6"></div>
                    <div class="space-y-3 mb-6">
                        <div class="h-3.5 bg-slate-100 dark:bg-slate-600 rounded w-full"></div>
                        <div class="h-3.5 bg-slate-100 dark:bg-slate-600 rounded w-full"></div>
                        <div class="h-3.5 bg-slate-100 dark:bg-slate-600 rounded w-3/4"></div>
                    </div>
                    <div class="h-11 bg-slate-200 dark:bg-slate-700 rounded-xl"></div>
                </div>
            </template>
        </div>

        <!-- Plans grid -->
        <div x-show="subscriptionPlans.length > 0" class="grid grid-cols-1 gap-6"
            :class="subscriptionPlans.length === 1 ? 'max-w-md mx-auto' : (subscriptionPlans.length === 2 ? 'md:grid-cols-2 max-w-4xl mx-auto' : 'md:grid-cols-3')">
            <template x-for="plan in subscriptionPlans" :key="plan.id">
                <div x-show="!plan.is_expired || (user && user.active_plan && user.active_plan.id == plan.id)"
                    class="rounded-3xl border shadow-sm p-6 sm:p-7 flex flex-col justify-between transition-all duration-300 relative overflow-hidden group hover:shadow-lg"
                    :class="{
                        'ring-2 ring-emerald-500 dark:ring-emerald-400 ring-offset-2 dark:ring-offset-gray-900 shadow-xl': user && user.active_plan && user.active_plan.id == plan.id,
                        'border-teal-300/80 dark:border-teal-700/70 bg-gradient-to-b from-teal-50/70 via-white to-white dark:from-teal-950/30 dark:via-gray-800 dark:to-gray-800': plan.slug === 'premium',
                        'border-amber-300/80 dark:border-amber-700/70 bg-gradient-to-b from-amber-50/70 via-white to-white dark:from-amber-950/30 dark:via-gray-800 dark:to-gray-800': plan.slug === 'business',
                        'border-slate-200/90 dark:border-gray-700 bg-white dark:bg-gray-800': plan.slug !== 'premium' && plan.slug !== 'business',
                        'opacity-60 grayscale-[40%]': plan.is_expired && !(user && user.active_plan && user.active_plan.id == plan.id)
                    }">

                    <!-- Decorative background accents -->
                    <div x-show="plan.slug === 'premium'" class="absolute -top-12 -right-12 w-28 h-28 bg-teal-400/15 rounded-full blur-2xl pointer-events-none"></div>
                    <div x-show="plan.slug === 'business'" class="absolute -top-12 -right-12 w-28 h-28 bg-amber-400/15 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="space-y-5 relative z-10">
                        <!-- Plan header -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 shadow-sm"
                                    :class="{
                                        'bg-slate-100 dark:bg-gray-700 text-slate-500 dark:text-slate-300': plan.slug === 'free',
                                        'bg-teal-100 dark:bg-teal-900/60 text-teal-600 dark:text-teal-400': plan.slug === 'premium',
                                        'bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400': plan.slug === 'business',
                                        'bg-teal-100 dark:bg-teal-900/60 text-teal-600 dark:text-teal-400': plan.slug !== 'free' && plan.slug !== 'premium' && plan.slug !== 'business'
                                    }">
                                    <!-- Free icon -->
                                    <template x-if="plan.slug === 'free'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </template>
                                    <!-- Premium icon -->
                                    <template x-if="plan.slug === 'premium'">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    </template>
                                    <!-- Business icon -->
                                    <template x-if="plan.slug === 'business'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </template>
                                    <!-- Custom plan icon -->
                                    <template x-if="plan.slug !== 'free' && plan.slug !== 'premium' && plan.slug !== 'business'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                                    </template>
                                </div>
                                <div>
                                    <h5 class="text-base font-extrabold text-slate-900 dark:text-white" x-text="plan.name"></h5>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1" x-text="plan.description || ''"></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-1.5 shrink-0">
                                <!-- Current Plan Badge -->
                                <span x-show="user && user.active_plan && user.active_plan.id == plan.id"
                                    class="px-2.5 py-1 text-[11px] font-bold text-emerald-800 dark:text-emerald-200 bg-emerald-100 dark:bg-emerald-900/60 border border-emerald-300/60 dark:border-emerald-700/60 rounded-full flex items-center gap-1 shadow-sm">
                                    <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    <span x-text="t('current_plan')">Current Plan</span>
                                </span>

                                <!-- Not current plan badges -->
                                <template x-if="!(user && user.active_plan && user.active_plan.id == plan.id)">
                                    <div class="flex items-center gap-1">
                                        <template x-if="plan.slug === 'premium'">
                                            <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-teal-700 dark:text-teal-300 bg-teal-100 dark:bg-teal-900/60 border border-teal-200/80 dark:border-teal-800/60 rounded-full shadow-sm">Popular</span>
                                        </template>
                                        <template x-if="plan.slug === 'business' && !plan.is_expired && plan.days_left !== null">
                                            <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-amber-800 dark:text-amber-200 bg-amber-100 dark:bg-amber-900/60 border border-amber-300/80 dark:border-amber-700/60 rounded-full shadow-sm flex items-center gap-1" x-text="Math.ceil(plan.days_left) + ' Days Left'"></span>
                                        </template>
                                        <template x-if="plan.slug === 'business' && plan.is_expired">
                                            <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/60 border border-red-200 dark:border-red-800 rounded-full shadow-sm">Offer Expired</span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="flex items-baseline gap-1.5 pt-1 border-t border-slate-100 dark:border-gray-700/60">
                            <span class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight"
                                x-text="plan.slug === 'free' ? '₹0' : '₹' + parseFloat(plan.price).toLocaleString('en-IN', { maximumFractionDigits: 0 })"></span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                / <span x-text="plan.billing_period === 'free' ? 'Forever' : (plan.billing_period === 'lifetime' ? 'One-Time' : (plan.billing_period === 'yearly' ? 'Year' : plan.billing_period))"></span>
                            </span>
                        </div>

                        <!-- Feature list from database -->
                        <ul class="space-y-2.5 pt-2 text-xs text-slate-600 dark:text-slate-300">
                            <li class="flex items-center gap-2.5">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span>1 Shop <a href="#" @click.prevent="navigateTo('addons')" class="text-teal-600 dark:text-teal-400 font-semibold hover:underline">(buy Shop Add-on for more)</a></span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span x-text="(plan.features && plan.features.max_devices ? plan.features.max_devices : 1) + ' Login Device' + ((plan.features && plan.features.max_devices > 1) ? 's' : '')"></span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span x-text="t('plan_feature_unlimited_sales_purchases')">Unlimited Sales & Purchases</span>
                            </li>
                            <!-- Cloud Backup -->
                            <li class="flex items-center gap-2.5" :class="plan.slug !== 'free' ? '' : 'opacity-40'">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0"
                                    :class="plan.slug !== 'free' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-gray-700 text-slate-400'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" x-bind:d="plan.slug !== 'free' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'"></path>
                                    </svg>
                                </div>
                                <span :class="plan.slug === 'free' ? 'line-through text-slate-400 dark:text-slate-500' : ''" x-text="t('cloud_backup_restore')">Cloud Backup & Restore</span>
                            </li>
                            <!-- Advanced Reports -->
                            <li class="flex items-center gap-2.5" :class="plan.slug !== 'free' ? '' : 'opacity-40'">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0"
                                    :class="plan.slug !== 'free' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-gray-700 text-slate-400'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" x-bind:d="plan.slug !== 'free' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'"></path>
                                    </svg>
                                </div>
                                <span :class="plan.slug === 'free' ? 'line-through text-slate-400 dark:text-slate-500' : ''" x-text="t('plan_feature_advanced_reports') || 'Advanced Reports'"></span>
                            </li>
                            <!-- Ad-free (non-free plans) -->
                            <li class="flex items-center gap-2.5" :class="plan.slug !== 'free' ? '' : 'opacity-40'">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0"
                                    :class="plan.slug !== 'free' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-gray-700 text-slate-400'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" x-bind:d="plan.slug !== 'free' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'"></path>
                                    </svg>
                                </div>
                                <span :class="plan.slug === 'free' ? 'line-through text-slate-400 dark:text-slate-500' : ''" x-text="t('ad_free_experience') || 'Ad-Free Experience'"></span>
                            </li>
                            <!-- Remove Branding (non-free plans) -->
                            <li class="flex items-center gap-2.5" :class="plan.slug !== 'free' ? '' : 'opacity-40'">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0"
                                    :class="plan.slug !== 'free' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-gray-700 text-slate-400'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" x-bind:d="plan.slug !== 'free' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'"></path>
                                    </svg>
                                </div>
                                <span :class="plan.slug === 'free' ? 'line-through text-slate-400 dark:text-slate-500' : ''" x-text="t('plan_feature_remove_branding')">Remove DukanHisab Branding</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Action button -->
                    <div class="pt-6 relative z-10">
                        <button type="button"
                            @click="upgradeSubscription(plan.slug)"
                            :disabled="(user && user.active_plan && user.active_plan.id == plan.id) || subscriptionLoading || plan.is_expired"
                            :class="{
                                'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-300/80 dark:border-emerald-700/80 cursor-default shadow-none': (user && user.active_plan && user.active_plan.id == plan.id),
                                'bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white shadow-md shadow-teal-600/25': plan.slug === 'premium' && !(user && user.active_plan && user.active_plan.id == plan.id) && !plan.is_expired,
                                'bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black shadow-md shadow-amber-500/25': plan.slug === 'business' && !(user && user.active_plan && user.active_plan.id == plan.id) && !plan.is_expired,
                                'border-2 border-slate-200 dark:border-gray-700 text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-gray-700': plan.slug === 'free' && !(user && user.active_plan && user.active_plan.id == plan.id) && !plan.is_expired,
                                'bg-teal-600 hover:bg-teal-700 text-white shadow-sm': plan.slug !== 'free' && plan.slug !== 'premium' && plan.slug !== 'business' && !(user && user.active_plan && user.active_plan.id == plan.id) && !plan.is_expired,
                                'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-gray-800 text-slate-400 border border-slate-200 dark:border-gray-700': plan.is_expired && !(user && user.active_plan && user.active_plan.id == plan.id)
                            }"
                            class="w-full py-3 rounded-xl text-xs font-bold transition-all disabled:pointer-events-none flex items-center justify-center gap-2">
                            <svg x-show="subscriptionLoading && subscriptionPurchasingSlug === plan.slug" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span x-show="subscriptionLoading && subscriptionPurchasingSlug === plan.slug" x-text="t('processing') || 'Processing...'">Processing...</span>
                            <span x-show="user && user.active_plan && user.active_plan.id == plan.id" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                <span x-text="t('current_plan') || 'Current Plan'">Current Plan</span>
                            </span>
                            <span x-show="!(user && user.active_plan && user.active_plan.id == plan.id) && plan.is_expired">Offer Expired</span>
                            <span x-show="!(subscriptionLoading && subscriptionPurchasingSlug === plan.slug) && !(user && user.active_plan && user.active_plan.id == plan.id) && !plan.is_expired" x-text="plan.slug === 'free' ? (t('free_plan') || 'Free Plan') : (plan.slug === 'business' ? (t('buy_lifetime') || 'Buy Lifetime') : (t('upgrade_to_premium') || 'Upgrade to Premium'))"></span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty state if no plans loaded -->
        <div x-show="!subscriptionLoading && subscriptionPlans.length === 0" class="text-center py-12 text-slate-400 text-sm">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <p>Could not load plans. <button @click="loadSubscriptionPlans()" class="text-teal-600 underline hover:no-underline">Try again</button></p>
        </div>
    </div>
</div>

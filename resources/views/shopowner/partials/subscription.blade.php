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
                    <span x-text="user && user.active_plan ? user.active_plan.name : t('free_plan')"></span>
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

    <!-- Available Plans Grid (Dynamic from API) -->
    <div>
        <h4 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4" x-text="t('choose_subscription_plan')">Choose a Subscription Plan</h4>

        <!-- Loading state -->
        <div x-show="subscriptionLoading && subscriptionPlans.length === 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <template x-for="i in 3" :key="i">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 animate-pulse">
                    <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-24 mb-3"></div>
                    <div class="h-3 bg-slate-100 dark:bg-slate-600 rounded w-40 mb-4"></div>
                    <div class="h-8 bg-slate-200 dark:bg-slate-700 rounded w-20 mb-6"></div>
                    <div class="space-y-2 mb-6">
                        <div class="h-3 bg-slate-100 dark:bg-slate-600 rounded w-full"></div>
                        <div class="h-3 bg-slate-100 dark:bg-slate-600 rounded w-full"></div>
                        <div class="h-3 bg-slate-100 dark:bg-slate-600 rounded w-3/4"></div>
                    </div>
                    <div class="h-10 bg-slate-200 dark:bg-slate-700 rounded-xl"></div>
                </div>
            </template>
        </div>

        <!-- Plans grid -->
        <div x-show="subscriptionPlans.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <template x-for="plan in subscriptionPlans" :key="plan.id">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border shadow-sm p-6 flex flex-col justify-between transition-all"
                    :class="{
                        'ring-2 ring-teal-600 border-transparent': user && user.active_plan && user.active_plan.id == plan.id,
                        'border-slate-200 dark:border-gray-700': !(user && user.active_plan && user.active_plan.id == plan.id)
                    }">
                    <div class="space-y-4">
                        <!-- Plan header -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <!-- Free icon -->
                                <template x-if="plan.slug === 'free'">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </template>
                                <!-- Premium icon -->
                                <template x-if="plan.slug === 'premium'">
                                    <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                </template>
                                <!-- Business icon -->
                                <template x-if="plan.slug === 'business'">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </template>
                                <!-- Custom plan icon -->
                                <template x-if="plan.slug !== 'free' && plan.slug !== 'premium' && plan.slug !== 'business'">
                                    <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                                </template>
                                <span class="text-base font-bold text-slate-800 dark:text-white" x-text="plan.name"></span>
                            </div>
                            <span x-show="user && user.active_plan && user.active_plan.id == plan.id"
                                class="px-2 py-0.5 text-[10px] font-bold text-teal-700 bg-teal-100 dark:bg-teal-900/40 dark:text-teal-300 rounded-full shrink-0"
                                x-text="t('current_plan')">Current Plan</span>
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-slate-400" x-text="plan.description || ''"></p>

                        <!-- Price -->
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-slate-800 dark:text-white"
                                x-text="plan.slug === 'free' ? '₹0' : '₹' + parseFloat(plan.price).toLocaleString('en-IN', { maximumFractionDigits: 0 })"></span>
                            <span class="text-xs text-slate-400">
                                / <span x-text="plan.billing_period === 'free' ? 'Forever' : (plan.billing_period === 'lifetime' ? 'One-Time' : (plan.billing_period === 'yearly' ? 'Year' : plan.billing_period))"></span>
                            </span>
                        </div>

                        <!-- Feature list from database -->
                        <ul class="space-y-2.5 pt-4 text-xs text-slate-600 dark:text-slate-300">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span x-text="(plan.features && plan.features.max_shops ? plan.features.max_shops : 1) + ' Shop' + ((plan.features && plan.features.max_shops > 1) ? 's' : '')"></span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span x-text="(plan.features && plan.features.max_devices ? plan.features.max_devices : 1) + ' Login Device' + ((plan.features && plan.features.max_devices > 1) ? 's' : '')"></span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span x-text="t('plan_feature_unlimited_sales_purchases')">Unlimited Sales & Purchases</span>
                            </li>
                            <!-- Cloud Backup -->
                            <li class="flex items-center gap-2" :class="plan.features && plan.features.backup ? '' : 'opacity-40 line-through'">
                                <svg class="w-4 h-4 shrink-0" :class="plan.features && plan.features.backup ? 'text-emerald-500' : 'text-slate-300 dark:text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-bind:d="plan.features && plan.features.backup ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'"></path>
                                </svg>
                                <span x-text="t('cloud_backup_restore')">Cloud Backup & Restore</span>
                            </li>
                            <!-- Advanced Reports -->
                            <li class="flex items-center gap-2" :class="plan.features && plan.features.advanced_reports ? '' : 'opacity-40 line-through'">
                                <svg class="w-4 h-4 shrink-0" :class="plan.features && plan.features.advanced_reports ? 'text-emerald-500' : 'text-slate-300 dark:text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-bind:d="plan.features && plan.features.advanced_reports ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'"></path>
                                </svg>
                                <span x-text="t('plan_feature_advanced_reports') || 'Advanced Reports'"></span>
                            </li>
                            <!-- Ad-free (non-free plans) -->
                            <li class="flex items-center gap-2" :class="plan.slug !== 'free' ? '' : 'opacity-40 line-through'">
                                <svg class="w-4 h-4 shrink-0" :class="plan.slug !== 'free' ? 'text-emerald-500' : 'text-slate-300 dark:text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-bind:d="plan.slug !== 'free' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'"></path>
                                </svg>
                                <span x-text="t('ad_free_experience') || 'Ad-Free Experience'"></span>
                            </li>
                            <!-- Remove Branding (non-free plans) -->
                            <li class="flex items-center gap-2" :class="plan.slug !== 'free' ? '' : 'opacity-40 line-through'">
                                <svg class="w-4 h-4 shrink-0" :class="plan.slug !== 'free' ? 'text-emerald-500' : 'text-slate-300 dark:text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-bind:d="plan.slug !== 'free' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'"></path>
                                </svg>
                                <span x-text="t('plan_feature_remove_branding')">Remove DukanHisab Branding</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Action button -->
                    <div class="pt-6">
                        <button type="button"
                            @click="upgradeSubscription(plan.slug)"
                            :disabled="(user && user.active_plan && user.active_plan.id == plan.id) || subscriptionLoading"
                            :class="{
                                'bg-teal-600 hover:bg-teal-700 text-white': plan.slug !== 'free' && !(user && user.active_plan && user.active_plan.id == plan.id),
                                'border border-slate-200 dark:border-gray-700 text-slate-800 dark:text-white hover:bg-slate-50 dark:hover:bg-gray-700/50': plan.slug === 'free' && !(user && user.active_plan && user.active_plan.id == plan.id),
                                'opacity-50 cursor-not-allowed border border-slate-200 dark:border-gray-700 text-slate-500 dark:text-slate-400': user && user.active_plan && user.active_plan.id == plan.id
                            }"
                            class="w-full py-2.5 rounded-xl text-xs font-bold transition-all disabled:pointer-events-none">
                            <span x-show="user && user.active_plan && user.active_plan.id == plan.id" x-text="t('current_plan') || 'Current Plan'"></span>
                            <span x-show="!(user && user.active_plan && user.active_plan.id == plan.id)" x-text="plan.slug === 'free' ? (t('free_plan') || 'Free Plan') : (plan.slug === 'business' ? (t('buy_lifetime') || 'Buy Lifetime') : (t('upgrade_to_premium') || 'Upgrade'))"></span>
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

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
                    <span x-text="user && user.active_plan ? user.active_plan.name : 'Free Plan'"></span>
                </div>
                <h3 class="text-2xl font-black">
                    <span x-show="user && user.active_plan && user.active_plan.slug !== 'free'">Premium Features Unlocked</span>
                    <span x-show="!user || !user.active_plan || user.active_plan.slug === 'free'">Go Premium to Unlock Pro Features</span>
                </h3>
                <p class="text-sm opacity-90 max-w-xl">
                    Manage multiple shops and login devices, remove invoice branding, WhatsApp/Email invoices, and download cloud backups securely.
                </p>
            </div>
            
            <div x-show="user && user.active_plan && user.active_plan.slug !== 'free'" class="shrink-0">
                <button type="button" @click="showConfirm('Cancel subscription?', 'You will immediately lose access to premium features and extra shops/devices.', () => cancelSubscription())"
                    class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl border border-red-600 transition-all text-xs flex items-center gap-1.5 shadow-sm">
                    Cancel Active Plan
                </button>
            </div>
        </div>
    </div>

    <!-- Available Plans Grid -->
    <div>
        <h4 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Choose a Subscription Plan</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Free Plan Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between transition-all"
                :class="user && user.active_plan && user.active_plan.slug === 'free' ? 'ring-2 ring-primary border-transparent' : ''">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-bold text-slate-800 dark:text-white">Free Plan</span>
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'free'" class="px-2 py-0.5 text-[10px] font-bold text-primary bg-primary/10 rounded-full">Current Plan</span>
                    </div>
                    <p class="text-xs text-slate-400">Core accounting features for single-shop owners.</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-slate-800 dark:text-white">₹0</span>
                        <span class="text-xs text-slate-400">/ Forever</span>
                    </div>
                    <ul class="space-y-2.5 pt-4 text-xs text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            1 Shop & 1 Login Device
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Core Ledger & Inventory
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Unlimited Sales & Purchases
                        </li>
                        <li class="text-slate-400 line-through flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Cloud Backup & Restore
                        </li>
                        <li class="text-slate-400 line-through flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Remove DukanHisab Branding
                        </li>
                    </ul>
                </div>
                <div class="pt-6">
                    <button type="button" @click="upgradeSubscription('free')" :disabled="user && user.active_plan && user.active_plan.slug === 'free'"
                        class="w-full py-2.5 rounded-xl border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700/50 text-xs font-bold transition-all disabled:opacity-50 disabled:pointer-events-none text-slate-800 dark:text-white">
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'free'">Active Free Plan</span>
                        <span x-show="!user || !user.active_plan || user.active_plan.slug !== 'free'">Free Plan</span>
                    </button>
                </div>
            </div>

            <!-- Premium Plan Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between transition-all"
                :class="user && user.active_plan && user.active_plan.slug === 'premium' ? 'ring-2 ring-primary border-transparent' : ''">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-bold text-slate-800 dark:text-white">⭐ Premium Plan</span>
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'premium'" class="px-2 py-0.5 text-[10px] font-bold text-primary bg-primary/10 rounded-full">Current Plan</span>
                    </div>
                    <p class="text-xs text-slate-400">Ad-free professional experience and multi-shop.</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-slate-800 dark:text-white">₹365</span>
                        <span class="text-xs text-slate-400">/ Year</span>
                    </div>
                    <ul class="space-y-2.5 pt-4 text-xs text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Max 2 Shops & 2 Devices
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Cloud Backup & Restore
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Ad-Free Experience
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            PDF, Print & WhatsApp Invoice
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Excel Export & Remove Branding
                        </li>
                    </ul>
                </div>
                <div class="pt-6">
                    <button type="button" @click="upgradeSubscription('premium')" :disabled="user && user.active_plan && user.active_plan.slug === 'premium'"
                        class="w-full py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition-all disabled:opacity-50 disabled:pointer-events-none">
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'premium'">Active Premium Plan</span>
                        <span x-show="!user || !user.active_plan || user.active_plan.slug !== 'premium'">Upgrade to Premium</span>
                    </button>
                </div>
            </div>

            <!-- Business Plan Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between transition-all"
                :class="user && user.active_plan && user.active_plan.slug === 'business' ? 'ring-2 ring-primary border-transparent' : ''">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-bold text-slate-800 dark:text-white">🚀 Business (Lifetime)</span>
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'business'" class="px-2 py-0.5 text-[10px] font-bold text-primary bg-primary/10 rounded-full">Current Plan</span>
                    </div>
                    <p class="text-xs text-slate-400">Complete control with lifetime updates & max capacity.</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-slate-800 dark:text-white">₹999</span>
                        <span class="text-xs text-slate-400">/ One-Time</span>
                    </div>
                    <ul class="space-y-2.5 pt-4 text-xs text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Max 5 Shops & 5 Devices
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Cloud Backup & Restore
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Excel Export & No Ads
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Premium Website Themes & SEO
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Founder Badge & Priority Support
                        </li>
                    </ul>
                </div>
                <div class="pt-6">
                    <button type="button" @click="upgradeSubscription('business')" :disabled="user && user.active_plan && user.active_plan.slug === 'business'"
                        class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-700 dark:hover:bg-slate-600">
                        <span x-show="user && user.active_plan && user.active_plan.slug === 'business'">Active Business Plan</span>
                        <span x-show="!user || !user.active_plan || user.active_plan.slug !== 'business'">Buy Lifetime</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

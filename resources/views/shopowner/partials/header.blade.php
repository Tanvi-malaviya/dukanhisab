{{-- TOP HEADER --}}
<header class="flex items-center justify-between h-14 bg-white dark:bg-gray-800 px-4 border-b border-slate-200 dark:border-gray-700 md:px-6">
    <div class="flex items-center gap-3">
        <button @click="mobileSidebarOpen = true" class="md:hidden p-1 text-slate-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        {{-- Page title: converts 'sales-history' → 'Sales History' --}}
        <h2 class="text-lg font-bold text-slate-900 dark:text-white"
            x-text="page.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')">
        </h2>
    </div>

    <div class="flex items-center gap-4">
        {{-- Current URL badge (small, shown in dev) --}}
        <!-- <span class="hidden lg:inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-mono font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-gray-700 rounded-full">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            /dukanhisab/<span x-text="page === 'dashboard' ? '' : page"></span>
        </span> -->
        <span class="text-sm text-slate-500 dark:text-slate-400 font-medium hidden sm:inline"
            x-text="new Date().toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })">
        </span>
    </div>
</header>

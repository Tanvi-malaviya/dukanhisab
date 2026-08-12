{{-- TOP HEADER --}}
<header class="flex items-center justify-between h-14 bg-white dark:bg-gray-800 px-4 border-b border-slate-200 dark:border-gray-700 md:px-6">
    <div class="flex items-center gap-3">
        <button @click="mobileSidebarOpen = true" class="md:hidden p-1 text-slate-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        {{-- Page title: converts 'sales-history' → 'Sales History' --}}
        <h2 class="text-lg font-bold text-slate-900 dark:text-white"
            x-text="t(page) || page.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')">
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

        {{-- Language Selector Dropdown --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.outside="open = false" class="p-1.5 rounded-lg bg-slate-100 dark:bg-gray-700 text-slate-500 dark:text-slate-300 flex items-center gap-1.5 transition-all hover:bg-slate-200 dark:hover:bg-gray-600" title="Select Language">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
                <span class="text-[10px] font-extrabold uppercase tracking-wide" x-text="currentLang"></span>
            </button>
            <div x-show="open" x-cloak
                class="absolute right-0 mt-1 w-28 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl shadow-lg z-50 py-1 overflow-hidden transition-all">
                <button @click="setLanguage('en'); open = false;"
                    class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-gray-700/50 flex items-center justify-between">
                    <span>English</span>
                    <span x-show="currentLang === 'en'" class="text-primary font-bold text-xs">✓</span>
                </button>
                <button @click="setLanguage('hi'); open = false;"
                    class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-gray-700/50 flex items-center justify-between">
                    <span>Hindi</span>
                    <span x-show="currentLang === 'hi'" class="text-primary font-bold text-xs">✓</span>
                </button>
                <button @click="setLanguage('gu'); open = false;"
                    class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-gray-700/50 flex items-center justify-between">
                    <span>Gujarati</span>
                    <span x-show="currentLang === 'gu'" class="text-primary font-bold text-xs">✓</span>
                </button>
            </div>
        </div>

        {{-- Theme Toggle --}}
        <button @click="toggleTheme()" class="p-1.5 rounded-lg bg-slate-100 dark:bg-gray-700 text-slate-500 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-gray-600 transition-colors" title="Toggle theme">
            <template x-if="dark">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
            </template>
            <template x-if="!dark">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </template>
        </button>

        {{-- User Profile & Logout Group Box --}}
        <div class="flex items-center gap-2 pl-2 py-0.5 pr-0.5 border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/40 rounded-xl">
            {{-- User Profile Image --}}
            <a href="/dukanhisab/settings" @click.prevent="navigateTo('settings')" title="My Profile" class="block w-7 h-7 rounded-lg overflow-hidden border border-slate-200 dark:border-gray-600 shrink-0 hover:opacity-90 transition-opacity">
                <img :src="user && user.avatar ? '/storage/' + user.avatar : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user ? user.name : 'User') + '&background=0d9488&color=fff'" class="w-full h-full object-cover">
            </a>

            <div class="h-4 w-px bg-slate-200 dark:bg-gray-700"></div>

            {{-- Logout --}}
            <button @click="handleLogout()" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/25 transition-all" title="Logout">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </button>
        </div>
    </div>
</header>

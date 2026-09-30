@extends('layouts.admin')

@section('title', 'Translations & Localization')
@section('page_title', 'Language & Translation Editor')
@section('page_subtitle', 'Edit AI-generated phrases and adapt words to natural, colloquial market terms across all languages.')

@section('content')
<div class="space-y-6">

    <!-- Metric Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-card-dark border border-border-dark p-4 rounded-2xl shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Translation Keys</p>
                <h3 class="text-2xl font-black text-slate-800">{{ number_format($stats['total_keys']) }}</h3>
                <span class="inline-flex items-center text-[10px] text-primary font-bold bg-primary/10 px-2 py-0.5 rounded-md">
                    System Vocabulary
                </span>
            </div>
            <span class="p-3 rounded-2xl bg-primary/10 border border-primary/20 text-primary">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                </svg>
            </span>
        </div>

        <div class="bg-card-dark border border-border-dark p-4 rounded-2xl shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">English (Base)</p>
                <h3 class="text-2xl font-black text-slate-800">{{ number_format($stats['en_count']) }}</h3>
                <span class="inline-flex items-center text-[10px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                    100% Complete
                </span>
            </div>
            <span class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 font-bold text-sm">
                EN
            </span>
        </div>

        <div class="bg-card-dark border border-border-dark p-4 rounded-2xl shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Gujarati (ગુજરાતી)</p>
                <h3 class="text-2xl font-black text-slate-800">{{ number_format($stats['gu_count']) }}</h3>
                <span class="inline-flex items-center text-[10px] text-amber-700 font-bold bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                    {{ round(($stats['gu_count'] / max(1, $stats['total_keys'])) * 100) }}% Translated
                </span>
            </div>
            <span class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 font-bold text-sm">
                GU
            </span>
        </div>

        <div class="bg-card-dark border border-border-dark p-4 rounded-2xl shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Hindi (हिंदी)</p>
                <h3 class="text-2xl font-black text-slate-800">{{ number_format($stats['hi_count']) }}</h3>
                <span class="inline-flex items-center text-[10px] text-blue-700 font-bold bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-md">
                    {{ round(($stats['hi_count'] / max(1, $stats['total_keys'])) * 100) }}% Translated
                </span>
            </div>
            <span class="p-3 rounded-2xl bg-blue-50 border border-blue-200 text-blue-600 font-bold text-sm">
                HI
            </span>
        </div>
    </div>

    <!-- Guidance Banner -->
    <div class="p-4 rounded-2xl bg-primary/5 border border-primary/20 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
        <div class="flex items-start gap-3">
            <span class="p-2 rounded-xl bg-primary/10 text-primary mt-0.5 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </span>
            <div class="text-xs text-slate-600 leading-relaxed">
                <strong class="text-slate-800 font-semibold">How to refine translations:</strong>
                The <span class="font-mono bg-slate-200/80 text-slate-800 px-1 py-0.5 rounded text-[11px]">Key</span> is the fixed system identifier (read-only).
                You can replace formal or overly literal AI words with simple, commonly used daily retail terms (e.g., change formal terms to <strong>"બાકી"</strong> / <strong>"ઉધાર"</strong> in Gujarati, or <strong>"बाकी"</strong> / <strong>"उधार"</strong> in Hindi).
                Click individual row <strong class="text-primary">Save</strong> buttons for instant updates or the top <strong class="text-primary">Save All Changes</strong> button.
            </div>
        </div>
        <button type="button" onclick="openNewKeyModal()"
            class="shrink-0 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all shadow-2xs flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Add New Key</span>
        </button>
    </div>

    <!-- Main Table Container Card -->
    <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">

        <!-- Search & Filter Controls -->
        <div class="p-4 md:p-5 border-b border-border-dark bg-secondary/5 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <!-- Search Form -->
            <form action="{{ route('admin.settings.translations') }}" method="GET" class="flex-1 flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[240px]">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Search key, English, Gujarati, or Hindi..."
                        class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none rounded-xl text-xs md:text-sm text-slate-800 placeholder-slate-400 shadow-2xs">
                </div>

                <!-- Filter Dropdown -->
                <select name="filter" class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs md:text-sm text-slate-700 focus:border-primary focus:outline-none shadow-2xs">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Keys</option>
                    <option value="missing" {{ $filter === 'missing' ? 'selected' : '' }}>Incomplete Only</option>
                </select>

                <!-- Per Page -->
                <select name="per_page" class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs md:text-sm text-slate-700 focus:border-primary focus:outline-none shadow-2xs">
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / page</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / page</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / page</option>
                    <option value="200" {{ $perPage == 200 ? 'selected' : '' }}>200 / page</option>
                </select>

                <button type="submit"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs md:text-sm font-semibold transition-all shadow-2xs cursor-pointer">
                    Search
                </button>

                @if($search !== '' || $filter !== 'all')
                    <a href="{{ route('admin.settings.translations') }}"
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs md:text-sm font-medium transition-all cursor-pointer">
                        Clear
                    </a>
                @endif
            </form>

            <!-- Bulk Save Button -->
            <button type="button" onclick="submitBulkForm()"
                class="px-5 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs md:text-sm font-bold transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Save All Visible Changes</span>
            </button>
        </div>

        <!-- Translation Form & Table -->
        <form id="bulk-translations-form" action="{{ route('admin.settings.translations.update') }}" method="POST">
            @csrf

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-dark bg-slate-50/70 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4 w-1/4">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Translation Key (Not Editable)
                                </span>
                            </th>
                            <th class="py-3 px-4 w-1/4">
                                <span class="flex items-center gap-1.5 text-emerald-800">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    English (Editable)
                                </span>
                            </th>
                            <th class="py-3 px-4 w-1/4">
                                <span class="flex items-center gap-1.5 text-amber-800">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    Gujarati (Editable)
                                </span>
                            </th>
                            <th class="py-3 px-4 w-1/4">
                                <span class="flex items-center gap-1.5 text-blue-800">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    Hindi (Editable)
                                </span>
                            </th>
                            <th class="py-3 px-4 w-24 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark text-xs md:text-sm">
                        @forelse($translations as $index => $item)
                            <tr id="row-{{ $loop->index }}" class="hover:bg-slate-50/50 transition-colors group">
                                <!-- Row number -->
                                <td class="py-3 px-4 text-center font-mono text-[11px] text-slate-400 align-middle">
                                    {{ $translations->firstItem() + $loop->index }}
                                </td>

                                <!-- Key (Read Only Badge) -->
                                <td class="py-3 px-4 align-middle">
                                    <div class="space-y-1">
                                        <input type="hidden" name="translations[{{ $index }}][key]" value="{{ $item['key'] }}" data-key="{{ $item['key'] }}">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono font-semibold text-xs border border-slate-200 select-all" title="Fixed System Key">
                                            {{ $item['key'] }}
                                        </span>
                                    </div>
                                </td>

                                <!-- English (Editable) -->
                                <td class="py-3 px-4 align-middle">
                                    <input type="text"
                                        name="translations[{{ $index }}][en]"
                                        value="{{ $item['en'] }}"
                                        data-en="{{ $item['key'] }}"
                                        placeholder="English text..."
                                        class="w-full px-3 py-2 bg-white border border-slate-200 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-500 focus:outline-none rounded-xl text-xs md:text-sm text-slate-800 transition-all shadow-2xs">
                                </td>

                                <!-- Gujarati (Editable) -->
                                <td class="py-3 px-4 align-middle">
                                    <input type="text"
                                        name="translations[{{ $index }}][gu]"
                                        value="{{ $item['gu'] }}"
                                        data-gu="{{ $item['key'] }}"
                                        placeholder="ગુજરાતી..."
                                        class="w-full px-3 py-2 bg-white border border-slate-200 focus:border-amber-600 focus:ring-1 focus:ring-amber-500 focus:outline-none rounded-xl text-xs md:text-sm text-slate-800 font-medium transition-all shadow-2xs">
                                </td>

                                <!-- Hindi (Editable) -->
                                <td class="py-3 px-4 align-middle">
                                    <input type="text"
                                        name="translations[{{ $index }}][hi]"
                                        value="{{ $item['hi'] }}"
                                        data-hi="{{ $item['key'] }}"
                                        placeholder="हिंदी..."
                                        class="w-full px-3 py-2 bg-white border border-slate-200 focus:border-blue-600 focus:ring-1 focus:ring-blue-500 focus:outline-none rounded-xl text-xs md:text-sm text-slate-800 font-medium transition-all shadow-2xs">
                                </td>

                                <!-- Action (Single Row Save) -->
                                <td class="py-3 px-4 text-center align-middle">
                                    <button type="button"
                                        onclick="saveSingleRow('{{ $item['key'] }}', {{ $loop->index }})"
                                        id="btn-save-{{ $loop->index }}"
                                        class="inline-flex items-center justify-center p-2 rounded-xl text-slate-600 hover:text-primary hover:bg-primary/10 border border-slate-200 hover:border-primary/30 transition-all cursor-pointer shadow-2xs"
                                        title="Save this row">
                                        <svg class="w-4 h-4 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-sm font-medium">No translation keys match your search criteria.</p>
                                        <a href="{{ route('admin.settings.translations') }}" class="text-xs text-primary font-bold hover:underline">Reset Filters</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bottom Actions & Pagination Bar -->
            <div class="p-4 md:p-5 border-t border-border-dark bg-slate-50/50 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Showing <span class="font-bold text-slate-800">{{ $translations->firstItem() ?? 0 }}</span> to <span class="font-bold text-slate-800">{{ $translations->lastItem() ?? 0 }}</span> of <span class="font-bold text-slate-800">{{ $translations->total() }}</span> keys
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="px-5 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs md:text-sm font-bold transition-all shadow-sm flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Save All Changes</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- Pagination Links -->
        @if($translations->hasPages())
            <div class="p-4 border-t border-border-dark flex justify-center">
                {{ $translations->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Add New Key -->
<div id="new-key-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-200 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-2.5">
                <span class="p-1.5 rounded-xl bg-primary/10 text-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </span>
                <h3 class="font-bold text-slate-800 text-sm">Add New Translation Key</h3>
            </div>
            <button type="button" onclick="closeNewKeyModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('admin.settings.translations.store_key') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Key Name (Snake Case)</label>
                <input type="text" name="new_key" placeholder="e.g. quick_cash_checkout" required pattern="[a-zA-Z0-9_\-\.]+"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:outline-none rounded-xl text-xs md:text-sm font-mono text-slate-800">
                <span class="text-[10px] text-slate-400 mt-1 block">Letters, numbers, underscores, and dashes only.</span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">English Text</label>
                <input type="text" name="new_en" placeholder="e.g. Quick Cash Checkout"
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:outline-none rounded-xl text-xs md:text-sm text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Gujarati Text (ગુજરાતી)</label>
                <input type="text" name="new_gu" placeholder="દા.ત. ઝડપી રોકડ ચુકવણી"
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:outline-none rounded-xl text-xs md:text-sm text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Hindi Text (हिंदी)</label>
                <input type="text" name="new_hi" placeholder="उदा. त्वरित नकद भुगतान"
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 focus:bg-white focus:border-primary focus:outline-none rounded-xl text-xs md:text-sm text-slate-800">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeNewKeyModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-all cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer">
                    Save Key
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Floating Toast Notification -->
<div id="toast-notify" class="fixed bottom-6 right-6 z-50 transform translate-y-12 opacity-0 pointer-events-none transition-all duration-300 flex items-center gap-3 px-4.5 py-3 rounded-2xl shadow-xl border text-xs font-semibold">
    <span id="toast-icon"></span>
    <span id="toast-message"></span>
</div>

<script>
    function submitBulkForm() {
        document.getElementById('bulk-translations-form').submit();
    }

    function openNewKeyModal() {
        document.getElementById('new-key-modal').classList.remove('hidden');
    }

    function closeNewKeyModal() {
        document.getElementById('new-key-modal').classList.add('hidden');
    }

    // Highlight modified inputs to show unsaved edits
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input[data-en], input[data-gu], input[data-hi]');
        inputs.forEach(input => {
            const originalVal = input.value;
            input.addEventListener('input', function() {
                if (this.value !== originalVal) {
                    this.classList.add('border-amber-400', 'bg-amber-50/30');
                } else {
                    this.classList.remove('border-amber-400', 'bg-amber-50/30');
                }
            });
        });
    });

    function showToast(message, isSuccess = true) {
        const toast = document.getElementById('toast-notify');
        const msgEl = document.getElementById('toast-message');
        const iconEl = document.getElementById('toast-icon');

        msgEl.textContent = message;
        if (isSuccess) {
            toast.className = 'fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4.5 py-3 rounded-2xl shadow-xl border bg-emerald-600 text-white border-emerald-500 text-xs font-semibold transition-all duration-300 transform translate-y-0 opacity-100';
            iconEl.innerHTML = `<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
        } else {
            toast.className = 'fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4.5 py-3 rounded-2xl shadow-xl border bg-red-600 text-white border-red-500 text-xs font-semibold transition-all duration-300 transform translate-y-0 opacity-100';
            iconEl.innerHTML = `<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
        }

        setTimeout(() => {
            toast.classList.add('translate-y-12', 'opacity-0');
        }, 3000);
    }

    function saveSingleRow(key, rowIndex) {
        const btn = document.getElementById('btn-save-' + rowIndex);
        const enInput = document.querySelector(`input[data-en="${key}"]`);
        const guInput = document.querySelector(`input[data-gu="${key}"]`);
        const hiInput = document.querySelector(`input[data-hi="${key}"]`);

        if (!btn || !enInput || !guInput || !hiInput) return;

        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>`;

        fetch("{{ route('admin.settings.translations.single') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                key: key,
                en: enInput.value,
                gu: guInput.value,
                hi: hiInput.value
            })
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                btn.innerHTML = `<svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
                enInput.classList.remove('border-amber-400', 'bg-amber-50/30');
                guInput.classList.remove('border-amber-400', 'bg-amber-50/30');
                hiInput.classList.remove('border-amber-400', 'bg-amber-50/30');
                showToast(`Saved '${key}'!`, true);
                setTimeout(() => {
                    btn.innerHTML = originalBtnHtml;
                }, 1800);
            } else {
                btn.innerHTML = originalBtnHtml;
                showToast(data.message || 'Error saving translation', false);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
            showToast('Network error while saving translation', false);
        });
    }
</script>
@endsection

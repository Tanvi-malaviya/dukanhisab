@extends('layouts.admin')

@section('title', 'Translations & Localization')
@section('page_title', 'Translations & Localization')
@section('page_subtitle', 'Manage and refine translations across all 22 official languages.')

@section('content')
<div class="space-y-3">

    <!-- Persistence Status Notice -->
    <div class="bg-teal-50/70 border border-teal-200/80 rounded-xl px-3.5 py-2 flex items-center justify-between text-xs text-teal-900 shadow-2xs">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-teal-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <span>
                <strong class="font-bold">Database Persistence Active:</strong> All translations saved here are stored directly in MySQL and cached in memory. When you deploy or run <code>git pull</code> on the live server, your custom translations remain 100% intact and will never revert.
            </span>
        </div>
    </div>

    <!-- Compact Unified Controls Card -->
    <div class="bg-white border border-slate-200 rounded-xl p-3.5 shadow-2xs space-y-3">
        <!-- Row 1: Search, Filter, Stats & Actions -->
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-2.5">
            <!-- Search & Filter Form -->
            <form action="{{ route('admin.settings.translations') }}" method="GET" class="flex-1 flex flex-wrap items-center gap-2">
                <input type="hidden" name="langs" value="{{ $currentLangsParam }}">

                <!-- Search Input -->
                <div class="relative flex-1 min-w-[200px]">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Search key, English, or translated text..."
                        class="w-full pl-9 pr-3 py-1.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-teal-600 focus:ring-1 focus:ring-teal-500/20 focus:outline-none rounded-lg text-xs text-slate-800 placeholder-slate-400 transition-colors">
                </div>

                <!-- Missing Filter -->
                <select name="filter" class="px-2.5 py-1.5 bg-slate-50 hover:bg-white border border-slate-200 rounded-lg text-xs text-slate-700 focus:border-teal-600 focus:outline-none transition-colors">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Keys ({{ number_format($stats['total_keys']) }})</option>
                    <option value="missing" {{ $filter === 'missing' ? 'selected' : '' }}>Incomplete Only</option>
                </select>

                <!-- Per Page -->
                <select name="per_page" class="px-2.5 py-1.5 bg-slate-50 hover:bg-white border border-slate-200 rounded-lg text-xs text-slate-700 focus:border-teal-600 focus:outline-none transition-colors">
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / page</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / page</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / page</option>
                </select>

                <button type="submit" class="px-3.5 py-1.5 bg-teal-700 hover:bg-teal-800 text-white rounded-lg text-xs font-bold transition-all shadow-2xs cursor-pointer">
                    Search
                </button>

                @if($search !== '' || $filter !== 'all')
                    <a href="{{ route('admin.settings.translations', ['langs' => $currentLangsParam]) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-lg text-xs font-medium transition-colors cursor-pointer">
                        Clear
                    </a>
                @endif
            </form>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 shrink-0">
                <form action="{{ route('admin.settings.translations.clear_cache') }}" method="POST" class="inline m-0">
                    @csrf
                    <button type="submit"
                        class="px-2.5 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors shadow-2xs flex items-center gap-1.5 cursor-pointer"
                        title="Reload fresh translations from disk and clear cache">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Refresh Cache</span>
                    </button>
                </form>

                <button type="button" onclick="openNewKeyModal()"
                    class="px-3 py-1.5 bg-white hover:bg-teal-50/50 border border-slate-200 hover:border-teal-300 text-slate-700 text-xs font-semibold rounded-lg transition-colors shadow-2xs flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Key</span>
                </button>

                <button type="button" onclick="submitBulkForm()"
                    class="px-3.5 py-1.5 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-lg transition-colors shadow-2xs flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Save Visible</span>
                </button>
            </div>
        </div>

        <!-- Row 2: Clean Language Selector Bar -->
        <div class="pt-2.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-1">
                <span class="text-[11px] font-semibold text-slate-500 mr-1">Languages:</span>

                @php
                    $isCommonActive = (count($activeLangs) === 2 && in_array('gu', $activeLangs) && in_array('hi', $activeLangs));
                @endphp
                <a href="{{ route('admin.settings.translations', array_merge(request()->query(), ['langs' => 'gu,hi'])) }}"
                   class="px-2.5 py-1 rounded-md text-xs font-semibold transition-colors flex items-center gap-1 {{ $isCommonActive ? 'bg-teal-700 text-white font-bold shadow-xs border border-teal-800' : 'bg-slate-100 hover:bg-teal-50/80 text-slate-700 border border-slate-200 hover:border-teal-300' }}">
                    <span>Common (EN + GU + HI)</span>
                </a>

                @php
                    $quickLangs = ['gu' => 'Gujarati', 'hi' => 'Hindi', 'mr' => 'Marathi', 'bn' => 'Bengali', 'ta' => 'Tamil', 'te' => 'Telugu', 'pa' => 'Punjabi'];
                @endphp
                @foreach($quickLangs as $code => $label)
                    @php
                        $isCurrent = (count($activeLangs) === 1 && $activeLangs[0] === $code);
                        $meta = $languages[$code];
                    @endphp
                    <a href="{{ route('admin.settings.translations', array_merge(request()->query(), ['langs' => $code])) }}"
                       class="px-2 py-1 rounded-md text-xs transition-colors flex items-center gap-1 {{ $isCurrent ? 'bg-teal-700 text-white font-bold shadow-xs border border-teal-800' : 'bg-white hover:bg-teal-50/80 text-slate-700 border border-slate-200 hover:border-teal-300' }}">
                        <span>{{ $meta['name'] }}</span>
                        <span class="text-[10px] {{ $isCurrent ? 'text-teal-100 font-normal' : 'text-slate-400' }}">({{ $meta['native'] }})</span>
                    </a>
                @endforeach
            </div>

            <!-- More Languages Dropdown -->
            <div class="flex items-center gap-1.5">
                <select onchange="if(this.value) window.location.href=this.value;"
                        class="px-2.5 py-1 bg-white hover:bg-teal-50/50 border border-slate-200 hover:border-teal-300 rounded-md text-xs text-slate-700 focus:border-teal-600 focus:outline-none transition-colors">
                    <option value="">More Languages ({{ count($languages) - count($quickLangs) - 1 }} more)...</option>
                    @foreach($languages as $code => $meta)
                        @if(array_key_exists($code, $quickLangs) || $code === 'en') @continue @endif
                        @php
                            $isSel = (count($activeLangs) === 1 && $activeLangs[0] === $code);
                        @endphp
                        <option value="{{ route('admin.settings.translations', array_merge(request()->query(), ['langs' => $code])) }}" {{ $isSel ? 'selected' : '' }}>
                            {{ $meta['name'] }} ({{ $meta['native'] }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Main Data Grid Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-2xs overflow-hidden">
        <form id="bulk-translations-form" action="{{ route('admin.settings.translations.update') }}" method="POST">
            @csrf

            <table class="w-full text-left border-collapse">
                <!-- Clean Sticky Header with Teal tint -->
                <thead>
                    <tr class="bg-teal-50/70 border-b border-teal-100 text-[11px] font-bold uppercase tracking-wider text-slate-700">
                        <th class="py-2.5 px-3 w-10 text-center border-r border-teal-100/80">#</th>

                        <!-- Key Column (22%) -->
                        <th class="py-2.5 px-3 w-1/5 min-w-[180px] border-r border-teal-100/80">
                            Translation Key
                        </th>

                        <!-- English Column (Base) -->
                        <th class="py-2.5 px-3 border-r border-teal-100/80">
                            English (Base)
                        </th>

                        <!-- Target Language Columns -->
                        @foreach($activeLangs as $activeCode)
                            @php
                                $meta = $languages[$activeCode] ?? ['name' => strtoupper($activeCode), 'native' => ''];
                            @endphp
                            <th class="py-2.5 px-3 border-r border-teal-100/80">
                                {{ $meta['name'] }} <span class="text-teal-800/60 font-medium">({{ $meta['native'] }})</span>
                            </th>
                        @endforeach

                        <!-- Action Column (80px) -->
                        <th class="py-2.5 px-2 w-20 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($translations as $index => $item)
                        <tr id="row-{{ $loop->index }}" class="hover:bg-teal-50/20 transition-colors">
                            <!-- # -->
                            <td class="py-2 px-2 text-center font-mono text-[11px] text-slate-400 border-r border-slate-100 align-top pt-2.5">
                                {{ $translations->firstItem() + $loop->index }}
                            </td>

                            <!-- Key -->
                            <td class="py-2 px-3 border-r border-slate-100 align-top pt-2.5">
                                <input type="hidden" name="translations[{{ $index }}][key]" value="{{ $item['key'] }}">
                                <div class="space-y-1">
                                    <div class="flex items-start justify-between gap-1 group/key">
                                        <span class="font-mono text-[11px] text-slate-700 leading-snug break-words select-all font-medium" title="{{ $item['key'] }}">
                                            {{ $item['key'] }}
                                        </span>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $item['key'] }}'); showToast('Copied key!');"
                                                class="opacity-0 group-hover/key:opacity-100 p-0.5 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-200 transition-all cursor-pointer shrink-0"
                                                title="Copy Key">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    @php
                                        $hasOverride = false;
                                        foreach(array_merge(['en'], $activeLangs) as $checkLang) {
                                            if (!empty($overridesMap[$checkLang][$item['key']])) {
                                                $hasOverride = true;
                                                break;
                                            }
                                        }
                                    @endphp
                                    @if($hasOverride)
                                        <div>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Saved in database (immune to git pull)">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> DB Saved
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- English (Base) Textarea -->
                            <td class="py-1.5 px-2 border-r border-slate-100 align-top">
                                <textarea
                                    name="translations[{{ $index }}][en]"
                                    data-lang-input="en"
                                    data-row-key="{{ $item['key'] }}"
                                    rows="2"
                                    class="w-full px-2.5 py-1.5 bg-white border border-slate-200 hover:border-slate-300 focus:border-teal-600 focus:ring-1 focus:ring-teal-500/20 focus:bg-teal-50/10 focus:outline-none rounded-md text-xs text-slate-800 transition-colors leading-relaxed font-sans resize-y">{{ $item['en'] }}</textarea>
                            </td>

                            <!-- Target Language Textarea(s) -->
                            @foreach($activeLangs as $activeCode)
                                @php
                                    $val = $item['values'][$activeCode] ?? '';
                                    $meta = $languages[$activeCode] ?? ['name' => strtoupper($activeCode), 'native' => ''];
                                @endphp
                                <td class="py-1.5 px-2 border-r border-slate-100 align-top">
                                    <textarea
                                        name="translations[{{ $index }}][values][{{ $activeCode }}]"
                                        data-lang-input="{{ $activeCode }}"
                                        data-row-key="{{ $item['key'] }}"
                                        rows="2"
                                        placeholder="{{ $meta['name'] }}..."
                                        class="w-full px-2.5 py-1.5 bg-white border border-slate-200 hover:border-slate-300 focus:border-teal-600 focus:ring-1 focus:ring-teal-500/20 focus:bg-teal-50/10 focus:outline-none rounded-md text-xs text-slate-800 transition-colors leading-relaxed font-sans resize-y">{{ $val }}</textarea>
                                </td>
                            @endforeach

                            <!-- Actions -->
                            <td class="py-1.5 px-2 text-center align-top pt-2">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Save Row Button -->
                                    <button type="button"
                                        onclick="saveSingleRow('{{ $item['key'] }}', {{ $loop->index }})"
                                        id="btn-save-{{ $loop->index }}"
                                        class="w-7 h-7 rounded-md text-teal-700 hover:text-white bg-teal-50 hover:bg-teal-700 border border-teal-200 hover:border-teal-700 transition-all flex items-center justify-center cursor-pointer shadow-2xs"
                                        title="Save Row">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                        </svg>
                                    </button>

                                    <!-- All 22 Modal -->
                                    <button type="button"
                                        onclick="openAllLangsModal('{{ $item['key'] }}')"
                                        class="px-1.5 h-7 rounded-md text-[10px] font-bold text-teal-800 hover:text-white bg-teal-50/70 hover:bg-teal-700 border border-teal-200 hover:border-teal-700 transition-all flex items-center cursor-pointer shadow-2xs"
                                        title="View/Edit All 22 Languages">
                                        <span>All (22)</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + count($activeLangs) }}" class="py-10 text-center text-slate-400">
                                <p class="text-xs font-semibold text-slate-600">No translation keys match your query.</p>
                                <a href="{{ route('admin.settings.translations', ['langs' => $currentLangsParam]) }}" class="text-xs text-teal-700 font-bold hover:underline">Clear Search</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Bottom Pagination Bar -->
            <div class="p-3 border-t border-slate-200 bg-slate-50/60 flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
                <div class="text-slate-500">
                    Showing <span class="font-bold text-slate-700">{{ $translations->firstItem() ?? 0 }}</span> to <span class="font-bold text-slate-700">{{ $translations->lastItem() ?? 0 }}</span> of <span class="font-bold text-slate-700">{{ $translations->total() }}</span> keys
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="px-4 py-1.5 bg-teal-700 hover:bg-teal-800 text-white rounded-lg text-xs font-bold transition-colors shadow-2xs flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Save All Changes</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- Pagination Links -->
        @if($translations->hasPages())
            <div class="p-2.5 border-t border-slate-200 flex justify-center bg-white">
                {{ $translations->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Edit All 22 Languages for a Key -->
<div id="all-langs-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-4xl max-h-[90vh] shadow-xl flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">All 22 Languages Editor</h3>
                <p class="text-xs font-mono text-slate-600 font-semibold select-all" id="modal-key-title">key_name</p>
            </div>
            <button type="button" onclick="closeAllLangsModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer leading-none">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-5 overflow-y-auto flex-1 space-y-3">
            <div id="modal-loading-spinner" class="py-12 flex flex-col items-center justify-center gap-2 text-slate-400">
                <svg class="animate-spin w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <p class="text-xs">Loading translations...</p>
            </div>

            <form id="all-langs-form" class="hidden space-y-3">
                <input type="hidden" id="modal-target-key" value="">

                <!-- English Base Card -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <label class="text-xs font-bold text-slate-700">English (Base Reference Standard)</label>
                    <textarea id="modal-input-en" rows="2"
                           class="w-full px-3 py-1.5 bg-white border border-slate-200 focus:border-slate-400 focus:outline-none rounded-lg text-xs font-semibold text-slate-800 leading-relaxed"></textarea>
                </div>

                <!-- 21 Indian Languages Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5" id="modal-langs-grid">
                    <!-- Populated via JavaScript -->
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
            <span class="text-xs text-slate-500">Save changes across all 22 language files</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeAllLangsModal()" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-200 rounded-lg cursor-pointer">
                    Cancel
                </button>
                <button type="button" id="btn-save-all-modal" onclick="submitAllLangsModal()" class="px-4 py-1.5 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-lg transition-colors shadow-2xs flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Save All 22 Languages</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add New Key -->
<div id="new-key-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-xl max-h-[90vh] shadow-xl flex flex-col overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
            <h3 class="font-bold text-slate-800 text-sm">Add New Translation Key</h3>
            <button type="button" onclick="closeNewKeyModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('admin.settings.translations.store_key') }}" method="POST" class="p-5 space-y-3 overflow-y-auto flex-1">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Key Name (Snake Case)</label>
                <input type="text" name="new_key" placeholder="e.g. quick_cash_checkout" required pattern="[a-zA-Z0-9_\-\.]+"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 focus:bg-white focus:border-slate-400 focus:outline-none rounded-lg text-xs font-mono text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">English Text (Base)</label>
                <input type="text" name="new_en" placeholder="e.g. Quick Cash Checkout" required
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 focus:bg-white focus:border-slate-400 focus:outline-none rounded-lg text-xs text-slate-800">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Gujarati (ગુજરાતી)</label>
                    <input type="text" name="new_gu" placeholder="દા.ત. ઝડપી રોકડ ચુકવણી"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 focus:bg-white focus:border-slate-400 focus:outline-none rounded-lg text-xs text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Hindi (हिंदी)</label>
                    <input type="text" name="new_hi" placeholder="उदा. त्वरित नकद भुगतान"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 focus:bg-white focus:border-slate-400 focus:outline-none rounded-lg text-xs text-slate-800">
                </div>
            </div>

            <!-- Optional other languages -->
            <details class="border border-slate-200 rounded-xl p-2.5 bg-slate-50 group">
                <summary class="text-xs font-semibold text-slate-700 cursor-pointer flex items-center justify-between">
                    <span>Other 19 Regional Languages (Optional)</span>
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2 pt-2 border-t border-slate-200 max-h-48 overflow-y-auto pr-1">
                    @foreach($languages as $code => $meta)
                        @if(in_array($code, ['en', 'gu', 'hi'])) @continue @endif
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-600 uppercase mb-0.5">{{ $meta['name'] }} ({{ $meta['native'] }})</label>
                            <input type="text" name="new_translations[{{ $code }}]" placeholder="{{ $meta['name'] }}..."
                                class="w-full px-2.5 py-1 bg-white border border-slate-200 focus:border-slate-400 focus:outline-none rounded-md text-xs text-slate-800">
                        </div>
                    @endforeach
                </div>
            </details>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2 shrink-0">
                <button type="button" onclick="closeNewKeyModal()" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-1.5 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-lg transition-colors cursor-pointer">
                    Save Key
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Floating Toast Notification -->
<div id="toast-notify" style="opacity: 0; transform: translateY(1.5rem); pointer-events: none; background-color: #059669; color: #ffffff;" class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-2.5 rounded-xl shadow-2xl border border-emerald-400 text-xs font-semibold transition-all duration-300">
    <span id="toast-icon" class="flex items-center"></span>
    <span id="toast-message" style="color: #ffffff !important; font-weight: 600;" class="text-white"></span>
    <button type="button" onclick="hideToast()" style="color: rgba(255,255,255,0.85);" class="ml-1.5 hover:text-white text-base leading-none cursor-pointer" title="Dismiss">&times;</button>
</div>

<script>
    const ALL_LANGUAGES_META = {!! json_encode($languages) !!};

    function submitBulkForm() {
        document.getElementById('bulk-translations-form').submit();
    }

    function openNewKeyModal() {
        document.getElementById('new-key-modal').classList.remove('hidden');
    }

    function closeNewKeyModal() {
        document.getElementById('new-key-modal').classList.add('hidden');
    }

    function closeAllLangsModal() {
        document.getElementById('all-langs-modal').classList.add('hidden');
    }

    function openAllLangsModal(key) {
        const modal = document.getElementById('all-langs-modal');
        const spinner = document.getElementById('modal-loading-spinner');
        const form = document.getElementById('all-langs-form');
        const keyTitle = document.getElementById('modal-key-title');
        const targetKey = document.getElementById('modal-target-key');
        const grid = document.getElementById('modal-langs-grid');

        keyTitle.textContent = key;
        targetKey.value = key;

        modal.classList.remove('hidden');
        spinner.classList.remove('hidden');
        form.classList.add('hidden');
        grid.innerHTML = '';

        fetch(`{{ route('admin.settings.translations.key_all') }}?key=${encodeURIComponent(key)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            spinner.classList.add('hidden');
            form.classList.remove('hidden');

            if (data.status === 'success' && data.translations) {
                document.getElementById('modal-input-en').value = (data.translations['en'] && data.translations['en'].value) ? data.translations['en'].value : '';

                for (const [code, meta] of Object.entries(ALL_LANGUAGES_META)) {
                    if (code === 'en') continue;

                    const val = (data.translations[code] && data.translations[code].value) ? data.translations[code].value : '';
                    const col = document.createElement('div');
                    col.className = 'p-2 bg-slate-50 border border-slate-200 rounded-lg space-y-1';
                    col.innerHTML = `
                        <div class="flex items-center justify-between">
                            <label class="text-[11px] font-bold text-slate-700">
                                ${meta.name} <span class="text-slate-400 font-normal">(${meta.native})</span>
                            </label>
                            <span class="text-[9px] font-mono text-slate-400 uppercase">${code}</span>
                        </div>
                        <textarea data-modal-lang="${code}" rows="2" placeholder="${meta.name} text..."
                               class="w-full px-2.5 py-1.5 bg-white border border-slate-200 focus:border-slate-400 focus:outline-none rounded-md text-xs text-slate-800 leading-relaxed font-sans resize-y">${escapeHtml(val)}</textarea>
                    `;
                    grid.appendChild(col);
                }
            } else {
                showToast(data.message || 'Error loading translations', false);
            }
        })
        .catch(err => {
            spinner.classList.add('hidden');
            showToast('Failed to load translations from server.', false);
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function submitAllLangsModal() {
        const key = document.getElementById('modal-target-key').value;
        const btn = document.getElementById('btn-save-all-modal');
        if (!key) return;

        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg> Saving...`;

        const payload = {
            key: key,
            translations: {
                en: document.getElementById('modal-input-en').value
            }
        };

        const otherInputs = document.querySelectorAll('#modal-langs-grid textarea[data-modal-lang]');
        otherInputs.forEach(input => {
            const langCode = input.getAttribute('data-modal-lang');
            payload.translations[langCode] = input.value;
        });

        fetch("{{ route('admin.settings.translations.update_all') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;

            if (data.status === 'success') {
                showToast(`Saved translations for '${key}'!`, true);
                closeAllLangsModal();

                const enRowInput = document.querySelector(`textarea[data-lang-input="en"][data-row-key="${key}"]`);
                if (enRowInput) enRowInput.value = payload.translations.en;

                for (const [code, val] of Object.entries(payload.translations)) {
                    const rowInput = document.querySelector(`textarea[data-lang-input="${code}"][data-row-key="${key}"]`);
                    if (rowInput) rowInput.value = val;
                }
            } else {
                showToast(data.message || 'Error saving translations', false);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
            showToast('Network error while saving translations', false);
        });
    }

    let toastTimer = null;

    function hideToast() {
        const toast = document.getElementById('toast-notify');
        if (!toast) return;
        if (toastTimer) {
            clearTimeout(toastTimer);
            toastTimer = null;
        }
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(1.5rem)';
        toast.style.pointerEvents = 'none';
    }

    function showToast(message, isSuccess = true) {
        const toast = document.getElementById('toast-notify');
        const msgEl = document.getElementById('toast-message');
        const iconEl = document.getElementById('toast-icon');

        if (!toast || !msgEl || !iconEl) return;

        if (toastTimer) {
            clearTimeout(toastTimer);
            toastTimer = null;
        }

        msgEl.textContent = message;

        // Apply theme colors and explicit styles to prevent layout CSS override
        if (isSuccess) {
            toast.style.backgroundColor = '#059669';
            toast.style.borderColor = '#10B981';
            iconEl.innerHTML = `<svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>`;
        } else {
            toast.style.backgroundColor = '#DC2626';
            toast.style.borderColor = '#EF4444';
            iconEl.innerHTML = `<svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>`;
        }

        toast.style.color = '#FFFFFF';
        msgEl.style.color = '#FFFFFF';

        // Force show with inline styles
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
        toast.style.pointerEvents = 'auto';

        // Auto dismiss after 2.4 seconds
        toastTimer = setTimeout(() => {
            hideToast();
        }, 2400);
    }

    function saveSingleRow(key, rowIndex) {
        const btn = document.getElementById('btn-save-' + rowIndex);
        const rowInputs = document.querySelectorAll(`textarea[data-row-key="${key}"]`);

        if (!btn || rowInputs.length === 0) return;

        const values = {};
        rowInputs.forEach(inp => {
            const langCode = inp.getAttribute('data-lang-input');
            values[langCode] = inp.value;
        });

        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin w-3.5 h-3.5 text-teal-700 m-auto block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;

        fetch("{{ route('admin.settings.translations.single') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                key: key,
                values: values
            })
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                btn.innerHTML = `<svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
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

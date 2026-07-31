@props([
    'title' => 'No records found',
    'message' => 'We couldn\'t find any items matching your current filters or search terms.',
    'resetUrl' => null,
    'resetText' => 'Clear Filters',
    'colspan' => null
])

@if($colspan)
    <tr>
        <td colspan="{{ $colspan }}" class="py-12 px-4 text-center bg-white">
@else
    <div class="w-full py-12 px-4 text-center bg-white border border-slate-200 rounded-2xl shadow-xs">
@endif
        <div class="max-w-md mx-auto space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center mx-auto shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <div>
                <h4 class="text-base font-bold text-slate-700">{{ $title }}</h4>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed max-w-sm mx-auto">{{ $message }}</p>
            </div>
            @if($resetUrl)
                <div class="pt-1">
                    <a href="{{ $resetUrl }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span>{{ $resetText }}</span>
                    </a>
                </div>
            @endif
        </div>
@if($colspan)
        </td>
    </tr>
@else
    </div>
@endif

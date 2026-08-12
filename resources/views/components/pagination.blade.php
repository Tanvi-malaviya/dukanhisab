@props(['records' => null, 'currentPage' => null, 'totalItems' => null, 'perPage' => null, 'loading' => null])

@if($records && method_exists($records, 'hasPages') && $records->hasPages())
<div class="px-6 py-4 border-t border-border-dark bg-secondary/10">
    {{ $records->links() }}
</div>
@elseif($currentPage && $totalItems && $perPage)
<div x-show="!{{ $loading ?? 'false' }} && {{ $totalItems }} > 0" x-cloak class="px-6 py-4 border-t border-slate-200 dark:border-gray-700 bg-slate-50/50 dark:bg-gray-800/50 flex flex-col sm:flex-row justify-between items-center gap-4">
    <div class="text-xs text-slate-500">
        <span x-text="t('showing') || 'Showing'">Showing</span> <span class="font-bold text-slate-800 dark:text-white" x-text="Math.min(({{ $currentPage }} - 1) * {{ $perPage }} + 1, {{ $totalItems }})"></span> 
        <span x-text="t('to') || 'to'">to</span> <span class="font-bold text-slate-800 dark:text-white" x-text="Math.min({{ $currentPage }} * {{ $perPage }}, {{ $totalItems }})"></span> 
        <span x-text="t('of') || 'of'">of</span> <span class="font-bold text-slate-800 dark:text-white" x-text="{{ $totalItems }}"></span> <span x-text="t('entries') || 'entries'">entries</span>
    </div>
    <div class="flex items-center gap-1" x-show="Math.ceil({{ $totalItems }} / {{ $perPage }}) > 1">
        <button 
            type="button"
            @click="{{ $currentPage }} = Math.max(1, {{ $currentPage }} - 1)" 
            :disabled="{{ $currentPage }} === 1"
            class="p-2 border border-slate-200 dark:border-gray-700 rounded-lg hover:bg-slate-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:hover:bg-transparent text-slate-600 dark:text-slate-400 transition-all cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        
        <template x-for="p in Math.ceil({{ $totalItems }} / {{ $perPage }})" :key="p">
            <button 
                type="button"
                @click="{{ $currentPage }} = p" 
                :class="{{ $currentPage }} === p ? 'bg-primary text-white border-primary' : 'border-slate-200 dark:border-gray-700 hover:bg-slate-100 dark:hover:bg-gray-700 text-slate-600 dark:text-slate-400'"
                class="px-3 py-1.5 border rounded-lg text-xs font-bold transition-all cursor-pointer"
                x-text="p"
            ></button>
        </template>
        
        <button 
            type="button"
            @click="{{ $currentPage }} = Math.min(Math.ceil({{ $totalItems }} / {{ $perPage }}), {{ $currentPage }} + 1)" 
            :disabled="{{ $currentPage }} >= Math.ceil({{ $totalItems }} / {{ $perPage }})"
            class="p-2 border border-slate-200 dark:border-gray-700 rounded-lg hover:bg-slate-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:hover:bg-transparent text-slate-600 dark:text-slate-400 transition-all cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </div>
</div>
@endif

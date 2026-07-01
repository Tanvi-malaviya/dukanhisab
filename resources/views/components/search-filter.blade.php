@props([
    'action',
    'placeholder' => 'Search...',
    'showReset' => true
])
<div class="bg-card-dark border border-border-dark p-3 rounded-2xl shadow-sm">
    <form action="{{ $action }}" method="GET" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1 flex flex-col md:flex-row gap-3" style="flex-grow: 1;">
            
            <!-- Search field -->
            <div class="relative flex-grow" style="flex-grow: 1; min-width: 240px;">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $placeholder }}" 
                    class="block w-full pl-9 pr-4 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-800 placeholder-slate-400">
            </div>

            <!-- Custom Slot Filters -->
            {{ $slot }}
        </div>

        <div class="flex gap-2">
            <x-button type="submit" variant="primary">Apply</x-button>
            @if($showReset)
                <x-button type="link" :href="$action" variant="secondary">Reset</x-button>
            @endif
            @if(isset($actions))
                {{ $actions }}
            @endif
        </div>
    </form>
</div>

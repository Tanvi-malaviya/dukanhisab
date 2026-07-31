@props([
    'id' => 'global-loader',
    'type' => 'overlay',
    'text' => 'Processing request...',
    'size' => 'md'
])

@if($type === 'overlay')
    <!-- GLOBAL OVERLAY LOADER COMPONENT -->
    <div id="{{ $id }}" class="fixed inset-0 z-50 pointer-events-none opacity-0 transition-opacity duration-200 flex items-center justify-center p-4">
        <!-- Dimmed Screen Backdrop -->
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        
        <!-- Top Animated Progress Line -->
        <div class="fixed top-0 left-0 right-0 h-1 bg-teal-500 animate-pulse z-50"></div>

        <!-- Center Rounded Pill Loader Card -->
        <div class="bg-white border border-slate-200 shadow-2xl rounded-full px-6 py-3 flex items-center gap-3.5 relative z-50">
            <!-- Spinner Badge -->
            <div class="w-7 h-7 rounded-full bg-teal-50 border border-teal-200 text-teal-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-800 tracking-wide">{{ $text }}</span>
                <span class="text-[11px] text-slate-400 font-medium hidden sm:inline">• Please wait...</span>
            </div>
        </div>
    </div>
@else
    <!-- INLINE SPINNER LOADER COMPONENT -->
    <span id="{{ $id }}" class="inline-flex items-center gap-2 text-teal-600">
        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        @if($text)
            <span class="text-xs font-semibold text-slate-600">{{ $text }}</span>
        @endif
    </span>
@endif

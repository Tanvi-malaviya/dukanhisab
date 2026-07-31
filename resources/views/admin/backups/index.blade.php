@extends('layouts.admin')

@section('title', 'Database Backups')
@section('page_title', 'Database Backup Center')

@section('content')
<div class="space-y-2">
    
    <!-- Actions Area -->
    <div class="flex justify-end">
        <form action="{{ route('admin.backups.create') }}" method="POST">
            @csrf
            <x-button type="submit" variant="primary">Generate Backup Archive</x-button>
        </form>
    </div>

    <!-- Alert details info -->
    <div class="p-4 rounded-xl bg-info/10 border border-info/30 text-info text-xs flex gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
            <span class="font-bold">Automated php-native SQL Dump engine:</span>
            <p class="mt-1 text-slate-300">Creates fully compatible standard SQL tables structure and inserts directly from active database. Restores can be completed in-system instantly.</p>
        </div>
    </div>

    <!-- Backups Table -->
    <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                        <th class="px-6 py-4">Archive Filename</th>
                        <th class="px-6 py-4">Created timestamp</th>
                        <th class="px-6 py-4">Archive Size</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                    @forelse($files as $file)
                    <tr class="hover:bg-secondary/10 transition-colors">
                        <td class="px-6 py-4 font-semibold text-white">
                            {{ $file['filename'] }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-400">
                            {{ $file['created_at'] }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-300">
                            {{ $file['size'] }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2.5">
                                <!-- Download -->
                                <a href="{{ route('admin.backups.download', $file['filename']) }}" download class="px-2.5 py-1 bg-primary text-xs text-slate-300 hover:text-white rounded-lg transition-all" title="Download SQL Dump">
                                    Download
                                </a>

                                <!-- Restore -->
                                <button type="button" 
                                    onclick="confirmAction({
                                        actionUrl: '{{ route('admin.backups.restore', $file['filename']) }}',
                                        title: 'Restore Database',
                                        message: 'Are you sure you want to restore the database from backup: {{ $file['filename'] }}? This will overwrite all current tables and unsaved records.',
                                        buttonText: 'Restore Now',
                                        variant: 'warning',
                                        method: 'POST'
                                    })"
                                    class="px-2.5 py-1 bg-warning/10 hover:bg-warning text-warning hover:text-bg-dark rounded-lg text-xs font-semibold transition-all cursor-pointer">
                                    Restore
                                </button>

                                <!-- Delete -->
                                <button type="button" 
                                    onclick="confirmDelete('{{ route('admin.backups.destroy', $file['filename']) }}', '{{ $file['filename'] }}')"
                                    class="p-1 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-white transition-colors cursor-pointer" 
                                    title="Delete Archive">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state
                            colspan="4"
                            title="No backup archives found"
                            message="No database backup archives have been generated yet."
                        />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

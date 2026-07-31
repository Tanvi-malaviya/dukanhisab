@extends('layouts.admin')

@section('title', 'Security Audit Trails')
@section('page_title', 'Security & Action Audit Logs')

@section('content')
<div class="space-y-2">

    <x-search-filter :action="route('admin.logs.index')" placeholder="Search action, IP address..." :show-reset="false">
        <div class="w-full md:w-48">
            <select name="actor_type" class="block w-full px-3 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-700">
                <option value="">All Actors</option>
                <option value="admin" {{ request('actor_type') === 'admin' ? 'selected' : '' }}>Admin Writes Only</option>
                <option value="user" {{ request('actor_type') === 'user' ? 'selected' : '' }}>User Writes Only</option>
            </select>
        </div>
    </x-search-filter>

    <!-- Logs Table -->
    <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                        <th class="px-6 py-4">Timestamp</th>
                        <th class="px-6 py-4">Actor</th>
                        <th class="px-6 py-4">Action Detail</th>
                        <th class="px-6 py-4">IP Address / Browser</th>
                        <th class="px-6 py-4">Event Payload (JSON)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                    @forelse($logs as $log)
                    <tr class="hover:bg-secondary/10 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs text-slate-500 whitespace-nowrap">
                            {{ $log->created_at->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->admin)
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                    <span class="font-semibold text-white">Admin: {{ $log->admin->name }}</span>
                                </div>
                            @elseif($log->user)
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-info"></span>
                                    <span class="font-medium text-slate-300">User: {{ $log->user->name }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-500 italic">System Engine</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-white max-w-md whitespace-normal">
                            {{ $log->action }}
                        </td>
                        <td class="px-6 py-4 text-xs space-y-0.5 text-slate-400">
                            <p class="font-mono">{{ $log->ip_address }}</p>
                            <p class="text-[10px] text-slate-500 max-w-sm whitespace-normal" title="{{ $log->user_agent }}">{{ $log->user_agent }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->payload)
                                <details class="cursor-pointer text-xs text-primary">
                                    <summary class="font-semibold">View Payload</summary>
                                    <pre class="bg-black/40 p-3 rounded-lg border border-border-dark font-mono text-[10px] text-slate-300 mt-2 overflow-x-auto max-w-md select-text">{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre>
                                </details>
                            @else
                                <span class="text-xs text-slate-500 italic">No Payload</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                        <x-empty-state
                            colspan="5"
                            title="No audit logs recorded"
                            message="No audit trail entries or logs were found for your current filter."
                            resetUrl="{{ route('admin.logs.index') }}"
                        />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :records="$logs" />
    </div>

</div>
@endsection

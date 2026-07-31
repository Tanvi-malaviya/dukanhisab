@extends('layouts.admin')

@section('title', 'Broadcast Center')
@section('page_title', 'Broadcast Center')


@section('content')
    <div class="space-y-2">

        <!-- Main Grid: Broadcaster & Segment Telemetry -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Side: Broadcaster Form -->
            <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden lg:col-span-2">
                <div class="px-6 py-4 border-b border-border-dark bg-secondary/10 font-semibold text-white text-sm">
                    Create Broadcast Campaign
                </div>

                <form action="{{ route('admin.notifications.send') }}" method="POST" class="p-6 space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Notification Title</label>
                        <input type="text" name="title" required placeholder="e.g. Scheduled Maintenance Notice"
                            class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Announcement Message
                            Content</label>
                        <textarea name="message" required rows="4"
                            placeholder="Enter short, informative summary here (Max 1000 characters)..."
                            class="block w-full px-3.5 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white placeholder-slate-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Alert Category</label>
                            <select name="type" required
                                class="block w-full px-3 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                                <option value="promotional">Promotional Broadcast (Emerald)</option>
                                <option value="maintenance">Maintenance alert (Amber/Warning)</option>
                                <option value="new_feature">New Feature Release (Info Blue)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Recipient Target
                                Segment</label>
                            <select name="target" required
                                class="block w-full px-3 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-300">
                                <option value="all">All Platform Users ({{ $userCounts['all'] }} total)</option>
                                <option value="free">Free / Trial tier shops ({{ $userCounts['free'] }} users)</option>
                                <option value="premium">Premium subscription shops ({{ $userCounts['premium'] }} users)
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-border-dark">
                        <x-button type="submit" variant="primary">Dispatch Broadcast</x-button>
                    </div>
                </form>
            </div>

            <!-- Right Side: Segment Telemetry info card -->
            <div class="space-y-5">
                <div class="bg-card-dark border border-border-dark p-6 rounded-2xl">
                    <h3 class="text-sm font-semibold text-white mb-4">Segment Segmentations</h3>

                    <div class="space-y-4 text-xs">
                        <div class="p-3 bg-secondary/20 border border-border-dark rounded-xl">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-white font-medium">All Users</span>
                                <span class="font-mono text-slate-400 font-bold">{{ $userCounts['all'] }}</span>
                            </div>
                            <p class="text-[10px] text-slate-500">Sends notification to every active device and browser
                                session.</p>
                        </div>

                        <div class="p-3 bg-secondary/20 border border-border-dark rounded-xl">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-white font-medium">Free/Trial Tier</span>
                                <span class="font-mono text-slate-400 font-bold">{{ $userCounts['free'] }}</span>
                            </div>
                            <p class="text-[10px] text-slate-500">Perfect target for discount coupon codes and subscription
                                upgrade promos.</p>
                        </div>

                        <div class="p-3 bg-secondary/20 border border-border-dark rounded-xl">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-white font-medium">Premium Members</span>
                                <span class="font-mono text-slate-400 font-bold">{{ $userCounts['premium'] }}</span>
                            </div>
                            <p class="text-[10px] text-slate-500">Notify premium users about database backups, server
                                status, or exclusive features.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sent History ledger -->
        <div class="space-y-2">
            <h2
                class="text-sm font-bold text-slate-800 flex items-center gap-2 bg-slate-100/50 py-1.5 px-3 rounded-lg  border-slate-200/40 w-fit">
                <span class="p-1 rounded-md bg-white text-emerald-600 shadow-sm flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                Broadcast Campaign Log
            </h2>

            <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-secondary/40 border-b border-border-dark text-[10px] font-semibold uppercase text-slate-400 tracking-wider">
                                <th class="px-4 py-2.5">Dispatch timestamp</th>
                                <th class="px-4 py-2.5">Recipient Name</th>
                                <th class="px-4 py-2.5">Campaign Title</th>
                                <th class="px-4 py-2.5">Message payload</th>
                                <th class="px-4 py-2.5 text-right">Category</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark text-sm text-slate-700">
                            @forelse($notifications as $notif)
                                <tr class="hover:bg-secondary/10 transition-colors">
                                    <td class="px-4 py-2 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                        {{ Carbon\Carbon::parse($notif->created_at)->format('Y-m-d ') }}
                                    </td>
                                    <td class="px-4 py-2 text-xs font-bold text-slate-800 whitespace-nowrap">
                                        {{ $notif->recipient_name ?? 'Unknown User' }}
                                    </td>
                                    <td class="px-4 py-2 text-xs font-semibold text-slate-800 whitespace-nowrap">
                                        {{ $notif->data['title'] ?? 'No Title' }}
                                    </td>
                                    <td class="px-4 py-2 text-xs text-slate-500 max-w-sm truncate"
                                        title="{{ $notif->data['message'] ?? '' }}">
                                        {{ $notif->data['message'] ?? '' }}
                                    </td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap flex items-center justify-end gap-2.5">
                                        <span
                                            class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase tracking-wider
                                            {{ ($notif->data['type'] ?? '') === 'maintenance' ? 'bg-warning/20 text-warning' : (($notif->data['type'] ?? '') === 'promotional' ? 'bg-success/20 text-success' : 'bg-info/20 text-info') }}">
                                            {{ $notif->data['type'] ?? 'Broadcast' }}
                                        </span>
                                        <button
                                            onclick="showNotifModal({{ json_encode($notif->data['title'] ?? '') }}, {{ json_encode($notif->data['message'] ?? '') }}, '{{ Carbon\Carbon::parse($notif->created_at)->format('Y-m-d') }}', {{ json_encode($notif->recipient_name ?? 'Unknown User') }}, '{{ $notif->data['type'] ?? 'Broadcast' }}')"
                                            class="p-1 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors cursor-pointer"
                                            title="View Full Message">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state
                                    colspan="5"
                                    title="No broadcasts dispatched"
                                    message="No broadcast campaign notifications were found in history."
                                />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($notifications->hasPages())
                    <div class="px-4 py-3 border-t border-border-dark bg-secondary/5">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Modal for Viewing Full Notification Details -->
    <div id="notif-detail-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeNotifModal()"></div>

        <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden"
            id="modal-container">
            <!-- Header -->
            <div class="px-5 py-3 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Broadcast Details</h3>
                <button onclick="closeNotifModal()" class="text-slate-400 hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Form-style Content -->
            <div class="p-5 space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Recipient Shop
                            Owner</label>
                        <input type="text" id="modal-recipient" readonly
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:outline-none rounded-xl text-sm text-white cursor-default select-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Dispatch Date</label>
                        <input type="text" id="modal-date" readonly
                            class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:outline-none rounded-xl text-sm text-white font-mono cursor-default select-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Campaign Title</label>
                    <input type="text" id="modal-title" readonly
                        class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:outline-none rounded-xl text-sm text-white cursor-default select-all">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Alert Category</label>
                    <div class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark rounded-xl text-sm">
                        <span id="modal-type-badge"
                            class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Message Content</label>
                    <textarea id="modal-message" readonly rows="4"
                        class="block w-full px-3 py-1.5 bg-secondary/40 border border-border-dark focus:outline-none rounded-xl text-sm text-white cursor-default select-all resize-none"></textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-3 border-t border-border-dark flex justify-end gap-3 bg-secondary/5">
                <x-button type="button" onclick="closeNotifModal()" variant="primary">Close View</x-button>
            </div>
        </div>
    </div>

    <script>
        function showNotifModal(title, message, date, recipient, type) {
            document.getElementById('modal-title').value = title;
            document.getElementById('modal-message').value = message;
            document.getElementById('modal-date').value = date;
            document.getElementById('modal-recipient').value = recipient;

            const badge = document.getElementById('modal-type-badge');
            badge.innerText = type;
            badge.className = 'inline-flex px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider ';

            if (type === 'maintenance') {
                badge.classList.add('bg-warning/20', 'text-warning');
            } else if (type === 'promotional') {
                badge.classList.add('bg-success/20', 'text-success');
            } else {
                badge.classList.add('bg-info/20', 'text-info');
            }

            document.getElementById('notif-detail-modal').classList.remove('hidden');
        }

        function closeNotifModal() {
            document.getElementById('notif-detail-modal').classList.add('hidden');
        }
    </script>
@endsection
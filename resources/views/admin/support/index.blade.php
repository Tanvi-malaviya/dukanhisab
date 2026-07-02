@extends('layouts.admin')

@section('title', 'Support Tickets')
@section('page_title', 'Support & Customer Helpdesk')

@section('content')
    <div class="space-y-2">

        <x-search-filter :action="route('admin.support.index')" placeholder="Search by subject, message, or user name...">
            <div class="w-full md:w-44 shrink-0" style="flex-shrink: 0; width: 176px;">
                <input type="date" name="date" value="{{ request('date') }}" class="block w-full px-3.5 py-2 bg-white border border-slate-200 focus:border-primary focus:outline-none rounded-xl text-sm text-slate-700 cursor-pointer">
            </div>
            <div class="w-full md:w-40 shrink-0" style="flex-shrink: 0; width: 160px;">
                <select name="status"
                    class="block w-full px-3 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-700">
                    <option value="">All Statu  ses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="inProgress" {{ request('status') === 'inProgress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
        </x-search-filter>

        <!-- Tickets Table -->
        <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                            <th class="px-6 py-4">SR. No</th>
                            <th class="px-6 py-4">Submitted By</th>
                            <th class="px-6 py-4">Subject</th>
                            <th class="px-6 py-4"> Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                        @forelse($tickets as $ticket)
                                        <tr class="hover:bg-secondary/10 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <p class="font-semibold text-white">#{{ $ticket->id }}</p>
                                                    <p class="text-xs text-slate-500 font-mono">{{ $ticket->created_at->format('Y-m-d') }}
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="font-medium text-white">{{ $ticket->user->name }}</p>
                                                <p class="text-xs text-slate-500">{{ $ticket->user->email }}</p>
                                            </td>
                                            <td class="px-6 py-4 max-w-md whitespace-normal">
                                                <p class="font-semibold text-white">{{ $ticket->subject }}</p>
                                                <p class="text-xs text-slate-400 mt-1 whitespace-pre-line">{{ $ticket->message }}</p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold 
                                                    {{ $ticket->status === 'open' ? 'bg-danger/10 text-danger border border-danger/20' :
                            ($ticket->status === 'resolved' ? 'bg-success/10 text-success border border-success/20' :
                                ($ticket->status === 'inProgress' ? 'bg-warning/10 text-warning border border-warning/20' :
                                    'bg-slate-100 text-slate-600 border border-slate-200')) }}">
                                                    <span class="w-1.5 h-1.5 rounded-full 
                                                        {{ $ticket->status === 'open' ? 'bg-danger' :
                            ($ticket->status === 'resolved' ? 'bg-success' :
                                ($ticket->status === 'inProgress' ? 'bg-warning' :
                                    'bg-slate-400')) }}"></span>
                                                    {{ $ticket->status === 'inProgress' ? 'In Progress' : ($ticket->status === 'pending' ? 'Pending' : ucfirst($ticket->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                                <div class="flex items-center justify-end gap-2.5">
                                                    <!-- Reply Action -->
                                                    <button onclick='openReplyModal(@json($ticket))'
                                                        class="px-3.5 py-1.5 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer shadow-sm hover:shadow">
                                                        {{ ($ticket->status === 'resolved' || $ticket->status === 'closed') ? 'View Reply' : 'Reply' }}
                                                    </button>

                                                    <!-- Status Change toggles -->
                                                    <div class="relative inline-block text-left">
                                                        <select onchange="updateTicketStatus(this, {{ $ticket->id }})"
                                                            class="w-32 px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 shadow-sm hover:border-slate-300 focus:outline-none transition-colors cursor-pointer">
                                                            <option value="">Set Status</option>
                                                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open
                                                            </option>
                                                            <option value="inProgress" {{ $ticket->status === 'inProgress' ? 'selected' : '' }}>In Progress</option>
                                                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>
                                                                Resolved</option>
                                                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">No support tickets found
                                    matching filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :records="$tickets" />
        </div>

    </div>

    <!-- Modal: Reply Support Ticket Form -->
    <div id="replyModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeReplyModal()"></div>
        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white" id="replyModalTitle">Helpdesk Ticket Reply</h3>
                <button onclick="closeReplyModal()" class="text-slate-400 hover:text-white"><svg class="w-5 h-5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button>
            </div>

            <div class="p-6 space-y-4">
                <!-- Ticket Info -->
                <div class="p-4 bg-secondary/25 border border-border-dark rounded-xl space-y-2">
                    <p class="text-xs font-semibold text-white">Subject: <span class="font-normal text-slate-300"
                            id="ticket_subject">Subject</span></p>
                    <div class="text-xs text-slate-400 border-t border-border-dark/60 pt-2">
                        <p class="font-medium text-white mb-1">Message:</p>
                        <p id="ticket_message" class="italic">Message content</p>
                    </div>
                </div>

                <!-- Existing Reply block if resolved -->
                <div id="existing_reply_section" class="hidden p-4 bg-success/5 border border-success/20 rounded-xl">
                    <p class="text-xs font-semibold text-success mb-1">Admin Reply Sent:</p>
                    <p id="ticket_existing_reply" class="text-xs text-slate-300 italic">Reply message</p>
                    <p id="ticket_reply_date" class="text-[10px] text-slate-500 mt-2">Sent date</p>
                </div>

                <!-- Submit Reply Form -->
                <form id="replyForm" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Write Reply</label>
                        <textarea name="admin_reply" id="admin_reply_field" required rows="4"
                            placeholder="Enter resolution details, update message, or notes to send to user..."
                            class="block w-full px-3 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white placeholder-slate-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2 border-t border-border-dark" id="modalButtons">
                        <x-button type="button" onclick="closeReplyModal()" variant="secondary">Cancel</x-button>
                        <x-button type="submit" variant="primary">Submit Reply</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden status update form -->
    <form id="status-update-form" method="POST" class="hidden">
        @csrf
    </form>

    <script>
        function openReplyModal(ticket) {
          document.getElementById('replyForm').action = "{{ route('admin.support.reply', ['id' => ':id']) }}".replace(':id', ticket.id);
            document.getElementById('ticket_subject').innerText = ticket.subject;
            document.getElementById('ticket_message').innerText = ticket.message;

            if (ticket.status === 'resolved' || ticket.status === 'closed') {
                document.getElementById('existing_reply_section').classList.remove('hidden');
                document.getElementById('ticket_existing_reply').innerText = ticket.admin_reply || 'No reply sent.';
                document.getElementById('ticket_reply_date').innerText = ticket.replied_at ? "Replied on: " + ticket.replied_at : "";

                // Hide the reply form inputs/buttons as it is already answered
                document.getElementById('replyForm').classList.add('hidden');
            } else {
                document.getElementById('existing_reply_section').classList.add('hidden');
                document.getElementById('replyForm').classList.remove('hidden');
                document.getElementById('admin_reply_field').value = '';
            }

            document.getElementById('replyModal').classList.remove('hidden');
        }

        function closeReplyModal() {
            document.getElementById('replyModal').classList.add('hidden');
        }

        function updateTicketStatus(selectElement, ticketId) {
            const status = selectElement.value;
            if (!status) return;

            const form = document.getElementById('status-update-form');
             form.action = "{{ route('admin.support.status', ['id' => ':id', 'status' => ':status']) }}".replace(':id', ticketId).replace(':status', status);
            form.submit();
        }
    </script>
@endsection
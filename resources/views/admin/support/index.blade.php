@extends('layouts.admin')

@section('title', 'Support Tickets')
@section('page_title', 'Support & Customer Helpdesk')

@section('content')
    <div class="space-y-4">

        {{-- Top KPI Metrics Cards --}}
        @if(isset($stats))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                <!-- Total Tickets -->
                <div class="bg-white border border-slate-200 p-4.5 rounded-2xl flex items-center justify-between shadow-xs">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Tickets</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ number_format($stats['total']) }}</h3>
                        <span class="inline-flex items-center text-[10px] text-slate-600 font-semibold bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-md">
                            All Inquiries
                        </span>
                    </div>
                    <span class="p-3 rounded-2xl bg-slate-50 border border-slate-200 shadow-2xs text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </span>
                </div>

                <!-- Open Issues -->
                <div class="bg-white border border-slate-200 p-4.5 rounded-2xl flex items-center justify-between shadow-xs">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Open Tickets</p>
                        <h3 class="text-2xl font-black text-rose-600">{{ number_format($stats['open']) }}</h3>
                        <span class="inline-flex items-center gap-1 text-[10px] text-rose-700 font-bold bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-md">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            Needs Action
                        </span>
                    </div>
                    <span class="p-3 rounded-2xl bg-rose-50 border border-rose-200 shadow-2xs text-rose-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </span>
                </div>

                <!-- In Progress -->
                <div class="bg-white border border-slate-200 p-4.5 rounded-2xl flex items-center justify-between shadow-xs">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">In Progress</p>
                        <h3 class="text-2xl font-black text-amber-600">{{ number_format($stats['in_progress']) }}</h3>
                        <span class="inline-flex items-center text-[10px] text-amber-700 font-bold bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                            Active Threads
                        </span>
                    </div>
                    <span class="p-3 rounded-2xl bg-amber-50 border border-amber-200 shadow-2xs text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>

                <!-- Resolved -->
                <div class="bg-white border border-slate-200 p-4.5 rounded-2xl flex items-center justify-between shadow-xs">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Resolved</p>
                        <h3 class="text-2xl font-black text-emerald-600">{{ number_format($stats['resolved']) }}</h3>
                        <span class="inline-flex items-center text-[10px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                            Solved & Closed
                        </span>
                    </div>
                    <span class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-2xs text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>
            </div>
        @endif

        {{-- Search & Filters Bar --}}
        <x-search-filter :action="route('admin.support.index')" placeholder="Search by subject, message, or user name...">
            <div class="w-full md:w-44 shrink-0">
                <input type="date" name="date" value="{{ request('date') }}" onclick="this.showPicker()"
                    class="block w-full px-3.5 py-2 bg-white border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none rounded-xl text-sm text-slate-700 cursor-pointer shadow-2xs">
            </div>
            <div class="w-full md:w-44 shrink-0">
                <select name="status"
                    class="block w-full px-3.5 py-2 bg-white border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none rounded-xl text-sm text-slate-700 cursor-pointer shadow-2xs">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="inProgress" {{ request('status') === 'inProgress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
        </x-search-filter>

        <!-- Tickets Table Container -->
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                            <th class="px-4 py-3 w-24 whitespace-nowrap">Ticket ID</th>
                            <th class="px-4 py-3 w-52 whitespace-nowrap">Submitted By</th>
                            <th class="px-4 py-3">Subject & Message</th>
                            <th class="px-4 py-3 w-32 whitespace-nowrap text-center">Status</th>
                            <th class="px-4 py-3 w-48 whitespace-nowrap text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                {{-- Ticket ID & Date --}}
                                <td class="px-4 py-3 whitespace-nowrap align-top">
                                    <span class="font-extrabold text-slate-800 text-xs">#{{ $ticket->id }}</span>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $ticket->created_at->timezone('Asia/Kolkata')->format('d M Y') }}</p>
                                </td>

                                {{-- User Details --}}
                                <td class="px-4 py-3 align-top whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-[11px] uppercase shrink-0">
                                            {{ substr($ticket->user?->name ?: 'US', 0, 2) }}
                                        </span>
                                        <div class="min-w-0 max-w-[170px]">
                                            <p class="font-bold text-slate-800 text-xs truncate" title="{{ $ticket->user?->name ?: 'Unknown User' }}">
                                                {{ $ticket->user?->name ?: 'Unknown User' }}
                                            </p>
                                            <p class="text-[11px] text-slate-400 truncate" title="{{ $ticket->user?->email }}">
                                                {{ $ticket->user?->email ?: 'No Email' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Subject & Message & Admin Reply (Compact) --}}
                                <td class="px-4 py-3 align-top">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-800 text-xs truncate max-w-sm" title="{{ $ticket->subject }}">
                                                {{ $ticket->subject }}
                                            </span>
                                            @if($ticket->screenshot)
                                                <a href="{{ asset('storage/' . $ticket->screenshot) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 text-[11px] text-primary hover:underline font-semibold shrink-0" title="View attached screenshot">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    <span>Attachment</span>
                                                </a>
                                            @endif
                                        </div>

                                        <p class="text-xs text-slate-500 line-clamp-1 max-w-lg leading-relaxed" title="{{ $ticket->message }}">
                                            {{ $ticket->message }}
                                        </p>

                                        {{-- Inline Conversation State / Admin Reply snippet or waiting indicator --}}
                                        @php
                                            $lastMessage = $ticket->messages->last();
                                            $totalReplies = $ticket->messages->count();
                                        @endphp

                                        @if($lastMessage && $lastMessage->sender_type === 'user')
                                            <div class="pt-0.5 flex items-center gap-1.5 flex-wrap">
                                                <div class="inline-flex items-center gap-1.5 text-xs text-blue-900 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-lg max-w-lg" title="User follow-up: {{ $lastMessage->message }}">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse shrink-0"></span>
                                                    <span class="font-bold text-[11px] shrink-0">Customer Reply:</span>
                                                    <span class="truncate text-[11px] text-slate-700 font-normal">{{ $lastMessage->message }}</span>
                                                    <span class="text-[10px] text-blue-600 font-mono shrink-0 ml-1">{{ $lastMessage->created_at->timezone('Asia/Kolkata')->format('d M, h:i A') }}</span>
                                                </div>
                                                @if($totalReplies > 1)
                                                    <span class="text-[10px] text-slate-400 font-semibold font-mono">({{ $totalReplies }} msgs)</span>
                                                @endif
                                            </div>
                                        @elseif($lastMessage && $lastMessage->sender_type === 'admin')
                                            <div class="pt-0.5 flex items-center gap-1.5 flex-wrap">
                                                <div class="inline-flex items-center gap-1.5 text-xs text-emerald-800 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-lg max-w-lg" title="Admin Reply: {{ $lastMessage->message }}">
                                                    <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <span class="font-bold text-[11px] shrink-0">Replied:</span>
                                                    <span class="truncate text-[11px] text-slate-700 font-normal">{{ $lastMessage->message }}</span>
                                                    <span class="text-[10px] text-emerald-600 font-mono shrink-0 ml-1">{{ $lastMessage->created_at->timezone('Asia/Kolkata')->format('d M, h:i A') }}</span>
                                                </div>
                                                @if($totalReplies > 1)
                                                    <span class="text-[10px] text-slate-400 font-semibold font-mono">({{ $totalReplies }} msgs)</span>
                                                @endif
                                            </div>
                                        @elseif($ticket->admin_reply)
                                            <div class="pt-0.5">
                                                <div class="inline-flex items-center gap-1.5 text-xs text-emerald-800 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-lg max-w-lg" title="Admin Reply: {{ $ticket->admin_reply }}">
                                                    <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <span class="font-bold text-[11px] shrink-0">Replied:</span>
                                                    <span class="truncate text-[11px] text-slate-700 font-normal">{{ $ticket->admin_reply }}</span>
                                                    @if($ticket->replied_at)
                                                        <span class="text-[10px] text-emerald-600 font-mono shrink-0 ml-1">{{ $ticket->replied_at->timezone('Asia/Kolkata')->format('d M, h:i A') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="pt-0.5">
                                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-200/80 px-2 py-0.5 rounded-md">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                    <span>Waiting for reply</span>
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status Column --}}
                                <td class="px-4 py-3 whitespace-nowrap text-center align-top">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider
                                        {{ $ticket->status === 'open' ? 'bg-rose-50 text-rose-700 border border-rose-200' :
                                            ($ticket->status === 'resolved' || $ticket->status === 'closed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                                'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                        <span class="w-1.5 h-1.5 rounded-full 
                                            {{ $ticket->status === 'open' ? 'bg-rose-500' :
                                                ($ticket->status === 'resolved' || $ticket->status === 'closed' ? 'bg-emerald-500' : 'bg-amber-500') }}"></span>
                                        {{ $ticket->status === 'inProgress' ? 'In Progress' : ($ticket->status === 'pending' ? 'Pending' : ucfirst($ticket->status)) }}
                                    </span>
                                </td>

                                {{-- Actions Column --}}
                                <td class="px-4 py-3 text-right whitespace-nowrap align-top">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Reply Action Button -->
                                        <button onclick='openReplyModal(@json($ticket))'
                                            class="px-2.5 py-1 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer shadow-2xs flex items-center gap-1
                                                {{ $ticket->admin_reply
                                                    ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300'
                                                    : 'bg-primary hover:bg-primary-hover text-white' }}">
                                            @if($ticket->admin_reply)
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                <span>View / Edit</span>
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                </svg>
                                                <span>Reply</span>
                                            @endif
                                        </button>

                                        <!-- Status Change Dropdown -->
                                        <select onchange="updateTicketStatus(this, {{ $ticket->id }})"
                                            class="w-28 px-2 py-1 bg-white border border-slate-200 hover:border-slate-300 rounded-xl text-xs font-semibold text-slate-700 shadow-2xs focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-colors cursor-pointer">
                                            <option value="">Set Status</option>
                                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                            <option value="inProgress" {{ $ticket->status === 'inProgress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state
                                colspan="5"
                                title="No support tickets found"
                                message="We couldn't find any helpdesk support tickets matching your search or status filter."
                                resetUrl="{{ route('admin.support.index') }}"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :records="$tickets" />
        </div>

    </div>

    <!-- Modal: Reply & Conversation Thread -->
    <div id="replyModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeReplyModal()"></div>
        <div class="bg-white border border-slate-200 rounded-3xl w-full max-w-2xl shadow-2xl relative z-10 overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/90 shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-primary/10 text-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-extrabold text-slate-800" id="replyModalTitle">Helpdesk Ticket</h3>
                            <span id="modal_status_badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"></span>
                        </div>
                        <p class="text-[11px] text-slate-400" id="modal_user_info"></p>
                    </div>
                </div>
                <button onclick="closeReplyModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Modal Body: Conversation History + Form --}}
            <div class="p-4 space-y-2.5 overflow-y-auto flex-1">
                {{-- Subject Banner --}}
                <div class="pb-2 border-b border-slate-100">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Subject</span>
                    <h4 class="text-sm font-extrabold text-slate-800 mt-0.5" id="ticket_subject"></h4>
                </div>

                {{-- Thread Container --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Conversation Thread</label>
                    <div id="conversation_thread" class="space-y-2">
                        <!-- Populated by JS -->
                    </div>
                </div>

                {{-- Submit Reply Form --}}
                <form id="replyForm" method="POST" enctype="multipart/form-data" class="mt-2 pt-2.5 border-t border-slate-200 space-y-2">
                    @csrf
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Send Response / Reply</label>
                            <span class="text-[10px] text-slate-400">User will see this in their app/portal & receive email</span>
                        </div>
                        <textarea name="admin_reply" id="admin_reply_field" required rows="3"
                            placeholder="Type your reply, resolution instructions, or update..."
                            class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none rounded-xl text-xs text-slate-800 placeholder-slate-400 transition-all"></textarea>
                    </div>

                    <!-- Admin Attachment (PDF, Screenshots, Images, Docs) -->
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold cursor-pointer transition-colors border border-slate-200">
                            <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            <span>Attach PDF / Screenshot / File</span>
                            <input type="file" name="attachment" id="admin_attachment_input" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip" class="hidden" onchange="handleAdminFileChange(this)">
                        </label>
                    <div id="admin_file_preview" class="hidden items-center gap-2 text-xs">
                        <img id="admin_img_preview" src="" class="hidden h-12 w-auto rounded-lg border border-slate-200 object-cover shadow-sm">
                        <div id="admin_pdf_preview" class="hidden items-center gap-1.5 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-lg">
                            <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                            <span id="admin_file_name" class="truncate max-w-[160px] font-medium text-rose-700"></span>
                        </div>
                        <button type="button" onclick="clearAdminFile()" class="text-slate-400 hover:text-rose-600 font-bold text-sm cursor-pointer">&times;</button>
                    </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-slate-600 whitespace-nowrap">Status:</label>
                            <select name="status" id="reply_status_field"
                                class="px-3 py-1.5 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none rounded-xl text-xs font-semibold text-slate-700 cursor-pointer">
                                <option value="inProgress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                                <option value="open">Open</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" onclick="closeReplyModal()"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" id="submitReplyBtn"
                                class="px-5 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl transition-all shadow-xs cursor-pointer flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                <span>Send Reply</span>
                            </button>
                        </div>
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
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, m => map[m]);
        }

        function formatDateTime(dtStr) {
            if (!dtStr) return '';
            try {
                const d = new Date(dtStr);
                if (isNaN(d.getTime())) return dtStr;
                const now = new Date();
                const isToday = d.toDateString() === now.toDateString();
                const timeStr = d.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true });
                if (isToday) {
                    return 'Today, ' + timeStr;
                }
                const dateStr = d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' });
                return dateStr + ', ' + timeStr;
            } catch (e) {
                return dtStr.substring(0, 16);
            }
        }

        function handleAdminFileChange(input) {
            const file = input.files && input.files[0];
            const preview = document.getElementById('admin_file_preview');
            const imgEl = document.getElementById('admin_img_preview');
            const pdfEl = document.getElementById('admin_pdf_preview');
            const nameEl = document.getElementById('admin_file_name');
            if (file) {
                preview.classList.remove('hidden');
                preview.classList.add('flex');
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => { imgEl.src = e.target.result; };
                    reader.readAsDataURL(file);
                    imgEl.classList.remove('hidden');
                    pdfEl.classList.add('hidden');
                    pdfEl.classList.remove('flex');
                } else {
                    nameEl.innerText = file.name;
                    pdfEl.classList.remove('hidden');
                    pdfEl.classList.add('flex');
                    imgEl.classList.add('hidden');
                    imgEl.src = '';
                }
            } else {
                preview.classList.add('hidden');
                preview.classList.remove('flex');
            }
        }

        function clearAdminFile() {
            const input = document.getElementById('admin_attachment_input');
            if (input) input.value = '';
            const preview = document.getElementById('admin_file_preview');
            const imgEl = document.getElementById('admin_img_preview');
            const pdfEl = document.getElementById('admin_pdf_preview');
            if (preview) { preview.classList.add('hidden'); preview.classList.remove('flex'); }
            if (imgEl) { imgEl.classList.add('hidden'); imgEl.src = ''; }
            if (pdfEl) { pdfEl.classList.add('hidden'); pdfEl.classList.remove('flex'); }
        }

        function openReplyModal(ticket) {
            document.getElementById('replyForm').action = "{{ route('admin.support.reply', ['id' => ':id']) }}".replace(':id', ticket.id);
            document.getElementById('replyModalTitle').innerText = "Ticket #" + ticket.id;
            document.getElementById('ticket_subject').innerText = ticket.subject || 'No Subject';

            const userInfo = (ticket.user ? ticket.user.name : 'Unknown User') + 
                             (ticket.user && ticket.user.email ? ' (' + ticket.user.email + ')' : '');
            document.getElementById('modal_user_info').innerText = userInfo;

            // Status badge
            const statusBadge = document.getElementById('modal_status_badge');
            const status = ticket.status || 'open';
            statusBadge.innerText = status;
            if (status === 'open') {
                statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-700';
            } else if (['inProgress', 'pending'].includes(status)) {
                statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-700';
            } else {
                statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-700';
            }

            // Reset admin file input
            clearAdminFile();

            // Build Conversation Thread
            const threadEl = document.getElementById('conversation_thread');
            threadEl.innerHTML = '';

            // 1. Initial User Query
            let initialHtml = `
                <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-800 flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[10px] font-bold">C</span>
                            <span>Customer: ${escapeHtml(ticket.user ? ticket.user.name : 'Customer')} (Initial Query)</span>
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono">${formatDateTime(ticket.created_at)}</span>
                    </div>
                    <p class="text-xs text-slate-700 whitespace-pre-line leading-relaxed">${escapeHtml(ticket.message || '')}</p>
            `;
                        if (ticket.screenshot) {
                            const isImg = !ticket.screenshot.toLowerCase().endsWith('.pdf');
                            initialHtml += `
                                <div class="pt-1.5 border-t border-slate-200">
                                    ${isImg
                                        ? `<a href="/storage/${ticket.screenshot}" target="_blank" class="inline-block"><img src="/storage/${ticket.screenshot}" class="h-14 w-auto rounded-lg border border-slate-200 object-cover shadow-sm hover:opacity-90 transition-opacity cursor-zoom-in" title="Click to view full image"></a>`
                                        : `<a href="/storage/${ticket.screenshot}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-primary hover:underline font-semibold bg-white border border-slate-200 px-2.5 py-1 rounded-lg"><svg class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg> <span>View Attached PDF</span></a>`
                                    }
                                </div>
                            `;
                        }
            initialHtml += `</div>`;
            threadEl.innerHTML += initialHtml;

            // 2. Thread messages
            const messages = ticket.messages || [];
            if (messages.length > 0) {
                messages.forEach(msg => {
                    if (msg.sender_type === 'admin') {
                        let adminMsgHtml = `
                            <div class="p-2.5 bg-emerald-50/80 border border-emerald-200/90 rounded-xl space-y-1 ml-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-emerald-800 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Support Desk (${escapeHtml(msg.sender_name || 'Admin')})</span>
                                    </span>
                                    <span class="text-[10px] text-emerald-700/80 font-mono">${formatDateTime(msg.created_at)}</span>
                                </div>
                                <p class="text-xs text-slate-700 whitespace-pre-line leading-relaxed font-normal">${escapeHtml(msg.message)}</p>
                        `;
                        if (msg.attachment) {
                            const isAdminImg = !msg.attachment.toLowerCase().endsWith('.pdf');
                            adminMsgHtml += `
                                <div class="pt-1.5 border-t border-emerald-200/60">
                                    ${isAdminImg
                                        ? `<a href="/storage/${msg.attachment}" target="_blank" class="inline-block"><img src="/storage/${msg.attachment}" class="h-14 w-auto rounded-lg border border-emerald-200 object-cover shadow-sm hover:opacity-90 transition-opacity cursor-zoom-in" title="Click to view full image"></a>`
                                        : `<a href="/storage/${msg.attachment}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-emerald-800 hover:underline font-semibold bg-white/80 border border-emerald-200 px-2.5 py-1 rounded-lg"><svg class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg> <span>View Attached PDF</span></a>`
                                    }
                                </div>
                            `;
                        }
                        adminMsgHtml += `</div>`;
                        threadEl.innerHTML += adminMsgHtml;
                    } else {
                        let userMsgHtml = `
                            <div class="p-2.5 bg-blue-50/70 border border-blue-200/80 rounded-xl space-y-1 mr-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-blue-800 flex items-center gap-1.5">
                                        <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">C</span>
                                        <span>Customer Follow-up</span>
                                    </span>
                                    <span class="text-[10px] text-blue-600 font-mono">${formatDateTime(msg.created_at)}</span>
                                </div>
                                <p class="text-xs text-slate-700 whitespace-pre-line leading-relaxed font-normal">${escapeHtml(msg.message)}</p>
                        `;
                        if (msg.attachment) {
                            const isUserImg = !msg.attachment.toLowerCase().endsWith('.pdf');
                            userMsgHtml += `
                                <div class="pt-1.5 border-t border-blue-200/60">
                                    ${isUserImg
                                        ? `<a href="/storage/${msg.attachment}" target="_blank" class="inline-block"><img src="/storage/${msg.attachment}" class="h-14 w-auto rounded-lg border border-blue-200 object-cover shadow-sm hover:opacity-90 transition-opacity cursor-zoom-in" title="Click to view full image"></a>`
                                        : `<a href="/storage/${msg.attachment}" target="_blank" class="inline-flex items-center gap-1 text-xs text-primary hover:underline font-semibold bg-white/80 border border-blue-200 px-2.5 py-1 rounded-lg"><svg class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg> <span>View Attached PDF</span></a>`
                                    }
                                </div>
                            `;
                        }
                        userMsgHtml += `</div>`;
                        threadEl.innerHTML += userMsgHtml;
                    }
                });
            } else if (ticket.admin_reply && ticket.admin_reply.trim() !== '') {
                // Legacy reply fallback
                threadEl.innerHTML += `
                    <div class="p-3.5 bg-emerald-50/80 border border-emerald-200/90 rounded-2xl space-y-1.5 ml-4">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-800 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Support Desk (Admin Reply)</span>
                            </span>
                            <span class="text-[10px] text-emerald-700/80 font-mono">${formatDateTime(ticket.replied_at)}</span>
                        </div>
                        <p class="text-xs text-slate-700 whitespace-pre-line leading-relaxed font-normal">${escapeHtml(ticket.admin_reply)}</p>
                    </div>
                `;
            }

            // Form defaults
            document.getElementById('admin_reply_field').value = '';
            const statusField = document.getElementById('reply_status_field');
            if (statusField) {
                statusField.value = (ticket.status === 'open' || !ticket.status) ? 'inProgress' : ticket.status;
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
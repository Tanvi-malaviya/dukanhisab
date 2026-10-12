@extends('layouts.admin')

@section('title', 'Support Tickets')
@section('page_title', 'Support & Customer Helpdesk')

@section('content')
    <div class="space-y-4">

        {{-- Top KPI Metrics Cards --}}
        @if(isset($stats))
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <!-- Total Tickets -->
                <a href="{{ route('admin.support.index', ['status' => 'all']) }}"
                    class="bg-white border border-slate-200 p-4.5 rounded-2xl flex items-center justify-between shadow-xs hover:border-slate-300 transition-colors">
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
                </a>

                <!-- Open Issues (Default) -->
                <a href="{{ route('admin.support.index', ['status' => 'open']) }}"
                    class="bg-white border {{ ($selectedStatus ?? 'open') === 'open' ? 'border-rose-400 ring-1 ring-rose-200' : 'border-slate-200' }} p-4.5 rounded-2xl flex items-center justify-between shadow-xs hover:border-rose-300 transition-colors">
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
                </a>

                <!-- Closed Tickets -->
                <a href="{{ route('admin.support.index', ['status' => 'closed']) }}"
                    class="bg-white border {{ ($selectedStatus ?? '') === 'closed' ? 'border-emerald-400 ring-1 ring-emerald-200' : 'border-slate-200' }} p-4.5 rounded-2xl flex items-center justify-between shadow-xs hover:border-emerald-300 transition-colors">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Closed Tickets</p>
                        <h3 class="text-2xl font-black text-emerald-600">{{ number_format($stats['closed']) }}</h3>
                        <span class="inline-flex items-center text-[10px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                            Solved & Closed
                        </span>
                    </div>
                    <span class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-2xs text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </a>
            </div>
        @endif

        {{-- Search & Filters Bar --}}
        <x-search-filter :action="route('admin.support.index')" placeholder="Search by subject, message, or user name..." :showApply="true" :showReset="true">
            <div class="w-full md:w-44 shrink-0">
                <input type="date" name="date" value="{{ request('date') }}" onclick="this.showPicker()" onchange="this.form.submit()"
                    class="block w-full px-3.5 py-2 bg-white border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none rounded-xl text-sm text-slate-700 cursor-pointer shadow-2xs">
            </div>
            <div class="w-full md:w-44 shrink-0">
                <select name="status" onchange="this.form.submit()"
                    class="block w-full px-3.5 py-2 bg-white border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none rounded-xl text-sm text-slate-700 cursor-pointer shadow-2xs font-medium">
                    <option value="open" {{ ($selectedStatus ?? request('status', 'open')) === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ ($selectedStatus ?? request('status')) === 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="all" {{ ($selectedStatus ?? request('status')) === 'all' ? 'selected' : '' }}>All Statuses</option>
                </select>
            </div>
        </x-search-filter>

        <!-- Tickets Table Container -->
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                            <th class="px-3 py-2.5 w-20 whitespace-nowrap">Ticket ID</th>
                            <th class="px-3 py-2.5 w-44 whitespace-nowrap">Submitted By</th>
                            <th class="px-3 py-2.5">Subject & Message</th>
                            <th class="px-3 py-2.5 w-24 whitespace-nowrap text-center">Status</th>
                            <th class="px-3 py-2.5 w-36 whitespace-nowrap text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                {{-- Ticket ID & Date --}}
                                <td class="px-3 py-2.5 whitespace-nowrap align-top">
                                    <span class="font-extrabold text-slate-800 text-xs">#{{ $ticket->id }}</span>
                                    <p class="text-[10px] text-slate-400 font-mono">{{ $ticket->created_at->timezone('Asia/Kolkata')->format('d M Y') }}</p>
                                </td>

                                {{-- User Details --}}
                                <td class="px-3 py-2.5 align-top whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-6 h-6 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-[10px] uppercase shrink-0">
                                            {{ substr($ticket->user?->name ?: 'US', 0, 2) }}
                                        </span>
                                        <div class="min-w-0 max-w-[130px]">
                                            <p class="font-bold text-slate-800 text-xs truncate" title="{{ $ticket->user?->name ?: 'Unknown User' }}">
                                                {{ $ticket->user?->name ?: 'Unknown User' }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 truncate" title="{{ $ticket->user?->email }}">
                                                {{ $ticket->user?->email ?: 'No Email' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Subject & Message & Admin Reply (Compact) --}}
                                <td class="px-3 py-2.5 align-top">
                                    <div class="space-y-0.5 min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-slate-800 text-xs truncate max-w-[200px] sm:max-w-xs" title="{{ $ticket->subject }}">
                                                {{ $ticket->subject }}
                                            </span>
                                            @if($ticket->screenshot)
                                                <a href="{{ asset('storage/' . $ticket->screenshot) }}" target="_blank"
                                                    class="inline-flex items-center gap-0.5 text-[10px] text-primary hover:underline font-semibold shrink-0" title="View attached file">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    <span>Attachment</span>
                                                </a>
                                            @endif
                                        </div>

                                        <p class="text-[11px] text-slate-500 truncate max-w-[220px] sm:max-w-md leading-relaxed" title="{{ $ticket->message }}">
                                            {{ $ticket->message }}
                                        </p>

                                        {{-- Inline Conversation State / Admin Reply snippet or waiting indicator --}}
                                        @php
                                            $lastMessage = $ticket->messages->last();
                                            $totalReplies = $ticket->messages->count();
                                        @endphp

                                        @if($lastMessage && $lastMessage->sender_type === 'user')
                                            <div class="pt-0.5 flex items-center gap-1.5">
                                                <div class="inline-flex items-center gap-1 text-[11px] text-slate-800 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg max-w-[280px]" title="Customer Reply: {{ $lastMessage->message }}">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500 animate-pulse shrink-0"></span>
                                                    <span class="font-bold shrink-0">Customer:</span>
                                                    <span class="truncate max-w-[120px] text-slate-700 font-normal">{{ $lastMessage->message }}</span>
                                                    <span class="text-[9px] text-slate-500 font-mono shrink-0 ml-0.5">{{ $lastMessage->created_at->timezone('Asia/Kolkata')->format('d M, h:i A') }}</span>
                                                </div>
                                                @if($totalReplies > 1)
                                                    <span class="text-[9px] text-slate-400 font-semibold font-mono">({{ $totalReplies }})</span>
                                                @endif
                                            </div>
                                        @elseif($lastMessage && $lastMessage->sender_type === 'admin')
                                            <div class="pt-0.5 flex items-center gap-1.5">
                                                <div class="inline-flex items-center gap-1 text-[11px] text-primary bg-primary/10 border border-primary/20 px-2 py-0.5 rounded-lg max-w-[280px]" title="Admin Reply: {{ $lastMessage->message }}">
                                                    <svg class="w-3 h-3 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <span class="font-bold shrink-0">Replied:</span>
                                                    <span class="truncate max-w-[120px] text-slate-700 font-normal">{{ $lastMessage->message }}</span>
                                                    <span class="text-[9px] text-primary font-mono shrink-0 ml-0.5">{{ $lastMessage->created_at->timezone('Asia/Kolkata')->format('d M, h:i A') }}</span>
                                                </div>
                                                @if($totalReplies > 1)
                                                    <span class="text-[9px] text-slate-400 font-semibold font-mono">({{ $totalReplies }})</span>
                                                @endif
                                            </div>
                                        @elseif($ticket->admin_reply)
                                            <div class="pt-0.5">
                                                <div class="inline-flex items-center gap-1 text-[11px] text-primary bg-primary/10 border border-primary/20 px-2 py-0.5 rounded-lg max-w-[280px]" title="Admin Reply: {{ $ticket->admin_reply }}">
                                                    <svg class="w-3 h-3 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <span class="font-bold shrink-0">Replied:</span>
                                                    <span class="truncate max-w-[120px] text-slate-700 font-normal">{{ $ticket->admin_reply }}</span>
                                                    @if($ticket->replied_at)
                                                        <span class="text-[9px] text-primary font-mono shrink-0 ml-0.5">{{ $ticket->replied_at->timezone('Asia/Kolkata')->format('d M, h:i A') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="pt-0.5">
                                                <span class="inline-flex items-center gap-1 text-[9px] font-semibold text-amber-700 bg-amber-50 border border-amber-200/80 px-1.5 py-0.5 rounded">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                    <span>Waiting for reply</span>
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status Column --}}
                                <td class="px-3 py-2.5 whitespace-nowrap text-center align-top">
                                    @if($ticket->status === 'closed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Closed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            Open
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions Column --}}
                                <td class="px-3 py-2.5 text-right whitespace-nowrap align-top">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Reply Action Button -->
                                        <button onclick='openReplyModal(@json($ticket))'
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer shadow-2xs flex items-center gap-1
                                                {{ $ticket->admin_reply
                                                    ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300'
                                                    : 'bg-primary hover:bg-primary-hover text-white' }}">
                                            @if($ticket->admin_reply)
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 01-2 2v11a2 2 0 012 2h11a2 2 0 012-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                <span>View</span>
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                </svg>
                                                <span>Reply</span>
                                            @endif
                                        </button>

                                        <!-- Status Change Dropdown -->
                                        <select onchange="updateTicketStatus(this, {{ $ticket->id }})"
                                            class="w-20 px-1.5 py-1 bg-white border border-slate-200 hover:border-slate-300 rounded-lg text-xs font-semibold text-slate-700 shadow-2xs focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-colors cursor-pointer">
                                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
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
    <!-- Modal: Reply & Conversation Thread -->
    <div id="replyModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeReplyModal()"></div>
        <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg shadow-2xl relative z-10 overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/90 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-primary/10 text-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs font-extrabold text-slate-800" id="replyModalTitle">Helpdesk Ticket</h3>
                            <span id="modal_status_badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"></span>
                        </div>
                        <p class="text-[10px] text-slate-400 truncate max-w-[260px] sm:max-w-xs" id="modal_user_info"></p>
                    </div>
                </div>
                <button onclick="closeReplyModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg transition-colors cursor-pointer" title="Close">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Modal Body: Conversation History + Form --}}
            <div class="p-3.5 space-y-2 overflow-y-auto flex-1 flex flex-col">
                {{-- Compact Subject Banner --}}
                <div class="px-3 py-1.5 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center gap-2 text-xs shrink-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 shrink-0">Subject:</span>
                    <span class="text-xs font-bold text-slate-800 truncate" id="ticket_subject"></span>
                </div>

                {{-- Thread Container --}}
                <div class="flex-1 flex flex-col min-h-0 space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Conversation</span>
                    <div id="conversation_thread" class="space-y-2 max-h-[300px] overflow-y-auto p-1.5 bg-slate-50/50 rounded-xl border border-slate-100 flex-1">
                        <!-- Populated by JS -->
                    </div>
                </div>

                {{-- Submit Reply Form --}}
                <form id="replyForm" method="POST" enctype="multipart/form-data" class="pt-2 border-t border-slate-200 space-y-2 shrink-0">
                    @csrf
                    <div>
                        <textarea name="admin_reply" id="admin_reply_field" required rows="2"
                            placeholder="Type your reply, update, or resolution instructions..."
                            class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none rounded-xl text-xs text-slate-800 placeholder-slate-400 transition-all"></textarea>
                    </div>

                    <!-- Admin Attachment (PDF, Screenshots, Images, Docs) -->
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-semibold cursor-pointer transition-colors border border-slate-200">
                            <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            <span>Attach File</span>
                            <input type="file" name="attachment" id="admin_attachment_input" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip" class="hidden" onchange="handleAdminFileChange(this)">
                        </label>
                        <div id="admin_file_preview" class="hidden items-center gap-1.5 text-xs">
                            <img id="admin_img_preview" src="" class="hidden h-8 w-auto rounded border border-slate-200 object-cover shadow-2xs">
                            <div id="admin_pdf_preview" class="hidden items-center gap-1 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded text-[11px]">
                                <svg class="w-3 h-3 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                <span id="admin_file_name" class="truncate max-w-[120px] font-medium text-rose-700"></span>
                            </div>
                            <button type="button" onclick="clearAdminFile()" class="text-slate-400 hover:text-rose-600 font-bold text-xs cursor-pointer">&times;</button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-0.5">
                        <div class="flex items-center gap-1.5">
                            <label class="text-[11px] font-bold text-slate-600">Status:</label>
                            <select name="status" id="reply_status_field"
                                class="px-2.5 py-1 bg-white border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none rounded-lg text-xs font-semibold text-slate-700 cursor-pointer">
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" onclick="closeReplyModal()"
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-all cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" id="submitReplyBtn"
                                class="px-4 py-1.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-lg transition-all shadow-2xs cursor-pointer flex items-center gap-1.5">
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
            const status = ticket.status === 'closed' ? 'closed' : 'open';
            statusBadge.innerText = status;
            if (status === 'closed') {
                statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-700';
            } else {
                statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-700';
            }

            // Reset admin file input
            clearAdminFile();

            // Build Conversation Thread
            const threadEl = document.getElementById('conversation_thread');
            threadEl.innerHTML = '';

            const customerName = ticket.user ? ticket.user.name : 'Customer';

            // 1. Initial User Query (Customer - Left aligned)
            let initialHtml = `
                <div class="flex flex-col items-start max-w-[85%] space-y-0.5">
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500 px-1">
                        <span class="w-3.5 h-3.5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[8px] font-bold shrink-0">C</span>
                        <span class="font-bold text-slate-700">${escapeHtml(customerName)}</span>
                        <span class="text-slate-400">·</span>
                        <span class="text-[9px] text-slate-400 font-mono">${formatDateTime(ticket.created_at)}</span>
                        <span class="text-[9px] bg-slate-200/70 text-slate-600 px-1 rounded font-medium">Initial</span>
                    </div>
                    <div class="w-fit p-2 px-3 bg-white border border-slate-200 rounded-2xl rounded-tl-xs space-y-1 shadow-2xs">
                        <p class="text-xs text-slate-800 whitespace-pre-line leading-relaxed font-normal">${escapeHtml(ticket.message || '')}</p>
            `;
            if (ticket.screenshot) {
                const isImg = !ticket.screenshot.toLowerCase().endsWith('.pdf');
                initialHtml += `
                    <div class="pt-1 border-t border-slate-100">
                        ${isImg
                            ? `<a href="/storage/${ticket.screenshot}" target="_blank" class="inline-block"><img src="/storage/${ticket.screenshot}" class="h-14 w-auto max-w-[180px] rounded-lg border border-slate-200 object-cover shadow-2xs hover:opacity-90 transition-opacity cursor-zoom-in" title="Click to view full image"></a>`
                            : `<a href="/storage/${ticket.screenshot}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-slate-700 hover:text-primary font-semibold bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-md"><svg class="w-3 h-3 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg> <span>View PDF</span></a>`
                        }
                    </div>
                `;
            }
            initialHtml += `</div></div>`;
            threadEl.innerHTML += initialHtml;

            // 2. Thread messages
            const messages = ticket.messages || [];
            if (messages.length > 0) {
                messages.forEach(msg => {
                    if (msg.sender_type === 'admin') {
                        let adminMsgHtml = `
                            <div class="flex flex-col items-end max-w-[85%] ml-auto space-y-0.5">
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-500 px-1 justify-end">
                                    <span class="text-[9px] text-slate-400 font-mono">${formatDateTime(msg.created_at)}</span>
                                    <span class="text-slate-400">·</span>
                                    <span class="font-bold text-primary">Support Desk</span>
                                    <span class="text-[9px] bg-primary/10 text-primary px-1 rounded font-semibold">${escapeHtml(msg.sender_name || 'Admin')}</span>
                                </div>
                                <div class="w-fit p-2 px-3 bg-primary/10 border border-primary/20 rounded-2xl rounded-tr-xs space-y-1 text-left shadow-2xs">
                                    <p class="text-xs text-slate-800 whitespace-pre-line leading-relaxed font-normal">${escapeHtml(msg.message)}</p>
                        `;
                        if (msg.attachment) {
                            const isAdminImg = !msg.attachment.toLowerCase().endsWith('.pdf');
                            adminMsgHtml += `
                                <div class="pt-1 border-t border-primary/15">
                                    ${isAdminImg
                                        ? `<a href="/storage/${msg.attachment}" target="_blank" class="inline-block"><img src="/storage/${msg.attachment}" class="h-14 w-auto max-w-[180px] rounded-lg border border-primary/20 object-cover shadow-2xs hover:opacity-90 transition-opacity cursor-zoom-in" title="Click to view full image"></a>`
                                        : `<a href="/storage/${msg.attachment}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-slate-700 hover:text-primary font-semibold bg-white border border-primary/20 px-2 py-0.5 rounded-md"><svg class="w-3 h-3 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg> <span>View PDF</span></a>`
                                    }
                                </div>
                            `;
                        }
                        adminMsgHtml += `</div></div>`;
                        threadEl.innerHTML += adminMsgHtml;
                    } else {
                        let userMsgHtml = `
                            <div class="flex flex-col items-start max-w-[85%] space-y-0.5">
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-500 px-1">
                                    <span class="w-3.5 h-3.5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[8px] font-bold shrink-0">C</span>
                                    <span class="font-bold text-slate-700">${escapeHtml(customerName)}</span>
                                    <span class="text-slate-400">·</span>
                                    <span class="text-[9px] text-slate-400 font-mono">${formatDateTime(msg.created_at)}</span>
                                    <span class="text-[9px] bg-slate-200/70 text-slate-600 px-1 rounded font-medium">Follow-up</span>
                                </div>
                                <div class="w-fit p-2 px-3 bg-white border border-slate-200 rounded-2xl rounded-tl-xs space-y-1 shadow-2xs">
                                    <p class="text-xs text-slate-800 whitespace-pre-line leading-relaxed font-normal">${escapeHtml(msg.message)}</p>
                        `;
                        if (msg.attachment) {
                            const isUserImg = !msg.attachment.toLowerCase().endsWith('.pdf');
                            userMsgHtml += `
                                <div class="pt-1 border-t border-slate-100">
                                    ${isUserImg
                                        ? `<a href="/storage/${msg.attachment}" target="_blank" class="inline-block"><img src="/storage/${msg.attachment}" class="h-14 w-auto max-w-[180px] rounded-lg border border-slate-200 object-cover shadow-2xs hover:opacity-90 transition-opacity cursor-zoom-in" title="Click to view full image"></a>`
                                        : `<a href="/storage/${msg.attachment}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-slate-700 hover:text-primary font-semibold bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-md"><svg class="w-3 h-3 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg> <span>View PDF</span></a>`
                                    }
                                </div>
                            `;
                        }
                        userMsgHtml += `</div></div>`;
                        threadEl.innerHTML += userMsgHtml;
                    }
                });
            } else if (ticket.admin_reply && ticket.admin_reply.trim() !== '') {
                // Legacy reply fallback
                threadEl.innerHTML += `
                    <div class="flex flex-col items-end max-w-[85%] ml-auto space-y-0.5">
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-500 px-1 justify-end">
                            <span class="text-[9px] text-slate-400 font-mono">${formatDateTime(ticket.replied_at)}</span>
                            <span class="text-slate-400">·</span>
                            <span class="font-bold text-primary">Support Desk</span>
                            <span class="text-[9px] bg-primary/10 text-primary px-1 rounded font-semibold">Admin Reply</span>
                        </div>
                        <div class="w-fit p-2 px-3 bg-primary/10 border border-primary/20 rounded-2xl rounded-tr-xs space-y-1 text-left shadow-2xs">
                            <p class="text-xs text-slate-800 whitespace-pre-line leading-relaxed font-normal">${escapeHtml(ticket.admin_reply)}</p>
                        </div>
                    </div>
                `;
            }

            // Auto-scroll to bottom of conversation
            setTimeout(() => {
                threadEl.scrollTop = threadEl.scrollHeight;
            }, 60);

            // Form defaults
            document.getElementById('admin_reply_field').value = '';
            const statusField = document.getElementById('reply_status_field');
            if (statusField) {
                statusField.value = ticket.status === 'closed' ? 'closed' : 'open';
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
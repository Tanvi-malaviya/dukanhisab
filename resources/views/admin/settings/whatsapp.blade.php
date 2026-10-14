@extends('layouts.admin')

@section('title', 'WhatsApp Settings')
@section('page_title', 'WhatsApp Messaging')
@section('page_subtitle', 'Meta Cloud API connection, fixed message templates, message-credit packs and webhook configuration')

@section('content')
@php
    $inputClass = 'block w-full px-3.5 py-2.5 bg-white dark:bg-secondary/30 border border-slate-300 dark:border-border-dark focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none rounded-xl text-xs sm:text-sm text-slate-800 dark:text-white transition-all shadow-2xs';
    $labelClass = 'block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5';
    // Badge colours for Meta's template review states.
    $metaStatusClass = fn ($status) => match ($status) {
        'APPROVED' => 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50',
        'PENDING', 'IN_APPEAL', 'PENDING_DELETION' => 'bg-amber-50 text-amber-800 border border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50',
        'REJECTED', 'DISABLED', 'NOT_IN_META', 'DELETED' => 'bg-rose-50 text-rose-800 border border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50',
        'PAUSED', 'FLAGGED' => 'bg-orange-50 text-orange-800 border border-orange-200/80 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800/50',
        default => 'bg-slate-100 text-slate-700 border border-slate-200/80 dark:bg-white/10 dark:text-slate-300 dark:border-white/10',
    };
    $metaStatusLabel = fn ($status) => match ($status) {
        null => 'Not submitted',
        'NOT_IN_META' => 'Not in Meta',
        default => ucwords(strtolower(str_replace('_', ' ', $status))),
    };
    $languageBadgeClass = fn ($code) => match ($code) {
        'en' => 'bg-blue-50 text-blue-700 border border-blue-200/80 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/50',
        'gu' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50',
        'hi' => 'bg-purple-50 text-purple-700 border border-purple-200/80 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800/50',
        default => 'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-white/10 dark:text-slate-300 dark:border-white/10',
    };
@endphp
<div class="space-y-3 w-full">

    <!-- 4 Tabs Navigation Bar -->
    <div class="bg-white dark:bg-card-dark border border-slate-200 dark:border-border-dark p-2 rounded-2xl shadow-xs flex items-center gap-2 overflow-x-auto no-scrollbar">
        <!-- Tab 1: WhatsApp Setup -->
        <button type="button" onclick="switchTab('setup')" id="tab-btn-setup"
            class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-primary text-white shadow-sm transition-all cursor-pointer whitespace-nowrap shrink-0 text-white-force">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>1. WhatsApp Setup</span>
        </button>

        <!-- Tab 2: Message Template -->
        <button type="button" onclick="switchTab('templates')" id="tab-btn-templates"
            class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-white dark:bg-card-dark hover:bg-slate-50 dark:hover:bg-secondary/20 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-border-dark transition-all cursor-pointer whitespace-nowrap shrink-0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
            <span>2. Message Template</span>
            <span id="tab-badge-templates" class="tab-counter-badge px-2 py-0.5 text-[11px] font-bold rounded-full transition-all">{{ count($templates) }}</span>
        </button>

        <!-- Tab 3: Message Pack -->
        <button type="button" onclick="switchTab('packs')" id="tab-btn-packs"
            class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-white dark:bg-card-dark hover:bg-slate-50 dark:hover:bg-secondary/20 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-border-dark transition-all cursor-pointer whitespace-nowrap shrink-0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <span>3. Message Pack</span>
            <span id="tab-badge-packs" class="tab-counter-badge px-2 py-0.5 text-[11px] font-bold rounded-full transition-all">{{ count($packs) }}</span>
        </button>

        <!-- Tab 4: Webhook Setup -->
        <button type="button" onclick="switchTab('webhook')" id="tab-btn-webhook"
            class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-white dark:bg-card-dark hover:bg-slate-50 dark:hover:bg-secondary/20 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-border-dark transition-all cursor-pointer whitespace-nowrap shrink-0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <span>4. Webhook</span>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-primary/10 text-primary border border-primary/20">Meta API</span>
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: WHATSAPP SETUP                      -->
    <!-- ========================================== -->
    <div id="tab-content-setup" class="space-y-4">
        <!-- Card 1: Cloud API connection -->
        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-secondary/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-sm">Meta WhatsApp Cloud API</h3>
                        <p class="text-xs text-slate-400">All messages are sent from the single DukanHisab WhatsApp number configured here.</p>
                    </div>
                </div>

                <button type="button" id="btn-test-connection" onclick="runConnectionTest()"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-primary/15 hover:bg-primary/25 text-primary border border-primary/30 transition-all flex items-center gap-2 cursor-pointer">
                    <svg id="test-spinner" class="animate-spin h-3.5 w-3.5 hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span>Test Connection</span>
                </button>
            </div>

            <div id="connection-test-result" class="hidden px-6 py-3 border-b text-xs flex items-center justify-between">
                <span id="connection-test-message"></span>
                <button type="button" onclick="document.getElementById('connection-test-result').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('admin.settings.whatsapp.update') }}" method="POST" class="p-6 space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $labelClass }}">WhatsApp Messaging</label>
                        <select name="whatsapp_enabled" required class="{{ $inputClass }}">
                            <option value="yes" {{ $settings['whatsapp_enabled'] === 'yes' ? 'selected' : '' }}>Enabled (send messages for all shops)</option>
                            <option value="no" {{ $settings['whatsapp_enabled'] !== 'yes' ? 'selected' : '' }}>Disabled (pause all WhatsApp sending)</option>
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Graph API Version</label>
                        <input type="text" name="whatsapp_api_version" id="whatsapp_api_version" required
                            value="{{ old('whatsapp_api_version', $settings['whatsapp_api_version']) }}" placeholder="v23.0"
                            class="{{ $inputClass }} font-mono">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Phone Number ID</label>
                        <input type="text" name="whatsapp_phone_number_id" id="whatsapp_phone_number_id"
                            value="{{ old('whatsapp_phone_number_id', $settings['whatsapp_phone_number_id']) }}" placeholder="e.g. 106540352242922"
                            class="{{ $inputClass }} font-mono">
                        <span class="text-[11px] text-slate-500 mt-1 block">WhatsApp Manager &rarr; API Setup &rarr; Phone number ID.</span>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">WhatsApp Business Account ID</label>
                        <input type="text" name="whatsapp_waba_id" id="whatsapp_waba_id"
                            value="{{ old('whatsapp_waba_id', $settings['whatsapp_waba_id']) }}" placeholder="e.g. 102290129340398"
                            class="{{ $inputClass }} font-mono">
                        <span class="text-[11px] text-slate-500 mt-1 block">Needed to submit and sync message templates with Meta.</span>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Meta App ID <span class="normal-case font-normal">(for PDF-header templates)</span></label>
                        <input type="text" name="whatsapp_app_id" id="whatsapp_app_id"
                            value="{{ old('whatsapp_app_id', $settings['whatsapp_app_id']) }}" placeholder="e.g. 1234567890123456"
                            class="{{ $inputClass }} font-mono">
                        <span class="text-[11px] text-slate-500 mt-1 block">Meta App &rarr; App Settings &rarr; Basic. Used to upload the sample PDF Meta requires for document headers.</span>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }} flex items-center justify-between">
                        <span>Permanent Access Token</span>
                        @if($settings['has_access_token'])
                            <span class="text-[10px] text-success font-mono normal-case">Saved ({{ $settings['access_token_hint'] }}) — leave blank to keep</span>
                        @endif
                    </label>
                    <input type="password" name="whatsapp_access_token" id="whatsapp_access_token" autocomplete="off"
                        placeholder="{{ $settings['has_access_token'] ? 'Enter a new token only to replace the saved one' : 'System User access token (EAAG...)' }}"
                        class="{{ $inputClass }} font-mono">
                    <span class="text-[11px] text-slate-500 mt-1 block">Create it in Business Settings &rarr; System Users with the <code>whatsapp_business_messaging</code> and <code>whatsapp_business_management</code> permissions (the second one lets the panel submit and sync templates). Stored encrypted.</span>
                </div>



                <div class="flex items-center justify-between pt-4 border-t border-border-dark">
                    <div class="text-xs text-slate-400">Shop owners can only send messages while this is enabled and they have credits.</div>
                    <x-button type="submit" variant="primary">Save WhatsApp Settings</x-button>
                </div>
            </form>
        </div>

        <!-- Card 2: Send a test message -->
        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm p-6 space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-bold text-white text-sm">Send a Test Message</h3>
                    <p class="text-xs text-slate-400">Pick any template that exists in your Meta account — variables are filled with sample values. Only <strong>approved</strong> templates can be sent.</p>
                </div>
                <button type="button" id="btn-refresh-meta-templates" onclick="loadMetaTemplates()"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-primary/15 hover:bg-primary/25 text-primary border border-primary/30 transition-all flex items-center gap-2 cursor-pointer shrink-0">
                    <svg id="meta-templates-spinner" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Refresh from Meta</span>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $labelClass }}">Mobile Number</label>
                    <input type="text" id="test_phone" placeholder="9876543210" class="{{ $inputClass }} font-mono">
                </div>
                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Template in Meta</label>
                    <select id="test_template" class="{{ $inputClass }}" onchange="onTestTemplateChange()">
                        <option value="">Loading templates from Meta...</option>
                    </select>
                    <span id="meta-templates-note" class="text-[11px] text-slate-500 mt-1 block"></span>
                </div>
            </div>
            <div id="test_media_wrap" class="hidden">
                <label class="{{ $labelClass }}" id="test_media_label">Sample PDF URL</label>
                <input type="url" id="test_document_url" placeholder="https://..." class="{{ $inputClass }}">
            </div>
            <div id="test_preview_wrap" class="hidden">
                <label class="{{ $labelClass }}">Preview — how the customer sees it</label>
                <div id="test_preview_bubble" class="max-w-md"></div>
                <span id="test_preview_meta" class="block mt-2 text-[11px] text-slate-500"></span>
            </div>
            <div class="flex items-center justify-between gap-4">
                <span id="test-send-result" class="text-xs"></span>
                <x-button type="button" id="btn-test-send" onclick="sendTestMessage()" variant="success">Send Test</x-button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: MESSAGE TEMPLATES                   -->
    <!-- ========================================== -->
    <div id="tab-content-templates" class="hidden space-y-4">
        @if(count($missing))
            <div class="p-3 rounded-xl bg-warning/10 border border-warning/30 text-warning text-xs flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>{{ count($missing) }} message type(s) have no active template: {{ implode(', ', $missing) }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-card-dark border border-slate-200 dark:border-border-dark rounded-2xl shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-200 dark:border-border-dark bg-slate-50/80 dark:bg-secondary/10 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Message Templates</h3>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-primary/10 text-primary border border-primary/20 font-bold font-mono">{{ count($templates) }} Registered</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <form action="{{ route('admin.settings.whatsapp.templates.sync') }}" method="POST" onsubmit="this.querySelector('button').disabled = true; this.querySelector('span').innerText = 'Syncing...';">
                        @csrf
                        <button type="submit" title="Pull review status from Meta"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-secondary/20 hover:bg-slate-100 dark:hover:bg-secondary/30 text-slate-700 dark:text-white border border-slate-300 dark:border-border-dark transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>Sync from Meta</span>
                        </button>
                    </form>
                    <button type="button" onclick="openTemplateModal()" class="px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl transition-all shadow-xs cursor-pointer flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Add Template</span>
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-secondary/20 border-b border-slate-200 dark:border-border-dark text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 tracking-wider">
                            <th class="px-3.5 py-3">Event</th>
                            <th class="px-3 py-3">Language</th>
                            <th class="px-3 py-3">Meta Template</th>
                            <th class="px-3 py-3">Preview</th>
                            <th class="px-3 py-3">Meta Review</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3.5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-border-dark text-sm">
                        @forelse($templates as $t)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-secondary/10 transition-colors align-middle">
                                <td class="px-3.5 py-3">
                                    <div class="font-bold text-slate-900 dark:text-white text-xs whitespace-nowrap">{{ $events[$t->key]['label'] ?? $t->key }}</div>
                                    <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">To {{ $t->recipient }}</div>
                                </td>
                                <td class="px-3 py-3 text-xs">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-md font-bold text-[11px] whitespace-nowrap {{ $languageBadgeClass($t->language) }}">
                                        {{ $languages[$t->language] ?? $t->language }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-xs font-mono">
                                    <div class="text-slate-900 dark:text-white font-bold text-xs whitespace-nowrap">{{ $t->meta_template_name }}</div>
                                    <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $t->meta_language_code }}</div>
                                </td>
                                <td class="px-3 py-3 text-xs">
                                    <div class="max-w-[190px] xl:max-w-[240px]">
                                        <p class="text-slate-800 dark:text-slate-200 text-xs font-medium truncate" title="{{ $t->renderBody($sampleValues) }}">
                                            {{ preg_replace('/\s+/', ' ', $t->renderBody($sampleValues)) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1 text-[10px]">
                                        @if($t->has_document)
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/50 font-semibold whitespace-nowrap">📄 PDF</span>
                                        @endif
                                        @if($t->has_pay_button)
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800/50 font-semibold whitespace-nowrap">💳 Pay Now</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-xs" data-meta-row="{{ $t->id }}">
                                    <span data-meta-badge class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap {{ $metaStatusClass($t->meta_status) }}">{{ $metaStatusLabel($t->meta_status) }}</span>
                                    @if($t->differsFromMeta())
                                        <span class="block text-amber-600 dark:text-amber-400 font-medium text-[10px] mt-0.5 whitespace-nowrap">Edited · resubmit</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap {{ $t->status === 'active' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50' : 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-white/10 dark:text-slate-400' }}">{{ ucfirst($t->status) }}</span>
                                </td>
                                <td class="px-3.5 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if(!$t->meta_template_id || $t->canEditInMeta())
                                            <button type="button" onclick="confirmAction({ actionUrl: '{{ route('admin.settings.whatsapp.templates.submit', $t->id) }}', title: '{{ $t->meta_template_id ? 'Resubmit to Meta' : 'Submit to Meta' }}', message: 'Send “{{ $t->meta_template_name }}” ({{ $t->meta_language_code }}) to Meta for review?', buttonText: 'Submit', method: 'POST' })"
                                                class="px-2.5 py-1 text-[11px] font-bold bg-teal-50 hover:bg-teal-100 dark:bg-primary/20 dark:hover:bg-primary/30 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-700/50 rounded-lg transition-colors cursor-pointer" title="Submit to Meta">
                                                {{ $t->meta_template_id ? 'Resubmit' : 'Submit' }}
                                            </button>
                                        @endif
                                        <button type="button" onclick='openPreviewModal(localPreviewModel(@json($t)), @json($t->meta_template_name . " · " . $t->meta_language_code))'
                                            class="px-2.5 py-1 text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-white/10 rounded-lg transition-colors cursor-pointer">Preview</button>
                                        <button type="button" onclick='openTemplateModal(@json($t))'
                                            class="px-2.5 py-1 text-[11px] font-semibold bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/40 dark:hover:bg-sky-900/50 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/50 rounded-lg transition-colors cursor-pointer">Edit</button>
                                        <button type="button" onclick="confirmAction({ actionUrl: '{{ route('admin.settings.whatsapp.templates.destroy', $t->id) }}', title: 'Delete Template', message: 'Delete this template?', buttonText: 'Delete', variant: 'danger', method: 'DELETE' })"
                                            class="px-2.5 py-1 text-[11px] font-semibold bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50 rounded-lg transition-colors cursor-pointer">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state colspan="7" title="No templates yet" message="Add a template and submit it to Meta, or click Sync from Meta to pull the ones you already have." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- TAB 3: MESSAGE PACKS                       -->
    <!-- ========================================== -->
    <div id="tab-content-packs" class="hidden space-y-6">
        <!-- Quick stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-card-dark border border-border-dark p-4 rounded-2xl shadow-xs">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Packs</p>
                <h3 class="text-2xl font-black text-white mt-1">{{ count($packs) }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Available bundle tiers</p>
            </div>
            <div class="bg-card-dark border border-border-dark p-4 rounded-2xl shadow-xs">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Active Packs</p>
                <h3 class="text-2xl font-black text-success mt-1">{{ $packs->where('status', 'active')->count() }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Visible to shop owners</p>
            </div>
            <div class="bg-card-dark border border-border-dark p-4 rounded-2xl shadow-xs">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Credit Rule</p>
                <h3 class="text-2xl font-black text-primary mt-1">1 Credit</h3>
                <p class="text-[11px] text-slate-400 mt-1">= 1 Sent WhatsApp message</p>
            </div>
        </div>

        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-secondary/10 flex items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-white text-sm">Message Credit Packs</h3>
                    <p class="text-xs text-slate-400">Credit bundles shop owners buy in the app. One credit = one WhatsApp message. If a message delivery fails permanently, the credit is auto-refunded via webhook.</p>
                </div>
                <x-button type="button" onclick="openPackModal()" variant="primary" class="whitespace-nowrap shrink-0">+ Add Pack</x-button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                            <th class="px-6 py-3">Pack</th>
                            <th class="px-6 py-3">Messages (Credits)</th>
                            <th class="px-6 py-3">Price</th>
                            <th class="px-6 py-3">Per Message Cost</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                        @forelse($packs as $p)
                            <tr class="hover:bg-secondary/10 transition-colors">
                                <td class="px-6 py-4 font-semibold text-white">{{ $p->name }}</td>
                                <td class="px-6 py-4 font-mono text-xs">{{ number_format($p->credits) }}</td>
                                <td class="px-6 py-4 font-mono text-xs">₹{{ number_format($p->price, 2) }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-400">₹{{ number_format($p->price / max($p->credits, 1), 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $p->status === 'active' ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger' }}">{{ ucfirst($p->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <button type="button" onclick='openPackModal(@json($p))' class="p-1 text-xs text-primary hover:underline cursor-pointer">Edit</button>
                                    <button type="button" onclick="confirmAction({ actionUrl: '{{ route('admin.settings.whatsapp.packs.destroy', $p->id) }}', title: 'Delete Pack', message: 'Delete this message pack? Shop owners will no longer be able to buy it.', buttonText: 'Delete', variant: 'danger', method: 'DELETE' })" class="p-1 text-xs text-danger hover:underline cursor-pointer">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state colspan="6" title="No message packs yet" message="Add packs such as 100, 500 and 2000 messages." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 4: WEBHOOK SETUP & LIVE EVENTS         -->
    <!-- ========================================== -->
    <div id="tab-content-webhook" class="hidden space-y-6">

        <!-- Webhook Configuration Form Card -->
        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-secondary/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-sm">Meta WhatsApp Webhook Configuration</h3>
                        <p class="text-xs text-slate-400">Configure these credentials in your Meta App Dashboard under WhatsApp &rarr; Configuration.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <!-- Callback URL Box -->
                <div>
                    <label class="{{ $labelClass }}">Callback URL</label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input type="text" readonly id="tab-webhook-url-input" value="{{ $webhookUrl }}"
                                class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark rounded-xl text-xs text-primary font-mono select-all">
                        </div>
                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('tab-webhook-url-input').value).then(() => { this.innerText = 'Copied!'; setTimeout(() => this.innerText = 'Copy URL', 2000); })"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-primary/20 hover:bg-primary/30 text-primary border border-primary/30 transition-all cursor-pointer shrink-0">
                            Copy URL
                        </button>
                    </div>
                    <span class="text-[11px] text-slate-500 mt-1 block">Meta calls this endpoint to verify the handshake (GET) and stream real-time delivery events (POST).</span>
                </div>

                <!-- Webhook Form -->
                <form action="{{ route('admin.settings.whatsapp.update') }}" method="POST" class="space-y-4 pt-2 border-t border-border-dark">
                    @csrf
                    <input type="hidden" name="whatsapp_enabled" value="{{ $settings['whatsapp_enabled'] }}">
                    <input type="hidden" name="whatsapp_api_version" value="{{ $settings['whatsapp_api_version'] }}">
                    <input type="hidden" name="whatsapp_phone_number_id" value="{{ $settings['whatsapp_phone_number_id'] }}">
                    <input type="hidden" name="whatsapp_waba_id" value="{{ $settings['whatsapp_waba_id'] }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-semibold text-slate-400 uppercase">Verify Token</label>
                                @if($settings['whatsapp_webhook_verify_token'])
                                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('tab_webhook_verify_token').value).then(() => { this.innerText = 'Copied!'; setTimeout(() => this.innerText = 'Copy Token', 2000); })" 
                                        class="text-[11px] text-primary hover:underline cursor-pointer font-medium">Copy Token</button>
                                @endif
                            </div>
                            <input type="text" name="whatsapp_webhook_verify_token" id="tab_webhook_verify_token"
                                value="{{ old('whatsapp_webhook_verify_token', $settings['whatsapp_webhook_verify_token']) }}"
                                placeholder="e.g. dukanhisab_secure_token_2026"
                                class="{{ $inputClass }} font-mono">
                            <span class="text-[11px] text-slate-500 mt-1 block">Secret handshake token entered into Meta during webhook verification.</span>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-semibold text-slate-400 uppercase">Meta App Secret</label>
                                @if($settings['has_app_secret'])
                                    <span class="text-[10px] text-success font-mono">Saved &bull; Leave blank to keep</span>
                                @endif
                            </div>
                            <input type="password" name="whatsapp_app_secret" autocomplete="off"
                                placeholder="{{ $settings['has_app_secret'] ? 'Enter a new secret only to replace the saved one' : 'Meta App → App Settings → Basic → App Secret' }}"
                                class="{{ $inputClass }} font-mono">
                            <span class="text-[11px] text-slate-500 mt-1 block">Used to verify HMAC-SHA256 signature on every webhook event. Stored encrypted.</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-3 border-t border-border-dark">
                        <x-button type="submit" variant="primary">Save Webhook Settings</x-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 4-Step Quick Setup Guide (Clean Stepper) -->
        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-border-dark pb-3">
                <h3 class="font-bold text-white text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Meta Developer Portal Setup Guide</span>
                </h3>
                <a href="https://developers.facebook.com/apps" target="_blank" class="text-xs text-primary hover:underline inline-flex items-center gap-1 font-semibold">
                    <span>Open Meta Apps</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="p-3.5 rounded-xl bg-secondary/15 border border-border-dark/60 space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-primary/20 text-primary flex items-center justify-center text-xs font-extrabold shrink-0">1</span>
                        <h4 class="text-xs font-bold text-white">Navigate</h4>
                    </div>
                    <p class="text-xs text-slate-400">Go to your App &rarr; <strong>WhatsApp</strong> &rarr; <strong>Configuration</strong>.</p>
                </div>

                <div class="p-3.5 rounded-xl bg-secondary/15 border border-border-dark/60 space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-primary/20 text-primary flex items-center justify-center text-xs font-extrabold shrink-0">2</span>
                        <h4 class="text-xs font-bold text-white">Edit Webhook</h4>
                    </div>
                    <p class="text-xs text-slate-400">Under the <strong>Webhook</strong> section, click the <strong>Edit</strong> button.</p>
                </div>

                <div class="p-3.5 rounded-xl bg-secondary/15 border border-border-dark/60 space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-primary/20 text-primary flex items-center justify-center text-xs font-extrabold shrink-0">3</span>
                        <h4 class="text-xs font-bold text-white">Verify & Save</h4>
                    </div>
                    <p class="text-xs text-slate-400">Paste the <strong>Callback URL</strong> and <strong>Verify Token</strong>, then click <strong>Verify and Save</strong>.</p>
                </div>

                <div class="p-3.5 rounded-xl bg-secondary/15 border border-border-dark/60 space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-primary/20 text-primary flex items-center justify-center text-xs font-extrabold shrink-0">4</span>
                        <h4 class="text-xs font-bold text-white">Subscribe</h4>
                    </div>
                    <p class="text-xs text-slate-400">Under Webhook fields, click <strong>Manage</strong> and subscribe to <code class="text-primary font-mono text-[11px]">messages</code>, <code class="text-primary font-mono text-[11px]">message_template_status_update</code> and <code class="text-primary font-mono text-[11px]">template_category_update</code>.</p>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal: Add / Edit Template -->
<div id="templateModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-5">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModal('templateModal')"></div>
    <div class="bg-white dark:bg-card-dark border border-slate-200 dark:border-border-dark rounded-2xl w-full max-w-4xl shadow-2xl relative z-10 overflow-hidden flex flex-col max-h-[92vh] my-auto animate-in fade-in zoom-in-95 duration-200">
        
        {{-- Sticky Modal Header --}}
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-border-dark flex items-center justify-between bg-slate-50/90 dark:bg-secondary/15 shrink-0">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </span>
                <div>
                    <h3 id="templateModalTitle" class="text-sm font-extrabold text-slate-800 dark:text-white">Add WhatsApp Template</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Configure Meta Cloud API message template &amp; preview live</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('templateModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 transition-colors cursor-pointer" title="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Form wrapped around body & sticky footer --}}
        <form id="templateForm" method="POST" class="flex flex-col flex-1 overflow-hidden m-0">
            @csrf
            <input type="hidden" name="form_type" value="template">
            <input type="hidden" name="template_id" id="tpl_id">

            {{-- Scrollable Body: 2 Columns on LG screens --}}
            <div class="overflow-y-auto flex-1 p-4 sm:p-5">
                <div class="flex flex-col lg:flex-row gap-5 lg:gap-6 items-start w-full">
                    
                    {{-- Left Column: Form Fields (Takes remaining width) --}}
                    <div class="w-full lg:flex-1 min-w-0 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="{{ $labelClass }}">Event</label>
                                <select name="key" id="tpl_key" required class="{{ $inputClass }}" onchange="refreshTemplateForm()">
                                    @foreach($events as $key => $event)
                                        <option value="{{ $key }}">{{ $event['label'] }} (to {{ $event['recipient'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">App Language</label>
                                <select name="language" id="tpl_language" required class="{{ $inputClass }}" onchange="suggestMetaLanguage()">
                                    @foreach($languages as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="{{ $labelClass }}">Meta Template Name</label>
                                <input type="text" name="meta_template_name" id="tpl_meta_name" required placeholder="due_reminder_gu" class="{{ $inputClass }} font-mono">
                                <span class="text-[10px] text-slate-500 mt-1 block">Lowercase, numbers &amp; underscores only.</span>
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Meta Language Code</label>
                                <input type="text" name="meta_language_code" id="tpl_meta_lang" required placeholder="en_US" class="{{ $inputClass }} font-mono">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="{{ $labelClass }} mb-0">Message Body</label>
                                <span class="text-[11px] text-slate-400 font-mono">Use &#123;&#123;1&#125;&#125;, &#123;&#123;2&#125;&#125;...</span>
                            </div>
                            <textarea name="body" id="tpl_body" rows="4" required maxlength="1024" oninput="refreshTemplateForm()"
                                placeholder="Type your WhatsApp message text with variables..."
                                class="{{ $inputClass }} resize-y"></textarea>
                        </div>

                        {{-- Dynamic Variables Section --}}
                        <div class="space-y-1.5">
                            <label class="{{ $labelClass }} mb-1">Variables Mapping</label>
                            <div id="tpl_variables" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5"></div>
                            <p id="tpl_no_vars" class="text-xs text-slate-400 italic">No &#123;&#123;1&#125;&#125; variables in message body yet.</p>
                        </div>

                        {{-- Options & Status --}}
                        <div class="p-3.5 bg-slate-50 dark:bg-secondary/10 rounded-xl border border-slate-200 dark:border-border-dark space-y-2.5">
                            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Features &amp; Status</span>
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-700 dark:text-slate-300">
                                <label class="flex items-center gap-2 cursor-pointer font-medium hover:text-primary transition-colors">
                                    <input type="checkbox" name="has_document" value="1" id="tpl_doc" onchange="refreshTemplateForm()" class="rounded border-slate-300 text-primary focus:ring-primary">
                                    <span>PDF document header</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer font-medium hover:text-primary transition-colors" id="tpl_pay_wrap">
                                    <input type="checkbox" name="has_pay_button" value="1" id="tpl_pay" onchange="refreshTemplateForm()" class="rounded border-slate-300 text-primary focus:ring-primary">
                                    <span>Pay Now button (URL)</span>
                                </label>
                                <label class="flex items-center gap-2 ml-auto">
                                    <span class="font-bold">Status:</span>
                                    <select name="status" id="tpl_status" class="px-2.5 py-1 bg-white dark:bg-secondary/30 border border-slate-300 dark:border-border-dark rounded-lg text-xs font-semibold text-slate-700 dark:text-white cursor-pointer">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Live WhatsApp Phone Preview (Fixed 320px width) --}}
                    <div class="w-full lg:w-[320px] shrink-0 space-y-2 lg:sticky lg:top-0">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Live Preview</label>
                            <span class="text-[10px] text-slate-400 font-medium">How customer sees it</span>
                        </div>
                        <div class="p-3 bg-slate-100/70 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-border-dark flex justify-center shadow-inner">
                            <div id="tpl_preview_bubble" class="w-full max-w-[300px]"></div>
                        </div>
                        <p class="text-[10px] text-slate-400 text-center">Preview updates live as you type.</p>
                    </div>

                </div>
            </div>

            {{-- Sticky Modal Footer --}}
            <div class="px-5 py-3.5 bg-slate-50 dark:bg-secondary/15 border-t border-slate-200 dark:border-border-dark flex flex-wrap items-center justify-between gap-2 shrink-0">
                <span class="text-[11px] text-slate-400 hidden sm:inline-block">Template approval takes ~5 minutes on Meta</span>
                <div class="flex items-center gap-2 ml-auto">
                    <button type="button" onclick="closeModal('templateModal')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-secondary/30 dark:hover:bg-secondary/50 text-slate-800 dark:text-white text-xs font-bold rounded-xl transition-all cursor-pointer">
                        Save Only
                    </button>
                    <button type="submit" name="submit_to_meta" value="1"
                        class="px-5 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl transition-all shadow-xs cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        <span>Save &amp; Submit to Meta</span>
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- Modal: Template preview -->
<div id="previewModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModal('previewModal')"></div>
    <div class="bg-white dark:bg-card-dark border border-slate-200 dark:border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh] my-auto animate-in fade-in zoom-in-95 duration-200">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-border-dark flex items-center justify-between bg-slate-50/90 dark:bg-secondary/15 shrink-0">
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Template Preview</h3>
                <p id="previewModalName" class="text-[11px] text-slate-400 font-mono"></p>
            </div>
            <button type="button" onclick="closeModal('previewModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1.5 rounded-lg transition-colors cursor-pointer"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <div class="p-5 overflow-y-auto flex-1 flex flex-col items-center">
            <div id="previewModalBubble" class="w-full max-w-[320px]"></div>
            <p class="text-[11px] text-slate-400 text-center mt-3">Shown with sample customer values. The real message dynamically replaces these.</p>
        </div>
        <div class="px-5 py-3 bg-slate-50 dark:bg-secondary/15 border-t border-slate-200 dark:border-border-dark flex justify-end shrink-0">
            <button type="button" onclick="closeModal('previewModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-all cursor-pointer">Close</button>
        </div>
    </div>
</div>

<!-- WhatsApp chat look for template previews (WhatsApp's own light / dark colours) -->
<style>
    .wa-chat { background: #efeae2; border-radius: 14px; overflow: hidden; border: 1px solid #e2ddd5; }
    .wa-chat-top { display: flex; align-items: center; gap: 10px; padding: 9px 12px; background: #008069; color: #fff; }
    .wa-chat-avatar { width: 30px; height: 30px; border-radius: 9999px; background: #dfe5e7; color: #008069; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; }
    .wa-chat-name { font-size: 13px; font-weight: 600; line-height: 1.1; color: #fff; }
    .wa-chat-sub { font-size: 10px; opacity: .85; color: #fff; }
    .wa-chat-body { padding: 14px 12px 16px; }
    .wa-bubble { position: relative; max-width: 300px; background: #fff; color: #111b21; border-radius: 0 8px 8px 8px; box-shadow: 0 1px .5px rgba(11, 20, 26, .13); font-size: 13.5px; line-height: 19px; }
    .wa-bubble::before { content: ""; position: absolute; top: 0; left: -7px; border-top: 0 solid transparent; border-right: 8px solid #fff; border-bottom: 10px solid transparent; }
    .wa-media { margin: 3px 3px 0; border-radius: 6px; overflow: hidden; }
    .wa-doc { display: flex; align-items: center; gap: 10px; padding: 10px; background: #f5f6f6; }
    .wa-doc-icon { width: 30px; height: 36px; border-radius: 4px; background: #e53935; color: #fff; font-size: 9px; font-weight: 800; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 4px; flex-shrink: 0; }
    .wa-doc-name { font-size: 13px; color: #111b21; font-weight: 500; }
    .wa-doc-meta { font-size: 11px; color: #667781; }
    .wa-img { height: 140px; background: #d1d7db; color: #8696a0; display: flex; align-items: center; justify-content: center; }
    .wa-head-text { padding: 6px 9px 0; font-weight: 700; font-size: 14.5px; }
    .wa-text { padding: 6px 9px 8px; white-space: pre-wrap; word-wrap: break-word; }
    .wa-text code { font-family: ui-monospace, monospace; font-size: 12.5px; }
    .wa-foot { padding: 0 9px 6px; font-size: 12px; color: #667781; }
    .wa-time { float: right; margin: 4px 0 -4px 10px; font-size: 10.5px; color: #667781; }
    .wa-btn { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 8px; border-top: 1px solid #e9edef; color: #027eb5; font-size: 13.5px; font-weight: 500; }
    .wa-empty { color: #8696a0; font-style: italic; }
    .dark .wa-chat { background: #0b141a; border-color: #222d34; }
    .dark .wa-chat-top { background: #202c33; }
    .dark .wa-chat-avatar { background: #6a7175; color: #e9edef; }
    .dark .wa-bubble { background: #202c33; color: #e9edef; box-shadow: none; }
    .dark .wa-bubble::before { border-right-color: #202c33; }
    .dark .wa-doc { background: #1d282f; }
    .dark .wa-doc-name { color: #e9edef; }
    .dark .wa-doc-meta, .dark .wa-foot, .dark .wa-time { color: #8696a0; }
    .dark .wa-img { background: #2a3942; }
    .dark .wa-btn { border-top-color: #2a3942; color: #53bdeb; }

    /* Tab count badges: High contrast in both active (primary) and inactive states */
    .tab-btn.bg-primary .tab-counter-badge {
        background-color: rgba(0, 0, 0, 0.28) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.35) !important;
    }
    .tab-btn:not(.bg-primary) .tab-counter-badge {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        border: 1px solid #cbd5e1 !important;
    }
    .dark .tab-btn:not(.bg-primary) .tab-counter-badge {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: #f8fafc !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
    }
</style>

<!-- Modal: Add / Edit Pack -->
<div id="packModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModal('packModal')"></div>
    <div class="bg-white dark:bg-card-dark border border-slate-200 dark:border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh] my-auto animate-in fade-in zoom-in-95 duration-200">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-border-dark flex items-center justify-between bg-slate-50/90 dark:bg-secondary/15 shrink-0">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </span>
                <h3 id="packModalTitle" class="text-sm font-bold text-slate-800 dark:text-white">Add Message Pack</h3>
            </div>
            <button type="button" onclick="closeModal('packModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1.5 rounded-lg transition-colors cursor-pointer"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form id="packForm" method="POST" class="flex flex-col flex-1 overflow-hidden m-0">
            @csrf
            <input type="hidden" name="form_type" value="pack">
            <input type="hidden" name="pack_id" id="pack_id">
            <div class="p-5 space-y-4 overflow-y-auto flex-1">
                <div>
                    <label class="{{ $labelClass }}">Pack Name</label>
                    <input type="text" name="name" id="pack_name" required placeholder="Starter — 100 messages" class="{{ $inputClass }}">
                </div>
                <div class="grid grid-cols-2 gap-3.5">
                    <div>
                        <label class="{{ $labelClass }}">Messages (credits)</label>
                        <input type="number" name="credits" id="pack_credits" min="1" required class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Price (INR)</label>
                        <input type="number" step="0.01" min="0" name="price" id="pack_price" required class="{{ $inputClass }}">
                    </div>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Status</label>
                    <select name="status" id="pack_status" class="{{ $inputClass }}">
                        <option value="active">Active (visible to shop owners)</option>
                        <option value="inactive">Inactive (hidden)</option>
                    </select>
                </div>
            </div>
            <div class="px-5 py-3.5 bg-slate-50 dark:bg-secondary/15 border-t border-slate-200 dark:border-border-dark flex justify-end gap-2 shrink-0">
                <button type="button" onclick="closeModal('packModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-all cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl transition-all shadow-xs cursor-pointer">Save Pack</button>
            </div>
        </form>
    </div>
</div>

<script>
const WA_VARIABLES = @json($variables);
const WA_SAMPLES = @json($sampleValues);
const WA_META_LANG = { en: 'en_US', gu: 'gu', hi: 'hi' };
const TEMPLATE_STORE_URL = "{{ route('admin.settings.whatsapp.templates.store') }}";
const TEMPLATE_UPDATE_URL = "{{ route('admin.settings.whatsapp.templates.update', ['id' => ':id']) }}";
const PACK_STORE_URL = "{{ route('admin.settings.whatsapp.packs.store') }}";
const PACK_UPDATE_URL = "{{ route('admin.settings.whatsapp.packs.update', ['id' => ':id']) }}";
let templateVariables = [];

// Tab switching logic
function switchTab(tabId) {
    const tabs = ['setup', 'templates', 'packs', 'webhook'];
    tabs.forEach(t => {
        const content = document.getElementById('tab-content-' + t);
        const btn = document.getElementById('tab-btn-' + t);
        if (content) {
            content.classList.toggle('hidden', t !== tabId);
        }
        if (btn) {
            if (t === tabId) {
                btn.className = 'tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-primary text-white shadow-sm transition-all cursor-pointer whitespace-nowrap shrink-0 text-white-force';
            } else {
                btn.className = 'tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-white dark:bg-card-dark hover:bg-slate-50 dark:hover:bg-secondary/20 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-border-dark transition-all cursor-pointer whitespace-nowrap shrink-0';
            }
        }
    });

    if (history.pushState) {
        history.pushState(null, null, '#' + tabId);
    } else {
        location.hash = '#' + tabId;
    }
}

// Sync verify tokens across Tab 1 and Tab 4
const t1Token = document.getElementById('whatsapp_webhook_verify_token');
const t4Token = document.getElementById('tab_webhook_verify_token');
if (t1Token && t4Token) {
    t1Token.addEventListener('input', () => { t4Token.value = t1Token.value; });
    t4Token.addEventListener('input', () => { t1Token.value = t4Token.value; });
}

window.addEventListener('DOMContentLoaded', () => {
    let hash = window.location.hash.replace('#', '');
    @if($errors->any() && old('form_type') === 'template')
        hash = 'templates';
    @elseif($errors->any() && old('form_type') === 'pack')
        hash = 'packs';
    @endif
    if (['setup', 'templates', 'packs', 'webhook'].includes(hash)) {
        switchTab(hash);
    } else {
        switchTab('setup');
    }
    loadMetaTemplates();
});

window.addEventListener('hashchange', () => {
    const hash = window.location.hash.replace('#', '');
    if (['setup', 'templates', 'packs', 'webhook'].includes(hash)) {
        switchTab(hash);
    }
});

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function openTemplateModal(t = null) {
    const form = document.getElementById('templateForm');
    form.action = t?.id ? TEMPLATE_UPDATE_URL.replace(':id', t.id) : TEMPLATE_STORE_URL;
    document.getElementById('templateModalTitle').innerText = t?.id ? 'Edit Template' : 'Add Template';
    document.getElementById('tpl_id').value = t?.id || '';
    document.getElementById('tpl_key').value = t ? t.key : 'sale_invoice';
    document.getElementById('tpl_language').value = t ? t.language : 'en';
    document.getElementById('tpl_meta_name').value = t ? t.meta_template_name : '';
    document.getElementById('tpl_meta_lang').value = t ? t.meta_language_code : 'en_US';
    document.getElementById('tpl_body').value = t ? t.body : '';
    document.getElementById('tpl_doc').checked = t ? !!t.has_document : false;
    document.getElementById('tpl_pay').checked = t ? !!t.has_pay_button : false;
    document.getElementById('tpl_status').value = t ? t.status : 'active';
    templateVariables = t ? [...(t.variables || [])] : [];
    refreshTemplateForm();
    document.getElementById('templateModal').classList.remove('hidden');
}



function suggestMetaLanguage() {
    document.getElementById('tpl_meta_lang').value = WA_META_LANG[document.getElementById('tpl_language').value] || 'en_US';
}

// One variable picker per numbered placeholder in the body, plus a live preview with sample values.
function refreshTemplateForm() {
    const body = document.getElementById('tpl_body').value;
    const nums = [...body.matchAll(/\{\{(\d+)\}\}/g)].map(m => parseInt(m[1], 10));
    const count = nums.length ? Math.max(...nums) : 0;

    const wrap = document.getElementById('tpl_variables');
    document.querySelectorAll('#tpl_variables select').forEach((sel, i) => templateVariables[i] = sel.value);
    wrap.innerHTML = '';
    for (let i = 0; i < count; i++) {
        const options = Object.entries(WA_VARIABLES).map(([key, v]) =>
            `<option value="${key}" ${templateVariables[i] === key ? 'selected' : ''}>${escapeHtml(v.label)}</option>`).join('');
        wrap.insertAdjacentHTML('beforeend', `
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">&#123;&#123;${i + 1}&#125;&#125; is</label>
                <select name="variables[]" onchange="refreshTemplateForm()" class="block w-full px-3 py-2 bg-white dark:bg-secondary/30 border border-slate-300 dark:border-border-dark rounded-xl text-xs sm:text-sm text-slate-800 dark:text-white shadow-2xs">${options}</select>
            </div>`);
    }
    document.querySelectorAll('#tpl_variables select').forEach((sel, i) => templateVariables[i] = sel.value);

    const noVarsEl = document.getElementById('tpl_no_vars');
    if (noVarsEl) {
        noVarsEl.classList.toggle('hidden', count > 0);
    }

    const isDueReminder = document.getElementById('tpl_key').value === 'due_reminder';
    document.getElementById('tpl_pay_wrap').classList.toggle('hidden', !isDueReminder);
    if (!isDueReminder) document.getElementById('tpl_pay').checked = false;

    document.getElementById('tpl_preview_bubble').innerHTML = renderWaPreview(localPreviewModel({
        body,
        variables: templateVariables.slice(0, count),
        has_document: document.getElementById('tpl_doc').checked,
        has_pay_button: document.getElementById('tpl_pay').checked,
    }));
}

function openPackModal(p = null) {
    document.getElementById('packForm').action = p?.id ? PACK_UPDATE_URL.replace(':id', p.id) : PACK_STORE_URL;
    document.getElementById('packModalTitle').innerText = p?.id ? 'Edit Message Pack' : 'Add Message Pack';
    document.getElementById('pack_id').value = p?.id || '';
    document.getElementById('pack_name').value = p ? p.name : '';
    document.getElementById('pack_credits').value = p ? p.credits : '';
    document.getElementById('pack_price').value = p ? p.price : '';
    document.getElementById('pack_status').value = p ? p.status : 'active';
    document.getElementById('packModal').classList.remove('hidden');
}

function showBanner(ok, text) {
    const banner = document.getElementById('connection-test-result');
    banner.className = 'px-6 py-3 border-b text-xs flex items-center justify-between ' +
        (ok ? 'bg-success/15 border-success/30 text-success' : 'bg-danger/15 border-danger/30 text-danger');
    document.getElementById('connection-test-message').innerText = (ok ? '✓ ' : '✕ ') + text;
}

async function postJson(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify(payload),
    });
    const data = await response.json().catch(() => ({}));
    const message = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Request failed.');
    return { ok: response.ok && data.success, message };
}

async function runConnectionTest() {
    const spinner = document.getElementById('test-spinner');
    const btn = document.getElementById('btn-test-connection');
    spinner.classList.remove('hidden');
    btn.disabled = true;
    try {
        const { ok, message } = await postJson("{{ route('admin.settings.whatsapp.test') }}", {
            phone_number_id: document.getElementById('whatsapp_phone_number_id').value.trim(),
            access_token: document.getElementById('whatsapp_access_token').value.trim(),
            api_version: document.getElementById('whatsapp_api_version').value.trim(),
        });
        showBanner(ok, message);
    } catch (e) {
        showBanner(false, 'Test request failed: ' + e.message);
    } finally {
        spinner.classList.add('hidden');
        btn.disabled = false;
    }
}

// ------------------------------------------------------------ WhatsApp-style preview

const WA_BUTTON_ICONS = {
    URL: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>',
    PHONE_NUMBER: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>',
    QUICK_REPLY: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>',
    COPY_CODE: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
};

// WhatsApp's own formatting: *bold*, _italic_, ~strike~, ```mono```.
function waFormat(text) {
    return escapeHtml(text)
        .replace(/```([\s\S]+?)```/g, '<code>$1</code>')
        .replace(/\*([^*\n]+)\*/g, '<strong>$1</strong>')
        .replace(/(^|[\s(])_([^_\n]+)_/g, '$1<em>$2</em>')
        .replace(/~([^~\n]+)~/g, '<s>$1</s>');
}

/**
 * Draws a template as a WhatsApp chat bubble. model = { headerFormat, headerText, body, footer,
 * buttons: [{type, text}], fill(name) } — fill() returns the sample for a numbered or named placeholder.
 */
function renderWaPreview(model) {
    const fill = (text) => (text || '').replace(/\{\{\s*([a-z0-9_]+)\s*\}\}/gi, (m, name) => model.fill(name) ?? m);
    const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    let media = '';
    if (model.headerFormat === 'DOCUMENT') {
        media = `<div class="wa-media"><div class="wa-doc"><span class="wa-doc-icon">PDF</span><span><span class="wa-doc-name block">INV-1024.pdf</span><span class="wa-doc-meta block">1 page · PDF</span></span></div></div>`;
    } else if (model.headerFormat === 'IMAGE' || model.headerFormat === 'VIDEO') {
        const icon = model.headerFormat === 'VIDEO'
            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>'
            : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>';
        media = `<div class="wa-media"><div class="wa-img"><svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">${icon}</svg></div></div>`;
    }
    const headText = model.headerFormat === 'TEXT' && model.headerText ? `<div class="wa-head-text">${waFormat(fill(model.headerText))}</div>` : '';
    const body = (model.body || '').trim() ? waFormat(fill(model.body)) : '<span class="wa-empty">Type the message body…</span>';
    const footer = model.footer ? `<div class="wa-foot">${escapeHtml(model.footer)}</div>` : '';

    // WhatsApp shows up to 3 buttons, then folds the rest into "See all options".
    let buttons = model.buttons || [];
    if (buttons.length > 3) buttons = [...buttons.slice(0, 2), { type: 'LIST', text: 'See all options' }];
    const buttonHtml = buttons.map(b => `<div class="wa-btn"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">${WA_BUTTON_ICONS[b.type] || '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>'}</svg><span>${escapeHtml(b.text)}</span></div>`).join('');

    return `<div class="wa-chat">
        <div class="wa-chat-top"><span class="wa-chat-avatar">DH</span><span><span class="wa-chat-name block">DukanHisab</span><span class="wa-chat-sub block">Business account</span></span></div>
        <div class="wa-chat-body"><div class="wa-bubble">${media}${headText}<div class="wa-text">${body}<span class="wa-time">${now}</span></div>${footer}${buttonHtml}</div></div>
    </div>`;
}

// One of our rows (or the form): numbered placeholders filled from its chosen variables.
function localPreviewModel(t) {
    const vars = t.variables || [];
    return {
        headerFormat: t.has_document ? 'DOCUMENT' : null,
        body: t.body,
        buttons: t.has_pay_button ? [{ type: 'URL', text: 'Pay Now' }] : [],
        fill: (name) => /^\d+$/.test(name) ? WA_SAMPLES[vars[name - 1]] : undefined,
    };
}

// A template as Meta holds it: linked ones use their variables' samples, others generic ones.
function remotePreviewModel(t) {
    const vars = t.linked_variables || [];
    return {
        headerFormat: t.header_format,
        headerText: t.header_text,
        body: t.body,
        footer: t.footer,
        buttons: t.buttons || [],
        fill: (name) => /^\d+$/.test(name)
            ? (WA_SAMPLES[vars[name - 1]] ?? 'Sample ' + name)
            : (WA_SAMPLES[name] ?? 'Sample ' + name.replace(/_/g, ' ')),
    };
}

function openPreviewModal(model, name) {
    document.getElementById('previewModalName').innerText = name || '';
    document.getElementById('previewModalBubble').innerHTML = renderWaPreview(model);
    document.getElementById('previewModal').classList.remove('hidden');
}

// ------------------------------------------------------------ test send: real Meta templates
const META_TEMPLATES_URL = "{{ route('admin.settings.whatsapp.meta_templates') }}";
let metaTemplates = [];

async function loadMetaTemplates() {
    const select = document.getElementById('test_template');
    const note = document.getElementById('meta-templates-note');
    const spinner = document.getElementById('meta-templates-spinner');
    spinner.classList.add('animate-spin');
    select.innerHTML = '<option value="">Loading templates from Meta...</option>';
    note.className = 'text-[11px] text-slate-500 mt-1 block';
    note.innerText = '';
    try {
        const res = await fetch(META_TEMPLATES_URL, { headers: { 'Accept': 'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) throw new Error(data.message || 'Could not load templates from Meta.');
        metaTemplates = data.templates || [];
        renderMetaTemplateOptions();
        const approved = metaTemplates.filter(t => t.status === 'APPROVED').length;
        note.innerText = `${metaTemplates.length} template(s) in Meta, ${approved} approved.`;
    } catch (e) {
        metaTemplates = [];
        select.innerHTML = '<option value="">No templates loaded</option>';
        note.className = 'text-[11px] text-danger mt-1 block';
        note.innerText = e.message;
    } finally {
        spinner.classList.remove('animate-spin');
        onTestTemplateChange();
    }
}

function renderMetaTemplateOptions() {
    const select = document.getElementById('test_template');
    const option = (t, i) => {
        const status = t.status === 'APPROVED' ? '' : ` — ${metaLabel(t.status)}`;
        const event = t.linked_event ? ` · ${t.linked_event}` : '';
        return `<option value="${i}" ${t.status === 'APPROVED' ? '' : 'disabled'}>${escapeHtml(t.name)} (${escapeHtml(t.language)})${escapeHtml(event)}${escapeHtml(status)}</option>`;
    };
    const groups = [
        ['Linked to a message event', metaTemplates.map((t, i) => [t, i]).filter(([t]) => t.linked_id)],
        ['Other templates in Meta', metaTemplates.map((t, i) => [t, i]).filter(([t]) => !t.linked_id)],
    ];
    select.innerHTML = groups.filter(([, items]) => items.length)
        .map(([label, items]) => `<optgroup label="${label}">${items.map(([t, i]) => option(t, i)).join('')}</optgroup>`).join('')
        || '<option value="">No templates in Meta yet</option>';
    const firstApproved = metaTemplates.findIndex(t => t.status === 'APPROVED');
    if (firstApproved >= 0) select.value = String(firstApproved);
}

function onTestTemplateChange() {
    const t = metaTemplates[document.getElementById('test_template').value];
    const media = t && ['DOCUMENT', 'IMAGE', 'VIDEO'].includes(t.header_format) ? t.header_format : null;
    document.getElementById('test_media_wrap').classList.toggle('hidden', !media);
    if (media) {
        document.getElementById('test_media_label').innerText = 'Sample ' + ({ DOCUMENT: 'PDF', IMAGE: 'image', VIDEO: 'video' })[media] + ' URL (public)';
    }
    document.getElementById('test_preview_wrap').classList.toggle('hidden', !t);
    if (!t) return;
    document.getElementById('test_preview_bubble').innerHTML = renderWaPreview(remotePreviewModel(t));
    document.getElementById('test_preview_meta').innerText = [t.category, t.has_pay_button ? 'Pay Now button' : null, t.linked_event ? 'Linked: ' + t.linked_event : 'Not linked to an event — generic samples are used'].filter(Boolean).join(' · ');
}

async function sendTestMessage() {
    const result = document.getElementById('test-send-result');
    const btn = document.getElementById('btn-test-send');
    btn.disabled = true;
    result.className = 'text-xs text-slate-400';
    result.innerText = 'Sending...';
    try {
        const t = metaTemplates[document.getElementById('test_template').value];
        if (!t) throw new Error('Pick an approved template from Meta first.');
        const { ok, message } = await postJson("{{ route('admin.settings.whatsapp.test_send') }}", {
            phone: document.getElementById('test_phone').value.trim(),
            template_name: t.name,
            language_code: t.language,
            document_url: document.getElementById('test_document_url').value.trim() || null,
        });
        result.className = 'text-xs ' + (ok ? 'text-success' : 'text-danger');
        result.innerText = (ok ? '✓ ' : '✕ ') + message;
    } catch (e) {
        result.className = 'text-xs text-danger';
        result.innerText = '✕ ' + e.message;
    } finally {
        btn.disabled = false;
    }
}

// Live Meta review status: while any template is waiting on Meta, poll our own DB (which the
// webhook keeps current) and update the badges in place.
const META_STATUS_URL = "{{ route('admin.settings.whatsapp.templates.statuses') }}";
const META_WAITING = ['PENDING', 'IN_APPEAL', 'PENDING_DELETION'];
const META_CLASS = {
    APPROVED: 'bg-success/15 text-success',
    PENDING: 'bg-info/15 text-info', IN_APPEAL: 'bg-info/15 text-info', PENDING_DELETION: 'bg-info/15 text-info',
    REJECTED: 'bg-danger/15 text-danger', DISABLED: 'bg-danger/15 text-danger', NOT_IN_META: 'bg-danger/15 text-danger', DELETED: 'bg-danger/15 text-danger',
    PAUSED: 'bg-warning/15 text-warning', FLAGGED: 'bg-warning/15 text-warning',
};
function metaLabel(status) {
    if (!status) return 'Not submitted';
    if (status === 'NOT_IN_META') return 'Not in Meta';
    return status.toLowerCase().replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}
let metaPollTimer = null;
let metaWasWaiting = {};
async function pollMetaStatuses() {
    let data;
    try {
        const res = await fetch(META_STATUS_URL, { headers: { 'Accept': 'application/json' } });
        data = await res.json();
    } catch (e) {
        return scheduleMetaPoll(true);
    }
    let changed = false;
    (data.templates || []).forEach(t => {
        const cell = document.querySelector(`[data-meta-row="${t.id}"]`);
        if (!cell) return;
        const badge = cell.querySelector('[data-meta-badge]');
        if (metaWasWaiting[t.id] && !META_WAITING.includes(t.meta_status)) changed = true;
        metaWasWaiting[t.id] = META_WAITING.includes(t.meta_status);
        badge.className = 'inline-flex px-2 py-0.5 rounded-full font-semibold whitespace-nowrap ' + (META_CLASS[t.meta_status] || 'bg-secondary/20 text-slate-400');
        badge.innerText = metaLabel(t.meta_status);
        const cat = cell.querySelector('[data-meta-category]');
        cat.innerText = t.meta_category || '';
        cat.classList.toggle('hidden', !t.meta_category);
        const reason = cell.querySelector('[data-meta-reason]');
        reason.innerText = t.meta_rejected_reason ? 'Reason: ' + t.meta_rejected_reason : '';
        reason.classList.toggle('hidden', !t.meta_rejected_reason);
        cell.querySelector('[data-meta-synced]').innerText = t.meta_synced_at || '';
    });
    // A decision just arrived: reload so the Submit / Resubmit actions and missing list catch up.
    if (changed) {
        location.hash = 'templates';
        return location.reload();
    }
    scheduleMetaPoll(Object.values(metaWasWaiting).some(Boolean));
}
function scheduleMetaPoll(waiting) {
    const dot = document.getElementById('meta-live-dot');
    const text = document.getElementById('meta-live-text');
    if (waiting) {
        dot.className = 'w-2 h-2 rounded-full bg-success animate-pulse';
        text.innerText = 'Live — waiting for Meta\'s review. Statuses update here automatically.';
        metaPollTimer = setTimeout(pollMetaStatuses, 15000);
    } else {
        dot.className = 'w-2 h-2 rounded-full bg-slate-500';
    }
}
(function initMetaPoll() {
    const statuses = @json($templates->mapWithKeys(fn ($t) => [$t->id => $t->meta_status]));
    Object.entries(statuses).forEach(([id, s]) => metaWasWaiting[id] = META_WAITING.includes(s));
    scheduleMetaPoll(Object.values(metaWasWaiting).some(Boolean));
})();

// Re-open the modal that failed validation, with what the admin typed.
@if($errors->any() && old('form_type') === 'template')
    openTemplateModal({
        id: @json(old('template_id')),
        key: @json(old('key')), language: @json(old('language')),
        meta_template_name: @json(old('meta_template_name')), meta_language_code: @json(old('meta_language_code')),
        body: @json(old('body')), variables: @json(old('variables', [])),
        has_document: @json((bool) old('has_document')), has_pay_button: @json((bool) old('has_pay_button')),
        status: @json(old('status', 'active')),
    });
@elseif($errors->any() && old('form_type') === 'pack')
    openPackModal({
        id: @json(old('pack_id')), name: @json(old('name')), credits: @json(old('credits')),
        price: @json(old('price')), status: @json(old('status', 'active')),
    });
@endif
</script>
@endsection

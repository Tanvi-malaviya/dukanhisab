@extends('layouts.admin')

@section('title', 'WhatsApp Settings')
@section('page_title', 'WhatsApp Messaging')
@section('page_subtitle', 'Meta Cloud API connection, fixed message templates, message-credit packs and webhook configuration')

@section('content')
@php
    $inputClass = 'block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white';
    $labelClass = 'block text-xs font-semibold text-slate-400 uppercase mb-2';
    // Badge colours for Meta's template review states.
    $metaStatusClass = fn ($status) => match ($status) {
        'APPROVED' => 'bg-success/15 text-success',
        'PENDING', 'IN_APPEAL', 'PENDING_DELETION' => 'bg-info/15 text-info',
        'REJECTED', 'DISABLED', 'NOT_IN_META', 'DELETED' => 'bg-danger/15 text-danger',
        'PAUSED', 'FLAGGED' => 'bg-warning/15 text-warning',
        default => 'bg-secondary/20 text-slate-400',
    };
    $metaStatusLabel = fn ($status) => match ($status) {
        null => 'Not submitted',
        'NOT_IN_META' => 'Not in Meta',
        default => ucwords(strtolower(str_replace('_', ' ', $status))),
    };
@endphp
<div class="space-y-3 w-full">

    <!-- 4 Tabs Navigation Bar -->
    <div class="bg-card-dark border border-border-dark p-2 rounded-2xl shadow-xs flex items-center gap-2 overflow-x-auto no-scrollbar">
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
            class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-secondary/15 hover:bg-secondary/30 text-slate-400 hover:text-white border border-border-dark/60 transition-all cursor-pointer whitespace-nowrap shrink-0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
            <span>2. Message Template</span>
            <span class="px-2 py-0.5 text-[11px] font-bold rounded-full {{ count($missing) > 0 ? 'bg-warning/20 text-warning' : 'bg-secondary/20 text-slate-400' }}">{{ count($templates) }}</span>
        </button>

        <!-- Tab 3: Message Pack -->
        <button type="button" onclick="switchTab('packs')" id="tab-btn-packs"
            class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-secondary/15 hover:bg-secondary/30 text-slate-400 hover:text-white border border-border-dark/60 transition-all cursor-pointer whitespace-nowrap shrink-0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <span>3. Message Pack</span>
            <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-secondary/20 text-slate-400">{{ count($packs) }}</span>
        </button>

        <!-- Tab 4: Webhook Setup -->
        <button type="button" onclick="switchTab('webhook')" id="tab-btn-webhook"
            class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-secondary/15 hover:bg-secondary/30 text-slate-400 hover:text-white border border-border-dark/60 transition-all cursor-pointer whitespace-nowrap shrink-0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <span>4. Webhook</span>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-primary/10 text-primary border border-primary/30">Meta API</span>
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
                <label class="{{ $labelClass }}">Template body in Meta</label>
                <div class="rounded-xl bg-secondary/20 border border-border-dark p-4 text-sm text-slate-300 max-w-lg">
                    <span id="test_preview_header" class="hidden block text-primary text-xs mb-1"></span>
                    <span id="test_preview" class="whitespace-pre-line"></span>
                    <span id="test_preview_meta" class="block mt-2 text-[11px] text-slate-500"></span>
                </div>
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
    <div id="tab-content-templates" class="hidden space-y-6">
        @if(count($missing))
            <div class="p-4 rounded-xl bg-warning/10 border border-warning/30 text-warning text-xs space-y-1">
                <div class="flex items-center gap-2 font-semibold">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>{{ count($missing) }} message type(s) have no active template and will not be sent:</span>
                </div>
                <p class="text-slate-300 pl-6">{{ implode(' · ', $missing) }}</p>
            </div>
        @endif

        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-secondary/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-white text-sm flex items-center gap-2">
                        <span>Message Templates</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-primary/20 text-primary font-mono">{{ count($templates) }} Registered</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Create a template here and <strong>Submit to Meta</strong> for review — its status updates live when Meta approves or rejects it. Only approved templates are sent. Shop owners see these read-only.</p>
                    <p class="text-xs text-slate-400 mt-1">Due reminder Pay Now button: create it in Meta as a <strong>URL button with a dynamic suffix</strong> pointing to <code class="text-primary font-mono">{{ $payUrlBase }}@{{1}}</code>.</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <form action="{{ route('admin.settings.whatsapp.templates.sync') }}" method="POST" onsubmit="this.querySelector('button').disabled = true; this.querySelector('span').innerText = 'Syncing...';">
                        @csrf
                        <button type="submit" title="Pull every template's review status from Meta"
                            class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-primary/15 hover:bg-primary/25 text-primary border border-primary/30 transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>Sync from Meta</span>
                        </button>
                    </form>
                    <x-button type="button" onclick="openTemplateModal()" variant="primary" class="whitespace-nowrap">+ Add Template</x-button>
                </div>
            </div>
            <div class="px-6 py-2 border-b border-border-dark text-[11px] text-slate-500 flex items-center gap-2">
                <span id="meta-live-dot" class="w-2 h-2 rounded-full bg-slate-500"></span>
                <span id="meta-live-text">Review results arrive from Meta through the webhook (subscribe to <code>message_template_status_update</code>).</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                            <th class="px-6 py-3">Event</th>
                            <th class="px-6 py-3">Language</th>
                            <th class="px-6 py-3">Meta Template</th>
                            <th class="px-6 py-3">Preview</th>
                            <th class="px-6 py-3">Meta Review</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                        @forelse($templates as $t)
                            <tr class="hover:bg-secondary/10 transition-colors align-top">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-white">{{ $events[$t->key]['label'] ?? $t->key }}</p>
                                    <p class="text-[11px] text-slate-500">To {{ $t->recipient }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-secondary/20 font-medium text-slate-300">
                                        {{ $languages[$t->language] ?? $t->language }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono">
                                    {{ $t->meta_template_name }}
                                    <span class="block text-slate-500">{{ $t->meta_language_code }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs max-w-xs">
                                    @if($t->has_document)<span class="block text-primary mb-1">📄 INV-1024.pdf</span>@endif
                                    <span class="whitespace-pre-line">{{ $t->renderBody($sampleValues) }}</span>
                                    @if($t->has_pay_button)<span class="block mt-1 text-success font-semibold">[ 💳 Pay Now ]</span>@endif
                                </td>
                                <td class="px-6 py-4 text-xs" data-meta-row="{{ $t->id }}">
                                    <span data-meta-badge class="inline-flex px-2 py-0.5 rounded-full font-semibold whitespace-nowrap {{ $metaStatusClass($t->meta_status) }}">{{ $metaStatusLabel($t->meta_status) }}</span>
                                    <span data-meta-category class="block text-slate-500 mt-1 {{ $t->meta_category ? '' : 'hidden' }}">{{ $t->meta_category }}</span>
                                    <span data-meta-reason class="block text-danger mt-1 max-w-[12rem] {{ $t->meta_rejected_reason ? '' : 'hidden' }}">{{ $t->meta_rejected_reason ? 'Reason: ' . $t->meta_rejected_reason : '' }}</span>
                                    @if($t->differsFromMeta())
                                        <span class="block text-warning mt-1">Edited here — resubmit</span>
                                    @endif
                                    <span data-meta-synced class="block text-slate-600 mt-1">{{ $t->meta_synced_at?->diffForHumans() }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $t->status === 'active' ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger' }}">{{ ucfirst($t->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    @if(!$t->meta_template_id || $t->canEditInMeta())
                                        <button type="button" onclick="confirmAction({ actionUrl: '{{ route('admin.settings.whatsapp.templates.submit', $t->id) }}', title: '{{ $t->meta_template_id ? 'Resubmit to Meta' : 'Submit to Meta' }}', message: 'Send “{{ $t->meta_template_name }}” ({{ $t->meta_language_code }}) to Meta for review? It can take from a few minutes up to 24 hours; shops can\'t send it until Meta approves.', buttonText: 'Submit', method: 'POST' })" class="p-1 text-xs font-semibold text-primary hover:underline cursor-pointer">{{ $t->meta_template_id ? 'Resubmit' : 'Submit to Meta' }}</button>
                                    @endif
                                    <button type="button" onclick='openTemplateModal(@json($t))' class="p-1 text-xs text-primary hover:underline cursor-pointer">Edit</button>
                                    <button type="button" onclick="confirmAction({ actionUrl: '{{ route('admin.settings.whatsapp.templates.destroy', $t->id) }}', title: 'Delete Template', message: 'Delete this template? Shops will stop sending this message in this language.', buttonText: 'Delete', variant: 'danger', method: 'DELETE' })" class="p-1 text-xs text-danger hover:underline cursor-pointer">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state colspan="7" title="No templates yet" message="Add a template and submit it to Meta, or click Sync from Meta to pull the ones you already have." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(count($unlinked))
            <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border-dark bg-secondary/10">
                    <h3 class="font-bold text-white text-sm">In Meta, not added here ({{ count($unlinked) }})</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Found in your WhatsApp Business Account during the last sync. Import one to pick its event and map its &#123;&#123;n&#125;&#125; variables.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                            @foreach($unlinked as $u)
                                <tr class="align-top">
                                    <td class="px-6 py-3 text-xs font-mono">{{ $u['name'] }}<span class="block text-slate-500">{{ $u['language'] }}</span></td>
                                    <td class="px-6 py-3 text-xs max-w-md whitespace-pre-line">{{ $u['body'] }}</td>
                                    <td class="px-6 py-3 text-xs">
                                        <span class="inline-flex px-2 py-0.5 rounded-full font-semibold {{ $metaStatusClass($u['status']) }}">{{ $metaStatusLabel($u['status']) }}</span>
                                        <span class="block text-slate-500 mt-1">{{ $u['category'] }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-right whitespace-nowrap">
                                        <button type="button" onclick='importMetaTemplate(@json($u))' class="p-1 text-xs text-primary hover:underline cursor-pointer">Import</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
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
<div id="templateModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal('templateModal')"></div>
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-2xl shadow-2xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 id="templateModalTitle" class="text-sm font-semibold text-white">Add Template</h3>
            <button type="button" onclick="closeModal('templateModal')" class="text-slate-400 hover:text-white cursor-pointer"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form id="templateForm" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="form_type" value="template">
            <input type="hidden" name="template_id" id="tpl_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                <div>
                    <label class="{{ $labelClass }}">Meta Template Name</label>
                    <input type="text" name="meta_template_name" id="tpl_meta_name" required placeholder="due_reminder_gu" class="{{ $inputClass }} font-mono">
                    <span class="text-[11px] text-slate-500 mt-1 block">Lowercase, numbers and underscores. Changing it later creates a new template in Meta.</span>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Meta Language Code</label>
                    <input type="text" name="meta_language_code" id="tpl_meta_lang" required placeholder="en_US" class="{{ $inputClass }} font-mono">
                </div>
            </div>
            <div>
                <label class="{{ $labelClass }}">Body (with &#123;&#123;1&#125;&#125;, &#123;&#123;2&#125;&#125; ... — sent to Meta with the sample values as examples)</label>
                <textarea name="body" id="tpl_body" rows="4" required maxlength="1024" oninput="refreshTemplateForm()" class="{{ $inputClass }}"></textarea>
            </div>
            <div id="tpl_variables" class="grid grid-cols-1 md:grid-cols-2 gap-3"></div>
            <div class="flex flex-wrap gap-6 text-sm text-slate-300">
                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="has_document" value="1" id="tpl_doc" onchange="refreshTemplateForm()"> PDF document header</label>
                <label class="flex items-center gap-2 cursor-pointer" id="tpl_pay_wrap"><input type="checkbox" name="has_pay_button" value="1" id="tpl_pay" onchange="refreshTemplateForm()"> Pay Now button (URL, dynamic suffix)</label>
                <label class="flex items-center gap-2">Status
                    <select name="status" id="tpl_status" class="px-2 py-1 bg-secondary/30 border border-border-dark rounded-lg text-xs text-white">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </label>
            </div>
            <div>
                <label class="{{ $labelClass }}">Preview with sample data</label>
                <div class="rounded-xl bg-secondary/20 border border-border-dark p-4 text-sm text-slate-200 max-w-sm">
                    <span id="tpl_preview_doc" class="hidden block text-primary text-xs mb-1">📄 INV-1024.pdf</span>
                    <span id="tpl_preview" class="whitespace-pre-line"></span>
                    <span id="tpl_preview_pay" class="hidden block mt-2 text-center text-success font-semibold text-xs border-t border-border-dark pt-2">💳 Pay Now</span>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeModal('templateModal')" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="secondary">Save Only</x-button>
                <x-button type="submit" name="submit_to_meta" value="1" variant="primary">Save &amp; Submit to Meta</x-button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add / Edit Pack -->
<div id="packModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal('packModal')"></div>
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 id="packModalTitle" class="text-sm font-semibold text-white">Add Message Pack</h3>
            <button type="button" onclick="closeModal('packModal')" class="text-slate-400 hover:text-white cursor-pointer"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form id="packForm" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="form_type" value="pack">
            <input type="hidden" name="pack_id" id="pack_id">
            <div>
                <label class="{{ $labelClass }}">Pack Name</label>
                <input type="text" name="name" id="pack_name" required placeholder="Starter — 100 messages" class="{{ $inputClass }}">
            </div>
            <div class="grid grid-cols-2 gap-4">
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
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeModal('packModal')" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Save Pack</x-button>
            </div>
        </form>
    </div>
</div>

<script>
const WA_VARIABLES = @json($variables);
const WA_SAMPLES = @json($sampleValues);
const WA_META_LANG = { en: 'en', gu: 'gu', hi: 'hi' };
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
                btn.className = 'tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-secondary/15 hover:bg-secondary/30 text-slate-400 hover:text-white border border-border-dark/60 transition-all cursor-pointer whitespace-nowrap shrink-0';
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
    document.getElementById('tpl_meta_lang').value = t ? t.meta_language_code : 'en';
    document.getElementById('tpl_body').value = t ? t.body : '';
    document.getElementById('tpl_doc').checked = t ? !!t.has_document : false;
    document.getElementById('tpl_pay').checked = t ? !!t.has_pay_button : false;
    document.getElementById('tpl_status').value = t ? t.status : 'active';
    templateVariables = t ? [...(t.variables || [])] : [];
    refreshTemplateForm();
    document.getElementById('templateModal').classList.remove('hidden');
}

// Prefills the Add modal from a template found only in Meta; the admin picks the event and variables.
function importMetaTemplate(u) {
    const appLanguage = Object.keys(WA_META_LANG).find(k => u.language === k || u.language.startsWith(k + '_')) || 'en';
    openTemplateModal({
        key: 'sale_invoice', language: appLanguage,
        meta_template_name: u.name, meta_language_code: u.language,
        body: u.body, variables: [], has_document: u.has_document, has_pay_button: u.has_pay_button, status: 'active',
    });
    document.getElementById('templateModalTitle').innerText = 'Import Template from Meta';
}

function suggestMetaLanguage() {
    document.getElementById('tpl_meta_lang').value = WA_META_LANG[document.getElementById('tpl_language').value] || 'en';
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
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">&#123;&#123;${i + 1}&#125;&#125; is</label>
                <select name="variables[]" onchange="refreshTemplateForm()" class="block w-full px-3 py-2 bg-secondary/30 border border-border-dark rounded-xl text-sm text-white">${options}</select>
            </div>`);
    }
    document.querySelectorAll('#tpl_variables select').forEach((sel, i) => templateVariables[i] = sel.value);

    const preview = body.replace(/\{\{(\d+)\}\}/g, (m, n) => WA_SAMPLES[templateVariables[n - 1]] ?? m);
    document.getElementById('tpl_preview').innerText = preview || '—';

    const isDueReminder = document.getElementById('tpl_key').value === 'due_reminder';
    document.getElementById('tpl_pay_wrap').classList.toggle('hidden', !isDueReminder);
    if (!isDueReminder) document.getElementById('tpl_pay').checked = false;
    document.getElementById('tpl_preview_doc').classList.toggle('hidden', !document.getElementById('tpl_doc').checked);
    document.getElementById('tpl_preview_pay').classList.toggle('hidden', !document.getElementById('tpl_pay').checked);
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
    const header = document.getElementById('test_preview_header');
    header.classList.toggle('hidden', !media);
    header.innerText = media === 'DOCUMENT' ? '📄 INV-1024.pdf' : (media ? '🖼 ' + media.toLowerCase() + ' header' : '');
    document.getElementById('test_preview').innerText = t.body || '—';
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

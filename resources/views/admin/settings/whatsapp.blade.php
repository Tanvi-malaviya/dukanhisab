@extends('layouts.admin')

@section('title', 'WhatsApp Settings')
@section('page_title', 'WhatsApp Messaging')
@section('page_subtitle', 'Meta Cloud API connection, fixed message templates, message-credit packs and webhook configuration')

@section('content')
@php
    $inputClass = 'block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white';
    $labelClass = 'block text-xs font-semibold text-slate-400 uppercase mb-2';
@endphp
<div class="space-y-6 max-w-5xl">

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

        <!-- Tab 4: Webhook Setup & Information -->
        <button type="button" onclick="switchTab('webhook')" id="tab-btn-webhook"
            class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-secondary/15 hover:bg-secondary/30 text-slate-400 hover:text-white border border-border-dark/60 transition-all cursor-pointer whitespace-nowrap shrink-0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <span>4. Webhook Setup & Info</span>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Guide</span>
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: WHATSAPP SETUP                      -->
    <!-- ========================================== -->
    <div id="tab-content-setup" class="space-y-6">
        <!-- Card 1: Cloud API connection -->
        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-secondary/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
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
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }} flex items-center justify-between">
                        <span>Permanent Access Token</span>
                        @if($settings['has_access_token'])
                            <span class="text-[10px] text-emerald-400 font-mono normal-case">Saved ({{ $settings['access_token_hint'] }}) — leave blank to keep</span>
                        @endif
                    </label>
                    <input type="password" name="whatsapp_access_token" id="whatsapp_access_token" autocomplete="off"
                        placeholder="{{ $settings['has_access_token'] ? 'Enter a new token only to replace the saved one' : 'System User access token (EAAG...)' }}"
                        class="{{ $inputClass }} font-mono">
                    <span class="text-[11px] text-slate-500 mt-1 block">Create it in Business Settings &rarr; System Users with the <code>whatsapp_business_messaging</code> permission. Stored encrypted.</span>
                </div>

                <div>
                    <label class="{{ $labelClass }} flex items-center justify-between">
                        <span>App Secret</span>
                        @if($settings['has_app_secret'])
                            <span class="text-[10px] text-emerald-400 font-mono normal-case">Saved — leave blank to keep</span>
                        @endif
                    </label>
                    <input type="password" name="whatsapp_app_secret" id="whatsapp_app_secret" autocomplete="off"
                        placeholder="{{ $settings['has_app_secret'] ? 'Enter a new secret only to replace the saved one' : 'Meta App → App Settings → Basic → App Secret' }}"
                        class="{{ $inputClass }} font-mono">
                    <span class="text-[11px] text-slate-500 mt-1 block">Used to verify that delivery updates really come from Meta. Required for the webhook. Stored encrypted.</span>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Webhook Verify Token</label>
                    <input type="text" name="whatsapp_webhook_verify_token" id="whatsapp_webhook_verify_token"
                        value="{{ old('whatsapp_webhook_verify_token', $settings['whatsapp_webhook_verify_token']) }}" placeholder="Any random string — you will enter the same value in Meta"
                        class="{{ $inputClass }} font-mono">
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-border-dark">
                    <div class="text-xs text-slate-400">Shop owners can only send messages while this is enabled and they have credits.</div>
                    <x-button type="submit" variant="primary">Save WhatsApp Settings</x-button>
                </div>
            </form>
        </div>

        <!-- Card 2: Send a test message -->
        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm p-6 space-y-4">
            <div>
                <h3 class="font-bold text-white text-sm">Send a Test Message</h3>
                <p class="text-xs text-slate-400">Uses the saved settings. Meta's <code>hello_world</code> template works on every new number before your own templates are approved.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $labelClass }}">Mobile Number</label>
                    <input type="text" id="test_phone" placeholder="9876543210" class="{{ $inputClass }} font-mono">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Template</label>
                    <select id="test_template" class="{{ $inputClass }}">
                        <option value="hello_world">hello_world (Meta sample)</option>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}" data-document="{{ $t->has_document ? 1 : 0 }}">
                                {{ $events[$t->key]['label'] ?? $t->key }} — {{ $languages[$t->language] ?? $t->language }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Sample PDF URL <span class="normal-case font-normal">(PDF templates)</span></label>
                    <input type="url" id="test_document_url" placeholder="https://..." class="{{ $inputClass }}">
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
                    <p class="text-xs text-slate-400 mt-0.5">Add each template only after Meta approves it, with exactly the same name, language and body. Shop owners see these read-only.</p>
                    <p class="text-xs text-slate-400 mt-1">Due reminder Pay Now button: create it in Meta as a <strong>URL button with a dynamic suffix</strong> pointing to <code class="text-emerald-400 font-mono">{{ $payUrlBase }}@{{1}}</code>.</p>
                </div>
                <x-button type="button" onclick="openTemplateModal()" variant="primary" class="whitespace-nowrap shrink-0">+ Add Template</x-button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                            <th class="px-6 py-3">Event</th>
                            <th class="px-6 py-3">Language</th>
                            <th class="px-6 py-3">Meta Template</th>
                            <th class="px-6 py-3">Preview</th>
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
                                    @if($t->has_document)<span class="block text-teal-400 mb-1">📄 INV-1024.pdf</span>@endif
                                    <span class="whitespace-pre-line">{{ $t->renderBody($sampleValues) }}</span>
                                    @if($t->has_pay_button)<span class="block mt-1 text-emerald-400 font-semibold">[ 💳 Pay Now ]</span>@endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $t->status === 'active' ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger' }}">{{ ucfirst($t->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <button type="button" onclick='openTemplateModal(@json($t))' class="p-1 text-xs text-primary hover:underline cursor-pointer">Edit</button>
                                    <button type="button" onclick="confirmAction({ actionUrl: '{{ route('admin.settings.whatsapp.templates.destroy', $t->id) }}', title: 'Delete Template', message: 'Delete this template? Shops will stop sending this message in this language.', buttonText: 'Delete', variant: 'danger', method: 'DELETE' })" class="p-1 text-xs text-danger hover:underline cursor-pointer">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state colspan="6" title="No templates yet" message="Add the templates you have approved in Meta WhatsApp Manager." />
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
                <h3 class="text-2xl font-black text-emerald-400 mt-1">{{ $packs->where('status', 'active')->count() }}</h3>
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
    <!-- TAB 4: WEBHOOK SETUP & INFO               -->
    <!-- ========================================== -->
    <div id="tab-content-webhook" class="hidden space-y-6">

        <!-- EDUCATIONAL SECTION: What is the use of Webhook setup? -->
        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-border-dark bg-gradient-to-r from-emerald-500/10 via-primary/5 to-transparent">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-white flex flex-wrap items-center gap-2">
                            <span>What is the use of Webhook Setup?</span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-600 font-semibold border border-emerald-500/30">Webhook સેટઅપ શા માટે જરૂરી છે?</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">
                            Webhook એ <strong>Meta WhatsApp Cloud API</strong> અને તમારા <strong>DukanHisab સર્વર</strong> વચ્ચેનો <strong>રીઅલ-ટાઇમ કનેક્શન પુલ (Real-time Bridge)</strong> છે. જ્યારે DukanHisab પરથી કોઈ ઇન્વોઇસ કે ડ્યુ રિમાઇન્ડર મેસેજ મોકલવામાં આવે છે, ત્યારે Meta તરત સ્વીકારે છે, પરંતુ તે મેસેજ ખરેખર ગ્રાહકના ફોનમાં પહોંચ્યો કે નહીં, વંચાયો કે ફેઈલ થયો તેનો લાઈવ રિપોર્ટ Webhook દ્વારા જ તમારા સર્વર પર પાછો આવે છે.
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">મુખ્ય 4 ફાયદા અને ઉપયોગ (Key Functions of Webhook):</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Benefit 1: Real-time Delivery Status -->
                    <div class="p-4 rounded-xl bg-secondary/10 border border-border-dark/60 space-y-2 hover:border-emerald-500/40 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center text-xs font-bold">1</span>
                            <h4 class="font-bold text-white text-xs">રીઅલ-ટાઇમ ડિલિવરી ટ્રેકિંગ (Live Delivery Status)</h4>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            જ્યારે પણ મેસેજ સેન્ડ થાય, ત્યારે વ્હોટ્સએપમાંથી લાઈવ સ્ટેટ્સ અપડેટ સીધું સર્વર પર નોંધાય છે:
                        </p>
                        <div class="flex flex-wrap gap-2 text-[11px] pt-1">
                            <span class="px-2 py-1 rounded-md bg-slate-700/50 text-slate-300">📤 Sent (મોકલાયો)</span>
                            <span class="px-2 py-1 rounded-md bg-slate-700/50 text-slate-300">📨 Delivered (ડબલ ગ્રે ટીક)</span>
                            <span class="px-2 py-1 rounded-md bg-sky-500/20 text-sky-400">👁️ Read (બ્લુ ટીક)</span>
                            <span class="px-2 py-1 rounded-md bg-rose-500/20 text-rose-400">❌ Failed (અસફળ)</span>
                        </div>
                    </div>

                    <!-- Benefit 2: Automatic Credit Refund -->
                    <div class="p-4 rounded-xl bg-secondary/10 border border-border-dark/60 space-y-2 hover:border-emerald-500/40 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold">2</span>
                            <h4 class="font-bold text-white text-xs">ઓટોમેટિક ક્રેડિટ રિફંડ (Auto Refund on Delivery Failure)</h4>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            જો કોઈ ગ્રાહકનો નંબર ખોટો હોય, નંબર વ્હોટ્સએપ પર ન હોય અથવા ફોન સ્વિચ ઓફ હોય, ત્યારે Meta <strong class="text-rose-400">failed</strong> ઇવેન્ટ મોકલે છે. DukanHisab Webhook દ્વારા આ જાણીને <strong>દુકાનદારના ખાતામાં કપાયેલી ૧ મેસેજ ક્રેડિટ આપોઆપ પરત (Refund)</strong> કરી દે છે, જેથી દુકાનદારનું કોઈ નુકસાન ન થાય!
                        </p>
                    </div>

                    <!-- Benefit 3: Pay Now & Customer Engagement -->
                    <div class="p-4 rounded-xl bg-secondary/10 border border-border-dark/60 space-y-2 hover:border-emerald-500/40 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs font-bold">3</span>
                            <h4 class="font-bold text-white text-xs">ડ્યુ પેમેન્ટ એન્ગેજમેન્ટ (Payment Link & Reports)</h4>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            ડ્યુ રિમાઇન્ડર મેસેજ સાથે મોકલેલ <strong>"💳 Pay Now"</strong> બટન અને કસ્ટમરના રિસ્પોન્સનું ટ્રેકિંગ થાય છે, જેથી દુકાનદારને ખબર પડે કે ગ્રાહકે લિંક જોઈ છે અને ચુકવણીની સ્થિતિ શું છે.
                        </p>
                    </div>

                    <!-- Benefit 4: Meta HMAC Security -->
                    <div class="p-4 rounded-xl bg-secondary/10 border border-border-dark/60 space-y-2 hover:border-emerald-500/40 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs font-bold">4</span>
                            <h4 class="font-bold text-white text-xs">સુરક્ષિત વેરિફિકેશન (HMAC-SHA256 Signature)</h4>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Meta દરેક વેબહુક પેલોડને તમારા <strong>App Secret</strong> વડે સુરક્ષિત રીતે સાઇન કરે છે (<code class="text-slate-300 font-mono">X-Hub-Signature-256</code>). આનાથી સુનિશ્ચિત થાય છે કે કોઈ ફેક કે બોગસ રિક્વેસ્ટ તમારા સર્વર પર ન આવી શકે.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Webhook Configuration & Credentials Card -->
        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-border-dark pb-4">
                <div>
                    <h3 class="font-bold text-white text-sm">Webhook Credentials & Endpoint</h3>
                    <p class="text-xs text-slate-400">Meta App Dashboard માં WhatsApp ➔ Configuration સેક્શનમાં આ URL અને Verify Token સેટ કરો.</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($settings['has_app_secret'])
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> App Secret Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> App Secret Missing
                        </span>
                    @endif
                </div>
            </div>

            <!-- Callback URL Row -->
            <div>
                <label class="{{ $labelClass }}">1. Webhook Callback URL (Copy & Paste into Meta)</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly id="tab-webhook-url-input" value="{{ $webhookUrl }}"
                        class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark rounded-xl text-xs text-emerald-400 font-mono select-all">
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('tab-webhook-url-input').value).then(() => { this.innerText = 'Copied!'; setTimeout(() => this.innerText = 'Copy URL', 2000); })"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-secondary/50 hover:bg-secondary text-white border border-border-dark transition-all cursor-pointer shrink-0">
                        Copy URL
                    </button>
                </div>
                <span class="text-[11px] text-slate-500 mt-1 block">Meta calls this URL to verify the handshake (GET) and push live message delivery receipts (POST).</span>
            </div>

            <!-- Webhook Settings Form -->
            <form action="{{ route('admin.settings.whatsapp.update') }}" method="POST" class="space-y-4 pt-2">
                @csrf
                <!-- Retain Tab 1 settings so backend validation passes without altering them -->
                <input type="hidden" name="whatsapp_enabled" value="{{ $settings['whatsapp_enabled'] }}">
                <input type="hidden" name="whatsapp_api_version" value="{{ $settings['whatsapp_api_version'] }}">
                <input type="hidden" name="whatsapp_phone_number_id" value="{{ $settings['whatsapp_phone_number_id'] }}">
                <input type="hidden" name="whatsapp_waba_id" value="{{ $settings['whatsapp_waba_id'] }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $labelClass }} flex items-center justify-between">
                            <span>2. Webhook Verify Token</span>
                            @if($settings['whatsapp_webhook_verify_token'])
                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('tab_webhook_verify_token').value).then(() => { this.innerText = 'Copied!'; setTimeout(() => this.innerText = 'Copy', 2000); })" class="text-[10px] text-primary hover:underline cursor-pointer">Copy</button>
                            @endif
                        </label>
                        <input type="text" name="whatsapp_webhook_verify_token" id="tab_webhook_verify_token"
                            value="{{ old('whatsapp_webhook_verify_token', $settings['whatsapp_webhook_verify_token']) }}"
                            placeholder="e.g. dukanhisab_secure_webhook_token_2026"
                            class="{{ $inputClass }} font-mono">
                        <span class="text-[11px] text-slate-500 mt-1 block">Any random secret string — enter the exact same string into Meta dashboard.</span>
                    </div>

                    <div>
                        <label class="{{ $labelClass }} flex items-center justify-between">
                            <span>3. Meta App Secret</span>
                            @if($settings['has_app_secret'])
                                <span class="text-[10px] text-emerald-400 font-mono normal-case">Saved — leave blank to keep</span>
                            @endif
                        </label>
                        <input type="password" name="whatsapp_app_secret" autocomplete="off"
                            placeholder="{{ $settings['has_app_secret'] ? 'Enter a new secret only to replace the saved one' : 'Meta App → App Settings → Basic → App Secret' }}"
                            class="{{ $inputClass }} font-mono">
                        <span class="text-[11px] text-slate-500 mt-1 block">Used to verify that delivery updates really come from Meta. Stored encrypted.</span>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-3 border-t border-border-dark">
                    <x-button type="submit" variant="primary">Save Webhook Credentials</x-button>
                </div>
            </form>
        </div>

        <!-- Step-by-Step Meta Developer Portal Setup Guide Card -->
        <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-white text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Meta Developer Portal માં Webhook સેટ કરવાની રીત (Step-by-Step Setup Guide):</span>
            </h3>

            <div class="space-y-3">
                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-secondary/15 border border-border-dark/60">
                    <span class="w-6 h-6 rounded-full bg-primary/20 text-primary flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">1</span>
                    <div class="text-xs text-slate-300">
                        <strong class="text-white">Meta Developer Portal ખોલો:</strong> <a href="https://developers.facebook.com" target="_blank" class="text-primary hover:underline font-semibold">developers.facebook.com</a> પર જાઓ, લોગિન કરો અને તમારું App સિલેક્ટ કરો.
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-secondary/15 border border-border-dark/60">
                    <span class="w-6 h-6 rounded-full bg-primary/20 text-primary flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">2</span>
                    <div class="text-xs text-slate-300">
                        <strong class="text-white">Configuration પેજ પર જાઓ:</strong> ડાબી બાજુના મેનુમાં <strong>WhatsApp</strong> ➔ <strong>Configuration</strong> પર ક્લિક કરો.
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-secondary/15 border border-border-dark/60">
                    <span class="w-6 h-6 rounded-full bg-primary/20 text-primary flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">3</span>
                    <div class="text-xs text-slate-300">
                        <strong class="text-white">Webhook એડિટ કરો:</strong> <strong>Webhook</strong> સેક્શનમાં જઈને <strong>Edit</strong> બટન પર ક્લિક કરો.
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-secondary/15 border border-border-dark/60">
                    <span class="w-6 h-6 rounded-full bg-primary/20 text-primary flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">4</span>
                    <div class="text-xs text-slate-300">
                        <strong class="text-white">URL અને Token પેસ્ટ કરો:</strong> ઉપર દર્શાવેલ <strong class="text-emerald-400">Callback URL</strong> અને <strong class="text-emerald-400">Verify Token</strong> કોપી કરી Meta ના ડાયલોગમાં પેસ્ટ કરો, પછી <strong>Verify and Save</strong> પર ક્લિક કરો.
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-secondary/15 border border-border-dark/60">
                    <span class="w-6 h-6 rounded-full bg-primary/20 text-primary flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">5</span>
                    <div class="text-xs text-slate-300">
                        <strong class="text-white">Messages ફીલ્ડ Subscribe કરો:</strong> તે જ પેજ પર <strong>Webhook fields</strong> પાસે <strong>Manage</strong> પર ક્લિક કરો અને <code class="text-teal-400 font-mono bg-teal-500/10 px-1.5 py-0.5 rounded">messages</code> ઇવેન્ટ શોધીને <strong>Subscribe</strong> કરો.
                    </div>
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
                </div>
                <div>
                    <label class="{{ $labelClass }}">Meta Language Code</label>
                    <input type="text" name="meta_language_code" id="tpl_meta_lang" required placeholder="en_US" class="{{ $inputClass }} font-mono">
                </div>
            </div>
            <div>
                <label class="{{ $labelClass }}">Body (exactly as approved, with &#123;&#123;1&#125;&#125;, &#123;&#123;2&#125;&#125; ...)</label>
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
                    <span id="tpl_preview_doc" class="hidden block text-teal-400 text-xs mb-1">📄 INV-1024.pdf</span>
                    <span id="tpl_preview" class="whitespace-pre-line"></span>
                    <span id="tpl_preview_pay" class="hidden block mt-2 text-center text-emerald-400 font-semibold text-xs border-t border-border-dark pt-2">💳 Pay Now</span>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeModal('templateModal')" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Save Template</x-button>
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
        (ok ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400' : 'bg-rose-500/15 border-rose-500/30 text-rose-400');
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

async function sendTestMessage() {
    const result = document.getElementById('test-send-result');
    const btn = document.getElementById('btn-test-send');
    btn.disabled = true;
    result.className = 'text-xs text-slate-400';
    result.innerText = 'Sending...';
    try {
        const { ok, message } = await postJson("{{ route('admin.settings.whatsapp.test_send') }}", {
            phone: document.getElementById('test_phone').value.trim(),
            template_id: document.getElementById('test_template').value,
            document_url: document.getElementById('test_document_url').value.trim() || null,
        });
        result.className = 'text-xs ' + (ok ? 'text-emerald-400' : 'text-rose-400');
        result.innerText = (ok ? '✓ ' : '✕ ') + message;
    } catch (e) {
        result.className = 'text-xs text-rose-400';
        result.innerText = '✕ ' + e.message;
    } finally {
        btn.disabled = false;
    }
}

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

@extends('layouts.admin')

@section('title', 'Payment Gateway Settings')
@section('page_title', 'Payment Gateway & Razorpay Credentials')
@section('page_subtitle', 'Configure Razorpay API Keys, Webhook Secrets, and Sandbox/Live Modes')

@section('content')
<div class="space-y-6 max-w-5xl">

    <!-- Main Settings Form Card -->
    <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark bg-secondary/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-teal-500 flex items-center justify-center font-black text-sm">
                    ₹
                </div>
                <div>
                    <h3 class="font-bold text-white text-sm">Razorpay API Credentials</h3>
                    <p class="text-xs text-slate-400">Credentials are securely stored in system settings and synchronized with environment configuration.</p>
                </div>
            </div>

            <button type="button" id="btn-test-connection" onclick="runConnectionTest()"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-primary/15 hover:bg-primary/25 text-primary border border-primary/30 transition-all flex items-center gap-2 cursor-pointer">
                <svg id="test-spinner" class="animate-spin h-3.5 w-3.5 hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <svg id="test-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span>Test Connection</span>
            </button>
        </div>

        <!-- Connection Test Result Banner -->
        <div id="connection-test-result" class="hidden px-6 py-3 border-b text-xs flex items-center justify-between">
            <span id="connection-test-message"></span>
            <button type="button" onclick="document.getElementById('connection-test-result').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('admin.settings.payment.update') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Toggle Status & Mode -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">
                        Gateway Enable / Disable
                    </label>
                    <select name="razorpay_enabled" required
                        class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                        <option value="yes" {{ $settings['razorpay_enabled'] === 'yes' ? 'selected' : '' }}>Enabled (Accept Razorpay payments & subscriptions)</option>
                        <option value="no" {{ $settings['razorpay_enabled'] === 'no' ? 'selected' : '' }}>Disabled (Temporarily suspend checkout)</option>
                    </select>
                    <span class="text-[11px] text-slate-500 mt-1 block">When enabled, shop owners can purchase plans and add-ons via Razorpay.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">
                        Operating Environment (Mode)
                    </label>
                    <select name="razorpay_mode" id="razorpay_mode" required
                        class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                        <option value="test" {{ $settings['razorpay_mode'] === 'test' ? 'selected' : '' }}>Test / Sandbox Mode (rzp_test_...)</option>
                        <option value="live" {{ $settings['razorpay_mode'] === 'live' ? 'selected' : '' }}>Live Production Mode (rzp_live_...)</option>
                    </select>
                    <span class="text-[11px] text-slate-500 mt-1 block">Use Test mode for development and testing; switch to Live for real transactions.</span>
                </div>
            </div>

            <!-- API Keys Section -->
            <div class="space-y-4 pt-2 border-t border-border-dark/60">
                <!-- Key ID -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2 flex items-center justify-between">
                        <span>Razorpay Key ID</span>
                        <span class="text-[10px] text-slate-500 font-mono normal-case">Starts with <code>rzp_test_</code> or <code>rzp_live_</code></span>
                    </label>
                    <input type="text" name="razorpay_key_id" id="razorpay_key_id"
                        value="{{ old('razorpay_key_id', $settings['razorpay_key_id']) }}"
                        placeholder="e.g. rzp_test_TBICUACsY1Twmh"
                        class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white font-mono">
                    <span class="text-[11px] text-slate-500 mt-1 block">Found in Razorpay Dashboard &rarr; Settings &rarr; API Keys.</span>
                </div>

                <!-- Key Secret -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">
                        Razorpay Key Secret
                    </label>
                    <div style="position: relative; display: flex; align-items: center; width: 100%;">
                        <input type="password" name="razorpay_key_secret" id="razorpay_key_secret"
                            value="{{ old('razorpay_key_secret', $settings['razorpay_key_secret']) }}"
                            placeholder="e.g. pSTkL5LObwYYYBzlFhLQ5R0A"
                            style="padding-right: 44px;"
                            class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white font-mono">
                        <button type="button" onclick="toggleSecretVisibility('razorpay_key_secret', this)"
                            style="position: absolute; right: 12px; top: 0; bottom: 0; margin: auto; height: 100%; display: flex; align-items: center; justify-content: center; background: transparent; border: none; cursor: pointer; color: #94a3b8; z-index: 10;"
                            onmouseover="this.style.color='#0d9488'"
                            onmouseout="this.style.color='#94a3b8'"
                            title="Toggle visibility">
                            <!-- Eye Open (shown when hidden) -->
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <!-- Eye Slashed (shown when visible) -->
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                            </svg>
                        </button>
                    </div>
                    <span class="text-[11px] text-slate-500 mt-1 block">Your secret key generated alongside the Key ID. Keep this private and confidential.</span>
                </div>

                <!-- Webhook Secret -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">
                        Razorpay Webhook Secret (Optional / Recommended)
                    </label>
                    <div style="position: relative; display: flex; align-items: center; width: 100%;">
                        <input type="password" name="razorpay_webhook_secret" id="razorpay_webhook_secret"
                            value="{{ old('razorpay_webhook_secret', $settings['razorpay_webhook_secret']) }}"
                            placeholder="Enter webhook secret if configured in Razorpay dashboard"
                            style="padding-right: 44px;"
                            class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white font-mono">
                        <button type="button" onclick="toggleSecretVisibility('razorpay_webhook_secret', this)"
                            style="position: absolute; right: 12px; top: 0; bottom: 0; margin: auto; height: 100%; display: flex; align-items: center; justify-content: center; background: transparent; border: none; cursor: pointer; color: #94a3b8; z-index: 10;"
                            onmouseover="this.style.color='#0d9488'"
                            onmouseout="this.style.color='#94a3b8'"
                            title="Toggle visibility">
                            <!-- Eye Open (shown when hidden) -->
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <!-- Eye Slashed (shown when visible) -->
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                            </svg>
                        </button>
                    </div>
                    <span class="text-[11px] text-slate-500 mt-1 block">Used to verify that webhook payload signatures originate genuinely from Razorpay.</span>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-4 border-t border-border-dark">
                <div class="text-xs text-slate-400">
                    Changes take effect immediately across web, mobile API, and tenant checkout.
                </div>
                <x-button type="submit" variant="primary">Save Payment Settings</x-button>
            </div>
        </form>
    </div>

    <!-- Webhook Setup & Information Guide Card -->
    <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm p-6 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-teal-400 flex items-center justify-center font-bold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Razorpay Webhook Endpoint Setup</h4>
                <p class="text-xs text-slate-400">Add this Webhook URL into your Razorpay Dashboard to automate subscription and add-on activations.</p>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Your Webhook URL</label>
            <div class="flex items-center gap-2">
                <input type="text" readonly id="webhook-url-input" value="{{ $webhookUrl }}"
                    class="block w-full px-3.5 py-2.5 bg-secondary/30 border border-border-dark rounded-xl text-xs text-emerald-400 font-mono select-all">
                <button type="button" onclick="copyWebhookUrl()"
                    id="copy-webhook-btn"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-secondary/50 hover:bg-secondary text-white border border-border-dark transition-all cursor-pointer shrink-0">
                    Copy URL
                </button>
            </div>
        </div>

        <div class="bg-secondary/20 rounded-xl p-4 border border-border-dark/60 text-xs text-slate-300 space-y-2">
            <p class="font-semibold text-white">Recommended Razorpay Webhook Events to Subscribe:</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] font-mono">
                <div class="flex items-center gap-2 bg-secondary/40 p-2 rounded-lg">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    <code>order.paid</code>
                </div>
                <div class="flex items-center gap-2 bg-secondary/40 p-2 rounded-lg">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    <code>payment.captured</code>
                </div>
                <div class="flex items-center gap-2 bg-secondary/40 p-2 rounded-lg">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    <code>subscription.charged</code>
                </div>
                <div class="flex items-center gap-2 bg-secondary/40 p-2 rounded-lg">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    <code>subscription.cancelled</code>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function toggleSecretVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const eyeOpen = btn.querySelector('.eye-open');
    const eyeClosed = btn.querySelector('.eye-closed');

    if (input.type === 'password') {
        input.type = 'text';
        if (eyeOpen) eyeOpen.classList.add('hidden');
        if (eyeClosed) eyeClosed.classList.remove('hidden');
    } else {
        input.type = 'password';
        if (eyeOpen) eyeOpen.classList.remove('hidden');
        if (eyeClosed) eyeClosed.classList.add('hidden');
    }
}

function copyWebhookUrl() {
    const input = document.getElementById('webhook-url-input');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = document.getElementById('copy-webhook-btn');
        const orig = btn.innerText;
        btn.innerText = 'Copied!';
        btn.classList.add('bg-emerald-600', 'text-white');
        setTimeout(() => {
            btn.innerText = orig;
            btn.classList.remove('bg-emerald-600', 'text-white');
        }, 2000);
    });
}

async function runConnectionTest() {
    const keyId = document.getElementById('razorpay_key_id').value.trim();
    const keySecret = document.getElementById('razorpay_key_secret').value.trim();
    const banner = document.getElementById('connection-test-result');
    const msg = document.getElementById('connection-test-message');
    const spinner = document.getElementById('test-spinner');
    const icon = document.getElementById('test-icon');
    const btn = document.getElementById('btn-test-connection');

    if (!keyId || !keySecret) {
        banner.className = 'px-6 py-3 border-b text-xs flex items-center justify-between bg-rose-500/15 border-rose-500/30 text-rose-400';
        msg.innerText = 'Please enter both Key ID and Key Secret before testing.';
        banner.classList.remove('hidden');
        return;
    }

    spinner.classList.remove('hidden');
    icon.classList.add('hidden');
    btn.disabled = true;

    try {
        const response = await fetch("{{ route('admin.settings.payment.test') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                key_id: keyId,
                key_secret: keySecret
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            banner.className = 'px-6 py-3 border-b text-xs flex items-center justify-between bg-emerald-500/15 border-emerald-500/30 text-emerald-400';
            msg.innerText = `✓ ${data.message} (${data.mode})`;
        } else {
            banner.className = 'px-6 py-3 border-b text-xs flex items-center justify-between bg-rose-500/15 border-rose-500/30 text-rose-400';
            msg.innerText = `✕ ${data.message || 'Authentication failed.'}`;
        }
        banner.classList.remove('hidden');
    } catch (e) {
        banner.className = 'px-6 py-3 border-b text-xs flex items-center justify-between bg-rose-500/15 border-rose-500/30 text-rose-400';
        msg.innerText = `✕ Test request failed: ${e.message}`;
        banner.classList.remove('hidden');
    } finally {
        spinner.classList.add('hidden');
        icon.classList.remove('hidden');
        btn.disabled = false;
    }
}
</script>
@endsection

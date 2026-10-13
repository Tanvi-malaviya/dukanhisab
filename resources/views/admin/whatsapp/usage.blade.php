@extends('layouts.admin')

@section('title', 'WhatsApp Usage')
@section('page_title', 'WhatsApp Usage')
@section('page_subtitle', 'Messages sent, credit pack sales and per-shop balances')

@section('content')
@php
    $inputClass = 'block w-full px-3.5 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white';
    $labelClass = 'block text-xs font-semibold text-slate-400 uppercase mb-2';
@endphp
<div class="space-y-6">

    <!-- Last-30-days totals -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach([
            ['Messages (' . $periodDays . 'd)', number_format($totals['messages']), 'text-white'],
            ['Delivered / read', number_format($totals['delivered']), 'text-success'],
            ['Failed (refunded)', number_format($totals['failed']), 'text-danger'],
            ['Pack sales (' . $periodDays . 'd)', '₹' . number_format($totals['revenue'], 2) . ' · ' . number_format($totals['credits_sold']) . ' cr', 'text-primary'],
            ['Unused credits (all shops)', number_format($totals['outstanding_credits']), 'text-warning'],
        ] as [$label, $value, $color])
            <div class="bg-card-dark border border-border-dark rounded-2xl p-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                <p class="text-xl font-bold mt-1 {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <!-- Per-shop usage -->
    <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark bg-secondary/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-white text-sm">Shops</h3>
                <p class="text-xs text-slate-400">Credit balance and messages in the last {{ $periodDays }} days.</p>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search shop..." class="px-3 py-1.5 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-xs text-white">
                </form>
                <x-button type="button" onclick="openAdjustModal()" variant="primary" class="whitespace-nowrap flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Assign Credits</span>
                </x-button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                        <th class="px-6 py-3">Shop</th>
                        <th class="px-6 py-3">Owner</th>
                        <th class="px-6 py-3">Credits Left</th>
                        <th class="px-6 py-3">Sent ({{ $periodDays }}d)</th>
                        <th class="px-6 py-3">Failed</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                    @forelse($shops as $shop)
                        <tr class="hover:bg-secondary/10 transition-colors">
                            <td class="px-6 py-3 font-semibold text-white">{{ $shop->name }} <span class="block text-[11px] text-slate-500 font-normal">ID #{{ $shop->id }}</span></td>
                            <td class="px-6 py-3 text-xs">{{ $shop->owner?->name }} <span class="block text-slate-500">{{ $shop->owner?->email ?? $shop->owner?->mobile }}</span></td>
                            <td class="px-6 py-3 font-mono text-xs {{ $shop->balance < 20 ? 'text-warning font-semibold' : 'text-emerald-400 font-bold' }}">{{ number_format($shop->balance) }}</td>
                            <td class="px-6 py-3 font-mono text-xs">{{ number_format($shop->sent) }}</td>
                            <td class="px-6 py-3 font-mono text-xs {{ $shop->failed ? 'text-danger font-semibold' : '' }}">{{ number_format($shop->failed) }}</td>
                            <td class="px-6 py-3 text-right">
                                <button type="button" onclick="openAdjustModal({{ $shop->id }}, '{{ addslashes($shop->name) }}', {{ (int)$shop->balance }})" class="px-2.5 py-1 bg-primary/10 hover:bg-primary/20 text-primary border border-primary/20 rounded-lg text-xs font-semibold transition-all inline-flex items-center gap-1 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <span>Assign Credits</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state colspan="6" title="No WhatsApp usage yet" message="Shops appear here once they buy credits or send a message." />
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination :records="$shops" />
    </div>

    <!-- Recent pack purchases -->
    <div class="bg-card-dark border border-border-dark rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark bg-secondary/10">
            <h3 class="font-bold text-white text-sm">Recent Pack Purchases</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Shop</th>
                        <th class="px-6 py-3">Bought by</th>
                        <th class="px-6 py-3">Pack</th>
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3">Razorpay Payment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                    @forelse($purchases as $p)
                        <tr>
                            <td class="px-6 py-3 text-xs font-mono">{{ $p->paid_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-3 text-xs">{{ $p->shop?->name }}</td>
                            <td class="px-6 py-3 text-xs">{{ $p->user?->name }}</td>
                            <td class="px-6 py-3 text-xs">{{ $p->pack_name }} <span class="text-slate-500">({{ number_format($p->credits) }} credits)</span></td>
                            <td class="px-6 py-3 text-xs font-mono">₹{{ number_format($p->amount, 2) }}</td>
                            <td class="px-6 py-3 text-xs font-mono text-slate-500">{{ $p->razorpay_payment_id }}</td>
                        </tr>
                    @empty
                        <x-empty-state colspan="6" title="No pack purchases yet" message="Paid message packs will appear here." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Assign / Adjust credits -->
<div id="adjustModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAdjustModal()"></div>
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Assign WhatsApp Credits</span>
            </h3>
            <button type="button" onclick="closeAdjustModal()" class="text-slate-400 hover:text-white cursor-pointer"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form action="{{ route('admin.whatsapp.usage.adjust') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="shop_id" id="adjust_shop_id_hidden" value="{{ old('shop_id') }}">

            <!-- Dropdown mode (shown when opened via top common "Assign Credits" button) -->
            <div id="shop_select_wrapper">
                <label class="{{ $labelClass }}">Shop</label>
                <select id="adjust_shop" class="{{ $inputClass }}" onchange="updateShopInfo()">
                    @foreach($allShops as $s)
                        <option value="{{ $s->id }}" @selected(old('shop_id') == $s->id)>{{ $s->name }} (#{{ $s->id }})</option>
                    @endforeach
                </select>
                <div id="shop_balance_badge" class="mt-1.5 hidden text-xs text-slate-400">
                    Current balance: <span id="shop_balance_val" class="font-mono font-bold text-emerald-400">0</span> messages
                </div>
            </div>

            <!-- Single shop display mode (when opened for a particular shop - only name, no dropdown) -->
            <div id="shop_fixed_wrapper" class="hidden">
                <span class="{{ $labelClass }}">Shop</span>
                <div class="px-4 py-3 bg-secondary/30 border border-border-dark rounded-xl flex items-center justify-between">
                    <div>
                        <span id="shop_fixed_name" class="font-bold text-white text-sm">Shop Name</span>
                    </div>
                    <div class="text-xs text-right">
                        <span class="text-slate-400">Current balance: </span>
                        <span id="shop_fixed_balance" class="font-mono font-bold text-emerald-400">20</span>
                        <span class="text-slate-400 text-xs"> messages</span>
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="{{ $labelClass }} mb-0">Credits to Assign (+ to add, − to remove)</label>
                </div>
                <input type="number" name="credits" id="adjust_credits" value="{{ old('credits', 20) }}" required class="{{ $inputClass }}" placeholder="e.g. 20 or 50">
                <!-- Preset chips -->
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <span class="text-[11px] text-slate-400 self-center mr-1">Quick add:</span>
                    @foreach([10, 20, 50, 100, 200, 500] as $preset)
                        <button type="button" onclick="setCredits({{ $preset }})" class="px-2 py-0.5 rounded-lg bg-secondary/50 hover:bg-primary/20 hover:text-primary text-slate-300 border border-border-dark text-[11px] font-semibold transition-colors cursor-pointer">+{{ $preset }}</button>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="{{ $labelClass }}">Reason / Note</label>
                <input type="text" name="note" id="adjust_note" value="{{ old('note', 'Admin assigned credits') }}" required maxlength="200" class="{{ $inputClass }}" placeholder="e.g. Admin assigned credits or Welcome bonus">
                <!-- Preset reasons -->
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @foreach(['Welcome Bonus', 'Admin Topup', 'Support Compensation', 'Promotional Pack'] as $reason)
                        <button type="button" onclick="setReason('{{ $reason }}')" class="px-2 py-0.5 rounded-lg bg-secondary/30 hover:bg-secondary/60 text-slate-400 text-[10px] transition-colors cursor-pointer">{{ $reason }}</button>
                    @endforeach
                </div>
            </div>
            <p class="text-[11px] text-slate-500">Recorded directly in the shop's credit history and admin audit log.</p>
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeAdjustModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Assign Credits</x-button>
            </div>
        </form>
    </div>
</div>

<script>
    const shopBalances = {
        @foreach($allShops as $s)
            {{ $s->id }}: {{ (int)($s->whatsappWallet?->balance ?? 20) }},
        @endforeach
    };

    function setCredits(val) {
        document.getElementById('adjust_credits').value = val;
    }
    function setReason(txt) {
        document.getElementById('adjust_note').value = txt;
    }
    function updateShopInfo() {
        const val = document.getElementById('adjust_shop').value;
        document.getElementById('adjust_shop_id_hidden').value = val;
        if (shopBalances && shopBalances[val] !== undefined) {
            document.getElementById('shop_balance_val').innerText = shopBalances[val];
            document.getElementById('shop_balance_badge').classList.remove('hidden');
        }
    }
    function openAdjustModal(shopId = null, shopName = null, currentBalance = null) {
        const hiddenInput = document.getElementById('adjust_shop_id_hidden');
        const selectWrapper = document.getElementById('shop_select_wrapper');
        const fixedWrapper = document.getElementById('shop_fixed_wrapper');
        const select = document.getElementById('adjust_shop');

        if (shopId) {
            // Particular shop clicked: NO dropdown! Only show shop name & balance
            selectWrapper.classList.add('hidden');
            fixedWrapper.classList.remove('hidden');

            document.getElementById('shop_fixed_name').innerText = shopName + ' (#' + shopId + ')';
            const bal = currentBalance !== null ? currentBalance : (shopBalances[shopId] ?? 0);
            document.getElementById('shop_fixed_balance').innerText = bal;
            hiddenInput.value = shopId;
        } else {
            // Common button clicked: show dropdown
            fixedWrapper.classList.add('hidden');
            selectWrapper.classList.remove('hidden');

            hiddenInput.value = select.value;
            updateShopInfo();
        }
        document.getElementById('adjustModal').classList.remove('hidden');
    }
    function closeAdjustModal() {
        document.getElementById('adjustModal').classList.add('hidden');
    }
    @if($errors->any())
        openAdjustModal();
    @endif
</script>
@endsection

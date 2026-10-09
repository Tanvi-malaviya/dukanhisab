@extends('layouts.admin')

@section('title', 'WhatsApp Usage')
@section('page_title', 'WhatsApp Usage')
@section('page_subtitle', 'Messages sent, credit pack sales and per-shop balances')

@section('content')
@php
    $inputClass = 'block w-full px-3.5 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white';
    $labelClass = 'block text-xs font-semibold text-slate-400 uppercase mb-2';
@endphp
<div class="space-y-6">

    <!-- Last-30-days totals -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach([
            ['Messages (' . $periodDays . 'd)', number_format($totals['messages']), 'text-white'],
            ['Delivered / read', number_format($totals['delivered']), 'text-emerald-400'],
            ['Failed (refunded)', number_format($totals['failed']), 'text-rose-400'],
            ['Pack sales (' . $periodDays . 'd)', '₹' . number_format($totals['revenue'], 2) . ' · ' . number_format($totals['credits_sold']) . ' cr', 'text-teal-400'],
            ['Unused credits (all shops)', number_format($totals['outstanding_credits']), 'text-amber-400'],
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search shop..." class="px-3 py-1.5 bg-secondary/40 border border-border-dark rounded-xl text-xs text-white">
                </form>
                <x-button type="button" onclick="openAdjustModal()" variant="primary" class="whitespace-nowrap">Adjust Credits</x-button>
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
                            <td class="px-6 py-3 font-mono text-xs {{ $shop->balance < 20 ? 'text-amber-400' : '' }}">{{ number_format($shop->balance) }}</td>
                            <td class="px-6 py-3 font-mono text-xs">{{ number_format($shop->sent) }}</td>
                            <td class="px-6 py-3 font-mono text-xs {{ $shop->failed ? 'text-rose-400' : '' }}">{{ number_format($shop->failed) }}</td>
                            <td class="px-6 py-3 text-right">
                                <button type="button" onclick="openAdjustModal({{ $shop->id }})" class="text-xs text-primary hover:underline cursor-pointer">Adjust</button>
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

<!-- Modal: Adjust credits -->
<div id="adjustModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAdjustModal()"></div>
    <div class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
            <h3 class="text-sm font-semibold text-white">Adjust WhatsApp Credits</h3>
            <button type="button" onclick="closeAdjustModal()" class="text-slate-400 hover:text-white cursor-pointer"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form action="{{ route('admin.whatsapp.usage.adjust') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="{{ $labelClass }}">Shop</label>
                <select name="shop_id" id="adjust_shop" required class="{{ $inputClass }}">
                    @foreach($allShops as $s)
                        <option value="{{ $s->id }}" @selected(old('shop_id') == $s->id)>{{ $s->name }} (#{{ $s->id }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelClass }}">Credits (+ to add, − to remove)</label>
                <input type="number" name="credits" value="{{ old('credits') }}" required class="{{ $inputClass }}" placeholder="e.g. 50 or -10">
            </div>
            <div>
                <label class="{{ $labelClass }}">Reason</label>
                <input type="text" name="note" value="{{ old('note') }}" required maxlength="200" class="{{ $inputClass }}" placeholder="e.g. Compensation for failed delivery on 1 Oct">
            </div>
            <p class="text-[11px] text-slate-500">Recorded in the shop's credit history and the audit log.</p>
            <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                <x-button type="button" onclick="closeAdjustModal()" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Save</x-button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAdjustModal(shopId = null) {
        if (shopId) document.getElementById('adjust_shop').value = shopId;
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

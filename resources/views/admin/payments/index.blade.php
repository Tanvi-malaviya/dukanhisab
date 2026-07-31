@extends('layouts.admin')

@section('title', 'Payments Ledger')
@section('page_title', 'Payments  Ledger')

@section('content')
    <div class="space-y-4">

        <!-- Metrics Cards Grid -->
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
            <!-- Successful Transactions count -->
            <div class="p-6 rounded-2xl shadow-sm border relative overflow-hidden"
                style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(209, 250, 229, 0.25) 100%); border-color: rgba(16, 185, 129, 0.2);">
                <p class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Successful Invoices
                </p>
                <h3 class="text-2xl font-black text-emerald-950 mt-2">{{ number_format($successCount) }} Transactions</h3>
                <div class="mt-2 text-xs text-emerald-800/80 font-medium">Processed by Stripe & Razorpay</div>
            </div>

            <!-- Failed Transactions count -->
            <div class="p-6 rounded-2xl shadow-sm border relative overflow-hidden"
                style="background: linear-gradient(135deg, rgba(244, 63, 94, 0.1) 0%, rgba(254, 226, 226, 0.25) 100%); border-color: rgba(244, 63, 94, 0.2);">
                <p class="text-[11px] font-bold text-rose-800 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Failed Checkouts
                </p>
                <h3 class="text-2xl font-black text-rose-950 mt-2">{{ number_format($failedCount) }} Drops</h3>
                <div class="mt-2 text-xs text-rose-800/80 font-medium">Checkout abandonments / gateway errors</div>
            </div>

            <!-- Total Refunded Sum -->
            <div class="p-6 rounded-2xl shadow-sm border relative overflow-hidden"
                style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(254, 243, 199, 0.25) 100%); border-color: rgba(245, 158, 11, 0.2);">
                <p class="text-[11px] font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Total Capital Refunded
                </p>
                <h3 class="text-2xl font-black text-amber-950 mt-2">₹{{ number_format($refundedSum, 2) }}</h3>
                <div class="mt-2 text-xs text-amber-800/80 font-medium">Reversed payments in last 30 days</div>
            </div>
        </div>

        <x-search-filter :action="route('admin.payments.index')" placeholder="Search transaction ID, shop, user..."
            :show-reset="false">
            <div class="w-full md:w-48">
                <select name="status"
                    class="block w-full px-3 py-2 bg-secondary/30 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-slate-700">
                    <option value="">All Statuses</option>
                    <option value="successful" {{ request('status') === 'successful' ? 'selected' : '' }}>Successful</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
        </x-search-filter>

        <!-- Payments Ledger -->
        <div class="space-y-2">
            <h2 class="text-lg font-bold text-white">Payment Transactions Log</h2>

            <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                                <th class="px-6 py-4">Transaction details</th>
                                <th class="px-6 py-4">Shop details</th>
                                <th class="px-6 py-4">Gateway / ID</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                            @forelse($payments as $pay)
                                <tr class="hover:bg-secondary/10 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-white">{{ $pay->plan->name }}</p>
                                        <p class="text-xs text-slate-500 font-mono">Date:
                                            {{ $pay->payment_date->format('Y-m-d H:i') }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-white">{{ $pay->shop->name }}</p>
                                        <p class="text-xs text-slate-500">Client: {{ $pay->user->name }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs uppercase font-medium text-white">{{ $pay->payment_gateway }}</p>
                                        @if(strtolower($pay->payment_gateway) !== 'manual')
                                            <p class="text-xs font-mono text-slate-500">{{ $pay->transaction_id }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-white">
                                        ₹{{ number_format($pay->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $pay->status === 'successful' ? 'bg-success/15 text-success' : ($pay->status === 'failed' ? 'bg-danger/15 text-danger' : 'bg-warning/15 text-warning') }}">
                                            {{ ucfirst($pay->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($pay->status === 'successful')
                                            <button
                                                onclick="openRefundModal({{ $pay->id }}, '{{ $pay->transaction_id }}', '{{ number_format($pay->amount, 2) }}')"
                                                class="px-3 py-1 bg-danger/10 hover:bg-danger text-danger hover:text-white rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                                                Refund
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-500 italic">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500 italic">No payments found in
                                        platform logs.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-pagination :records="$payments" />
            </div>
        </div>

        <!-- Refunds History Section -->
        <div class="space-y-4 pt-4">
            <h2 class="text-lg font-bold text-white">Processed Refunds</h2>

            <div class="bg-card-dark border border-border-dark rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-secondary/40 border-b border-border-dark text-[11px] font-semibold uppercase text-slate-400 tracking-wider">
                                <th class="px-6 py-4">Refund Details</th>
                                <th class="px-6 py-4">Shop details</th>
                                <th class="px-6 py-4">Original TXN</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark text-sm text-slate-300">
                            @forelse($refunds as $ref)
                                <tr class="hover:bg-secondary/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-white">#REF-{{ $ref->id }}</p>
                                        <p class="text-xs text-slate-500">{{ $ref->created_at->format('M d, Y') }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-white">{{ $ref->payment->shop->name }}</p>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs text-slate-400">
                                        {{ $ref->payment->transaction_id ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-white">
                                        ₹{{ number_format($ref->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($ref->status === 'successful')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-success/15 text-success">
                                                Successful
                                            </span>
                                        @elseif($ref->status === 'failed')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-danger/15 text-danger">
                                                Failed
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-warning/15 text-warning">
                                                {{ ucfirst($ref->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($ref->status === 'pending')
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('admin.refunds.update_status', $ref->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="successful">
                                                <button type="submit" class="px-2 py-0.5 bg-success/10 hover:bg-success text-success hover:text-white rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.refunds.update_status', $ref->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="failed">
                                                <button type="submit" class="px-2 py-0.5 bg-danger/10 hover:bg-danger text-danger hover:text-white rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                                                    Fail
                                                </button>
                                            </form>
                                        </div>
                                        @else
                                        <span class="text-xs text-slate-500 italic">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state
                                    colspan="6"
                                    title="No refunds processed"
                                    message="No refund requests or records were found in the log history."
                                />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-pagination :records="$refunds" />
            </div>
        </div>

    </div>

    <!-- Modal: Trigger Refund Form -->
    <div id="refundModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeRefundModal()"></div>
        <div
            class="bg-card-dark border border-border-dark rounded-2xl w-full max-w-md shadow-2xl relative z-10 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-secondary/20">
                <h3 class="text-sm font-semibold text-white">Process Transaction Refund</h3>
                <button onclick="closeRefundModal()" class="text-slate-400 hover:text-white"><svg class="w-5 h-5"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button>
            </div>

            <form id="refundForm" method="POST" class="p-6 space-y-4">
                @csrf
                <p class="text-xs text-slate-400">Requesting a full reversal of <span class="text-white font-semibold"
                        id="refund_tx_amount">₹0.00</span> for transaction ID <span class="text-white font-mono"
                        id="refund_tx_id">tx_id</span>.</p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Refund Status</label>
                        <select name="status" required class="block w-full px-3 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white">
                            <option value="successful">Successful (Refunded)</option>
                            <option value="pending">Pending / Processing</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Reason for Refund</label>
                        <textarea name="reason" required rows="3"
                            placeholder="Provide reason e.g. Customer cancelled subscription within 24 hours..."
                            class="block w-full px-3 py-2 bg-secondary/40 border border-border-dark focus:border-primary focus:outline-none rounded-xl text-sm text-white placeholder-slate-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-border-dark">
                    <x-button type="button" onclick="closeRefundModal()" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="danger">Submit Refund</x-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRefundModal(id, txId, amount) {
           document.getElementById('refundForm').action = "{{ route('admin.payments.refund', ['id' => ':id']) }}".replace(':id', id);
            document.getElementById('refund_tx_id').innerText = txId;
            document.getElementById('refund_tx_amount').innerText = "₹" + amount;
            document.getElementById('refundModal').classList.remove('hidden');
        }
        function closeRefundModal() {
            document.getElementById('refundModal').classList.add('hidden');
        }
    </script>
@endsection
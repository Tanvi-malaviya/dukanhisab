<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('pay_title', ['shop' => $shop->name]) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="min-h-full flex items-start sm:items-center justify-center p-4">
<main class="w-full max-w-md bg-white rounded-3xl shadow-lg border border-slate-200 overflow-hidden">

    <header class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-5 text-white text-center">
        @if($shop->logo)
            <img src="{{ url('storage/' . ltrim($shop->logo, '/')) }}" alt="" class="h-14 w-14 rounded-full object-cover mx-auto mb-2 border-2 border-white/40 bg-white">
        @endif
        <h1 class="text-xl font-extrabold">{{ $shop->name }}</h1>
        @if($shop->mobile)
            <p class="text-xs opacity-90">{{ $shop->mobile }}</p>
        @endif
    </header>

    <section class="p-6 space-y-5">
        <p class="text-sm text-slate-600">{{ __('pay_hello', ['name' => $customer->name]) }}</p>

        @if($state === 'confirmed')
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5 text-center">
                <p class="text-3xl">✅</p>
                <p class="mt-2 font-bold text-emerald-800">{{ __('pay_confirmed') }}</p>
            </div>
        @elseif($state === 'claimed')
            <div class="rounded-2xl bg-sky-50 border border-sky-200 p-5 text-center space-y-1">
                <p class="text-3xl">⏳</p>
                <p class="font-bold text-sky-800">{{ __('pay_claim_received', ['shop' => $shop->name]) }}</p>
                <p class="text-xs text-sky-700">UTR {{ $link->claimed_utr }} · ₹{{ number_format($link->claimed_amount, 2) }}</p>
            </div>
        @elseif($state === 'settled')
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5 text-center">
                <p class="text-3xl">🙏</p>
                <p class="mt-2 font-bold text-emerald-800">{{ __('pay_nothing_due') }}</p>
            </div>
        @elseif($state === 'expired')
            <div class="rounded-2xl bg-slate-100 p-5 text-center">
                <p class="font-bold text-slate-700">{{ __('pay_expired') }}</p>
                @if($shop->mobile)<a href="tel:{{ $shop->mobile }}" class="mt-2 inline-block text-sm font-bold text-emerald-700">📞 {{ $shop->mobile }}</a>@endif
            </div>
        @else
            <div class="text-center">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('pay_amount_due') }}</p>
                <p class="text-4xl font-extrabold text-slate-900">₹{{ number_format($due, 2) }}</p>
            </div>

            @if($state === 'no_upi')
                <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-center text-sm text-amber-800">
                    {{ __('pay_no_upi', ['shop' => $shop->name]) }}
                    @if($shop->mobile)<a href="tel:{{ $shop->mobile }}" class="mt-2 block font-bold">📞 {{ $shop->mobile }}</a>@endif
                </div>
            @else
                @if($state === 'rejected')
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 text-xs text-rose-700">{{ __('pay_claim_rejected') }}</div>
                @endif

                <a id="pay-btn" href="{{ $upiUri }}"
                    class="block w-full text-center py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-lg font-extrabold shadow">
                    {{ __('pay_with_upi', ['amount' => number_format($due, 2)]) }}
                </a>
                <p class="text-center text-[11px] text-slate-400 -mt-3">Google Pay · PhonePe · Paytm · BHIM</p>

                <details class="text-sm">
                    <summary class="cursor-pointer font-semibold text-emerald-700">{{ __('pay_other_amount') }}</summary>
                    <div class="mt-2 flex gap-2">
                        <input id="custom-amount" type="number" min="1" step="0.01" inputmode="decimal" placeholder="₹"
                            class="flex-1 px-3 py-2 border border-slate-300 rounded-xl">
                        <a id="custom-pay" href="#" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold">{{ __('pay_pay') }}</a>
                    </div>
                </details>

                <div class="text-center space-y-2">
                    <p class="text-xs text-slate-500">{{ __('pay_scan_qr') }}</p>
                    <div id="qr" class="inline-block p-2 bg-white border border-slate-200 rounded-xl"></div>
                    <p class="text-xs text-slate-500 font-mono">UPI: {{ $shop->upi_id }}</p>
                </div>

                {{-- After paying: report it with the UTR so the shop can confirm --}}
                <form method="POST" action="{{ route('pay.claim', $link->token) }}" class="border-t border-slate-200 pt-5 space-y-3">
                    @csrf
                    <p class="font-bold text-slate-800">{{ __('pay_already_paid') }}</p>
                    <p class="text-xs text-slate-500">{{ __('pay_utr_help') }}</p>
                    @if($errors->any())
                        <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 text-xs text-rose-700">{{ $errors->first() }}</div>
                    @endif
                    <input name="utr" value="{{ old('utr') }}" required maxlength="30" autocomplete="off" placeholder="{{ __('pay_utr_placeholder') }}"
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-xl font-mono uppercase">
                    <input name="amount" type="number" min="1" step="0.01" inputmode="decimal" required value="{{ old('amount', number_format($due, 2, '.', '')) }}"
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-xl">
                    <button type="submit" class="w-full py-3 rounded-xl bg-slate-900 text-white font-bold">{{ __('pay_i_have_paid') }}</button>
                </form>
            @endif
        @endif
    </section>

    <footer class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-center text-[11px] text-slate-400">
        {{ __('pay_powered_by') }} <span class="font-bold text-slate-500">DukanHisab</span>
    </footer>
</main>

@if($upiUri)
<script>
    (function () {
        const base = @json($upiUri);
        const withAmount = (amount) => base.replace(/([?&])am=[^&]*/, '$1am=' + Number(amount).toFixed(2));
        if (window.QRCode) new QRCode(document.getElementById('qr'), { text: base, width: 160, height: 160 });
        document.getElementById('custom-pay').addEventListener('click', function (e) {
            const amount = parseFloat(document.getElementById('custom-amount').value);
            if (!(amount > 0)) { e.preventDefault(); return; }
            this.href = withAmount(amount);
        });
    })();
</script>
@endif
</body>
</html>

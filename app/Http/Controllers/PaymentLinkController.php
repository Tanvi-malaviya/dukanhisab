<?php

namespace App\Http\Controllers;

use App\Models\PaymentLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Public "Pay Now" page opened from a WhatsApp due reminder. No login: the random token is the
 * only key. It opens the customer's UPI app with the shop's UPI ID, and lets the customer report
 * a payment (UTR) for the shop owner to confirm.
 */
class PaymentLinkController extends Controller
{
    public function show(string $token)
    {
        $link = PaymentLink::with('shop.owner', 'customer')->where('token', $token)->firstOrFail();
        App::setLocale($link->shop->owner?->language ?: 'en');

        $due = max(0, (float) $link->customer->net_balance);

        return view('pay.show', [
            'link' => $link,
            'shop' => $link->shop,
            'customer' => $link->customer,
            'due' => $due,
            'upiUri' => $link->shop->upi_id && $due > 0 ? $link->upiUri($due) : null,
            'state' => $this->state($link, $due),
        ]);
    }

    public function claim(Request $request, string $token)
    {
        $link = PaymentLink::with('shop.owner', 'customer')->where('token', $token)->firstOrFail();
        App::setLocale($link->shop->owner?->language ?: 'en');

        if (!in_array($this->state($link, max(0, (float) $link->customer->net_balance)), ['payable', 'rejected'], true)) {
            return redirect()->route('pay.show', $token);
        }

        $data = $request->validate([
            'utr' => ['required', 'regex:/^[A-Za-z0-9]{6,30}$/'],
            'amount' => 'required|numeric|min:1|max:10000000',
        ], [
            'utr.regex' => __('pay_utr_invalid'),
        ]);

        $link->update([
            'status' => 'claimed',
            'claimed_utr' => strtoupper($data['utr']),
            'claimed_amount' => $data['amount'],
            'claimed_at' => now(),
            'resolved_at' => null,
        ]);

        return redirect()->route('pay.show', $token);
    }

    /** What the page should show: payable, claimed, confirmed, rejected, expired, settled or no_upi. */
    private function state(PaymentLink $link, float $due): string
    {
        if (in_array($link->status, ['claimed', 'confirmed'], true)) {
            return $link->status;
        }
        if ($link->isExpired()) {
            return 'expired';
        }
        if ($due <= 0) {
            return 'settled';
        }
        if (!$link->shop->upi_id) {
            return 'no_upi';
        }

        return $link->status === 'rejected' ? 'rejected' : 'payable';
    }
}

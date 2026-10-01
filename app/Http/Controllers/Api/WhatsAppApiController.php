<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppCreditLedger;
use App\Models\WhatsAppPack;
use App\Models\WhatsAppPackPurchase;
use App\Services\WhatsApp\WhatsAppClient;
use App\Services\WhatsApp\WhatsAppWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Shop-owner WhatsApp API (web panel + mobile app): message-credit wallet and pack purchases.
 * Every route runs under shop.scope, so `shop_id` is always one of the user's own shops.
 */
class WhatsAppApiController extends Controller
{
    public function __construct(private WhatsAppWallet $wallet)
    {
    }

    public function wallet(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $balance = $this->wallet->balance($shopId);

        return response()->json([
            'available' => WhatsAppClient::isEnabled(),
            'balance' => $balance,
            'low_balance' => $balance < WhatsAppWallet::LOW_BALANCE_THRESHOLD,
            'low_balance_threshold' => WhatsAppWallet::LOW_BALANCE_THRESHOLD,
            'packs' => WhatsAppPack::where('status', 'active')->orderBy('credits')->get(['id', 'name', 'credits', 'price']),
            'ledger' => WhatsAppCreditLedger::where('shop_id', $shopId)->latest('id')->limit(50)
                ->get(['id', 'type', 'credits', 'balance_after', 'note', 'created_at']),
        ]);
    }

    /** Creates a Razorpay order for a pack; the app/web opens Razorpay Checkout with it. */
    public function purchasePack(Request $request, $id)
    {
        if (!config('services.razorpay.enabled', true)) {
            return response()->json(['message' => 'Payments are temporarily unavailable. Please try again later.'], 503);
        }

        $pack = WhatsAppPack::where('status', 'active')->find($id);
        if (!$pack) {
            return response()->json(['message' => 'This message pack is not available.'], 404);
        }

        $user = $request->user();
        $shopId = $request->attributes->get('shop_id');
        $amount = (int) round(((float) $pack->price) * 100);
        $keyId = config('services.razorpay.key');
        $keySecret = config('services.razorpay.secret');
        $isTestMode = false;

        try {
            if (empty($keyId) || empty($keySecret)) {
                throw new \Exception('Razorpay credentials not configured.');
            }

            $orderRes = Http::withBasicAuth($keyId, $keySecret)->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amount,
                'currency' => 'INR',
                'receipt' => 'wa_pack_' . $shopId . '_' . time(),
                'notes' => [
                    'type' => 'whatsapp_pack',
                    'user_id' => (string) $user->id,
                    'shop_id' => (string) $shopId,
                    'pack_id' => (string) $pack->id,
                ],
            ]);
            if (!$orderRes->successful()) {
                throw new \Exception('Failed to create Razorpay order: ' . $orderRes->body());
            }
            $orderId = $orderRes->json('id');
        } catch (\Throwable $e) {
            Log::error('Razorpay WhatsApp pack order failed: ' . $e->getMessage());
            if (app()->isProduction()) {
                return response()->json(['message' => 'Could not start the payment. Please try again later.'], 502);
            }
            // Sandbox / no-credentials fallback, same as the add-on flow.
            $orderId = 'order_mock_' . bin2hex(random_bytes(8));
            $keyId = 'rzp_test_placeholder';
            $isTestMode = true;
        }

        WhatsAppPackPurchase::create([
            'shop_id' => $shopId,
            'user_id' => $user->id,
            'whatsapp_pack_id' => $pack->id,
            'pack_name' => $pack->name,
            'credits' => $pack->credits,
            'amount' => $pack->price,
            'razorpay_order_id' => $orderId,
            'status' => 'pending',
        ]);

        return response()->json([
            'requires_payment' => true,
            'gateway' => 'razorpay',
            'is_one_time' => true,
            'is_test_mode' => $isTestMode,
            'key_id' => $keyId,
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => 'INR',
            'pack' => $pack->only(['id', 'name', 'credits', 'price']),
            'user' => [
                'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
            ],
        ]);
    }

    public function verifyPackPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $purchase = WhatsAppPackPurchase::where('shop_id', $request->attributes->get('shop_id'))
            ->where('user_id', $request->user()->id)
            ->where('razorpay_order_id', $request->razorpay_order_id)
            ->first();
        if (!$purchase) {
            return response()->json(['message' => 'Payment order not found.'], 404);
        }

        $keySecret = config('services.razorpay.secret');
        // Mock verification only without a Razorpay secret and outside production, like add-ons.
        $verified = empty($keySecret)
            ? !app()->isProduction()
            : hash_equals(
                hash_hmac('sha256', $purchase->razorpay_order_id . '|' . $request->razorpay_payment_id, $keySecret),
                $request->razorpay_signature
            );
        if (!$verified) {
            return response()->json(['message' => 'Payment signature verification failed.'], 400);
        }

        $this->wallet->completePurchase($purchase, $request->razorpay_payment_id);

        return response()->json([
            'message' => number_format($purchase->credits) . ' WhatsApp message credits added.',
            'balance' => $this->wallet->balance($purchase->shop_id),
        ]);
    }
}

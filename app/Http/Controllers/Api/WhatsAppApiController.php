<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\WhatsAppCreditLedger;
use App\Models\WhatsAppPack;
use App\Models\WhatsAppPackPurchase;
use App\Models\WhatsAppShopSetting;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\InsufficientCreditsException;
use App\Services\WhatsApp\WhatsAppClient;
use App\Services\WhatsApp\WhatsAppException;
use App\Services\WhatsApp\WhatsAppMessenger;
use App\Services\WhatsApp\WhatsAppWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Shop-owner WhatsApp API (web panel + mobile app): message settings, credit wallet and packs.
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

    /**
     * Every message type with the shop's on/off choice and schedule, plus a read-only preview of
     * the admin's template filled with this shop's name and sample customer data.
     */
    public function settings(Request $request, WhatsAppMessenger $messenger)
    {
        $shop = Shop::findOrFail($request->attributes->get('shop_id'));
        $saved = WhatsAppShopSetting::where('shop_id', $shop->id)->get()->keyBy('event');
        $sample = array_merge(WhatsAppTemplate::sampleValues(), ['shop_name' => $shop->name, 'shop_mobile' => (string) $shop->mobile]);

        $events = [];
        foreach (WhatsAppTemplate::EVENTS as $key => $meta) {
            $setting = $saved->get($key);
            $template = $messenger->templateFor($shop, $key);
            $scheduled = WhatsAppShopSetting::isScheduled($key);

            $events[] = [
                'key' => $key,
                'label' => $meta['label'],
                'recipient' => $meta['recipient'],
                'scheduled' => $scheduled,
                'enabled' => (bool) ($setting?->enabled ?? WhatsAppShopSetting::DEFAULTS['enabled']),
                'schedule_days' => $scheduled ? ($setting?->schedule_days ?? WhatsAppShopSetting::DEFAULTS['schedule_days']) : null,
                'schedule_time' => $scheduled ? ($setting?->schedule_time ?? WhatsAppShopSetting::DEFAULTS['schedule_time']) : null,
                'min_due_amount' => $scheduled ? (float) ($setting?->min_due_amount ?? WhatsAppShopSetting::DEFAULTS['min_due_amount']) : null,
                'template_available' => (bool) $template,
                'has_document' => (bool) $template?->has_document,
                'has_pay_button' => (bool) $template?->has_pay_button && !empty($shop->upi_id),
                'preview' => $template?->renderBody($sample),
            ];
        }

        $balance = $this->wallet->balance($shop->id);

        return response()->json([
            'available' => WhatsAppClient::isEnabled(),
            'balance' => $balance,
            'low_balance' => $balance < WhatsAppWallet::LOW_BALANCE_THRESHOLD,
            'upi_id' => $shop->upi_id,
            'max_schedule_days' => WhatsAppShopSetting::MAX_SCHEDULE_DAYS,
            'events' => $events,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'events' => 'required|array',
            'events.*.key' => ['required', 'distinct', Rule::in(array_keys(WhatsAppTemplate::EVENTS))],
            'events.*.enabled' => 'required|boolean',
            'events.*.schedule_days' => 'nullable|array|max:' . WhatsAppShopSetting::MAX_SCHEDULE_DAYS,
            'events.*.schedule_days.*' => ['distinct', Rule::in(WhatsAppShopSetting::DAYS)],
            'events.*.schedule_time' => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'events.*.min_due_amount' => 'nullable|numeric|min:0|max:100000000',
        ], [
            'events.*.schedule_days.max' => 'Choose at most ' . WhatsAppShopSetting::MAX_SCHEDULE_DAYS . ' days a week.',
            'events.*.schedule_time.regex' => 'Choose a valid time.',
        ]);

        $shopId = $request->attributes->get('shop_id');

        foreach ($data['events'] as $i => $event) {
            $values = ['enabled' => $event['enabled']];

            if (WhatsAppShopSetting::isScheduled($event['key'])) {
                if ($event['enabled'] && (empty($event['schedule_days']) || empty($event['schedule_time']))) {
                    throw ValidationException::withMessages([
                        "events.{$i}.schedule_days" => 'Choose the day(s) and time to send ' . strtolower(WhatsAppTemplate::EVENTS[$event['key']]['label']) . 's.',
                    ]);
                }
                $values += [
                    'schedule_days' => array_values(array_intersect(WhatsAppShopSetting::DAYS, $event['schedule_days'] ?? [])),
                    'schedule_time' => $event['schedule_time'] ?? WhatsAppShopSetting::DEFAULTS['schedule_time'],
                    'min_due_amount' => $event['min_due_amount'] ?? WhatsAppShopSetting::DEFAULTS['min_due_amount'],
                ];
            }

            WhatsAppShopSetting::updateOrCreate(['shop_id' => $shopId, 'event' => $event['key']], $values);
        }

        return $this->settings($request, app(WhatsAppMessenger::class));
    }

    /** "Send via WhatsApp" on a sale invoice — from the shop's credits, regardless of the auto toggle. */
    public function sendSaleInvoice(Request $request, WhatsAppMessenger $messenger, $id)
    {
        $shop = Shop::findOrFail($request->attributes->get('shop_id'));
        $sale = Sale::where('shop_id', $shop->id)->findOrFail($id);
        if (!$sale->customer_id || !($customer = Customer::find($sale->customer_id))) {
            return response()->json(['message' => 'This sale has no customer to send it to.'], 422);
        }

        return $this->sendNow(fn () => $messenger->send($shop, 'sale_invoice', $customer, ['sale' => $sale]), $shop);
    }

    public function sendPurchaseInvoice(Request $request, WhatsAppMessenger $messenger, $id)
    {
        $shop = Shop::findOrFail($request->attributes->get('shop_id'));
        $purchase = Purchase::where('shop_id', $shop->id)->findOrFail($id);
        if (!$purchase->supplier_id || !($supplier = Supplier::find($purchase->supplier_id))) {
            return response()->json(['message' => 'This purchase has no supplier to send it to.'], 422);
        }

        return $this->sendNow(fn () => $messenger->send($shop, 'purchase_record', $supplier, ['purchase' => $purchase]), $shop);
    }

    /** Runs a manual send and maps the outcome: 202 queued, 402 out of credits, 422 can't send. */
    private function sendNow(callable $send, Shop $shop)
    {
        try {
            $log = $send();
        } catch (InsufficientCreditsException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'no_credits'], 402);
        } catch (WhatsAppException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Message is being sent on WhatsApp to +' . $log->phone . '.',
            'log_id' => $log->id,
            'balance' => $this->wallet->balance($shop->id),
        ], 202);
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

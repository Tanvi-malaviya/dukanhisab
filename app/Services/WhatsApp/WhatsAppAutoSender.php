<?php

namespace App\Services\WhatsApp;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\WhatsAppShopSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The "instant" WhatsApp messages, fired from the controllers that create sales, purchases and
 * payments (web, app and offline sync all go through them). Each runs only after the surrounding
 * transaction commits and never throws: a WhatsApp problem must not affect the sale or payment.
 */
class WhatsAppAutoSender
{
    public function __construct(private WhatsAppMessenger $messenger)
    {
    }

    public function saleCreated(Sale $sale): void
    {
        $this->afterCommit($sale->shop_id, 'sale_invoice', function () use ($sale) {
            $customer = $sale->customer_id ? Customer::find($sale->customer_id) : null;

            return $customer ? [$customer, ['sale' => $sale->fresh()]] : null;
        });
    }

    public function customerPaid(Customer $customer, float $amount): void
    {
        $this->afterCommit($customer->shop_id, 'payment_received', fn () => [$customer->fresh(), ['amount' => $amount]]);
    }

    public function purchaseCreated(Purchase $purchase): void
    {
        $this->afterCommit($purchase->shop_id, 'purchase_record', function () use ($purchase) {
            $supplier = $purchase->supplier_id ? Supplier::find($purchase->supplier_id) : null;

            return $supplier ? [$supplier, ['purchase' => $purchase->fresh()]] : null;
        });
    }

    public function supplierPaid(Supplier $supplier, float $amount): void
    {
        $this->afterCommit($supplier->shop_id, 'supplier_payment', fn () => [$supplier->fresh(), ['amount' => $amount]]);
    }

    /**
     * @param  callable(): (array{0: Customer|Supplier, 1: array}|null)  $resolve  recipient and context, loaded after commit
     */
    private function afterCommit(int $shopId, string $event, callable $resolve): void
    {
        if (!WhatsAppClient::isEnabled() || !WhatsAppShopSetting::isEnabled($shopId, $event)) {
            return;
        }

        DB::afterCommit(function () use ($shopId, $event, $resolve) {
            try {
                $resolved = $resolve();
                if (!$resolved) {
                    return;
                }
                [$recipient, $context] = $resolved;

                // Common, expected skips — not worth an error.
                if ($recipient->whatsapp_opt_out || !WhatsAppClient::normalizePhone($recipient->mobile)) {
                    return;
                }

                $this->messenger->send(Shop::findOrFail($shopId), $event, $recipient, $context);
            } catch (WhatsAppException $e) {
                Log::info("WhatsApp {$event} not sent for shop {$shopId}: " . $e->getMessage());
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}

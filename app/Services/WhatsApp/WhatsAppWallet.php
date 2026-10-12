<?php

namespace App\Services\WhatsApp;

use App\Models\Shop;
use App\Models\WhatsAppCreditLedger;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppPackPurchase;
use App\Models\WhatsAppWallet as Wallet;
use Illuminate\Support\Facades\DB;

/**
 * A shop's WhatsApp message credits. Every balance change locks the wallet row and writes a
 * ledger entry in the same transaction, so concurrent sends can't overspend and the ledger
 * always adds up to the balance.
 */
class WhatsAppWallet
{
    public const LOW_BALANCE_THRESHOLD = 20;

    public function balance(int $shopId): int
    {
        return (int) (Wallet::where('shop_id', $shopId)->value('balance') ?? 0);
    }

    /** Takes one credit for a message. Throws when the shop has none left. */
    public function debitForMessage(WhatsAppMessageLog $log): void
    {
        $this->change($log->shop_id, -1, 'debit', ['whatsapp_message_log_id' => $log->id], requireFunds: true);
    }

    /** Gives back the credit of a message that failed — at most once per message. */
    public function refundMessage(WhatsAppMessageLog $log): void
    {
        DB::transaction(function () use ($log) {
            $this->lockedWallet($log->shop_id);

            $charged = WhatsAppCreditLedger::where('whatsapp_message_log_id', $log->id)->where('type', 'debit')->exists();
            $refunded = WhatsAppCreditLedger::where('whatsapp_message_log_id', $log->id)->where('type', 'refund')->exists();
            if ($charged && !$refunded) {
                $this->change($log->shop_id, 1, 'refund', ['whatsapp_message_log_id' => $log->id, 'note' => 'Message failed']);
            }
        });
    }

    /**
     * Credits a paid pack purchase. Safe to call from both the app's verify call and the
     * Razorpay webhook — whichever arrives second is a no-op.
     */
    public function completePurchase(WhatsAppPackPurchase $purchase, ?string $paymentId): bool
    {
        return DB::transaction(function () use ($purchase, $paymentId) {
            $purchase = WhatsAppPackPurchase::whereKey($purchase->id)->lockForUpdate()->first();
            if ($purchase->status === 'paid') {
                return false;
            }

            $purchase->update(['status' => 'paid', 'razorpay_payment_id' => $paymentId, 'paid_at' => now()]);
            $this->change($purchase->shop_id, $purchase->credits, 'purchase', [
                'whatsapp_pack_purchase_id' => $purchase->id,
                'note' => $purchase->pack_name,
            ]);

            return true;
        });
    }

    /** Admin correction (support cases). Negative values can't take the balance below zero. */
    public function adjust(Shop $shop, int $credits, string $note, ?int $adminId): void
    {
        $this->change($shop->id, $credits, 'adjustment', ['note' => $note, 'admin_id' => $adminId], requireFunds: $credits < 0);
    }

    private function change(int $shopId, int $credits, string $type, array $refs = [], bool $requireFunds = false): void
    {
        DB::transaction(function () use ($shopId, $credits, $type, $refs, $requireFunds) {
            $wallet = $this->lockedWallet($shopId);

            if ($requireFunds && $wallet->balance + $credits < 0) {
                throw new InsufficientCreditsException('You have no WhatsApp message credits left. Buy a message pack to continue.');
            }

            $wallet->balance += $credits;
            $wallet->save();

            WhatsAppCreditLedger::create($refs + [
                'shop_id' => $shopId,
                'type' => $type,
                'credits' => $credits,
                'balance_after' => $wallet->balance,
            ]);
        });
    }

    private function lockedWallet(int $shopId): Wallet
    {
        Wallet::firstOrCreate(['shop_id' => $shopId], ['balance' => 0]);

        return Wallet::where('shop_id', $shopId)->lockForUpdate()->first();
    }
}

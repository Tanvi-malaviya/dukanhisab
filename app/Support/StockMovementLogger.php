<?php

namespace App\Support;

use App\Models\StockMovement;

/**
 * Shared by every controller that changes Product::stock, so a sale, purchase, return, cancel or
 * manual adjustment always leaves the same kind of audit row behind — this is what the web and
 * mobile Inventory/history screens read, replacing what used to be a client-only local log.
 */
trait StockMovementLogger
{
    protected function logStockMovement(
        int $shopId,
        int $productId,
        int $quantityChange,
        int $resultingStock,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $note = null
    ): void {
        if ($quantityChange === 0) {
            return;
        }

        StockMovement::create([
            'shop_id' => $shopId,
            'product_id' => $productId,
            'type' => $type,
            'quantity_change' => $quantityChange,
            'resulting_stock' => $resultingStock,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'note' => $note,
            'created_by' => auth()->id(),
        ]);
    }
}

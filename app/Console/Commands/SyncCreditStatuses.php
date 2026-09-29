<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\SupplierApiController;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Console\Command;

/**
 * Recomputes Unpaid / Partially Paid / Completed for credit sales and purchases from the
 * customers' and suppliers' current dues. The API keeps these in step on every write; run this
 * once to repair statuses that drifted before that was the case.
 */
class SyncCreditStatuses extends Command
{
    protected $signature = 'dukanhisab:sync-credit-statuses';
    protected $description = 'Recompute credit sale/purchase statuses from customer and supplier dues';

    public function handle(): int
    {
        $customers = 0;
        Customer::query()->select(['id', 'shop_id'])->chunkById(200, function ($rows) use (&$customers) {
            foreach ($rows as $c) {
                CustomerApiController::syncCustomerSaleStatuses($c->id, $c->shop_id);
                $customers++;
            }
        });

        $suppliers = 0;
        Supplier::query()->select(['id', 'shop_id'])->chunkById(200, function ($rows) use (&$suppliers) {
            foreach ($rows as $s) {
                SupplierApiController::syncSupplierPurchaseStatuses($s->id, $s->shop_id);
                $suppliers++;
            }
        });

        $this->info("Checked {$customers} customers and {$suppliers} suppliers.");
        return self::SUCCESS;
    }
}

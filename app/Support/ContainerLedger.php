<?php

namespace App\Support;

use App\Http\Controllers\Api\CustomerApiController;
use App\Models\CashBook;
use App\Models\ContainerEntry;
use App\Models\ContainerMovement;
use App\Models\ContainerType;
use App\Models\Customer;
use App\Models\InvoiceCounter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Returnable containers ledger (Tub / Can / Bucket lent to customers against a refundable deposit).
 *
 * Accounting rules this class enforces:
 * - A deposit is a liability the shop owes back, never sales income: it is kept out of the sale's
 *   grand_total and posted to the cashbook as its own reference_type (container_deposit /
 *   container_refund), which the expense scope and the sales reports already ignore.
 * - Only damage deductions and the deposit of lost containers become income ("forfeit").
 * - A customer's due (udhar) and deposit are never netted silently — "due_adjustment" is an
 *   explicit, numbered entry.
 * - Posted entries are never edited; reverse() posts an opposite entry instead.
 *
 * Returns are matched to the customer's oldest open lots first (or to one sale's lots when a
 * sale_id is given), so one return entry can settle containers from several invoices at once,
 * each refunded at the deposit actually paid on it.
 */
class ContainerLedger
{
    public const PREFIXES = [
        'give' => 'CG',
        'return' => 'CR',
        'exchange' => 'CX',
        'opening' => 'CO',
        'stock' => 'CS',
        'reversal' => 'CV',
    ];

    private const CASH_METHODS = ['cash', 'upi', 'bank'];

    /**
     * Post containers given to and/or returned by one customer.
     *
     * $data keys:
     *   customer_id        int (required)
     *   sale_id            ?int  — sale the given containers belong to
     *   opening            bool  — opening balance: containers already out before this module was
     *                              switched on; records the deposit held without any cash entry
     *   issues             [{container_type_id, quantity, deposit_per_unit?}]
     *   returns            [{container_type_id, returned?, damaged?, damage_deduction?, lost?, sale_id?}]
     *   collect_deposit    bool (default true) — false = deposit not collected, count only
     *   settlement_method  cash|upi|bank|due_adjustment — how the net amount is settled
     *   note, entry_date
     */
    public static function post(int $shopId, array $data): ContainerEntry
    {
        return DB::transaction(function () use ($shopId, $data) {
            $customer = Customer::where('shop_id', $shopId)->lockForUpdate()->find($data['customer_id'] ?? null);
            if (!$customer) {
                throw ValidationException::withMessages(['customer_id' => 'Select a customer for containers.']);
            }

            $opening = (bool) ($data['opening'] ?? false);
            $collectDeposit = (bool) ($data['collect_deposit'] ?? true);

            $issues = array_values(array_filter($data['issues'] ?? [], fn ($l) => (int) ($l['quantity'] ?? 0) > 0));
            $returns = array_values(array_filter($data['returns'] ?? [], fn ($l) =>
                (int) ($l['returned'] ?? 0) + (int) ($l['damaged'] ?? 0) + (int) ($l['lost'] ?? 0) > 0));

            if (empty($issues) && empty($returns)) {
                throw ValidationException::withMessages(['containers' => 'Enter at least one container quantity.']);
            }
            if ($opening && !empty($returns)) {
                throw ValidationException::withMessages(['containers' => 'An opening balance can only add containers.']);
            }

            $typeIds = array_unique(array_merge(
                array_column($issues, 'container_type_id'),
                array_column($returns, 'container_type_id')
            ));
            $types = ContainerType::where('shop_id', $shopId)->whereIn('id', $typeIds)->get()->keyBy('id');
            if ($types->count() !== count($typeIds)) {
                throw ValidationException::withMessages(['containers' => 'Unknown container type.']);
            }

            $type = $opening ? 'opening' : (!empty($issues) && !empty($returns) ? 'exchange' : (!empty($issues) ? 'give' : 'return'));
            $saleId = $data['sale_id'] ?? null;

            $entry = ContainerEntry::create([
                'shop_id' => $shopId,
                'entry_number' => self::nextEntryNumber($shopId, $type),
                'type' => $type,
                'customer_id' => $customer->id,
                'sale_id' => $saleId,
                'note' => $data['note'] ?? null,
                'entry_date' => isset($data['entry_date']) ? Carbon::parse($data['entry_date']) : Carbon::now(),
                'created_by' => auth()->id(),
            ]);

            // Returns first, so containers given in this same entry (an exchange) are never matched
            // against the empties coming back.
            foreach ($returns as $line) {
                self::applyReturnLine($entry, $customer, $types[$line['container_type_id']], $line);
            }

            foreach ($issues as $line) {
                $containerType = $types[$line['container_type_id']];
                $qty = (int) $line['quantity'];
                $rate = $collectDeposit
                    ? round((float) ($line['deposit_per_unit'] ?? $containerType->deposit_amount), 2)
                    : 0.0;
                if ($rate < 0) {
                    throw ValidationException::withMessages(['containers' => 'Deposit cannot be negative.']);
                }

                ContainerMovement::create([
                    'shop_id' => $shopId,
                    'container_entry_id' => $entry->id,
                    'customer_id' => $customer->id,
                    'container_type_id' => $containerType->id,
                    'sale_id' => $saleId,
                    'kind' => 'issue',
                    'quantity' => $qty,
                    'deposit_per_unit' => $rate,
                    'amount' => round($qty * $rate, 2),
                ]);
            }

            $movements = $entry->movements()->get();
            $deposit = round($movements->where('kind', 'issue')->sum('amount'), 2);
            $refund = round($movements->whereIn('kind', ['return', 'damaged'])->sum('amount'), 2);
            $forfeit = round($movements->sum('forfeit_amount'), 2);
            // Opening balance deposits were collected in the past — nothing moves today.
            $net = $opening ? 0.0 : round($deposit - $refund, 2);

            $method = self::settle($entry, $customer, $net, $data['settlement_method'] ?? null);

            $entry->update([
                'deposit_amount' => $deposit,
                'refund_amount' => $refund,
                'forfeit_amount' => $forfeit,
                'net_amount' => $net,
                'settlement_method' => $method,
            ]);

            return $entry->load('movements.containerType', 'customer');
        });
    }

    /** Add (+) or remove (-) containers the shop owns, e.g. new tubs bought or broken ones scrapped. */
    public static function adjustStock(int $shopId, ContainerType $containerType, int $change, ?string $note = null): ContainerEntry
    {
        return DB::transaction(function () use ($shopId, $containerType, $change, $note) {
            $containerType = ContainerType::where('shop_id', $shopId)->lockForUpdate()->findOrFail($containerType->id);
            if ($change === 0) {
                throw ValidationException::withMessages(['quantity' => 'Enter a quantity to add or remove.']);
            }
            if ($containerType->total_owned + $change < 0) {
                throw ValidationException::withMessages(['quantity' => "Cannot remove more than the {$containerType->total_owned} {$containerType->name} owned."]);
            }

            $entry = ContainerEntry::create([
                'shop_id' => $shopId,
                'entry_number' => self::nextEntryNumber($shopId, 'stock'),
                'type' => 'stock',
                'note' => $note,
                'entry_date' => Carbon::now(),
                'created_by' => auth()->id(),
            ]);

            ContainerMovement::create([
                'shop_id' => $shopId,
                'container_entry_id' => $entry->id,
                'container_type_id' => $containerType->id,
                'kind' => $change > 0 ? 'stock_in' : 'stock_out',
                'quantity' => abs($change),
            ]);

            $containerType->increment('total_owned', $change);

            return $entry->load('movements.containerType');
        });
    }

    /** Undo a posted entry with an opposite entry: containers, cashbook and dues all go back. */
    public static function reverse(int $shopId, ContainerEntry $entry, ?string $note = null): ContainerEntry
    {
        return DB::transaction(function () use ($shopId, $entry, $note) {
            $entry = ContainerEntry::where('shop_id', $shopId)->lockForUpdate()->with('movements')->findOrFail($entry->id);

            if ($entry->type === 'reversal') {
                throw ValidationException::withMessages(['entry' => 'A reversal entry cannot be reversed.']);
            }
            if ($entry->reversed_at) {
                throw ValidationException::withMessages(['entry' => 'This entry is already reversed.']);
            }

            foreach ($entry->movements as $movement) {
                if ($movement->kind === 'issue' && $movement->closed_quantity > 0) {
                    throw ValidationException::withMessages(['entry' =>
                        'Some containers from this entry were already returned. Reverse those returns first.']);
                }
            }

            foreach ($entry->movements as $movement) {
                if (in_array($movement->kind, ['return', 'damaged', 'lost'], true) && $movement->lot_id) {
                    $lot = ContainerMovement::lockForUpdate()->find($movement->lot_id);
                    if ($lot) {
                        $lot->decrement('closed_quantity', min($movement->quantity, $lot->closed_quantity));
                    }
                } elseif (in_array($movement->kind, ['stock_in', 'stock_out'], true)) {
                    $containerType = ContainerType::withTrashed()->lockForUpdate()->find($movement->container_type_id);
                    $change = $movement->kind === 'stock_in' ? -$movement->quantity : $movement->quantity;
                    if ($containerType->total_owned + $change < 0) {
                        throw ValidationException::withMessages(['entry' => 'Reversing this would make the owned count negative.']);
                    }
                    $containerType->increment('total_owned', $change);
                }
            }

            $reversal = ContainerEntry::create([
                'shop_id' => $shopId,
                'entry_number' => self::nextEntryNumber($shopId, 'reversal'),
                'type' => 'reversal',
                'customer_id' => $entry->customer_id,
                'sale_id' => $entry->sale_id,
                'deposit_amount' => $entry->deposit_amount,
                'refund_amount' => $entry->refund_amount,
                'forfeit_amount' => $entry->forfeit_amount,
                'net_amount' => -$entry->net_amount,
                'settlement_method' => $entry->settlement_method,
                'note' => $note ?: ('Reversal of ' . $entry->entry_number),
                'entry_date' => Carbon::now(),
                'reversal_of_id' => $entry->id,
                'created_by' => auth()->id(),
            ]);

            $net = (float) $entry->net_amount;
            if ($net != 0.0) {
                if (in_array($entry->settlement_method, self::CASH_METHODS, true)) {
                    CashBook::create([
                        'shop_id' => $shopId,
                        'type' => $net > 0 ? 'cash_out' : 'cash_in',
                        'amount' => abs($net),
                        'payment_method' => $entry->settlement_method,
                        'description' => 'Reversal (Container): ' . $entry->entry_number,
                        'reference_id' => $reversal->id,
                        'reference_type' => 'container_reversal',
                        'transaction_date' => Carbon::now(),
                    ]);
                } elseif ($entry->settlement_method === 'due_adjustment' && $entry->customer_id) {
                    $customer = Customer::withTrashed()->find($entry->customer_id);
                    if ($customer) {
                        $customer->increment('due_amount', abs($net));
                        CustomerApiController::syncCustomerSaleStatuses($customer->id, $shopId);
                    }
                }
            }

            $entry->update(['reversed_at' => Carbon::now()]);

            return $reversal->load('movements.containerType', 'customer');
        });
    }

    /** Close up to the requested quantities against the customer's open lots, oldest first. */
    private static function applyReturnLine(ContainerEntry $entry, Customer $customer, ContainerType $containerType, array $line): void
    {
        $wanted = [
            'return' => max(0, (int) ($line['returned'] ?? 0)),
            'damaged' => max(0, (int) ($line['damaged'] ?? 0)),
            'lost' => max(0, (int) ($line['lost'] ?? 0)),
        ];
        $total = array_sum($wanted);
        $lineSaleId = $line['sale_id'] ?? null;

        $lots = ContainerMovement::where('shop_id', $entry->shop_id)
            ->where('customer_id', $customer->id)
            ->where('container_type_id', $containerType->id)
            ->openLots()
            ->when($lineSaleId, fn ($q) => $q->where('sale_id', $lineSaleId))
            ->orderBy(ContainerEntry::select('entry_date')->whereColumn('container_entries.id', 'container_movements.container_entry_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $available = $lots->sum(fn ($lot) => $lot->quantity - $lot->closed_quantity);
        if ($total > $available) {
            throw ValidationException::withMessages(['containers' =>
                "{$customer->name} has only {$available} {$containerType->name} pending" . ($lineSaleId ? ' on this invoice' : '') . ", cannot return {$total}."]);
        }

        $deductionLeft = round(max(0, (float) ($line['damage_deduction'] ?? 0)), 2);

        foreach ($lots as $lot) {
            foreach ($wanted as $kind => $left) {
                $lotPending = $lot->quantity - $lot->closed_quantity;
                if ($left <= 0 || $lotPending <= 0) {
                    continue;
                }
                $qty = min($left, $lotPending);
                $wanted[$kind] -= $qty;

                $value = round($qty * (float) $lot->deposit_per_unit, 2);
                $amount = $value;
                $forfeit = 0.0;
                if ($kind === 'damaged') {
                    $forfeit = min($deductionLeft, $value);
                    $deductionLeft = round($deductionLeft - $forfeit, 2);
                    $amount = round($value - $forfeit, 2);
                } elseif ($kind === 'lost') {
                    $forfeit = $value;
                    $amount = 0.0;
                }

                ContainerMovement::create([
                    'shop_id' => $entry->shop_id,
                    'container_entry_id' => $entry->id,
                    'customer_id' => $customer->id,
                    'container_type_id' => $containerType->id,
                    'sale_id' => $lot->sale_id,
                    'lot_id' => $lot->id,
                    'kind' => $kind,
                    'quantity' => $qty,
                    'deposit_per_unit' => $lot->deposit_per_unit,
                    'amount' => $amount,
                    'forfeit_amount' => $forfeit,
                ]);

                $lot->closed_quantity += $qty;
                $lot->save();
            }
        }

        if ($deductionLeft > 0.004) {
            throw ValidationException::withMessages(['containers' =>
                "Damage deduction for {$containerType->name} is more than the deposit held on the damaged containers."]);
        }
    }

    /** Post the net amount to the cashbook or the customer's due; returns the method used. */
    private static function settle(ContainerEntry $entry, Customer $customer, float $net, ?string $method): string
    {
        $method = $method ? strtolower($method) : null;

        if (abs($net) < 0.005) {
            return 'none';
        }

        if ($net > 0) {
            if (!in_array($method, self::CASH_METHODS, true)) {
                throw ValidationException::withMessages(['settlement_method' => 'Choose Cash, UPI or Bank to collect the container deposit.']);
            }
            CashBook::create([
                'shop_id' => $entry->shop_id,
                'type' => 'cash_in',
                'amount' => $net,
                'payment_method' => $method,
                'description' => 'Container Deposit: ' . $entry->entry_number . ' (' . $customer->name . ')',
                'reference_id' => $entry->id,
                'reference_type' => 'container_deposit',
                'transaction_date' => Carbon::now(),
            ]);
            return $method;
        }

        $refund = abs($net);
        if (in_array($method, self::CASH_METHODS, true)) {
            CashBook::create([
                'shop_id' => $entry->shop_id,
                'type' => 'cash_out',
                'amount' => $refund,
                'payment_method' => $method,
                'description' => 'Container Refund: ' . $entry->entry_number . ' (' . $customer->name . ')',
                'reference_id' => $entry->id,
                'reference_type' => 'container_refund',
                'transaction_date' => Carbon::now(),
            ]);
            return $method;
        }

        if ($method === 'due_adjustment') {
            $customer->refresh();
            if ($refund > (float) $customer->due_amount + 0.004) {
                throw ValidationException::withMessages(['settlement_method' =>
                    'Deposit refund ₹' . number_format($refund, 2) . ' is more than the customer\'s due ₹'
                    . number_format((float) $customer->due_amount, 2) . '. Refund it in Cash, UPI or Bank instead.']);
            }
            $customer->decrement('due_amount', $refund);
            CustomerApiController::syncCustomerSaleStatuses($customer->id, $entry->shop_id);
            return 'due_adjustment';
        }

        throw ValidationException::withMessages(['settlement_method' => 'Choose how to refund the deposit: Cash, UPI, Bank or Adjust against dues.']);
    }

    private static function nextEntryNumber(int $shopId, string $type): string
    {
        $today = Carbon::now();
        do {
            $next = InvoiceCounter::nextNumber($shopId, 'container', $today);
            $number = self::PREFIXES[$type] . '-' . $today->format('Ymd') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
        } while (ContainerEntry::where('shop_id', $shopId)->where('entry_number', $number)->exists());

        return $number;
    }
}

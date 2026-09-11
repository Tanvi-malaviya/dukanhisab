<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CashBook;
use App\Models\CreditNote;
use App\Models\InvoiceCounter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SaleApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        // Automatically sync sale statuses with current customer due balances
        $customerIds = Customer::where('shop_id', $shopId)->pluck('id');
        foreach ($customerIds as $custId) {
            CustomerApiController::syncCustomerSaleStatuses($custId, $shopId);
        }
        
        $query = Sale::where('shop_id', $shopId)->with(['customer', 'items.product']);

        if ($request->filled('updated_since')) {
            $validator = Validator::make($request->only('updated_since'), [
                'updated_since' => 'date',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $query->withTrashed()->where('updated_at', '>=', Carbon::parse($request->updated_since));
        }

        // Apply Date Filters
        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        // Apply Customer Filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Apply Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                  ->orWhere('payment_type', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items.product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = $request->input('per_page', 10);
            $sales = $query->orderBy('sale_date', 'desc')->paginate($perPage);
        } else {
            $sales = $query->orderBy('sale_date', 'desc')->get();
        }
        return response()->json($sales);
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'payment_type' => 'required|string|in:Cash,UPI,Bank,Credit,Store Credit',
            'used_credit_balance' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selling_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'sale_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request, $shopId) {
            $today = Carbon::now();
            $todayStr = $today->format('Ymd');
            do {
                $nextNumber = InvoiceCounter::nextNumber($shopId, 'sale', $today);
                $saleNumber = 'INV-' . $todayStr . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            } while (Sale::withTrashed()->where('shop_id', $shopId)->where('sale_number', $saleNumber)->exists());

            // Handle Store Credit / Credit Balance calculation
            $usedCredit = 0.00;
            $customer = null;
            if ($request->customer_id) {
                $customer = Customer::find($request->customer_id);
                if ($customer) {
                    if ($request->payment_type === 'Store Credit') {
                        $usedCredit = min((float) $request->grand_total, (float) $customer->credit_balance);
                    } elseif ($request->filled('used_credit_balance') && (float) $request->used_credit_balance > 0) {
                        $usedCredit = min((float) $request->used_credit_balance, (float) $customer->credit_balance);
                    }
                }
            }

            $paidAmount = 0.00;
            if ($request->payment_type !== 'Credit') {
                $paidAmount = max(0, (float) $request->grand_total - $usedCredit);
            } else {
                $paidAmount = 0.00;
            }

            $saleStatus = 'Completed';
            if ($request->payment_type === 'Credit') {
                $remDue = max(0, (float) $request->grand_total - $usedCredit);
                $saleStatus = ($remDue <= 0) ? 'Completed' : (($usedCredit > 0) ? 'Partially Paid' : 'Unpaid');
            }

            $sale = Sale::create([
                'shop_id' => $shopId,
                'customer_id' => $request->customer_id,
                'sale_number' => $saleNumber,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'grand_total' => $request->grand_total,
                'paid_amount' => $paidAmount,
                'store_credit' => $usedCredit,
                'payment_type' => $request->payment_type,
                'status' => $saleStatus,
                'sale_date' => $request->filled('sale_date') ? Carbon::parse($request->sale_date) : Carbon::now(),
            ]);

            foreach ($request->items as $item) {
                // Create Sale Item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['selling_price'],
                    'discount' => $item['discount'] ?? 0,
                ]);

                // Decrement Product Stock
                $product = Product::findOrFail($item['product_id']);
                $product->decrement('stock', $item['quantity']);
            }

            // Deduct Store Credit from Customer and redeem CreditNotes FIFO
            if ($customer && $usedCredit > 0) {
                $customer->decrement('credit_balance', $usedCredit);

                $activeNotes = CreditNote::where('shop_id', $shopId)
                    ->where('customer_id', $customer->id)
                    ->where('status', 'Active')
                    ->where('remaining_balance', '>', 0)
                    ->orderBy('id', 'asc')
                    ->get();

                $remDeduct = $usedCredit;
                foreach ($activeNotes as $cn) {
                    if ($remDeduct <= 0) break;
                    $d = min($remDeduct, (float) $cn->remaining_balance);
                    $cn->used_amount += $d;
                    $cn->remaining_balance -= $d;
                    if ($cn->remaining_balance <= 0) {
                        $cn->status = 'Redeemed';
                    }
                    $cn->save();
                    $remDeduct -= $d;
                }
            }

            // Adjust Customer Dues if Credit sale (net after store credit)
            if ($request->payment_type === 'Credit' && $request->customer_id) {
                $customer = Customer::findOrFail($request->customer_id);
                $netCreditDue = max(0, $request->grand_total - $usedCredit);
                if ($netCreditDue > 0) {
                    $customer->increment('due_amount', $netCreditDue);
                }
            }

            // Log in Cash Book for non-credit sales (net cash/bank/upi received after store credit)
            $cashAmount = max(0, $request->grand_total - $usedCredit);
            if ($request->payment_type !== 'Credit' && $cashAmount > 0) {
                $methodMap = [
                    'Cash' => 'cash',
                    'Bank' => 'bank',
                    'UPI' => 'upi',
                    'cash' => 'cash',
                    'bank' => 'bank',
                    'upi' => 'upi',
                    'Store Credit' => 'cash',
                ];
                
                $desc = 'Sale: ' . $saleNumber;
                if ($usedCredit > 0) {
                    $desc .= ' (₹' . number_format($usedCredit, 2) . ' from Store Credit)';
                }

                CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_in',
                    'amount' => $cashAmount,
                    'payment_method' => $methodMap[$request->payment_type] ?? 'cash',
                    'description' => $desc,
                    'reference_id' => $sale->id,
                    'reference_type' => 'sale',
                    'transaction_date' => Carbon::now(),
                ]);
            }

            return response()->json($sale->load('items.product', 'customer'), 201);
        });
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $sale = Sale::where('shop_id', $shopId)->with('items.product', 'customer')->findOrFail($id);
        return response()->json($sale);
    }

    public function update(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $sale = Sale::where('shop_id', $shopId)->with('items')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'payment_type' => 'sometimes|required|string|in:Cash,UPI,Bank,Credit',
            'sale_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|string|in:Completed,Returned,Partially Returned',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.selling_price' => 'required_with:items|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'subtotal' => 'sometimes|numeric|min:0',
            'discount' => 'sometimes|numeric|min:0',
            'grand_total' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('items')) {
            if ($sale->status !== 'Completed') {
                return response()->json(['message' => 'Cannot edit items of a returned sale.'], 400);
            }

            return DB::transaction(function () use ($request, $sale, $shopId) {
                $oldPaymentType = $sale->payment_type;
                $oldCustomerId = $sale->customer_id;

                // Revert stock for the items currently on this sale
                foreach ($sale->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }

                // Revert old payment effects
                if ($oldPaymentType === 'Credit' && $oldCustomerId) {
                    $oldCust = Customer::find($oldCustomerId);
                    if ($oldCust) {
                        $oldCust->decrement('due_amount', $sale->grand_total);
                    }
                } else {
                    CashBook::where('shop_id', $shopId)
                        ->where('reference_type', 'sale')
                        ->where('reference_id', $sale->id)
                        ->delete();
                }

                // Replace items with the new set
                $sale->items()->delete();
                foreach ($request->items as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'selling_price' => $item['selling_price'],
                        'discount' => $item['discount'] ?? 0,
                    ]);
                    $product = Product::findOrFail($item['product_id']);
                    $product->decrement('stock', $item['quantity']);
                }

                if ($request->has('customer_id')) {
                    $sale->customer_id = $request->customer_id;
                }
                if ($request->has('payment_type')) {
                    $sale->payment_type = $request->payment_type;
                }
                $sale->subtotal = $request->input('subtotal', $sale->subtotal);
                $sale->discount = $request->input('discount', $sale->discount);
                $sale->grand_total = $request->input('grand_total', $sale->grand_total);
                $sale->save();

                $newPaymentType = $sale->payment_type;
                $newCustomerId = $sale->customer_id;

                // Apply new payment effects
                if ($newPaymentType === 'Credit' && $newCustomerId) {
                    $newCust = Customer::find($newCustomerId);
                    if ($newCust) {
                        $newCust->increment('due_amount', $sale->grand_total);
                    }
                } elseif ($newPaymentType !== 'Credit') {
                    $methodMap = [
                        'Cash' => 'cash',
                        'Bank' => 'bank',
                        'UPI' => 'upi'
                    ];
                    CashBook::create([
                        'shop_id' => $shopId,
                        'type' => 'cash_in',
                        'amount' => $sale->grand_total,
                        'payment_method' => $methodMap[$newPaymentType] ?? 'cash',
                        'description' => 'Sale updated: ' . $sale->sale_number,
                        'reference_id' => $sale->id,
                        'reference_type' => 'sale',
                        'transaction_date' => $sale->sale_date,
                    ]);
                }

                return response()->json($sale->load('items.product', 'customer'));
            });
        }

        return DB::transaction(function () use ($request, $sale, $shopId) {
            $oldPaymentType = $sale->payment_type;
            $oldCustomerId = $sale->customer_id;
            $oldGrandTotal = $sale->grand_total;

            if ($request->has('status') && $request->status === 'Returned' && $sale->status !== 'Returned') {
                return $this->processSaleReturn($sale);
            }

            if ($request->has('customer_id')) {
                $sale->customer_id = $request->customer_id;
            }
            if ($request->has('payment_type')) {
                $sale->payment_type = $request->payment_type;
            }
            if ($request->has('sale_date')) {
                $sale->sale_date = Carbon::parse($request->sale_date);
            }
            $sale->save();

            $newPaymentType = $sale->payment_type;
            $newCustomerId = $sale->customer_id;

            // Revert old payment effects
            if ($oldPaymentType === 'Credit' && $oldCustomerId) {
                $oldCust = Customer::find($oldCustomerId);
                if ($oldCust) {
                    $oldCust->decrement('due_amount', $oldGrandTotal);
                }
            } else {
                CashBook::where('shop_id', $shopId)
                    ->where('reference_type', 'sale')
                    ->where('reference_id', $sale->id)
                    ->delete();
            }

            // Apply new payment effects
            if ($newPaymentType === 'Credit' && $newCustomerId) {
                $newCust = Customer::find($newCustomerId);
                if ($newCust) {
                    $newCust->increment('due_amount', $oldGrandTotal);
                }
            } elseif ($newPaymentType !== 'Credit') {
                $methodMap = [
                    'Cash' => 'cash',
                    'Bank' => 'bank',
                    'UPI' => 'upi'
                ];
                CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_in',
                    'amount' => $oldGrandTotal,
                    'payment_method' => $methodMap[$newPaymentType] ?? 'cash',
                    'description' => 'Sale updated: ' . $sale->sale_number,
                    'reference_id' => $sale->id,
                    'reference_type' => 'sale',
                    'transaction_date' => $sale->sale_date,
                ]);
            }

            return response()->json($sale->load('customer'));
        });
    }

    /**
     * Cancel a posted sale invoice with full reversal of stock, dues, store credit, and cashbook audit trail.
     */
    public function cancel(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $sale = Sale::where('shop_id', $shopId)->with(['items', 'customer'])->findOrFail($id);

        if ($sale->status === 'Cancelled') {
            return response()->json(['message' => 'Sale is already cancelled.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|min:3|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reason = $request->input('cancellation_reason');
        $userId = auth()->id();

        return DB::transaction(function () use ($sale, $reason, $shopId, $userId) {
            // 1. Stock Reversal (increment stock for unreturned items)
            foreach ($sale->items as $item) {
                $unreturnedQty = $item->quantity - ($item->returned_quantity ?? 0);
                if ($unreturnedQty > 0) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $unreturnedQty);
                    }
                }
            }

            // 2. Customer Khata / Due Balance Reversal
            if ($sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    $unpaidDue = max(0, (float)$sale->grand_total - (float)($sale->paid_amount ?? 0) - (float)($sale->store_credit ?? 0));
                    if ($unpaidDue > 0) {
                        $customer->decrement('due_amount', min($unpaidDue, (float)$customer->due_amount));
                    }

                    // 3. Store Credit Reversal: restore store credit to customer and reactivate redeemed notes
                    $redeemedCredit = (float)($sale->store_credit ?? 0);
                    if ($redeemedCredit > 0) {
                        $customer->increment('credit_balance', $redeemedCredit);

                        $redeemedNotes = CreditNote::where('shop_id', $shopId)
                            ->where('customer_id', $customer->id)
                            ->where('used_amount', '>', 0)
                            ->orderBy('id', 'desc')
                            ->get();

                        $remToRestore = $redeemedCredit;
                        foreach ($redeemedNotes as $cn) {
                            if ($remToRestore <= 0) break;
                            $restoreAmt = min($remToRestore, (float)$cn->used_amount);
                            $cn->used_amount -= $restoreAmt;
                            $cn->remaining_balance += $restoreAmt;
                            if ($cn->status === 'Redeemed' && $cn->remaining_balance > 0) {
                                $cn->status = 'Active';
                            }
                            $cn->save();
                            $remToRestore -= $restoreAmt;
                        }
                    }
                }
            }

            // 4. CashBook Reversal: Post an explicit reversal cash_out entry for audit integrity
            $actualPaid = (float)($sale->paid_amount ?? 0);
            if ($actualPaid > 0) {
                $methodMap = [
                    'Cash' => 'cash',
                    'Bank' => 'bank',
                    'UPI' => 'upi',
                    'cash' => 'cash',
                    'bank' => 'bank',
                    'upi' => 'upi',
                    'Store Credit' => 'cash',
                ];

                CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_out',
                    'amount' => $actualPaid,
                    'payment_method' => $methodMap[$sale->payment_type] ?? 'cash',
                    'description' => 'Reversal (Cancelled): ' . $sale->sale_number . ' - ' . $reason,
                    'reference_id' => $sale->id,
                    'reference_type' => 'sale_cancel',
                    'transaction_date' => Carbon::now(),
                ]);
            }

            // 5. Update Sale status and record cancellation reason (remains visible in DB and UI)
            $sale->update([
                'status' => 'Cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => Carbon::now(),
                'cancelled_by' => $userId,
            ]);

            return response()->json($sale->load('items.product', 'customer'));
        });
    }

    public function destroy(Request $request, $id)
    {
        $reason = $request->input('cancellation_reason', 'Cancelled by user');
        $request->merge(['cancellation_reason' => $reason]);
        return $this->cancel($request, $id);
    }

    /**
     * Process sale return.
     */
    public function returnSale(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $sale = Sale::where('shop_id', $shopId)->with('items')->findOrFail($id);

        if ($sale->status === 'Returned') {
            return response()->json(['message' => 'Sale is already returned.'], 400);
        }

        $refundMethod = $request->input('refund_method'); // cash, bank, upi, credit_note, due_adjustment

        // If request has items (partial return)
        if ($request->has('items') && is_array($request->items)) {
            $validator = Validator::make($request->all(), [
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'refund_method' => 'nullable|string|in:cash,bank,upi,credit_note,due_adjustment',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return DB::transaction(function () use ($sale, $request, $shopId, $refundMethod) {
                foreach ($request->items as $returnItem) {
                    $productId = $returnItem['product_id'];
                    $returnQty = $returnItem['quantity'];

                    // Find corresponding sale item
                    $saleItem = $sale->items()->where('product_id', $productId)->first();
                    if (!$saleItem) {
                        return response()->json(['message' => 'Product not found in this sale.'], 400);
                    }

                    $availableToReturn = $saleItem->quantity - $saleItem->returned_quantity;
                    if ($returnQty > $availableToReturn) {
                        return response()->json(['message' => "Cannot return more than available quantity ({$availableToReturn}) for product ID {$productId}."], 400);
                    }

                    // Increment stock of the returned product
                    $product = Product::findOrFail($productId);
                    $product->increment('stock', $returnQty);

                    // Increment returned_quantity of the Sale Item
                    $saleItem->increment('returned_quantity', $returnQty);
                }

                // Recalculate subtotal from remaining items
                $allSaleItems = $sale->items()->get();
                $newSubtotal = 0;
                foreach ($allSaleItems as $item) {
                    $netQty = $item->quantity - $item->returned_quantity;
                    $effectiveRate = $item->quantity > 0
                        ? max(0, ($item->quantity * $item->selling_price - ($item->discount ?? 0)) / $item->quantity)
                        : $item->selling_price;
                    $newSubtotal += $netQty * $effectiveRate;
                }

                // Recalculate grand total. Ensure discount doesn't exceed new subtotal.
                $newDiscount = min($sale->discount, $newSubtotal);
                $newGrandTotal = max(0, $newSubtotal - $newDiscount);
                
                $actualRefund = $sale->grand_total - $newGrandTotal;

                // Process refund according to refund_method
                $this->applyRefundSettlement($sale, $actualRefund, $refundMethod, $shopId, $request);

                // Update Sale status
                $hasRemaining = false;
                foreach ($allSaleItems as $item) {
                    if ($item->quantity > $item->returned_quantity) {
                        $hasRemaining = true;
                        break;
                    }
                }

                $sale->subtotal = $newSubtotal;
                $sale->discount = $newDiscount;
                $sale->grand_total = $newGrandTotal;
                
                if (!$hasRemaining) {
                    $sale->status = 'Returned';
                } else {
                    $sale->status = 'Partially Returned';
                }
                $sale->save();

                return response()->json($sale->load('items.product', 'customer', 'creditNotes'));
            });
        }

        // Otherwise process full return
        return $this->processSaleReturn($sale, $refundMethod, $request);
    }

    private function processSaleReturn($sale, ?string $refundMethod = null, ?Request $request = null)
    {
        return DB::transaction(function () use ($sale, $refundMethod, $request) {
            $shopId = $sale->shop_id;

            // 1. Restore Product Stocks & Batch Stocks, and update returned_quantity
            $totalRefundAmount = 0;
            foreach ($sale->items as $item) {
                $unreturnedQty = $item->quantity - $item->returned_quantity;
                if ($unreturnedQty > 0) {
                    $product = Product::findOrFail($item->product_id);
                    $product->increment('stock', $unreturnedQty);

                    $item->returned_quantity = $item->quantity;
                    $item->save();

                    $totalRefundAmount += $unreturnedQty * $item->selling_price;
                }
            }

            $actualRefund = $sale->grand_total;

            // 2. Process refund according to refund_method
            $this->applyRefundSettlement($sale, $actualRefund, $refundMethod, $shopId, $request);

            $sale->status = 'Returned';
            $sale->grand_total = 0; // Everything is returned
            $sale->save();

            return response()->json($sale->load('items.product', 'customer', 'creditNotes'));
        });
    }

    private function applyRefundSettlement($sale, float $actualRefund, ?string $refundMethod, int $shopId, ?Request $request = null): void
    {
        if ($actualRefund <= 0) return;

        $isCreditNote = ($refundMethod === 'credit_note') || ($request && $request->boolean('issue_credit_note'));
        $isDueAdj = ($refundMethod === 'due_adjustment') || (!$refundMethod && $sale->payment_type === 'Credit');

        if ($isCreditNote && $sale->customer_id) {
            $today = Carbon::now();
            $todayStr = $today->format('Ymd');
            do {
                $nextNumber = InvoiceCounter::nextNumber($shopId, 'credit_note', $today);
                $cnNumber = 'CN-' . $todayStr . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            } while (CreditNote::withTrashed()->where('shop_id', $shopId)->where('credit_note_number', $cnNumber)->exists());

            CreditNote::create([
                'shop_id' => $shopId,
                'customer_id' => $sale->customer_id,
                'sale_id' => $sale->id,
                'credit_note_number' => $cnNumber,
                'total_amount' => $actualRefund,
                'used_amount' => 0.00,
                'remaining_balance' => $actualRefund,
                'status' => 'Active',
                'reason' => $request ? $request->input('reason', 'Sales Return: ' . $sale->sale_number) : 'Sales Return: ' . $sale->sale_number,
            ]);

            $customer = Customer::find($sale->customer_id);
            if ($customer) {
                $customer->increment('credit_balance', $actualRefund);
            }
        } elseif ($isDueAdj && $sale->customer_id) {
            $customer = Customer::find($sale->customer_id);
            if ($customer) {
                $customer->decrement('due_amount', $actualRefund);
            }
        } else {
            $methodMap = [
                'Cash' => 'cash',
                'Bank' => 'bank',
                'UPI' => 'upi',
                'cash' => 'cash',
                'bank' => 'bank',
                'upi' => 'upi',
            ];

            $chosenMethod = $methodMap[$refundMethod] ?? ($methodMap[$sale->payment_type] ?? 'cash');

            CashBook::create([
                'shop_id' => $shopId,
                'type' => 'cash_out',
                'amount' => $actualRefund,
                'payment_method' => $chosenMethod,
                'description' => 'Return: ' . $sale->sale_number,
                'reference_id' => $sale->id,
                'reference_type' => 'sale',
                'transaction_date' => Carbon::now(),
            ]);
        }
    }
}

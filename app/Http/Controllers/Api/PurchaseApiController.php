<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\CashBook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PurchaseApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        // Automatically sync purchase statuses with current supplier due balances
        $supplierIds = Supplier::where('shop_id', $shopId)->pluck('id');
        foreach ($supplierIds as $supId) {
            SupplierApiController::syncSupplierPurchaseStatuses($supId, $shopId);
        }

        $query = Purchase::where('shop_id', $shopId)->with(['supplier', 'items.product']);

        if ($request->filled('updated_since')) {
            $validator = Validator::make($request->only('updated_since'), [
                'updated_since' => 'date',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $query->withTrashed()->where('updated_at', '>=', Carbon::parse($request->updated_since));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'Completed') {
                $query->whereIn('status', ['Completed', 'Unpaid', 'Partially Returned']);
            } elseif ($request->status === 'Returned') {
                $query->whereIn('status', ['Returned', 'Partially Returned']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = $request->input('per_page', 10);
            $purchases = $query->orderBy('purchase_date', 'desc')->paginate($perPage);
        } else {
            $purchases = $query->orderBy('purchase_date', 'desc')->get();
        }
        return response()->json($purchases);
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_type' => 'required|string|in:Cash,Bank,UPI,Credit',
            'purchase_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $totalAmount = (float)$request->total_amount;
        $discount = (float)($request->discount ?? 0);

        if ($request->has('paid_amount')) {
            $paidAmount = min($totalAmount, max(0, (float)$request->paid_amount));
        } else {
            $paidAmount = ($request->payment_type === 'Credit') ? 0.00 : $totalAmount;
        }

        $dueAmount = max(0, $totalAmount - $paidAmount);

        if ($dueAmount > 0 && empty($request->supplier_id)) {
            return response()->json(['errors' => ['supplier_id' => ['Supplier selection is required for purchases with unpaid due balance.']]], 422);
        }

        if ($dueAmount <= 0) {
            $status = 'Completed';
        } elseif ($paidAmount > 0) {
            $status = 'Partially Paid';
        } else {
            $status = 'Unpaid';
        }

        return DB::transaction(function () use ($request, $shopId, $totalAmount, $discount, $paidAmount, $dueAmount, $status) {
            $today = Carbon::now();
            $todayStr = $today->format('Ymd');
            do {
                $nextNumber = \App\Models\InvoiceCounter::nextNumber($shopId, 'purchase', $today);
                $purchaseNumber = 'PUR-' . $todayStr . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            } while (Purchase::withTrashed()->where('shop_id', $shopId)->where('purchase_number', $purchaseNumber)->exists());

            $purchaseDate = $request->filled('purchase_date') ? Carbon::parse($request->purchase_date) : Carbon::now();
            $purchase = Purchase::create([
                'shop_id' => $shopId,
                'supplier_id' => $request->supplier_id,
                'purchase_number' => $purchaseNumber,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'paid_amount' => $paidAmount,
                'payment_type' => $request->payment_type,
                'status' => $status,
                'purchase_date' => $purchaseDate,
                'paid_date' => $status === 'Completed' ? $purchaseDate : null,
            ]);

            foreach ($request->items as $item) {
                $itemQty = (int)$item['quantity'];
                $itemPrice = (float)$item['purchase_price'];
                $itemDiscount = (float)($item['discount'] ?? 0);

                // Create Purchase Item
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $itemQty,
                    'purchase_price' => $itemPrice,
                    'discount' => $itemDiscount,
                ]);

                // Increment Product Stock and update purchase price (COGS reduced by scheme discount)
                $product = Product::findOrFail($item['product_id']);
                $product->increment('stock', $itemQty);

                // Calculate Net discounted unit cost
                $netUnitCost = round((($itemQty * $itemPrice) - $itemDiscount) / $itemQty, 2);
                $product->update(['purchase_price' => $netUnitCost]);
            }

            // Adjust Supplier Dues for unpaid portion
            if ($dueAmount > 0 && $request->supplier_id) {
                $supplier = Supplier::findOrFail($request->supplier_id);
                $supplier->increment('due_amount', $dueAmount);
            }

            // Log in Cash Book for the paid portion
            if ($paidAmount > 0) {
                $methodMap = [
                    'Cash' => 'cash',
                    'Bank' => 'bank',
                    'UPI' => 'upi',
                    'Credit' => 'cash',
                ];

                $desc = 'Purchase: ' . $purchaseNumber;
                if ($dueAmount > 0) {
                    $desc .= ' (Paid: ₹' . number_format($paidAmount, 2) . ', Due: ₹' . number_format($dueAmount, 2) . ')';
                }

                CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_out',
                    'amount' => $paidAmount,
                    'payment_method' => $methodMap[$request->payment_type] ?? 'cash',
                    'description' => $desc,
                    'reference_id' => $purchase->id,
                    'reference_type' => 'purchase',
                    'transaction_date' => Carbon::now(),
                ]);
            }

            return response()->json($purchase->load('items.product', 'supplier'), 201);
        });
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $purchase = Purchase::where('shop_id', $shopId)->with('items.product', 'supplier')->findOrFail($id);
        return response()->json($purchase);
    }

    public function update(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $purchase = Purchase::where('shop_id', $shopId)->with('items')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'payment_type' => 'sometimes|required|string|in:Cash,UPI,Bank,Credit',
            'purchase_date' => 'sometimes|required|date',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.purchase_price' => 'required_with:items|numeric|min:0',
            'total_amount' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('items')) {
            if ($purchase->status !== 'Completed') {
                return response()->json(['message' => 'Cannot edit items of a returned purchase.'], 400);
            }

            return DB::transaction(function () use ($request, $purchase, $shopId) {
                $oldPaymentType = $purchase->payment_type;
                $oldSupplierId = $purchase->supplier_id;

                // Revert stock for the items currently on this purchase
                foreach ($purchase->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('stock', $item->quantity);
                    }
                }

                // Revert old payment effects
                if ($oldPaymentType === 'Credit' && $oldSupplierId) {
                    $oldSupplier = Supplier::find($oldSupplierId);
                    if ($oldSupplier) {
                        $oldSupplier->decrement('due_amount', $purchase->total_amount);
                    }
                } else {
                    CashBook::where('shop_id', $shopId)
                        ->where('reference_type', 'purchase')
                        ->where('reference_id', $purchase->id)
                        ->delete();
                }

                // Replace items with the new set
                $purchase->items()->delete();
                foreach ($request->items as $item) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'purchase_price' => $item['purchase_price'],
                    ]);
                    $product = Product::findOrFail($item['product_id']);
                    $product->increment('stock', $item['quantity']);
                    $product->update(['purchase_price' => $item['purchase_price']]);
                }

                if ($request->has('supplier_id')) {
                    $purchase->supplier_id = $request->supplier_id;
                }
                if ($request->has('payment_type')) {
                    $purchase->payment_type = $request->payment_type;
                }
                $purchase->total_amount = $request->input('total_amount', $purchase->total_amount);
                $purchase->save();

                $newPaymentType = $purchase->payment_type;
                $newSupplierId = $purchase->supplier_id;

                // Apply new payment effects
                if ($newPaymentType === 'Credit' && $newSupplierId) {
                    $newSupplier = Supplier::find($newSupplierId);
                    if ($newSupplier) {
                        $newSupplier->increment('due_amount', $purchase->total_amount);
                    }
                } elseif ($newPaymentType !== 'Credit') {
                    $methodMap = [
                        'Cash' => 'cash',
                        'Bank' => 'bank',
                        'UPI' => 'upi'
                    ];
                    CashBook::create([
                        'shop_id' => $shopId,
                        'type' => 'cash_out',
                        'amount' => $purchase->total_amount,
                        'payment_method' => $methodMap[$newPaymentType] ?? 'cash',
                        'description' => 'Purchase updated: ' . $purchase->purchase_number,
                        'reference_id' => $purchase->id,
                        'reference_type' => 'purchase',
                        'transaction_date' => $purchase->purchase_date,
                    ]);
                }

                return response()->json($purchase->load('items.product', 'supplier'));
            });
        }

        return DB::transaction(function () use ($request, $purchase, $shopId) {
            $oldPaymentType = $purchase->payment_type;
            $oldSupplierId = $purchase->supplier_id;
            $oldTotalAmount = $purchase->total_amount;

            if ($request->has('supplier_id')) {
                $purchase->supplier_id = $request->supplier_id;
            }
            if ($request->has('payment_type')) {
                $purchase->payment_type = $request->payment_type;
            }
            if ($request->has('purchase_date')) {
                $purchase->purchase_date = Carbon::parse($request->purchase_date);
            }
            $purchase->save();

            $newPaymentType = $purchase->payment_type;
            $newSupplierId = $purchase->supplier_id;

            // Revert old payment effects
            if ($oldPaymentType === 'Credit' && $oldSupplierId) {
                $oldSupplier = Supplier::find($oldSupplierId);
                if ($oldSupplier) {
                    $oldSupplier->decrement('due_amount', $oldTotalAmount);
                }
            } else {
                CashBook::where('shop_id', $shopId)
                    ->where('reference_type', 'purchase')
                    ->where('reference_id', $purchase->id)
                    ->delete();
            }

            // Apply new payment effects
            if ($newPaymentType === 'Credit' && $newSupplierId) {
                $newSupplier = Supplier::find($newSupplierId);
                if ($newSupplier) {
                    $newSupplier->increment('due_amount', $oldTotalAmount);
                }
            } elseif ($newPaymentType !== 'Credit') {
                $methodMap = [
                    'Cash' => 'cash',
                    'Bank' => 'bank',
                    'UPI' => 'upi'
                ];
                CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_out',
                    'amount' => $oldTotalAmount,
                    'payment_method' => $methodMap[$newPaymentType] ?? 'cash',
                    'description' => 'Purchase updated: ' . $purchase->purchase_number,
                    'reference_id' => $purchase->id,
                    'reference_type' => 'purchase',
                    'transaction_date' => $purchase->purchase_date,
                ]);
            }

            return response()->json($purchase->load('supplier'));
        });
    }

    /**
     * Cancel a posted purchase invoice with full reversal of stock, dues, and cashbook audit trail.
     */
    public function cancel(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $purchase = Purchase::where('shop_id', $shopId)->with(['items', 'supplier'])->findOrFail($id);

        if ($purchase->status === 'Cancelled') {
            return response()->json(['message' => 'Purchase is already cancelled.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|min:3|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reason = $request->input('cancellation_reason');
        $userId = auth()->id();

        return DB::transaction(function () use ($purchase, $reason, $shopId, $userId) {
            // 1. Stock Reversal (decrement stock for unreturned items)
            foreach ($purchase->items as $item) {
                $unreturnedQty = $item->quantity - ($item->returned_quantity ?? 0);
                if ($unreturnedQty > 0) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('stock', $unreturnedQty);
                    }
                }
            }

            // 2. Supplier Khata / Due Balance Reversal
            if ($purchase->supplier_id) {
                $supplier = Supplier::find($purchase->supplier_id);
                if ($supplier) {
                    $unpaidDue = max(0, (float)$purchase->total_amount - (float)($purchase->paid_amount ?? 0));
                    if ($unpaidDue > 0) {
                        $supplier->decrement('due_amount', min($unpaidDue, (float)$supplier->due_amount));
                    }
                }
            }

            // 3. CashBook Reversal: Post an explicit cash_in reversal entry for audit integrity
            $actualPaid = (float)($purchase->paid_amount ?? 0);
            if ($actualPaid > 0) {
                $methodMap = [
                    'Cash' => 'cash',
                    'Bank' => 'bank',
                    'UPI' => 'upi',
                    'Credit' => 'cash',
                ];

                CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_in',
                    'amount' => $actualPaid,
                    'payment_method' => $methodMap[$purchase->payment_type] ?? 'cash',
                    'description' => 'Reversal (Cancelled): ' . $purchase->purchase_number . ' - ' . $reason,
                    'reference_id' => $purchase->id,
                    'reference_type' => 'purchase_cancel',
                    'transaction_date' => Carbon::now(),
                ]);
            }

            // 4. Update Purchase status and record cancellation reason (remains visible in DB and UI)
            $purchase->update([
                'status' => 'Cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => Carbon::now(),
                'cancelled_by' => $userId,
            ]);

            return response()->json($purchase->load('items.product', 'supplier'));
        });
    }

    public function destroy(Request $request, $id)
    {
        $reason = $request->input('cancellation_reason', 'Cancelled by user');
        $request->merge(['cancellation_reason' => $reason]);
        return $this->cancel($request, $id);
    }

    /**
     * Process purchase return.
     */
    public function returnPurchase(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $purchase = Purchase::where('shop_id', $shopId)->with('items')->findOrFail($id);

        if ($purchase->status === 'Returned') {
            return response()->json(['message' => 'Purchase is already returned.'], 400);
        }

        // If request has items (partial return)
        if ($request->has('items') && is_array($request->items)) {
            $validator = Validator::make($request->all(), [
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return DB::transaction(function () use ($purchase, $request, $shopId) {
                foreach ($request->items as $returnItem) {
                    $productId = $returnItem['product_id'];
                    $returnQty = $returnItem['quantity'];

                    // Find corresponding purchase item
                    $purchaseItem = $purchase->items()->where('product_id', $productId)->first();
                    if (!$purchaseItem) {
                        return response()->json(['message' => 'Product not found in this purchase.'], 400);
                    }

                    $availableToReturn = $purchaseItem->quantity - $purchaseItem->returned_quantity;
                    if ($returnQty > $availableToReturn) {
                        return response()->json(['message' => "Cannot return more than available quantity ({$availableToReturn}) for product ID {$productId}."], 400);
                    }

                    // Decrement Product Stock (since we returned them to supplier, inventory decreases)
                    $product = Product::findOrFail($productId);
                    $product->decrement('stock', $returnQty);

                    // Update returned_quantity of the Purchase Item
                    $purchaseItem->increment('returned_quantity', $returnQty);
                }

                // Recalculate total amount from remaining items
                $allPurchaseItems = $purchase->items()->get();
                $newTotalAmount = 0;
                foreach ($allPurchaseItems as $item) {
                    $netQty = $item->quantity - $item->returned_quantity;
                    $itemDiscount = (float)($item->discount ?? 0);
                    $netItemTotal = ($netQty * $item->purchase_price) - ($item->quantity > 0 ? ($itemDiscount * $netQty / $item->quantity) : 0);
                    $newTotalAmount += max(0, $netItemTotal);
                }

                $actualRefund = max(0, $purchase->total_amount - $newTotalAmount);

                // Adjust Supplier Due first if there was an unpaid balance on this purchase
                $currentDue = max(0, $purchase->total_amount - ($purchase->paid_amount ?? 0));
                $dueReduction = min($currentDue, $actualRefund);
                $cashRefund = max(0, $actualRefund - $dueReduction);

                if ($dueReduction > 0 && $purchase->supplier_id) {
                    $supplier = Supplier::find($purchase->supplier_id);
                    if ($supplier) {
                        $supplier->decrement('due_amount', $dueReduction);
                    }
                }

                // Log refund in Cash Book (Refund received from supplier) for actual cash paid portion
                if ($cashRefund > 0) {
                    $methodMap = [
                        'Cash' => 'cash',
                        'Bank' => 'bank',
                        'UPI' => 'upi',
                        'Credit' => 'cash',
                    ];

                    CashBook::create([
                        'shop_id' => $shopId,
                        'type' => 'cash_in',
                        'amount' => $cashRefund,
                        'payment_method' => $methodMap[$purchase->payment_type] ?? 'cash',
                        'description' => 'Partial Return: ' . $purchase->purchase_number,
                        'reference_id' => $purchase->id,
                        'reference_type' => 'purchase',
                        'transaction_date' => Carbon::now(),
                    ]);
                }

                // Update Purchase status and balances
                $hasRemaining = false;
                foreach ($allPurchaseItems as $item) {
                    if ($item->quantity > $item->returned_quantity) {
                        $hasRemaining = true;
                        break;
                    }
                }

                $purchase->paid_amount = max(0, ($purchase->paid_amount ?? 0) - $cashRefund);
                $purchase->total_amount = $newTotalAmount;
                if (!$hasRemaining) {
                    $purchase->status = 'Returned';
                } else {
                    $purchase->status = 'Partially Returned';
                }
                $purchase->save();

                return response()->json($purchase->load('items.product', 'supplier'));
            });
        }

        // Otherwise process full return
        return DB::transaction(function () use ($purchase, $shopId) {
            // 1. Decrement Product Stocks & Update returned_quantity of all items
            $totalRefundAmount = 0;
            foreach ($purchase->items as $item) {
                $unreturnedQty = $item->quantity - $item->returned_quantity;
                if ($unreturnedQty > 0) {
                    $product = Product::findOrFail($item->product_id);
                    $product->decrement('stock', $unreturnedQty);

                    $item->returned_quantity = $item->quantity;
                    $item->save();

                    $itemDiscount = (float)($item->discount ?? 0);
                    $netItemTotal = ($unreturnedQty * $item->purchase_price) - ($item->quantity > 0 ? ($itemDiscount * $unreturnedQty / $item->quantity) : 0);
                    $totalRefundAmount += max(0, $netItemTotal);
                }
            }

            // 2. Adjust Supplier Due first for unpaid portion
            $currentDue = max(0, $purchase->total_amount - ($purchase->paid_amount ?? 0));
            $dueReduction = min($currentDue, $totalRefundAmount);
            $cashRefund = max(0, $totalRefundAmount - $dueReduction);

            if ($dueReduction > 0 && $purchase->supplier_id) {
                $supplier = Supplier::find($purchase->supplier_id);
                if ($supplier) {
                    $supplier->decrement('due_amount', $dueReduction);
                }
            }

            // 3. Log cash in in Cash Book for cash refund portion
            if ($cashRefund > 0) {
                $methodMap = [
                    'Cash' => 'cash',
                    'Bank' => 'bank',
                    'UPI' => 'upi',
                    'Credit' => 'cash',
                ];

                CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_in',
                    'amount' => $cashRefund,
                    'payment_method' => $methodMap[$purchase->payment_type] ?? 'cash',
                    'description' => 'Purchase Return: ' . $purchase->purchase_number,
                    'reference_id' => $purchase->id,
                    'reference_type' => 'purchase',
                    'transaction_date' => Carbon::now(),
                ]);
            }

            $purchase->status = 'Returned';
            $purchase->total_amount = 0;
            $purchase->paid_amount = 0;
            $purchase->save();

            return response()->json($purchase->load('items.product', 'supplier'));
        });
    }
}

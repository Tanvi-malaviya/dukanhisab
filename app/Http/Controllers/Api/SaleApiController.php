<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CashBook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SaleApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        
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
            'payment_type' => 'required|string|in:Cash,UPI,Bank,Credit',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selling_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request, $shopId) {
            $today = Carbon::now();
            $todayStr = $today->format('Ymd');
            $nextNumber = \App\Models\InvoiceCounter::nextNumber($shopId, 'sale', $today);
            $saleNumber = 'INV-' . $todayStr . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $sale = Sale::create([
                'shop_id' => $shopId,
                'customer_id' => $request->customer_id,
                'sale_number' => $saleNumber,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'grand_total' => $request->grand_total,
                'payment_type' => $request->payment_type,
                'status' => $request->payment_type === 'Credit' ? 'Unpaid' : 'Completed',
                'sale_date' => Carbon::now(),
            ]);

            foreach ($request->items as $item) {
                // Create Sale Item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['selling_price'],
                ]);

                // Decrement Product Stock
                $product = Product::findOrFail($item['product_id']);
                $product->decrement('stock', $item['quantity']);
            }

            // Adjust Customer Dues if Credit sale
            if ($request->payment_type === 'Credit' && $request->customer_id) {
                $customer = Customer::findOrFail($request->customer_id);
                $customer->increment('due_amount', $request->grand_total);
            }

            // Log in Cash Book for Non-Credit sales
            if ($request->payment_type !== 'Credit') {
                $methodMap = [
                    'Cash' => 'cash',
                    'Bank' => 'bank',
                    'UPI' => 'upi'
                ];
                
                CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_in',
                    'amount' => $request->grand_total,
                    'payment_method' => $methodMap[$request->payment_type] ?? 'cash',
                    'description' => 'Sale: ' . $saleNumber,
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

    public function destroy(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $sale = Sale::where('shop_id', $shopId)->findOrFail($id);

        return DB::transaction(function () use ($sale, $shopId) {
            // Reverse stock and dues if status is not already Returned
            if ($sale->status !== 'Returned') {
                foreach ($sale->items as $item) {
                    $product = Product::findOrFail($item->product_id);
                    $product->increment('stock', $item->quantity);
                }

                if ($sale->payment_type === 'Credit' && $sale->customer_id) {
                    $customer = Customer::findOrFail($sale->customer_id);
                    $customer->decrement('due_amount', $sale->grand_total);
                }

                // Delete related Cash Book entry
                CashBook::where('shop_id', $shopId)
                    ->where('reference_type', 'sale')
                    ->where('reference_id', $sale->id)
                    ->delete();
            }

            $sale->delete();
            return response()->json(null, 204);
        });
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

            return DB::transaction(function () use ($sale, $request, $shopId) {
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

                    // Restore Product Stock
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
                    $newSubtotal += $netQty * $item->selling_price;
                }

                // Recalculate grand total. Ensure discount doesn't exceed new subtotal.
                $newDiscount = min($sale->discount, $newSubtotal);
                $newGrandTotal = max(0, $newSubtotal - $newDiscount);
                
                $actualRefund = $sale->grand_total - $newGrandTotal;

                // Adjust Customer Due if Credit sale
                if ($sale->payment_type === 'Credit' && $sale->customer_id) {
                    $customer = Customer::findOrFail($sale->customer_id);
                    $customer->decrement('due_amount', $actualRefund);
                }

                // Log refund in Cash Book (Refund) for non-credit sales
                if ($sale->payment_type !== 'Credit' && $actualRefund > 0) {
                    $methodMap = [
                        'Cash' => 'cash',
                        'Bank' => 'bank',
                        'UPI' => 'upi'
                    ];

                    CashBook::create([
                        'shop_id' => $shopId,
                        'type' => 'cash_out',
                        'amount' => $actualRefund,
                        'payment_method' => $methodMap[$sale->payment_type] ?? 'cash',
                        'description' => 'Partial Return: ' . $sale->sale_number,
                        'reference_id' => $sale->id,
                        'reference_type' => 'sale',
                        'transaction_date' => Carbon::now(),
                    ]);
                }

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

                return response()->json($sale->load('items.product', 'customer'));
            });
        }

        // Otherwise process full return
        return $this->processSaleReturn($sale);
    }

    private function processSaleReturn($sale)
    {
        return DB::transaction(function () use ($sale) {
            // 1. Restore Product Stocks & Update returned_quantity of all items
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

            // 2. Adjust Customer Due if Credit sale
            if ($sale->payment_type === 'Credit' && $sale->customer_id && $actualRefund > 0) {
                $customer = Customer::findOrFail($sale->customer_id);
                $customer->decrement('due_amount', $actualRefund);
            }

            // 3. Log cash out in Cash Book (Refund)
            if ($sale->payment_type !== 'Credit' && $actualRefund > 0) {
                $methodMap = [
                    'Cash' => 'cash',
                    'Bank' => 'bank',
                    'UPI' => 'upi'
                ];

                CashBook::create([
                    'shop_id' => $sale->shop_id,
                    'type' => 'cash_out',
                    'amount' => $actualRefund,
                    'payment_method' => $methodMap[$sale->payment_type] ?? 'cash',
                    'description' => 'Return: ' . $sale->sale_number,
                    'reference_id' => $sale->id,
                    'reference_type' => 'sale',
                    'transaction_date' => Carbon::now(),
                ]);
            }

            $sale->status = 'Returned';
            $sale->grand_total = 0; // Everything is returned
            $sale->save();

            return response()->json($sale->load('items.product', 'customer'));
        });
    }
}

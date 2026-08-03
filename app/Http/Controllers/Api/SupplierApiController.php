<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SupplierApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $query = Supplier::where('shop_id', $shopId);

        if ($request->filled('updated_since')) {
            $validator = Validator::make($request->only('updated_since'), [
                'updated_since' => 'date',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $query->withTrashed()->where('updated_at', '>=', Carbon::parse($request->updated_since));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = $request->input('per_page', 10);
            $suppliers = $query->paginate($perPage);
        } else {
            $suppliers = $query->get();
        }
        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'due_amount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['shop_id'] = $shopId;
        if (!isset($data['due_amount'])) {
            $data['due_amount'] = 0.00;
        }

        $supplier = Supplier::create($data);
        return response()->json($supplier, 201);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $supplier = Supplier::where('shop_id', $shopId)->findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $supplier = Supplier::where('shop_id', $shopId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'due_amount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $supplier->update($request->all());
        return response()->json($supplier);
    }

    public function destroy(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $supplier = Supplier::where('shop_id', $shopId)->findOrFail($id);
        $supplier->delete();
        return response()->json(null, 204);
    }

    /**
     * Record payment / settlement paid to a Supplier for outstanding due (Udhar).
     */
    public function recordPayment(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $supplier = Supplier::where('shop_id', $shopId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:Cash,Bank,UPI,cash,bank,upi',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $amount = (float) $request->amount;
        $currentDue = (float) $supplier->due_amount;

        if ($amount > $currentDue) {
            return response()->json([
                'message' => 'Payment amount (₹' . number_format($amount, 2) . ') cannot exceed current supplier due balance (₹' . number_format($currentDue, 2) . ').'
            ], 422);
        }

        // 1. Decrement Supplier Due Balance
        $supplier->decrement('due_amount', $amount);
        $supplier->refresh();

        // 2. Add CashBook Entry (Cash Out)
        $paymentMethod = strtolower($request->payment_method);
        \App\Models\CashBook::create([
            'shop_id' => $shopId,
            'type' => 'cash_out',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'description' => 'Supplier Udhar Payment: ' . $supplier->name . ($request->filled('note') ? ' (' . $request->note . ')' : ''),
            'reference_id' => $supplier->id,
            'reference_type' => 'supplier_payment',
            'transaction_date' => Carbon::now(),
        ]);

        // 3. Sync Purchase Statuses based on Supplier Due Balance
        static::syncSupplierPurchaseStatuses($supplier->id, $shopId);

        return response()->json([
            'message' => 'Supplier due payment recorded successfully.',
            'supplier' => $supplier,
            'paid_amount' => $amount,
            'remaining_due' => $supplier->due_amount
        ], 200);
    }

    public static function syncSupplierPurchaseStatuses($supplierId, $shopId)
    {
        $supplier = Supplier::where('shop_id', $shopId)->find($supplierId);
        if (!$supplier) return;

        $creditPurchases = \App\Models\Purchase::where('shop_id', $shopId)
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['Unpaid', 'Completed'])
            ->where('payment_type', 'Credit')
            ->orderBy('purchase_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalCredit = $creditPurchases->sum('total_amount');
        $due = (float) $supplier->due_amount;
        $paidAmount = max(0, $totalCredit - $due);

        $rem = $paidAmount;
        foreach ($creditPurchases as $purchase) {
            $total = (float) $purchase->total_amount;
            if ($rem >= $total) {
                if ($purchase->status !== 'Completed') {
                    $purchase->update(['status' => 'Completed']);
                }
                $rem -= $total;
            } else {
                if ($purchase->status !== 'Unpaid') {
                    $purchase->update(['status' => 'Unpaid']);
                }
            }
        }
    }
}

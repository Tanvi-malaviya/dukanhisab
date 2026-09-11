<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CustomerApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $query = Customer::where('shop_id', $shopId);

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
            $customers = $query->paginate($perPage);
        } else {
            $customers = $query->get();
        }
        return response()->json($customers);
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

        $customer = Customer::create($data);
        return response()->json($customer, 201);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $customer = Customer::where('shop_id', $shopId)->findOrFail($id);
        return response()->json($customer);
    }

    public function update(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $customer = Customer::where('shop_id', $shopId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'due_amount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer->update($request->all());
        return response()->json($customer);
    }

    public function destroy(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $customer = Customer::where('shop_id', $shopId)->findOrFail($id);
        $customer->delete();
        return response()->json(null, 204);
    }

    /**
     * Record payment / settlement received from a Customer for outstanding due (Udhar).
     */
    public function recordPayment(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $customer = Customer::where('shop_id', $shopId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:Cash,Bank,UPI,cash,bank,upi',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $amount = (float) $request->amount;
        $currentDue = (float) $customer->due_amount;

        if ($amount > $currentDue) {
            return response()->json([
                'message' => 'Payment amount (₹' . number_format($amount, 2) . ') cannot exceed current due balance (₹' . number_format($currentDue, 2) . ').'
            ], 422);
        }

        // 1. Decrement Customer Due Balance
        $customer->decrement('due_amount', $amount);
        $customer->refresh();

        // 2. Add CashBook Entry (Cash In)
        $paymentMethod = strtolower($request->payment_method);
        \App\Models\CashBook::create([
            'shop_id' => $shopId,
            'type' => 'cash_in',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'description' => 'Udhar Repayment: ' . $customer->name . ($request->filled('note') ? ' (' . $request->note . ')' : ''),
            'reference_id' => $customer->id,
            'reference_type' => 'customer_payment',
            'transaction_date' => Carbon::now(),
        ]);

        // 3. Sync Sale Statuses based on Customer Due Balance
        static::syncCustomerSaleStatuses($customer->id, $shopId);

        return response()->json([
            'message' => 'Udhar repayment recorded successfully.',
            'customer' => $customer,
            'paid_amount' => $amount,
            'remaining_due' => $customer->due_amount
        ], 200);
    }

    public static function syncCustomerSaleStatuses($customerId, $shopId)
    {
        $customer = Customer::where('shop_id', $shopId)->find($customerId);
        if (!$customer) return;

        $creditSales = \App\Models\Sale::where('shop_id', $shopId)
            ->where('customer_id', $customerId)
            ->whereIn('status', ['Unpaid', 'Partially Paid', 'Completed'])
            ->where('payment_type', 'Credit')
            ->orderBy('sale_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalCredit = $creditSales->sum('grand_total');
        $due = (float) $customer->due_amount;
        $paidAmount = max(0, $totalCredit - $due);

        $rem = $paidAmount;
        foreach ($creditSales as $sale) {
            $total = (float) $sale->grand_total;
            if ($rem >= $total) {
                if ($sale->status !== 'Completed') {
                    $sale->update([
                        'status' => 'Completed',
                        'paid_amount' => $total,
                        'paid_date' => Carbon::now(),
                    ]);
                } else {
                    $sale->update([
                        'paid_amount' => $total,
                        'paid_date' => $sale->paid_date ?? Carbon::now(),
                    ]);
                }
                $rem -= $total;
            } elseif ($rem > 0) {
                $sale->update([
                    'status' => 'Partially Paid',
                    'paid_amount' => $rem,
                    'paid_date' => null,
                ]);
                $rem = 0;
            } else {
                $sale->update([
                    'status' => 'Unpaid',
                    'paid_amount' => 0.00,
                    'paid_date' => null,
                ]);
            }
        }
    }
}

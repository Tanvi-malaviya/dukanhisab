<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashBook;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CashBookApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $query = CashBook::where('shop_id', $shopId);

        if ($request->filled('updated_since')) {
            $validator = Validator::make($request->only('updated_since'), [
                'updated_since' => 'date',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $query->withTrashed()->where('updated_at', '>=', Carbon::parse($request->updated_since));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Cash Book shows only physical cash by default.
        // UPI/Bank entries are tracked separately in Bank Accounts.
        $query->where('payment_method', $request->filled('payment_method') ? $request->payment_method : 'cash');

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        // Return totals alongside transactions so frontend can display correct cash-only summary
        $cashIn  = $transactions->where('type', 'cash_in')->sum('amount');
        $cashOut = $transactions->where('type', 'cash_out')->sum('amount');

        // For backward compatibility with existing web panel and mobile apps expecting an array:
        // If 'with_totals' query param is passed, return object with totals; otherwise return transactions collection
        // with custom headers or if request expects array.
        if ($request->boolean('with_totals')) {
            return response()->json([
                'transactions' => $transactions,
                'total_cash_in'  => round($cashIn, 2),
                'total_cash_out' => round($cashOut, 2),
                'net_balance'    => round($cashIn - $cashOut, 2),
            ]);
        }

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'type'        => 'required|string|in:cash_in,cash_out',
            'amount'      => 'required|numeric|min:0.01',
            // Manual Cash Book entries are always physical cash.
            // UPI/Bank transactions should be recorded via Bank Accounts.
            'payment_method' => 'sometimes|string|in:cash',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data                     = $request->all();
        $data['shop_id']          = $shopId;
        $data['payment_method']   = 'cash'; // Always cash for manual Cash Book entries
        $data['transaction_date'] = Carbon::now();

        $transaction = CashBook::create($data);
        return response()->json($transaction, 201);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $transaction = CashBook::where('shop_id', $shopId)->findOrFail($id);
        return response()->json($transaction);
    }

    public function destroy(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $transaction = CashBook::where('shop_id', $shopId)->findOrFail($id);
        
        // Safety: Manual entries are deletable, sale/purchase references should not be deleted directly without returning
        if ($transaction->reference_type !== null) {
            return response()->json([
                'message' => 'System generated transactions cannot be deleted directly. Please void the source Sale/Purchase instead.'
            ], 400);
        }

        $transaction->delete();
        return response()->json(null, 204);
    }
}

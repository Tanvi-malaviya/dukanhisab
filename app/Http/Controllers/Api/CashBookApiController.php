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
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();
        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:cash_in,cash_out',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,bank,upi',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['shop_id'] = $shopId;
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

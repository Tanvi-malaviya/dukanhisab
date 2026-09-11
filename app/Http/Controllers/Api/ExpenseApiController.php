<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashBook;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ExpenseApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $query = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where(function ($q) {
                $q->whereNull('reference_type')
                  ->orWhere('reference_type', 'expense');
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%");
        }

        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = $request->input('per_page', 10);
            $expenses = $query->orderBy('transaction_date', 'desc')->paginate($perPage);
        } else {
            $expenses = $query->orderBy('transaction_date', 'desc')->get();
        }
        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,bank,upi',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense = CashBook::create([
            'shop_id' => $shopId,
            'type' => 'cash_out',
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'description' => $request->description,
            'reference_type' => 'expense',
            'transaction_date' => Carbon::now(),
        ]);

        return response()->json($expense, 201);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $expense = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where(function ($q) {
                $q->whereNull('reference_type')
                  ->orWhere('reference_type', 'expense');
            })
            ->findOrFail($id);
        return response()->json($expense);
    }

    public function update(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $expense = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where(function ($q) {
                $q->whereNull('reference_type')
                  ->orWhere('reference_type', 'expense');
            })
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric|min:0.01',
            'payment_method' => 'sometimes|required|string|in:cash,bank,upi',
            'description' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense->update($request->only('amount', 'payment_method', 'description'));
        return response()->json($expense);
    }

    public function destroy(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $expense = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where(function ($q) {
                $q->whereNull('reference_type')
                  ->orWhere('reference_type', 'expense');
            })
            ->findOrFail($id);

        $expense->delete();
        return response()->json(null, 204);
    }
}

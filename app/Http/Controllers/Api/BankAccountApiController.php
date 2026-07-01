<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashBook;

class BankAccountApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        
        // Calculate current bank balance from CashBook records
        $bankIn = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_in')
            ->whereIn('payment_method', ['bank', 'upi'])
            ->sum('amount');

        $bankOut = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->whereIn('payment_method', ['bank', 'upi'])
            ->sum('amount');

        $bankBalance = $bankIn - $bankOut;

        // Return a mock listing of bank accounts for UI purposes, with the aggregated balance
        return response()->json([
            [
                'id' => 1,
                'name' => 'Primary Bank Account',
                'account_number' => 'XXXX-XXXX-1234',
                'balance' => $bankBalance,
            ]
        ]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Manual account creation is not supported.'], 400);
    }

    public function show($id)
    {
        return response()->json([
            'id' => $id,
            'name' => 'Primary Bank Account',
            'account_number' => 'XXXX-XXXX-1234',
            'balance' => 0,
        ]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Not supported.'], 400);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Not supported.'], 400);
    }
}

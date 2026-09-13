<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashBook;
use App\Models\CashRegisterClosure;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RegisterClosureApiController extends Controller
{
    /**
     * Get current status of the register (expected cash in drawer, today's breakdown).
     */
    public function currentStatus(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $today = Carbon::today();

        // 1. Current physical cash in hand according to the system
        $totalCashIn = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_in')
            ->where('payment_method', 'cash')
            ->sum('amount');

        $totalCashOut = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where('payment_method', 'cash')
            ->sum('amount');

        $expectedCashInHand = (float) ($totalCashIn - $totalCashOut);

        // 2. Today's Cash Movement
        $todayCashIn = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_in')
            ->where('payment_method', 'cash')
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        $todayCashOut = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where('payment_method', 'cash')
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        // 3. Check if already closed today
        $lastClosure = CashRegisterClosure::where('shop_id', $shopId)
            ->whereDate('closing_date', $today)
            ->latest('id')
            ->first();

        // 4. Opening cash before today's transactions
        $openingBalance = (float) ($expectedCashInHand - ($todayCashIn - $todayCashOut));

        $data = [
            'closing_date' => $today->toDateString(),
            'opening_balance' => round($openingBalance, 2),
            'cash_in' => round((float) $todayCashIn, 2),
            'cash_out' => round((float) $todayCashOut, 2),
            'expected_cash' => round($expectedCashInHand, 2),
            'is_closed_today' => !empty($lastClosure),
            'last_closure' => $lastClosure,
        ];

        return response()->json(array_merge(['status' => 'success', 'data' => $data], $data));
    }

    /**
     * List all register closures.
     */
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $closures = CashRegisterClosure::where('shop_id', $shopId)
            ->with('closedByUser:id,name')
            ->orderBy('closing_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json([
            'status' => 'success',
            'data' => $closures
        ]);
    }

    /**
     * Submit and save daily cash register closing.
     */
    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'actual_cash' => 'required|numeric|min:0',
            'denominations' => 'nullable|array',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Calculate expected cash in drawer
        $totalCashIn = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_in')
            ->where('payment_method', 'cash')
            ->sum('amount');

        $totalCashOut = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where('payment_method', 'cash')
            ->sum('amount');

        $expectedCash = (float) ($totalCashIn - $totalCashOut);
        $actualCash = (float) $request->actual_cash;
        $difference = round($actualCash - $expectedCash, 2);

        $today = Carbon::today();
        $todayCashIn = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_in')
            ->where('payment_method', 'cash')
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        $todayCashOut = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where('payment_method', 'cash')
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        $openingBalance = (float) ($expectedCash - ($todayCashIn - $todayCashOut));

        $existingClosure = CashRegisterClosure::where('shop_id', $shopId)
            ->whereDate('closing_date', $today)
            ->latest('id')
            ->first();

        if ($existingClosure) {
            $existingClosure->update([
                'closed_by_user_id' => $user ? $user->id : null,
                'opening_balance' => round($openingBalance, 2),
                'cash_in' => round((float) $todayCashIn, 2),
                'cash_out' => round((float) $todayCashOut, 2),
                'expected_cash' => round($expectedCash, 2),
                'actual_cash' => round($actualCash, 2),
                'difference' => $difference,
                'denominations' => $request->input('denominations', []),
                'note' => $request->input('note'),
            ]);
            $closure = $existingClosure;
        } else {
            $closure = CashRegisterClosure::create([
                'shop_id' => $shopId,
                'closed_by_user_id' => $user ? $user->id : null,
                'closing_date' => $today,
                'opening_balance' => round($openingBalance, 2),
                'cash_in' => round((float) $todayCashIn, 2),
                'cash_out' => round((float) $todayCashOut, 2),
                'expected_cash' => round($expectedCash, 2),
                'actual_cash' => round($actualCash, 2),
                'difference' => $difference,
                'denominations' => $request->input('denominations', []),
                'note' => $request->input('note'),
            ]);
        }

        $status = $difference == 0 ? 'balanced' : ($difference > 0 ? 'surplus' : 'shortage');
        $closureData = $closure->load('closedByUser:id,name');

        return response()->json([
            'status' => 'success',
            'message' => 'Cash register closed and reconciled successfully.',
            'data' => [
                'expected_cash' => round($expectedCash, 2),
                'actual_cash' => round($actualCash, 2),
                'difference' => $difference,
                'reconciliation_status' => $status,
                'closure' => $closureData,
            ],
            'closure' => $closureData,
            'reconciliation_status' => $status,
        ], 201);
    }
}

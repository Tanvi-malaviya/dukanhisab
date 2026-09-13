<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashBook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BankTransferApiController extends Controller
{
    /**
     * Record a Contra Bank Transfer (Cash Deposit or Bank Withdrawal).
     */
    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:deposit,withdraw',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $type = $request->type;
        $amount = (float) $request->amount;
        $desc = $request->input('description') ?: ($type === 'deposit' ? 'Bank Cash Deposit' : 'Bank Cash Withdrawal');

        return DB::transaction(function () use ($shopId, $type, $amount, $desc) {
            $now = Carbon::now();

            if ($type === 'deposit') {
                // Shift from Physical Cash to Bank Account
                // 1. Cash Out from Cash drawer
                $entry1 = CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_out',
                    'amount' => $amount,
                    'payment_method' => 'cash',
                    'description' => $desc . ' (Cash to Bank)',
                    'reference_type' => 'contra',
                    'transaction_date' => $now,
                ]);

                // 2. Cash In to Bank Account
                $entry2 = CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_in',
                    'amount' => $amount,
                    'payment_method' => 'bank',
                    'description' => $desc . ' (Deposited to Bank)',
                    'reference_type' => 'contra',
                    'transaction_date' => $now,
                ]);
            } else {
                // Shift from Bank Account to Physical Cash
                // 1. Cash Out from Bank Account
                $entry1 = CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_out',
                    'amount' => $amount,
                    'payment_method' => 'bank',
                    'description' => $desc . ' (Withdrawn from Bank)',
                    'reference_type' => 'contra',
                    'transaction_date' => $now,
                ]);

                // 2. Cash In to Cash drawer
                $entry2 = CashBook::create([
                    'shop_id' => $shopId,
                    'type' => 'cash_in',
                    'amount' => $amount,
                    'payment_method' => 'cash',
                    'description' => $desc . ' (Cash from Bank)',
                    'reference_type' => 'contra',
                    'transaction_date' => $now,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Bank transfer recorded successfully.',
                'transfer_type' => $type,
                'amount' => $amount,
                'data' => [
                    'type' => $type,
                    'amount' => $amount,
                ],
                'entries' => [$entry1, $entry2]
            ], 201);
        });
    }
}

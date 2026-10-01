<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Every shop has exactly one bank account — its default — and every bank/UPI entry belongs to it
 * (see CashBook's creating hook). Shop owners can edit its details but can't add or remove accounts.
 */
class BankAccountApiController extends Controller
{
    /**
     * Returned as a one-element list so existing clients that render a list keep working.
     */
    public function index(Request $request)
    {
        $account = BankAccount::defaultForShop($request->attributes->get('shop_id'));
        $account->setAttribute('balance', $account->computeBalance());

        return response()->json([$account]);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $account = BankAccount::where('shop_id', $shopId)->findOrFail($id);
        $account->setAttribute('balance', $account->computeBalance());

        return response()->json($account);
    }

    public function update(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $account = BankAccount::where('shop_id', $shopId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $account->update($request->only(['name', 'account_number', 'bank_name', 'ifsc_code', 'opening_balance']));
        $account->refresh();
        $account->setAttribute('balance', $account->computeBalance());

        return response()->json($account);
    }
}

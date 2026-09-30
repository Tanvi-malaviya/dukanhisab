<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BankAccountApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        // Lazily create the default account so a shop with no bank accounts yet (predates this
        // feature, or has simply never used one) still sees at least one instead of an empty list.
        BankAccount::defaultForShop($shopId);

        $accounts = BankAccount::where('shop_id', $shopId)->orderByDesc('is_default')->orderBy('id')->get();
        $accounts->each(fn (BankAccount $a) => $a->setAttribute('balance', $a->computeBalance()));

        return response()->json($accounts);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $account = BankAccount::where('shop_id', $shopId)->findOrFail($id);
        $account->setAttribute('balance', $account->computeBalance());

        return response()->json($account);
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $hasAny = BankAccount::where('shop_id', $shopId)->exists();

        $account = BankAccount::create([
            'shop_id' => $shopId,
            'name' => $request->name,
            'account_number' => $request->account_number,
            'bank_name' => $request->bank_name,
            'ifsc_code' => $request->ifsc_code,
            'opening_balance' => $request->input('opening_balance', 0),
            // The shop's very first account is automatically the default — there must always be one.
            'is_default' => !$hasAny,
            'status' => 'active',
        ]);

        $account->setAttribute('balance', $account->computeBalance());

        return response()->json($account, 201);
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
            'status' => 'sometimes|required|string|in:active,inactive',
            'make_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->boolean('make_default') && !$account->is_default) {
            DB::transaction(function () use ($account, $shopId) {
                BankAccount::where('shop_id', $shopId)->update(['is_default' => false]);
                $account->update(['is_default' => true]);
            });
        }

        $account->update($request->only(['name', 'account_number', 'bank_name', 'ifsc_code', 'opening_balance', 'status']));
        $account->refresh();
        $account->setAttribute('balance', $account->computeBalance());

        return response()->json($account);
    }

    public function destroy(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $account = BankAccount::where('shop_id', $shopId)->findOrFail($id);

        if ($account->is_default) {
            return response()->json([
                'message' => 'This is the default account and cannot be deleted. Make another account the default first.',
            ], 400);
        }

        if ($account->cashBookEntries()->exists()) {
            return response()->json([
                'message' => 'This account has transactions recorded against it and cannot be deleted — mark it inactive instead.',
            ], 400);
        }

        $account->delete();

        return response()->json(null, 204);
    }
}

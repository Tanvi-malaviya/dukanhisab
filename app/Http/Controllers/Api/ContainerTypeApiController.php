<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContainerMovement;
use App\Models\ContainerType;
use App\Models\Product;
use App\Support\ContainerLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ContainerTypeApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        return response()->json(self::withStats($shopId, ContainerType::where('shop_id', $shopId)->orderBy('name')->get()));
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100', Rule::unique('container_types')->where('shop_id', $shopId)->whereNull('deleted_at')],
            'deposit_amount' => 'required|numeric|min:0',
            'total_owned' => 'nullable|integer|min:0',
        ], [
            'name.unique' => 'A container type with this name already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $type = ContainerType::create([
            'shop_id' => $shopId,
            'name' => $request->name,
            'deposit_amount' => $request->deposit_amount,
            'total_owned' => (int) $request->input('total_owned', 0),
        ]);

        return response()->json(self::withStats($shopId, collect([$type]))->first(), 201);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $type = ContainerType::where('shop_id', $shopId)->findOrFail($id);
        return response()->json(self::withStats($shopId, collect([$type]))->first());
    }

    public function update(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $type = ContainerType::where('shop_id', $shopId)->findOrFail($id);

        // total_owned is changed only through adjust-stock, so every change has a numbered entry.
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:100',
                Rule::unique('container_types')->where('shop_id', $shopId)->whereNull('deleted_at')->ignore($type->id)],
            'deposit_amount' => 'sometimes|required|numeric|min:0',
        ], [
            'name.unique' => 'A container type with this name already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // A new deposit rate applies to containers given from now on; lots already out keep the
        // rate the customer actually paid.
        $type->update($request->only(['name', 'deposit_amount']));

        return response()->json(self::withStats($shopId, collect([$type]))->first());
    }

    public function destroy(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $type = ContainerType::where('shop_id', $shopId)->findOrFail($id);

        $pending = ContainerMovement::where('shop_id', $shopId)->where('container_type_id', $type->id)->openLots()
            ->sum(DB::raw('quantity - closed_quantity'));
        if ($pending > 0) {
            return response()->json([
                'message' => "{$pending} {$type->name} are still with customers. Receive them back before deleting this type.",
            ], 422);
        }

        DB::transaction(function () use ($shopId, $type) {
            Product::where('shop_id', $shopId)->where('container_type_id', $type->id)->update(['container_type_id' => null]);
            $type->delete();
        });

        return response()->json(null, 204);
    }

    public function adjustStock(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $type = ContainerType::where('shop_id', $shopId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|not_in:0',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $entry = ContainerLedger::adjustStock($shopId, $type, (int) $request->quantity, $request->note);

        return response()->json([
            'entry' => $entry,
            'container_type' => self::withStats($shopId, collect([$type->fresh()]))->first(),
        ], 201);
    }

    /**
     * Attach live counts to each type: with_customers, deposit_held, lost and in_shop
     * (in_shop is null when the shop does not track how many it owns).
     */
    public static function withStats(int $shopId, $types)
    {
        $ids = $types->pluck('id');

        $open = DB::table('container_movements as m')
            ->join('container_entries as e', 'e.id', '=', 'm.container_entry_id')
            ->where('m.shop_id', $shopId)
            ->whereIn('m.container_type_id', $ids)
            ->where('m.kind', 'issue')
            ->whereNull('e.reversed_at')
            ->whereColumn('m.closed_quantity', '<', 'm.quantity')
            ->groupBy('m.container_type_id')
            ->selectRaw('m.container_type_id, SUM(m.quantity - m.closed_quantity) as pending, SUM((m.quantity - m.closed_quantity) * m.deposit_per_unit) as deposit')
            ->get()->keyBy('container_type_id');

        $lost = DB::table('container_movements as m')
            ->join('container_entries as e', 'e.id', '=', 'm.container_entry_id')
            ->where('m.shop_id', $shopId)
            ->whereIn('m.container_type_id', $ids)
            ->where('m.kind', 'lost')
            ->whereNull('e.reversed_at')
            ->groupBy('m.container_type_id')
            ->selectRaw('m.container_type_id, SUM(m.quantity) as lost')
            ->pluck('lost', 'container_type_id');

        return $types->map(function ($type) use ($open, $lost) {
            $withCustomers = (int) ($open[$type->id]->pending ?? 0);
            $lostCount = (int) ($lost[$type->id] ?? 0);
            $type->with_customers = $withCustomers;
            $type->deposit_held = round((float) ($open[$type->id]->deposit ?? 0), 2);
            $type->lost = $lostCount;
            $type->in_shop = $type->total_owned > 0 ? $type->total_owned - $withCustomers - $lostCount : null;
            return $type;
        })->values();
    }
}

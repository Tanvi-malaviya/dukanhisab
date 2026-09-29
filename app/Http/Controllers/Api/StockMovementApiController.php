<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\StockMovementLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockMovementApiController extends Controller
{
    use StockMovementLogger;

    /**
     * The shop's full stock-movement log (all products). Supports `updated_since` for the mobile
     * app's incremental sync, and `product_id` for a single product's history screen. The log is
     * append-only, so `updated_since` really means "created since" here.
     */
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $query = StockMovement::where('shop_id', $shopId)->with('product:id,name');

        if ($request->filled('updated_since')) {
            $validator = Validator::make($request->only('updated_since'), ['updated_since' => 'date']);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $query->where('created_at', '>=', Carbon::parse($request->updated_since));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('page') || $request->boolean('paginate')) {
            $movements = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 50));
        } else {
            $movements = $query->orderBy('created_at', 'desc')->limit((int) $request->input('limit', 500))->get();
        }

        return response()->json($movements);
    }

    /**
     * Manual stock correction (e.g. damage, physical recount) — the one stock change a shop owner
     * makes directly rather than as a side effect of a sale or purchase.
     */
    /**
     * Batch-sync shim: the generic sync queue only knows create/update/delete against a resource
     * collection, so a queued offline adjustment arrives here as a "create" with `product_id` in
     * the body instead of the URL, and this just forwards it to adjust().
     */
    public function store(Request $request)
    {
        $validated = $request->validate(['product_id' => 'required|integer']);
        return $this->adjust($request, $validated['product_id']);
    }

    public function adjust(Request $request, $productId)
    {
        $shopId = $request->attributes->get('shop_id');
        $product = Product::where('shop_id', $shopId)->findOrFail($productId);

        $validator = Validator::make($request->all(), [
            'quantity_change' => 'required|integer|not_in:0',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $change = (int) $request->quantity_change;

        return DB::transaction(function () use ($product, $change, $shopId, $request) {
            $product->increment('stock', $change);
            $product->refresh();

            $this->logStockMovement(
                $shopId,
                $product->id,
                $change,
                $product->stock,
                'adjustment',
                null,
                null,
                $request->input('note')
            );

            return response()->json($product);
        });
    }
}

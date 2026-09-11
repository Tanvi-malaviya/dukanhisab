<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\SupplierProductPrice;
use Illuminate\Support\Facades\Validator;

class SupplierProductPriceApiController extends Controller
{
    /**
     * Get all products with their default purchase price and custom purchase price for this supplier.
     */
    public function index(Request $request, $supplierId)
    {
        $shopId = $request->attributes->get('shop_id');
        $supplier = Supplier::where('shop_id', $shopId)->find($supplierId);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found.'], 404);
        }

        $products = Product::where('shop_id', $shopId)->orderBy('name')->get();
        $customPrices = SupplierProductPrice::where('supplier_id', $supplierId)
            ->where('shop_id', $shopId)
            ->pluck('custom_price', 'product_id');

        $result = $products->map(function ($prod) use ($customPrices) {
            $hasCustom = isset($customPrices[$prod->id]);
            return [
                'product_id' => $prod->id,
                'name' => $prod->name,
                'barcode' => $prod->barcode,
                'default_price' => (float) $prod->purchase_price,
                'custom_price' => $hasCustom ? (float) $customPrices[$prod->id] : null,
                'has_custom' => $hasCustom,
                'stock' => $prod->stock,
            ];
        });

        return response()->json([
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'mobile' => $supplier->mobile,
            ],
            'products' => $result,
        ]);
    }

    /**
     * Save custom purchase prices for this supplier.
     */
    public function update(Request $request, $supplierId)
    {
        $shopId = $request->attributes->get('shop_id');
        $supplier = Supplier::where('shop_id', $shopId)->find($supplierId);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'prices' => 'required|array',
            'prices.*.product_id' => 'required|integer|exists:products,id',
            'prices.*.custom_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->input('prices', []) as $item) {
            $productId = $item['product_id'];
            $customPrice = $item['custom_price'] ?? null;

            if ($customPrice === null || $customPrice === '' || (float) $customPrice <= 0) {
                // Remove custom purchase price if cleared or 0
                SupplierProductPrice::where('supplier_id', $supplier->id)
                    ->where('product_id', $productId)
                    ->delete();
            } else {
                SupplierProductPrice::updateOrCreate(
                    [
                        'supplier_id' => $supplier->id,
                        'product_id' => $productId,
                    ],
                    [
                        'shop_id' => $shopId,
                        'custom_price' => round((float) $customPrice, 2),
                    ]
                );
            }
        }

        return response()->json(['message' => 'Supplier product prices updated successfully.']);
    }
}

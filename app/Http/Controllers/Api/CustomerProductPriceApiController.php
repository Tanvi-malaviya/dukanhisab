<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CustomerProductPrice;
use Illuminate\Support\Facades\Validator;

class CustomerProductPriceApiController extends Controller
{
    /**
     * Get all products with their default selling price and custom price for this customer.
     */
    public function index(Request $request, $customerId)
    {
        $shopId = $request->attributes->get('shop_id');
        $customer = Customer::where('shop_id', $shopId)->find($customerId);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }

        $products = Product::where('shop_id', $shopId)->orderBy('name')->get();
        $customPrices = CustomerProductPrice::where('customer_id', $customerId)
            ->where('shop_id', $shopId)
            ->pluck('custom_price', 'product_id');

        $result = $products->map(function ($prod) use ($customPrices) {
            $hasCustom = isset($customPrices[$prod->id]);
            return [
                'product_id' => $prod->id,
                'name' => $prod->name,
                'barcode' => $prod->barcode,
                'default_price' => (float) $prod->selling_price,
                'custom_price' => $hasCustom ? (float) $customPrices[$prod->id] : null,
                'has_custom' => $hasCustom,
                'stock' => $prod->stock,
            ];
        });

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'mobile' => $customer->mobile,
            ],
            'products' => $result,
        ]);
    }

    /**
     * Save custom prices for this customer.
     */
    public function update(Request $request, $customerId)
    {
        $shopId = $request->attributes->get('shop_id');
        $customer = Customer::where('shop_id', $shopId)->find($customerId);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found.'], 404);
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
                // Remove custom price if cleared or 0
                CustomerProductPrice::where('customer_id', $customer->id)
                    ->where('product_id', $productId)
                    ->delete();
            } else {
                CustomerProductPrice::updateOrCreate(
                    [
                        'customer_id' => $customer->id,
                        'product_id' => $productId,
                    ],
                    [
                        'shop_id' => $shopId,
                        'custom_price' => round((float) $customPrice, 2),
                    ]
                );
            }
        }

        return response()->json(['message' => 'Customer product prices updated successfully.']);
    }
}

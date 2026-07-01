<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $query = Product::where('shop_id', $shopId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = $request->input('per_page', 10);
            $products = $query->paginate($perPage);
        } else {
            $products = $query->get();
        }
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:100',
            'stock' => 'nullable|integer',
            'low_stock_threshold' => 'nullable|integer',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['shop_id'] = $shopId;

        // Auto-generate barcode if missing and not walk-in
        if (empty($data['barcode'])) {
            $data['barcode'] = 'BC-' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        }

        $product = Product::create($data);
        return response()->json($product, 201);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $product = Product::where('shop_id', $shopId)->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $product = Product::where('shop_id', $shopId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:100',
            'stock' => 'nullable|integer',
            'low_stock_threshold' => 'nullable|integer',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update($request->all());
        return response()->json($product);
    }

    public function destroy(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $product = Product::where('shop_id', $shopId)->findOrFail($id);
        $product->delete();
        return response()->json(null, 204);
    }
}

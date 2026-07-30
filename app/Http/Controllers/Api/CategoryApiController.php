<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CategoryApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $query = Category::where('shop_id', $shopId);

        if ($request->filled('updated_since')) {
            $validator = Validator::make($request->only('updated_since'), [
                'updated_since' => 'date',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $query->withTrashed()->where('updated_at', '>=', Carbon::parse($request->updated_since));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = $request->input('per_page', 10);
            $categories = $query->paginate($perPage);
        } else {
            $categories = $query->get();
        }
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['shop_id'] = $shopId;

        $category = Category::create($data);
        return response()->json($category, 201);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $category = Category::where('shop_id', $shopId)->findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $category = Category::where('shop_id', $shopId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $category = Category::where('shop_id', $shopId)->findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}

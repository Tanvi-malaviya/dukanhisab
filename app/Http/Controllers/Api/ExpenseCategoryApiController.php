<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ExpenseCategoryApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $query = ExpenseCategory::where('shop_id', $shopId)->withCount('cashBooks');

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
            $perPage = $request->input('per_page', 20);
            $categories = $query->orderBy('name', 'asc')->paginate($perPage);
        } else {
            $categories = $query->orderBy('name', 'asc')->get();
        }
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories')->where(function ($q) use ($shopId) {
                    return $q->where('shop_id', $shopId)->whereNull('deleted_at');
                }),
            ],
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = ExpenseCategory::create([
            'shop_id' => $shopId,
            'name' => trim($request->name),
            'description' => $request->description,
        ]);

        return response()->json($category, 201);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $category = ExpenseCategory::where('shop_id', $shopId)->withCount('cashBooks')->findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $category = ExpenseCategory::where('shop_id', $shopId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories')->where(function ($q) use ($shopId) {
                    return $q->where('shop_id', $shopId)->whereNull('deleted_at');
                })->ignore($category->id),
            ],
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category->update([
            'name' => $request->filled('name') ? trim($request->name) : $category->name,
            'description' => $request->has('description') ? $request->description : $category->description,
        ]);

        return response()->json($category);
    }

    public function destroy(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $category = ExpenseCategory::where('shop_id', $shopId)->findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}

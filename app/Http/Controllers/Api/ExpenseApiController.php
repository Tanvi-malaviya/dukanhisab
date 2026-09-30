<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashBook;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ExpenseApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $query = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where(function ($q) {
                $q->whereNull('reference_type')
                  ->orWhere('reference_type', 'expense');
            })
            ->where('description', 'not like', 'Purchase:%')
            ->where('description', 'not like', 'Return:%')
            ->where('description', 'not like', 'Reversal:%')
            ->with('expenseCategory');

        if ($request->filled('expense_category_id')) {
            $query->where('expense_category_id', $request->expense_category_id);
        }

        if ($request->filled('month')) {
            try {
                $month = Carbon::parse($request->month);
                $query->whereYear('transaction_date', $month->year)
                      ->whereMonth('transaction_date', $month->month);
            } catch (\Exception $e) {
                // Ignore invalid date format
            }
        }

        if ($request->filled('updated_since')) {
            // Include soft-deleted rows so the mobile app can remove expenses deleted elsewhere.
            $query->withTrashed()->where('updated_at', '>=', Carbon::parse($request->updated_since));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('expenseCategory', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = $request->input('per_page', 10);
            $expenses = $query->orderBy('transaction_date', 'desc')->paginate($perPage);
        } else {
            $expenses = $query->orderBy('transaction_date', 'desc')->get();
        }
        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,bank,upi',
            'description' => 'required|string|max:255',
            'expense_category_id' => 'nullable|integer|exists:expense_categories,id',
            'transaction_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $description = trim($request->description);
        $categoryId = $request->expense_category_id;

        // Auto-extract [Category] from description if present
        if (preg_match('/^\[(.*?)\]\s*(.*)$/', $description, $matches)) {
            $catName = trim($matches[1]);
            $remainingDesc = trim($matches[2]);
            if ($catName) {
                $cat = ExpenseCategory::firstOrCreate(
                    ['shop_id' => $shopId, 'name' => $catName],
                    ['description' => null]
                );
                $categoryId = $cat->id;
                if ($remainingDesc) {
                    $description = $remainingDesc;
                }
            }
        }

        $expense = CashBook::create([
            'shop_id' => $shopId,
            'type' => 'cash_out',
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'description' => $description,
            'expense_category_id' => $categoryId,
            'reference_type' => 'expense',
            'transaction_date' => $request->filled('transaction_date') ? Carbon::parse($request->transaction_date) : Carbon::now(),
        ]);

        return response()->json($expense->load('expenseCategory'), 201);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $expense = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where(function ($q) {
                $q->whereNull('reference_type')
                  ->orWhere('reference_type', 'expense');
            })
            ->with('expenseCategory')
            ->findOrFail($id);
        return response()->json($expense);
    }

    public function update(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $expense = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where(function ($q) {
                $q->whereNull('reference_type')
                  ->orWhere('reference_type', 'expense');
            })
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric|min:0.01',
            'payment_method' => 'sometimes|required|string|in:cash,bank,upi',
            'description' => 'sometimes|required|string|max:255',
            'expense_category_id' => 'nullable|integer|exists:expense_categories,id',
            'transaction_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only('amount', 'payment_method', 'expense_category_id');

        if ($request->has('description')) {
            $description = trim($request->description);
            if (preg_match('/^\[(.*?)\]\s*(.*)$/', $description, $matches)) {
                $catName = trim($matches[1]);
                $remainingDesc = trim($matches[2]);
                if ($catName) {
                    $cat = ExpenseCategory::firstOrCreate(
                        ['shop_id' => $shopId, 'name' => $catName],
                        ['description' => null]
                    );
                    $data['expense_category_id'] = $cat->id;
                    if ($remainingDesc) {
                        $description = $remainingDesc;
                    }
                }
            }
            $data['description'] = $description;
        }

        if ($request->filled('transaction_date')) {
            $data['transaction_date'] = Carbon::parse($request->transaction_date);
        }

        $expense->update($data);
        return response()->json($expense->load('expenseCategory'));
    }

    public function destroy(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $expense = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where(function ($q) {
                $q->whereNull('reference_type')
                  ->orWhere('reference_type', 'expense');
            })
            ->findOrFail($id);

        if ($expense->reference_type !== null && $expense->reference_type !== 'expense') {
            return response()->json([
                'message' => 'System generated transactions cannot be deleted from expenses.'
            ], 400);
        }

        $expense->delete();
        return response()->json(null, 204);
    }
}

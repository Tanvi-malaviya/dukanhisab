<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use Illuminate\Http\Request;

class CreditNoteApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $query = CreditNote::where('shop_id', $shopId)->with(['customer', 'sale']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('credit_note_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        $creditNotes = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 20));

        return response()->json($creditNotes);
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $creditNote = CreditNote::where('shop_id', $shopId)
            ->with(['customer', 'sale.items.product'])
            ->findOrFail($id);

        return response()->json($creditNote);
    }
}

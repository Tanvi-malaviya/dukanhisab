<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContainerEntry;
use App\Models\ContainerMovement;
use App\Models\ContainerType;
use App\Models\Customer;
use App\Support\ContainerLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Returnable containers module (enabled per shop by the admin): who holds which containers,
 * the deposit held against them, and give / return / exchange / opening / reversal entries.
 */
class ContainerApiController extends Controller
{
    /** Overview cards: per-type counts plus deposit liability and forfeit income. */
    public function summary(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        $monthStart = Carbon::now()->startOfMonth();

        $types = ContainerTypeApiController::withStats($shopId, ContainerType::where('shop_id', $shopId)->orderBy('name')->get());

        $effective = fn () => ContainerEntry::where('shop_id', $shopId)->effective();

        $returnedThisMonth = DB::table('container_movements as m')
            ->join('container_entries as e', 'e.id', '=', 'm.container_entry_id')
            ->where('m.shop_id', $shopId)
            ->whereIn('m.kind', ['return', 'damaged'])
            ->whereNull('e.reversed_at')
            ->where('e.entry_date', '>=', $monthStart)
            ->sum('m.quantity');

        return response()->json([
            'types' => $types,
            'containers_out' => $types->sum('with_customers'),
            'deposit_held' => round($types->sum('deposit_held'), 2),
            'customers_holding' => ContainerMovement::where('shop_id', $shopId)->openLots()->distinct()->count('customer_id'),
            'returned_this_month' => (int) $returnedThisMonth,
            'deposit_received_this_month' => round((float) $effective()->where('type', '!=', 'opening')->where('entry_date', '>=', $monthStart)->sum('deposit_amount'), 2),
            'deposit_refunded_this_month' => round((float) $effective()->where('entry_date', '>=', $monthStart)->sum('refund_amount'), 2),
            'forfeit_income_this_month' => round((float) $effective()->where('entry_date', '>=', $monthStart)->sum('forfeit_amount'), 2),
            'forfeit_income_total' => round((float) $effective()->sum('forfeit_amount'), 2),
        ]);
    }

    /** Customers currently holding containers, with per-type counts and deposit held (the deposit liability list). */
    public function customers(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $rows = DB::table('container_movements as m')
            ->join('container_entries as e', 'e.id', '=', 'm.container_entry_id')
            ->where('m.shop_id', $shopId)
            ->where('m.kind', 'issue')
            ->whereNull('e.reversed_at')
            ->whereColumn('m.closed_quantity', '<', 'm.quantity')
            ->groupBy('m.customer_id', 'm.container_type_id')
            ->selectRaw('m.customer_id, m.container_type_id, SUM(m.quantity - m.closed_quantity) as pending, SUM((m.quantity - m.closed_quantity) * m.deposit_per_unit) as deposit, MIN(e.entry_date) as since')
            ->get();

        $customers = Customer::withTrashed()->where('shop_id', $shopId)->whereIn('id', $rows->pluck('customer_id')->unique())->get()->keyBy('id');
        $typeNames = ContainerType::withTrashed()->where('shop_id', $shopId)->pluck('name', 'id');

        $list = $rows->groupBy('customer_id')->map(function ($group, $customerId) use ($customers, $typeNames) {
            $customer = $customers[$customerId] ?? null;
            return [
                'customer_id' => (int) $customerId,
                'name' => $customer->name ?? 'Unknown',
                'mobile' => $customer->mobile ?? null,
                'due_amount' => (float) ($customer->due_amount ?? 0),
                'holdings' => $group->map(fn ($r) => [
                    'container_type_id' => (int) $r->container_type_id,
                    'name' => $typeNames[$r->container_type_id] ?? 'Container',
                    'pending' => (int) $r->pending,
                    'deposit' => round((float) $r->deposit, 2),
                ])->values(),
                'total_pending' => (int) $group->sum('pending'),
                'deposit_held' => round((float) $group->sum('deposit'), 2),
                'since' => $group->min('since'),
            ];
        });

        if ($request->filled('search')) {
            $search = mb_strtolower($request->search);
            $list = $list->filter(fn ($c) => str_contains(mb_strtolower($c['name']), $search) || str_contains((string) $c['mobile'], $search));
        }

        return response()->json($list->sortBy('since')->values());
    }

    /** One customer's containers: holdings, the open lots per invoice (for returns) and recent entries. */
    public function customer(Request $request, $customerId)
    {
        $shopId = $request->attributes->get('shop_id');
        $customer = Customer::where('shop_id', $shopId)->findOrFail($customerId);

        $lots = ContainerMovement::where('shop_id', $shopId)
            ->where('customer_id', $customer->id)
            ->openLots()
            ->with(['containerType:id,name', 'sale:id,sale_number', 'entry:id,entry_number,entry_date,type'])
            ->orderBy(ContainerEntry::select('entry_date')->whereColumn('container_entries.id', 'container_movements.container_entry_id'))
            ->orderBy('id')
            ->get();

        $holdings = $lots->groupBy('container_type_id')->map(fn ($group) => [
            'container_type_id' => $group->first()->container_type_id,
            'name' => $group->first()->containerType->name ?? 'Container',
            'pending' => $group->sum('pending_quantity'),
            'deposit' => round($group->sum(fn ($l) => $l->pending_quantity * $l->deposit_per_unit), 2),
        ])->values();

        $entries = ContainerEntry::where('shop_id', $shopId)
            ->where('customer_id', $customer->id)
            ->with(['movements.containerType:id,name', 'sale:id,sale_number'])
            ->orderByDesc('entry_date')->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json([
            'customer' => $customer,
            'holdings' => $holdings,
            'deposit_held' => round($holdings->sum('deposit'), 2),
            'lots' => $lots,
            'entries' => $entries,
        ]);
    }

    /** Entry history with filters. */
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $query = ContainerEntry::where('shop_id', $shopId)
            ->with(['movements.containerType:id,name', 'customer:id,name,mobile', 'sale:id,sale_number']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('container_type_id')) {
            $query->whereHas('movements', fn ($q) => $q->where('container_type_id', $request->container_type_id));
        }
        if ($request->filled('sale_id')) {
            $query->where(fn ($q) => $q->where('sale_id', $request->sale_id)
                ->orWhereHas('movements', fn ($m) => $m->where('sale_id', $request->sale_id)));
        }
        if ($request->filled('start_date')) {
            $query->whereDate('entry_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('entry_date', '<=', $request->end_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('entry_number', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")));
        }

        $query->orderByDesc('entry_date')->orderByDesc('id');

        if ($request->has('page') || $request->boolean('paginate')) {
            return response()->json($query->paginate((int) $request->input('per_page', 15)));
        }
        return response()->json($query->limit(500)->get());
    }

    public function show(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $entry = ContainerEntry::where('shop_id', $shopId)
            ->with(['movements.containerType:id,name', 'movements.sale:id,sale_number', 'customer', 'sale:id,sale_number', 'reversalOf:id,entry_number'])
            ->findOrFail($id);
        return response()->json($entry);
    }

    /** Give / return / exchange / opening balance for one customer. */
    public function store(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $typeRule = Rule::exists('container_types', 'id')->where('shop_id', $shopId)->whereNull('deleted_at');
        $validator = Validator::make($request->all(), [
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('shop_id', $shopId)->whereNull('deleted_at')],
            'opening' => 'nullable|boolean',
            'issues' => 'nullable|array',
            'issues.*.container_type_id' => ['required', $typeRule],
            'issues.*.quantity' => 'required|integer|min:0|max:100000',
            'issues.*.deposit_per_unit' => 'nullable|numeric|min:0',
            'returns' => 'nullable|array',
            'returns.*.container_type_id' => ['required', Rule::exists('container_types', 'id')->where('shop_id', $shopId)],
            'returns.*.returned' => 'nullable|integer|min:0|max:100000',
            'returns.*.damaged' => 'nullable|integer|min:0|max:100000',
            'returns.*.damage_deduction' => 'nullable|numeric|min:0',
            'returns.*.lost' => 'nullable|integer|min:0|max:100000',
            'returns.*.sale_id' => ['nullable', Rule::exists('sales', 'id')->where('shop_id', $shopId)],
            'collect_deposit' => 'nullable|boolean',
            'settlement_method' => 'nullable|string|in:cash,upi,bank,due_adjustment',
            'note' => 'nullable|string|max:255',
            'entry_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $entry = ContainerLedger::post($shopId, [
            'customer_id' => (int) $request->customer_id,
            'opening' => $request->boolean('opening'),
            'issues' => $request->input('issues', []),
            'returns' => $request->input('returns', []),
            'collect_deposit' => $request->input('collect_deposit', true),
            'settlement_method' => $request->settlement_method,
            'note' => $request->note,
            'entry_date' => $request->entry_date,
        ]);

        return response()->json($entry->load('sale:id,sale_number', 'movements.sale:id,sale_number'), 201);
    }

    public function reverse(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $entry = ContainerEntry::where('shop_id', $shopId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'note' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reversal = ContainerLedger::reverse($shopId, $entry, $request->note);

        return response()->json($reversal, 201);
    }
}

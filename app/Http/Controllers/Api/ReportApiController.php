<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CashBook;
use Carbon\Carbon;

class ReportApiController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date)->startOfDay() 
            : Carbon::now()->startOfMonth();
            
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date)->endOfDay() 
            : Carbon::now()->endOfDay();

        // 1. Sales Report
        $salesQuery = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'Completed');

        $totalSales = $salesQuery->sum('grand_total');
        $salesCount = $salesQuery->count();

        // Sales by payment type
        $salesByPaymentType = $salesQuery->selectRaw('payment_type, SUM(grand_total) as total')
            ->groupBy('payment_type')
            ->get();

        // 2. Purchases Report
        $purchasesQuery = Purchase::where('shop_id', $shopId)
            ->whereBetween('purchase_date', [$startDate, $endDate]);

        $totalPurchases = $purchasesQuery->sum('total_amount');
        $purchasesCount = $purchasesQuery->count();

        // 3. Expenses Report (manual cash_out entries + system expenses)
        $expensesQuery = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        $totalExpenses = $expensesQuery->sum('amount');
        $expensesCount = $expensesQuery->count();

        // Net Profit estimate = Sales - Purchases - Expenses
        $netProfit = $totalSales - $totalPurchases - $totalExpenses;

        return response()->json([
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_sales' => round($totalSales, 2),
            'sales_count' => $salesCount,
            'sales_by_payment_type' => $salesByPaymentType,
            'total_purchases' => round($totalPurchases, 2),
            'purchases_count' => $purchasesCount,
            'total_expenses' => round($totalExpenses, 2),
            'expenses_count' => $expensesCount,
            'net_profit' => round($netProfit, 2),
        ]);
    }
}

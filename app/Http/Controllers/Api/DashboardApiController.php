<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\CashBook;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    /**
     * Return basic dashboard summary for the scoped shop.
     */
    public function index(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');
        
        $today = Carbon::today();

        // 1. Today's Sales (Completed sales only)
        $todaySales = Sale::where('shop_id', $shopId)
            ->whereDate('sale_date', $today)
            ->where('status', 'Completed')
            ->sum('grand_total');

        // 2. Today's Purchases
        $todayPurchases = Purchase::where('shop_id', $shopId)
            ->whereDate('purchase_date', $today)
            ->sum('total_amount');

        // 3. Cash Balance (net from CashBook)
        $cashIn = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_in')
            ->where('payment_method', 'cash')
            ->sum('amount');
        $cashOut = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->where('payment_method', 'cash')
            ->sum('amount');
        $cashBalance = $cashIn - $cashOut;

        // 4. Bank Balance (net bank & upi from CashBook)
        $bankIn = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_in')
            ->whereIn('payment_method', ['bank', 'upi'])
            ->sum('amount');
        $bankOut = CashBook::where('shop_id', $shopId)
            ->where('type', 'cash_out')
            ->whereIn('payment_method', ['bank', 'upi'])
            ->sum('amount');
        $bankBalance = $bankIn - $bankOut;

        // 5. Customer Due (total outstanding dues)
        $customerDue = Customer::where('shop_id', $shopId)->sum('due_amount');

        // 6. Supplier Due (total outstanding dues)
        $supplierDue = Supplier::where('shop_id', $shopId)->sum('due_amount');

        // 7. Low Stock Products
        $lowStockQuery = Product::where('shop_id', $shopId)
            ->whereRaw('stock <= low_stock_threshold');
            
        $lowStockCount = $lowStockQuery->count();
        $lowStockProducts = $lowStockQuery->take(5)->get();

        // 8. Recent Sales
        $recentSales = Sale::where('shop_id', $shopId)
            ->with('customer')
            ->orderBy('sale_date', 'desc')
            ->take(5)
            ->get();

        // 9. Recent Purchases
        $recentPurchases = Purchase::where('shop_id', $shopId)
            ->with('supplier')
            ->orderBy('purchase_date', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'shop_id' => $shopId,
            'today_sales' => round($todaySales, 2),
            'today_purchases' => round($todayPurchases, 2),
            'cash_balance' => round($cashBalance, 2),
            'bank_balance' => round($bankBalance, 2),
            'customer_due' => round($customerDue, 2),
            'supplier_due' => round($supplierDue, 2),
            'low_stock_count' => $lowStockCount,
            'low_stock_products' => $lowStockProducts,
            'recent_sales' => $recentSales,
            'recent_purchases' => $recentPurchases,
        ]);
    }
}

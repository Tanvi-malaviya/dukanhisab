<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    /**
     * Display the Product Analytics & User Insights Dashboard.
     */
    public function index(Request $request)
    {
        $range = $request->input('range', '30d');
        $from = $request->input('from');
        $to = $request->input('to');
        $tab = $request->input('tab', 'overview');

        $metrics = AnalyticsService::getDashboardMetrics($range, $from, $to);

        return view('admin.analytics.index', compact('metrics', 'range', 'from', 'to', 'tab'));
    }

    /**
     * Export analytics users explorer data to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $range = $request->input('range', '30d');
        $from = $request->input('from');
        $to = $request->input('to');

        $metrics = AnalyticsService::getDashboardMetrics($range, $from, $to);
        $users = $metrics['usersExplorer'];

        $filename = 'dukanhisab_user_analytics_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'User ID',
                'Name',
                'Phone',
                'Email',
                'Shops Count',
                'Primary Shop',
                'Plan',
                'Sales Count',
                'Purchases Count',
                'Products Count',
                'Total Activities',
                'Status',
                'Registered At',
                'Last Active',
            ]);

            foreach ($users as $u) {
                fputcsv($handle, [
                    $u['id'],
                    $u['name'],
                    $u['phone'],
                    $u['email'],
                    $u['shops_count'],
                    $u['primary_shop'],
                    $u['plan'],
                    $u['sales_count'],
                    $u['purchases_count'],
                    $u['products_count'],
                    $u['total_activities'],
                    $u['status'],
                    $u['registered_at'],
                    $u['last_active_at'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}

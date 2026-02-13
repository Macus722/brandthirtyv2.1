<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Session;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class SalesReportController extends Controller
{
    private function checkLogin()
    {
        if (!auth()->check()) {
            redirect('/admin/login')->send();
            exit;
        }
    }

    public function index(Request $request)
    {
        $this->checkLogin();

        $query = Order::query();

        // 1. Date Range Filter
        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        // 2. Service Type / Plan Filter
        if ($request->filled('plan') && $request->plan != 'All') {
            $query->where('plan', $request->plan);
        }

        // Get filtered results
        $orders = $query->orderBy('created_at', 'desc')->get();

        // Constants for Revenue Calculation
        $paidStatuses = ['Processing', 'In Progress', 'Paid', 'Completed', 'Approved'];

        // 3. Financial Metrics
        $revenueOrders = $orders->whereIn('status', $paidStatuses);

        $totalRevenue = $revenueOrders->sum('total_amount');
        $potentialRevenue = $orders->sum('total_amount');

        // Average Order Value (AOV)
        $paidCount = $revenueOrders->count();
        $aov = $paidCount > 0 ? $totalRevenue / $paidCount : 0;

        // 4. Charts Data

        // A. Revenue Trend (Daily)
        $dailyTrend = $revenueOrders->groupBy(function ($date) {
            return $date->created_at->format('Y-m-d');
        })->map(function ($row) {
            return $row->sum('total_amount');
        })->sortKeys();

        $trendLabels = $dailyTrend->keys()->toArray();
        $trendValues = $dailyTrend->values()->toArray();

        // B. Service Distribution (By Revenue)
        $planDistribution = $revenueOrders->groupBy('plan')->map(function ($row) {
            return $row->sum('total_amount');
        });

        $planLabels = $planDistribution->keys()->toArray();
        $planValues = $planDistribution->values()->toArray();

        // C. Top 5 Customers (By Revenue)
        $topCustomers = $revenueOrders->groupBy('customer_email')
            ->map(function ($group) {
                return [
                    'name' => $group->first()->customer_name,
                    'email' => $group->first()->customer_email,
                    'total' => $group->sum('total_amount'),
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('total')
            ->take(5);

        // D. Staff Sales Leaderboard
        $staffPerformance = \App\Models\User::where('role', 'staff')
            ->withSum([
                'orders' => function ($q) use ($paidStatuses) {
                    $q->whereIn('status', $paidStatuses);
                }
            ], 'total_amount')
            ->withCount([
                'orders' => function ($q) use ($paidStatuses) {
                    $q->whereIn('status', $paidStatuses);
                }
            ])
            ->get()
            ->sortByDesc('orders_sum_total_amount');

        // 5. Period Comparison (Month over Month)
        // Previous Month Revenue (Same paid statuses)
        $prevMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $prevMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $prevMonthRevenue = Order::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
            ->sum('total_amount');

        // Current Month Revenue (Global)
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthRevenue = Order::whereIn('status', $paidStatuses)
            ->where('created_at', '>=', $currentMonthStart)
            ->sum('total_amount');

        $growthPercentage = 0;
        if ($prevMonthRevenue > 0) {
            $growthPercentage = (($currentMonthRevenue - $prevMonthRevenue) / $prevMonthRevenue) * 100;
        } else {
            $growthPercentage = $currentMonthRevenue > 0 ? 100 : 0;
        }

        // Unique Plans for Filter Dropdown
        $plans = Order::select('plan')->distinct()->pluck('plan');

        return view('admin.reports.sales', compact(
            'orders',
            'totalRevenue',
            'potentialRevenue',
            'aov',
            'plans',
            'trendLabels',
            'trendValues',
            'planLabels',
            'planValues',
            'topCustomers',
            'staffPerformance',
            'growthPercentage',
            'currentMonthRevenue',
            'prevMonthRevenue'
        ));
    }

    public function downloadPdf(Request $request)
    {
        $this->checkLogin();

        $query = Order::query();
        if ($request->filled('date_start'))
            $query->whereDate('created_at', '>=', $request->date_start);
        if ($request->filled('date_end'))
            $query->whereDate('created_at', '<=', $request->date_end);
        if ($request->filled('plan') && $request->plan != 'All')
            $query->where('plan', $request->plan);
        $orders = $query->orderBy('created_at', 'desc')->get();

        $paidStatuses = ['Processing', 'In Progress', 'Paid', 'Completed', 'Approved'];
        $revenueOrders = $orders->whereIn('status', $paidStatuses);
        $totalRevenue = $revenueOrders->sum('total_amount');
        $potentialRevenue = $orders->sum('total_amount');
        $paidCount = $revenueOrders->count();
        $aov = $paidCount > 0 ? $totalRevenue / $paidCount : 0;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.sales_pdf', compact('orders', 'totalRevenue', 'potentialRevenue', 'aov'));
        return $pdf->download('Sales_Report_' . date('Y-m-d') . '.pdf');
    }

    public function export(Request $request)
    {
        $this->checkLogin();
        // Pass request parameters to the Export class
        return Excel::download(new OrdersExport($request->all()), 'sales_report_' . date('Y-m-d') . '.xlsx');
    }
}

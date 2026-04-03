<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OfflinePayment;
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

    private function getCombinedOrders(Request $request)
    {
        $ordersQuery = Order::with('staff');
        $offlineQuery = OfflinePayment::with(['client', 'markedByUser']);

        // 1. Date Range Filter
        if ($request->filled('date_start')) {
            $ordersQuery->whereDate('created_at', '>=', $request->date_start);
            $offlineQuery->whereDate('paid_at', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $ordersQuery->whereDate('created_at', '<=', $request->date_end);
            $offlineQuery->whereDate('paid_at', '<=', $request->date_end);
        }

        // 2. Service Type / Plan Filter
        $planFilter = $request->filled('plan') && $request->plan != 'All' ? $request->plan : null;

        $orders = collect();
        if (!$planFilter || $planFilter !== 'Offline') {
            if ($planFilter && $planFilter !== 'Offline') {
                $ordersQuery->where('plan', $planFilter);
            }
            $orders = $ordersQuery->get()->map(function ($o) {
                return (object) [
                    'type' => 'online',
                    'order_id' => $o->order_id,
                    'created_at' => $o->created_at,
                    'customer_name' => $o->customer_name,
                    'company_name' => $o->company_name,
                    'customer_email' => $o->customer_email,
                    'phone' => $o->phone ?? '',
                    'website_url' => $o->website_url ?? '',
                    'plan' => $o->plan,
                    'strategy' => $o->strategy ?? '',
                    'total_amount' => $o->total_amount,
                    'status' => $o->status,
                    'is_revenue' => in_array($o->status, ['Processing', 'In Progress', 'Paid', 'Completed', 'Approved']),
                    'staff_id' => $o->staff_id,
                    'staff_name' => $o->staff ? $o->staff->name : null,
                ];
            });
        }

        $offlines = collect();
        if (!$planFilter || $planFilter === 'Offline') {
            $offlines = $offlineQuery->get()->map(function ($o) {
                return (object) [
                    'type' => 'offline',
                    'order_id' => 'OFF-' . str_pad($o->id, 5, '0', STR_PAD_LEFT),
                    'created_at' => Carbon::parse($o->paid_at ?? $o->created_at),
                    'customer_name' => $o->client ? $o->client->pic_name : 'Unknown',
                    'company_name' => $o->client ? $o->client->company_name : 'Unknown',
                    'customer_email' => $o->client ? $o->client->pic_email : null,
                    'phone' => $o->client ? $o->client->pic_phone : '',
                    'website_url' => '',
                    'plan' => 'Offline',
                    'strategy' => '',
                    'total_amount' => $o->amount,
                    'status' => 'Paid',
                    'is_revenue' => true,
                    'staff_id' => $o->marked_by,
                    'staff_name' => $o->markedByUser ? $o->markedByUser->name : null,
                ];
            });
        }

        return $orders->concat($offlines)->sortByDesc('created_at')->values();
    }

    public function index(Request $request)
    {
        $this->checkLogin();

        $allOrders = $this->getCombinedOrders($request);

        // 3. Financial Metrics
        $revenueOrders = $allOrders->where('is_revenue', true);

        $totalRevenue = $revenueOrders->sum('total_amount');
        $potentialRevenue = $allOrders->sum('total_amount');

        // Average Order Value (AOV)
        $paidCount = $revenueOrders->count();
        $aov = $paidCount > 0 ? $totalRevenue / $paidCount : 0;

        // 4. Charts Data

        // A. Revenue Trend (Daily)
        $dailyTrend = $revenueOrders->groupBy(function ($o) {
            return $o->created_at->format('Y-m-d');
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
        $topCustomers = $revenueOrders->whereNotNull('customer_email')->groupBy('customer_email')
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
        $staffPerformance = $revenueOrders->whereNotNull('staff_id')->groupBy('staff_id')
            ->map(function ($group) {
                return (object) [
                    'name' => $group->first()->staff_name,
                    'orders_sum_total_amount' => $group->sum('total_amount'),
                    'orders_count' => $group->count()
                ];
            })
            ->filter(function ($item) {
                return !empty($item->name);
            })
            ->sortByDesc('orders_sum_total_amount')
            ->values();

        // 5. Period Comparison (Month over Month)
        // Previous Month Revenue (Same paid statuses)
        $prevMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $prevMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $paidStatuses = ['Processing', 'In Progress', 'Paid', 'Completed', 'Approved'];

        $prevMonthRevenueOrders = Order::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
            ->sum('total_amount');
        $prevMonthRevenueOffline = OfflinePayment::whereBetween('paid_at', [$prevMonthStart, $prevMonthEnd])
            ->sum('amount');
        $prevMonthRevenue = $prevMonthRevenueOrders + $prevMonthRevenueOffline;

        // Current Month Revenue (Global)
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthRevenueOrders = Order::whereIn('status', $paidStatuses)
            ->where('created_at', '>=', $currentMonthStart)
            ->sum('total_amount');
        $currentMonthRevenueOffline = OfflinePayment::where('paid_at', '>=', $currentMonthStart)
            ->sum('amount');
        $currentMonthRevenue = $currentMonthRevenueOrders + $currentMonthRevenueOffline;

        $growthPercentage = 0;
        if ($prevMonthRevenue > 0) {
            $growthPercentage = (($currentMonthRevenue - $prevMonthRevenue) / $prevMonthRevenue) * 100;
        } else {
            $growthPercentage = $currentMonthRevenue > 0 ? 100 : 0;
        }

        // Unique Plans for Filter Dropdown
        $plans = Order::select('plan')->distinct()->pluck('plan')->toArray();
        if (!in_array('Offline', $plans)) {
            $plans[] = 'Offline';
        }

        $orders = $allOrders; // For the view

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

        $allOrders = $this->getCombinedOrders($request);

        $revenueOrders = $allOrders->where('is_revenue', true);
        $totalRevenue = $revenueOrders->sum('total_amount');
        $potentialRevenue = $allOrders->sum('total_amount');
        $paidCount = $revenueOrders->count();
        $aov = $paidCount > 0 ? $totalRevenue / $paidCount : 0;

        $orders = $allOrders;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.sales_pdf', compact('orders', 'totalRevenue', 'potentialRevenue', 'aov'));
        return $pdf->download('Sales_Report_' . date('Y-m-d') . '.pdf');
    }

    public function export(Request $request)
    {
        $this->checkLogin();
        return Excel::download(new OrdersExport($request->all(), $this->getCombinedOrders($request)), 'sales_report_' . date('Y-m-d') . '.xlsx');
    }
}

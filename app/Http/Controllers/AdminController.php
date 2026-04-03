<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\Setting;
use App\Exports\OrdersExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    private $validUsername = 'admin';
    private $validPassword = '123';

    // Helper to log actions
    private function logAction($orderId, $action, $details = null)
    {
        \App\Models\OrderLog::create([
            'order_id' => $orderId,
            'user' => auth()->user() ? auth()->user()->name : 'System',
            'action' => $action,
            'details' => $details
        ]);
    }

    public function index(Request $request)
    {
        // 1. Auth handled by Middleware

        $user = auth()->user();
        $isStaff = $user->role === 'staff';

        // 2. Metrics & BI Data
        $today = Carbon::today();

        // Base Query: Staff see only their assigned orders; Admin sees all
        $baseQuery = Order::query();
        if ($isStaff) {
            $baseQuery->where('staff_id', $user->id); // "MY" = assigned to this staff only; Unassigned orders are not counted
        }

        // Single source of truth for tab counts (shared with Order Management)
        $tabCounts = Order::getTabCountsForUser($user);
        $pendingCount = $tabCounts['pending'];
        $inProgressCount = $tabCounts['processing'];
        $pendingApprovalCount = $tabCounts['pending_approval'];
        $completedCount = $tabCounts['completed'];
        $rejectedCount = $tabCounts['cancelled'];

        // Financial Stats - HIDDEN for Staff (Completed orders only; matches "Completed" tab)
        $totalRevenue = 0;
        $todaySales = 0;
        $potentialSales = 0;
        $salesData = collect([]);
        $chartLabels = [];
        $chartValues = [];

        if (!$isStaff) {
            $revenueStats = Order::getRevenueStats();
            $totalRevenue = $revenueStats['total_revenue'];
            $todaySales = $revenueStats['today_sales'];
            $potentialSales = Order::whereNotIn('status', ['Rejected', 'Cancelled'])->sum('total_amount');

            // Charts Data (Revenue Trend) — completed orders only
            $salesData = Order::select(DB::raw('DATE(COALESCE(completed_at, created_at)) as date'), DB::raw('SUM(total_amount) as total'))
                ->whereIn('status', Order::STATUSES_COMPLETED)
                ->where(function ($q) {
                    $q->where('completed_at', '>=', Carbon::now()->subDays(30))
                        ->orWhere(function ($q2) {
                            $q2->whereNull('completed_at')->where('created_at', '>=', Carbon::now()->subDays(30));
                        });
                })
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get();
            $chartLabels = $salesData->pluck('date');
            $chartValues = $salesData->pluck('total');
        }

        // Conversion Funnel Data
        $totalOrders = $tabCounts['all'];
        $approvedOrders = (clone $baseQuery)->whereIn('status', ['Processing', 'In Progress', 'Paid', 'Assigned', 'Review'])->count();
        $completedOrdersCount = $tabCounts['completed'];

        // Plan Data
        $planDataQuery = (clone $baseQuery)->select('plan', DB::raw('count(*) as total'))->groupBy('plan');
        $planData = $planDataQuery->get();
        $planLabels = $planData->pluck('plan');
        $planValues = $planData->pluck('total');

        // Staff Workload (Admin sees all, Staff sees self?) 
        // Admin View: See all staff workload. Staff View: Irrelevant or show self.
        $staffWorkload = collect([]);
        if (!$isStaff) {
            $staffWorkload = \App\Models\User::where('role', 'staff')
                ->withCount([
                    'orders' => function ($query) {
                        $query->whereIn('status', ['Processing', 'In Progress', 'Assigned', 'Review']);
                    }
                ])
                ->get();
        }

        // Recent Activity
        $recentOrders = (clone $baseQuery)->orderBy('created_at', 'desc')->take(5)->get();
        // Logs - Staff should only see logs for their orders? Or simplified logs? 
        // For now, let's show logs related to their assigned orders OR global logs?
        // Let's restrict logs for staff to their orders for privacy/scope.
        if ($isStaff) {
            $recentLogs = \App\Models\OrderLog::whereIn('order_id', (clone $baseQuery)->select('id'))->orderBy('created_at', 'desc')->take(10)->get();
        } else {
            $recentLogs = \App\Models\OrderLog::orderBy('created_at', 'desc')->take(10)->get();
        }

        // 3. Search & Advanced Filter Logic for Dashboard Table
        $query = (clone $baseQuery); // Inherit staff filter

        $defaultTab = $isStaff ? 'Processing' : 'Pending';
        $statusTab = $request->input('status', $defaultTab);
        if ($statusTab === 'To Review') {
            $statusTab = 'Pending Approval';
        }

        if ($isStaff && $statusTab == 'Pending') {
            return redirect('admin?status=Processing');
        }
        if ($statusTab != 'All') {
            if ($statusTab == 'Completed') {
                $query->whereIn('status', Order::STATUSES_COMPLETED);
            } elseif ($statusTab == 'Cancelled') {
                $query->where('status', 'Rejected');
            } elseif ($statusTab == 'Processing') {
                $query->whereIn('status', Order::STATUSES_IN_PROGRESS);
            } elseif ($statusTab == 'Pending Approval') {
                $query->where('status', 'Review');
            } elseif ($statusTab == 'Pending') {
                $query->where('status', 'Pending');
            } else {
                $query->where('status', $statusTab);
            }
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%$search%")
                    ->orWhere('company_name', 'like', "%$search%")
                    ->orWhere('order_id', 'like', "%$search%");
            });
        }

        if ($request->filled('date_start'))
            $query->whereDate('created_at', '>=', $request->date_start);
        if ($request->filled('date_end'))
            $query->whereDate('created_at', '<=', $request->date_end);

        // Amount filter - Admin only? Or Staff allowed? Let's allow but they won't see totals.
        if ($request->filled('min_amount'))
            $query->where('total_amount', '>=', $request->min_amount);
        if ($request->filled('max_amount'))
            $query->where('total_amount', '<=', $request->max_amount);

        // Get Results — limit to 50 for performance; full list in Order Management
        $orders = $query->orderBy('created_at', 'desc')->take(50)->get();

        // Today's Orders / My New Orders card
        $todayOrders = $isStaff
            ? (clone $baseQuery)->where('status', 'Pending')->count()
            : (clone $baseQuery)->whereDate('created_at', $today)->count();
        if ($isStaff) {
            $todaySales = 0;
        }


        if ($request->ajax()) {
            return view('admin.partials.dashboard_rows', compact('orders'))->render();
        }

        return view('admin.dashboard', compact(
            'orders',
            'totalRevenue',
            'potentialSales',
            'pendingCount',
            'inProgressCount',
            'completedCount',
            'rejectedCount',
            'approvedOrders',
            'completedOrdersCount',
            'staffWorkload',
            'totalOrders',
            'chartLabels',
            'chartValues',
            'planLabels',
            'planValues',
            'recentOrders',
            'recentLogs',
            'statusTab',
            'tabCounts',
            'todayOrders',
            'todaySales',
            'isStaff'
        ));
    }

    public function getLatestUpdates(Request $request)
    {
        if (!auth()->check())
            return response()->json(['error' => 'Unauthorized'], 401);

        $user = auth()->user();
        $isStaff = $user->role === 'staff';

        $baseQuery = Order::query();
        if ($isStaff) {
            $baseQuery->where('staff_id', $user->id);
        }

        // Current tab from request (same param as Dashboard index) — single source of truth for list
        $statusTab = $request->input('status', $isStaff ? 'Processing' : 'Pending');
        if ($statusTab === 'To Review') {
            $statusTab = 'Pending Approval';
        }

        // Build same filter as AdminController@index so poll returns exactly what the current tab shows
        $listQuery = (clone $baseQuery);
        if ($statusTab !== 'All') {
            if ($statusTab === 'Completed') {
                $listQuery->whereIn('status', Order::STATUSES_COMPLETED);
            } elseif ($statusTab === 'Cancelled') {
                $listQuery->where('status', 'Rejected');
            } elseif ($statusTab === 'Processing') {
                $listQuery->whereIn('status', Order::STATUSES_IN_PROGRESS);
            } elseif ($statusTab === 'Pending Approval') {
                $listQuery->where('status', 'Review');
            } elseif ($statusTab === 'Pending') {
                $listQuery->where('status', 'Pending');
            } else {
                $listQuery->where('status', $statusTab);
            }
        }
        $latestOrders = $listQuery->orderBy('created_at', 'desc')->take(15)->get();

        // Counts: exact same as Order::getTabCountsForUser (no caching)
        $tabCounts = Order::getTabCountsForUser($user);
        $today = Carbon::today();
        $todayOrders = $isStaff
            ? (clone $baseQuery)->where('status', 'Pending')->count()
            : (clone $baseQuery)->whereDate('created_at', $today)->count();
        $pendingCount = $tabCounts['pending'];

        $payload = [
            'latest_order_id' => $latestOrders->first() ? $latestOrders->first()->id : 0,
            'today_orders' => $todayOrders,
            'pending_count' => $pendingCount,
            'tab_counts' => $tabCounts,
            'html' => view('admin.partials.dashboard_rows', ['orders' => $latestOrders])->render(),
            'status_tab' => $statusTab,
        ];

        if (!$isStaff) {
            $revenue = Order::getRevenueStats();
            $payload['total_revenue'] = $revenue['total_revenue'];
            $payload['today_sales'] = $revenue['today_sales'];
        }

        return response()->json($payload)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function orders(Request $request)
    {
        // Redirect to global controller
        return redirect()->action([OrderController::class, 'index']);
    }

    public function login(Request $request)
    {
        $loginValue = $request->input('username');
        $password = $request->input('password');

        // Legacy Alias: 'admin' checks against specific email
        if ($loginValue === 'admin') {
            $loginValue = 'admin@brandthirty.com';
        } elseif ($loginValue === 'superadmin') {
            $loginValue = 'superadmin@brandthirty.com';
        }

        if (\Illuminate\Support\Facades\Auth::attempt(['email' => $loginValue, 'password' => $password])) {
            return redirect('/admin');
        }

        return view('admin.login')->with('error', "Invalid credentials. Try admin@brandthirty.com / 123");
    }

    public function logout()
    {
        \Illuminate\Support\Facades\Auth::logout();
        return redirect('/admin');
    }

    // --- CRM / Customer Logic ---

    private function syncCustomers()
    {
        // 1. Get all orders and group by email in-memory to prevent N+1 remote DB queries
        $allOrders = Order::select('customer_email', 'customer_name', 'phone', 'created_at', 'total_amount', 'status')
            ->orderBy('created_at', 'desc') // we want latest orders first for the loop
            ->get();

        $grouped = $allOrders->groupBy('customer_email');

        $upsertData = [];
        foreach ($grouped as $email => $ordersForEmail) {
            if (empty($email))
                continue;

            $latestOrder = $ordersForEmail->first();

            // Replicate previous logic: Paid/Completed orders count towards stats
            $paidOrders = $ordersForEmail->filter(function ($o) {
                return in_array($o->status, ['Paid', 'Completed']);
            });

            $totalSpent = $paidOrders->sum('total_amount');
            $orderCount = $paidOrders->count();

            // Pluck the max created_at for paid orders
            $lastOrderDate = $paidOrders->max('created_at');

            $isVip = $totalSpent >= 10000;

            $upsertData[] = [
                'email' => $email,
                'name' => $latestOrder->customer_name,
                'phone' => $latestOrder->phone,
                'total_spent' => $totalSpent,
                'order_count' => $orderCount,
                'is_vip' => $isVip,
                'last_order_at' => $lastOrderDate ? \Carbon\Carbon::parse($lastOrderDate)->format('Y-m-d H:i:s') : null,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ];
        }

        if (!empty($upsertData)) {
            \App\Models\Customer::upsert(
                $upsertData,
                ['email'],
                ['name', 'phone', 'total_spent', 'order_count', 'is_vip', 'last_order_at', 'updated_at']
            );
        }
    }

    public function customers(Request $request)
    {
        if (!Session::get('logged_in'))
            return redirect('/admin/login');

        // Sync first to ensure fresh data
        $this->syncCustomers();

        $query = \App\Models\Customer::query();

        if ($request->has('search') && $request->search != '') {
            $s = $request->search;
            $query->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%");
        }

        $customers = $query->orderBy('is_vip', 'desc')
            ->orderBy('total_spent', 'desc')
            ->get();

        return view('admin.customers', compact('customers'));
    }

    // --- Actions ---

    public function batchUpdate(Request $request)
    {
        $ids = $request->input('order_ids', []);
        $action = $request->input('action'); // 'processing', 'completed', 'cancelled', 'delete'

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No orders selected.');
        }

        foreach ($ids as $id) {
            $order = Order::find($id);
            if (!$order)
                continue;

            switch ($action) {
                case 'processing':
                    $order->update(['status' => 'Processing']);
                    $this->logAction($id, 'Status Change', 'Marked as Processing');
                    break;
                case 'completed':
                    // Strict review workflow: only finalize orders already at Step 7/Review with submitted report.
                    if (($order->status === 'Review' || (int) $order->current_step === 7) && !empty($order->report_file)) {
                        $order->update([
                            'status' => 'Completed',
                            'current_step' => 8,
                            'completed_at' => now(),
                        ]);
                        $this->logAction($id, 'Status Change', 'Batch finalized from Pending Approval to Completed.');
                    } else {
                        $this->logAction($id, 'Status Change Blocked', 'Batch complete blocked: order not in Pending Approval or report missing.');
                    }
                    break;
                case 'cancelled':
                    $order->update(['status' => 'Rejected']);
                    $this->logAction($id, 'Status Change', 'Marked as Rejected (Cancelled)');
                    break;
                case 'delete':
                    $this->logAction($id, 'Delete', 'Order Deleted');
                    $order->delete();
                    break;
            }
        }

        // Sync customers after batch update to reflect potentially new 'Paid' statuses
        $this->syncCustomers();

        return redirect()->back()->with('success', 'Batch action completed successfully.');
    }

    public function markPaid($id)
    {
        Order::where('id', $id)->update(['status' => 'Processing']);
        $this->logAction($id, 'Status Change', 'Marked as Processing (Accepted)');
        return redirect()->back()->with('success', 'Order accepted and moved to Processing.');
    }

    public function markCompleted($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Only admins can mark orders as Completed.');
        }

        Order::where('id', $id)->update([
            'status' => 'Completed',
            'current_step' => 8, // Ensure stepper syncs
            'completed_at' => now(),
        ]);
        $this->logAction($id, 'Status Change', 'Marked as Completed (Step 8)');
        $this->syncCustomers(); // Update stats
        return redirect()->back()->with('success', 'Order marked as Completed.');
    }

    public function markRejected($id)
    {
        Order::where('id', $id)->update(['status' => 'Rejected']);
        $this->logAction($id, 'Status Change', 'Marked as Rejected');
        return redirect()->back()->with('success', 'Order marked as Rejected.');
    }

    public function deleteOrder($id)
    {
        $this->logAction($id, 'Delete', 'Order Deleted');
        Order::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Order Deleted.');
    }

    public function invoice($id)
    {
        $order = Order::findOrFail($id);
        return view('admin.invoice', compact('order'));
    }

    public function downloadInvoice($id)
    {
        $order = Order::findOrFail($id);
        $pdf = Pdf::loadView('admin.invoice', compact('order'));
        return $pdf->download('Invoice_' . $order->order_id . '.pdf');
    }

    public function exportOrders()
    {
        return Excel::download(new OrdersExport, 'orders.xlsx');
    }

    public function edit($id)
    {
        $order = Order::findOrFail($id);
        return view('admin.edit_order', compact('order'));
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $order->customer_name = $request->input('customer_name');
        $order->customer_email = $request->input('customer_email');
        $order->phone = $request->input('phone');
        $order->company_name = $request->input('company_name');
        $order->website_url = $request->input('website_url');
        $order->plan = $request->input('plan');
        $order->strategy = $request->input('strategy');
        $order->total_amount = $request->input('total_amount');

        $order->save();
        $this->logAction($id, 'Update', 'Order details updated');

        return redirect('admin')->with('success', 'Order Updated!');
    }

    // --- Settings ---
    public function settings()
    {
        if (!Session::get('logged_in'))
            return redirect('/admin/login');

        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

}

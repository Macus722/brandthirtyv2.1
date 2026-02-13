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

        // Base Query
        $baseQuery = Order::query();
        if ($isStaff) {
            $baseQuery->where('staff_id', $user->id);
            // Explicitly exclude Pending for staff in all base counts unless specifically querying valid statuses
            $baseQuery->where('status', '!=', 'Pending');
        }

        // Specific Counters (Filtered)
        $pendingCount = (clone $baseQuery)->where('status', 'Pending')->count();
        $inProgressCount = (clone $baseQuery)->whereIn('status', ['Processing', 'In Progress', 'Assigned', 'Review'])->count();
        $completedCount = (clone $baseQuery)->whereIn('status', ['Paid', 'Completed'])->count();
        $rejectedCount = (clone $baseQuery)->where('status', 'Rejected')->count();

        // Financial Stats - HIDDEN for Staff
        $totalRevenue = 0;
        $potentialSales = 0;
        $salesData = collect([]);
        $chartLabels = [];
        $chartValues = [];

        if (!$isStaff) {
            $totalRevenue = Order::whereIn('status', ['Processing', 'In Progress', 'Paid', 'Assigned', 'Review'])->sum('total_amount');
            $potentialSales = Order::sum('total_amount');

            // Charts Data (Revenue Trend)
            $salesData = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
                ->whereIn('status', ['Processing', 'In Progress', 'Paid', 'Assigned', 'Review'])
                ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get();
            $chartLabels = $salesData->pluck('date');
            $chartValues = $salesData->pluck('total');
        }

        // Conversion Funnel Data
        $totalOrders = (clone $baseQuery)->count();
        $approvedOrders = (clone $baseQuery)->whereIn('status', ['Processing', 'In Progress', 'Paid', 'Assigned', 'Review'])->count();
        $completedOrdersCount = $completedCount;

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

        if ($isStaff && $statusTab == 'Pending') {
            return redirect('admin?status=Processing');
        }
        if ($statusTab != 'All') {
            if ($statusTab == 'Completed')
                $query->where('status', 'Paid');
            elseif ($statusTab == 'Cancelled')
                $query->where('status', 'Rejected');
            else
                $query->where('status', $statusTab);
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

        // Get Results
        $orders = $query->orderBy('created_at', 'desc')->get();

        // Simple "Today's Orders" count for the card
        $todayOrders = (clone $baseQuery)->whereDate('created_at', $today)->count();
        $todaySales = $isStaff ? 0 : Order::whereDate('created_at', $today)->sum('total_amount');


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
            'todayOrders',
            'todaySales',
            'isStaff'
        ));
    }

    public function getLatestUpdates()
    {
        if (!auth()->check())
            return response()->json(['error' => 'Unauthorized'], 401);

        $user = auth()->user();
        $isStaff = $user->role === 'staff';

        $baseQuery = Order::query();
        if ($isStaff) {
            $baseQuery->where('staff_id', $user->id);
        }

        // 1. Fetch Latest 10 Orders (Pending or Processing)
        $latestOrders = (clone $baseQuery)->whereIn('status', ['Pending', 'Processing'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // 2. Fetch Stats
        $today = Carbon::today();
        $todayOrders = (clone $baseQuery)->whereDate('created_at', $today)->count();
        $pendingCount = (clone $baseQuery)->whereIn('status', ['Pending', 'Processing'])->count();

        // 3. Render Partial Rows
        $html = view('admin.partials.dashboard_rows', ['orders' => $latestOrders])->render();

        return response()->json([
            'latest_order_id' => $latestOrders->first() ? $latestOrders->first()->id : 0,
            'today_orders' => $todayOrders,
            'pending_count' => $pendingCount,
            'html' => $html
        ]);
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

    // Helper: Syncs ALL customers from orders (Simple approach for this scale)
    // In a larger app, this would be event-driven or a job.
    private function syncCustomers()
    {
        // Get all unique emails from orders
        $emails = Order::select('customer_email')->distinct()->pluck('customer_email');

        foreach ($emails as $email) {
            // Find or create customer
            $customerOrder = Order::where('customer_email', $email)->latest()->first();

            if (!$customerOrder)
                continue;

            $customer = \App\Models\Customer::firstOrCreate(
                ['email' => $email],
                ['name' => $customerOrder->customer_name, 'phone' => $customerOrder->phone]
            );

            // Calculate stats
            $stats = Order::where('customer_email', $email)
                ->where('status', 'Paid')
                ->selectRaw('sum(total_amount) as total_spent, count(*) as order_count, max(created_at) as last_order')
                ->first();

            $totalSpent = $stats->total_spent ?? 0;
            $orderCount = $stats->order_count ?? 0;
            $lastOrder = $stats->last_order ?? null;
            $isVip = $totalSpent >= 10000;

            $customer->update([
                'name' => $customerOrder->customer_name, // Update name in case it changed
                'phone' => $customerOrder->phone,
                'total_spent' => $totalSpent,
                'order_count' => $orderCount,
                'is_vip' => $isVip,
                'last_order_at' => $lastOrder
            ]);
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
                    $order->update(['status' => 'Paid']);
                    $this->logAction($id, 'Status Change', 'Marked as Paid (Completed)');
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
        Order::where('id', $id)->update([
            'status' => 'Completed',
            'current_step' => 8 // Ensure stepper syncs
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderLog;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderSuccessful;
use App\Mail\OrderRejected;
use App\Mail\OrderDelivered;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    // ... existing checkLogin ...

    // ... existing index/show/assignStaff ...
    // Note: I am not replacing the whole file, just the top part to add imports and then targeting specific methods below.
    // wait, replace_file_content replaces a block. I should just add imports at the top first.
    // actually, I can do it in one go if I target the right lines or use multi_replace.
    // Let's use multi_replace to be safe and precise.

    // checkLogin removed - handled by middleware

    public function index(Request $request)
    {
        // Auth handled by Middleware
        $user = auth()->user();

        $baseQuery = Order::query();

        // RBAC: Staff Filter
        if ($user->role == 'staff') {
            $baseQuery->where('staff_id', $user->id);
        }

        // Filter Logic
        // Two distinct "pending" scopes: Customer Pending (Step 2) vs Admin Review (Step 7)
        $defaultTab = ($user->role == 'staff') ? 'Processing' : 'Pending';
        $status = $request->input('status', $defaultTab);
        if ($status === 'To Review') {
            $status = 'Pending Approval';
        }

        if ($user->role == 'staff' && $status == 'Pending') {
            return redirect('admin/orders?status=Processing');
        }

        // Single source of truth (shared with Dashboard)
        $tabCounts = Order::getTabCountsForUser($user);

        $query = (clone $baseQuery)->with('brand');

        if ($status != 'All') {
            if ($status == 'Completed') {
                $query->whereIn('status', Order::STATUSES_COMPLETED);
            } elseif ($status == 'Processing') {
                $query->whereIn('status', Order::STATUSES_IN_PROGRESS);
            } elseif ($status == 'Pending Approval') {
                $query->where('status', 'Review');
            } elseif ($status == 'Cancelled' || $status == 'Rejected') {
                $query->where('status', 'Rejected');
            } elseif ($status == 'Pending') {
                $query->where('status', 'Pending');
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%$search%")
                    ->orWhere('order_id', 'like', "%$search%");
            });
        }
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.orders.index', compact('orders', 'tabCounts', 'status'));
    }

    public function show($id)
    {
        // Auth handled by Middleware

        $order = Order::with(['brand', 'staff'])->findOrFail($id);

        // Auto-fix Sync Issue: If Completed/Paid, ensure Step 8
        if (($order->status == 'Completed' || $order->status == 'Paid') && $order->current_step < 8) {
            $order->current_step = 8;
            $order->save();
        }

        $staffMembers = \App\Models\User::where('role', 'staff')->orWhere('role', 'admin')->get();
        return view('admin.orders.show', compact('order', 'staffMembers'));
    }

    public function assignStaff(Request $request, $id)
    {
        // Auth handled by Middleware
        // Admin Only
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $order = Order::findOrFail($id);

        // Allow assignment at any stage (Requirement: Assign Staff BEFORE approval)
        $order->staff_id = $request->input('staff_id');
        $order->save();

        $staffName = $order->staff_id ? \App\Models\User::find($order->staff_id)->name : 'Unassigned';

        OrderLog::create([
            'order_id' => $id,
            'user' => 'Admin',
            'action' => 'Staff Assignment',
            'details' => "Order assigned to: $staffName"
        ]);

        return redirect()->back()->with('success', "Order assigned to $staffName successfully.");
    }

    public function acceptOrder($id)
    {
        // Auth handled by Middleware
        // Admin Only
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $order = Order::findOrFail($id);
        $order->is_payment_verified = true;
        $order->is_content_verified = true;
        $order->save();

        OrderLog::create([
            'order_id' => $id,
            'user' => 'Admin',
            'action' => 'Order Accepted',
            'details' => 'Admin accepted order (Payment & Content Verified).'
        ]);

        return redirect()->back()->with('success', 'Order Accepted. You can now assign staff.');
    }

    public function verifyPayment($id)
    {
        // Auth handled by Middleware

        // Admin Only
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Only Admins can verify payment.');
        }

        $order = Order::findOrFail($id);

        $order->is_payment_verified = true;
        // Align Step: Step 3 is Payment Verified
        if ($order->current_step < 3) {
            $order->current_step = 3;
        }
        $order->save();

        OrderLog::create([
            'order_id' => $id,
            'user' => 'Admin',
            'action' => 'Payment Verification',
            'details' => "Payment marked as Verified."
        ]);

        return redirect()->back()->with('success', "Payment status verified (Step 3).");
    }

    public function verifyContent($id)
    {
        // Auth handled by Middleware
        $order = Order::findOrFail($id);

        $order->is_content_verified = true;
        $order->save();

        OrderLog::create([
            'order_id' => $id,
            'user' => 'Admin',
            'action' => 'Content Verification',
            'details' => "Content marked as Accepted."
        ]);

        return redirect()->back()->with('success', "Content accepted.");
    }

    // "Confirm & Approve" Action
    // "Confirm & Approve" Action (Manual Override)
    // Unified "Approve Order" Action (Replacing Accept/Verify Manual Steps)
    public function approve($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $order = Order::findOrFail($id);

        if (!$order->staff_id) {
            return redirect()->back()->with('error', 'Please assign a staff member before approving the order.');
        }

        $order->is_payment_verified = true;
        $order->is_content_verified = true;
        $order->current_step = 3;
        $order->status = 'Assigned';
        $order->approved_at = now();
        $order->save();

        OrderLog::create([
            'order_id' => $id,
            'user' => auth()->user()->name,
            'action' => 'Order Approved',
            'details' => 'Admin approved order. Payment & Content Verified. Locked to Step 3.'
        ]);

        // Generate Invoice PDF and send "Order Successful" email (log driver in local = no connection needed)
        $order->load('brand');
        try {
            $pdf = Pdf::loadView('admin.invoice', ['order' => $order]);
            Mail::to($order->customer_email)->send(new OrderSuccessful($order, $pdf));

            OrderLog::create([
                'order_id' => $id,
                'user' => 'System',
                'action' => 'Invoice & Email Sent',
                'details' => 'Auto-generated invoice PDF and sent OrderSuccessful email to ' . $order->customer_email
            ]);
        } catch (\Throwable $e) {
            \Log::error("Failed to send approval email for order #{$order->order_id}: " . $e->getMessage());
            return redirect()->back()->with('success', 'Order approved. Email could not be sent (check storage/logs/laravel.log).');
        }

        return redirect()->back()->with('success', 'Order approved. Invoice PDF emailed to ' . $order->customer_email . '.');
    }

    public function rejectContent(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:2000'], [
            'reason.required' => 'Please provide a rejection reason (for the order and customer email).',
        ]);

        $order = Order::findOrFail($id);
        $order->status = 'Rejected';
        $order->rejection_reason = $request->input('reason');
        $order->save();

        $order->load('brand');

        try {
            Mail::to($order->customer_email)->send(new OrderRejected($order));
        } catch (\Exception $e) {
            \Log::error("Failed to send rejection email: " . $e->getMessage());
        }

        OrderLog::create([
            'order_id' => $id,
            'user' => auth()->user()->name,
            'action' => 'Order Rejected',
            'details' => 'Reason: ' . $request->input('reason')
        ]);

        return redirect()->back()->with('success', 'Order rejected. Reason saved and customer notified by email.');
    }

    public function complete(Request $request, $id)
    {
        // Staff uploads report -> move to admin review (Step 7), not completed directly.
        $request->validate([
            'report_file' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,zip',
                'max:10240', // 10MB
            ],
        ], [
            'report_file.file' => 'The uploaded file is invalid.',
            'report_file.mimes' => 'The report must be a PDF, DOC, DOCX or ZIP file.',
            'report_file.max' => 'The report file must not exceed 10MB.',
        ]);

        $order = Order::findOrFail($id);

        // Only assigned staff (or admin) can submit final work for review.
        if (auth()->user()->role == 'staff' && $order->staff_id != auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        if ($request->hasFile('report_file')) {
            $path = $request->file('report_file')->store('reports', 'public');
            $order->report_file = $path;
        }

        $order->current_step = Order::STEP_REPORT_UPLOADED; // Step 7
        $order->status = 'Review'; // Pending admin approval
        $order->completed_at = null;
        $order->save();

        OrderLog::create([
            'order_id' => $id,
            'user' => auth()->user()->name,
            'action' => 'Report Submitted',
            'details' => 'Staff uploaded final report and submitted for admin approval (Step 7).'
        ]);

        return redirect()->back()->with('success', 'Report uploaded. Order is now pending admin approval.');
    }

    public function adminApprove($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Only admins can finalize orders.');
        }

        $order = Order::findOrFail($id);
        if (!$this->isPendingAdminApproval($order)) {
            return redirect()->back()->with('error', 'Only orders in Pending Approval (Step 7) can be finalized.');
        }

        $this->finalizeAsCompleted($order);

        return redirect()->back()->with('success', 'Order approved and marked as Completed.');
    }

    // Deprecated GET endpoint compatibility: /admin/completed/{id}
    public function adminApproveLegacy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Only admins can finalize orders.');
        }

        $order = Order::findOrFail($id);
        if (!$this->isPendingAdminApproval($order)) {
            return redirect()->back()->with('error', 'Deprecated endpoint blocked: order must be at Pending Approval (Step 7).');
        }

        $this->finalizeAsCompleted($order);

        return redirect()->back()->with('success', 'Order approved and finished (via deprecated endpoint).');
    }

    private function isPendingAdminApproval(Order $order): bool
    {
        return $order->status === 'Review' || (int) $order->current_step === (int) Order::STEP_REPORT_UPLOADED;
    }

    private function finalizeAsCompleted(Order $order): void
    {
        $order->current_step = Order::STEP_COMPLETED;
        $order->status = 'Completed';
        $order->completed_at = now();
        $order->save();

        OrderLog::create([
            'order_id' => $order->id,
            'user' => auth()->user()->name,
            'action' => 'Admin Approved & Finished',
            'details' => 'Admin approved submitted work and finalized order (Step 8).'
        ]);

        // Send "Order Delivered" email to customer with report_file from storage attached
        $order->load('brand');
        try {
            Mail::to($order->customer_email)->send(new OrderDelivered($order));

            OrderLog::create([
                'order_id' => $order->id,
                'user' => 'System',
                'action' => 'Delivery Email Sent',
                'details' => 'Sent OrderDelivered email to ' . $order->customer_email
                    . ($order->report_file ? ' (report attached)' : ' (no report file)')
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to send delivery email for order #{$order->order_id}: " . $e->getMessage());
        }
    }

    public function submitForReview($id)
    {
        // Auth handled by Middleware
        $order = Order::findOrFail($id);

        // Advance to Step 7 (Report Ready / Review)
        $order->current_step = 7;
        $order->status = 'Review'; // Or keep 'Processing' but use step 7 to indicate review
        $order->save();

        OrderLog::create([
            'order_id' => $id,
            'user' => auth()->user()->name,
            'action' => 'Submitted for Review',
            'details' => 'Staff submitted order for final verification (Step 7).'
        ]);

        return redirect()->back()->with('success', 'Order submitted to Admin for final review.');
    }

    // Quick Complete for Dashboard Actions
    public function quickComplete($id)
    {
        // Auth handled by Middleware
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $order = Order::findOrFail($id);
        if (!$this->isPendingAdminApproval($order)) {
            return redirect()->back()->with('error', 'Quick Complete disabled: order must be at Pending Approval (Step 7).');
        }

        $this->finalizeAsCompleted($order);

        return redirect()->back()->with('success', 'Order approved and marked as Completed.');
    }

    // Staff: Start Work
    public function startWork($id)
    {
        // Auth handled by Middleware
        $order = Order::findOrFail($id);

        // Ensure user is the assigned staff
        if (auth()->user()->role == 'staff' && $order->staff_id != auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $order->status = 'Processing'; // Or 'In Progress' - let's stick to Processing for consistent tab visibility
        $order->current_step = 6; // In Progress
        $order->save();

        OrderLog::create([
            'order_id' => $id,
            'user' => auth()->user()->name,
            'action' => 'Started Work',
            'details' => 'Staff started working on order (Step 6).'
        ]);

        return redirect()->back()->with('success', 'Order status updated to Processing (Step 6).');
    }
}

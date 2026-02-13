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

        $query = Order::query()->with('brand');

        // RBAC: Staff Filter
        if ($user->role == 'staff') {
            $query->where('staff_id', $user->id);
        }

        // Filter Logic
        $defaultTab = ($user->role == 'staff') ? 'Processing' : 'Pending';
        $status = $request->input('status', $defaultTab);

        // Redirect Staff attempting to access Pending
        if ($user->role == 'staff' && $status == 'Pending') {
            return redirect('admin/orders?status=Processing');
        }

        if ($status != 'All') {
            if ($status == 'Completed') {
                $query->whereIn('status', ['Completed', 'Paid']);
            } elseif ($status == 'Processing') {
                $query->whereIn('status', ['Processing', 'In Progress', 'Review', 'Assigned']);
            } elseif ($status == 'Cancelled' || $status == 'Rejected') {
                $query->where('status', 'Rejected');
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
        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        // Auth handled by Middleware

        $order = Order::findOrFail($id);

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
        // Auth handled by Middleware
        // Admin Only
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $order = Order::findOrFail($id);

        // Requirement: Staff must be assigned BEFORE approval
        if (!$order->staff_id) {
            return redirect()->back()->with('error', 'Please assign a staff member before approving the order.');
        }

        // 1. Force Verification Logic
        $order->is_payment_verified = true;
        $order->is_content_verified = true;

        // 2. Sync Stepper & Status
        // Force Step 3 (Payment Verified) as requested.
        // Step 4 (Content Pending) starts effectively immediately after since verified.
        // User requested: "Force set current_step = 3".
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

        return redirect()->back()->with('success', 'Order Approved and sent to Staff dashboard.');
    }

    public function rejectContent(Request $request, $id)
    {
        // Auth handled by Middleware
        $order = Order::findOrFail($id);

        $order->status = 'Rejected';
        $order->rejection_reason = $request->input('reason');
        $order->save();

        // Send Rejection Email
        try {
            Mail::to($order->customer_email)->send(new OrderRejected($order));
        } catch (\Exception $e) {
            \Log::error("Failed to send rejection email: " . $e->getMessage());
        }

        OrderLog::create([
            'order_id' => $id,
            'user' => 'Admin',
            'action' => 'Order Rejected',
            'details' => 'Reason: ' . $request->input('reason')
        ]);

        return redirect()->back()->with('success', 'Order Rejected and Notification Sent.');
    }

    public function complete(Request $request, $id)
    {
        // Auth handled by Middleware
        $request->validate([
            'report_file' => 'required|file|mimes:pdf,doc,docx,zip|max:10240'
        ]);

        $order = Order::findOrFail($id);

        if ($request->hasFile('report_file')) {
            $path = $request->file('report_file')->store('reports', 'public');
            $order->report_file = $path;
        }

        // Step 7: Report Uploaded (implicitly done by uploading)
        // Step 8: Completed
        $order->current_step = Order::STEP_COMPLETED;
        $order->status = 'Completed';
        $order->completed_at = now();
        $order->save();

        OrderLog::create([
            'order_id' => $id,
            'user' => auth()->user() ? auth()->user()->name : 'Admin',
            'action' => 'Order Completed',
            'details' => 'Final Report Uploaded & Order Completed (Step 8).'
        ]);

        return redirect()->back()->with('success', 'Order Completed and Report Uploaded.');
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
        $order->current_step = 8;
        $order->status = 'Completed';
        $order->completed_at = now();
        $order->save();

        OrderLog::create([
            'order_id' => $id,
            'user' => 'Admin',
            'action' => 'Quick Complete',
            'details' => 'Order marked as Completed via Dashboard Quick Action.'
        ]);

        return redirect()->back()->with('success', 'Order successfully marked as Completed.');
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

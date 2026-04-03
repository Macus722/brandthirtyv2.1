<?php

namespace App\Http\Controllers;

use App\Models\OfflineClient;
use App\Models\OfflinePayment;
use App\Models\Order;
use App\Exports\OfflinePaymentsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends Controller
{
    /**
     * Hub page — two option cards.
     */
    public function index()
    {
        $onlineCount = Order::whereIn('status', ['Completed', 'Paid'])->count();
        $offlineCount = OfflineClient::where('status', 'active')->count();

        return view('admin.clients.index', compact('onlineCount', 'offlineCount'));
    }

    /**
     * Online Sales — read-only list of website orders.
     */
    public function onlineSales(Request $request)
    {
        $query = Order::with('staff')->orderBy('created_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('order_id', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'Completed') {
                $query->whereIn('status', ['Completed', 'Paid']);
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.clients.online', compact('orders'));
    }

    // ─────────────────────────────────────────────────────────
    //  OFFLINE CLIENTS  –  CRUD
    // ─────────────────────────────────────────────────────────

    public function offlineIndex(Request $request)
    {
        $query = OfflineClient::with('payments')->orderBy('created_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('pic_phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $clients = $query->paginate(20)->withQueryString();

        return view('admin.clients.offline', compact('clients'));
    }

    public function offlineCreate()
    {
        $client = null;
        return view('admin.clients.offline_form', compact('client'));
    }

    public function offlineStore(Request $request)
    {
        $rules = [
            'company_name' => 'required|string|max:255',
            'pic_name' => 'required|string|max:255',
            'pic_phone' => 'required|string|max:50',
            'pic_email' => 'nullable|email|max:255',
            'billing_mode' => 'required|in:fixed,recurring',
            'monthly_payment' => 'required|numeric|min:1',
            'contract_start' => 'required|date',
            'due_day' => 'required|integer|min:1|max:28',
            'notes' => 'nullable|string|max:2000',
        ];

        if ($request->input('billing_mode') === 'fixed') {
            $rules['total_package'] = 'required|numeric|min:1';
        } else {
            $rules['total_package'] = 'nullable|numeric';
        }

        $validated = $request->validate($rules);

        if ($validated['billing_mode'] === 'recurring') {
            $validated['total_package'] = null;
        }

        OfflineClient::create($validated);

        return redirect(url('admin/clients/offline'))->with('success', 'Client added successfully.');
    }

    public function offlineEdit($id)
    {
        $client = OfflineClient::findOrFail($id);
        return view('admin.clients.offline_form', compact('client'));
    }

    public function offlineUpdate(Request $request, $id)
    {
        $client = OfflineClient::findOrFail($id);

        $rules = [
            'company_name' => 'required|string|max:255',
            'pic_name' => 'required|string|max:255',
            'pic_phone' => 'required|string|max:50',
            'pic_email' => 'nullable|email|max:255',
            'billing_mode' => 'required|in:fixed,recurring',
            'monthly_payment' => 'required|numeric|min:1',
            'contract_start' => 'required|date',
            'due_day' => 'required|integer|min:1|max:28',
            'notes' => 'nullable|string|max:2000',
            'status' => 'nullable|in:active,completed,cancelled',
        ];

        if ($request->input('billing_mode') === 'fixed') {
            $rules['total_package'] = 'required|numeric|min:1';
        } else {
            $rules['total_package'] = 'nullable|numeric';
        }

        $validated = $request->validate($rules);

        if ($validated['billing_mode'] === 'recurring') {
            $validated['total_package'] = null;
        }

        $client->update($validated);

        return redirect(url('admin/clients/offline'))->with('success', 'Client updated successfully.');
    }

    public function offlineDelete($id)
    {
        $client = OfflineClient::findOrFail($id);
        $client->delete();

        return redirect(url('admin/clients/offline'))->with('success', 'Client deleted successfully.');
    }

    // ─────────────────────────────────────────────────────────
    //  PAYMENTS  –  Variable amount + invoice
    // ─────────────────────────────────────────────────────────

    /**
     * Mark a month as paid with a custom amount.
     */
    public function markPaid(Request $request, $id)
    {
        $client = OfflineClient::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_note' => 'nullable|string|max:500',
        ]);

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $amount = (float) $request->input('amount');

        // Prevent duplicate payment for same period
        $exists = OfflinePayment::where('offline_client_id', $client->id)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->exists();

        if ($exists) {
            return redirect(url('admin/clients/offline'))->with('info', 'Payment already recorded for this period.');
        }

        // Generate invoice number: INV-YYYYMM-XXXX
        $invoiceSeq = OfflinePayment::whereNotNull('invoice_number')->count() + 1;
        $invoiceNumber = 'INV-' . now()->format('Ym') . '-' . str_pad($invoiceSeq, 4, '0', STR_PAD_LEFT);

        OfflinePayment::create([
            'offline_client_id' => $client->id,
            'period_month' => $month,
            'period_year' => $year,
            'amount' => $amount,
            'notes' => $request->input('payment_note'),
            'invoice_number' => $invoiceNumber,
            'paid_at' => now(),
            'marked_by' => auth()->id(),
        ]);

        // Auto-complete if fully paid (only for Fixed Contract mode)
        if ($client->isFixed() && $client->total_package) {
            $totalPaid = $client->payments()->sum('amount') + $amount;
            if ($totalPaid >= $client->total_package) {
                $client->update(['status' => 'completed']);
            }
        }

        return redirect(url('admin/clients/offline'))->with('success', 'Payment of RM ' . number_format($amount, 2) . ' recorded for ' . Carbon::create($year, $month)->format('F Y') . '.');
    }

    // ─────────────────────────────────────────────────────────
    //  REPORTS
    // ─────────────────────────────────────────────────────────

    /**
     * Financial reports dashboard.
     */
    public function offlineReports()
    {
        // === Revenue Summary Cards ===
        $totalCollected = OfflinePayment::sum('amount');
        $thisMonthCollected = OfflinePayment::where('period_month', now()->month)
            ->where('period_year', now()->year)
            ->sum('amount');
        $activeClients = OfflineClient::where('status', 'active')->count();
        $expectedMonthly = OfflineClient::where('status', 'active')->sum('monthly_payment');

        // === Monthly Collections Chart (last 12 months) ===
        $chartLabels = [];
        $chartCollected = [];
        $chartExpected = [];

        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $chartLabels[] = $d->format('M Y');
            $chartCollected[] = (float) OfflinePayment::where('period_month', $d->month)
                ->where('period_year', $d->year)
                ->sum('amount');
            $chartExpected[] = (float) $expectedMonthly;
        }

        // === Aging Report — Unpaid clients ===
        $overdueClients = [];
        $activeClientList = OfflineClient::where('status', 'active')->with('payments')->get();

        foreach ($activeClientList as $client) {
            $unpaidMonths = [];
            // Check last 6 months
            for ($i = 0; $i <= 5; $i++) {
                $d = now()->subMonths($i);
                $status = $client->getPaymentStatus($d->month, $d->year);
                if ($status === 'Unpaid') {
                    $unpaidMonths[] = [
                        'period' => $d->format('M Y'),
                        'amount' => (float) $client->monthly_payment,
                    ];
                }
            }

            if (count($unpaidMonths) > 0) {
                $overdueClients[] = [
                    'client' => $client,
                    'unpaid_months' => $unpaidMonths,
                    'total_overdue' => array_sum(array_column($unpaidMonths, 'amount')),
                ];
            }
        }

        return view('admin.clients.reports', compact(
            'totalCollected',
            'thisMonthCollected',
            'activeClients',
            'expectedMonthly',
            'chartLabels',
            'chartCollected',
            'chartExpected',
            'overdueClients'
        ));
    }

    // ─────────────────────────────────────────────────────────
    //  INVOICE PDF
    // ─────────────────────────────────────────────────────────

    /**
     * Generate and download an invoice PDF for a payment.
     */
    public function generateInvoice($paymentId)
    {
        $payment = OfflinePayment::with('client')->findOrFail($paymentId);

        // Generate invoice number if missing
        if (!$payment->invoice_number) {
            $seq = OfflinePayment::whereNotNull('invoice_number')->count() + 1;
            $payment->invoice_number = 'INV-' . $payment->paid_at->format('Ym') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            $payment->save();
        }

        $pdf = Pdf::loadView('admin.clients.invoice', compact('payment'));
        $pdf->setPaper('a4');

        return $pdf->download($payment->invoice_number . '.pdf');
    }

    // ─────────────────────────────────────────────────────────
    //  EXCEL EXPORT
    // ─────────────────────────────────────────────────────────

    /**
     * Export all offline payments as Excel.
     */
    public function exportPayments()
    {
        return Excel::download(new OfflinePaymentsExport, 'offline-payments-' . now()->format('Y-m-d') . '.xlsx');
    }
}

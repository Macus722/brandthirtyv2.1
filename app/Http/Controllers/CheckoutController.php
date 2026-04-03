<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Order;

class CheckoutController extends Controller
{
    /** Single source of truth for prices – must match order page data-price values. */
    protected $planPrices = [
        'access' => 980,
        'growth' => 2380,
        'authority' => 3980,
        'ultimate' => 4980,
    ];

    protected $adminEmail = 'admin@brandthirty.com';
    protected $whatsappNumber = "601111293598";
    protected $costPerReach = 200;

    /**
     * Handle the checkout form submission and show confirmation page.
     */
    public function process(Request $request)
    {
        // 1. Validate Input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'plan' => 'required|string',
            'strategy' => 'required|string',
            'distribution' => 'nullable|integer',
        ]);

        // 2. Calculate Costs (canonical prices – same logic as order page)
        $selectedPlan = strtolower(trim($validated['plan'] ?? ''));
        $strategy = strtolower(trim($validated['strategy'] ?? ''));
        $distributionCount = (int) ($validated['distribution'] ?? 0);
        $validated['distribution'] = $distributionCount;

        $grandTotal = 0;

        $price = $this->planPrices[$selectedPlan] ?? $this->planPrices['access'];
        $grandTotal += (int) $price;

        // Strategy Cost
        $addonText = "No Content Add-on";
        if (strpos($strategy, 'pro') !== false) {
            $grandTotal += 200;
            $addonText = "Pro Copywriting (+RM 200)";
        } elseif (strpos($strategy, 'ai') !== false) {
            $grandTotal += 100;
            $addonText = "AI-Assisted Content (+RM 100)";
        } else {
            $addonText = "Self-Provide Content (RM 0)";
        }

        // Distribution Cost
        // Frontend logic: distValue * 200
        $distCost = $distributionCount * $this->costPerReach;
        $grandTotal += $distCost;

        // Generate preliminary Order ID for display
        $orderId = 'B30-' . strtoupper(Str::random(6));

        // Generate WA Links
        $waText = "Hi BrandThirty, I am interested in Order $orderId (Total: RM $grandTotal).";
        $waUrl = "https://wa.me/" . $this->whatsappNumber . "?text=" . urlencode($waText);

        $ccWaText = "Request Card Link";
        $ccWaUrl = "https://wa.me/" . $this->whatsappNumber . "?text=" . urlencode($ccWaText);

        // Render Confirmation View
        return view('checkout_confirmation', [
            'orderData' => $validated,
            'grandTotal' => $grandTotal,
            'orderId' => $orderId,
            'addonText' => $addonText,
            'waUrl' => $waUrl,
            'ccWaUrl' => $ccWaUrl
        ]);
    }

    /**
     * Handle final confirmation and DB insertion.
     */
    public function confirm(Request $request)
    {
        // 1. Validate Confirmation Data
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'company' => 'nullable|string',
            'website' => 'nullable|string|max:500',
            'plan' => 'required|string',
            'strategy' => 'required|string',
            'distribution' => 'nullable|integer',
            'total_amount' => 'required|numeric', // Validated but re-calculated below
            'order_id' => 'required|string',
            'confirm_payment' => 'required',
            'receipt' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max, payment proof
        ]);

        // 2. Duplicate Check
        $currentHash = md5(json_encode($request->except(['_token', 'order_id', 'receipt']))); // Exclude token, order_id, and file from hash
        $isDuplicate = false;

        if (Session::get('last_order_hash') === $currentHash) {
            $isDuplicate = true;
        }

        if (!$isDuplicate) {
            // 3. Database Insertion
            try {
                // RE-CALCULATE COSTS SERVER-SIDE (same canonical prices as process())
                $selectedPlan = strtolower(trim($data['plan'] ?? ''));
                $strategy = strtolower(trim($data['strategy'] ?? ''));
                $distributionCount = (int) ($data['distribution'] ?? 0);
                $grandTotal = 0;

                $price = $this->planPrices[$selectedPlan] ?? $this->planPrices['access'];
                $grandTotal += (int) $price;

                // 2. Strategy Cost
                if (strpos($strategy, 'pro') !== false) {
                    $grandTotal += 200;
                } elseif (strpos($strategy, 'ai') !== false) {
                    $grandTotal += 100;
                }

                // 3. Distribution Cost
                $grandTotal += ($distributionCount * $this->costPerReach);

                $order = Order::create([
                    'order_id' => $data['order_id'],
                    'customer_name' => $data['name'],
                    'customer_email' => $data['email'],
                    'phone' => $data['phone'],
                    'company_name' => $data['company'],
                    'website_url' => $data['website'],
                    'plan' => $data['plan'],
                    'strategy' => $data['strategy'],
                    'distribution_reach' => $data['distribution'] ?? 0,
                    'total_amount' => $grandTotal,
                    'status' => 'Pending',
                    'current_step' => 1,
                ]);

                // Store payment proof image if uploaded
                if ($request->hasFile('receipt')) {
                    try {
                        $path = $request->file('receipt')->store('receipts/' . $order->id, 'public');
                        if ($path) {
                            $order->update(['receipt_path' => $path]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Payment proof upload failed: ' . $e->getMessage());
                    }
                }

                // Update data array for emails so they show the correct, calculated amount
                $data['total_amount'] = $grandTotal;

                Session::put('last_order_hash', $currentHash);

                $adminSubject = "New Order Alert - {$data['name']} - " . ucfirst($data['plan']);
                $adminMessage = "
                <h3>New Order Received ({$data['order_id']})</h3>
                <p><strong>Customer:</strong> {$data['name']} ({$data['email']})</p>
                <p><strong>Phone:</strong> {$data['phone']}</p>
                <p><strong>Company:</strong> {$data['company']}</p> 
                <p><strong>Website:</strong> {$data['website']}</p>
                <hr>
                <p><strong>Total:</strong> RM {$data['total_amount']}</p>
                <p><strong>Plan:</strong> " . ucfirst($data['plan']) . "</p>
                <p><strong>Distribution:</strong> {$data['distribution']} Articles</p>
                ";

                $customerSubject = "Order Confirmation - BrandThirty";
                $customerMessage = "
                <h3>Thank you for your order!</h3>
                <p>Your Order ID is <strong>{$data['order_id']}</strong>.</p>
                <p>Total Pending: <strong>RM {$data['total_amount']}</strong></p>
                <p>Please complete your payment via DuitNow or Bank Transfer to proceed.</p>
                ";

                try {
                    Mail::html($adminMessage, function ($message) use ($data, $adminSubject) {
                        $message->to($this->adminEmail)
                            ->subject($adminSubject)
                            ->from('no-reply@brandthirty.com', 'BrandThirty Orders');
                    });

                    Mail::html($customerMessage, function ($message) use ($data, $customerSubject) {
                        $message->to($data['email'])
                            ->subject($customerSubject)
                            ->from('no-reply@brandthirty.com', 'BrandThirty Orders');
                    });
                } catch (\Exception $e) {
                    \Log::error('Failed to send order emails: ' . $e->getMessage());
                }

            } catch (\Exception $e) {
                // Log error or handle DB failure locally
                // \Log::error('Order DB Insert Failed: ' . $e->getMessage());
            }
        }

        // 5. Generate Success Response (Redirect to WhatsApp)
        $waText = "Hi BrandThirty, I have placed Order {$data['order_id']} (Total: RM {$data['total_amount']}). Here is my payment receipt.";
        $waUrl = "https://wa.me/" . $this->whatsappNumber . "?text=" . urlencode($waText);

        // Return a view that shows SweetAlert then redirects
        return view('order_success', ['waUrl' => $waUrl]);
    }
}

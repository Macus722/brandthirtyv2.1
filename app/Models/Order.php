<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /** Statuses that count as "In Progress" (Steps 3–6) for tab/filter. */
    const STATUSES_IN_PROGRESS = ['Processing', 'In Progress', 'Assigned'];

    /** Statuses that count as "Completed" for counts and revenue. */
    const STATUSES_COMPLETED = ['Completed', 'Paid'];

    /** Statuses for "Pending Approval" (Step 7). */
    const STATUSES_PENDING_APPROVAL = ['Review'];

    // Workflow Steps
    const STEP_ORDER_PLACED = 1;
    const STEP_PENDING_PAYMENT = 2;
    const STEP_PAYMENT_VERIFIED = 3;
    const STEP_CONTENT_PENDING = 4;
    const STEP_CONTENT_REVIEW = 5;
    const STEP_IN_PROGRESS = 6;
    const STEP_REPORT_UPLOADED = 7;
    const STEP_COMPLETED = 8;

    protected $fillable = [
        'order_id',
        'brand_id',
        'current_step',
        'staff_id',
        'customer_name',
        'customer_email',
        'phone',
        'company_name',
        'website_url',
        'plan',
        'strategy',
        'distribution_reach',
        'total_amount',
        'status',
        'rejection_reason',
        'report_file',
        'receipt_path',
        'is_payment_verified',
        'is_content_verified', // Keep specifically for backward compat or granular checks
        'approved_at',
        'completed_at'
    ];

    protected $casts = [
        'is_payment_verified' => 'boolean',
        'is_content_verified' => 'boolean',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Single source of truth for tab counts. Used by Dashboard and Order Management.
     * One query with conditional aggregation to avoid 6 separate COUNTs.
     */
    public static function getTabCountsForUser($user): array
    {
        $base = static::query();
        if ($user && $user->role === 'staff') {
            $base->where('staff_id', $user->id);
        }

        $row = (clone $base)->selectRaw(
            "SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
             SUM(CASE WHEN status = 'Review' THEN 1 ELSE 0 END) as pending_approval,
             SUM(CASE WHEN status IN ('Processing','In Progress','Assigned') THEN 1 ELSE 0 END) as processing,
             SUM(CASE WHEN status IN ('Completed','Paid') THEN 1 ELSE 0 END) as completed,
             SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as cancelled,
             COUNT(*) as all_count"
        )->first();

        $counts = [
            'pending'          => (int) ($row->pending ?? 0),
            'pending_approval' => (int) ($row->pending_approval ?? 0),
            'processing'       => (int) ($row->processing ?? 0),
            'completed'        => (int) ($row->completed ?? 0),
            'cancelled'        => (int) ($row->cancelled ?? 0),
            'all'              => (int) ($row->all_count ?? 0),
        ];

        if (config('app.debug') && app()->runningInConsole() === false) {
            $sumOfTabs = $counts['pending'] + $counts['pending_approval'] + $counts['processing']
                + $counts['completed'] + $counts['cancelled'];
            \Log::debug('[TabCounts] ' . request()->path() . ' all=' . $counts['all'] . ' sumOfTabs=' . $sumOfTabs);
        }

        return $counts;
    }

    /**
     * Total revenue and today's sales from completed orders only (matches "Completed" tab).
     * Admin-only; no staff filter.
     */
    public static function getRevenueStats(): array
    {
        $completedQuery = static::whereIn('status', self::STATUSES_COMPLETED);
        $totalRevenue = (clone $completedQuery)->sum('total_amount');
        $todaySales = (clone $completedQuery)
            ->where(function (Builder $q) {
                $q->whereDate('completed_at', now()->toDateString())
                    ->orWhere(function (Builder $q2) {
                        $q2->whereNull('completed_at')->whereDate('created_at', now()->toDateString());
                    });
            })
            ->sum('total_amount');

        return [
            'total_revenue' => $totalRevenue,
            'today_sales' => $todaySales,
        ];
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}

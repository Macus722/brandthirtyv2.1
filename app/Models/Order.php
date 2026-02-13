<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

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

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}

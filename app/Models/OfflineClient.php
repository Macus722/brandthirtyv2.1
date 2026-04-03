<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflineClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'pic_name',
        'pic_phone',
        'pic_email',
        'total_package',
        'monthly_payment',
        'contract_start',
        'due_day',
        'notes',
        'status',
        'billing_mode',
    ];

    protected $casts = [
        'total_package' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'contract_start' => 'date',
        'due_day' => 'integer',
    ];

    // ─── Relationships ───

    public function payments()
    {
        return $this->hasMany(OfflinePayment::class);
    }

    // ─── Mode Helpers ───

    /**
     * Check if this client is on a recurring retainer (no total cap).
     */
    public function isRecurring(): bool
    {
        return $this->billing_mode === 'recurring';
    }

    /**
     * Check if this client is on a fixed contract (total package + installments).
     */
    public function isFixed(): bool
    {
        return $this->billing_mode !== 'recurring';
    }

    // ─── Computed Attributes ───

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /**
     * Remaining balance — only meaningful for fixed contracts.
     * Returns 0 for recurring retainers.
     */
    public function getRemainingBalanceAttribute(): float
    {
        if ($this->isRecurring()) {
            return 0;
        }
        return (float) ($this->total_package ?? 0) - $this->total_paid;
    }

    /**
     * Progress percentage — only meaningful for fixed contracts.
     * Returns 0 for recurring retainers (no finite goal).
     */
    public function getProgressPercentAttribute(): float
    {
        if ($this->isRecurring() || !$this->total_package || $this->total_package <= 0) {
            return $this->isRecurring() ? 0 : 100;
        }
        return min(100, round(($this->total_paid / $this->total_package) * 100, 1));
    }

    /**
     * How many months since contract start.
     */
    public function getMonthsActiveAttribute(): int
    {
        return max(0, (int) $this->contract_start->diffInMonths(now()));
    }

    /**
     * Next billing date — the due day of the earliest UNPAID month.
     * Payment-aware: if current month is paid, jumps to next unpaid month.
     */
    public function getNextBillingDateAttribute(): Carbon
    {
        $nextUnpaid = $this->getNextUnpaidMonth();
        $daysInMonth = Carbon::create($nextUnpaid['year'], $nextUnpaid['month'], 1)->daysInMonth;
        return Carbon::create($nextUnpaid['year'], $nextUnpaid['month'], min($this->due_day, $daysInMonth));
    }

    // ─── Payment Status Logic ───

    /**
     * Get payment status for a given month/year.
     * Uses period_month + period_year tags on payment records.
     *
     * @return string 'Paid' | 'Unpaid' | 'Upcoming' | 'N/A'
     */
    public function getPaymentStatus(int $month, int $year): string
    {
        // Check if a payment record exists for this billing month (Year-Month tag)
        $paid = $this->payments()
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->exists();

        if ($paid) {
            return 'Paid';
        }

        // Check if contract had started by that month
        $periodStart = Carbon::create($year, $month, 1);
        if ($periodStart->lt($this->contract_start->startOfMonth())) {
            return 'N/A'; // Contract hadn't started yet
        }

        // Check if we're past the due day for the given month
        $today = Carbon::today();
        $dueDate = Carbon::create($year, $month, min($this->due_day, $periodStart->daysInMonth));

        if ($today->gte($dueDate)) {
            return 'Unpaid';
        }

        return 'Upcoming';
    }

    /**
     * Current month payment status.
     */
    public function getCurrentMonthStatus(): string
    {
        return $this->getPaymentStatus(now()->month, now()->year);
    }

    /**
     * Last month payment status.
     */
    public function getLastMonthStatus(): string
    {
        $lastMonth = now()->subMonth();
        return $this->getPaymentStatus($lastMonth->month, $lastMonth->year);
    }

    /**
     * Find the earliest unpaid month starting from contract_start up to the current month (or next month for advance).
     * Returns ['month' => int, 'year' => int, 'label' => string]
     */
    public function getNextUnpaidMonth(): array
    {
        $cursor = $this->contract_start->copy()->startOfMonth();
        $limit = Carbon::today()->addMonth()->startOfMonth(); // allow 1 month advance

        while ($cursor->lte($limit)) {
            $paid = $this->payments()
                ->where('period_month', $cursor->month)
                ->where('period_year', $cursor->year)
                ->exists();

            if (!$paid) {
                return [
                    'month' => $cursor->month,
                    'year' => $cursor->year,
                    'label' => $cursor->format('F Y'),
                ];
            }

            $cursor->addMonth();
        }

        // All paid up to limit — offer next month after limit
        return [
            'month' => $cursor->month,
            'year' => $cursor->year,
            'label' => $cursor->format('F Y'),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflinePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'offline_client_id',
        'period_month',
        'period_year',
        'amount',
        'notes',
        'invoice_number',
        'paid_at',
        'marked_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(OfflineClient::class, 'offline_client_id');
    }

    public function markedByUser()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}

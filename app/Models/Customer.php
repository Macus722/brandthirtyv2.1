<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'total_spent',
        'order_count',
        'is_vip',
        'last_order_at',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_email', 'email');
    }
}

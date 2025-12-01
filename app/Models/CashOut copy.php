<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'user_id',
        'payment_method',
        'charges',
        'order_date',
        'store_id',
        // Other relevant fields
    ];
}

<?php
// App\Models\Order.php
// App\Models\Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'store_id', 'order_date', 'payment_method', 'amount'
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the Store model
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Define the relationship with OrderItem
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Calculate total price for the order
    public function totalPrice()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }
}

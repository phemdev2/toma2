<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'user_id', // Add user_id
        'store_id', // Add shop_id
    ];

    /**
     * Get the product that owns the order item.
     */
   public function product()
{
    return $this->belongsTo(Product::class);
}

public function order()
{
    return $this->belongsTo(Order::class);
}

    
    /**
     * Get the variant associated with the order item.
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id'); // Adjust according to your model name
    }

    /**
     * Calculate the total price for this order item.
     */
    public function totalPrice()
    {
        return $this->quantity * $this->price; // Total price calculation
    }

    // Define any additional relationships or methods here
}
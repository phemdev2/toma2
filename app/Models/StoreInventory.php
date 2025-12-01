<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreInventory extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'product_id', 'quantity', 'batch_number', 'expiry_date','user_id'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

   public function product()
{
    return $this->belongsTo(Product::class, 'product_id');
}


    // Method to get total quantity of a product across all stores
    public static function totalQuantity($productId)
    {
        return self::where('product_id', $productId)->sum('quantity');
    }

    // Method to get quantities by store for a specific product
    public static function quantitiesByStore($productId)
    {
        return self::with('store')
            ->where('product_id', $productId)
            ->get();
    }
}

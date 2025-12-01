<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Specify which attributes should be mass assignable
    protected $fillable = [
        'name', 
        'barcode', 
        'cost', 
        'sale', 
        'description', 
        'allow_overselling',
        'expiry_date'
    ];
public function items()
{
    return $this->hasMany(OrderItem::class);
}

    // Define the relationship with ProductVariant
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Define the relationship with StoreInventory
   public function storeInventories()
{
    return $this->hasMany(StoreInventory::class, 'product_id');
}


    // Method to get total inventory quantity for a specific store
    public function getStoreQuantity($storeId)
    {
        return $this->storeInventories()
            ->where('store_id', $storeId)
            ->sum('quantity');
    }

    // Method to get all inventories for the product
    public function inventories()
    {
        return $this->hasMany(StoreInventory::class, 'product_id', 'id');
    }

    // Method to get total quantity across all stores
    public function getTotalQuantity()
    {
        return $this->storeInventories()->sum('quantity');
    }

    // Optional: Method to get the latest inventory update
    public function latestInventoryUpdate()
    {
        return $this->storeInventories()
            ->latest('updated_at')
            ->first();
    }
}

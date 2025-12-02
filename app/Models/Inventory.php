<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property int $store_id
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Product $product
 * @property-read Store $store
 */
class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'store_id',
        'quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'product_id' => 'integer',
        'store_id' => 'integer',
        'quantity' => 'integer', // Use 'decimal:2' if you sell weight/length (e.g. 1.5kg)
    ];

    /* --------------------------------------------------------------------------
     | Relationships
     | -------------------------------------------------------------------------- */

    /**
     * Get the product associated with the inventory.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the store where this inventory is located.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class); // Assuming you have a Store model
    }

    /* --------------------------------------------------------------------------
     | Scopes (Query Helpers)
     | -------------------------------------------------------------------------- */

    /**
     * Scope a query to only include items in stock.
     * Usage: Inventory::inStock()->get();
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope a query to include items that are out of stock.
     * Usage: Inventory::outOfStock()->get();
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Scope a query to find low stock items.
     * Usage: Inventory::lowStock(5)->get();
     */
    public function scopeLowStock($query, int $threshold = 10)
    {
        return $query->where('quantity', '<=', $threshold)
                     ->where('quantity', '>', 0);
    }

    /**
     * Scope a query to a specific store.
     * Usage: Inventory::forStore(1)->get();
     */
    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /* --------------------------------------------------------------------------
     | Accessors & Helpers
     | -------------------------------------------------------------------------- */

    /**
     * Check if the item is available for sale.
     * Usage: if ($inventory->is_available) { ... }
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->quantity > 0;
    }
}
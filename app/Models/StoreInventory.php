<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // Import for type hinting

class StoreInventory extends Model
{
    use HasFactory;

    protected $table = 'store_inventories'; // Explicit definition is good practice

    protected $fillable = [
        'store_id',
        'product_id',
        'quantity',
        'batch_number',
        'expiry_date',
        'user_id'
    ];

    /**
     * The attributes that should be cast.
     * 
     * @var array
     */
    protected $casts = [
        'expiry_date' => 'date',   // Carbon instance
        'quantity'    => 'integer', // or 'float' if you support decimals
        'store_id'    => 'integer',
        'product_id'  => 'integer',
    ];

    /* -------------------------------------------------------------------------- */
    /*                               Relationships                                */
    /* -------------------------------------------------------------------------- */

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
        return $this->belongsTo(Product::class);
    }

    /* -------------------------------------------------------------------------- */
    /*                                 Accessors                                  */
    /* -------------------------------------------------------------------------- */

    /**
     * Check if the specific inventory batch is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    /**
     * Filter by Store.
     */
    public function scopeByStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Filter by Product.
     */
    public function scopeByProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Filter products expiring within X days (excluding already expired).
     */
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '>', now()) // Not yet expired
                     ->where('expiry_date', '<=', now()->addDays($days))
                     ->orderBy('expiry_date', 'asc');
    }

    /**
     * Filter products that have already expired.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<=', now());
    }

    /**
     * Filter to return groups of products with low stock globally.
     */
    public function scopeLowStock(Builder $query, int $threshold = 10): Builder
    {
        return $query->selectRaw('product_id, SUM(quantity) as total_qty')
                     ->groupBy('product_id')
                     ->having('total_qty', '<=', $threshold);
    }

    /* -------------------------------------------------------------------------- */
    /*                              Static Helpers                                */
    /* -------------------------------------------------------------------------- */
    // Note: Kept these for specific aggregation tasks where scopes return Collections
    // but we need raw numbers or specific summary formats.

    /**
     * Get total quantity of a product across all stores.
     */
    public static function totalQuantity(int $productId): int // or float
    {
        return self::byProduct($productId)->sum('quantity');
    }

    /**
     * Get store-specific quantity for a product.
     */
    public static function storeProductQuantity(int $storeId, int $productId): int // or float
    {
        return self::byStore($storeId)
            ->byProduct($productId)
            ->sum('quantity');
    }

    /**
     * Get quantities grouped by store for a given product.
     * Useful for seeing distribution: Store A (50), Store B (20).
     */
    public static function quantitiesByStore(int $productId)
    {
        return self::selectRaw('store_id, SUM(quantity) as total_qty')
            ->where('product_id', $productId)
            ->groupBy('store_id')
            ->with('store')
            ->get();
    }

    /**
     * Get all products and their aggregated quantity for a specific store.
     */
    public static function productsByStore(int $storeId)
    {
        return self::selectRaw('product_id, SUM(quantity) as total_qty')
            ->where('store_id', $storeId)
            ->groupBy('product_id')
            ->with('product') // Be careful: accessing non-aggregated columns on product is fine, but on pivot is not.
            ->get();
    }
}
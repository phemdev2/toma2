<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'store_id',
        'quantity',
    ];

    // Define relationships if necessary
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
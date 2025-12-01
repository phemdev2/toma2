<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    // Define the table name if it's not the plural of the model name
    protected $table = 'product_variants';

    // Specify the fillable attributes
    protected $fillable = [
        'product_id',
        'unit_type',
        'unit_qty',
        'price',
    ];

    // Relationship to Product model
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Optionally, you can define mutators or accessors here
}

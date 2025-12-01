<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTotal extends Model
{
    use HasFactory;

    // Define your table if it's not the default 'user_totals'
    protected $table = 'user_totals';

    // Define fillable properties
    protected $fillable = [
        'user_id',
        'store_id',
        'total_orders',
        'totalCash',
        'totalPOS',
        'totalBank',
        // Add other fields as necessary
    ];

    // Define relationships if necessary
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    // Specify the table name if it's not the plural form of the model name
    protected $table = 'transactions';
    
    protected $fillable = ['type', 'total']; // Add 'total' here

    // Define the relationship with TransactionItem
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}

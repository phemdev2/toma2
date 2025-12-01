<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    // Specify the table name if it's not the plural form of the model name
    protected $table = 'transaction_items';
    
    // The attributes that are mass assignable
    protected $fillable = [
        'transaction_id', 
        'name', 
        'variant', 
        'unit_qty', 
        'price', 
        'quantity'
    ];

    // Define the relationship with Transaction
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}

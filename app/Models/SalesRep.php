<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesRep extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email']; // Add fields as needed

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_sales_rep');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    // Allowed fillable attributes for mass assignment
    protected $fillable = [
        'name',
        'company',
        'phone',
        'email',
        'website',
        'thank_you_message',
        'visit_again_message',
        'allow_overselling',
    ];

    // Relationship with StoreInventory
    public function storeInventories()
    {
        return $this->hasMany(StoreInventory::class);
    }

    // Relationship with User (Many-to-Many)
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Relationship with Order
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
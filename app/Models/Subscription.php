<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // assuming you have a user_id field
        'plan',
        'subscription_start_date',
        'subscription_expiry_date',
    ];

    // Relationship with User model (if applicable)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

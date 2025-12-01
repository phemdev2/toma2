<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRecord extends Model
{
    use HasFactory;

    // ✅ Only fillable attributes
    protected $fillable = [
        'user_id',
        'store_id',
        'date',
        'cash',
        'pos',
    ];

    /**
     * A daily record belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A daily record belongs to a store.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * A daily record has many expenses.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}

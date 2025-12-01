<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    /**
     * A company has many users.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * A company has one subscription.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'company_id',
        'type',
        'expires_at',
    ];

    protected $dates = [
        'expires_at',
    ];

    /**
     * A subscription belongs to a company.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at instanceof Carbon
            ? $this->expires_at->isPast()
            : Carbon::parse($this->expires_at)->isPast();
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }
}

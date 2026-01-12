<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 
        'store_id', 
        'user_id', 
        'quantity', 
        'quantity_before',
        'quantity_after',
        'unit_cost_at_time',
        'unit_sale_at_time',
        'event_type',
        'batch_number', 
        'expiry_date', 
        'action', // Legacy field, can correspond to event_type
        'notes'
    ];

    // Standardized Reason Codes for the UI
    const REASONS = [
        'restock'           => 'Supplier Delivery / Restock',
        'sale'              => 'Customer Sale',
        'return'            => 'Customer Return',
        'damage'            => 'Damaged in Store (Write-off)',
        'expired'           => 'Expired / Spoilage (Write-off)',
        'theft'             => 'Theft / Shrinkage (Write-off)',
        'internal_use'      => 'Internal Store Use (Expense)',
        'audit_adjustment'  => 'Audit Correction (Blind Count)'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function store() {
        return $this->belongsTo(Store::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
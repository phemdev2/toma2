<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyExpense extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'date',
        'description',
        'amount',
    ];
}

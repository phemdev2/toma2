<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'store_id',
        'plan',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin'; // Adjust based on your role setup
    }
public function dailyRecords()
{
    return $this->hasMany(DailyRecord::class);
}
    public function getCashBalance(): float
    {
        // Get total cash balance from orders
        return $this->hasMany(Order::class)
            ->where('payment_method', 'cash')
            ->sum('amount');
    }

    public function getCashBalanceForToday(): float
    {
        // Get today's cash balance
        return Order::where('user_id', $this->id)
            ->whereIn('payment_method', ['cash', 'withdraw', 'pos', 'bank'])
            ->whereDate('order_date', today())
            ->sum('amount');
    }

    public function getAvailableCash(): float
    {
        return Order::where('user_id', $this->id)
            ->where('payment_method', 'cash')
            ->whereDate('order_date', today())
            ->sum('amount');
    }

    public function getPosBalance(): float
    {
        // Return POS balance
        return $this->pos_balance ?? 0; // Default to 0 if pos_balance is not set
    }

    public function getPosBalanceForToday(): float
    {
        // Get today's POS balance
        return Order::where('user_id', $this->id)
            ->where('payment_method', 'pos')
            ->whereDate('order_date', today())
            ->sum('amount');
    }

    public function getMaxWithdrawal(): float
    {
        // Calculate the maximum withdrawal limit
        $availableCash = $this->getAvailableCash();
        $totalCashOutsToday = CashOut::where('user_id', $this->id)
            ->whereDate('created_at', today())
            ->sum('amount');

        return $availableCash - $totalCashOutsToday;
    }

    public function getTotalWithdrawnToday(): float
    {
        // Calculate total withdrawn today
        return CashOut::where('user_id', $this->id)
            ->whereDate('created_at', today())
            ->sum('amount');
    }
    
    public function getTotalEarningsForToday(): float
    {
        // Calculate total earnings for today
        return Order::where('user_id', $this->id)
            ->whereDate('order_date', today())
            ->sum('amount'); // Adjust as needed
    }
    
    public function getPendingWithdrawals(): int
    {
        // Calculate pending withdrawals count or total amount
        return CashOut::where('user_id', $this->id)
            ->where('status', 'pending') // Adjust status as needed
            ->count(); // or sum('amount') if you need total amount
    }
    
    public function getLastCashOut(): float
    {
        // Get the last cash out amount
        return CashOut::where('user_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->first()?->amount ?? 0; // Return last cash out amount or 0 if none
    }

    public function decrementCashBalance($amount)
    {
        // Logically, we don’t directly update a cash_balance field, but you could handle transactions here
        // Just a placeholder to show intent
    }
    
    public function incrementPosBalance($amount)
    {
        // Similar to decrementCashBalance
    }
    public function subscribeUser(Request $request)
{
    $user = Auth::user();
    
    // Assuming you have logic to determine the plan type
    $planType = $request->input('plan_type'); // e.g., 'trial', 'basic', 'premium'
    
    // Set start date to now when they subscribe
    $user->start_date = Carbon::now(); 
    $user->plan_type = $planType; // Set the plan type
    $user->save();

    return redirect()->route('subscription.success');
}
}

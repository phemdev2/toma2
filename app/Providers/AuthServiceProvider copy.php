<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Define Gates
        Gate::define('view-products', function ($user) {
            return $user->hasRole('admin') || $user->hasRole('editor'); // Example check
        });

        Gate::define('add-inventory', function ($user) {
            return $user->hasRole('admin'); // Only admins can add inventory
        });

        Gate::define('create-product', function ($user) {
            return $user->hasRole('admin') || $user->hasRole('manager'); // Example check
        });

        Gate::define('view-product-cards', function ($user) {
            return $user->hasRole('admin') || $user->hasRole('viewer'); // Example check
        });

        Gate::define('view-transactions', function ($user) {
            return $user->hasRole('admin'); // Only admins can view transactions
        });

        Gate::define('view-store-inventories', function ($user) {
            return $user->hasRole('admin') || $user->hasRole('manager'); // Example check
        });

        Gate::define('update-inventories', function ($user) {
            return $user->hasRole('admin'); // Only admins can update inventories
        });
    }
}
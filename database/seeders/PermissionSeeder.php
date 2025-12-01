<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Add the view-users permission
        Permission::create(['name' => 'view-users']);
        
        // Other permissions (if needed)
        Permission::create(['name' => 'add-inventory']);
        Permission::create(['name' => 'create-product']);
        Permission::create(['name' => 'view-products']);
        Permission::create(['name' => 'view-product-cards']);
        Permission::create(['name' => 'view-transactions']);
        Permission::create(['name' => 'view-store-inventories']);
        Permission::create(['name' => 'update-inventories']);
    }
}
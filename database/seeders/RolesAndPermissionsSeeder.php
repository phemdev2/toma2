<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'editor']);
        Role::create(['name' => 'viewer']);

        // Create permissions
        Permission::create(['name' => 'view-users']);
        Permission::create(['name' => 'add-inventory']);
        Permission::create(['name' => 'create-product']);
        Permission::create(['name' => 'view-products']);
        Permission::create(['name' => 'view-product-cards']);
        Permission::create(['name' => 'view-transactions']);
        Permission::create(['name' => 'view-store-inventories']);
        Permission::create(['name' => 'update-inventories']);

        // Optionally assign permissions to roles
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo([
            'view-users',
            'add-inventory',
            'create-product',
            'view-products',
            'view-product-cards',
            'view-transactions',
            'view-store-inventories',
            'update-inventories',
        ]);

        // You can assign different permissions to other roles as needed
        $editorRole = Role::findByName('editor');
        $editorRole->givePermissionTo(['add-inventory', 'create-product', 'view-products']);

        $viewerRole = Role::findByName('viewer');
        $viewerRole->givePermissionTo(['view-products', 'view-product-cards']);
    }
}
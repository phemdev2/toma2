<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Call the Roles and Permissions seeder
        $this->call(RolesAndPermissionsSeeder::class);
        
        // You can add other seeders here if needed
        // $this->call(OtherSeeder::class);
    }
}
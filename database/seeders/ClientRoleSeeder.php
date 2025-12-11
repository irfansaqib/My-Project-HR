<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ClientRoleSeeder extends Seeder
{
    public function run()
    {
        // Check if role exists, if not, create it
        if (!Role::where('name', 'Client')->exists()) {
            Role::create(['name' => 'Client', 'guard_name' => 'web']);
        }
    }
}
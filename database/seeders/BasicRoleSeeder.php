<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class BasicRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'staff',
        ]);
        Role::create([
            'name' => 'admin',
        ]);
        Role::create([
            'name' => 'director',
        ]);
    }
}

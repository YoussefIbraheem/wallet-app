<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['admin', 'user', 'moderator'];

        foreach ($roles as $role) {
            $role_exists = Role::query()->where('name', $role)->exists();
            if ($role_exists) {
                continue;
            }
            Role::create(['name' => $role]);
        }
    }
}

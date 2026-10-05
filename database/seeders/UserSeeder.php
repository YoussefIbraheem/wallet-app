<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(10)->create()->each(fn ($user) => $user->assignRole(Role::MODERATOR));
        User::factory(20)->create()->each(fn ($user) => $user->assignRole(Role::USER));
    }
}

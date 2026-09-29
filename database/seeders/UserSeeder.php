<?php

namespace Database\Seeders;

use App\Enums\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Contracts\Role as ContractsRole;
use Spatie\Permission\Models\Role as ModelsRole;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::factory(10)->create()->each(fn($user)=>$user->assignRole(Role::MODERATOR));
        \App\Models\User::factory(20)->create()->each(fn($user)=>$user->assignRole(Role::USER));
    }
}

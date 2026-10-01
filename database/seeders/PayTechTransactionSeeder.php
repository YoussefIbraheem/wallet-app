<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Database\Factories\PayTechTransactionFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PayTechTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaction::factory()->bank("paytech")->count(10)->create();
    }
}

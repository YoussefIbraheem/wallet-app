<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "transaction" => "",
        ];
    }

    public function bank(string $bankName): static
    {
        $fakeDate = fake()->date("Ymd");
        $fakeAmount = fake()->randomFloat(2, 0, 999);
        $fakeReference = $fakeDate . fake()->randomNumber(7, true);

        $format = match ($bankName) {
            "paytech" => $fakeDate .
                $fakeAmount .
                "#" .
                $fakeReference .
                "#" .
                "note/" .
                fake()->word() .
                "/" .
                strtoupper(fake()->bothify("?###??##")),
            "acme" => $fakeAmount . "//" . $fakeReference . "//" . $fakeDate,
        };

        return $this->state(
            fn($attributes) => [
                "transaction" => $format,
            ],
        );
    }
}

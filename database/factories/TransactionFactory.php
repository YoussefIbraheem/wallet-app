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
        return [];
    }

    public function bank(string $bankName): static
    {
        $webhookId = fake()->uuid();
        return $this->state(
            function ($attributes) use ($bankName, $webhookId) {
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
                        "internal_reference" .
                        "/" .
                        strtoupper(fake()->bothify("?###??##")),
                    "acme" => $fakeAmount . "//" . $fakeReference . "//" . $fakeDate,
                };

                return [
                    "webhook_id" => $webhookId,
                    "raw_line" => $format,
                    "bank_name" => $bankName,
                    "raw_line_hashed" => hash("sha256", $format),
                ];
            }
        );
    }
}

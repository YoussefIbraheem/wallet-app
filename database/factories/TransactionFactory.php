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


        return $this->state(
            function ($attributes) use ($bankName) {

                $webhookId = fake()->uuid();
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
                    "referenece" => $fakeReference,
                    "raw_line" => $format,
                    "raw_line_hashed" => hash("sha256", $format),
                ];
            }
        );
    }
}

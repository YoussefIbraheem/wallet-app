<?php

namespace Database\Factories;

use Faker\Factory as FakerFactory;
use Faker\Generator;

class PaymentRequestFactory
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = FakerFactory::create();
    }

    public function make(array $overrides = []): array
    {
        return array_merge([
            'reference' => $this->faker->uuid(),
            'date' => $this->faker->dateTime()->format('Y-m-d H:i:sP'),
            'amount' => (string) $this->faker->randomFloat(2, 0, 999),
            'currency' => $this->faker->currencyCode(),
            'senderAccountNumber' => $this->faker->iban(),
            'bankCode' => $this->faker->swiftBicNumber(),
            'receiverAccountNumber' => $this->faker->iban(),
            'beneficiaryName' => $this->faker->name(),
            'notes' => $this->faker->words(2),
            'paymentType' => $this->faker->numerify('##'),
            'chargeDetails' => $this->faker->randomElement(['RB', 'SHA']),
        ], $overrides);
    }
}

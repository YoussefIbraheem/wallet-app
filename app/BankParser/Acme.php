<?php

namespace App\BankParser;

use DateTime;
use Override;

class Acme implements BankParser
{
    #[Override]
    public function parse(string $rawLine): mixed
    {
        [$amount, $reference, $date] = explode("//", $rawLine, 3);

        return [
            "amount" => $this->extractAmount($amount),
            "reference" => $this->extractReference($reference),
            "date" => $this->extractDate($date),
        ];
    }

    #[Override]
    public function name(): string
    {
        return "acme";
    }

    private function extractAmount(string $amount): float
    {
        return (float) $amount;
    }

    private function extractDate(string $date): string
    {
        return DateTime::createFromFormat("Ymd", $date)->format("Y-m-d");;
    }

    private function extractReference(string $reference): string
    {
        return (string) $reference;
    }
}

<?php

namespace App\BankParser;

use DateTime;
use Override;

class Acme implements BankParser, HasMatchingFormat
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

    #[Override]
    public function isMatchingFormat(string $rawLine): bool
    {
        return preg_match("/^\d+,\d{2}\/\/[^\/]+\/\/\d{8}$/", $rawLine);
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

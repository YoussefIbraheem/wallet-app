<?php

namespace App\BankParser;

use DateTime;
use Override;

class PayTech implements BankParser
{
    public function name(): string
    {
        return "paytech";
    }

    public function parse(string $rawLine): array
    {
        [$dateAndAmount, $reference, $notes] = explode("#", $rawLine, 3);

        return [
            "date" => $this->extractDate($dateAndAmount),
            "amount" => $this->extractAmount($dateAndAmount),
            "reference" => $reference,
            "notes" => $this->extractNotes($notes),
        ];
    }

    private function extractDate(string $dateAndAmount): string
    {
        $date = substr($dateAndAmount, 0, 8);

        return DateTime::createFromFormat("Ymd", $date)->format("Y-m-d");
    }

    private function extractAmount(string $dateAndAmount): float
    {
        $strAmount = str_replace(",", ".", substr($dateAndAmount, 8));
        return floatval($strAmount);
    }

    private function extractNotes(string $notes): array
    {
        $notes = str_replace("/", "=", $notes);

        parse_str($notes, $parsedNotes);

        return $parsedNotes;
    }
}

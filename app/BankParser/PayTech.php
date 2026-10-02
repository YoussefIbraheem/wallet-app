<?php

namespace App\BankParser;

use DateTime;

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
        return (float) substr($dateAndAmount, 8);
    }

    private function extractNotes(string $notes): array
    {
        $notes = str_replace("/", "=", $notes);

        parse_str($notes, $parsedNotes);

        return $parsedNotes;
    }

    /**
     * Extract reference depnding on given bank name
     *
     * @param string $transaction
     * @return string
     *
     */
    private function extractReference(string $transaction): string
    {
        $delimiter = "#";

        $refStart = strpos($transaction, $delimiter);
        if ($refStart === false) {
            return "";
        }

        $refStart += strlen($delimiter);

        $refEnd = strpos($transaction, $delimiter, $refStart);
        if ($refEnd === false) {
            return "";
        }

        return substr($transaction, $refStart, $refEnd - $refStart);
    }
}

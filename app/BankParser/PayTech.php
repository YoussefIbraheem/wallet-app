<?php

namespace App\BankParser;

use App\Models\ParsedTransaction;
use DateTime;
use Override;

class PayTech implements BankParser, HasMetadata, HasMatchingFormat
{

    public function name(): string
    {
        return "paytech";
    }

    #[Override]
    public function isMatchingFormat(string $rawLine): bool
    {
        return (bool) preg_match("/^\d{8}\d+(?:\.\d+)?#[^#]+#[^\/#]+\/[^\/#]+(?:\/[^\/#]+\/[^\/#]+)*$/", $rawLine);
    }

    public function parse(string $rawLine): array
    {
        [$dateAndAmount, $reference, $notes] = explode("#", $rawLine, 3);

        return [
            "date" => $this->extractDate($dateAndAmount),
            "amount" => $this->extractAmount($dateAndAmount),
            "reference" => $reference,
            "metadata" => $this->extractNotes($notes),
        ];
    }

    public function parseMetadata($transaction_id,array $parsedData): array
    {
        return array_map(fn($key, $value) => [
            "key" => $key,
            "value" => $value,
            "transaction_id" => $transaction_id,
        ], array_keys($parsedData["metadata"]), array_values($parsedData["metadata"]));
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
        $notesArr = explode("/", $notes);
        $parsedNotes = [];
        for ($i = 0; $i < count($notesArr); $i++) {
            if (!isset($notesArr[$i + 1])) {
                break;
            }
            if ($i % 2 == 0) {
                $parsedNotes[$notesArr[$i]] = $notesArr[$i + 1];
            }
        }

        return $parsedNotes;
    }
}

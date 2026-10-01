<?php

namespace App\Enums;

enum Bank: string
{
    case PAYTECH = "paytech";
    case ACME = "acme";

    public function attributes(): array
    {
        return match ($this) {
            self::ACME => ["label" => "Acme", "ref_symbol" => "//"],
            self::PAYTECH => ["label" => "Paytech", "ref_symbol" => "#"],
        };
    }

    public static function fromValue(string $value): self
    {
        return match ($value) {
            "paytech" => self::PAYTECH,
            "acme" => self::ACME,
            default => throw new \InvalidArgumentException(
                "Invalid value: {$value}",
            ),
        };
    }
}

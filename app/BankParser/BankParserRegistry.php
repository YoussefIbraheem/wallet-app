<?php

namespace App\BankParser;

class BankParserRegistry
{
    private array $parsers = [];

    public function register(BankParser $parser): void
    {
        $this->parsers[$parser->name()] = $parser;
    }

    public function get(string $bankName): BankParser
    {
        if (!isset($this->parsers[$bankName])) {
            throw new \InvalidArgumentException(
                "Bank parser not found for bank: {$bankName}",
            );
        }

        return $this->parsers[$bankName];
    }
}

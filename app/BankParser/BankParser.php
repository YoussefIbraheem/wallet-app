<?php

namespace App\BankParser;

interface BankParser
{
    public function name(): string;

    public function parse(string $body): mixed;
}

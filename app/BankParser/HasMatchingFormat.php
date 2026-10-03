<?php

namespace App\BankParser;

interface HasMatchingFormat
{
    public function isMatchingFormat(string $rawLine): bool;
}

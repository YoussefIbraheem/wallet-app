<?php

namespace App\BankParser;

use App\Models\ParsedTransaction;

interface HasMetadata
{
    public function storeMetadata(ParsedTransaction $transaction, array $parsedData): void;
}

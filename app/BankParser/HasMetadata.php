<?php

namespace App\BankParser;

use App\Models\ParsedTransaction;

interface HasMetadata
{
    public function parseMetadata($transaction_id, array $parsedData): array;
}

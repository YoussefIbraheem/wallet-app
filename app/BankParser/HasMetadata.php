<?php

namespace App\BankParser;

interface HasMetadata
{
    public function parseMetadata($transaction_id, array $parsedData): array;
}

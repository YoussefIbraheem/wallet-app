<?php

namespace App\Filament\Resources\ParsedTransactions\Pages;

use App\Filament\Resources\ParsedTransactions\ParsedTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateParsedTransaction extends CreateRecord
{
    protected static string $resource = ParsedTransactionResource::class;
}

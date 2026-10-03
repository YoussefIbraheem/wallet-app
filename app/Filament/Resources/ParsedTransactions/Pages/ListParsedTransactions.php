<?php

namespace App\Filament\Resources\ParsedTransactions\Pages;

use App\Filament\Resources\ParsedTransactions\ParsedTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParsedTransactions extends ListRecords
{
    protected static string $resource = ParsedTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

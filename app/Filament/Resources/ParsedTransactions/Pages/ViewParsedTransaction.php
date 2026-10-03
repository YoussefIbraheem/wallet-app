<?php

namespace App\Filament\Resources\ParsedTransactions\Pages;

use App\Filament\Resources\ParsedTransactions\ParsedTransactionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewParsedTransaction extends ViewRecord
{
    protected static string $resource = ParsedTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

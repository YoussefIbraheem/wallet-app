<?php

namespace App\Filament\Resources\ParsedTransactions\Pages;

use App\Filament\Resources\ParsedTransactions\ParsedTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditParsedTransaction extends EditRecord
{
    protected static string $resource = ParsedTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

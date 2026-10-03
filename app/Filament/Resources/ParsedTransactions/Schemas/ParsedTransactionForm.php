<?php

namespace App\Filament\Resources\ParsedTransactions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ParsedTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('transaction_id')
                    ->required()
                    ->numeric(),
                TextInput::make('reference')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('date')
                    ->required(),
            ]);
    }
}

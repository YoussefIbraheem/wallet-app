<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('webhook_id')
                    ->required(),
                TextInput::make('bank_name')
                    ->required(),
                Textarea::make('raw_line')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('raw_line_hashed')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
            ]);
    }
}

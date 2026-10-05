<?php

namespace App\Filament\Resources\ParsedTransactions\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ParsedTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('transaction_id')
                            ->numeric(),
                        TextEntry::make('reference'),
                        TextEntry::make('amount')
                            ->numeric(),
                        TextEntry::make('date')
                            ->date(),
                    ])
                    ->columns()
                    ->columnSpanFull(),
                KeyValueEntry::make('transactionMetadata')
                    ->label('Metadata')
                    ->columns()
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record->transactionMetadata->isNotEmpty())
                    ->getStateUsing(function ($record) {
                        return $record->transactionMetadata->pluck('value', 'key')->toArray();
                    }),
                Section::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->date(),
                        TextEntry::make('updated_at')
                            ->date(),
                    ])
                    ->columns()
                    ->columnSpanFull(),

            ]);
    }
}

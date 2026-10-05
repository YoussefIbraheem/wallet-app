<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->schema([
                        TextEntry::make('webhook_id'),
                        TextEntry::make('raw_line'),
                        TextEntry::make('raw_line_hashed'),
                    ])
                    ->columnspanFull()
                    ->columns(1),

                TextEntry::make('bank_name')
                    ->formatStateUsing(fn (string $state) => strtoupper($state)),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

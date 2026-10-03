<?php

namespace App\Filament\Resources\ParsedTransactions;

use App\Filament\Resources\ParsedTransactions\Pages\CreateParsedTransaction;
use App\Filament\Resources\ParsedTransactions\Pages\EditParsedTransaction;
use App\Filament\Resources\ParsedTransactions\Pages\ListParsedTransactions;
use App\Filament\Resources\ParsedTransactions\Pages\ViewParsedTransaction;
use App\Filament\Resources\ParsedTransactions\Schemas\ParsedTransactionForm;
use App\Filament\Resources\ParsedTransactions\Schemas\ParsedTransactionInfolist;
use App\Filament\Resources\ParsedTransactions\Tables\ParsedTransactionsTable;
use App\Models\ParsedTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ParsedTransactionResource extends Resource
{
    protected static ?string $model = ParsedTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ParsedTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ParsedTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParsedTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParsedTransactions::route('/'),
            'create' => CreateParsedTransaction::route('/create'),
            'view' => ViewParsedTransaction::route('/{record}'),
            'edit' => EditParsedTransaction::route('/{record}/edit'),
        ];
    }
}

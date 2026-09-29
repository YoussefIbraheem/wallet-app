<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Role;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Builder\Block;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\Users\Tables\Model;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\SelectAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\SelectColumn;

use function Illuminate\Support\now;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")->searchable(),
                TextColumn::make("email")
                    ->label("Email address")
                    ->icon(Heroicon::Envelope)
                    ->searchable(),
                TextColumn::make("roles.name")
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            Role::ADMIN->value => "warning",
                            Role::MODERATOR->value => "success",
                            default => "gray",
                        },
                    )
                    ->label("Role"),
                IconColumn::make("email_verified_at")
                    ->label("Verified?")
                    ->sortable()
                    ->state(function ($record): bool {
                        return $record->email_verified_at != null;
                    })
                    ->color(
                        fn(string $state): string => $state
                            ? "success"
                            : "danger",
                    )
                    ->icon(
                        fn(string $state): Heroicon => $state
                            ? Heroicon::CheckCircle
                            : Heroicon::XCircle,
                    ),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->disabled(fn($record) => $record->id != auth()->user()->id)
                    ->visible(fn($record) => $record->id == auth()->user()->id),
                ViewAction::make(),
                ActionGroup::make([
                    Action::make("update_role")
                        ->visible(fn($record)=> auth()->user()->isAdmin() && $record->id != auth()->user()->id)
                        ->schema([
                            Select::make("new_role")->options(Role::class),
                        ])
                        ->action(
                            fn($record, $data) => $record->syncRoles(
                                $data["new_role"],
                            ),
                        ),
                    Action::make("verify_user")
                        ->visible(
                            fn($record) => $record->email_verified_at == null,
                        )
                        ->action(fn($record) => $record->markEmailAsVerified()),
                ]),
            ])
            ->recordUrl(false)
            ->toolbarActions([
                // BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}

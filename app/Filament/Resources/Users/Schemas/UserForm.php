<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("first_name"),
            TextInput::make("last_name"),
            TextInput::make("email")
                ->label("Email address")
                ->email()
                ->required(),
            TextInput::make("password")
                ->visibleOn("create")
                ->password()
                ->confirmed()
                ->revealable()
                ->required(),
                TextInput::make("password_confirmation")
                    ->password()
                    ->revealable()
                    ->required(),
            Toggle::make("update_password")
                ->inlineLabel()
                ->visibleOn("edit")
                ->live()
                ->label("Update Password"),

            Section::make("Update Password")
                ->inlineLabel()
                ->columns(1)
                ->visible(fn(Get $get) => $get("update_password"))
                ->columnSpanFull()
                ->schema([
                    TextInput::make("password")
                        ->password()
                        ->confirmed()
                        ->revealable()
                        ->required(),
                    TextInput::make("password_confirmation")
                        ->password()
                        ->revealable()
                        ->required(),
                ]),
        ]);
    }
}

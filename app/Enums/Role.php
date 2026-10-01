<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    case ADMIN = "admin";
    case MODERATOR = "moderator";
    case USER = "user";

    public function getLabel(): string
    {
        return $this->name;
    }
}

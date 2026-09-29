<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Role: string implements HasLabel
{
    case ADMIN = "admin";
    case MODERATOR = "moderator";
    case USER = "user";

    public function getLabel(): string | Htmlable | null
    {
        return $this->name;
    }
}

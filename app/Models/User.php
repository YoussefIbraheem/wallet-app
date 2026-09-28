<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method bool isAdmin()
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(["first_name", "last_name", "name", "email", "password"])]
#[
    Hidden([
        "password",
        "two_factor_secret",
        "two_factor_recovery_codes",
        "remember_token",
    ]),
]
class User extends Authenticatable implements MustVerifyEmail, HasName
{
    use HasFactory;
    use Notifiable;
    use HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn() => !$this->first_name && !$this->last_name
                ? $this->email
                : "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1) . Str::substr($initials, -1)
            : $initials;
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    /**
     * Check if the user is admin
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(RoleEnum::ADMIN);
    }

    /**
     * Check if the user is a regular user
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->hasRole(RoleEnum::USER);
    }

    /**
     * Check if the user is a moderator
     * @return bool
     */
    public function isModerator(): bool
    {
        return $this->hasRole(RoleEnum::MODERATOR);
    }
}

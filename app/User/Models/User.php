<?php

namespace App\User\Models;

use App\User\Concerns\HasDisplayNames;
use App\User\Concerns\HasNotifications;
use App\User\Concerns\HasOnlineStatus;
use App\User\Concerns\HasPermissions;
use App\User\Concerns\HasPreferences;
use App\User\Concerns\HasProfilePhoto;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\RoutesNotifications;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['username', 'nickname', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
#[UseFactory(UserFactory::class)]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    use HasDisplayNames;
    use HasFactory;
    use HasNotifications;
    use HasOnlineStatus;
    use HasPermissions;
    use HasPreferences;
    use HasProfilePhoto;
    use HasRoles;
    use PasskeyAuthenticatable;
    use RoutesNotifications;
    use TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

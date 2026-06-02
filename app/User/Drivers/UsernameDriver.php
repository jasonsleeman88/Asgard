<?php

namespace App\User\Drivers;

use App\User\Contracts\DisplayNameDriverInterface;
use App\User\Models\User;

class UsernameDriver implements DisplayNameDriverInterface
{
    public function displayName(User $user): string
    {
        return $user->username;
    }
}

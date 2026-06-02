<?php

namespace App\User\Contracts;

use App\User\Models\User;

interface DisplayNameDriverInterface
{
    public function displayName(User $user): string;
}

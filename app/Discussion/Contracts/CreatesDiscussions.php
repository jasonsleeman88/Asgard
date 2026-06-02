<?php

namespace App\Discussion\Contracts;

use App\Discussion\Models\Discussion;
use App\User\Models\User;
use Illuminate\Support\Fluent;

interface CreatesDiscussions
{
    public function handle(User $actor, Fluent $data, string $ipAddress): Discussion;
}

<?php

namespace App\Discussion\Contracts;

use App\Discussion\Models\Discussion;
use App\User\Models\User;
use Illuminate\Support\Fluent;

interface UpdatesDiscussions
{
    public function handle(Discussion $discussion, User $actor, Fluent $data): Discussion;
}

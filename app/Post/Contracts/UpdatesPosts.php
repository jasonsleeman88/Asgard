<?php

namespace App\Post\Contracts;

use App\Post\Models\Post;
use App\User\Models\User;
use Illuminate\Support\Fluent;

interface UpdatesPosts
{
    public function handle(Post $post, User $actor, Fluent $data): Post;
}

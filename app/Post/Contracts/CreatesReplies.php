<?php

namespace App\Post\Contracts;

use App\Discussion\Models\Discussion;
use App\Post\Models\CommentPost;
use App\User\Models\User;
use Illuminate\Support\Fluent;

interface CreatesReplies
{
    public function handle(Discussion $discussion, User $actor, Fluent $data, ?string $ipAddress = null, bool $isFirstPost = false): CommentPost;
}

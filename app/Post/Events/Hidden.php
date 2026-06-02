<?php

namespace App\Post\Events;

use App\Post\Models\Post;
use App\User\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Hidden
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Post $post, public ?User $actor = null) {}
}

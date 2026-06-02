<?php

namespace App\Post\Events;

use App\Post\Models\Post;
use App\User\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Fluent;

class Saving
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Post $post, public User $actor, public Fluent $data) {}
}

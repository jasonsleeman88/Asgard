<?php

namespace App\Discussion\Events;

use App\Discussion\Models\Discussion;
use App\User\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Deleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Discussion $discussion, public ?User $actor = null) {}
}

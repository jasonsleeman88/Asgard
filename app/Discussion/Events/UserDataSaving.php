<?php

namespace App\Discussion\Events;

use App\Discussion\Models\UserState;
use App\User\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserDataSaving
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public UserState $state, public ?User $actor = null) {}
}

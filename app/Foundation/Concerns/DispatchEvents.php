<?php

namespace App\Foundation\Concerns;

use App\User\Models\User;

trait DispatchEvents
{
    public function dispatchEventsFor($entity, ?User $actor = null): void
    {
        foreach ($entity->releaseEvents() as $event) {
            $event->actor = $actor;

            event($event);
        }
    }
}

<?php

namespace App\Foundation\Concerns;

trait EventGenerator
{
    protected array $pendingEvents = [];

    public function raise($event): void
    {
        $this->pendingEvents[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->pendingEvents;

        $this->pendingEvents = [];

        return $events;
    }
}

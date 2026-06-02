<?php

namespace App\Notification\Jobs;

use App\Notification\Contracts\BlueprintInterface;
use App\Notification\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly BlueprintInterface $blueprint,
        private readonly array $recipients = []
    ) {}

    public function handle(): void
    {
        Notification::notify($this->recipients, $this->blueprint);
    }
}

<?php

namespace App\Notification\Jobs;

use App\Notification\Contracts\BlueprintInterface;
use App\Notification\Contracts\MailableInterface;
use App\Notification\Support\NotificationMailer;
use App\User\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmailNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly BlueprintInterface|MailableInterface $blueprint,
        private readonly User $recipient
    ) {}

    public function handle(NotificationMailer $mailer): void
    {
        $mailer->send($this->blueprint, $this->recipient);
    }
}

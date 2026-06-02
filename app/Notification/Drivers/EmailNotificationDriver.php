<?php

namespace App\Notification\Drivers;

use App\Notification\Contracts\BlueprintInterface;
use App\Notification\Contracts\MailableInterface;
use App\Notification\Contracts\NotificationDriverInterface;
use App\Notification\Jobs\SendEmailNotificationJob;
use App\User\Models\User;
use ReflectionClass;
use ReflectionException;

class EmailNotificationDriver implements NotificationDriverInterface
{
    public function send(BlueprintInterface $blueprint, array $users): void
    {
        if ($blueprint instanceof MailableInterface) {
            $this->mailNotifications($blueprint, $users);
        }
    }

    /**
     * @throws ReflectionException
     */
    public function registerType(BlueprintInterface|string $blueprintClass, array $driversEnabledByDefault): void
    {
        if ((new ReflectionClass($blueprintClass))->implementsInterface(MailableInterface::class)) {
            User::registerPreference(
                User::getNotificationPreferenceKey($blueprintClass::getType(), 'email'),
                'boolval',
                in_array('email', $driversEnabledByDefault)
            );
        }
    }

    protected function mailNotifications(BlueprintInterface|MailableInterface $blueprint, array $recipients): void
    {
        foreach ($recipients as $user) {
            if ($user->shouldEmail($blueprint::getType())) {
                SendEmailNotificationJob::dispatch($blueprint, $user);
            }
        }
    }

    public function getIcon(): string
    {
        return 'envelope';
    }

    public function getLabel(): string
    {
        return __('Email');
    }
}

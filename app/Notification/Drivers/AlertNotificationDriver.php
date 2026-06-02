<?php

namespace App\Notification\Drivers;

use App\Notification\Contracts\BlueprintInterface;
use App\Notification\Contracts\NotificationDriverInterface;
use App\Notification\Jobs\SendNotificationsJob;
use App\User\Models\User;

class AlertNotificationDriver implements NotificationDriverInterface
{
    public function send(BlueprintInterface $blueprint, array $users): void
    {
        if (count($users)) {
            SendNotificationsJob::dispatch($blueprint, $users);
        }
    }

    public function registerType(BlueprintInterface|string $blueprintClass, array $driversEnabledByDefault): void
    {
        User::registerPreference(
            User::getNotificationPreferenceKey($blueprintClass::getType(), 'alert'),
            'boolval',
            in_array('alert', $driversEnabledByDefault)
        );
    }

    public function getIcon(): string
    {
        return 'bell-alert';
    }

    public function getLabel(): string
    {
        return __('Alert');
    }
}

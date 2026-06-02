<?php

namespace App\Notification\Support;

use App\Notification\Contracts\BlueprintInterface;
use App\Notification\Contracts\NotificationDriverInterface;
use App\Notification\Models\Notification;
use App\User\Models\User;

class NotificationSyncer
{
    protected static bool $onePerUser = false;

    protected static array $sentTo = [];

    protected static array $notificationDrivers = [];

    protected static array $beforeSendingCallbacks = [];

    public function sync(BlueprintInterface $blueprint, array $users): void
    {
        // Find all existing notification records in the database matching this
        // blueprint. We will begin by assuming that they all need to be
        // deleted to match the provided list of users.
        $toDelete = Notification::matchingBlueprint($blueprint)->get();
        $toUndelete = [];
        $newRecipients = [];

        // For each of the provided users, check to see if they already have
        // a notification record in the database. If they do, we will make sure
        // it isn't marked as deleted. If they don't, we will want to create a
        // new record for them.
        foreach ($users as $user) {
            if (! ($user instanceof User)) {
                continue;
            }

            $existing = $toDelete->first(function ($notification) use ($user) {
                return $notification->user_id === $user->id;
            });

            if ($existing) {
                $toUndelete[] = $existing->id;
                $toDelete->forget($toDelete->search($existing));
            } elseif (! static::$onePerUser || ! in_array($user->id, static::$sentTo)) {
                $newRecipients[] = $user;
                static::$sentTo[] = $user->id;
            }
        }

        // Delete all of the remaining notification records which weren't
        // removed from this collection by the above loop. Un-delete the
        // existing records that we want to keep.
        if (count($toDelete)) {
            $this->setDeleted($toDelete->pluck('id')->all(), true);
        }

        if (count($toUndelete)) {
            $this->setDeleted($toUndelete, false);
        }

        foreach (static::$beforeSendingCallbacks as $callback) {
            $newRecipients = $callback($blueprint, $newRecipients);
        }

        // Create a notification record, and send an email, for all users
        // receiving this notification for the first time (we know because they
        // didn't have a record in the database). As both operations can be
        // intensive on resources (database and mail server), we queue them.
        foreach (static::getNotificationDrivers() as $driverName => $driver) {
            $driver->send($blueprint, $newRecipients);
        }
    }

    public function delete(BlueprintInterface $blueprint): void
    {
        Notification::matchingBlueprint($blueprint)->update(['is_deleted' => true]);
    }

    public function restore(BlueprintInterface $blueprint): void
    {
        Notification::matchingBlueprint($blueprint)->update(['is_deleted' => false]);
    }

    public function onePerUser(callable $callback): void
    {
        static::$sentTo = [];
        static::$onePerUser = true;

        $callback();

        static::$onePerUser = false;
    }

    protected function setDeleted(array $ids, $isDeleted)
    {
        Notification::whereIn('id', $ids)->update(['is_deleted' => $isDeleted]);
    }

    public static function addNotificationDriver(string $driverName, NotificationDriverInterface $driver): void
    {
        static::$notificationDrivers[$driverName] = $driver;
    }

    public static function getNotificationDrivers(): array
    {
        return static::$notificationDrivers;
    }

    public static function beforeSending($callback): void
    {
        static::$beforeSendingCallbacks[] = $callback;
    }
}

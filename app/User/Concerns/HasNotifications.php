<?php

declare(strict_types=1);

namespace App\User\Concerns;

use App\Notification\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasNotifications
{
    public static function getNotificationPreferenceKey($type, $method): string
    {
        return 'notify_'.$type.'_'.$method;
    }

    public function markAllAsRead(): static
    {
        $this->marked_all_as_read_at = now();

        return $this;
    }

    public function markNotificationsAsRead(): static
    {
        $this->read_notifications_at = now();

        return $this;
    }

    public function getAlertableNotificationTypes(): array
    {
        $types = array_keys(Notification::getSubjectModels());

        return array_filter($types, [$this, 'shouldAlert']);
    }

    public function getUnreadNotificationCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    public function getNewNotificationCount(): int
    {
        return $this->unreadNotifications()
            ->where('created_at', '>', $this->read_notifications_at ?? 0)
            ->count();
    }

    public function shouldAlert($type): bool
    {
        return (bool) $this->getPreference(static::getNotificationPreferenceKey($type, 'alert'));
    }

    public function shouldEmail($type): bool
    {
        return (bool) $this->getPreference(static::getNotificationPreferenceKey($type, 'email'));
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    protected function unreadNotifications()
    {
        return $this->notifications()
            ->whereIn('type', $this->getAlertableNotificationTypes())
            ->whereNull('read_at')
            ->where('is_deleted', false)
            ->whereSubjectVisibleTo($this);
    }

    protected function getUnreadNotifications(): Collection
    {
        return $this->unreadNotifications()->get();
    }
}

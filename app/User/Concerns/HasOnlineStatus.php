<?php

namespace App\User\Concerns;

use Carbon\Carbon;

/**
 * @property Carbon $last_active_at
 */
trait HasOnlineStatus
{
    private const int LAST_SEEN_UPDATE_DIFF = 180;

    public function initializeHasOnlineStatus(): void
    {
        $this->mergeCasts([
            'last_active_at' => 'datetime',
        ]);
    }

    public function updateLastActiveAt(): self
    {
        $now = now();

        if (is_null($this->last_active_at) || $this->last_active_at->diffInSeconds($now) > self::LAST_SEEN_UPDATE_DIFF) {
            $this->last_active_at = $now;
        }

        return $this;
    }

    public function isOnline(): bool
    {
        return $this->getPreference('discloseOnline', false)
            && $this->last_active_at && ($this->last_active_at)->diffInSeconds() < self::LAST_SEEN_UPDATE_DIFF;

        //        return (bool) app(UserSettings::class)->show_online_statuses
        //            && $this->getPreference('discloseOnline')
        //            && $this->last_active_at && ($this->last_active_at)->diffInMinutes() < 5;
    }
}

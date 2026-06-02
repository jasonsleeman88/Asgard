<?php

declare(strict_types=1);

namespace App\User\Concerns;

use App\User\Contracts\DisplayNameDriverInterface;
use Illuminate\Support\Str;

trait HasDisplayNames
{
    protected static DisplayNameDriverInterface $displayNameDriver;

    public static function setDisplayNameDriver(DisplayNameDriverInterface $driver): void
    {
        static::$displayNameDriver = $driver;
    }

    public function getDisplayNameAttribute(): string
    {
        return static::$displayNameDriver->displayName($this);
    }

    public function getNameAttribute(): string
    {
        return static::$displayNameDriver->displayName($this);
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}

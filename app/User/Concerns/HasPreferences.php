<?php

namespace App\User\Concerns;

use Illuminate\Support\Arr;

/**
 * @property array $preferences
 */
trait HasPreferences
{
    protected static array $preferences = [];

    public static function registerPreference($key, ?callable $transformer = null, $default = null): void
    {
        static::$preferences[$key] = compact('transformer', 'default');
    }

    public function getPreferencesAttribute($value): array
    {
        $defaults = array_map(function ($value) {
            return $value['default'];
        }, static::$preferences);

        $user = $value !== null ? Arr::only((array) json_decode($value, true), array_keys(static::$preferences)) : [];

        return array_merge($defaults, $user);
    }

    public function setPreferencesAttribute($value): void
    {
        $this->attributes['preferences'] = json_encode($value);
    }

    public function getPreference($key, $default = null)
    {
        return Arr::get($this->preferences, $key, $default);
    }

    public function setPreference($key, $value): static
    {
        if (isset(static::$preferences[$key])) {
            $preferences = $this->preferences;

            if (! is_null($transformer = static::$preferences[$key]['transformer'])) {
                $preferences[$key] = call_user_func($transformer, $value);
            } else {
                $preferences[$key] = $value;
            }

            $this->preferences = $preferences;
        }

        return $this;
    }
}

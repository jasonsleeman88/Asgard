<?php

namespace App\Settings\Repositories;

use App\Settings\Contracts\SettingsRepository;

class UninstalledSettings implements SettingsRepository
{
    public function all(): array
    {
        return [];
    }

    public function get($key, $default = null)
    {
        return $default;
    }

    public function set($key, $value)
    {
        // Do nothing
    }

    public function delete($keyLike)
    {
        // Do nothing
    }
}

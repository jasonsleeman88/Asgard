<?php

namespace App\Settings\Contracts;

interface SettingsRepository
{
    public function all(): array;

    public function get($key, $default = null);

    public function set($key, $value);

    public function delete($keyLike);
}

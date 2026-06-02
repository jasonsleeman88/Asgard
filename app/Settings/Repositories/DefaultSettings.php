<?php

namespace App\Settings\Repositories;

use App\Settings\Contracts\SettingsRepository;
use Illuminate\Support\Collection;

class DefaultSettings implements SettingsRepository
{
    public function __construct(private SettingsRepository $inner, protected Collection $defaults) {}

    public function all(): array
    {
        return array_merge($this->defaults->toArray(), $this->inner->all());
    }

    public function get($key, $default = null)
    {
        return $this->inner->get($key, $this->defaults->get($key, $default));
    }

    public function set($key, $value)
    {
        $this->inner->set($key, $value);
    }

    public function delete($keyLike)
    {
        $this->inner->delete($keyLike);
    }
}

<?php

namespace App\Settings\Repositories;

use App\Settings\Contracts\SettingsRepository;
use Illuminate\Support\Arr;

class MemoryCacheSettings implements SettingsRepository
{
    protected bool $isCached = false;

    protected array $cache = [];

    public function __construct(protected SettingsRepository $inner) {}

    public function all(): array
    {
        if (! $this->isCached) {
            $this->cache = $this->inner->all();
            $this->isCached = true;
        }

        return $this->cache;
    }

    public function get($key, $default = null): mixed
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        } elseif (! $this->isCached) {
            return Arr::get($this->all(), $key, $default);
        }

        return $default;
    }

    public function set($key, $value): void
    {
        $this->cache[$key] = $value;

        $this->inner->set($key, $value);
    }

    public function delete($keyLike): void
    {
        unset($this->cache[$keyLike]);

        $this->inner->delete($keyLike);
    }
}

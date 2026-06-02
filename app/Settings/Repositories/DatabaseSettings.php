<?php

namespace App\Settings\Repositories;

use App\Settings\Contracts\SettingsRepository;
use Illuminate\Database\ConnectionInterface;

class DatabaseSettings implements SettingsRepository
{
    public function __construct(protected ConnectionInterface $database) {}

    public function all(): array
    {
        return $this->database->table('settings')->pluck('value', 'key')->all();
    }

    public function get($key, $default = null): mixed
    {
        if (is_null($value = $this->database->table('settings')->where('key', $key)->value('value'))) {
            return $default;
        }

        return $value;
    }

    public function set($key, $value): void
    {
        $query = $this->database->table('settings')->where('key', $key);

        $method = $query->exists() ? 'update' : 'insert';

        $query->$method(compact('key', 'value'));
    }

    public function delete($keyLike): void
    {
        $this->database->table('settings')->where('key', $keyLike)->delete();
    }
}

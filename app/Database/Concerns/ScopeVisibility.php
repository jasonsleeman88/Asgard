<?php

namespace App\Database\Concerns;

use App\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

trait ScopeVisibility
{
    protected static array $visibilityScopers = [];

    public static function registerVisibilityScoper($scoper, $ability = null): void
    {
        $model = static::class;

        if ($ability === null) {
            $ability = '*';
        }

        if (! Arr::has(static::$visibilityScopers, "$model.$ability")) {
            Arr::set(static::$visibilityScopers, "$model.$ability", []);
        }

        static::$visibilityScopers[$model][$ability][] = $scoper;
    }

    public function scopeWhereVisibleTo(Builder $query, User $actor, string $ability = 'view')
    {
        foreach (array_reverse(array_merge([static::class], class_parents($this))) as $class) {
            foreach (Arr::get(static::$visibilityScopers, "$class.*", []) as $listener) {
                $listener($actor, $query, $ability);
            }
            foreach (Arr::get(static::$visibilityScopers, "$class.$ability", []) as $listener) {
                $listener($actor, $query);
            }
        }

        return $query;
    }
}

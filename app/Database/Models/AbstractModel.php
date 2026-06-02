<?php

namespace App\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use LogicException;

abstract class AbstractModel extends Model
{
    protected array $afterSaveCallbacks = [];

    protected array $afterDeleteCallbacks = [];

    public static array $defaults = [];

    public static array $customCasts = [];

    public static array $customRelations = [];

    public static function boot(): void
    {
        parent::boot();

        static::saved(function (self $model) {
            foreach ($model->releaseAfterSaveCallbacks() as $callback) {
                $callback($model);
            }
        });

        static::deleted(function (self $model) {
            foreach ($model->releaseAfterDeleteCallbacks() as $callback) {
                $callback($model);
            }
        });
    }

    public function __construct(array $attributes = [])
    {
        $this->attributes = [];

        foreach (array_merge(array_reverse(class_parents($this)), [static::class]) as $class) {
            $this->attributes = array_merge($this->attributes, Arr::get(static::$defaults, $class, []));
        }

        $this->attributes = array_map(function ($item) {
            return is_callable($item) ? $item($this) : $item;
        }, $this->attributes);

        parent::__construct($attributes);
    }

    public function getAttribute($key)
    {
        if (! is_null($value = parent::getAttribute($key))) {
            return $value;
        }

        // If a custom relation with this key has been set up, then we will load
        // and return results from the query and hydrate the relationship's
        // value on the "relationships" array.
        if (! $this->relationLoaded($key) && ($relation = $this->getCustomRelation($key))) {
            if (! $relation instanceof Relation) {
                throw new LogicException(
                    'Relationship method must return an object of type '.Relation::class
                );
            }

            return $this->relations[$key] = $relation->getResults();
        }
    }

    protected function getCustomRelation($name)
    {
        foreach (array_merge([static::class], class_parents($this)) as $class) {
            $relation = Arr::get(static::$customRelations, $class.".$name", null);
            if (! is_null($relation)) {
                return $relation($this);
            }
        }
    }

    public function getCasts(): array
    {
        $casts = parent::getCasts();

        foreach (array_merge(array_reverse(class_parents($this)), [static::class]) as $class) {
            $casts = array_merge($casts, Arr::get(static::$customCasts, $class, []));
        }

        return $casts;
    }

    public function afterSave($callback): void
    {
        $this->afterSaveCallbacks[] = $callback;
    }

    public function afterDelete($callback): void
    {
        $this->afterDeleteCallbacks[] = $callback;
    }

    public function releaseAfterSaveCallbacks(): array
    {
        $callbacks = $this->afterSaveCallbacks;

        $this->afterSaveCallbacks = [];

        return $callbacks;
    }

    public function releaseAfterDeleteCallbacks(): array
    {
        $callbacks = $this->afterDeleteCallbacks;

        $this->afterDeleteCallbacks = [];

        return $callbacks;
    }
}

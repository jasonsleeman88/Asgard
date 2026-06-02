<?php

namespace App\Post\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class RegisteredTypesScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $query = $builder->getQuery();
        $types = array_keys($model::getModels());
        $query->whereIn('type', $types);
    }
}

<?php

namespace App\Gate\Middleware;

use App\Gate\Gate;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

use function Illuminate\Support\enum_value;

class Authorize
{
    public function __construct(protected Gate $gate) {}

    public static function using($ability, ...$models)
    {
        return static::class.':'.implode(',', [enum_value($ability), ...$models]);
    }

    public function handle($request, Closure $next, string $ability, string $models)
    {
        if (! $this->gate->allows($ability, $this->getModel($request, $models))) {
            throw new AuthorizationException;
        }

        return $next($request);
    }

    protected function getModel($request, $model): Model|string|null
    {
        if ($this->isClassName($model)) {
            return trim($model);
        }

        return $request->route($model, null) ??
            ((preg_match("/^['\"](.*)['\"]$/", trim($model), $matches)) ? $matches[1] : null);
    }

    protected function isClassName($value): bool
    {
        return str_contains($value, '\\');
    }
}

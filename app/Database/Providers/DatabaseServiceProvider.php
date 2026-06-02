<?php

namespace App\Database\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('forum.database.model_private_checkers', function () {
            return config('forum.database.model_private_checkers', []);
        });
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        foreach ($this->app->make('forum.database.model_private_checkers') as $modelClass => $checkers) {
            /** @var Model $modelClass */
            $modelClass::saving(function ($instance) use ($checkers) {
                foreach ($checkers as $checker) {
                    if ($checker($instance) === true) {
                        $instance->is_private = true;

                        return;
                    }
                }

                $instance->is_private = false;
            });
        }
    }
}

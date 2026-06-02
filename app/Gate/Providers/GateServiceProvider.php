<?php

namespace App\Gate\Providers;

use App\Gate\Gate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class GateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('forum.policies', function () {
            return config('forum.policies', []);
        });

        $this->app->when(Gate::class)->needs('$policyClasses')->give(function () {
            return $this->app->make('forum.policies');
        })->needs('$userResolver')->give(function () {
            return $this->app->make('auth')->userResolver();
        });
    }

    public function boot()
    {
        Blade::if('can', function ($permission, $model = null) {
            return $this->app->make(Gate::class)->allows($permission, $model);
        });

        Blade::if('canany', function ($permissions, $model) {
            return $this->app->make(Gate::class)->any($permissions, $model);
        });
    }
}

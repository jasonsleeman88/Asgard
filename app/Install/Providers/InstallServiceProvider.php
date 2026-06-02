<?php

namespace App\Install\Providers;

use App\Settings\Contracts\SettingsRepository;
use App\Settings\Repositories\UninstalledSettings;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;

class InstallServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsRepository::class, UninstalledSettings::class);
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->app->make('session')->setDefaultDriver('file');
        $this->app->make('cache')->setDefaultDriver('file');
    }
}

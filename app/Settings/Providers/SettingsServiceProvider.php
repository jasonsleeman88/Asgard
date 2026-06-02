<?php

namespace App\Settings\Providers;

use App\Settings\Contracts\SettingsRepository;
use App\Settings\Repositories\DatabaseSettings;
use App\Settings\Repositories\DefaultSettings;
use App\Settings\Repositories\MemoryCacheSettings;
use App\Settings\Repositories\UninstalledSettings;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('forum.settings.defaults', function (): Collection {
            return new Collection(config('forum.settings.defaults', []));
        });

        $this->app->singleton(SettingsRepository::class, function () {
            return new DefaultSettings(
                new MemoryCacheSettings(
                    new DatabaseSettings(
                        $this->app->make(ConnectionInterface::class)
                    )
                ),
                $this->app->make('forum.settings.defaults')
            );
        });

        if ($this->app->runningUnitTests()) {
            $this->app->singleton(SettingsRepository::class, UninstalledSettings::class);
        }

        $this->app->alias(SettingsRepository::class, 'forum.settings');
    }

    public function boot(): void
    {
        //
    }
}

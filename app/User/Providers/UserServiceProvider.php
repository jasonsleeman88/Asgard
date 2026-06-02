<?php

namespace App\User\Providers;

use App\Settings\Contracts\SettingsRepository;
use App\User\Contracts\DisplayNameDriverInterface;
use App\User\Drivers\UsernameDriver;
use App\User\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerDisplayNameDrivers();
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        User::setDisplayNameDriver($this->app->make('forum.user.display_name.driver'));
    }

    protected function registerDisplayNameDrivers(): void
    {
        $this->app->singleton('forum.user.display_name.supported_drivers', function () {
            return config('forum.user.display_name.supported_drivers', []);
        });

        $this->app->singleton('forum.user.display_name.driver', function () {
            $drivers = $this->app->make('forum.user.display_name.supported_drivers');
            $settings = $this->app->make(SettingsRepository::class);
            $driverName = $settings->get('display_name_driver', '');

            $driverClass = Arr::get($drivers, $driverName);

            return $driverClass
                ? $this->app->make($driverClass)
                : $this->app->make(UsernameDriver::class);
        });

        $this->app->alias('forum.user.display_name.driver', DisplayNameDriverInterface::class);
    }
}

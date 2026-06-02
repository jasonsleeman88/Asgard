<?php

namespace App\Notification\Providers;

use App\Notification\Contracts\BlueprintInterface;
use App\Notification\Models\Notification;
use App\Notification\Support\NotificationSyncer;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('forum.notification.drivers', function () {
            return config('forum.notification.drivers', []);
        });

        $this->app->singleton('forum.notification.blueprints', function () {
            return config('forum.notification.blueprints', []);
        });
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->setNotificationDrivers();
        $this->setNotificationTypes();
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setNotificationDrivers(): void
    {
        foreach ($this->app->make('forum.notification.drivers') as $driverName => $driver) {
            NotificationSyncer::addNotificationDriver($driverName, $this->app->make($driver));
        }
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setNotificationTypes(): void
    {
        $blueprints = $this->app->make('forum.notification.blueprints');

        foreach ($blueprints as $blueprint => $driversEnabledByDefault) {
            $this->addType($blueprint, $driversEnabledByDefault);
        }
    }

    protected function addType(string $blueprint, array $driversEnabledByDefault): void
    {
        Notification::setSubjectModel(
            /** @var BlueprintInterface $blueprint */
            $blueprint::getType(),
            $blueprint::getSubjectModel()
        );

        foreach (NotificationSyncer::getNotificationDrivers() as $driverName => $driver) {
            $driver->registerType(
                $blueprint,
                $driversEnabledByDefault
            );
        }
    }
}

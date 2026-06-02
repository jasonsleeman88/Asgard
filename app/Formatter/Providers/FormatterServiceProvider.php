<?php

namespace App\Formatter\Providers;

use App\Formatter\Console\Commands\FormatterFlusher;
use App\Formatter\Formatter;
use Illuminate\Support\ServiceProvider;

class FormatterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('forum.formatter', function () {
            return new Formatter(
                $this->app->make('cache')->store('file'),
                storage_path('app/private')
            );
        });

        $this->app->alias('forum.formatter', Formatter::class);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FormatterFlusher::class,
            ]);
        }
    }
}

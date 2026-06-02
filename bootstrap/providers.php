<?php

use App\Database\Providers\DatabaseServiceProvider;
use App\Discussion\Providers\DiscussionServiceProvider;
use App\Formatter\Providers\FormatterServiceProvider;
use App\Foundation\Providers\AppServiceProvider;
use App\Gate\Providers\GateServiceProvider;
use App\Notification\Providers\NotificationServiceProvider;
use App\Post\Providers\PostServiceProvider;
use App\Settings\Providers\SettingsServiceProvider;
use App\User\Providers\FortifyServiceProvider;
use App\User\Providers\UserServiceProvider;

return [
    AppServiceProvider::class,
    DatabaseServiceProvider::class,
    DiscussionServiceProvider::class,
    FormatterServiceProvider::class,
    FortifyServiceProvider::class,
    GateServiceProvider::class,
    NotificationServiceProvider::class,
    PostServiceProvider::class,
    SettingsServiceProvider::class,
    UserServiceProvider::class,
];

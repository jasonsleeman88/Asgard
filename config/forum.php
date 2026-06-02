<?php

use App\Discussion\Models\Discussion;
use App\Discussion\Notifications\Blueprints\DiscussionRenamedBlueprint;
use App\Discussion\Policies\DiscussionPolicy;
use App\Gate\Policies\AbstractPolicy;
use App\Notification\Drivers\AlertNotificationDriver;
use App\Notification\Drivers\EmailNotificationDriver;
use App\Post\Models\Post;
use App\Post\Policies\PostPolicy;
use App\User\Drivers\UsernameDriver;
use App\User\Models\User;

return [
    'database' => [
        'model_private_checkers' => [
            //
        ],
    ],

    'notifications' => [
        'drivers' => [
            'alert' => AlertNotificationDriver::class,
            'email' => EmailNotificationDriver::class,
        ],
        'blueprints' => [
            DiscussionRenamedBlueprint::class => ['alert', 'email'],
        ],
    ],

    'policies' => [
        AbstractPolicy::class => [],
        Discussion::class => [
            DiscussionPolicy::class,
        ],
        Post::class => [
            PostPolicy::class,
        ],
        User::class => [
            //
        ],
    ],

    'settings' => [
        'defaults' => [
            //
        ],
    ],

    'user' => [
        'display_name' => [
            'supported_drivers' => [
                'username' => UsernameDriver::class,
            ],
        ],
    ],
];

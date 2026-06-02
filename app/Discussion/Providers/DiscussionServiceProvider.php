<?php

namespace App\Discussion\Providers;

use App\Discussion\Actions\CreateDiscussion;
use App\Discussion\Actions\UpdateDiscussion;
use App\Discussion\Contracts\CreatesDiscussions;
use App\Discussion\Contracts\UpdatesDiscussions;
use App\Discussion\Models\Discussion;
use App\Discussion\Models\Scopes\ScopeDiscussionVisibility;
use Illuminate\Support\ServiceProvider;

class DiscussionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CreatesDiscussions::class, CreateDiscussion::class);
        $this->app->singleton(UpdatesDiscussions::class, UpdateDiscussion::class);
    }

    public function boot(): void
    {
        Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility, 'view');
    }
}

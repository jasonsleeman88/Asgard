<?php

namespace App\Post\Providers;

use App\Discussion\Models\Posts\DiscussionRenamedPost;
use App\Formatter\Formatter;
use App\Post\Actions\CreateReply;
use App\Post\Actions\UpdatePost;
use App\Post\Contracts\CreatesReplies;
use App\Post\Contracts\UpdatesPosts;
use App\Post\Models\CommentPost;
use App\Post\Models\Post;
use App\Post\Models\Scopes\ScopePostVisibility;
use Illuminate\Support\ServiceProvider;

class PostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CreatesReplies::class, CreateReply::class);
        $this->app->singleton(UpdatesPosts::class, UpdatePost::class);
    }

    public function boot(Formatter $formatter): void
    {
        CommentPost::setFormatter($formatter);

        $this->setPostTypes();

        Post::registerVisibilityScoper(new ScopePostVisibility, 'view');
    }

    protected function setPostTypes(): void
    {
        $models = [
            CommentPost::class,
            DiscussionRenamedPost::class,
        ];

        foreach ($models as $model) {
            /** @var Post $model */
            Post::setModel($model::$type, $model);
        }
    }
}

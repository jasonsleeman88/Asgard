<?php

declare(strict_types=1);

namespace App\Post\Actions;

use App\Foundation\Concerns\DispatchEvents;
use App\Post\Contracts\UpdatesPosts;
use App\Post\Events\Saving;
use App\Post\Models\CommentPost;
use App\Post\Models\Post;
use App\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Fluent;
use Throwable;

class UpdatePost implements UpdatesPosts
{
    use DispatchEvents;

    /**
     * @throws Throwable
     */
    public function handle(Post $post, User $actor, Fluent $data): Post
    {
        return DB::transaction(function () use ($post, $actor, $data) {

            if ($post instanceof CommentPost) {
                if ($data->has('content')) {
                    Gate::forUser($actor)->authorize('edit', $post);

                    $post->revise($data->get('content'), $actor);
                }

                if ($data->has('isHidden')) {
                    Gate::forUser($actor)->authorize('hide', $post);

                    if ($data->get('isHidden')) {
                        $post->hide($actor);
                    } else {
                        $post->restore();
                    }
                }
            }

            event(new Saving($post, $actor, $data));

            $post->save();

            $this->dispatchEventsFor($post, $actor);

            return $post;
        });
    }
}

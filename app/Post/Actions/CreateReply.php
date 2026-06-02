<?php

namespace App\Post\Actions;

use App\Discussion\Models\Discussion;
use App\Foundation\Concerns\DispatchEvents;
use App\Notification\Support\NotificationSyncer;
use App\Post\Contracts\CreatesReplies;
use App\Post\Events\Saving;
use App\Post\Models\CommentPost;
use App\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class CreateReply implements CreatesReplies
{
    use DispatchEvents;

    public function __construct(protected NotificationSyncer $notifications) {}

    /**
     * @throws \Throwable
     */
    public function handle(Discussion $discussion, User $actor, Fluent $data, ?string $ipAddress = null, bool $isFirstPost = false): CommentPost
    {
        return DB::transaction(function () use ($discussion, $actor, $data, $ipAddress, $isFirstPost): CommentPost {
            if (! $isFirstPost) {
                $actor->assertCan('reply', $discussion);
            }

            $post = CommentPost::reply(
                discussion: $discussion,
                actor: $actor,
                data: $data,
                ipAddress: $ipAddress
            );

            event(new Saving($post, $actor, $data));

            $post->save();

            $this->notifications->onePerUser(function () use ($post, $actor) {
                $this->dispatchEventsFor($post, $actor);
            });

            return $post;
        });
    }
}

<?php

namespace App\Discussion\Actions;

use App\Discussion\Contracts\CreatesDiscussions;
use App\Discussion\Events\Saving;
use App\Discussion\Models\Discussion;
use App\Foundation\Concerns\DispatchEvents;
use App\Post\Contracts\CreatesReplies;
use App\User\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class CreateDiscussion implements CreatesDiscussions
{
    use DispatchEvents;

    public function handle(User $actor, Fluent $data, string $ipAddress): Discussion
    {
        return DB::transaction(function () use ($actor, $data, $ipAddress): Discussion {
            $actor->assertCan('startDiscussion');

            $discussion = Discussion::start(
                $data->get('title'),
                $actor
            );

            event(new Saving($discussion, $actor, $data));

            $discussion->save();

            try {
                $post = app(CreatesReplies::class)
                    ->handle($discussion, $actor, $data, $ipAddress, true);
            } catch (Exception $e) {
                $discussion->delete();

                throw $e;
            }

            $discussion->setRawAttributes($post->discussion->getAttributes(), true);
            $discussion->setFirstPost($post);
            $discussion->setLastPost($post);

            $this->dispatchEventsFor($discussion, $actor);

            $discussion->save();

            return $discussion;
        });
    }
}

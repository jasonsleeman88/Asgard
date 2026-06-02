<?php

namespace App\Discussion\Listeners;

use App\Post\Events\Posted;
use Illuminate\Contracts\Events\Dispatcher;

class DiscussionMetadataUpdater
{
    public function subscribe(Dispatcher $events): array
    {
        return [
            Posted::class => 'handlePosted',
        ];
    }

    public function handlePosted(Posted $event): void
    {
        $discussion = $event->post->discussion;

        if ($discussion && $discussion->exists) {
            $discussion->refreshCommentCount();
            $discussion->refreshLastPost();
            $discussion->refreshParticipantCount();
            $discussion->save();
        }
    }
}

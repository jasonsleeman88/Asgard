<?php

namespace App\Discussion\Actions;

use App\Discussion\Contracts\UpdatesDiscussions;
use App\Discussion\Events\Saving;
use App\Discussion\Models\Discussion;
use App\Foundation\Concerns\DispatchEvents;
use App\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class UpdateDiscussion implements UpdatesDiscussions
{
    use DispatchEvents;

    /**
     * @throws \Throwable
     */
    public function handle(Discussion $discussion, User $actor, Fluent $data): Discussion
    {
        return DB::transaction(function () use ($discussion, $actor, $data): Discussion {
            if ($data->filled('title')) {
                $actor->assertCan('rename', $discussion);
                $discussion->rename($data->get('title'));
            }

            if ($data->filled('isHidden')) {
                $actor->assertCan('hide', $discussion);

                if ($data->get('isHidden')) {
                    $discussion->hide($actor);
                } else {
                    $discussion->restore();
                }
            }

            event(new Saving($discussion, $actor, $data));

            $discussion->save();

            $this->dispatchEventsFor($discussion, $actor);

            return $discussion;
        });
    }
}

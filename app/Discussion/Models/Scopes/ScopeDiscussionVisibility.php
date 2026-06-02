<?php

namespace App\Discussion\Models\Scopes;

use App\User\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ScopeDiscussionVisibility
{
    public function __invoke(?User $actor, Builder $query): void
    {
        if (is_null($actor) || $actor->cannot('viewForum')) {
            $query->whereRaw('FALSE');

            return;
        }

        $query->where(function ($query) use ($actor) {
            $query->where('discussions.is_private', false)
                ->orWhere(function ($query) use ($actor) {
                    $query->whereVisibleTo($actor, 'viewPrivate');
                });
        });

        if (! $actor->hasPermission('discussion.hide')) {
            $query->where(function ($query) use ($actor) {
                $query->whereNull('discussions.hidden_at')
                    ->orWhere('discussions.user_id', $actor->id)
                    ->orWhere(function ($query) use ($actor) {
                        $query->whereVisibleTo($actor, 'hide');
                    });
            });
        }

        if (! $actor->hasPermission('discussion.editPosts')) {
            $query->where(function ($query) use ($actor) {
                $query->where('discussions.comment_count', '>', 0)
                    ->orWhere('discussions.user_id', $actor->id)
                    ->orWhere(function ($query) use ($actor) {
                        $query->whereVisibleTo($actor, 'editPosts');
                    });
            });
        }
    }
}

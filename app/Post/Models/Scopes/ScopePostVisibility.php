<?php

namespace App\Post\Models\Scopes;

use App\Discussion\Models\Discussion;
use App\User\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ScopePostVisibility
{
    public function __invoke(?User $actor, Builder $query)
    {
        $query->whereExists(function ($query) use ($actor) {
            $query->selectRaw('1')
                ->from('discussions')
                ->whereColumn('discussions.id', 'posts.discussion_id');
            Discussion::query()->setQuery($query)->whereVisibleTo($actor);
        });

        $query->where(function ($query) use ($actor) {
            $query->where('posts.is_private', false)
                ->orWhere(function ($query) use ($actor) {
                    $query->whereVisibleTo($actor, 'viewPrivate');
                });
        });

        if (! $actor->hasPermission('discussion.hidePosts')) {
            $query->where(function ($query) use ($actor) {
                $query->whereNull('posts.hidden_at')
                    ->orWhere('posts.user_id', $actor->id)
                    ->orWhereExists(function ($query) use ($actor) {
                        $query->selectRaw('1')
                            ->from('discussions')
                            ->whereColumn('discussions.id', 'posts.discussion_id')
                            ->where(function ($query) use ($actor) {
                                $query
                                    ->whereRaw('1=0')
                                    ->orWhere(function ($query) use ($actor) {
                                        Discussion::query()->setQuery($query)->whereVisibleTo($actor, 'hidePosts');
                                    });
                            });
                    });
            });
        }
    }
}

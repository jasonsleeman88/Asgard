<?php

namespace App\Post\Policies;

use App\Gate\Policies\AbstractPolicy;
use App\Post\Models\Post;
use App\Settings\Contracts\SettingsRepository;
use App\User\Models\User;

class PostPolicy extends AbstractPolicy
{
    public function __construct(protected SettingsRepository $settings) {}

    public function can(User $actor, string $ability, Post $post): ?string
    {
        if ($actor->can($ability.'Posts', $post->discussion)) {
            return $this->allow();
        }

        return null;
    }

    public function edit(User $actor, Post $post): ?string
    {
        if ($post->user_id == $actor->id && (! $post->hidden_at || $post->hidden_user_id == $actor->id) && $actor->can('reply', $post->discussion)) {
            $allowEditing = $this->settings->get('allow_post_editing');

            if ($allowEditing === '-1'
                || ($allowEditing === 'reply' && $post->number >= $post->discussion->last_post_number)
                || (is_numeric($allowEditing) && $post->created_at->diffInMinutes() < $allowEditing)) {
                return $this->allow();
            }
        }

        return null;
    }

    public function hide(User $actor, Post $post): ?string
    {
        if ($post->user_id == $actor->id && (! $post->hidden_at || $post->hidden_user_id == $actor->id) && $actor->can('reply', $post->discussion)) {
            $allowHiding = $this->settings->get('allow_hide_own_posts');

            if ($allowHiding === '-1'
                || ($allowHiding === 'reply' && $post->number >= $post->discussion->last_post_number)
                || (is_numeric($allowHiding) && $post->created_at->diffInMinutes() < $allowHiding)) {
                return $this->allow();
            }
        }

        return null;
    }
}

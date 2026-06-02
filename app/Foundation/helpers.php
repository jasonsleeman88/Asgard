<?php

use App\Post\Models\Post;
use App\User\Models\User;

if (! function_exists('calculatePageForPost')) {
    //    function calculatePageForPost(Post $post, ?User $user = null): float
    //    {
    //        $user = $user ?? auth()->user();
    //
    //        $postIds = $post->discussion->posts()->whereVisibleTo($user)->orderBy('number')->pluck('id');
    //        $position = $postIds->search($post->id) + 1;
    //
    //        return ceil($position / 15);
    //    }

    function calculatePageForPost(Post $post, ?User $user = null): float
    {
        $user = $user ?? auth()->user();

        $postIds = $post->discussion->posts()->whereVisibleTo($user)->orderBy('number')->pluck('id');
        $position = $postIds->search($post->id) + 1;

        return ceil($position / 15);
    }
}

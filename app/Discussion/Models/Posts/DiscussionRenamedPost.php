<?php

namespace App\Discussion\Models\Posts;

use App\Discussion\Models\Discussion;
use App\Post\Contracts\MergeableInterface;
use App\Post\Models\Post;
use App\User\Models\User;

class DiscussionRenamedPost extends Post implements MergeableInterface
{
    public static string $type = 'discussionRenamed';

    public function saveAfter(?Post $previous = null): static
    {
        // If the previous post is another 'discussion renamed' post, and it's
        // by the same user, then we can merge this post into it. If we find
        // that we've in fact reverted the title, delete it. Otherwise, update
        // its content.
        if ($previous instanceof static && $this->user_id === $previous->user_id) {
            if ($previous->content[0] == $this->content[1]) {
                $previous->delete();
            } else {
                $previous->content = static::buildContent($previous->content[0], $this->content[1]);

                $previous->save();
            }

            return $previous;
        }

        $this->save();

        return $this;
    }

    public static function reply(Discussion $discussion, User $user, string $oldTitle, string $newTitle): static
    {
        $post = new static;

        $post->content = static::buildContent($oldTitle, $newTitle);
        $post->discussion_id = $discussion->getKey();
        $post->user_id = $user->getKey();

        return $post;
    }

    protected static function buildContent(string $oldTitle, string $newTitle): array
    {
        return [
            'old' => $oldTitle,
            'new' => $newTitle,
        ];
    }

    protected function casts(): array
    {
        return [
            'content' => 'json',
        ];
    }
}

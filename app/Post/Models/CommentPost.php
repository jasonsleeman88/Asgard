<?php

namespace App\Post\Models;

use App\Discussion\Models\Discussion;
use App\Formatter\Formatter;
use App\Post\Events\Hidden;
use App\Post\Events\Posted;
use App\Post\Events\Restored;
use App\Post\Events\Revised;
use App\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Fluent;

class CommentPost extends Post
{
    public static string $type = 'comment';

    protected static Formatter $formatter;

    public function getContentAttribute($value): ?string
    {
        return static::$formatter->unparse($value, $this);
    }

    public function getParsedContentAttribute()
    {
        return $this->attributes['content'];
    }

    public function setContentAttribute($value, ?User $actor = null): void
    {
        $this->attributes['content'] = $value ? static::$formatter->parse($value, $this, $actor ?? $this->user) : null;
    }

    public function setParsedContentAttribute($value): void
    {
        $this->attributes['content'] = $value;
    }

    public function formatContent(?Request $request = null)
    {
        return static::$formatter->render($this->attributes['content'], $this, $request);
    }

    public static function getFormatter(): Formatter
    {
        return static::$formatter;
    }

    public static function setFormatter(Formatter $formatter): void
    {
        static::$formatter = $formatter;
    }

    public static function reply(Discussion $discussion, User $actor, Fluent $data, string $ipAddress): static
    {
        $post = new static;

        $post->discussion_id = $discussion->getKey();
        $post->user_id = $actor->getKey();
        $post->type = static::$type;
        $post->ip_address = $ipAddress;

        //        $post->content = $data->get('content');
        $post->setContentAttribute($data->get('content'), $actor);

        $post->raise(new Posted($post));

        return $post;
    }

    public function revise(string $content, User $actor): static
    {
        if ($this->content !== $content) {
            $oldContent = $this->content;

            //            $this->content = $content;
            $this->setContentAttribute($content, $actor);

            $this->edited_at = Date::now();
            $this->edited_user_id = $actor->getKey();

            $this->raise(new Revised($this, $actor, $oldContent));
        }

        return $this;
    }

    public function hide(?User $actor = null): static
    {
        if (! $this->hidden_at) {
            $this->hidden_at = Date::now();
            $this->hidden_user_id = $actor?->getKey();

            $this->raise(new Hidden($this));
        }

        return $this;
    }

    public function restore(): static
    {
        if ($this->hidden_at !== null) {
            $this->hidden_at = null;
            $this->hidden_user_id = null;

            $this->raise(new Restored($this));
        }

        return $this;
    }
}

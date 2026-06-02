<?php

namespace App\Discussion\Models;

use App\Database\Concerns\ScopeVisibility;
use App\Database\Models\AbstractModel;
use App\Discussion\Events\Deleted;
use App\Discussion\Events\Hidden;
use App\Discussion\Events\Renamed;
use App\Discussion\Events\Restored;
use App\Discussion\Events\Started;
use App\Foundation\Concerns\EventGenerator;
use App\Notification\Models\Notification;
use App\Post\Contracts\MergeableInterface;
use App\Post\Models\Post;
use App\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Date;
use Spatie\Sluggable\Attributes\Sluggable;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int $comment_count
 * @property int $participant_count
 * @property Carbon $created_at
 * @property int|null $user_id
 * @property int|null $first_post_id
 * @property Carbon|null $last_posted_at
 * @property int|null $last_posted_user_id
 * @property int|null $last_post_id
 * @property int|null $last_post_number
 * @property Carbon|null $hidden_at
 * @property int|null $hidden_user_id
 * @property UserState|null $state
 * @property Collection $posts
 * @property Collection $comments
 * @property Collection $participants
 * @property Post|null $firstPost
 * @property User|null $user
 * @property Post|null $lastPost
 * @property User|null $lastPostedUser
 * @property Collection $readers
 * @property bool $is_locked
 * @property bool $is_private
 */
#[Sluggable(from: 'title', to: 'slug')]
class Discussion extends AbstractModel
{
    use EventGenerator;
    use ScopeVisibility;

    protected array $modifiedPosts = [];

    protected static $stateUser;

    public static array $defaults = [
        'is_approved' => true,
    ];

    public static function boot(): void
    {
        parent::boot();

        static::deleting(function (self $discussion) {
            Notification::whereSubjectModel(Post::class)
                ->whereIn('subject_id', function ($query) use ($discussion) {
                    $query->select('id')->from('posts')->where('discussion_id', $discussion->id);
                })
                ->delete();
        });

        static::deleted(function (self $discussion) {
            $discussion->raise(new Deleted($discussion));

            Notification::whereSubject($discussion)->delete();
        });
    }

    public static function start(string $title, User $user): static
    {
        $discussion = new static;

        $discussion->title = $title;
        $discussion->user_id = $user->getKey();

        $discussion->setRelation('user', $user);

        $discussion->raise(new Started($discussion));

        return $discussion;
    }

    public function rename($title): static
    {
        if ($this->title !== $title) {
            $oldTitle = $this->title;
            $this->title = $title;

            $this->raise(new Renamed($this, $oldTitle));
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

    public function setFirstPost(Post $post): static
    {
        $this->created_at = $post->created_at;
        $this->user_id = $post->user_id;
        $this->first_post_id = $post->getKey();

        return $this;
    }

    public function setLastPost(Post $post): static
    {
        $this->last_posted_at = $post->created_at;
        $this->last_posted_user_id = $post->user_id;
        $this->last_post_id = $post->id;
        $this->last_post_number = $post->number;

        return $this;
    }

    public function refreshLastPost(): static
    {
        if ($lastPost = $this->comments()->latest()->first()) {
            /** @var Post $lastPost */
            $this->setLastPost($lastPost);
        }

        return $this;
    }

    public function refreshCommentCount(): static
    {
        $this->comment_count = $this->comments()->count();

        return $this;
    }

    public function refreshParticipantCount(): static
    {
        $this->participant_count = $this->participants()->count('users.id');

        return $this;
    }

    public function mergePost(MergeableInterface $post)
    {
        $lastPost = $this->posts()->latest()->first();

        $post = $post->saveAfter($lastPost);

        return $this->modifiedPosts[] = $post;
    }

    public function getModifiedPosts(): array
    {
        return $this->modifiedPosts;
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->posts()
            ->where('is_private', false)
            ->whereNull('hidden_at')
            ->where('type', 'comment');
    }

    public function participants(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Post::class, 'discussion_id', 'id', 'id', 'user_id')
            ->where('posts.is_private', false)
            ->where('posts.type', 'comment')
            ->distinct();
    }

    public function firstPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'first_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lastPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'last_post_id');
    }

    public function lastPostedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_posted_user_id');
    }

    public function mostRelevantPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'most_relevant_post_id');
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function state(?User $user = null): HasOne
    {
        $user = $user ?: static::$stateUser;

        return $this->hasOne(UserState::class)->where('user_id', $user ? $user->id : null);
    }

    public function stateFor(User $user): UserState
    {
        /** @var UserState|null $state */
        $state = $this->state($user)->first();

        if (! $state) {
            $state = new UserState;
            $state->discussion_id = $this->id;
            $state->user_id = $user->id;
        }

        return $state;
    }

    public static function setStateUser(User $user): void
    {
        static::$stateUser = $user;
    }

    protected function casts(): array
    {
        return [
            'last_posted_at' => 'datetime',
            'hidden_at' => 'datetime',
            'is_private' => 'boolean',
            'is_approved' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }
}

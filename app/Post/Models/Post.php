<?php

namespace App\Post\Models;

use App\Database\Concerns\ScopeVisibility;
use App\Database\Models\AbstractModel;
use App\Discussion\Models\Discussion;
use App\Foundation\Concerns\EventGenerator;
use App\Notification\Models\Notification;
use App\Post\Events\Deleted;
use App\Post\Models\Scopes\RegisteredTypesScope;
use App\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Expression;

/**
 * @property int $id
 * @property int $discussion_id
 * @property int|Expression $number
 * @property Carbon $created_at
 * @property int|null $user_id
 * @property string|null $type
 * @property string|null $content
 * @property Carbon|null $edited_at
 * @property int|null $edited_user_id
 * @property Carbon|null $hidden_at
 * @property int|null $hidden_user_id
 * @property Discussion|null $discussion
 * @property User|null $user
 * @property User|null $editedUser
 * @property User|null $hiddenUser
 * @property string $ip_address
 * @property bool $is_private
 * @property bool $is_approved
 */
class Post extends AbstractModel
{
    use EventGenerator;
    use ScopeVisibility;

    protected $table = 'posts';

    protected static array $models = [];

    public static string $type = '';

    public static array $defaults = [
        'is_approved' => true,
    ];

    public static function boot(): void
    {
        parent::boot();

        // When a post is created, set its type according to the value of the
        // subclass. Also give it an auto-incrementing number within the
        // discussion.
        static::creating(function (self $post) {
            $post->type = $post::$type;

            $db = static::getConnectionResolver()->connection();

            $post->number = new Expression('('.
                $db->table('posts', 'pn')
                    ->whereRaw($db->getTablePrefix().'pn.discussion_id = '.intval($post->discussion_id))
                    // IFNULL only works on MySQL/MariaDB
                    ->selectRaw('IFNULL(MAX('.$db->getTablePrefix().'pn.number), 0) + 1')
                    ->toSql()
                .')');
        });

        static::created(function (self $post) {
            $post->refresh();
            $post->discussion->save();
        });

        static::deleted(function (self $post) {
            $post->raise(new Deleted($post));

            Notification::whereSubject($post)->delete();
        });

        static::addGlobalScope(new RegisteredTypesScope);
    }

    public function isVisibleTo(User $user): bool
    {
        return (bool) $this->newQuery()->whereVisibleTo($user)->find($this->id);
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function editedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_user_id');
    }

    public function hiddenUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_user_id');
    }

    public function scopeAllTypes(Builder $query): Builder
    {
        return $query->withoutGlobalScopes();
    }

    public function mentionsUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_mentions_user', 'post_id', 'mentions_user_id');
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'like_post', 'post_id', 'user_id');
    }

    public function newFromBuilder($attributes = [], $connection = null): Post
    {
        $attributes = (array) $attributes;

        if (! empty($attributes['type'])
            && isset(static::$models[$attributes['type']])
            && class_exists($class = static::$models[$attributes['type']])
        ) {
            /** @var Post $instance */
            $instance = new $class;
            $instance->exists = true;
            $instance->setRawAttributes($attributes, true);
            $instance->setConnection($connection ?: $this->connection);

            return $instance;
        }

        return parent::newFromBuilder($attributes, $connection);
    }

    public static function getModels(): array
    {
        return static::$models;
    }

    public static function setModel(string $type, string $model): void
    {
        static::$models[$type] = $model;
    }

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
            'is_approved' => 'boolean',
            'edited_at' => 'datetime',
            'hidden_at' => 'datetime',
        ];
    }
}

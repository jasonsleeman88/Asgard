<?php

namespace App\Notification\Models;

use App\Database\Models\AbstractModel;
use App\Notification\Contracts\BlueprintInterface;
use App\User\Models\User;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

#[Guarded([])]
class Notification extends AbstractModel
{
    protected static array $subjectModels = [];

    public function read(): void
    {
        $this->read_at = Date::now();
    }

    public function getDataAttribute($value): ?array
    {
        return $value !== null
            ? json_decode($value, true)
            : null;
    }

    public function setDataAttribute($value): void
    {
        $this->attributes['data'] = json_encode($value);
    }

    public function getSubjectModelAttribute(): ?string
    {
        return $this->type ? Arr::get(static::$subjectModels, $this->type) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo('subject', 'subjectModel');
    }

    public function scopeWhereSubjectVisibleTo(Builder $query, User $actor): Builder
    {
        return $query->where(function ($query) use ($actor) {
            $classes = [];

            foreach (static::$subjectModels as $type => $class) {
                $classes[$class][] = $type;
            }

            foreach ($classes as $class => $types) {
                $query->orWhere(function ($query) use ($types, $class, $actor) {
                    $query->whereIn('type', $types)
                        ->whereExists(function ($query) use ($class, $actor) {
                            $query->selectRaw(1)
                                ->from((new $class)->getTable())
                                ->whereColumn('id', 'subject_id');

                            if (method_exists($class, 'registerVisibilityScoper')) {
                                $class::query()->setQuery($query)->whereVisibleTo($actor);
                            }
                        });
                });
            }
        });
    }

    public function scopeWhereSubject(Builder $query, $model): Builder
    {
        return $query->whereSubjectModel(get_class($model))
            ->where('subject_id', $model->id);
    }

    public function scopeWhereSubjectModel(Builder $query, string $class): Builder
    {
        $notificationTypes = array_filter(self::getSubjectModels(), function ($modelClass) use ($class) {
            return $modelClass === $class or is_subclass_of($class, $modelClass);
        });

        return $query->whereIn('type', array_keys($notificationTypes));
    }

    public function scopeMatchingBlueprint(Builder $query, BlueprintInterface $blueprint): Builder
    {
        return $query->where(static::getBlueprintAttributes($blueprint));
    }

    public static function notify(array $recipients, BlueprintInterface $blueprint): void
    {
        $attributes = static::getBlueprintAttributes($blueprint);
        $now = Date::now()->toDateTimeString();

        static::insert(
            array_map(function (User $user) use ($attributes, $now) {
                return $attributes + [
                    'user_id' => $user->id,
                    'created_at' => $now,
                ];
            }, $recipients)
        );

        // Invalidate notification count caches for all recipients
        $cache = resolve('cache.store');
        foreach ($recipients as $user) {
            $cache->forget("user.{$user->id}.unread_notification_count");
            $cache->forget("user.{$user->id}.new_notification_count");
        }
    }

    public static function getSubjectModels(): array
    {
        return static::$subjectModels;
    }

    public static function setSubjectModel($type, $subjectModel): void
    {
        static::$subjectModels[$type] = $subjectModel;
    }

    protected static function getBlueprintAttributes(BlueprintInterface $blueprint): array
    {
        return [
            'type' => $blueprint::getType(),
            'from_user_id' => ($fromUser = $blueprint->getFromUser()) ? $fromUser->id : null,
            'subject_id' => ($subject = $blueprint->getSubject()) ? $subject->id : null,
            'data' => ($data = $blueprint->getData()) ? json_encode($data) : null,
        ];
    }

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }
}

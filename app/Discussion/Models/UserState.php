<?php

namespace App\Discussion\Models;

use App\Database\Models\AbstractModel;
use App\Discussion\Events\UserRead;
use App\Foundation\Concerns\EventGenerator;
use App\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;

/**
 * @property int $user_id
 * @property int $discussion_id
 * @property int $last_read_post_number
 * @property Carbon $last_read_at
 * @property string $subscription
 */
#[Guarded([])]
#[Table('discussion_user')]
#[WithoutTimestamps]
class UserState extends AbstractModel
{
    use EventGenerator;

    public function read($number): static
    {
        if ($number > $this->last_read_post_number) {
            $this->last_read_post_number = $number;
            $this->last_read_at = Date::now();

            $this->raise(new UserRead($this));
        }

        return $this;
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
            'subscription' => 'string',
        ];
    }

    protected function setKeysForSaveQuery($query): Builder
    {
        $query->where('discussion_id', $this->discussion_id)
            ->where('user_id', $this->user_id);

        return $query;
    }
}

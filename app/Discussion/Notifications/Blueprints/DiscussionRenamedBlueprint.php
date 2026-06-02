<?php

namespace App\Discussion\Notifications\Blueprints;

use App\Database\Models\AbstractModel;
use App\Discussion\Models\Discussion;
use App\Discussion\Models\Posts\DiscussionRenamedPost;
use App\Notification\Contracts\BlueprintInterface;
use App\User\Models\User;

class DiscussionRenamedBlueprint implements BlueprintInterface
{
    public string $type = 'discussions';

    public function __construct(protected DiscussionRenamedPost $post) {}

    public function getFromUser(): ?User
    {
        return $this->post->user;
    }

    public function getSubject(): ?AbstractModel
    {
        return $this->post->discussion;
    }

    public function getData(): array
    {
        return [
            'postNumber' => (int) $this->post->number,
        ];
    }

    public static function getType(): string
    {
        return 'discussionRenamed';
    }

    public static function getSubjectModel(): string
    {
        return Discussion::class;
    }

    public function getIcon(): string
    {
        return 'pencil';
    }

    public function getLabel(): string
    {
        return __('Someone renames a discussion I started');
    }
}

<?php

namespace App\Notification\Contracts;

use App\Database\Models\AbstractModel;
use App\User\Models\User;

interface BlueprintInterface
{
    public function getFromUser(): ?User;

    public function getSubject(): ?AbstractModel;

    public function getData(): array;

    public static function getType(): string;

    public static function getSubjectModel(): string;

    public function getIcon(): string;

    public function getLabel(): string;
}

<?php

namespace App\Notification\Contracts;

interface NotificationDriverInterface
{
    public function send(BlueprintInterface $blueprint, array $users): void;

    public function registerType(string $blueprintClass, array $driversEnabledByDefault): void;

    public function getIcon(): string;

    public function getLabel(): string;
}

<?php

declare(strict_types=1);

namespace App\User\Concerns;

use App\Gate\Gate;
use BackedEnum;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Models\Permission;

trait HasPermissions
{
    protected static Gate $gate;

    public static function setGate($gate): void
    {
        static::$gate = $gate;
    }

    public function can($ability, $arguments = null): bool
    {
        return static::$gate->forUser($this)->allows($ability, $arguments);
    }

    public function cannot($ability, $arguments = null): bool
    {
        return ! $this->can($ability, $arguments);
    }

    public function hasPermission(string|int|Permission|BackedEnum $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->checkPermissionTo($permission);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function assertPermission($condition): void
    {
        if (! $condition) {
            throw new AuthorizationException;
        }
    }

    public function assertCan($ability, $arguments = null): void
    {
        $this->assertPermission(
            $this->can($ability, $arguments)
        );
    }

    public function assertAdmin(): void
    {
        $this->assertCan('administrate');
    }
}

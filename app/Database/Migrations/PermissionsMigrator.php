<?php

namespace App\Database\Migrations;

use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Illuminate\Support\enum_value;

class PermissionsMigrator
{
    public function addPermissions(array $permissions): void
    {
        $rows = [];

        foreach ($permissions as $permission => $roles) {
            foreach ((array) $roles as $role) {
                $rows[] = [
                    'role' => enum_value($role),
                    'permission' => enum_value($permission),
                ];
            }
        }

        foreach ($rows as $row) {
            try {
                $role = Role::findByName($row['role']);
            } catch (RoleDoesNotExist $exception) {
                continue;
            }

            $role->givePermissionTo(Permission::findOrCreate($row['permission']));
        }
    }

    public function removePermissions(array $permissions): void
    {
        $rows = [];

        foreach ($permissions as $permission => $roles) {
            foreach ((array) $roles as $role) {
                $rows[] = [
                    'role' => $role,
                    'permission' => $permission,
                ];
            }
        }

        foreach ($rows as $row) {
            try {
                Permission::findByName($row['permission'])->removeRole($row['role'])->delete();
            } catch (PermissionDoesNotExist $exception) {
                continue;
            }
        }
    }
}

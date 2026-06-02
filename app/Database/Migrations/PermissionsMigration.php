<?php

namespace App\Database\Migrations;

use Illuminate\Database\Migrations\Migration;

abstract class PermissionsMigration extends Migration
{
    protected PermissionsMigrator $migrator;

    abstract public function up();

    public function __construct()
    {
        $this->migrator = app(PermissionsMigrator::class);
    }
}

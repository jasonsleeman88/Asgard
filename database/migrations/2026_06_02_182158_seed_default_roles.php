<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::findOrCreate('admin');
        Role::findOrCreate('moderator');
        Role::findOrCreate('member');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::findByName('admin')->delete();
        Role::findByName('moderator')->delete();
        Role::findByName('member')->delete();
    }
};

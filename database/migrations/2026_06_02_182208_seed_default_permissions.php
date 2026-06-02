<?php

use App\Database\Migrations\PermissionsMigration;

return new class extends PermissionsMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->migrator->addPermissions([
            'viewForum' => 'member',
            'startDiscussion' => 'member',
            'discussion.reply' => 'member',

            // moderators
            'discussion.hide' => 'moderator',
            'discussion.editPosts' => 'moderator',
            'discussion.hidePosts' => 'moderator',
            'discussion.rename' => 'moderator',
            'discussion.viewIpsPosts' => 'moderator',
            'user.viewLastSeenAt' => 'moderator',
            'user.edit' => 'moderator',
            'user.editCredentials' => 'moderator',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->migrator->removePermissions([
            'viewForum' => 'member',
            'startDiscussion' => 'member',
            'discussion.reply' => 'member',

            // moderators
            'discussion.hide' => 'moderator',
            'discussion.editPosts' => 'moderator',
            'discussion.hidePosts' => 'moderator',
            'discussion.rename' => 'moderator',
            'discussion.viewIpsPosts' => 'moderator',
            'user.viewLastSeenAt' => 'moderator',
            'user.edit' => 'moderator',
            'user.editCredentials' => 'moderator',

            // admins
            'edit.editRoles' => 'admin',
        ]);
    }
};

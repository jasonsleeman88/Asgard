<?php

namespace App\Install\Steps;

use App\Settings\Repositories\DatabaseSettings;
use Closure;
use Illuminate\Database\ConnectionInterface;

class SeedDefaultSettings
{
    private array $custom = [];

    public function __construct(private readonly ConnectionInterface $database) {}

    public function __invoke(array $data, Closure $next)
    {
        $repo = new DatabaseSettings($this->database);

        $repo->set('forum_title', $data['forum_title']);

        foreach ($this->getSettings() as $key => $value) {
            $repo->set($key, $value);
        }

        return $next($data);
    }

    private function getSettings(): array
    {
        return $this->custom + $this->getDefaults();
    }

    private function getDefaults(): array
    {
        return [
            'allow_hide_own_posts' => 'reply',
            'allow_post_editing' => 'reply',
            'allow_renaming' => '10',
            'allow_sign_up' => '1',
            'display_name_driver' => 'username',
            'extensions_enabled' => '[]',
            'forum_description' => '',
        ];
    }
}

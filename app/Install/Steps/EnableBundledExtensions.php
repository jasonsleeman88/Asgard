<?php

namespace App\Install\Steps;

use App\Extension\Extension;
use App\Extension\ExtensionManager;
use App\Settings\Repositories\DatabaseSettings;
use Closure;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EnableBundledExtensions
{
    const array DEFAULT_ENABLED_EXTENSIONS = [
        'forum-approval',
        'forum-bbcode',
        'forum-emoji',
        'forum-flags',
        'forum-likes',
        'forum-lock',
        'forum-markdown',
        'forum-mentions',
        'forum-sticky',
        'forum-subscriptions',
    ];

    public function __construct(
        private readonly ConnectionInterface $database,
        private readonly Migrator $migrator,
        private ?array $enabledExtensions = null
    ) {
        $this->enabledExtensions = $enabledExtensions ?? self::DEFAULT_ENABLED_EXTENSIONS;
    }

    /**
     * @throws FileNotFoundException
     */
    public function __invoke(array $data, Closure $next)
    {
        $extensions = ExtensionManager::resolveExtensionOrder($this->loadExtensions()->all())['valid'];

        foreach ($extensions as $extension) {
            /** @var Extension $extension */
            $extension->migrate($this->getMigrator());
            $extension->copyAssetsTo(app('filesystem')->disk('public'));
        }

        $extensionNames = json_encode(array_map(function (Extension $extension) {
            return $extension->getId();
        }, $extensions));

        (new DatabaseSettings($this->database))->set('extensions_enabled', $extensionNames);

        return $next($data);
    }

    private function loadExtensions()
    {
        $json = file_get_contents(base_path('vendor/composer/installed.json'));
        $installed = json_decode($json, true);

        // Composer 2.0 changes the structure of the installed.json manifest
        $installed = $installed['packages'] ?? $installed;

        $installedExtensions = (new Collection($installed))
            ->filter(function ($package) {
                return Arr::get($package, 'type') == 'forum-extension';
            })->filter(function ($package) {
                return ! empty(Arr::get($package, 'name'));
            })->map(function ($package) {
                $path = isset($package['install-path'])
                    ? base_path('vendor/composer/'.$package['install-path'])
                    : base_path('vendor/'.Arr::get($package, 'name'));

                $extension = new Extension($path, $package);
                $extension->setVersion(Arr::get($package, 'version'));

                return $extension;
            })->mapWithKeys(function (Extension $extension) {
                return [$extension->name => $extension];
            });

        return $installedExtensions->filter(function (Extension $extension) {
            return in_array($extension->getId(), $this->enabledExtensions);
        })->map(function (Extension $extension) use ($installedExtensions) {
            $extension->calculateDependencies($installedExtensions->map(function () {
                return true;
            })->toArray());

            return $extension;
        });
    }

    private function getMigrator(): Migrator
    {
        return $this->migrator;
    }
}

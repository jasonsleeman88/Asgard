<?php

namespace App\Install\Steps;

use Closure;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConfigureDatabase
{
    /**
     * @throws FileNotFoundException
     */
    public function __invoke(array $data, Closure $next)
    {
        Env::writeVariable(
            key: 'DB_CONNECTION',
            value: 'mysql',
            pathToFile: base_path('.env'),
            overwrite: true
        );

        $this->uncommentDatabaseConfiguration(base_path());

        Env::writeVariable(
            key: 'DB_DATABASE',
            value: $data['database_name'],
            pathToFile: base_path('.env'),
            overwrite: true
        );

        if (isset($data['database_host'])) {
            if (Str::contains($data['database_host'], ':')) {
                [$host, $post] = explode(':', $data['database_host'], 2);

                Env::writeVariable(
                    key: 'DB_PORT',
                    value: $post ?? '3306',
                    pathToFile: base_path('.env'),
                    overwrite: true
                );
            }

            Env::writeVariable(
                key: 'DB_HOST',
                value: $data['database_host'],
                pathToFile: base_path('.env'),
                overwrite: true
            );
        }

        Env::writeVariable(
            key: 'DB_USERNAME',
            value: $data['database_username'] ?? 'root',
            pathToFile: base_path('.env'),
            overwrite: true
        );

        if (isset($data['database_password'])) {
            Env::writeVariable(
                key: 'DB_PASSWORD',
                value: $data['database_password'],
                pathToFile: base_path('.env'),
                overwrite: true
            );
        }

        Config::set('database.connections.mysql.database', $data['database_name']);
        Config::set('database.connections.mysql.host', $data['database_host']);

        if (isset($data['database_port'])) {
            Config::set('database.connections.mysql.port', $data['database_port']);
        }

        Config::set('database.connections.mysql.username', $data['database_username']);

        if (isset($data['database_password'])) {
            Config::set('database.connections.mysql.password', $data['database_password']);
        }

        DB::setDefaultConnection('mysql');
        DB::purge('mysql');

        Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);

        return $next($data);
    }

    private function uncommentDatabaseConfiguration(string $directory): void
    {
        $defaults = [
            '# DB_HOST=127.0.0.1',
            '# DB_PORT=3306',
            '# DB_DATABASE=laravel',
            '# DB_USERNAME=root',
            '# DB_PASSWORD=',
        ];

        $this->replaceInFile(
            $defaults,
            collect($defaults)->map(fn ($default) => substr($default, 2))->all(),
            $directory.'/.env'
        );

        $this->replaceInFile(
            $defaults,
            collect($defaults)->map(fn ($default) => substr($default, 2))->all(),
            $directory.'/.env.example'
        );
    }

    protected function replaceInFile(string|array $search, string|array $replace, string $file): void
    {
        file_put_contents(
            $file,
            str_replace($search, $replace, file_get_contents($file))
        );
    }
}

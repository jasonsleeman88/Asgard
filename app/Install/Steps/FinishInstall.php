<?php

namespace App\Install\Steps;

use Illuminate\Support\Facades\Date;

class FinishInstall
{
    public function __invoke(array $data, \Closure $next)
    {
        file_put_contents(base_path('installed.json'), json_encode([
            'installed' => true,
            'date' => Date::now()->toDateTimeString(),
        ]));

        return $next($data);
    }
}

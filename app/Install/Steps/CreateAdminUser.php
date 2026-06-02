<?php

namespace App\Install\Steps;

use App\User\Actions\Fortify\CreateNewUser;
use Closure;

class CreateAdminUser
{
    public function __invoke(array $data, Closure $next)
    {
        $user = (new CreateNewUser)->create([
            'username' => $data['user_username'],
            'email' => $data['user_email'],
            'password' => $data['user_password'],
            'password_confirmation' => $data['user_password_confirmation'],
        ]);

        $user->markEmailAsVerified();

        $user->assignRole('admin');

        $user->save();

        return $next($data);
    }
}

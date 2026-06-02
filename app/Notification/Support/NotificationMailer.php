<?php

namespace App\Notification\Support;

use App\Notification\Contracts\MailableInterface;
use App\User\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationMailer
{
    public function send(MailableInterface $blueprint, User $user): void
    {
        $classname = $blueprint->getEmailMailableClass();

        Mail::to($user->email, $user->username)
            ->send(new $classname($blueprint, $user));
    }
}

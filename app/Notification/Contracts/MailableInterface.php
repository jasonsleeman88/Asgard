<?php

namespace App\Notification\Contracts;

interface MailableInterface
{
    public function getEmailMailableClass(): string;

    public function getEmailSubject(): string;
}

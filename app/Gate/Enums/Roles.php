<?php

namespace App\Gate\Enums;

enum Roles: string
{
    case Admin = 'admin';
    case Moderator = 'moderator';
    case Member = 'member';
}

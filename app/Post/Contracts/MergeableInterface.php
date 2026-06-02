<?php

namespace App\Post\Contracts;

use App\Post\Models\Post;

interface MergeableInterface
{
    public function saveAfter(?Post $previous = null);
}

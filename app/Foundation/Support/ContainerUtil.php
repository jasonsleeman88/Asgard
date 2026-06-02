<?php

namespace App\Foundation\Support;

use Illuminate\Contracts\Container\Container;

class ContainerUtil
{
    public static function wrapCallback($callback, Container $container)
    {
        if (is_string($callback) && ! is_callable($callback)) {
            $callback = function (&...$args) use ($container, $callback) {
                $callback = $container->make($callback);

                return $callback(...$args);
            };
        }

        return $callback;
    }
}

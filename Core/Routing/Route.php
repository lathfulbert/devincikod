<?php

namespace App\Core\Routing;

use App\Core\Application;

class Route
{
    public static function __callStatic($method, $args)
    {
        $router = Application::getInstance()->router;

        if (!$router) {
            throw new \RuntimeException("Router not initialized");
        }

        return $router->$method(...$args);
    }
}

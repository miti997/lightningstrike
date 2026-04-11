<?php

namespace Lightningstrike\Exception\Middleware;

use Exception;

class MiddlewareNotFound extends Exception
{
    public function __construct(string $middlewareClass)
    {
        parent::__construct("Middleware does not exist: {$middlewareClass}");
    }
}
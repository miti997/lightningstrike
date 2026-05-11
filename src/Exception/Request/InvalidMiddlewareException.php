<?php

namespace Lightningstrike\Exception\Request;

use Exception;
use Lightningstrike\Middleware\MiddlewareInterface;

class InvalidMiddlewareException extends Exception
{
    public function __construct(string $middlewareClass)
    {
        $middlewareInterface = MiddlewareInterface::class;

        parent::__construct(
            "Invalid request handler class: {$middlewareClass}. It must implement {$middlewareInterface}."
        );
    }
}

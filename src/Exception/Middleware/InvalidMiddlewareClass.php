<?php

namespace Lightningstrike\Exception\Middleware;

use Exception;
use Lightningstrike\Middleware\MiddlewareInterface;

class InvalidMiddlewareClass extends Exception
{
    public function __construct(string $middlewareClass)
    {
        $middlewareInterface = MiddlewareInterface::class;

        parent::__construct(
            "Invalid view class: {$middlewareClass}. It must implement {$middlewareInterface}."
        );
    }
}
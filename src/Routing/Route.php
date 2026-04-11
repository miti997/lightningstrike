<?php

namespace Lightningstrike\Routing;

use Lightningstrike\Exception\Middleware\InvalidMiddlewareClass;
use Lightningstrike\Exception\Middleware\MiddlewareNotFound;
use Lightningstrike\Exception\Request\InvalidRequestHandlerException;
use Lightningstrike\Exception\Request\RequestHandlerNotFound;
use Lightningstrike\Middleware\MiddlewareInterface;
use Lightningstrike\RequestHandler\RequestHandlerInterface;

class Route
{
    private array $middlewareQueue = [];

    public function __construct(
        public string $method,
        public ?string $name,
        public string $path,
        public string $requestHandler
    ) {
        if (!class_exists($requestHandler)) {
            throw new RequestHandlerNotFound($requestHandler);
        }

        if (!is_subclass_of($requestHandler, RequestHandlerInterface::class)) {
            throw new InvalidRequestHandlerException($requestHandler);
        }
    }

    public function addMiddleware(string $middleware)
    {
        if (!class_exists($middleware)) {
            throw new MiddlewareNotFound($middleware);
        }

        if (!is_subclass_of($middleware, MiddlewareInterface::class)) {
            throw new InvalidMiddlewareClass($middleware);
        }

        $this->middlewareQueue[] = $middleware;
    }

    public function getMiddlewareQueue(): array
    {
        return array_reverse($this->middlewareQueue);
    }
}
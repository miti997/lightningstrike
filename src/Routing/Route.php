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
    /** @var array<int, string> */
    public array $middlewareQueue = [];
    /** @var array<string, string> */
    public array $paramPatterns = [];

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

    public function addMiddleware(string $middleware): self
    {
        if (!class_exists($middleware)) {
            throw new MiddlewareNotFound($middleware);
        }

        if (!is_subclass_of($middleware, MiddlewareInterface::class)) {
            throw new InvalidMiddlewareClass($middleware);
        }

        $this->middlewareQueue[] = $middleware;

        return $this;
    }

    public function where(string $param, string $pattern): self
    {
        $pattern = $this->normalizePattern($pattern);

        $this->paramPatterns[$param] = $pattern;
        return $this;
    }

    private function normalizePattern(string $pattern): string
    {
        $first = $pattern[0];
        $last  = substr($pattern, -1);

        if ($first === '/' && $last === '/') {
            return $pattern;
        }

        if ($first === '/' || $last === '/') {
            throw new \InvalidArgumentException("Invalid regex pattern: {$pattern}");
        }

        return '/' . $pattern . '/';
    }
}

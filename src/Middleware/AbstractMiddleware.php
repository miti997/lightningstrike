<?php

namespace Lightningstrike\Middleware;

use Lightningstrike\Request\RequestInterface;
use Lightningstrike\RequestHandler\RequestHandlerInterface;
use Lightningstrike\Response\ResponseInterface;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    public function __construct(private RequestHandlerInterface $next)
    {
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->process($request, $this->next);
    }
}

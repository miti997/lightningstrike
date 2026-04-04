<?php

namespace Lightningstrike\RequestHandler;

use Lightningstrike\Request\RequestInterface;
use Lightningstrike\Response\ResponseInterface;

abstract class AbstractMiddleware implements MiddlewareInterface, RequestHandlerInterface
{
    public function __construct(private RequestHandlerInterface $next)
    {
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->process($request, $this->next);
    }
}

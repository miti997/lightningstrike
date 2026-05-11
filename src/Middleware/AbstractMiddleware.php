<?php

namespace Lightningstrike\Middleware;

use Lightningstrike\Request\RequestInterface;
use Lightningstrike\RequestHandler\RequestHandlerInterface;
use Lightningstrike\Response\ResponseInterface;
use Override;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    private RequestHandlerInterface $next;

    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->process($request, $this->next);
    }

    #[Override]
    public function setNext(RequestHandlerInterface $next): void
    {
        $this->next = $next;
    }
}

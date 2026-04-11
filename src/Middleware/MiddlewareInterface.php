<?php

namespace Lightningstrike\Middleware;

use Lightningstrike\Request\RequestInterface;
use Lightningstrike\RequestHandler\RequestHandlerInterface;
use Lightningstrike\Response\ResponseInterface;

interface MiddlewareInterface extends RequestHandlerInterface
{
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface;
}

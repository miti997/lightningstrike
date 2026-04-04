<?php

namespace Lightningstrike\RequestHandler;

use Lightningstrike\Request\RequestInterface;
use Lightningstrike\Response\ResponseInterface;

interface MiddlewareInterface
{
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface;
}

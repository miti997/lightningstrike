<?php

namespace Lightningstrike\RequestHandler;

use Lightningstrike\Request\RequestInterface;
use Lightningstrike\Response\ResponseInterface;

interface RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface;
}

<?php

namespace Lightningstrike\Script;

use Lightningstrike\Request\RequestInterface;
use Lightningstrike\RequestHandler\RequestHandlerInterface;
use Lightningstrike\Response\ResponseInterface;

interface ScriptInterface extends RequestHandlerInterface
{
    public function process(RequestInterface $request): ResponseInterface;
}
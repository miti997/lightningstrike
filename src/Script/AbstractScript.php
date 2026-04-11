<?php

namespace Lightningstrike\Script;

use Lightningstrike\Request\RequestInterface;
use Lightningstrike\Response\ResponseInterface;

abstract class AbstractScript implements ScriptInterface
{
    public function handle(RequestInterface $request): ResponseInterface
    {
       return $this->process($request);
    }
}
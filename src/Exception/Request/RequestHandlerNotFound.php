<?php

namespace Lightningstrike\Exception\Request;

use Exception;

class RequestHandlerNotFound extends Exception
{
    public function __construct(string $handlerClass) {
        parent::__construct("Request handler does not exist: {$handlerClass}");
    }
}
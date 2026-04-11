<?php

namespace Lightningstrike\Exception\Request;

use Exception;
use Lightningstrike\RequestHandler\RequestHandlerInterface;

class InvalidRequestHandlerException extends Exception
{
    public function __construct(string $handlerClass) {
        $handlerInterface = RequestHandlerInterface::class;

        parent::__construct(
            "Invalid request handler class: {$handlerClass}. It must implement {$handlerInterface}."
        );
    }
}
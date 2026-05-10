<?php

namespace Lightningstrike\Exception\Request;

use Exception;

class InvalidRequestMethod extends Exception
{
     public function __construct(string $request)
    {
        parent::__construct("Request of type {$request} is not supported");
    }
}
<?php

namespace Lightningstrike\Exception\Request;

use Exception;

class NoRouteMatched extends Exception
{
    public function __construct(string $uri)
    {
        parent::__construct("No route matched for URI: {$uri}");
    }
}
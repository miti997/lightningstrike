<?php

namespace Lightningstrike\Exception\Request;

use Exception;

class NoRequestHandlerProvided extends Exception
{
     public function __construct()
    {
        parent::__construct("No request handler provided");
    }
}
<?php

namespace Lightningstrike\Exception\View;

use Exception;

class ViewClassNotFound extends Exception
{
    public function __construct(string $viewClass)
    {
        parent::__construct("View class does not exist: {$viewClass}");
    }
}
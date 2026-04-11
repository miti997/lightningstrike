<?php

namespace Lightningstrike\Exception\View;

use Exception;
use Lightningstrike\View\ViewInterface;

class InvalidViewClass extends Exception
{
    public function __construct(string $viewClass)
    {
        $viewInterface = ViewInterface::class;

        parent::__construct(
            "Invalid view class: {$viewClass}. It must implement {$viewInterface}."
        );
    }
}
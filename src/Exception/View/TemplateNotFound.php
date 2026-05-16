<?php

namespace Lightningstrike\Exception\View;

use Exception;
use Lightningstrike\View\ViewInterface;

class TemplateNotFound extends Exception
{
    public function __construct(string $template)
    {
        parent::__construct(
            "Template not found: {$template}"
        );
    }
}

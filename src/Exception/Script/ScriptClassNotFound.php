<?php

namespace Lightningstrike\Exception\Script;

use Exception;

class ScriptClassNotFound extends Exception
{
    public function __construct(string $scriptClass)
    {
        parent::__construct("Script class does not exist: {$scriptClass}");
    }
}
<?php

namespace Lightningstrike\Exception\Script;

use Exception;
use Lightningstrike\Script\ScriptInterface;

class InvalidScriptClass extends Exception
{
    public function __construct(string $scriptClass)
    {
        $scriptInterface = ScriptInterface::class;

        parent::__construct(
            "Invalid script class: {$scriptClass}. It must implement {$scriptInterface}."
        );
    }
}
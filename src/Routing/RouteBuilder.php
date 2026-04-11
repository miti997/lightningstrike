<?php

namespace Lightningstrike\Routing;

use Lightningstrike\Exception\Script\InvalidScriptClass;
use Lightningstrike\Exception\Script\ScriptClassNotFound;
use Lightningstrike\Exception\View\InvalidViewClass;
use Lightningstrike\Exception\View\ViewClassNotFound;
use Lightningstrike\Request\Request;
use Lightningstrike\Script\ScriptInterface;
use Lightningstrike\View\ViewInterface;

class RouteBuilder
{
    private ?Route $currentRoute = null;

    private array $routes = [];

    public function get(
        string $path,
        string $view,
        ?string $name = null,
    ): Route {
        if (!class_exists($view)) {
            throw new ViewClassNotFound($view);
        }

        if (!is_subclass_of($view, ViewInterface::class)) {
            throw new InvalidViewClass($view);
        }

        if ($this->currentRoute !== null) {
            $this->routes[] = $this->currentRoute;
        }

        $this->currentRoute = new Route(Request::METHOD_GET, $name, $path, $view);

        return $this->currentRoute;
    }

    public function post(
        string $path,
        string $script,
        ?string $name = null,
    ): Route {
        if (!class_exists($script)) {
            throw new ScriptClassNotFound($script);
        }

        if (!is_subclass_of($script, ScriptInterface::class)) {
            throw new InvalidScriptClass($script);
        }

        if ($this->currentRoute !== null) {
            $this->routes[] = $this->currentRoute;
        }

        $this->currentRoute = new Route(Request::METHOD_GET, $name, $path, $script);

        return $this->currentRoute;
    }
}

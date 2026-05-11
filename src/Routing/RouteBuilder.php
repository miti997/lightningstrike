<?php

namespace Lightningstrike\Routing;

use Lightningstrike\Exception\Script\InvalidScriptClass;
use Lightningstrike\Exception\Script\ScriptClassNotFound;
use Lightningstrike\Exception\View\InvalidViewClass;
use Lightningstrike\Exception\View\ViewClassNotFound;
use Lightningstrike\Request\Request;
use Lightningstrike\Script\ScriptInterface;
use Lightningstrike\View\ViewInterface;
use Lightningstrike\Routing\Route;

/**
 * @phpstan-type RouteNode array{
 *     FIXED: array<string,mixed>,
 *     DYNAMIC: array<string,mixed>,
 *     HANDLER: mixed,
 *     MIDDLEWARE: array<string,mixed>,
 *     PATTERN?: string|null
 * }
 */
class RouteBuilder
{
    private ?Route $currentRoute = null;

    /** @var array<int, Route> $routes */
    private array $routes = [];

    public const string FIXED = 'FIXED';
    public const string DYNAMIC = 'DYNAMIC';
    public const string HANDLER = 'HANDLER';
    public const string MIDDLEWARE = 'MIDDLEWARE';
    public const string PATTERN = 'PATTERN';

    /**
     * @var array<string, RouteNode>
     */
    private array $tree = [
        Request::METHOD_GET => [
            self::FIXED => [],
            self::DYNAMIC => [],
            self::HANDLER => null,
            self::MIDDLEWARE => [],
        ],
        Request::METHOD_POST => [
            self::FIXED => [],
            self::DYNAMIC => [],
            self::HANDLER => null,
            self::MIDDLEWARE => [],
        ],
    ];

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

        $this->currentRoute = new Route(Request::METHOD_POST, $name, $path, $script);

        return $this->currentRoute;
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function buildRouteTree(): array
    {
        if ($this->currentRoute !== null) {
            $this->routes[] = $this->currentRoute;
            $this->currentRoute = null;
        }

        foreach ($this->routes as $route) {
            $method = $route->method;
            $path = trim($route->path, '/');

            $segments = $path === '' ? [] : explode('/', $path);

            $current = &$this->tree[$method];

            foreach ($segments as $segment) {
                // Dynamic segment: {id}
                if (preg_match('/^\{.+\}$/', $segment)) {
                    $segment = trim($segment, '{');
                    $segment = trim($segment, '}');

                    if (!isset($current[self::DYNAMIC][$segment])) {// @phpstan-ignore-line
                        $current[self::DYNAMIC][$segment] = [// @phpstan-ignore-line
                            self::FIXED => [],
                            self::DYNAMIC => [],
                            self::HANDLER => null,
                            self::PATTERN => $route->paramPatterns[$segment] ?? null,
                        ];
                    }

                    $current = &$current[self::DYNAMIC][$segment];
                } else {
                    // Fixed segment
                    if (!isset($current[self::FIXED][$segment])) {// @phpstan-ignore-line
                        $current[self::FIXED][$segment] = [// @phpstan-ignore-line
                            self::FIXED => [],
                            self::DYNAMIC => [],
                            self::HANDLER => null,
                        ];
                    }

                    $current = &$current[self::FIXED][$segment];
                }
            }

            $current[self::HANDLER] = $route->requestHandler;// @phpstan-ignore-line
            $current[self::MIDDLEWARE] = $route->middlewareQueue;// @phpstan-ignore-line

            unset($current);
        }

        return $this->tree;
    }

    /**
     * @param array<string, mixed> $tree
     */
    public function cacheTree(string $cachePath, array $tree): void
    {
        $dir = dirname($cachePath);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $export = var_export($tree, true);

        $content = <<<PHP
            <?php

            return {$export};

            PHP;

        $tempFile = $cachePath . '.tmp';

        file_put_contents($tempFile, $content);
        rename($tempFile, $cachePath);
    }
}

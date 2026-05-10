<?php

namespace Lightningstrike\Middleware;

use Lightningstrike\Exception\Request\InvalidRequestHandlerException;
use Lightningstrike\Exception\Request\InvalidRequestMethod;
use Lightningstrike\Exception\Request\NoRequestHandlerProvided;
use Lightningstrike\Exception\Request\NoRouteMatched;
use Lightningstrike\Request\RequestInterface;
use Lightningstrike\RequestHandler\RequestHandlerInterface;
use Lightningstrike\Response\ResponseInterface;
use Lightningstrike\Routing\RouteBuilder;

class RoutingMiddleware extends AbstractMiddleware
{
    public function __construct(private array $routes)
    {
        parent::__construct(null);
    }

    public function process(RequestInterface $request, ?RequestHandlerInterface $next = null): ResponseInterface
    {
        $uri = $request->getUri();
        $method = $request->getRequestMethod();

        if (!isset($this->routes[$method])) {
            throw new InvalidRequestMethod($method);
        } else {
            $current = $this->routes[$method];
        }

        $uri = trim($uri, '/');

        if ($uri === '') {
            $matched = true;
        } else {
            $uriParts = explode('/', trim($uri, '/'));
            $parseCount = 0;

            foreach ($uriParts as $part) {
                if (isset($current[RouteBuilder::FIXED][$part])) {
                    $current = $current[RouteBuilder::FIXED][$part];
                    $parseCount += 1;
                } elseif (isset($current[RouteBuilder::DYNAMIC]) && !empty($current[RouteBuilder::DYNAMIC])) {
                    foreach ($current[RouteBuilder::DYNAMIC] as $paramName => $dynamicPart) {
                        if (isset($dynamicPart[RouteBuilder::PATTERN]) && $dynamicPart[RouteBuilder::PATTERN] !== null) {
                            if (preg_match($this->normalizePattern($dynamicPart[RouteBuilder::PATTERN]), $part)) {
                                $current = $dynamicPart;
                                $parseCount += 1;
                                $request->setPathParam($paramName, $part);
                                break;
                            } else {
                                continue;
                            }
                        } else {
                            $current = $dynamicPart;
                            $parseCount += 1;
                            $request->setPathParam($paramName, $part);
                            break;
                        }
                    }
                } else {
                    break;
                }
            }

            $matched = $parseCount === count($uriParts);
        }

        if (!$matched) {
            throw new NoRouteMatched($uri);
        }

        if (!isset($current[RouteBuilder::HANDLER]) || $current[RouteBuilder::HANDLER] === null) {
            throw new NoRequestHandlerProvided();
        } else {
            $handler = $current[RouteBuilder::HANDLER];
        }

        $handlerInstance = new $handler();

        if (!$handlerInstance instanceof RequestHandlerInterface) {
            throw new InvalidRequestHandlerException($handlerInstance::class);
        } else {
            return $handlerInstance->handle($request);
        }
    }

    private function normalizePattern(string $pattern): string
    {
        $first = $pattern[0];
        $last  = substr($pattern, -1);

        if ($first === '/' && $last === '/') {
            return $pattern;
        }

        if ($first === '/' || $last === '/') {
            throw new \InvalidArgumentException("Invalid regex pattern: {$pattern}");
        }

        return '/' . $pattern . '/';
    }
}
<?php

namespace Lightningstrike\Middleware;

use Lightningstrike\Exception\Request\InvalidMiddlewareException;
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
    /** @param array<string,mixed> $routes */
    public function __construct(private array $routes)
    {
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->process($request, $this->getMatchedHandler($request));
    }

    private function getMatchedHandler(RequestInterface $request): RequestHandlerInterface
    {
        $method = (string) $request->getRequestMethod();

        if (!isset($this->routes[$method])) {
            throw new InvalidRequestMethod($method);
        } else {
            $current = $this->routes[$method];
        }

        $uri = $request->getUri();

        $uri = trim($uri, '/');

        if ($uri === '') {
            $matched = true;
        } else {
            $uriParts = explode('/', trim($uri, '/'));
            $parseCount = 0;

            foreach ($uriParts as $part) {
                if (
                    is_array($current) &&
                    is_array($current[RouteBuilder::FIXED]) &&
                    isset($current[RouteBuilder::FIXED][$part])
                ) {
                    $current = $current[RouteBuilder::FIXED][$part];
                    $parseCount += 1;
                } elseif (
                    is_array($current) &&
                    is_array($current[RouteBuilder::DYNAMIC]) &&
                    !empty($current[RouteBuilder::DYNAMIC])
                ) {
                    foreach ($current[RouteBuilder::DYNAMIC] as $paramName => $dynamicPart) {
                        if (
                            is_array($dynamicPart) &&
                            isset($dynamicPart[RouteBuilder::PATTERN]) &&
                            is_string($dynamicPart[RouteBuilder::PATTERN])
                        ) {
                            if (preg_match($dynamicPart[RouteBuilder::PATTERN], $part)) {
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

        if (!is_array($current) || !isset($current[RouteBuilder::HANDLER])) {
            throw new NoRequestHandlerProvided();
        } else {
            $handler = $current[RouteBuilder::HANDLER];
        }

        $next = new $handler();

        if (!$next instanceof RequestHandlerInterface) {
            throw new InvalidRequestHandlerException($next::class);
        }

        if (is_array($current[RouteBuilder::MIDDLEWARE]) && !empty($current[RouteBuilder::MIDDLEWARE])) {
            foreach (array_reverse($current[RouteBuilder::MIDDLEWARE]) as $middleware) {
                $middlewareInstance = new $middleware();
                if (!$middlewareInstance instanceof MiddlewareInterface) {
                    throw new InvalidMiddlewareException($middlewareInstance::class);
                }

                $middlewareInstance->setNext($next);

                $next = $middlewareInstance;
            }
        }

        return $next;
    }

    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        return $next->handle($request);
    }
}
